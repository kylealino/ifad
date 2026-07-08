<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyPPMP extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myppmp = model('App\Models\MyPPMPModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '8001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadPPMPView();
                }else {
                    return view('errors/html/access-restricted');
                }
                break;

            case 'PPMP-SAVE': 
                $this->myppmp->ppmp_save();
                return redirect()->to('myppmp?meaction=MAIN');
                break;
            
            case 'PPMP-PRINT':
				return view('procurement/ppmp/ppmp-pdf');
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
    

    private function loadPPMPView() {

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

        $projectsdataquery = $this->db->query("
            SELECT
                `recid`,
                `project_title`,
                `responsibility_code`
            FROM
                `tbl_reference_project`
            WHERE
                `fundcluster_id` = '1'
            ORDER BY
                `recid` DESC
            
        ");
        $projectsdata = $projectsdataquery->getResultArray();

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

        $ppmplistdataquery = $this->db->query("
            SELECT
                `recid`,
                `ppmpno`,
                `end_user`,
                `fiscal_year`,
                `project_title`,
                `responsibility_code`
            FROM
                `tbl_ppmp_hd`
            ORDER BY
                `recid` DESC
        ");
        $ppmplistdata = $ppmplistdataquery->getResultArray();

        $signatoriesdataquery = $this->db->query("
            SELECT
                `full_name`
            FROM
                `myua_user`
            WHERE
                `is_ppmp_signatory` = 1
            ORDER BY
                `recid` DESC
        ");
        $signatoriesdata = $signatoriesdataquery->getResultArray();

        return view('procurement/ppmp/ppmp-main', [
            'productsdata' => $productsdata,
            'projectsdata' => $projectsdata,
            'prlistdata' => $prlistdata,
            'ppmplistdata' => $ppmplistdata,
            'abstractlistdata' => $abstractlistdata,
            'signatoriesdata' => $signatoriesdata,
        ]);
    }
    
    
}
