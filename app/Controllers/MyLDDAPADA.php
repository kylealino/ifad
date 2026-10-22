<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyLDDAPADA extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mylddapada = model('App\Models\MyLDDAPADAModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::LDDAPADA_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One LDDAP-ADA with its DV lines, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::LDDAPADA_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mylddapada->lddapada_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'LDDAP-ADA not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->mylddapada->lddapada_save();
                return redirect()->to('mylddapada?meaction=MAIN');
                break;

            case 'PRINT-LDDAPADA':
                if (!fods_has_access(\Config\AccessCodes::LDDAPADA_VIEW)) { return view('errors/html/access-restricted'); }
                return view('cashier/lddapada-pdf');
                break;

            // A line for each picked DV (payee, account no., amounts, allotment class)
            case 'LOAD-DV':
                if (!fods_has_access(\Config\AccessCodes::LDDAPADA_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $dvno = $this->request->getPostGet('dvno');
                if (empty($dvno)) {
                    return $this->response->setJSON(['error' => 'No DV numbers selected']);
                }
                return $this->response->setJSON($this->mylddapada->dv_lines($dvno));

        }
    }


    private function loadMainView() {

        // LDDAP-ADAs with their DV count and amounts
        $lddapdata = $this->db->query("
        SELECT
            a.`recid`,
            a.`lddapadano`,
            a.`ckno`,
            a.`mds_branch`,
            a.`mds_accountno`,
            a.`lddapada_date`,
            a.`fund_cluster_code`,
            a.`funding_source`,
            a.`is_ci`,
            a.`is_approved`,
            a.`is_disapproved`,
            a.`added_at`,
            a.`added_by`,
            COUNT(d.`recid`) AS dv_count,
            COALESCE(SUM(d.`gross_amount`), 0) AS gross_amount,
            COALESCE(SUM(d.`net_amount`), 0) AS net_amount
        FROM `tbl_lddapada_hd` a
        LEFT JOIN `tbl_lddapada_dt` d ON d.`lddapada_id` = a.`recid`
        GROUP BY a.`recid`
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($lddapdata as &$row) {
            $row['status'] = self::statusOf($row);
            unset($row['is_approved'], $row['is_disapproved']);
        }
        unset($row);

        // DVs to pick from (newest first), with the LDDAP-ADA each is already on
        $dvdata = $this->db->query("
        SELECT
            a.`dvno`,
            a.`payee_name`,
            a.`net_amount`,
            (SELECT GROUP_CONCAT(DISTINCT h.`lddapadano` SEPARATOR ', ')
               FROM `tbl_lddapada_dvno` v JOIN `tbl_lddapada_hd` h ON h.`recid` = v.`lddapada_id`
              WHERE v.`dvno` = a.`dvno`) AS on_lddapada
        FROM `tbl_disbursement_hd` a
        WHERE a.`dvno` <> ''
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        // The four printed signatures and the names offered for them
        $signatories = [];
        foreach (\App\Models\MyLDDAPADAModel::SIGNATORIES as $name => $position) {
            $signatories[] = ['full_name' => $name, 'position' => $position];
        }
        $signatures = [];
        foreach (\App\Models\MyLDDAPADAModel::SIGNATURES as $field => [$label, $default]) {
            $signatures[] = ['field' => $field, 'position_field' => \App\Models\MyLDDAPADAModel::position_col($field), 'label' => $label, 'default' => $default];
        }

        return view('cashier/lddapada-main', [
            'lddapdata' => $lddapdata,
            'lookups' => [
                'dvs' => $dvdata,
                'signatories' => $signatories,
                'signatures' => $signatures,
                'fund_clusters' => ['01', '07'],
                // MDS sub-account per fund cluster, as on the previous form
                'mds_accounts' => ['01' => '2182-9001-36', '07' => '2182-9006-16'],
                'mds_branch' => 'Land Bank of the Philippines - DOST Branch',
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::LDDAPADA_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::LDDAPADA_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that LDDAP-ADA directly
        ]);
    }

    // Single approval step
    public static function statusOf(array $hd) {
        if ($hd['is_disapproved'] == '1') {
            return 'disapproved';
        }
        if ($hd['is_approved'] == '1') {
            return 'approved';
        }
        return 'pending';
    }


}
