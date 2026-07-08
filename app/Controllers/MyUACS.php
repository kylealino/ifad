<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyUACS extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myuacs = model('App\Models\MyUACSModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '9001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
            case 'MAIN-SAVE': 
                $this->myuacs->uacs_save();
                return redirect()->to('myuacs?meaction=MAIN');
                break;

            // case 'MAIN-DELETE': 
            //     $this->myuacs->projects_delete();
            //     return redirect()->to('myuacs?meaction=MAIN');
            //     break;
        }
    }
    

    private function loadMainView() {

        $uacsquery = $this->db->query("
        SELECT
            `recid`,
            `allotment_class`,
            `object_code`,
            `sub_object_code`,
            `uacs_code`,
            `added_at`,
            `added_by`
        FROM
            `mst_uacs`
        ORDER BY recid DESC
        ");
        $uacsdata = $uacsquery->getResultArray();

    
        return view('uacs/uacs-main', [
            'uacsdata' => $uacsdata,
        ]);
    }
    
    
}
