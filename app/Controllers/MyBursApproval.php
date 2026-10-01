<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyBursApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myburs = model('App\Models\MyBursModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        $canView = fods_has_access(\Config\AccessCodes::BURSAPPROVAL_VIEW);

        switch ($meaction) {
            // One BURS with its line items, for the review modal
            case 'MAIN-LOAD':
                if (!$canView) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myburs->burs_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'BURS not found']);
                }
                return $this->response->setJSON($record);

            // Box A - requesting office
            case 'MAIN-APPROVE-A':
                $this->myburs->burs_certifya_approve();
                return redirect()->to('mybursapproval');

            case 'MAIN-DISAPPROVE-A':
                $this->myburs->burs_certifya_disapprove();
                return redirect()->to('mybursapproval');

            // Box B - budget
            case 'MAIN-APPROVE-B':
                $this->myburs->burs_certifyb_approve();
                return redirect()->to('mybursapproval');

            case 'MAIN-DISAPPROVE-B':
                $this->myburs->burs_certifyb_disapprove();
                return redirect()->to('mybursapproval');

            // Same BURS printout as BURS Entry, for certifiers
            case 'PRINT-BURS':
                if (!$canView) { return view('errors/html/access-restricted'); }
                return view('burs/burs-pdf');

            default:
                if ($canView) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }


    private function loadMainView() {

        $orsdata = $this->db->query("
        SELECT
            a.`recid`,
            a.`serialno`,
            a.`particulars`,
            a.`payee_name`,
            a.`funding_source`,
            a.`burs_date`,
            a.`certified_a`,
            a.`certified_b`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certb`,
            a.`is_disapproved_certb`,
            a.`certa_approver`,
            a.`certb_approver`,
            a.`added_at`,
            a.`added_by`,
            IFNULL(ps.amount,0) + IFNULL(ips.amount,0) + IFNULL(m.amount,0) +
            IFNULL(im.amount,0) + IFNULL(co.amount,0) + IFNULL(ico.amount,0) AS amount
        FROM tbl_burs_hd a
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_burs_direct_ps_dt GROUP BY project_id) ps ON ps.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_burs_indirect_ps_dt GROUP BY project_id) ips ON ips.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_burs_direct_mooe_dt GROUP BY project_id) m ON m.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_burs_indirect_mooe_dt GROUP BY project_id) im ON im.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_burs_direct_co_dt GROUP BY project_id) co ON co.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_burs_indirect_co_dt GROUP BY project_id) ico ON ico.project_id = a.recid
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($orsdata as &$row) {
            $row['status'] = MyBurs::statusOf($row);
            // who decided last, for the list
            $row['decided_by'] = $row['certb_approver'] !== '' && $row['status'] !== 'pending_b' ? $row['certb_approver'] : $row['certa_approver'];
            unset($row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb'], $row['certa_approver'], $row['certb_approver']);
        }
        unset($row);


        return view('burs-approval/burs-approval-main', [
            'orsdata' => $orsdata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'a' => fods_has_access(\Config\AccessCodes::BURSAPPROVAL_APPROVE),
                'b' => fods_has_access(\Config\AccessCodes::BURSAPPROVAL_APPROVE),
            ],
        ]);
    }


}
