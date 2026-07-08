<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyOrs extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myors = model('App\Models\MyOrsModel');
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
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
            case 'MAIN-SAVE': 
                $this->myors->ors_save();
                return redirect()->to('myors?meaction=MAIN');
                break;

             case 'MAIN-APPROVE-A': 
                $this->myors->ors_certifya_approve();
                return redirect()->to('myors?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE-A': 
                $this->myors->ors_certifya_disapprove();
                return redirect()->to('myors?meaction=MAIN');
                break;

            case 'MAIN-APPROVE-B': 
                $this->myors->ors_certifyb_approve();
                return redirect()->to('myors?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE-B': 
                $this->myors->ors_certifyb_disapprove();
                return redirect()->to('myors?meaction=MAIN');
                break;
            
            case 'MAIN-UPLOAD': 
                $this->myors->budget_attachment_upload();
                return redirect()->to('myors?meaction=MAIN');
                break;
            
            case 'PRINT-ORS': 
                return view('ors/ors-pdf');
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

        // $orshdquery = $this->db->query("
        // SELECT 
        // a.`recid`,
        // a.`serialno`,
        // a.`particulars`,
        // a.`funding_source`,
        // a.`payee_name`,
        // a.`payee_office`,
        // a.`payee_address`,
        // (
        //     IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_ps_dt` WHERE project_id = a.recid), 0) +
        //     IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_ps_dt` WHERE project_id = a.recid), 0) +
        //     IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_mooe_dt` WHERE project_id = a.recid), 0) +
        //     IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
        //     IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_indirect_co_dt` WHERE project_id = a.recid), 0) +
        //     IFNULL((SELECT SUM(`amount`) FROM `tbl_ors_direct_co_dt` WHERE project_id = a.recid), 0)
        // ) AS amount        
        //  FROM tbl_ors_hd a ORDER BY a.`recid` DESC");
        // $orshddata = $orshdquery->getResultArray();

        $orshdquery = $this->db->query("
        SELECT 
            a.recid,
            a.serialno,
            a.particulars,
            a.funding_source,
            a.payee_name,
            a.payee_office,
            a.payee_address,
            COALESCE(ps.amount,0)
        + COALESCE(ips.amount,0)
        + COALESCE(m.amount,0)
        + COALESCE(im.amount,0)
        + COALESCE(co.amount,0)
        + COALESCE(ico.amount,0) AS amount
        FROM tbl_ors_hd a
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_ors_direct_ps_dt
            GROUP BY project_id
        ) ps ON ps.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_ors_indirect_ps_dt
            GROUP BY project_id
        ) ips ON ips.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_ors_direct_mooe_dt
            GROUP BY project_id
        ) m ON m.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_ors_indirect_mooe_dt
            GROUP BY project_id
        ) im ON im.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_ors_direct_co_dt
            GROUP BY project_id
        ) co ON co.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_ors_indirect_co_dt
            GROUP BY project_id
        ) ico ON ico.project_id = a.recid
        ORDER BY a.recid DESC
        ");
        $orshddata = $orshdquery->getResultArray();

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
        WHERE a.`fundcluster_id` = '1'
        ORDER BY a.`project_title` DESC
        ");
        $projectdata = $projectquery->getResultArray();

        $certifyaquery = $this->db->query("SELECT * FROM myua_user WHERE cert_tag = '1' ORDER BY recid DESC");
        $certifyadata = $certifyaquery->getResultArray();

        $certifybquery = $this->db->query("SELECT * FROM myua_user WHERE cert_tag = '2' ORDER BY recid DESC");
        $certifybdata = $certifybquery->getResultArray();


        return view('ors/ors-main', [
            'psuacsdata' => $psuacsdata,
            'mooeuacsdata' => $mooeuacsdata,
            'couacsdata' => $couacsdata,
            'payeedata' => $payeedata,
            'projectdata' => $projectdata,
            'orshddata' => $orshddata,
            'certifyadata' => $certifyadata,
            'certifybdata' => $certifybdata,
        ]);
    }
    
    
}
