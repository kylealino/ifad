<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyHistoryModel;

class MyHistory extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myhistory = model('App\Models\MyHistoryModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    // A record's history as JSON (the History window on each module):
    // myhistory?module=ors&recid=12, only for users who can open that module
    public function index() {
        $module = (string) $this->request->getGet('module');
        $recid = trim((string) $this->request->getGet('recid'));

        $m = MyHistoryModel::MODULES[$module] ?? null;
        if ($m === null || $recid === '') {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No history for that record']);
        }
        if (!fods_has_any_access($m['view'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        // an approver mapping is logged per person
        $id = $module === 'approvermap' ? MyHistoryModel::person_id($recid) : $recid;

        return $this->response->setJSON([
            'label' => $m['label'],
            'history' => $this->myhistory->for_record($module, $id),
        ]);
    }


}
