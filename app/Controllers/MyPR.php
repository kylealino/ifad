<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyPR extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mypr = model('App\Models\MyPRModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'PR-MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '8001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadPRView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
			case 'PR-PRINT':
				return view('procurement/pr/pr-pdf');
				break;

            case 'RFQ-PRINT':
				return view('procurement/pr/rfq-pdf');
				break;

            case 'PR-SAVE': 
                $this->mypr->pr_save();
                return redirect()->to('myprocurement?meaction=PR-MAIN');
                break;

            case 'PR-RFQ-SAVE': 
                $this->mypr->pr_rfq_save();
                return redirect()->to('myprocurement?meaction=PR-MAIN');
                break;

            case 'LOAD-PPMP':

                $ppmpnos = $this->request->getPost('ppmpno');

                if (empty($ppmpnos)) {
                    echo json_encode([]);
                    exit;
                }

                $in = "'" . implode("','", $ppmpnos) . "'";

                $query = $this->db->query("
                    SELECT 
                        item_desc,
                        size AS unit,
                        SUM(quantity) AS quantity,
                        SUM(quantity * unit_cost) / SUM(quantity) AS unit_cost,
                        SUM(quantity * unit_cost) AS total_cost
                    FROM tbl_ppmp_dt
                    WHERE ppmpno IN ($in)
                    GROUP BY item_desc, size
                    ORDER BY item_desc
                ");

                echo json_encode($query->getResultArray());
                exit;
                break;
        }
    }
    

    private function loadPRView() {

        $prhddataquery = $this->db->query("
            SELECT
                `recid`,
                `entity_name`,
                `office`,
                `prno`,
                `responsibility_code`,
                `fund_cluster`,
                `pr_date`,
                `end_user`,
                `position`,
                `charge_to`,
                `purpose`,
                `estimated_cost`,
                `added_by`,
                `added_at`
            FROM
                `tbl_pr_hd`
            ORDER BY
                `recid` DESC
        ");
        $prhddata = $prhddataquery->getResultArray();

        $productsdataquery = $this->db->query("
            SELECT
                `product_code`,
                `product_desc`,
                `uom`,
                `price`,
                `quantity`,
                `remarks`
            FROM
                `mst_products`
            ORDER BY
                `product_desc` ASC
        ");
        $productsdata = $productsdataquery->getResultArray();


        return view('procurement/pr/pr-main', [
            'prhddata' => $prhddata,
            'productsdata' => $productsdata
        ]);
    }
    
    
}
