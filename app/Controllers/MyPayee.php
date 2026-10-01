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
                if (fods_has_access(\Config\AccessCodes::PAYEE_VIEW)) {
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

        $query = $this->db->query("
            SELECT `recid`, `payee_name`, `payee_account_num`, `payee_office`, `payee_tin`,
                   `contact_no`, `payee_address`, `disb_method`, `currency`
            FROM tbl_payee
            ORDER BY `payee_name` ASC
        ");
        $results = $query->getResultArray();

        // Standard methods first, then any other value already used in the data
        $methods = ['LDDAP-ADA', 'LDDAP-IC', 'ADA', 'CHECK'];
        foreach ($this->db->query("SELECT DISTINCT `disb_method` FROM tbl_payee WHERE `disb_method` <> '' ORDER BY `disb_method`")->getResultArray() as $row) {
            if (!in_array($row['disb_method'], $methods, true)) {
                $methods[] = $row['disb_method'];
            }
        }

        $blank = fn ($v) => trim((string) $v) === '';
        $stats = [
            'total' => count($results),
            'no_account' => count(array_filter($results, fn ($r) => $blank($r['payee_account_num']))),
            'no_tin' => count(array_filter($results, fn ($r) => $blank($r['payee_tin']))),
            'lddap' => count(array_filter($results, fn ($r) => $r['disb_method'] === 'LDDAP-ADA')),
        ];

        return view('payee/payee-main', [
            'results' => $results,
            'methods' => $methods,
            'stats' => $stats,
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::PAYEE_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::PAYEE_UPDATE),
                'del' => fods_has_access(\Config\AccessCodes::PAYEE_DELETE),
            ],
        ]);
    }
    
    
}
