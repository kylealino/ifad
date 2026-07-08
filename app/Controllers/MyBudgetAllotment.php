<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyBudgetAllotment extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '1001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }
                
                break;
    
            case 'MAIN-SAVE': 
                $this->mybudgetallotment->budget_save();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;

             case 'MAIN-APPROVE': 
                $this->mybudgetallotment->budget_approve();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE': 
                $this->mybudgetallotment->budget_disapprove();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;
            
            case 'MAIN-UPLOAD': 
                $this->mybudgetallotment->budget_attachment_upload();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
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
            a.recid,
            a.trxno,
            a.project_title,
            a.responsibility_code,
            a.fund_cluster_code,
            a.division_name,
            a.is_pending,
            a.is_approved,
            a.is_disapproved,
            a.added_at,
            
            IFNULL(dps.total,0) +
            IFNULL(ips.total,0) +
            IFNULL(dm.total,0) +
            IFNULL(im.total,0) +
            IFNULL(ico.total,0) +
            IFNULL(dco.total,0) AS approved_budget

        FROM tbl_budget_hd a

        LEFT JOIN (
            SELECT project_id, SUM(approved_budget) total
            FROM tbl_budget_direct_ps_dt
            GROUP BY project_id
        ) dps ON dps.project_id = a.recid

        LEFT JOIN (
            SELECT project_id, SUM(approved_budget) total
            FROM tbl_budget_indirect_ps_dt
            GROUP BY project_id
        ) ips ON ips.project_id = a.recid

        LEFT JOIN (
            SELECT project_id, SUM(approved_budget) total
            FROM tbl_budget_direct_mooe_dt
            GROUP BY project_id
        ) dm ON dm.project_id = a.recid

        LEFT JOIN (
            SELECT project_id, SUM(approved_budget) total
            FROM tbl_budget_indirect_mooe_dt
            GROUP BY project_id
        ) im ON im.project_id = a.recid

        LEFT JOIN (
            SELECT project_id, SUM(approved_budget) total
            FROM tbl_budget_indirect_co_dt
            GROUP BY project_id
        ) ico ON ico.project_id = a.recid

        LEFT JOIN (
            SELECT project_id, SUM(approved_budget) total
            FROM tbl_budget_direct_co_dt
            GROUP BY project_id
        ) dco ON dco.project_id = a.recid
        ");

        $budgetdtdata = $budgetdtquery->getResultArray();

        //hd lookup data
        $fundclusterquery = $this->db->query("SELECT `fund_cluster_code` FROM tbl_fundcluster");
        $fundclusterdata = $fundclusterquery->getResultArray();

        $divisionquery = $this->db->query("SELECT `division_name` FROM tbl_division");
        $divisiondata = $divisionquery->getResultArray();

        $psuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Personnel Services' ORDER BY TRIM(sub_object_code) ASC");
        $psuacsdata = $psuacsquery->getResultArray();

        $mooeuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Maintenance and Other Operating Expenses' ORDER BY TRIM(sub_object_code) ASC");
        $mooeuacsdata = $mooeuacsquery->getResultArray();

        //reference/project title lookup
        $projectquery = $this->db->query("
        SELECT
            a.`fundcluster_id`,
            b.`fund_cluster_code`,
            a.`division_id`,
            c.`division_name`,
            a.`responsibility_code`,
            a.`project_leader`,
            a.`project_title`
        FROM
            `tbl_reference_project` a
        JOIN
            `tbl_fundcluster`b
        ON 
            a.fundcluster_id = b.`recid`
        JOIN
            `tbl_division` c
        ON
            a.`division_id` = c.recid
        ORDER BY a.`project_title` ASC
        ");
        $projectdata = $projectquery->getResultArray();

        return view('budget/budget-allotment-main', [
            'fundclusterdata' => $fundclusterdata,
            'divisiondata' => $divisiondata,
            'psuacsdata' => $psuacsdata,
            'mooeuacsdata' => $mooeuacsdata,
            'budgetdtdata' => $budgetdtdata,
            'projectdata' => $projectdata,
        ]);
    }
    
    
}
