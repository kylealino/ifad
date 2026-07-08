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
                return $this->loadMainView();
                break;
    
            case 'MAIN-SAVE': 
                $this->mylddapada->lddapada_save();
                return redirect()->to('mylddapada?meaction=MAIN');
                break;

            case 'PRINT-LDDAPADA': 
                return view('cashier/lddapada-pdf');
                break;

            case 'LOAD-DV':
            // Get the selected DV numbers
            $dvno = $this->request->getPostGet('dvno');
            
            // Debug: Log received values
            log_message('debug', 'LOAD-DV called - dvno: ' . print_r($dvno, true));
            
            // Validate input
            if (empty($dvno)) {
                echo json_encode(['error' => 'No DV numbers selected']);
                exit;
            }
            
            // Prepare DVNO list for IN clause
            if (is_array($dvno)) {
                $escapedDvnos = array_map([$this->db, 'escapeString'], $dvno);
                $in = "'" . implode("','", $escapedDvnos) . "'";
            } else {
                $in = "'" . $this->db->escapeString($dvno) . "'";
            }
            
            // STEP 1: Get serialno and project_id from the selected DVNO
            // Assuming tbl_disbursement_hd has serialno field that links to tbl_ors_hd
            $dvquery = $this->db->query("
                SELECT DISTINCT
                    a.serialno,
                    b.recid AS project_id
                FROM tbl_disbursement_hd a
                LEFT JOIN tbl_ors_hd b ON b.serialno = a.serialno
                WHERE a.dvno IN ($in)
                LIMIT 1
            ");
            
            $dvdata = $dvquery->getRowArray();
            
            if (!$dvdata) {
                echo json_encode(['error' => 'No data found for selected DV numbers']);
                exit;
            }
            
            $serialno = $dvdata['serialno'];
            $project_id = $dvdata['project_id'];
            
            // Debug: Log found values
            log_message('debug', 'Found serialno: ' . $serialno . ', project_id: ' . $project_id);
            
            // STEP 2: Get UACS code for the project_id
            $uacsSubquery = "
                SELECT uacs_code FROM (
                    SELECT uacs_code, amount FROM tbl_ors_direct_ps_dt WHERE project_id = '$project_id'
                    UNION ALL
                    SELECT uacs_code, amount FROM tbl_ors_direct_mooe_dt WHERE project_id = '$project_id'
                    UNION ALL
                    SELECT uacs_code, amount FROM tbl_ors_direct_co_dt WHERE project_id = '$project_id'
                    UNION ALL
                    SELECT uacs_code, amount FROM tbl_ors_indirect_ps_dt WHERE project_id = '$project_id'
                    UNION ALL
                    SELECT uacs_code, amount FROM tbl_ors_indirect_mooe_dt WHERE project_id = '$project_id'
                    UNION ALL
                    SELECT uacs_code, amount FROM tbl_ors_indirect_co_dt WHERE project_id = '$project_id'
                ) AS all_uacs
                ORDER BY amount DESC
                LIMIT 1
            ";
            
            // STEP 3: Main query
            $query = $this->db->query("
                SELECT
                    a.payee_name,
                    a.dvno,
                    (SELECT payee_account_num 
                    FROM tbl_payee 
                    WHERE payee_name = a.payee_name) AS payee_account_num,
                    a.serialno,
                    COALESCE(($uacsSubquery), '') AS uacs_code,
                    a.gross_amount,
                    a.total_deduction,
                    a.net_amount,
                    a.dvno
                FROM tbl_disbursement_hd a
                WHERE a.dvno IN ($in)
            ");
            
            $results = $query->getResultArray();
            
            // Add the project_id to each result (optional)
            foreach ($results as &$row) {
                $row['project_id'] = $project_id;
            }
            
            echo json_encode($results);
            exit;
            break;
            
        }
    }
    

    private function loadMainView() {

        //serialno lookup
        $dvquery = $this->db->query("
        SELECT
            `recid`,
            `dvno`
        FROM
            `tbl_disbursement_hd`
        ORDER BY `recid` DESC
        ");
        $dvdata = $dvquery->getResultArray();

        $dvhdquery = $this->db->query("
        SELECT
            a.`recid`,
            a.`lddapadano`,
            a.`mds_branch`,
            a.`mds_accountno`,
            a.`lddapada_date`,
            a.`fund_cluster_code`,
            a.`funding_source`
        FROM
            `tbl_lddapada_hd` a
        ORDER BY recid DESC
        ");
        $dvhddata = $dvhdquery->getResultArray();

        $approverquery = $this->db->query("SELECT * FROM myua_user WHERE ada_tag = '1' ORDER BY recid DESC");
        $adaapprover = $approverquery->getResultArray();

        return view('cashier/lddapada-main', [
            'dvdata' => $dvdata,
            'dvhddata' => $dvhddata,
            'adaapprover' => $adaapprover,
        ]);
    }
    
    
}
