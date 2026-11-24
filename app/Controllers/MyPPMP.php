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

            // Supporting documents (Documents tab): add, remove, open
            case 'DOC-UPLOAD':
                return $this->json($this->myppmp->attachment_add($this->request->getPost('recid')));

            case 'DOC-REMOVE':
                return $this->json($this->myppmp->attachment_remove($this->request->getPost('id')));

            case 'DOC-FILE':
                if (!fods_has_access(\Config\AccessCodes::PPMP_VIEW)) {
                    return $this->response->setStatusCode(403)->setBody('Access denied.');
                }
                return $this->document($this->myppmp->attachment_file($this->request->getGet('id')), $this->request->getGet('dl') === '1');
        }
    }

    // Model results carry 'error' on failure -> 422 so the JS shows it
    private function json(array $result) {
        return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);
    }

    // PDFs and images open in the browser (the preview); other files, or ?dl=1, download
    private function document(?array $file, bool $download) {
        if ($file === null) {
            return $this->response->setStatusCode(404)->setBody('Document not found.');
        }
        $inline = !$download && ($file['is_image'] || $file['mime_type'] === 'application/pdf');
        // plain-ASCII name for old browsers, the real (UTF-8) name for the rest
        $ascii = preg_replace('/[^\x20-\x7E]|["\\\\]/', '_', $file['orig_name']);
        return $this->response
            ->setHeader('Content-Type', $file['mime_type'])
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($file['orig_name']))
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($file['path']));
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

        // One status per PPMP: final, indicative, or not marked yet; plus how much the PRs used
        $usage = (new \App\Models\MyPPMPUsageModel())->summary();
        foreach ($ppmphddata as &$row) {
            $row['status'] = self::statusOf($row);
            unset($row['is_indicative'], $row['is_final']);
            $u = $usage[(int) $row['recid']] ?? ['total_qty' => 0, 'used_qty' => 0, 'used_amount' => 0];
            $row['total_qty'] = (int) $u['total_qty'];
            $row['used_qty'] = (int) $u['used_qty'];
            $row['used_amount'] = round((float) $u['used_amount'], 2);
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
                // suggestions only: older items keep the mode they were saved with (SVP, Public Bidding)
                'modes' => [
                    'Competitive Bidding',
                    'Limited Source Bidding',
                    'Competitive Dialogue',
                    'Unsolicited Offer with Bid Matching',
                    'Direct Contracting',
                    'Direct Acquisition',
                    'Repeat Order',
                    'Small Value Procurement',
                    'Negotiated Procurement',
                    'Direct Sales',
                    'Direct Procurement for Science, Technology and Innovation',
                ],
                'preproc' => ['Yes', 'No'],
                'funding_sources' => ['General Fund', 'Trust Fund', 'Project Fund (Externally Funded)'],
            ],
            'perms' => [
                'add' => fods_has_access(\Config\AccessCodes::PPMP_ADD),
                'edit' => fods_has_access(\Config\AccessCodes::PPMP_UPDATE),
                'attach' => \App\Models\MyPPMPModel::can_attach(),   // add / remove supporting documents
            ],
            'doc_max_mb' => \App\Models\MyPPMPModel::DOC_MAX_BYTES / 1048576,
            'doc_max_files' => \App\Models\MyPPMPModel::DOC_MAX_FILES,
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
