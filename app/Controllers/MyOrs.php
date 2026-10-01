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
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::ORS_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One ORS with its line items, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::ORS_VIEW)) {
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
                if (!fods_has_access(\Config\AccessCodes::ORS_VIEW)) { return view('errors/html/access-restricted'); }
                return view('ors/ors-pdf');
                break;

            case 'PRINT-DISBURSEMENT':
                if (!fods_has_access(\Config\AccessCodes::ORS_VIEW)) { return view('errors/html/access-restricted'); }
                return view('ors/ors-disbursement-pdf');
                break;

        }
    }


    private function loadMainView() {

        $orshddata = $this->db->query("
        SELECT
            a.recid,
            a.serialno,
            a.particulars,
            a.funding_source,
            a.fund_cluster_code,
            a.payee_name,
            a.ors_date,
            a.dvno,
            a.is_approved_certa,
            a.is_disapproved_certa,
            a.is_approved_certb,
            a.is_disapproved_certb,
            a.added_at,
            a.added_by,
            COALESCE(ps.amount,0)
        + COALESCE(ips.amount,0)
        + COALESCE(m.amount,0)
        + COALESCE(im.amount,0)
        + COALESCE(co.amount,0)
        + COALESCE(ico.amount,0) AS amount
        FROM tbl_ors_hd a
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_ors_direct_ps_dt GROUP BY project_id) ps ON ps.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_ors_indirect_ps_dt GROUP BY project_id) ips ON ips.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_ors_direct_mooe_dt GROUP BY project_id) m ON m.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_ors_indirect_mooe_dt GROUP BY project_id) im ON im.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_ors_direct_co_dt GROUP BY project_id) co ON co.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_ors_indirect_co_dt GROUP BY project_id) ico ON ico.project_id = a.recid
        ORDER BY a.recid DESC
        ")->getResultArray();

        // One status per ORS (Box A certifies first, then Box B)
        foreach ($orshddata as &$row) {
            $row['status'] = self::statusOf($row);
            unset($row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb']);
        }
        unset($row);


        //payee lookup
        $payeedata = $this->db->query("
        SELECT
            `payee_name` AS value,
            `payee_office` AS office,
            `payee_address` AS address
        FROM `tbl_payee`
        WHERE `payee_name` <> ''
        ORDER BY `payee_name` ASC
        ")->getResultArray();

        //reference/project title lookup
        $projectdata = $this->db->query("
        SELECT
            a.`responsibility_code`,
            a.`project_title`,
            a.`mfopaps_code`
        FROM `tbl_reference_project` a
        WHERE a.`fundcluster_id` = '1'
        ORDER BY a.`project_title` ASC
        ")->getResultArray();

        $uacsLookup = function ($class) {
            return array_map(fn ($r) => ['label' => $r['sub_object_code'], 'value' => $r['sub_object_code'], 'uacs_code' => $r['uacs_code']],
                $this->db->query("SELECT `sub_object_code`, `uacs_code` FROM mst_uacs WHERE allotment_class = ? ORDER BY TRIM(sub_object_code) ASC", [$class])->getResultArray());
        };

        $certifiers = fn ($tag) => $this->db->query("SELECT `full_name`, `position` FROM myua_user WHERE cert_tag = ? AND is_active = 1 ORDER BY `full_name` ASC", [$tag])->getResultArray();

        return view('ors/ors-main', [
            'orshddata' => $orshddata,
            'lookups' => [
                'payees' => $payeedata,
                'projects' => $projectdata,
                'psuacs' => $uacsLookup('Personnel Services'),
                'mooeuacs' => $uacsLookup('Maintenance and Other Operating Expenses'),
                'couacs' => $uacsLookup('Capital Outlay'),
                'certifya' => $certifiers('1'),
                'certifyb' => $certifiers('2'),
                'programs' => [
                    'General Administration and Support Service',
                    'Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition',
                    "Expanding the FNRI's Nutrigenomics Laboratory: Towards Establishment of a World Class Philippines Nutrigenomics Center",
                    'Nutritional Assessment and Monitoring on Food and Nutrition',
                    'Expanded National Nutrition Survey',
                    'Technical Services on Food and Nutrition',
                ],
                'funding_sources' => ['101101', '102101', '102406', '102407', '104102', '105462', '308601', '308602', '308603'],
                'fund_clusters' => ['01', '07'],
                'categories' => ['Goods', 'Services', 'Manual', 'Payroll', 'Income Tax'],
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::ORS_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::ORS_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that ORS directly
        ]);
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
