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
                if (fods_has_access(\Config\AccessCodes::UACS_VIEW)) {
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
        ORDER BY `uacs_code` ASC
        ");
        $uacsdata = $uacsquery->getResultArray();

    
        $byClass = array_count_values(array_column($uacsdata, 'allotment_class'));

        return view('uacs/uacs-main', [
            'uacsdata' => $uacsdata,
            'byClass' => $byClass,
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::UACS_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::UACS_UPDATE),
                'del' => false,
            ],
        ]);
    }
    
    
}
