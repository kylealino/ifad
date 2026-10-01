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
                if (fods_has_access(\Config\AccessCodes::ADA_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One ADA, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::ADA_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myada->ada_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'ADA not found']);
                }
                return $this->response->setJSON($record);

            // The DV an ADA is prepared from: particulars, payee, account no., net amount
            case 'DV-LOAD':
                if (!fods_has_any_access([\Config\AccessCodes::ADA_ADD, \Config\AccessCodes::ADA_UPDATE])) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myada->dv_load($this->request->getGet('dvno'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'DV not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->myada->ada_save();
                return redirect()->to('myada?meaction=MAIN');
                break;

            case 'PRINT-ADA':
                if (!fods_has_access(\Config\AccessCodes::ADA_VIEW)) { return view('errors/html/access-restricted'); }
                return view('cashier/ada-pdf');
                break;

        }
    }


    private function loadMainView() {

        $adadata = $this->db->query("
        SELECT
            `recid`,
            `adano`,
            `dvno`,
            `particulars`,
            `payee_name`,
            `payee_accountno`,
            `ada_date`,
            `net_amount`,
            `is_approved`,
            `is_disapproved`,
            `added_at`,
            `added_by`
        FROM tbl_ada
        ORDER BY `recid` DESC
        ")->getResultArray();

        foreach ($adadata as &$row) {
            $row['status'] = MyLDDAPADA::statusOf($row);
            unset($row['is_approved'], $row['is_disapproved']);
        }
        unset($row);

        // DVs to pick from (newest first), with the ADA each already has
        $dvdata = $this->db->query("
        SELECT
            a.`dvno`,
            a.`payee_name`,
            a.`net_amount`,
            (SELECT GROUP_CONCAT(x.`adano` SEPARATOR ', ') FROM `tbl_ada` x WHERE x.`dvno` = a.`dvno`) AS on_ada
        FROM `tbl_disbursement_hd` a
        WHERE a.`dvno` <> ''
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        $certifiers = fn ($tag) => $this->db->query("SELECT `full_name`, `position` FROM myua_user WHERE ada_tag = ? AND is_active = 1 ORDER BY `full_name` ASC", [$tag])->getResultArray();

        return view('cashier/ada-main', [
            'adadata' => $adadata,
            'lookups' => [
                'dvs' => $dvdata,
                'certifya' => $certifiers('1'),
                'certifyb' => $certifiers('2'),
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::ADA_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::ADA_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that ADA directly
        ]);
    }


}
