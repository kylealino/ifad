<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyBurs extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myburs = model('App\Models\MyBursModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            // The list and editor are on the ORS / BURS page (MyObligation); old links open the record there
            case 'MAIN':
                if (fods_has_any_access([\Config\AccessCodes::BURS_VIEW, \Config\AccessCodes::BURSAPPROVAL_VIEW])) {
                    return redirect()->to(MyObligation::link('burs', (string) $this->request->getGet('recid')));
                }else {
                    return view('errors/html/access-restricted');
                }

            // One BURS with its line items, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_any_access([\Config\AccessCodes::BURS_VIEW, \Config\AccessCodes::BURSAPPROVAL_VIEW])) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myburs->burs_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'BURS not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->myburs->burs_save();
                return redirect()->to('myburs?meaction=MAIN');
                break;

            // Certification now happens in BURS Approval (MyBursApproval);
            // these stay so older links keep working
            case 'MAIN-APPROVE-A':
                $this->myburs->burs_certifya_approve();
                return redirect()->to('mybursapproval');
                break;

            case 'MAIN-DISAPPROVE-A':
                $this->myburs->burs_certifya_disapprove();
                return redirect()->to('mybursapproval');
                break;

            case 'MAIN-APPROVE-B':
                $this->myburs->burs_certifyb_approve();
                return redirect()->to('mybursapproval');
                break;

            case 'MAIN-DISAPPROVE-B':
                $this->myburs->burs_certifyb_disapprove();
                return redirect()->to('mybursapproval');
                break;

            case 'PRINT-BURS':
                if (!fods_has_any_access([\Config\AccessCodes::BURS_VIEW, \Config\AccessCodes::BURSAPPROVAL_VIEW])) { return view('errors/html/access-restricted'); }
                return view('burs/burs-pdf');
                break;

            case 'PRINT-DISBURSEMENT':
                if (!fods_has_any_access([\Config\AccessCodes::BURS_VIEW, \Config\AccessCodes::BURSAPPROVAL_VIEW])) { return view('errors/html/access-restricted'); }
                return view('burs/burs-disbursement-pdf');
                break;

        }
    }


    // pending_a: waiting for the requesting office (Box A)
    // pending_b: Box A certified, waiting for budget (Box B)
    public static function statusOf(array $hd) {
        if ($hd['is_disapproved_certa'] == '1' || $hd['is_disapproved_certb'] == '1') {
            return 'disapproved';
        }
        if ($hd['is_approved_certb'] == '1') {
            return 'approved';
        }
        if ($hd['is_approved_certa'] == '1') {
            return 'pending_b';
        }
        return 'pending_a';
    }


}
