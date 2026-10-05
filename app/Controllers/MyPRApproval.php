<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyPRModel;

class MyPRApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mypr = model('App\Models\MyPRModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        $canView = fods_has_access(\Config\AccessCodes::PRAPPROVAL_VIEW);

        switch ($meaction) {
            // One PR with its items, for the review modal
            case 'MAIN-LOAD':
                if (!$canView) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mypr->pr_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Purchase request not found']);
                }
                return $this->response->setJSON($record);

            // Box A or B, whichever the PR is waiting at
            case 'MAIN-APPROVE':
                $this->mypr->pr_approve();
                return redirect()->to('myprapproval');

            case 'MAIN-DISAPPROVE':
                $this->mypr->pr_disapprove();
                return redirect()->to('myprapproval');

            // Same PR printout as Purchase Request, for the signatories
            case 'PRINT-PR':
                if (!$canView) { return view('errors/html/access-restricted'); }
                return view('procurement/pr/pr-pdf');

            default:
                if ($canView) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }


    private function loadMainView() {

        $prdata = $this->db->query("
        SELECT
            a.`recid`,
            a.`prno`,
            a.`pr_date`,
            a.`office`,
            a.`end_user`,
            a.`purpose`,
            a.`charge_to`,
            a.`estimated_cost`,
            a.`requested_by`,
            a.`approved_by`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certb`,
            a.`is_disapproved_certb`,
            a.`certa_approver`,
            a.`certb_approver`,
            a.`added_at`,
            a.`added_by`
        FROM tbl_pr_hd a
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($prdata as &$row) {
            $row['status'] = MyPR::approvalOf($row);
            // whose turn it is, and whether that is this user
            $box = MyPRModel::step_of($row);
            $row['waiting_on'] = $box === '' ? '' : MyPRModel::signatory($row, $box);
            $row['mine'] = MyPRModel::can_decide($row);
            // who decided last, for the list
            $row['decided_by'] = '';
            foreach (['b', 'a'] as $b) {
                if ($row['is_approved_cert' . $b] == '1' || $row['is_disapproved_cert' . $b] == '1') {
                    $row['decided_by'] = $row['cert' . $b . '_approver'];
                    break;
                }
            }
            unset($row['requested_by'], $row['approved_by'], $row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb'],
                $row['certa_approver'], $row['certb_approver']);
        }
        unset($row);


        return view('pr-approval/pr-approval-main', [
            'prdata' => $prdata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'approve' => fods_has_access(\Config\AccessCodes::PRAPPROVAL_APPROVE),
            ],
        ]);
    }


}
