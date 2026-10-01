<?php

namespace App\Controllers;

class MyBudgetApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index(){
        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            // One allotment with its line items, for the review modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::BUDGETAPPROVAL_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mybudgetallotment->budget_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Budget allotment not found']);
                }
                return $this->response->setJSON($record);

            // Decisions go through the same model methods as Budget Allotment
            // (they check Budget Approval - Approve and send us back here)
            case 'MAIN-APPROVE':
                $this->mybudgetallotment->budget_approve();
                return redirect()->to('mybudgetapproval');

            case 'MAIN-DISAPPROVE':
                $this->mybudgetallotment->budget_disapprove();
                return redirect()->to('mybudgetapproval');

            // Same LIB printout as Budget Allotment, for approvers
            case 'PRINT-LIB':
                if (!fods_has_access(\Config\AccessCodes::BUDGETAPPROVAL_VIEW)) { return view('errors/html/access-restricted'); }
                return view('budget/budget-lib-print');

            default:
                if (fods_has_access(\Config\AccessCodes::BUDGETAPPROVAL_VIEW)) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }

    private function loadMainView() {

        // Everything that was sent for approval, with its total approved budget
        $budgetdata = $this->db->query("
        SELECT
            a.`recid`,
            a.`trxno`,
            a.`project_title`,
            a.`responsibility_code`,
            a.`fund_cluster_code`,
            a.`division_name`,
            a.`is_pending`,
            a.`is_approved`,
            a.`is_disapproved`,
            a.`approver`,
            a.`remarks`,
            a.`added_at`,
            a.`added_by`,
            IFNULL(dps.total,0) + IFNULL(ips.total,0) + IFNULL(dm.total,0) +
            IFNULL(im.total,0) + IFNULL(dco.total,0) + IFNULL(ico.total,0) AS approved_budget
        FROM tbl_budget_hd a
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_direct_ps_dt GROUP BY project_id) dps ON dps.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_indirect_ps_dt GROUP BY project_id) ips ON ips.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_direct_mooe_dt GROUP BY project_id) dm ON dm.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_indirect_mooe_dt GROUP BY project_id) im ON im.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_direct_co_dt GROUP BY project_id) dco ON dco.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_indirect_co_dt GROUP BY project_id) ico ON ico.project_id = a.recid
        WHERE a.`tagging` = 'For Approval'
        ORDER BY a.`is_pending` DESC, a.`added_at` DESC
        ")->getResultArray();

        foreach ($budgetdata as &$row) {
            if ($row['is_disapproved'] == '1') {
                $row['status'] = 'disapproved';
            } elseif ($row['is_approved'] == '1') {
                $row['status'] = 'approved';
            } else {
                $row['status'] = 'pending';
            }
        }
        unset($row);


        return view('budget-approval/budget-approval-main', [
            'budgetdata' => $budgetdata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'approve' => fods_has_access(\Config\AccessCodes::BUDGETAPPROVAL_APPROVE),
            ],
        ]);
    }
}
