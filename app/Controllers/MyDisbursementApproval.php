<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyDisbursementApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mydisbursement = model('App\Models\MyDisbursementModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        $canView = fods_has_access(\Config\AccessCodes::DISBURSEMENTAPPROVAL_VIEW);

        switch ($meaction) {
            // One DV with its line items and history, for the review modal
            case 'MAIN-LOAD':
                if (!$canView) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mydisbursement->disbursement_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Disbursement not found']);
                }
                return $this->response->setJSON($record);

            // Accounting approval (single step)
            case 'MAIN-APPROVE':
                $this->mydisbursement->disbursement_approve();
                return redirect()->to('mydisbursementapproval');

            case 'MAIN-DISAPPROVE':
                $this->mydisbursement->disbursement_disapprove();
                return redirect()->to('mydisbursementapproval');

            // Same DV printout as Disbursement, for certifiers
            case 'PRINT-DISBURSEMENT':
                if (!$canView) { return view('errors/html/access-restricted'); }
                return view('disbursement/disbursement-pdf');

            default:
                if ($canView) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }


    private function loadMainView() {

        $dvdata = $this->db->query("
        SELECT
            a.`recid`,
            a.`dvno`,
            a.`serialno`,
            a.`particulars`,
            a.`payee_name`,
            a.`disbursement_date`,
            a.`net_amount`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`certa_approver`,
            a.`added_at`,
            a.`added_by`
        FROM tbl_disbursement_hd a
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($dvdata as &$row) {
            $row['status'] = MyDisbursement::statusOf($row);
            $row['decided_by'] = $row['status'] === 'pending' ? '' : $row['certa_approver'];
            unset($row['is_approved_certa'], $row['is_disapproved_certa'], $row['certa_approver']);
        }
        unset($row);


        return view('disbursement-approval/disbursement-approval-main', [
            'dvdata' => $dvdata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'approve' => fods_has_access(\Config\AccessCodes::DISBURSEMENTAPPROVAL_APPROVE),
            ],
        ]);
    }


}
