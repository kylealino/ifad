<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyADAApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myada = model('App\Models\MyADAModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        $canView = fods_has_access(\Config\AccessCodes::ADAAPPROVAL_VIEW);

        switch ($meaction) {
            // One ADA, for the review modal
            case 'MAIN-LOAD':
                if (!$canView) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myada->ada_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'ADA not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-APPROVE':
                $this->myada->ada_approve();
                return redirect()->to('myadaapproval');

            case 'MAIN-DISAPPROVE':
                $this->myada->ada_disapprove();
                return redirect()->to('myadaapproval');

            // Same printout as ADA, for the approver
            case 'PRINT-ADA':
                if (!$canView) { return view('errors/html/access-restricted'); }
                return view('cashier/ada-pdf');

            default:
                if ($canView) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }


    private function loadMainView() {

        $adadata = $this->db->query("
        SELECT
            `recid`,
            `adano`,
            `dvno`,
            `particulars`,
            `payee_name`,
            `ada_date`,
            `net_amount`,
            `is_approved`,
            `is_disapproved`,
            `approver`,
            `added_at`,
            `added_by`
        FROM tbl_ada
        ORDER BY `recid` DESC
        ")->getResultArray();

        foreach ($adadata as &$row) {
            $row['status'] = MyLDDAPADA::statusOf($row);
            $row['decided_by'] = $row['status'] === 'pending' ? '' : $row['approver'];
            unset($row['is_approved'], $row['is_disapproved'], $row['approver']);
        }
        unset($row);

        return view('ada-approval/ada-approval-main', [
            'adadata' => $adadata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'approve' => fods_has_access(\Config\AccessCodes::ADAAPPROVAL_APPROVE),
            ],
        ]);
    }


}
