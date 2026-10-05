<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MyPOModel;

class MyPOApproval extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->mypo = model('App\Models\MyPOModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        $canView = fods_has_access(\Config\AccessCodes::POAPPROVAL_VIEW);

        switch ($meaction) {
            // One PO with its items, for the review modal
            case 'MAIN-LOAD':
                if (!$canView) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
                $record = $this->mypo->po_load($this->request->getGet('recid'));
                if ($record === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Purchase order not found']);
                }
                return $this->response->setJSON($record);

            // Box A or B, whichever the PO is waiting at
            case 'MAIN-APPROVE':
                $this->mypo->po_approve();
                return redirect()->to('mypoapproval');

            case 'MAIN-DISAPPROVE':
                $this->mypo->po_disapprove();
                return redirect()->to('mypoapproval');

            // Same PO printout as Purchase Order, for the signatories
            case 'PRINT-PO':
                if (!$canView) { return view('errors/html/access-restricted'); }
                return view('procurement/po/po-pdf');

            default:
                if ($canView) {
                    return $this->loadMainView();
                }
                return view('errors/html/access-restricted');
        }
    }


    private function loadMainView() {

        $podata = $this->db->query("
        SELECT
            a.`recid`,
            a.`pono`,
            a.`po_date`,
            a.`prno`,
            a.`supplier_name`,
            a.`charge_to`,
            a.`end_user`,
            a.`total_amount`,
            a.`accountant`,
            a.`approved_by`,
            a.`is_approved_certa`,
            a.`is_disapproved_certa`,
            a.`is_approved_certb`,
            a.`is_disapproved_certb`,
            a.`certa_approver`,
            a.`certb_approver`,
            a.`added_at`,
            a.`added_by`
        FROM tbl_po_hd a
        ORDER BY a.`recid` DESC
        ")->getResultArray();

        foreach ($podata as &$row) {
            $row['status'] = MyPO::statusOf($row);
            // whose turn it is, and whether that is this user
            $box = MyPOModel::step_of($row);
            $row['waiting_on'] = $box === '' ? '' : MyPOModel::signatory($row, $box);
            $row['mine'] = MyPOModel::can_decide($row);
            // who decided last, for the list
            $row['decided_by'] = '';
            foreach (['b', 'a'] as $b) {
                if ($row['is_approved_cert' . $b] == '1' || $row['is_disapproved_cert' . $b] == '1') {
                    $row['decided_by'] = $row['cert' . $b . '_approver'];
                    break;
                }
            }
            unset($row['accountant'], $row['approved_by'], $row['is_approved_certa'], $row['is_disapproved_certa'], $row['is_approved_certb'], $row['is_disapproved_certb'],
                $row['certa_approver'], $row['certb_approver']);
        }
        unset($row);


        return view('po-approval/po-approval-main', [
            'podata' => $podata,
            'open' => (string) $this->request->getGet('recid'),  // ?recid= opens that review directly
            'perms' => [
                'approve' => fods_has_access(\Config\AccessCodes::POAPPROVAL_APPROVE),
            ],
        ]);
    }


}
