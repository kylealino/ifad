<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyADA extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myada = model('App\Models\MyADAModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '13001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
            case 'MAIN-SAVE': 
                $this->myada->ada_save();
                return redirect()->to('myada?meaction=MAIN');
                break;

            case 'PRINT-ADA': 
                return view('cashier/ada-pdf');
                break;
            
        }
    }
    

    private function loadMainView() {

        //dvno lookup
        $dvnoquery = $this->db->query("
        SELECT
        *
        FROM
            `tbl_disbursement_hd`
        GROUP BY
            `serialno`
        ORDER BY `recid` DESC
        ");
        $dvdata = $dvnoquery->getResultArray();

        //adano lookup
        $adaquery = $this->db->query("
        SELECT
            recid,
            adano,
            dvno,
            particulars,
            payee_name,
            payee_accountno,
            ada_date,
            net_amount
        FROM tbl_ada
        ORDER BY `recid` DESC
        ");
        $adadata = $adaquery->getResultArray();

        $certifyaquery = $this->db->query("SELECT * FROM myua_user WHERE ada_tag = '1' ORDER BY recid DESC");
        $certifyadata = $certifyaquery->getResultArray();

        $certifybquery = $this->db->query("SELECT * FROM myua_user WHERE ada_tag = '2' ORDER BY recid DESC");
        $certifybdata = $certifybquery->getResultArray();

        return view('cashier/ada-main', [
            'dvdata' => $dvdata,
            'adadata' => $adadata,
            'certifyadata' => $certifyadata,
            'certifybdata' => $certifybdata,
        ]);
    }
    
    
}
