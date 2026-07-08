<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyAbstract extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myabstract = model('App\Models\MyAbstractModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '12001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadProductsView();
                }else {
                    return view('errors/html/access-restricted');
                }
                break;


            case 'ABSTRACT-SAVE': 
                $this->myabstract->abstract_save();
                return redirect()->to('myabstract?meaction=MAIN');
                break;
            
            case 'ABSTRACT-PRINT':
				return view('procurement/abstract/abstract-pdf');
				break;

            case 'PO-PRINT':
				return view('procurement/abstract/po-pdf');
				break;

            //  case 'MAIN-APPROVE': 
            //     $this->mysaob->budget_approve();
            //     return redirect()->to('myua?meaction=MAIN');
            //     break;

            //   case 'MAIN-DISAPPROVE': 
            //     $this->mysaob->budget_disapprove();
            //     return redirect()->to('myua?meaction=MAIN');
            //     break;
        }
    }
    

    private function loadProductsView() {

        $productsdataquery = $this->db->query("
            SELECT
                `recid`,
                `product_code`,
                `product_desc`,
                `uom`,
                `price`,
                `quantity`,
                `remarks`
            FROM
                `mst_products`
            ORDER BY
                `recid` DESC
        ");
        $productsdata = $productsdataquery->getResultArray();

        $prlistdataquery = $this->db->query("
            SELECT
                `prno`
            FROM
                `tbl_pr_hd`
            ORDER BY
                `recid` DESC
        ");
        $prlistdata = $prlistdataquery->getResultArray();

        $abstractlistdataquery = $this->db->query("
            SELECT
                `recid`,
                `prno`,
                `transaction_no`,
                `abstract_date`,
                `bidder_1`,
                `bidder_2`,
                `bidder_3`,
                `bidder_4`,
                `bidder_5`
            FROM
                `tbl_abstract_hd`
            ORDER BY
                `recid` DESC
        ");
        $abstractlistdata = $abstractlistdataquery->getResultArray();

        // //hd lookup data
        // $fundclusterquery = $this->db->query("SELECT `fund_cluster_code` FROM tbl_fundcluster");
        // $fundclusterdata = $fundclusterquery->getResultArray();

        // $divisionquery = $this->db->query("SELECT `division_name` FROM tbl_division");
        // $divisiondata = $divisionquery->getResultArray();

        // $psuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Personnel Services' ORDER BY TRIM(sub_object_code) ASC");
        // $psuacsdata = $psuacsquery->getResultArray();

        // $psobjectquery = $this->db->query("SELECT DISTINCT object_code FROM mst_uacs WHERE allotment_class = 'Personnel Services'  ORDER BY TRIM(object_code) ASC");
        // $psobjectdata = $psobjectquery->getResultArray();

        // $mooeuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Maintenance and Other Operating Expenses' ORDER BY TRIM(sub_object_code) ASC");
        // $mooeuacsdata = $mooeuacsquery->getResultArray();

        // $mooeobjectquery = $this->db->query("SELECT DISTINCT object_code FROM mst_uacs WHERE allotment_class = 'Maintenance and Other Operating Expenses' ORDER BY TRIM(object_code) ASC");
        // $mooeobjectdata = $mooeobjectquery->getResultArray();
        // $mooeobjectdata[] = ['object_code' => 'General Services'];

        // $couacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Capital Outlay' ORDER BY TRIM(sub_object_code) ASC");
        // $couacsdata = $couacsquery->getResultArray();

        // $coobjectquery = $this->db->query("SELECT DISTINCT object_code FROM mst_uacs WHERE allotment_class = 'Capital Outlay' ORDER BY TRIM(object_code) ASC");
        // $coobjectdata = $coobjectquery->getResultArray();

        // $programtitlequery = $this->db->query("SELECT program_title FROM tbl_budget_hd WHERE fund_cluster_code = '01'  GROUP BY program_title ORDER BY recid DESC");
        // $programtitledata = $programtitlequery->getResultArray();

        // $saobhdquery = $this->db->query("SELECT * FROM tbl_saob_hd ORDER BY recid DESC");
        // $saobhddata = $saobhdquery->getResultArray();


        //reference/project title lookup
        // $projectquery = $this->db->query("
        //     SELECT
        //         a.`fundcluster_id`,
        //         b.`fund_cluster_code`,
        //         a.`division_id`,
        //         c.`division_name`,
        //         a.`responsibility_code`,
        //         a.`project_title`
        //     FROM
        //         `tbl_reference_project` a
        //     JOIN
        //         `tbl_fundcluster`b
        //     ON 
        //         a.fundcluster_id = b.`recid`
        //     JOIN
        //         `tbl_division` c
        //     ON
        //         a.`division_id` = c.recid
        //     ORDER BY a.`project_title` ASC
        // ");
        // $projectdata = $projectquery->getResultArray();

        return view('procurement/abstract/abstract-main', [
            // 'fundclusterdata' => $fundclusterdata,
            // 'divisiondata' => $divisiondata,
            // 'psobjectdata' => $psobjectdata,
            // 'mooeobjectdata' => $mooeobjectdata,
            // 'coobjectdata' => $coobjectdata,
            // 'psuacsdata' => $psuacsdata,
            // 'mooeuacsdata' => $mooeuacsdata,
            // 'couacsdata' => $couacsdata,
            // 'budgetdtdata' => $budgetdtdata,
            // 'projectdata' => $projectdata,
            // // 'programtitledata' => $programtitledata,
            'productsdata' => $productsdata,
            'prlistdata' => $prlistdata,
            'abstractlistdata' => $abstractlistdata,
        ]);
    }
    
    
}
