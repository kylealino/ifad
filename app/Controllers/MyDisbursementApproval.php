<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyDisbursementModel;

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

            // Box A, C or D, whichever the DV is waiting at
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
            a.`certified_a`,
            a.`certified_c`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certc`,
            a.`is_disapproved_certc`,
            a.`is_approved_certd`,
            a.`is_disapproved_certd`,
            a.`certa_approver`,
            a.`certc_approver`,
            a.`certd_approver`,
            a.`added_at`,
            a.`added_by`
        FROM tbl_disbursement_hd a
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($dvdata as &$row) {
            $row['status'] = MyDisbursement::statusOf($row);
            // whose turn it is, and whether that is this user
            $box = MyDisbursementModel::step_of($row);
            $row['waiting_on'] = $box === '' ? '' : MyDisbursementModel::signatory($row, $box);
            $row['mine'] = MyDisbursementModel::can_decide($row);
            // who decided last, for the list
            $row['decided_by'] = '';
            foreach (['d', 'c', 'a'] as $b) {
                if ($row['is_approved_cert' . $b] == '1' || $row['is_disapproved_cert' . $b] == '1') {
                    $row['decided_by'] = $row['cert' . $b . '_approver'];
                    break;
                }
            }
            unset($row['certified_a'], $row['certified_c'], $row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certc'], $row['is_disapproved_certc'],
                $row['is_approved_certd'], $row['is_disapproved_certd'], $row['certa_approver'], $row['certc_approver'], $row['certd_approver']);
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
