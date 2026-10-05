<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyPPMP extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myppmp = model('App\Models\MyPPMPModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::PPMP_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One PPMP with its items, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::PPMP_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myppmp->ppmp_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'PPMP not found']);
                }
                return $this->response->setJSON($record);

            case 'PPMP-SAVE':
                $this->myppmp->ppmp_save();
                return redirect()->to('myppmp?meaction=MAIN');
                break;

            case 'PPMP-PRINT':
                if (!fods_has_access(\Config\AccessCodes::PPMP_VIEW)) { return view('errors/html/access-restricted'); }
                return view('procurement/ppmp/ppmp-pdf');
                break;
        }
    }


    private function loadMainView() {

        $ppmphddata = $this->db->query("
        SELECT
            a.`recid`,
            a.`ppmpno`,
            a.`end_user`,
            a.`fiscal_year`,
            a.`project_title`,
            a.`responsibility_code`,
            a.`is_indicative`,
            a.`is_final`,
            a.`added_at`,
            a.`added_by`,
            COALESCE(d.n,0) AS items,
            COALESCE(d.amount,0) AS amount
        FROM tbl_ppmp_hd a
        LEFT JOIN (SELECT ppmp_id, COUNT(*) n, SUM(estimated_budget) amount FROM tbl_ppmp_dt GROUP BY ppmp_id) d ON d.ppmp_id = a.recid
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        // One status per PPMP: final, indicative, or not marked yet
        foreach ($ppmphddata as &$row) {
            $row['status'] = self::statusOf($row);
            unset($row['is_indicative'], $row['is_final']);
        }
        unset($row);


        //item lookup: picking one fills the unit and the unit cost
        $productsdata = $this->db->query("
        SELECT
            `product_desc` AS value,
            `uom`,
            `price`
        FROM `mst_products`
        WHERE `product_desc` <> ''
        ORDER BY `product_desc` ASC
        ")->getResultArray();

        //responsibility code -> project title lookup
        $projectsdata = $this->db->query("
        SELECT
            `responsibility_code`,
            `project_title`
        FROM `tbl_reference_project`
        WHERE `fundcluster_id` = '1'
        ORDER BY `responsibility_code` ASC
        ")->getResultArray();

        $signatories = array_column(
            $this->db->query("SELECT `full_name` FROM myua_user WHERE `is_ppmp_signatory` = 1 AND `is_active` = 1 ORDER BY `full_name` ASC")->getResultArray(),
            'full_name'
        );

        return view('procurement/ppmp/ppmp-main', [
            'ppmphddata' => $ppmphddata,
            'lookups' => [
                'products' => $productsdata,
                'projects' => $projectsdata,
                'signatories' => $signatories,
                'fiscal_years' => range((int) date('Y') + 1, 2024),
                'item_types' => ['Goods', 'Infrastructure', 'Consulting Services'],
                'units' => ['pc', 'btl', 'pack', 'box', 'cyl', 'crtg'],
                'modes' => ['SVP', 'Direct Acquisition', 'Direct Contracting', 'Public Bidding'],
                'preproc' => ['Yes', 'No'],
                'funding_sources' => ['General Fund', 'Trust Fund', 'Project Fund (Externally Funded)'],
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::PPMP_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::PPMP_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that PPMP directly
        ]);
    }

    // final: marked Final (wins when both switches are on)
    // indicative: marked Indicative only
    // unmarked: neither switch is on yet
    public static function statusOf(array $hd) {
        if ($hd['is_final'] == '1') {
            return 'final';
        }
        if ($hd['is_indicative'] == '1') {
            return 'indicative';
        }
        return 'unmarked';
    }


}
