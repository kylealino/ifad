<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyPRModel;
use App\Models\MyPOModel;

class MyPR extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mypr = model('App\Models\MyPRModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'PR-MAIN':
                if (fods_has_access(\Config\AccessCodes::PR_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One PR with its items, PPMPs and RFQs, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::PR_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mypr->pr_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Purchase request not found']);
                }
                return $this->response->setJSON($record);

            // Items of the picked PPMPs, summed per description and unit
            case 'LOAD-PPMP':
                if (!fods_has_access(\Config\AccessCodes::PR_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                return $this->response->setJSON($this->mypr->ppmp_items((array) $this->request->getPost('ppmpno')));

            case 'PR-SAVE':
                $this->mypr->pr_save();
                return redirect()->to('mypr?meaction=PR-MAIN');
                break;

            case 'PR-RFQ-SAVE':
                $this->mypr->pr_rfq_save();
                return redirect()->to('mypr?meaction=PR-MAIN');
                break;

            case 'PR-PRINT':
                if (!fods_has_access(\Config\AccessCodes::PR_VIEW)) { return view('errors/html/access-restricted'); }
                return view('procurement/pr/pr-pdf');
                break;

            case 'RFQ-PRINT':
                if (!fods_has_access(\Config\AccessCodes::PR_VIEW)) { return view('errors/html/access-restricted'); }
                return view('procurement/pr/rfq-pdf');
                break;
        }
    }


    private function loadMainView() {

        $prhddata = $this->db->query("
        SELECT
            a.`recid`,
            a.`prno`,
            a.`office`,
            a.`pr_date`,
            a.`end_user`,
            a.`charge_to`,
            a.`purpose`,
            a.`estimated_cost`,
            a.`added_at`,
            a.`added_by`,
            a.`requested_by`,
            a.`approved_by`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certb`,
            a.`is_disapproved_certb`,
            COALESCE(d.n,0) AS items,
            COALESCE(r.n,0) AS rfqs
        FROM tbl_pr_hd a
        LEFT JOIN (SELECT pr_id, COUNT(*) n FROM tbl_pr_dt GROUP BY pr_id) d ON d.pr_id = a.recid
        LEFT JOIN (SELECT prno, COUNT(*) n FROM tbl_pr_rfq WHERE prno <> '' GROUP BY prno) r ON r.prno = a.prno
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        // One status per PR: waiting for a PR no., numbered, or out for quotation
        // ...and where it stands in approval (Box A requesting office, Box B approval)
        foreach ($prhddata as &$row) {
            $row['status'] = self::statusOf($row);
            $row['approval'] = self::approvalOf($row);
            $box = MyPRModel::step_of($row);
            $row['waiting_on'] = $box === '' ? '' : MyPRModel::signatory($row, $box);
            unset($row['requested_by'], $row['approved_by'], $row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb']);
        }
        unset($row);


        //item lookup: picking one fills the unit and the unit cost
        $productsdata = $this->db->query("
        SELECT
            `product_desc` AS value,
            `uom`,
            `price`
        FROM `mst_products`
        WHERE `product_desc` <> ''
        ORDER BY `product_desc` ASC
        ")->getResultArray();

        //PPMP lookup: a PR takes its items from the PPMPs picked
        $ppmpdata = $this->db->query("
        SELECT
            `ppmpno` AS value,
            `end_user`,
            `fiscal_year`
        FROM `tbl_ppmp_hd`
        WHERE `ppmpno` <> ''
        ORDER BY `ppmpno` ASC
        ")->getResultArray();

        return view('procurement/pr/pr-main', [
            'prhddata' => $prhddata,
            'lookups' => [
                'products' => $productsdata,
                'ppmps' => $ppmpdata,
                // Box A: heads of the requesting offices (supervisors and PPMP signatories)
                'signatories' => $this->db->query("SELECT DISTINCT `full_name`, `position` FROM myua_user WHERE (`cert_tag` = '1' OR `is_ppmp_signatory` = 1) AND `is_active` = 1 AND TRIM(`full_name`) <> '' ORDER BY `full_name` ASC")->getResultArray(),
                // Box B follows the estimated cost (MyPRModel::approver_for, mirrored in mypr.js)
                'approval' => [
                    'limit' => MyPOModel::APPROVAL_LIMIT,
                    'below' => MyPRModel::approver_for(0),
                    'above' => MyPRModel::approver_for(MyPOModel::APPROVAL_LIMIT),
                ],
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::PR_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::PR_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that PR directly
            'tab' => (string) $this->request->getGet('tab'),     // ...on this tab (rfq)
        ]);
    }

    // unnumbered: no PR no. assigned yet
    // numbered: has a PR no., no RFQ yet
    // rfq: at least one RFQ was issued for it
    public static function statusOf(array $hd) {
        if (trim((string) $hd['prno']) === '') {
            return 'unnumbered';
        }
        return $hd['rfqs'] > 0 ? 'rfq' : 'numbered';
    }

    // Approval, like ORS: Box A (requesting office) approves first, then Box B
    public static function approvalOf(array $hd) {
        if ($hd['is_disapproved_certa'] == '1' || $hd['is_disapproved_certb'] == '1') {
            return 'disapproved';
        }
        if ($hd['is_approved_certb'] == '1') {
            return 'approved';
        }
        if ($hd['is_approved_certa'] == '1') {
            return 'pending_b';
        }
        return 'pending_a';
    }


}
