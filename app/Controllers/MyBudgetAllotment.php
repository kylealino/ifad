<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyBudgetAllotment extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::BUDGET_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

                break;

            // One allotment with its line items, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::BUDGET_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mybudgetallotment->budget_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Budget allotment not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->mybudgetallotment->budget_save();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;

             case 'MAIN-APPROVE':
                $this->mybudgetallotment->budget_approve();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;

            case 'MAIN-DISAPPROVE':
                $this->mybudgetallotment->budget_disapprove();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;

            case 'MAIN-UPLOAD':
                fods_require_access(\Config\AccessCodes::BUDGET_UPDATE, 'Uploading');
                $this->mybudgetallotment->budget_attachment_upload();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;

            case 'PRINT-LIB':
                if (!fods_has_access(\Config\AccessCodes::BUDGET_VIEW)) { return view('errors/html/access-restricted'); }
                return view('budget/budget-lib-print');
                break;

        }
    }


    private function loadMainView() {

        $budgetdtdata = $this->db->query("
        SELECT
            a.recid,
            a.trxno,
            a.project_title,
            a.responsibility_code,
            a.fund_cluster_code,
            a.division_name,
            a.tagging,
            a.is_pending,
            a.is_approved,
            a.is_disapproved,
            a.added_at,
            a.added_by,

            IFNULL(dps.total,0) +
            IFNULL(ips.total,0) +
            IFNULL(dm.total,0) +
            IFNULL(im.total,0) +
            IFNULL(ico.total,0) +
            IFNULL(dco.total,0) AS approved_budget

        FROM tbl_budget_hd a
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_direct_ps_dt GROUP BY project_id) dps ON dps.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_indirect_ps_dt GROUP BY project_id) ips ON ips.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_direct_mooe_dt GROUP BY project_id) dm ON dm.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_indirect_mooe_dt GROUP BY project_id) im ON im.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_indirect_co_dt GROUP BY project_id) ico ON ico.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(approved_budget) total FROM tbl_budget_direct_co_dt GROUP BY project_id) dco ON dco.project_id = a.recid
        ORDER BY a.added_at DESC
        ")->getResultArray();

        // One status per allotment, used by the list, cards and filters
        foreach ($budgetdtdata as &$row) {
            if ($row['is_disapproved'] == '1') {
                $row['status'] = 'disapproved';
            } elseif ($row['is_approved'] == '1') {
                $row['status'] = 'approved';
            } elseif ($row['tagging'] === 'For Approval') {
                $row['status'] = 'pending';
            } else {
                $row['status'] = 'draft';
            }
        }
        unset($row);


        //reference/project title lookup
        $projectdata = $this->db->query("
        SELECT
            b.`fund_cluster_code`,
            c.`division_name`,
            a.`responsibility_code`,
            a.`project_leader`,
            a.`project_title`
        FROM `tbl_reference_project` a
        JOIN `tbl_fundcluster` b ON a.fundcluster_id = b.`recid`
        JOIN `tbl_division` c ON a.`division_id` = c.recid
        ORDER BY a.`project_title` ASC
        ")->getResultArray();

        $uacsLookup = function ($class) {
            return array_map(fn ($r) => ['label' => $r['sub_object_code'], 'value' => $r['sub_object_code'], 'uacs_code' => $r['uacs_code']],
                $this->db->query("SELECT `sub_object_code`, `uacs_code` FROM mst_uacs WHERE allotment_class = ? ORDER BY TRIM(sub_object_code) ASC", [$class])->getResultArray());
        };

        return view('budget/budget-allotment-main', [
            'budgetdtdata' => $budgetdtdata,
            'projectdata' => $projectdata,
            'psuacs' => $uacsLookup('Personnel Services'),
            'mooeuacs' => $uacsLookup('Maintenance and Other Operating Expenses'),
            'programs' => [
                'General Administration and Support Service',
                'Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition',
                "Expanding the FNRI's Nutrigenomics Laboratory: Towards Establishment of a World Class Philippines Nutrigenomics Center",
                'Nutritional Assessment and Monitoring on Food and Nutrition',
                'Expanded National Nutrition Survey',
                'Technical Services on Food and Nutrition',
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::BUDGET_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::BUDGET_UPDATE),
                'approve' => fods_has_access(\Config\AccessCodes::BUDGETAPPROVAL_APPROVE),
            ],
            // Deep link from Budget Approval: open this allotment on load
            'open' => [
                'recid' => (string) $this->request->getGet('recid'),
                'action' => (string) $this->request->getGet('action'),
            ],
        ]);
    }


}
