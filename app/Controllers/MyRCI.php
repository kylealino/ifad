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
                return $this->loadMainView();
                break;
            case 'MAIN-SAVE': 
                $this->myrci->rci_save();
                return redirect()->to('myrci?meaction=MAIN');
                break;
            case 'PRINT-RCI': 
                return view('cashier/rci-pdf');
                break;
        }
    }
    

    private function loadMainView() {

        //serialno lookup
        $rciquery = $this->db->query("
        SELECT
            `recid`,
            `mds_branch`,
            `mds_accountno`,
            `fund_cluster_code`,
            `reportno`
        FROM
            `tbl_rci_hd`
        ORDER BY `recid` DESC
        ");
        $rcidata = $rciquery->getResultArray();

        $lddapadaquery = $this->db->query("
            SELECT
                a.lddapadano,
                a.lddapada_date,
                a.ckno,
                b.dvno,
                b.serialno,
                
                x.responsibility_code,
                b.payee_name,
                c.particulars,
                b.gross_amount,
                '0' AS philhealth_amount,
                b.total_deduction AS tax_amount,
                b.net_amount
            FROM tbl_lddapada_hd a

            JOIN tbl_lddapada_dt b
                ON a.recid = b.lddapada_id

            JOIN tbl_ors_hd c
                ON b.serialno = c.serialno

            JOIN (

                SELECT
                    project_id,
                    responsibility_code,
                    amount
                FROM tbl_ors_direct_ps_dt

                UNION ALL

                SELECT
                    project_id,
                    responsibility_code,
                    amount
                FROM tbl_ors_direct_co_dt

                UNION ALL

                SELECT
                    project_id,
                    responsibility_code,
                    amount
                FROM tbl_ors_direct_mooe_dt

                UNION ALL

                SELECT
                    project_id,
                    responsibility_code,
                    amount
                FROM tbl_ors_indirect_ps_dt

                UNION ALL

                SELECT
                    project_id,
                    responsibility_code,
                    amount
                FROM tbl_ors_indirect_co_dt

                UNION ALL

                SELECT
                    project_id,
                    responsibility_code,
                    amount
                FROM tbl_ors_indirect_mooe_dt

            ) x

            ON c.recid = x.project_id
        
        ");
        $lddapadadata = $lddapadaquery->getResultArray();

        return view('cashier/rci-main', [
            'lddapadadata' => $lddapadadata,
            'rcidata' => $rcidata,
        ]);
    }
    
    
}
