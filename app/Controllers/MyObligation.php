<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * ORS / BURS: one entry page for both obligation forms.
 *
 * The list shows the ORS and BURS records the user may view, and the editor
 * takes either form (picked on a new record, fixed once saved). Each form keeps
 * its own tables, numbering, access codes and endpoints: the page loads, saves,
 * certifies and prints through myors / myburs (MyOrs, MyBurs), so their rules
 * and access checks apply unchanged. Opening myors / myburs redirects here.
 */
class MyObligation extends BaseController
{
    // The two forms. Table prefixes and columns come only from this list.
    const TYPES = [
        'ors' => [
            'hd' => 'tbl_ors_hd', 'dt' => 'tbl_ors_', 'date' => 'ors_date', 'fundcluster_id' => '1',
            'view' => [\Config\AccessCodes::ORS_VIEW, \Config\AccessCodes::ORSAPPROVAL_VIEW],
            'add' => \Config\AccessCodes::ORS_ADD, 'edit' => \Config\AccessCodes::ORS_UPDATE, 'approve' => \Config\AccessCodes::ORSAPPROVAL_APPROVE,
        ],
        'burs' => [
            'hd' => 'tbl_burs_hd', 'dt' => 'tbl_burs_', 'date' => 'burs_date', 'fundcluster_id' => '2',
            'view' => [\Config\AccessCodes::BURS_VIEW, \Config\AccessCodes::BURSAPPROVAL_VIEW],
            'add' => \Config\AccessCodes::BURS_ADD, 'edit' => \Config\AccessCodes::BURS_UPDATE, 'approve' => \Config\AccessCodes::BURSAPPROVAL_APPROVE,
        ],
    ];

    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        // Forms this user may open
        $types = array_keys(array_filter(self::TYPES, fn ($t) => fods_has_any_access($t['view'])));
        if (!$types) {
            return view('errors/html/access-restricted');
        }
        return $this->loadMainView($types);
    }

    // Link to the page for one record (old myors / myburs links redirect here)
    public static function link(string $type, $recid = ''): string {
        $recid = (string) $recid;
        return 'myobligation?meaction=MAIN&type=' . $type . ($recid !== '' ? '&recid=' . urlencode($recid) : '');
    }

    private function listOf(string $type): array {
        $t = self::TYPES[$type];
        $hd = $t['hd'];
        $dt = $t['dt'];
        $date = $t['date'];

        $rows = $this->db->query("
        SELECT
            a.recid,
            a.serialno,
            a.particulars,
            a.funding_source,
            a.fund_cluster_code,
            a.payee_name,
            a.`{$date}` AS doc_date,
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
        FROM `{$hd}` a
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM `{$dt}direct_ps_dt` GROUP BY project_id) ps ON ps.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM `{$dt}indirect_ps_dt` GROUP BY project_id) ips ON ips.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM `{$dt}direct_mooe_dt` GROUP BY project_id) m ON m.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM `{$dt}indirect_mooe_dt` GROUP BY project_id) im ON im.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM `{$dt}direct_co_dt` GROUP BY project_id) co ON co.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM `{$dt}indirect_co_dt` GROUP BY project_id) ico ON ico.project_id = a.recid
        ORDER BY a.recid DESC
        ")->getResultArray();

        // One status per record (Box A certifies first, then Box B; same rule for both forms)
        foreach ($rows as &$row) {
            $row['type'] = $type;
            $row['status'] = MyOrs::statusOf($row);
            unset($row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb']);
        }
        unset($row);
        return $rows;
    }

    private function loadMainView(array $types) {

        $rows = [];
        $projects = [];
        $certifya = [];
        $certifyb = [];
        $perms = [];
        foreach ($types as $type) {
            $t = self::TYPES[$type];
            $rows = array_merge($rows, $this->listOf($type));

            // ORS takes FC 01 projects, BURS FC 07
            $projects[$type] = $this->db->query("
            SELECT
                a.`responsibility_code`,
                a.`project_title`,
                a.`mfopaps_code`
            FROM `tbl_reference_project` a
            WHERE a.`fundcluster_id` = ?
            ORDER BY a.`project_title` ASC
            ", [$t['fundcluster_id']])->getResultArray();

            // Signatories offered on each box, from Approver Mapping
            $certifya[$type] = \App\Models\MyApproverMapModel::options($type, 'a');
            $certifyb[$type] = \App\Models\MyApproverMapModel::options($type, 'b');

            $perms[$type] = [
                'add' => fods_has_access($t['add']),
                'edit' => fods_has_access($t['edit']),
                'approve' => fods_has_access($t['approve']),
            ];
        }

        // Newest first across both forms
        usort($rows, fn ($a, $b) => strcmp((string) $b['added_at'], (string) $a['added_at']));

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

        $uacsLookup = function ($class) {
            return array_map(fn ($r) => ['label' => $r['sub_object_code'], 'value' => $r['sub_object_code'], 'uacs_code' => $r['uacs_code']],
                $this->db->query("SELECT `sub_object_code`, `uacs_code` FROM mst_uacs WHERE allotment_class = ? ORDER BY TRIM(sub_object_code) ASC", [$class])->getResultArray());
        };

        $openType = (string) $this->request->getGet('type');
        $openRecid = (string) $this->request->getGet('recid');

        return view('obligation/obligation-main', [
            'rows' => $rows,
            'types' => $types,
            'lookups' => [
                'payees' => $payeedata,
                'projects' => $projects,
                'psuacs' => $uacsLookup('Personnel Services'),
                'mooeuacs' => $uacsLookup('Maintenance and Other Operating Expenses'),
                'couacs' => $uacsLookup('Capital Outlay'),
                'certifya' => $certifya,
                'certifyb' => $certifyb,
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
            'perms' => $perms,
            // ?type=&recid= opens that record directly
            'open' => in_array($openType, $types, true) && $openRecid !== '' ? ['type' => $openType, 'recid' => $openRecid] : null,
            // ?type= alone shows that form's records first
            'show' => in_array($openType, $types, true) ? $openType : '',
        ]);
    }
}
