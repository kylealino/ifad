<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyLDDAPADAApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mylddapada = model('App\Models\MyLDDAPADAModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        $canView = fods_has_access(\Config\AccessCodes::LDDAPADAAPPROVAL_VIEW);

        switch ($meaction) {
            // One LDDAP-ADA with its DV lines, for the review modal
            case 'MAIN-LOAD':
                if (!$canView) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mylddapada->lddapada_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'LDDAP-ADA not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-APPROVE':
                $this->mylddapada->lddapada_approve();
                return redirect()->to('mylddapadaapproval');

            case 'MAIN-DISAPPROVE':
                $this->mylddapada->lddapada_disapprove();
                return redirect()->to('mylddapadaapproval');

            // Same printout as LDDAP-ADA, for the approver
            case 'PRINT-LDDAPADA':
                if (!$canView) { return view('errors/html/access-restricted'); }
                return view('cashier/lddapada-pdf');

            default:
                if ($canView) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }


    private function loadMainView() {

        $lddapdata = $this->db->query("
        SELECT
            a.`recid`,
            a.`lddapadano`,
            a.`ckno`,
            a.`lddapada_date`,
            a.`fund_cluster_code`,
            a.`is_approved`,
            a.`is_disapproved`,
            a.`approver`,
            a.`added_at`,
            a.`added_by`,
            COUNT(d.`recid`) AS dv_count,
            COALESCE(SUM(d.`net_amount`), 0) AS net_amount
        FROM `tbl_lddapada_hd` a
        LEFT JOIN `tbl_lddapada_dt` d ON d.`lddapada_id` = a.`recid`
        GROUP BY a.`recid`
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($lddapdata as &$row) {
            $row['status'] = MyLDDAPADA::statusOf($row);
            $row['decided_by'] = $row['status'] === 'pending' ? '' : $row['approver'];
            unset($row['is_approved'], $row['is_disapproved'], $row['approver']);
        }
        unset($row);

        return view('lddapada-approval/lddapada-approval-main', [
            'lddapdata' => $lddapdata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'approve' => fods_has_access(\Config\AccessCodes::LDDAPADAAPPROVAL_APPROVE),
            ],
        ]);
    }


}
