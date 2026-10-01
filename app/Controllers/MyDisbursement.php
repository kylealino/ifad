<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyDisbursement extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mydisbursement = model('App\Models\MyDisbursementModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::DISBURSEMENT_VIEW)) {
                    return $this->loadMainView();
                }else {
                    return view('errors/html/access-restricted');
                }

            // One DV with its line items and history, for the editor modal
            case 'MAIN-LOAD':
                if (!fods_has_access(\Config\AccessCodes::DISBURSEMENT_VIEW)) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mydisbursement->disbursement_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Disbursement not found']);
                }
                return $this->response->setJSON($record);

            // ORS / BURS the DV is prepared from: header, deductions and lines
            case 'OBLIGATION-LOAD':
                if (!fods_has_any_access([\Config\AccessCodes::DISBURSEMENT_ADD, \Config\AccessCodes::DISBURSEMENT_UPDATE])) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mydisbursement->obligation_load($this->request->getGet('source'), $this->request->getGet('id'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'ORS / BURS not found']);
                }
                return $this->response->setJSON($record);

            case 'MAIN-SAVE':
                $this->mydisbursement->disbursement_save();
                return redirect()->to('mydisbursement?meaction=MAIN');
                break;

            // Approval now happens in Disbursement Approval (MyDisbursementApproval);
            // these stay so older links keep working (they decide the current box)
            case 'MAIN-APPROVE':
            case 'MAIN-APPROVE-A':
                $this->mydisbursement->disbursement_approve();
                return redirect()->to('mydisbursementapproval');
                break;

            case 'MAIN-DISAPPROVE':
            case 'MAIN-DISAPPROVE-A':
                $this->mydisbursement->disbursement_disapprove();
                return redirect()->to('mydisbursementapproval');
                break;

            case 'PRINT-DISBURSEMENT':
                if (!fods_has_access(\Config\AccessCodes::DISBURSEMENT_VIEW)) { return view('errors/html/access-restricted'); }
                return view('disbursement/disbursement-pdf');
                break;

        }
    }


    private function loadMainView() {

        $disbursementhddata = $this->db->query("
        SELECT
            a.recid,
            a.dvno,
            a.serialno,
            a.particulars,
            a.funding_source,
            a.fund_cluster_code,
            a.payee_name,
            a.disbursement_date,
            a.category,
            a.net_amount,
            a.is_approved_certa,
            a.is_disapproved_certa,
            a.is_approved_certc,
            a.is_disapproved_certc,
            a.is_approved_certd,
            a.is_disapproved_certd,
            a.added_at,
            a.added_by,
            COALESCE(ps.amount,0)
        + COALESCE(ips.amount,0)
        + COALESCE(m.amount,0)
        + COALESCE(im.amount,0)
        + COALESCE(co.amount,0)
        + COALESCE(ico.amount,0) AS amount
        FROM tbl_disbursement_hd a
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_disbursement_direct_ps_dt GROUP BY project_id) ps ON ps.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_disbursement_indirect_ps_dt GROUP BY project_id) ips ON ips.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_disbursement_direct_mooe_dt GROUP BY project_id) m ON m.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_disbursement_indirect_mooe_dt GROUP BY project_id) im ON im.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_disbursement_direct_co_dt GROUP BY project_id) co ON co.project_id = a.recid
        LEFT JOIN (SELECT project_id, SUM(amount) amount FROM tbl_disbursement_indirect_co_dt GROUP BY project_id) ico ON ico.project_id = a.recid
        ORDER BY a.recid DESC
        ")->getResultArray();

        // One status per DV (Box A approves first, then Box C, then Box D)
        foreach ($disbursementhddata as &$row) {
            $row['status'] = self::statusOf($row);
            unset($row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certc'], $row['is_disapproved_certc'], $row['is_approved_certd'], $row['is_disapproved_certd']);
        }
        unset($row);


        // ORS / BURS serial lookup (newest first); a DV copies from the one picked
        $obligations = $this->db->query("
        SELECT 'ORS' AS source, `recid` AS id, `serialno`, `payee_name`, `dvno`
        FROM `tbl_ors_hd` WHERE `serialno` <> ''
        UNION ALL
        SELECT 'BURS', `recid`, `serialno`, `payee_name`, `dvno`
        FROM `tbl_burs_hd` WHERE `serialno` <> ''
        ORDER BY id DESC
        ")->getResultArray();

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

        // responsibility code -> MFO/PAP lookup
        $projectdata = $this->db->query("
        SELECT
            a.`responsibility_code`,
            a.`project_title`,
            a.`mfopaps_code`
        FROM `tbl_reference_project` a
        ORDER BY a.`responsibility_code` ASC
        ")->getResultArray();

        $certifiers = fn ($tag) => $this->db->query("SELECT `full_name`, `position` FROM myua_user WHERE cert_tag = ? AND is_active = 1 ORDER BY `full_name` ASC", [$tag])->getResultArray();

        return view('disbursement/disbursement-main', [
            'disbursementhddata' => $disbursementhddata,
            'lookups' => [
                'obligations' => $obligations,
                'payees' => $payeedata,
                'projects' => $projectdata,
                'certifya' => $certifiers('1'),
                'certifyc' => $certifiers('3'),
                'funding_sources' => ['101101', '102101', '102406', '102407', '104102', '105462', '308601', '308602', '308603'],
                'fund_clusters' => ['01', '07'],
                'categories' => ['Goods', 'Services', 'Manual', 'Payroll', 'Income Tax'],
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::DISBURSEMENT_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::DISBURSEMENT_UPDATE),
            ],
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that DV directly
        ]);
    }

    // pending_a: waiting for the supervisor (Box A)
    // pending_c: Box A approved, waiting for accounting (Box C)
    // pending_d: Box C approved, waiting for approval for payment (Box D)
    public static function statusOf(array $hd) {
        if ($hd['is_disapproved_certa'] == '1' || $hd['is_disapproved_certc'] == '1' || $hd['is_disapproved_certd'] == '1') {
            return 'disapproved';
        }
        if ($hd['is_approved_certd'] == '1') {
            return 'approved';
        }
        if ($hd['is_approved_certc'] == '1') {
            return 'pending_d';
        }
        if ($hd['is_approved_certa'] == '1') {
            return 'pending_c';
        }
        return 'pending_a';
    }


}
