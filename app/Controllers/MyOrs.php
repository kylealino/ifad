<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyOrs extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myors = model('App\Models\MyOrsModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            // The list and editor are on the ORS / BURS page (MyObligation); old links open the record there
            case 'MAIN':
                if (fods_has_any_access([\Config\AccessCodes::ORS_VIEW, \Config\AccessCodes::ORSAPPROVAL_VIEW])) {
                    return redirect()->to(MyObligation::link('ors', (string) $this->request->getGet('recid')));
                }else {
                    return view('errors/html/access-restricted');
                }

            // One ORS with its line items, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_any_access([\Config\AccessCodes::ORS_VIEW, \Config\AccessCodes::ORSAPPROVAL_VIEW])) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myors->ors_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'ORS not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->myors->ors_save();
                return redirect()->to('myors?meaction=MAIN');
                break;

            // Certification now happens in ORS Approval (MyOrsApproval);
            // these stay so older links keep working
            case 'MAIN-APPROVE-A':
                $this->myors->ors_certifya_approve();
                return redirect()->to('myorsapproval');
                break;

            case 'MAIN-DISAPPROVE-A':
                $this->myors->ors_certifya_disapprove();
                return redirect()->to('myorsapproval');
                break;

            case 'MAIN-APPROVE-B':
                $this->myors->ors_certifyb_approve();
                return redirect()->to('myorsapproval');
                break;

            case 'MAIN-DISAPPROVE-B':
                $this->myors->ors_certifyb_disapprove();
                return redirect()->to('myorsapproval');
                break;

            case 'PRINT-ORS':
                if (!fods_has_any_access([\Config\AccessCodes::ORS_VIEW, \Config\AccessCodes::ORSAPPROVAL_VIEW])) { return view('errors/html/access-restricted'); }
                return view('ors/ors-pdf');
                break;

            case 'PRINT-DISBURSEMENT':
                if (!fods_has_any_access([\Config\AccessCodes::ORS_VIEW, \Config\AccessCodes::ORSAPPROVAL_VIEW])) { return view('errors/html/access-restricted'); }
                return view('ors/ors-disbursement-pdf');
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
