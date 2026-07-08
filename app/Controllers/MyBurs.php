<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyBurs extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myburs = model('App\Models\MyBursModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '11001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
            case 'MAIN-SAVE': 
                $this->myburs->burs_save();
                return redirect()->to('myburs?meaction=MAIN');
                break;

             case 'MAIN-APPROVE-A': 
                $this->myburs->burs_certifya_approve();
                return redirect()->to('myburs?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE-A': 
                $this->myburs->burs_certifya_disapprove();
                return redirect()->to('myburs?meaction=MAIN');
                break;

            case 'MAIN-APPROVE-B': 
                $this->myburs->burs_certifyb_approve();
                return redirect()->to('myburs?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE-B': 
                $this->myburs->burs_certifyb_disapprove();
                return redirect()->to('myburs?meaction=MAIN');
                break;
            
            case 'MAIN-UPLOAD': 
                $this->myburs->budget_attachment_upload();
                return redirect()->to('myburs?meaction=MAIN');
                break;
            
            case 'PRINT-BURS': 
                return view('burs/burs-pdf');
                break;
            
        }
    }
    

    private function loadMainView() {
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


        $psuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Personnel Services' ORDER BY TRIM(sub_object_code) ASC");
        $psuacsdata = $psuacsquery->getResultArray();

        $mooeuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Maintenance and Other Operating Expenses' ORDER BY TRIM(sub_object_code) ASC");
        $mooeuacsdata = $mooeuacsquery->getResultArray();

        $couacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Capital Outlay' ORDER BY TRIM(sub_object_code) ASC");
        $couacsdata = $couacsquery->getResultArray();


        $burshdquery = $this->db->query("
        SELECT 
        a.`recid`,
        a.`particulars`,
        a.`funding_source`,
        a.`payee_name`,
        a.`payee_office`,
        a.`payee_address`,
        (
            IFNULL((SELECT SUM(`amount`) FROM `tbl_burs_direct_ps_dt` WHERE project_id = a.recid), 0) +
            IFNULL((SELECT SUM(`amount`) FROM `tbl_burs_indirect_ps_dt` WHERE project_id = a.recid), 0) +
            IFNULL((SELECT SUM(`amount`) FROM `tbl_burs_direct_mooe_dt` WHERE project_id = a.recid), 0) +
            IFNULL((SELECT SUM(`amount`) FROM `tbl_burs_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
            IFNULL((SELECT SUM(`amount`) FROM `tbl_burs_indirect_co_dt` WHERE project_id = a.recid), 0) +
            IFNULL((SELECT SUM(`amount`) FROM `tbl_burs_direct_co_dt` WHERE project_id = a.recid), 0)
        ) AS amount        
         FROM tbl_burs_hd a ORDER BY a.`recid` DESC");
        $burshddata = $burshdquery->getResultArray();

        //reference/project title lookup
        $projectquery = $this->db->query("
        SELECT
            a.`fundcluster_id`,
            b.`fund_cluster_code`,
            a.`division_id`,
            c.`division_name`,
            a.`responsibility_code`,
            a.`project_title`,
            a.`mfopaps_code`
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
        WHERE a.`fundcluster_id` = '2'
        ORDER BY a.`project_title` DESC
        ");
        $projectdata = $projectquery->getResultArray();

        $certifyaquery = $this->db->query("SELECT * FROM myua_user WHERE cert_tag = '1' ORDER BY recid DESC");
        $certifyadata = $certifyaquery->getResultArray();

        $certifybquery = $this->db->query("SELECT * FROM myua_user WHERE cert_tag = '2' ORDER BY recid DESC");
        $certifybdata = $certifybquery->getResultArray();


        return view('burs/burs-main', [
            'psuacsdata' => $psuacsdata,
            'mooeuacsdata' => $mooeuacsdata,
            'couacsdata' => $couacsdata,
            'payeedata' => $payeedata,
            'projectdata' => $projectdata,
            'burshddata' => $burshddata,
            'certifyadata' => $certifyadata,
            'certifybdata' => $certifybdata,
        ]);
    }
    
    
}
