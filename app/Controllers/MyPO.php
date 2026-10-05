<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyPOModel;

class MyPO extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mypo = model('App\Models\MyPOModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::PO_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One PO with its items (and the RFQs of its PR), for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::PO_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mypo->po_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Purchase order not found']);
                }
                return $this->response->setJSON($record);

            // The PR a PO is prepared from: header, items and RFQs
            case 'PR-LOAD':
                if (!fods_has_any_access([\Config\AccessCodes::PO_ADD, \Config\AccessCodes::PO_UPDATE])) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mypo->pr_source($this->request->getGet('id'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Purchase request not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->mypo->po_save();
                return redirect()->to('mypo?meaction=MAIN');
                break;

            case 'PRINT-PO':
                if (!fods_has_access(\Config\AccessCodes::PO_VIEW)) { return view('errors/html/access-restricted'); }
                return view('procurement/po/po-pdf');
                break;
        }
    }


    private function loadMainView() {

        $pohddata = $this->db->query("
        SELECT
            a.`recid`,
            a.`pono`,
            a.`po_date`,
            a.`prno`,
            a.`supplier_name`,
            a.`mode_of_procurement`,
            a.`end_user`,
            a.`charge_to`,
            a.`ors_burs_no`,
            a.`total_amount`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certb`,
            a.`is_disapproved_certb`,
            a.`added_at`,
            a.`added_by`,
            COALESCE(d.n,0) AS items
        FROM tbl_po_hd a
        LEFT JOIN (SELECT po_id, COUNT(*) n FROM tbl_po_dt GROUP BY po_id) d ON d.po_id = a.recid
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        // One status per PO (Box A accounting approves first, then Box B)
        foreach ($pohddata as &$row) {
            $row['status'] = self::statusOf($row);
            unset($row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb']);
        }
        unset($row);


        //PR lookup: a PO is prepared from a numbered PR (newest first)
        $prdata = $this->db->query("
        SELECT
            `recid` AS id,
            `prno`,
            `pr_date`,
            `end_user`,
            `purpose`,
            `estimated_cost`
        FROM `tbl_pr_hd`
        WHERE TRIM(`prno`) <> ''
        ORDER BY `recid` DESC
        ")->getResultArray();

        //supplier lookup: picking a payee fills the address, TIN and telephone
        $supplierdata = $this->db->query("
        SELECT
            `payee_name` AS value,
            `payee_address` AS address,
            `payee_tin` AS tin,
            `contact_no` AS tel
        FROM `tbl_payee`
        WHERE `payee_name` <> '' AND `active_status` = 1
        ORDER BY `payee_name` ASC
        ")->getResultArray();

        //ORS / BURS lookup: picking one fills its date and amount (each line table
        //is summed once per obligation, not once per row)
        $obligationAmounts = function ($prefix) {
            $tables = array_map(
                fn ($t) => "SELECT project_id, amount FROM tbl_{$prefix}_{$t}",
                ['direct_ps_dt', 'indirect_ps_dt', 'direct_mooe_dt', 'indirect_mooe_dt', 'direct_co_dt', 'indirect_co_dt']
            );
            return '(SELECT project_id, SUM(amount) amount FROM (' . implode(' UNION ALL ', $tables) . ') x GROUP BY project_id)';
        };
        $obligationdata = $this->db->query("
        SELECT 'ORS' AS source, h.`serialno`, h.`ors_date` AS obligation_date, h.`payee_name`, COALESCE(a.amount,0) AS amount, h.`recid` AS id
        FROM `tbl_ors_hd` h
        LEFT JOIN {$obligationAmounts('ors')} a ON a.project_id = h.recid
        WHERE h.`serialno` <> ''
        UNION ALL
        SELECT 'BURS', h.`serialno`, h.`burs_date`, h.`payee_name`, COALESCE(a.amount,0), h.`recid`
        FROM `tbl_burs_hd` h
        LEFT JOIN {$obligationAmounts('burs')} a ON a.project_id = h.recid
        WHERE h.`serialno` <> ''
        ORDER BY id DESC
        ")->getResultArray();

        return view('procurement/po/po-main', [
            'pohddata' => $pohddata,
            'lookups' => [
                'prs' => $prdata,
                'suppliers' => $supplierdata,
                'obligations' => $obligationdata,
                'modes' => ['SVP', 'Direct Acquisition', 'Direct Contracting', 'Public Bidding'],
                'fund_clusters' => ['01', '07'],
                'defaults' => MyPOModel::DEFAULTS,
                // Box A: accounting signatories to pick from
                'accountants' => $this->db->query("SELECT `full_name`, `position` FROM myua_user WHERE cert_tag = '3' AND is_active = 1 ORDER BY `full_name` ASC")->getResultArray(),
                // Box B: follows the PO total (same rule as MyPOModel::approver_for)
                'approval' => [
                    'limit' => MyPOModel::APPROVAL_LIMIT,
                    'below' => MyPOModel::approver_for(0),
                    'above' => MyPOModel::approver_for(MyPOModel::APPROVAL_LIMIT),
                ],
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::PO_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::PO_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that PO directly
        ]);
    }

    // pending_a: waiting for accounting (Box A)
    // pending_b: Box A approved, waiting for the approving officer (Box B)
    public static function statusOf(array $hd) {
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
