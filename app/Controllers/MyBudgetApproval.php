<?php

namespace App\Controllers;

class MyBudgetApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index(){
        $accessQuery = $this->db->query("
            SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '1004' AND `is_active` = '1'
        ");
        if ($accessQuery->getNumRows() > 0) {
            return $this->loadMainView();
        }else {
            return view('errors/html/access-restricted');
        }
    }

    private function loadMainView() {

        //pending budget table fetching
        $pendingbudgetquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`project_title`,
            a.`responsibility_code`,
            a.`fund_cluster_code`,
            a.`division_name`,
            (
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_co_dt` WHERE project_id = a.recid), 0)
            ) AS approved_budget
        FROM
            tbl_budget_hd a
        WHERE
            `is_pending` = '1' AND `is_approved` = '0' AND `is_disapproved` = '0' AND tagging = 'For Approval'
        ORDER BY a.`recid` DESC
        ");
        $pendingbudgetdata = $pendingbudgetquery->getResultArray();

        //approved budget table fetching
        $approvedbudgetquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`project_title`,
            a.`responsibility_code`,
            a.`fund_cluster_code`,
            a.`division_name`,
            (
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_co_dt` WHERE project_id = a.recid), 0)
            ) AS approved_budget
        FROM
            tbl_budget_hd a
        WHERE
            a.`is_approved` = '1' AND a.`is_disapproved` = '0' AND a.`is_pending` = '0' AND tagging = 'For Approval'
        ");
        $approvedbudgetdata = $approvedbudgetquery->getResultArray();

        //diaapproved budget table fetching
        $disapprovedbudgetquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`project_title`,
            a.`responsibility_code`,
            a.`fund_cluster_code`,
            a.`division_name`,
            (
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_co_dt` WHERE project_id = a.recid), 0)
            ) AS approved_budget
        FROM
            tbl_budget_hd a
        WHERE
            a.`is_disapproved` = '1' AND a.`is_approved` = '0' AND a.`is_pending` = '0' AND tagging = 'For Approval'
        ");
        $disapprovedbudgetdata = $disapprovedbudgetquery->getResultArray();

        return view('budget-approval/budget-approval-main', [
            'pendingbudgetdata' => $pendingbudgetdata,
            'approvedbudgetdata' => $approvedbudgetdata,
            'disapprovedbudgetdata' => $disapprovedbudgetdata,
        ]);
    }
    
}
