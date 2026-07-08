<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyBurs extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myorsburs = model('App\Models\MyOrsBursModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '2001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }
                
                break;
    
            case 'MAIN-SAVE': 
                $this->myorsburs->orsburs_save();
                // return redirect()->to('myorsburs?meaction=MAIN');
                break;

             case 'MAIN-APPROVE': 
                $this->myorsburs->budget_approve();
                return redirect()->to('myorsburs?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE': 
                $this->myorsburs->budget_disapprove();
                return redirect()->to('myorsburs?meaction=MAIN');
                break;
            
            case 'MAIN-UPLOAD': 
                $this->myorsburs->budget_attachment_upload();
                return redirect()->to('myorsburs?meaction=MAIN');
                break;
            
            case 'PRINT-LIB': 
                return view('budget/budget-lib-print');
                break;
            
        }
    }
    

    private function loadMainView() {

        //budget table dt fetching
        $budgetdtquery = $this->db->query("
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
            a.`added_at`,
            (
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_co_dt` WHERE project_id = a.recid), 0)
            ) AS approved_budget
        FROM
            tbl_budget_hd a
        ");
        $budgetdtdata = $budgetdtquery->getResultArray();

        //hd lookup data
        $fundclusterquery = $this->db->query("SELECT `fund_cluster_code` FROM tbl_fundcluster");
        $fundclusterdata = $fundclusterquery->getResultArray();

        $divisionquery = $this->db->query("SELECT `division_name` FROM tbl_division");
        $divisiondata = $divisionquery->getResultArray();

        $psuacsquery = $this->db->query("SELECT * FROM tbl_uacs WHERE parent_category = 'Personal Services'");
        $psuacsdata = $psuacsquery->getResultArray();

        $mooeuacsquery = $this->db->query("SELECT * FROM tbl_uacs WHERE parent_category = 'Maintenance and Other Operating Expenses'");
        $mooeuacsdata = $mooeuacsquery->getResultArray();

        //hd lookup data
        $programquery = $this->db->query("SELECT * FROM tbl_budget_hd order by recid desc");
        $programdata = $programquery->getResultArray();

        //payee lookup
        $payeedata = $this->db->query("
        SELECT
            `payee_name`,
            `payee_office`,
            `payee_address`
        FROM
            `tbl_payee`
        ORDER BY `payee_name` ASC
        ");
        $payeedata = $payeedata->getResultArray();

        return view('orsburs/orsburs-main', [
            'fundclusterdata' => $fundclusterdata,
            'divisiondata' => $divisiondata,
            'psuacsdata' => $psuacsdata,
            'mooeuacsdata' => $mooeuacsdata,
            'budgetdtdata' => $budgetdtdata,
            'payeedata' => $payeedata,
            'programdata' => $programdata,
        ]);
    }
    
    
}
