<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyOrsApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myorsapproval = model('App\Models\MyOrsApprovalModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $accessQuery = $this->db->query("
            SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '2004' AND `is_active` = '1'
        ");
        if ($accessQuery->getNumRows() > 0) {
            return $this->loadMainView();
        }else {
            return view('errors/html/access-restricted');
        }
    }
    

    private function loadMainView() {

        //certify a table fetching
        $certifyaquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`particulars`,
            a.`serialno`,
            (
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_co_dt` WHERE project_id = a.recid), 0)
            ) AS amount
        FROM
            tbl_ors_hd a
        WHERE 
            is_pending = '1'
        ORDER BY a.`recid` DESC
        ");
        $certifyadata = $certifyaquery->getResultArray();

        //certify b table fetching
        $certifybquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`particulars`,
            (
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_co_dt` WHERE project_id = a.recid), 0)
            ) AS amount
        FROM
            tbl_ors_hd a
        WHERE
            is_approved_certa = '1' AND is_approved_certb = '0'
        ORDER BY a.`recid` DESC
        ");
        $certifybdata = $certifybquery->getResultArray();

        //transaction history
        $transactionquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`particulars`,
            a.`is_pending`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certb`,
            a.`is_disapproved_certb`,
            a.`certa_remarks`,
            a.`certb_remarks`,
            a.`certa_approver`,
            a.`certb_approver`,
            (
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_co_dt` WHERE project_id = a.recid), 0)
            ) AS amount
        FROM
            tbl_ors_hd a
        WHERE 
            `is_pending` = '0'
        ORDER BY a.`recid` DESC
        ");
        $transactiondata = $transactionquery->getResultArray();

        return view('ors-approval/ors-approval-main', [
            'certifyadata' => $certifyadata,
            'certifybdata' => $certifybdata,
            'transactiondata' => $transactiondata,
        ]);
    }
    
    
}
