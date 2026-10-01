<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyProducts extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myproducts = model('App\Models\MyProductsModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                if (fods_has_access(\Config\AccessCodes::SUPPLIES_VIEW)) {
                    return $this->loadProductsView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    

            case 'PRODUCTS-SAVE': 
                $this->myproducts->products_save();
                return redirect()->to('mysaobrpt?meaction=MAIN');
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

        $productsdata = $this->db->query("
            SELECT `recid`, `product_code`, `product_desc`, `uom`, `price`, `quantity`, `remarks`
            FROM `mst_products`
            ORDER BY `product_desc` ASC
        ")->getResultArray();

        $uoms = array_column($this->db->query("
            SELECT DISTINCT `uom` FROM `mst_products` WHERE `uom` <> '' ORDER BY `uom`
        ")->getResultArray(), 'uom');

        $stats = [
            'total' => count($productsdata),
            'out_of_stock' => count(array_filter($productsdata, fn ($r) => (float) $r['quantity'] <= 0)),
            'uoms' => count($uoms),
            'value' => array_sum(array_map(fn ($r) => (float) $r['price'] * max(0, (float) $r['quantity']), $productsdata)),
        ];

        return view('products/products-main', [
            'productsdata' => $productsdata,
            'uoms' => $uoms,
            'stats' => $stats,
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::SUPPLIES_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::SUPPLIES_UPDATE),
                'del' => false,
            ],
        ]);
    }
}
