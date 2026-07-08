<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyPayee extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mypayee = model('App\Models\MyPayeeModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '5001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
            case 'MAIN-SAVE': 
                $this->mypayee->payee_save();
                return redirect()->to('mypayee?meaction=MAIN');
                break;

            case 'MAIN-DELETE': 
                $this->mypayee->payee_delete();
                return redirect()->to('mypayee?meaction=MAIN');
                break;
        }
    }
    

    private function loadMainView() {

        $query = $this->db->query("SELECT * FROM tbl_payee");
        $results = $query->getResultArray();
    
        return view('payee/payee-main', [
            'results' => $results
        ]);
    }
    
    
}
