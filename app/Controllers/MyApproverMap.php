<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyApproverMapModel;

class MyApproverMap extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myapprovermap = model('App\Models\MyApproverMapModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::APPROVERMAP_VIEW)) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }

            case 'MAIN-SAVE':
                $this->myapprovermap->approvermap_save();
                return redirect()->to('myapprovermap?meaction=MAIN');
                break;

            case 'MAIN-DELETE':
                $this->myapprovermap->approvermap_delete();
                return redirect()->to('myapprovermap?meaction=MAIN');
                break;

            default:
                return redirect()->to('myapprovermap?meaction=MAIN');
        }
    }


    private function loadMainView() {

        $results = $this->myapprovermap->list_rows();

        // Per person (matched on the name) and per box
        $people = [];
        $covered = [];
        foreach ($results as $r) {
            $key = mb_strtolower(trim($r['full_name']));
            $people[$key] = ($people[$key] ?? 0) || (int) $r['is_active'] === 1;
            if ((int) $r['is_active'] === 1) {
                $covered[$r['module'] . '|' . $r['box']] = true;
            }
        }
        $boxes = 0;
        foreach (MyApproverMapModel::BOXES as $m) {
            $boxes += count($m['boxes']);
        }
        $stats = [
            'approvers' => count(array_filter($people)),
            'boxes' => count($covered),
            'box_total' => $boxes,
            'empty' => $boxes - count($covered),
            'inactive' => count($people) - count(array_filter($people)),
        ];

        // Active users to pick from (their position fills in)
        $users = $this->db->query("
            SELECT DISTINCT `full_name`, `position`
            FROM myua_user
            WHERE `is_active` = 1 AND TRIM(`full_name`) <> ''
            ORDER BY `full_name` ASC
        ")->getResultArray();

        return view('approver-map/approver-map-main', [
            'results' => $results,
            'boxes' => MyApproverMapModel::BOXES,
            'users' => $users,
            'stats' => $stats,
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::APPROVERMAP_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::APPROVERMAP_UPDATE),
                'del' => fods_has_access(\Config\AccessCodes::APPROVERMAP_DELETE),
            ],
        ]);
    }


}
