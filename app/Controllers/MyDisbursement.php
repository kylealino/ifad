<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyDisbursement extends BaseController
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
    
        switch ($meaction) {
            case 'MAIN': 
                return $this->loadMainView();
                break;
    
            case 'MAIN-SAVE': 
                $this->mydisbursement->disbursement_save();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;

             case 'MAIN-APPROVE-A': 
                $this->mydisbursement->disbursement_certifya_approve();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE-A': 
                $this->mydisbursement->disbursement_certifya_disapprove();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;

            case 'MAIN-APPROVE-B': 
                $this->mydisbursement->disbursement_certifyb_approve();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE-B': 
                $this->mydisbursement->disbursement_certifyb_disapprove();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;
            
            case 'MAIN-UPLOAD': 
                $this->mydisbursement->budget_attachment_upload();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;
            
            case 'PRINT-DISBURSEMENT': 
                return view('disbursement/disbursement-pdf');
                break;
            
        }
    }
    

    private function loadMainView() {

        //serialno lookup
        $serialquery = $this->db->query("
        SELECT
            `serialno`,
            `recid` AS ors_id
        FROM
            `tbl_ors_hd`
        GROUP BY
            `serialno`
        ORDER BY `recid` DESC
        ");
        $serialdata = $serialquery->getResultArray();

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


        $disbursementhdquery = $this->db->query("
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
        FROM tbl_disbursement_hd a
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_disbursement_direct_ps_dt
            GROUP BY project_id
        ) ps ON ps.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_disbursement_indirect_ps_dt
            GROUP BY project_id
        ) ips ON ips.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_disbursement_direct_mooe_dt
            GROUP BY project_id
        ) m ON m.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_disbursement_indirect_mooe_dt
            GROUP BY project_id
        ) im ON im.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_disbursement_direct_co_dt
            GROUP BY project_id
        ) co ON co.project_id = a.recid
        LEFT JOIN (
            SELECT project_id, SUM(amount) amount
            FROM tbl_disbursement_indirect_co_dt
            GROUP BY project_id
        ) ico ON ico.project_id = a.recid
        ORDER BY a.recid DESC
        ");
        $disbursementhddata = $disbursementhdquery->getResultArray();

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


        return view('disbursement/disbursement-main', [
            'payeedata' => $payeedata,
            'serialdata' => $serialdata,
            'projectdata' => $projectdata,
            'disbursementhddata' => $disbursementhddata,
            'certifyadata' => $certifyadata,
            'certifybdata' => $certifybdata,
        ]);
    }
    
    
}
