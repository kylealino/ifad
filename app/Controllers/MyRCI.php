<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyRCI extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myrci = model('App\Models\MyRCIModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::RCI_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One report with its lines, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::RCI_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->myrci->rci_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'RCI not found']);
                }
                return $this->response->setJSON($record);

            // One line per DV of the picked LDDAP-ADA
            case 'LDDAPADA-LINES':
                if (!fods_has_any_access([\Config\AccessCodes::RCI_ADD, \Config\AccessCodes::RCI_UPDATE])) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                return $this->response->setJSON($this->myrci->lddapada_lines($this->request->getGet('id')));

            case 'MAIN-SAVE':
                $this->myrci->rci_save();
                return redirect()->to('myrci?meaction=MAIN');
                break;

            case 'PRINT-RCI':
                if (!fods_has_access(\Config\AccessCodes::RCI_VIEW)) { return view('errors/html/access-restricted'); }
                return view('cashier/rci-pdf');
                break;
        }
    }


    private function loadMainView() {

        // Reports with the LDDAP-ADAs, DVs and amounts they cover
        $rcidata = $this->db->query("
        SELECT
            a.`recid`,
            a.`reportno`,
            a.`mds_branch`,
            a.`mds_accountno`,
            a.`fund_cluster_code`,
            a.`created_at`,
            a.`created_by`,
            COUNT(d.`recid`) AS dv_count,
            GROUP_CONCAT(DISTINCT d.`lddapadano` ORDER BY d.`lddapadano` SEPARATOR ', ') AS lddapadanos,
            COUNT(DISTINCT NULLIF(d.`lddapadano`, '')) AS lddapada_count,
            COALESCE(SUM(d.`gross_amount`), 0) AS gross_amount,
            COALESCE(SUM(d.`net_amount`), 0) AS net_amount
        FROM `tbl_rci_hd` a
        LEFT JOIN `tbl_rci_dt` d ON d.`hd_rid` = a.`recid`
        GROUP BY a.`recid`
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        // LDDAP-ADAs to pick from (newest first), with the report each is already on
        $lddapadas = $this->db->query("
        SELECT
            a.`recid` AS id,
            a.`lddapadano`,
            a.`lddapada_date`,
            a.`ckno`,
            a.`fund_cluster_code`,
            COUNT(d.`recid`) AS dv_count,
            COALESCE(SUM(d.`net_amount`), 0) AS net_amount,
            (SELECT GROUP_CONCAT(DISTINCT h.`reportno` SEPARATOR ', ')
               FROM `tbl_rci_dt` r JOIN `tbl_rci_hd` h ON h.`recid` = r.`hd_rid`
              WHERE r.`lddapadano` = a.`lddapadano`) AS on_rci
        FROM `tbl_lddapada_hd` a
        LEFT JOIN `tbl_lddapada_dt` d ON d.`lddapada_id` = a.`recid`
        GROUP BY a.`recid`
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        return view('cashier/rci-main', [
            'rcidata' => $rcidata,
            'lookups' => [
                'lddapadas' => $lddapadas,
                'fund_clusters' => ['01', '07'],
                // MDS sub-account per fund cluster, as on LDDAP-ADA
                'mds_accounts' => ['01' => '2182-9001-36', '07' => '2182-9006-16'],
                'mds_branch' => 'Land Bank of the Philippines - DOST Branch',
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::RCI_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::RCI_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that report directly
        ]);
    }


}
