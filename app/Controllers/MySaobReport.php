<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MySaobReport extends BaseController
{
    
    public function __construct()
	{

		$this->request = \Config\Services::request();
        $this->mysaob = model('App\Models\MySaobReportModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
        $this->recid = $this->request->getPostGet('recid');
	}

    public function index() {
        
        $meaction = $this->request->getPostGet('meaction');
    
        switch ($meaction) {
            case 'MAIN': 
                $accessQuery = $this->db->query("
                    SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '3001' AND `is_active` = '1'
                ");
                if ($accessQuery->getNumRows() > 0) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }
    
			case 'SAOB-PDF':
				return view('report/saob-pdf');
				break;

            case 'MAIN-SAVE': 
                $this->mysaob->saob_save();
                return redirect()->to('mysaobrpt?meaction=MAIN');
                break;

            case 'SAVINGS-SAVE': 
                $this->mysaob->savings_save();
                return redirect()->to('mysaobrpt?meaction=MAIN');
                break;

            case 'SAVINGS-PRINT': 
                return view('report/saob-savings-pdf');
                break;
                
            //  case 'MAIN-APPROVE': 
            //     $this->mysaob->budget_approve();
            //     return redirect()->to('myua?meaction=MAIN');
            //     break;

            //   case 'MAIN-DISAPPROVE': 
            //     $this->mysaob->budget_disapprove();
            //     return redirect()->to('myua?meaction=MAIN');
            //     break;
        }
    }
    
    public function exportCsv(){
        $from = $this->request->getGet('date_from');
        $to   = $this->request->getGet('date_to');

        if (!$from || !$to) {
            return;
        }

        $sql = "
            SELECT
                hd.ors_date,
                hd.payee_name,
                hd.serialno,
                hd.particulars,
                d.program_title,
                d.project_title,
                d.responsibility_code,
                d.mfopaps_code,
                (SELECT allotment_class FROM mst_uacs WHERE sub_object_code = d.sub_object_code LIMIT 1) AS allotment_class,
                (SELECT object_code FROM mst_uacs WHERE sub_object_code = d.sub_object_code LIMIT 1) AS object_code,
                d.sub_object_code,
                d.uacs_code,
                d.amount
            FROM tbl_ors_hd hd
            JOIN (
                SELECT project_id, program_title, project_title, responsibility_code,
                    mfopaps_code, sub_object_code, uacs_code, amount, added_at, added_by
                FROM tbl_ors_direct_ps_dt

                UNION ALL
                SELECT project_id, program_title, project_title, responsibility_code,
                    mfopaps_code, sub_object_code, uacs_code, amount, added_at, added_by
                FROM tbl_ors_indirect_ps_dt

                UNION ALL
                SELECT project_id, program_title, project_title, responsibility_code,
                    mfopaps_code, sub_object_code, uacs_code, amount, added_at, added_by
                FROM tbl_ors_direct_mooe_dt

                UNION ALL
                SELECT project_id, program_title, project_title, responsibility_code,
                    mfopaps_code, sub_object_code, uacs_code, amount, added_at, added_by
                FROM tbl_ors_indirect_mooe_dt

                UNION ALL
                SELECT project_id, program_title, project_title, responsibility_code,
                    mfopaps_code, sub_object_code, uacs_code, amount, added_at, added_by
                FROM tbl_ors_direct_co_dt

                UNION ALL
                SELECT project_id, program_title, project_title, responsibility_code,
                    mfopaps_code, sub_object_code, uacs_code, amount, added_at, added_by
                FROM tbl_ors_indirect_co_dt
            ) d ON d.project_id = hd.recid
            WHERE hd.ors_date BETWEEN ? AND ?
            ORDER BY
                hd.ors_date ASC,
                /* 1. Extract the last numeric part of the serial number (e.g., 0070) and sort it numerically */
                CAST(SUBSTRING_INDEX(hd.serialno, '-', -1) AS UNSIGNED) ASC
        ";

        $query = $this->db->query($sql, [$from, $to]);
        $data  = $query->getResultArray();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="SAOB_'.$from.'_to_'.$to.'.csv"');

        $out = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($out, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($out, $row);
            }
        }

        fclose($out);
        exit;
    }

    public function monthlyExportCsv(){
        $from = $this->request->getGet('date_from'); // selected start month
        $to   = $this->request->getGet('date_to');   // selected end month
        $year_start = date('Y-01-01', strtotime($to)); // Jan 1 of selected year
        $program_title   = $this->request->getGet('monthly_program_title');   // selected end month

        if (!$from || !$to) {
            return redirect()->back()->with('error', 'Invalid date.');
        }

        $sql = "
        SELECT
            budget.program_title,
            budget.project_title,
            budget.responsibility_code,
            budget.project_leader,

            /* =========================
            TOTAL ALLOTMENT
            ========================== */
            (
                COALESCE((SELECT SUM(dps.approved_budget) FROM tbl_budget_direct_ps_dt dps
                        JOIN tbl_budget_hd hd ON dps.project_id = hd.recid
                        WHERE hd.project_title = budget.project_title),0)
                + COALESCE((SELECT SUM(idps.approved_budget) FROM tbl_budget_indirect_ps_dt idps
                        JOIN tbl_budget_hd hd ON idps.project_id = hd.recid
                        WHERE hd.project_title = budget.project_title),0)
                + COALESCE((SELECT SUM(dmooe.approved_budget) FROM tbl_budget_direct_mooe_dt dmooe
                        JOIN tbl_budget_hd hd ON dmooe.project_id = hd.recid
                        WHERE hd.project_title = budget.project_title),0)
                + COALESCE((SELECT SUM(idmooe.approved_budget) FROM tbl_budget_indirect_mooe_dt idmooe
                        JOIN tbl_budget_hd hd ON idmooe.project_id = hd.recid
                        WHERE hd.project_title = budget.project_title),0)
                + COALESCE((SELECT SUM(dco.approved_budget) FROM tbl_budget_direct_co_dt dco
                        JOIN tbl_budget_hd hd ON dco.project_id = hd.recid
                        WHERE hd.project_title = budget.project_title),0)
                + COALESCE((SELECT SUM(idco.approved_budget) FROM tbl_budget_indirect_co_dt idco
                        JOIN tbl_budget_hd hd ON idco.project_id = hd.recid
                        WHERE hd.project_title = budget.project_title),0)
            ) AS allotment,

            /* =========================
            ADMIN COST
            ========================== */
            COALESCE((SELECT SUM(approved_budget)
                    FROM tbl_budget_savings_dt
                    WHERE project_id = budget.recid),0) AS admin_cost,

            /* =========================
            REVISED ALLOTMENT = allotment - admin_cost
            ========================== */
            (
                (
                    COALESCE((SELECT SUM(dps.approved_budget) FROM tbl_budget_direct_ps_dt dps
                            JOIN tbl_budget_hd hd ON dps.project_id = hd.recid
                            WHERE hd.project_title = budget.project_title),0)
                    + COALESCE((SELECT SUM(idps.approved_budget) FROM tbl_budget_indirect_ps_dt idps
                            JOIN tbl_budget_hd hd ON idps.project_id = hd.recid
                            WHERE hd.project_title = budget.project_title),0)
                    + COALESCE((SELECT SUM(dmooe.approved_budget) FROM tbl_budget_direct_mooe_dt dmooe
                            JOIN tbl_budget_hd hd ON dmooe.project_id = hd.recid
                            WHERE hd.project_title = budget.project_title),0)
                    + COALESCE((SELECT SUM(idmooe.approved_budget) FROM tbl_budget_indirect_mooe_dt idmooe
                            JOIN tbl_budget_hd hd ON idmooe.project_id = hd.recid
                            WHERE hd.project_title = budget.project_title),0)
                    + COALESCE((SELECT SUM(dco.approved_budget) FROM tbl_budget_direct_co_dt dco
                            JOIN tbl_budget_hd hd ON dco.project_id = hd.recid
                            WHERE hd.project_title = budget.project_title),0)
                    + COALESCE((SELECT SUM(idco.approved_budget) FROM tbl_budget_indirect_co_dt idco
                            JOIN tbl_budget_hd hd ON idco.project_id = hd.recid
                            WHERE hd.project_title = budget.project_title),0)
                )
                - COALESCE((SELECT SUM(approved_budget) FROM tbl_budget_savings_dt WHERE project_id = budget.recid),0)
            ) AS revised_allotment,

            /* =========================
            THIS MONTH (Filtered by ORS Date)
            ========================== */
            (
                COALESCE((SELECT SUM(dps.amount) FROM tbl_ors_direct_ps_dt dps
                        JOIN tbl_ors_hd hd ON dps.project_id = hd.recid
                        WHERE dps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(idps.amount) FROM tbl_ors_indirect_ps_dt idps
                        JOIN tbl_ors_hd hd ON idps.project_id = hd.recid
                        WHERE idps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(dmooe.amount) FROM tbl_ors_direct_mooe_dt dmooe
                        JOIN tbl_ors_hd hd ON dmooe.project_id = hd.recid
                        WHERE dmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(idmooe.amount) FROM tbl_ors_indirect_mooe_dt idmooe
                        JOIN tbl_ors_hd hd ON idmooe.project_id = hd.recid
                        WHERE idmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(dco.amount) FROM tbl_ors_direct_co_dt dco
                        JOIN tbl_ors_hd hd ON dco.project_id = hd.recid
                        WHERE dco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(idco.amount) FROM tbl_ors_indirect_co_dt idco
                        JOIN tbl_ors_hd hd ON idco.project_id = hd.recid
                        WHERE idco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
            ) AS this_month,

            /* =========================
            UP TO DATE (FROM Jan 1 → Selected TO)
            ========================== */
            (
                COALESCE((SELECT SUM(dps.amount) FROM tbl_ors_direct_ps_dt dps
                        JOIN tbl_ors_hd hd ON dps.project_id = hd.recid
                        WHERE dps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(idps.amount) FROM tbl_ors_indirect_ps_dt idps
                        JOIN tbl_ors_hd hd ON idps.project_id = hd.recid
                        WHERE idps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(dmooe.amount) FROM tbl_ors_direct_mooe_dt dmooe
                        JOIN tbl_ors_hd hd ON dmooe.project_id = hd.recid
                        WHERE dmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(idmooe.amount) FROM tbl_ors_indirect_mooe_dt idmooe
                        JOIN tbl_ors_hd hd ON idmooe.project_id = hd.recid
                        WHERE idmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(dco.amount) FROM tbl_ors_direct_co_dt dco
                        JOIN tbl_ors_hd hd ON dco.project_id = hd.recid
                        WHERE dco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                + COALESCE((SELECT SUM(idco.amount) FROM tbl_ors_indirect_co_dt idco
                        JOIN tbl_ors_hd hd ON idco.project_id = hd.recid
                        WHERE idco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
            ) AS todate,

            /* =========================
            BALANCE = revised_allotment - todate
            ========================== */
            (
                (
                    (
                        COALESCE((SELECT SUM(dps.approved_budget) FROM tbl_budget_direct_ps_dt dps
                                JOIN tbl_budget_hd hd ON dps.project_id = hd.recid
                                WHERE hd.project_title = budget.project_title),0)
                        + COALESCE((SELECT SUM(idps.approved_budget) FROM tbl_budget_indirect_ps_dt idps
                                JOIN tbl_budget_hd hd ON idps.project_id = hd.recid
                                WHERE hd.project_title = budget.project_title),0)
                        + COALESCE((SELECT SUM(dmooe.approved_budget) FROM tbl_budget_direct_mooe_dt dmooe
                                JOIN tbl_budget_hd hd ON dmooe.project_id = hd.recid
                                WHERE hd.project_title = budget.project_title),0)
                        + COALESCE((SELECT SUM(idmooe.approved_budget) FROM tbl_budget_indirect_mooe_dt idmooe
                                JOIN tbl_budget_hd hd ON idmooe.project_id = hd.recid
                                WHERE hd.project_title = budget.project_title),0)
                        + COALESCE((SELECT SUM(dco.approved_budget) FROM tbl_budget_direct_co_dt dco
                                JOIN tbl_budget_hd hd ON dco.project_id = hd.recid
                                WHERE hd.project_title = budget.project_title),0)
                        + COALESCE((SELECT SUM(idco.approved_budget) FROM tbl_budget_indirect_co_dt idco
                                JOIN tbl_budget_hd hd ON idco.project_id = hd.recid
                                WHERE hd.project_title = budget.project_title),0)
                    )
                    - COALESCE((SELECT SUM(approved_budget) FROM tbl_budget_savings_dt WHERE project_id = budget.recid),0)
                )
                - (
                    COALESCE((SELECT SUM(dps.amount) FROM tbl_ors_direct_ps_dt dps
                            JOIN tbl_ors_hd hd ON dps.project_id = hd.recid
                            WHERE dps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                    + COALESCE((SELECT SUM(idps.amount) FROM tbl_ors_indirect_ps_dt idps
                            JOIN tbl_ors_hd hd ON idps.project_id = hd.recid
                            WHERE idps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                    + COALESCE((SELECT SUM(dmooe.amount) FROM tbl_ors_direct_mooe_dt dmooe
                            JOIN tbl_ors_hd hd ON dmooe.project_id = hd.recid
                            WHERE dmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                    + COALESCE((SELECT SUM(idmooe.amount) FROM tbl_ors_indirect_mooe_dt idmooe
                            JOIN tbl_ors_hd hd ON idmooe.project_id = hd.recid
                            WHERE idmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                    + COALESCE((SELECT SUM(dco.amount) FROM tbl_ors_direct_co_dt dco
                            JOIN tbl_ors_hd hd ON dco.project_id = hd.recid
                            WHERE dco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                    + COALESCE((SELECT SUM(idco.amount) FROM tbl_ors_indirect_co_dt idco
                            JOIN tbl_ors_hd hd ON idco.project_id = hd.recid
                            WHERE idco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                )
            ) AS balance,


            /* =========================
            PERCENT UTILIZATION = (todate / revised_allotment) * 100 with % sign
            ========================== */
            CONCAT(
                ROUND(
                    (
                        (
                            COALESCE((SELECT SUM(dps.amount) FROM tbl_ors_direct_ps_dt dps
                                    JOIN tbl_ors_hd hd ON dps.project_id = hd.recid
                                    WHERE dps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                            + COALESCE((SELECT SUM(idps.amount) FROM tbl_ors_indirect_ps_dt idps
                                    JOIN tbl_ors_hd hd ON idps.project_id = hd.recid
                                    WHERE idps.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                            + COALESCE((SELECT SUM(dmooe.amount) FROM tbl_ors_direct_mooe_dt dmooe
                                    JOIN tbl_ors_hd hd ON dmooe.project_id = hd.recid
                                    WHERE dmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                            + COALESCE((SELECT SUM(idmooe.amount) FROM tbl_ors_indirect_mooe_dt idmooe
                                    JOIN tbl_ors_hd hd ON idmooe.project_id = hd.recid
                                    WHERE idmooe.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                            + COALESCE((SELECT SUM(dco.amount) FROM tbl_ors_direct_co_dt dco
                                    JOIN tbl_ors_hd hd ON dco.project_id = hd.recid
                                    WHERE dco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                            + COALESCE((SELECT SUM(idco.amount) FROM tbl_ors_indirect_co_dt idco
                                    JOIN tbl_ors_hd hd ON idco.project_id = hd.recid
                                    WHERE idco.project_title = budget.project_title AND hd.ors_date BETWEEN ? AND ?),0)
                        )
                        / NULLIF(
                            (
                                (
                                    COALESCE((SELECT SUM(dps.approved_budget) FROM tbl_budget_direct_ps_dt dps
                                            JOIN tbl_budget_hd hd ON dps.project_id = hd.recid
                                            WHERE hd.project_title = budget.project_title),0)
                                    + COALESCE((SELECT SUM(idps.approved_budget) FROM tbl_budget_indirect_ps_dt idps
                                            JOIN tbl_budget_hd hd ON idps.project_id = hd.recid
                                            WHERE hd.project_title = budget.project_title),0)
                                    + COALESCE((SELECT SUM(dmooe.approved_budget) FROM tbl_budget_direct_mooe_dt dmooe
                                            JOIN tbl_budget_hd hd ON dmooe.project_id = hd.recid
                                            WHERE hd.project_title = budget.project_title),0)
                                    + COALESCE((SELECT SUM(idmooe.approved_budget) FROM tbl_budget_indirect_mooe_dt idmooe
                                            JOIN tbl_budget_hd hd ON idmooe.project_id = hd.recid
                                            WHERE hd.project_title = budget.project_title),0)
                                    + COALESCE((SELECT SUM(dco.approved_budget) FROM tbl_budget_direct_co_dt dco
                                            JOIN tbl_budget_hd hd ON dco.project_id = hd.recid
                                            WHERE hd.project_title = budget.project_title),0)
                                    + COALESCE((SELECT SUM(idco.approved_budget) FROM tbl_budget_indirect_co_dt idco
                                            JOIN tbl_budget_hd hd ON idco.project_id = hd.recid
                                            WHERE hd.project_title = budget.project_title),0)
                                )
                                - COALESCE((SELECT SUM(approved_budget) FROM tbl_budget_savings_dt WHERE project_id = budget.recid),0)
                            ), 0
                        )
                        * 100
                    ), 2
                ),
                '%'
            ) AS percent_utilization


        FROM tbl_budget_hd budget
        WHERE budget.program_title LIKE ?
        AND budget.duration_from <= ?   -- project starts on or before $to
        AND budget.duration_to   >= ?   -- project ends on or after $from
        ";


        $bindings = [
            // THIS MONTH (6 subqueries × 2)
            $from, $to,
            $from, $to,
            $from, $to,
            $from, $to,
            $from, $to,
            $from, $to,

            // TO DATE (6 subqueries × 2)
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,



            // BALANCE (6 × 2)
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,

            // PERCENT UTILIZATION (6 × 2)
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,
            $year_start, $to,

                        // Duration filter
            $program_title,
            $to, $from,

            
        ];


        $query = $this->db->query($sql, $bindings);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="SAOB_MONTHLY_REPORT_'.$from.'_to_'.$to.'.csv"');

        $out = fopen('php://output', 'w');

        // Write header manually
        fputcsv($out, [
            'Program Title', 'Project Title', 'Responsibility Code',
            'Project Leader', 'Allotment',
            'Admin Cost', 'Revised Allotment', 'This Month', 'Up to Date', 'Balance', 'Percent of Utilization'
        ]);

        while ($row = $query->getUnbufferedRow('array')) {
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    public function saobExportCsv(){
        $month = $this->request->getGet('month'); // January - December
        $year  = $this->request->getGet('year');  // e.g. 2026

        if (!$month || !$year) {
            return redirect()->back()->with('Oops', 'Please select month and year.');
        }

        /*
        |--------------------------------------------------------------------------
        | Convert Month + Year to Date Ranges
        |--------------------------------------------------------------------------
        */
        $monthNumber = date('m', strtotime($month));

        $from = date('Y-m-d', strtotime("$year-$monthNumber-01"));
        $to   = date('Y-m-t', strtotime($from)); // last day of selected month

        $year_start = "$year-01-01";
        $year_end   = "$year-12-31";

        /*
        |--------------------------------------------------------------------------
        | SQL (Using ? Bindings)
        |--------------------------------------------------------------------------
        */
        $sql = "
        /* ================= General Administration and Support Services ================= */
        /* ================= PS DATA ================= */
        SELECT
            a.program_title, u.allotment_class, b.object_code,
            b.particulars AS sub_object_code, b.code AS uacs_code, b.approved_budget, 
            /* TOTAL PER QUARTER */
            b.january_revision, b.february_revision, b.march_revision,
            (COALESCE(b.january_revision,0)
            + COALESCE(b.february_revision,0)
            + COALESCE(b.march_revision,0)) AS first_quarter_total,
            b.april_revision, b.may_revision, b.june_revision,
            (COALESCE(b.april_revision,0)
            + COALESCE(b.may_revision,0)
            + COALESCE(b.june_revision,0)) AS second_quarter_total,
            b.july_revision, b.august_revision, b.september_revision,
            (COALESCE(b.july_revision,0)
            + COALESCE(b.august_revision,0)
            + COALESCE(b.september_revision,0)) AS third_quarter_total,
            b.october_revision, b.november_revision, b.december_revision,
            (COALESCE(b.october_revision,0)
            + COALESCE(b.november_revision,0)
            + COALESCE(b.december_revision,0)) AS forth_quarter_total,
            /* TOTAL REALIGNMENT */
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS total_realignment,
            /* REVISED ALLOTMENT */
            b.approved_budget + 
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS revised_allotment,

            /* ORS 1ST QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_january,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_february,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_march,
            
            /* ORS 1ST QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_first_quarter_total,

            /* ORS 2ND QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_april,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_may,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_june,

            /* ORS 2ND QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_second_quarter_total,


            /* ORS 3RD QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_july,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_august,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_september,

            /* ORS 3RD QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_third_quarter_total,

            /* ORS 4TH QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_october,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_november,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_december,

            /* ORS 4TH QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_forth_quarter_total,

            /* WHOLE YEAR PS */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND i.project_title LIKE '%General Administration and%'), 0)) AS total_sub_all,
            
            /* UNOBLIGATED BALANCE */
            (
                -- Revised Allotment
                b.approved_budget + 
                (
                    COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                    COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                    COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                    COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
                )
                -- Minus total_sub_all
                - 
                (
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_direct_ps_dt d 
                        JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
                        WHERE d.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND d.project_title LIKE '%General Administration and%'), 0
                    ) +
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_indirect_ps_dt i 
                        JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
                        WHERE i.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND i.project_title LIKE '%General Administration and%'), 0
                    )
                )
            ) AS unobligated_balance

        FROM tbl_saob_hd a
        JOIN tbl_saob_ps_dt b ON a.recid = b.project_id
        LEFT JOIN mst_uacs u ON b.code = u.uacs_code
        WHERE b.code NOT IN ('50103010-00','50102990-14','50104990-06','50104990-14')
        AND a.program_title LIKE '%General Administration and%' AND a.current_year = ?

        UNION ALL

        /* ================= MOOE DATA ================= */
        SELECT
            a.program_title, u.allotment_class, b.object_code,
            b.particulars AS sub_object_code, b.code AS uacs_code, b.approved_budget,
            /* TOTAL PER QUARTER */
            b.january_revision, b.february_revision, b.march_revision,
            (COALESCE(b.january_revision,0)
            + COALESCE(b.february_revision,0)
            + COALESCE(b.march_revision,0)) AS first_quarter_total,
            b.april_revision, b.may_revision, b.june_revision,
            (COALESCE(b.april_revision,0)
            + COALESCE(b.may_revision,0)
            + COALESCE(b.june_revision,0)) AS second_quarter_total,
            b.july_revision, b.august_revision, b.september_revision,
            (COALESCE(b.july_revision,0)
            + COALESCE(b.august_revision,0)
            + COALESCE(b.september_revision,0)) AS third_quarter_total,
            b.october_revision, b.november_revision, b.december_revision,
            (COALESCE(b.october_revision,0)
            + COALESCE(b.november_revision,0)
            + COALESCE(b.december_revision,0)) AS forth_quarter_total,
            /* TOTAL REALIGNMENT */
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS total_realignment,
            /* REVISED ALLOTMENT */
            b.approved_budget + 
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS revised_allotment,
            /* ORS 1ST QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_january,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_february,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_march,

            /* ORS 1ST QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_first_quarter_total,

            /* ORS 2ND QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_april,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_may,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_june,

            /* ORS 2ND QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_second_quarter_total,

            /* ORS 3RD QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_july,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_august,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_september,

            /* ORS 3RD QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_third_quarter_total,

            /* ORS 4TH QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_october,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_november,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND i.project_title LIKE '%General Administration and%'), 0)) AS total_ors_december,

            /* ORS 4TH QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND d.project_title LIKE '%General Administration and%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND i.project_title LIKE '%General Administration and%'),0)
            ) AS ors_forth_quarter_total,

            /* WHOLE YEAR PS */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_mooe_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND d.project_title LIKE '%General Administration and%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND i.project_title LIKE '%General Administration and%'), 0)) AS total_sub_all,

            (
                -- Revised Allotment
                b.approved_budget + 
                (
                    COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                    COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                    COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                    COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
                )
                -- Minus total_sub_all
                - 
                (
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_direct_mooe_dt d 
                        JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
                        WHERE d.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND d.project_title LIKE '%General Administration and%'), 0
                    ) +
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_indirect_ps_dt i 
                        JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
                        WHERE i.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND i.project_title LIKE '%General Administration and%'), 0
                    )
                )
            ) AS unobligated_balance
             
        FROM tbl_saob_hd a
        JOIN tbl_saob_mooe_dt b ON a.recid = b.project_id
        LEFT JOIN mst_uacs u ON b.code = u.uacs_code
        WHERE a.program_title LIKE '%General Administration and%' AND a.current_year = ?

        UNION ALL

        /* ================= Relocation and Construction of New DOST-FNRI ================= */
        /* ================= CO DATA ================= */
        SELECT
            a.program_title, u.allotment_class, b.object_code,
            b.particulars AS sub_object_code, b.code AS uacs_code, b.approved_budget,
            /* TOTAL PER QUARTER */
            b.january_revision, b.february_revision, b.march_revision,
            (COALESCE(b.january_revision,0)
            + COALESCE(b.february_revision,0)
            + COALESCE(b.march_revision,0)) AS first_quarter_total,
            b.april_revision, b.may_revision, b.june_revision,
            (COALESCE(b.april_revision,0)
            + COALESCE(b.may_revision,0)
            + COALESCE(b.june_revision,0)) AS second_quarter_total,
            b.july_revision, b.august_revision, b.september_revision,
            (COALESCE(b.july_revision,0)
            + COALESCE(b.august_revision,0)
            + COALESCE(b.september_revision,0)) AS third_quarter_total,
            b.october_revision, b.november_revision, b.december_revision,
            (COALESCE(b.october_revision,0)
            + COALESCE(b.november_revision,0)
            + COALESCE(b.december_revision,0)) AS forth_quarter_total,
            /* TOTAL REALIGNMENT */
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS total_realignment,
            /* REVISED ALLOTMENT */
            b.approved_budget + 
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS revised_allotment,
            /* ORS 1ST QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_january,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_february,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_march,

            /* ORS 1ST QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            ) AS ors_first_quarter_total,

            /* ORS 2ND QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_april,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_may,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_june,

            /* ORS 2ND QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            ) AS ors_second_quarter_total,

            /* ORS 3RD QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_july,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_august,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_september,

            /* ORS 3RD QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            ) AS ors_third_quarter_total,

            /* ORS 4TH QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_october,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_november,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_ors_december,

            /* ORS 4TH QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'),0)
            ) AS ors_forth_quarter_total,

            /* WHOLE YEAR PS */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_co_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0)) AS total_sub_all,

            (
                -- Revised Allotment
                b.approved_budget + 
                (
                    COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                    COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                    COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                    COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
                )
                -- Minus total_sub_all
                - 
                (
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_direct_co_dt d 
                        JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
                        WHERE d.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND d.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0
                    ) +
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_indirect_ps_dt i 
                        JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
                        WHERE i.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND i.project_title LIKE '%Relocation and Construction of New DOST-FNRI%'), 0
                    )
                )
            ) AS unobligated_balance
             
        FROM tbl_saob_hd a
        JOIN tbl_saob_co_dt b ON a.recid = b.project_id
        LEFT JOIN mst_uacs u ON b.code = u.uacs_code
        WHERE a.project_title LIKE '%Relocation and Construction of New DOST-FNRI%' AND a.current_year = ?

        UNION ALL

        /* ================= Administration of Personnel Benefits ================= */
        /* ================= PS DATA ================= */
        SELECT
            a.program_title, u.allotment_class, b.object_code,
            b.particulars AS sub_object_code, b.code AS uacs_code, b.approved_budget,
            /* TOTAL PER QUARTER */
            b.january_revision, b.february_revision, b.march_revision,
            (COALESCE(b.january_revision,0)
            + COALESCE(b.february_revision,0)
            + COALESCE(b.march_revision,0)) AS first_quarter_total,
            b.april_revision, b.may_revision, b.june_revision,
            (COALESCE(b.april_revision,0)
            + COALESCE(b.may_revision,0)
            + COALESCE(b.june_revision,0)) AS second_quarter_total,
            b.july_revision, b.august_revision, b.september_revision,
            (COALESCE(b.july_revision,0)
            + COALESCE(b.august_revision,0)
            + COALESCE(b.september_revision,0)) AS third_quarter_total,
            b.october_revision, b.november_revision, b.december_revision,
            (COALESCE(b.october_revision,0)
            + COALESCE(b.november_revision,0)
            + COALESCE(b.december_revision,0)) AS forth_quarter_total,
            /* TOTAL REALIGNMENT */
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS total_realignment,
            /* REVISED ALLOTMENT */
            b.approved_budget + 
            (
                COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
            ) AS revised_allotment,
            /* ORS 1ST QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_january,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_february,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_march,

            /* ORS 1ST QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-01-01' AND LAST_DAY('$year-01-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-02-01' AND LAST_DAY('$year-02-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-03-01' AND LAST_DAY('$year-03-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            ) AS ors_first_quarter_total,

            /* ORS 2ND QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_april,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_may,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_june,

            /* ORS 2ND QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-04-01' AND LAST_DAY('$year-04-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-05-01' AND LAST_DAY('$year-05-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-06-01' AND LAST_DAY('$year-06-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            ) AS ors_second_quarter_total,

            /* ORS 3RD QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_july,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_august,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_september,

            /* ORS 3RD QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-07-01' AND LAST_DAY('$year-07-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-08-01' AND LAST_DAY('$year-08-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-09-01' AND LAST_DAY('$year-09-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            ) AS ors_third_quarter_total,

            /* ORS 4TH QUARTER */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_october,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_november,
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01') AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_ors_december,

            /* ORS 4TH QUARTER TOTAL */
            (
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-10-01' AND LAST_DAY('$year-10-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-11-01' AND LAST_DAY('$year-11-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d 
            JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND d.project_title LIKE '%Administration of Personnel Benefits%'),0)
            +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_mooe_dt i 
            JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars 
            AND hd.ors_date BETWEEN '$year-12-01' AND LAST_DAY('$year-12-01')
            AND i.project_title LIKE '%Administration of Personnel Benefits%'),0)
            ) AS ors_forth_quarter_total,

            /* WHOLE YEAR PS */
            (COALESCE((SELECT SUM(amount) FROM tbl_ors_direct_ps_dt d JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
            WHERE d.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0) +
            COALESCE((SELECT SUM(amount) FROM tbl_ors_indirect_ps_dt i JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
            WHERE i.sub_object_code = b.particulars AND hd.ors_date >= ? AND hd.ors_date <= ? AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0)) AS total_sub_all,

            (
                -- Revised Allotment
                b.approved_budget + 
                (
                    COALESCE(b.january_revision,0) + COALESCE(b.february_revision,0) + COALESCE(b.march_revision,0) +
                    COALESCE(b.april_revision,0) + COALESCE(b.may_revision,0) + COALESCE(b.june_revision,0) +
                    COALESCE(b.july_revision,0) + COALESCE(b.august_revision,0) + COALESCE(b.september_revision,0) +
                    COALESCE(b.october_revision,0) + COALESCE(b.november_revision,0) + COALESCE(b.december_revision,0)
                )
                -- Minus total_sub_all
                - 
                (
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_direct_ps_dt d 
                        JOIN tbl_ors_hd hd ON d.project_id = hd.recid 
                        WHERE d.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND d.project_title LIKE '%Administration of Personnel Benefits%'), 0
                    ) +
                    COALESCE(
                        (SELECT SUM(amount) 
                        FROM tbl_ors_indirect_ps_dt i 
                        JOIN tbl_ors_hd hd ON i.project_id = hd.recid 
                        WHERE i.sub_object_code = b.particulars 
                        AND hd.ors_date >= ? AND hd.ors_date <= ? 
                        AND i.project_title LIKE '%Administration of Personnel Benefits%'), 0
                    )
                )
            ) AS unobligated_balance
             
        FROM tbl_saob_hd a
        JOIN tbl_saob_ps_dt b ON a.recid = b.project_id
        LEFT JOIN mst_uacs u ON b.code = u.uacs_code
        WHERE a.project_title LIKE '%Administration of Personnel Benefits%' AND a.current_year = ?

        ORDER BY 
            /* 1. Primary Sort: Weight the Allotment Class to force PS (0) before MOOE (1) */
            CASE 
                WHEN allotment_class LIKE 'Personnel Services%' THEN 0 
                WHEN allotment_class LIKE 'Maintenance%' THEN 1 
                ELSE 2 
            END ASC, 
            
            /* 2. Secondary Sort: Priority within the PS group */
            CASE 
                WHEN object_code LIKE 'Salaries and Wages%' THEN 0
                WHEN object_code LIKE 'Other Compensation%' THEN 1
                WHEN object_code LIKE 'Personnel Benefit Contributions%' THEN 2
                ELSE 3 
            END ASC,
            
            /* 3. Final Sort: By UACS code for numerical order within groups */
            uacs_code ASC;
        ";

        /*
        |--------------------------------------------------------------------------
        | Bindings (Order MUST match ? sequence in SQL)
        |--------------------------------------------------------------------------
        */
        $bindings = [
            // --- GF PS SECTION ---

            $year_start, $year_end, // Whole Year Direct/Indirect PS
            $year_start, $year_end,
            // Current Year PS

            $year_start, $year_end, // Whole Year Direct/Indirect PS
            $year_start, $year_end,
            $year,                  // Current Year PS

            // --- GF MOOE SECTION ---
            $year_start, $year_end, // Whole Year Direct/Indirect MOOE
            $year_start, $year_end,            // Current Year MOOE

            $year_start, $year_end, // Whole Year Direct/Indirect PS
            $year_start, $year_end,
            $year,                  // Current Year PS

            // --- CO RELOCATION SECTION ---
            $year_start, $year_end, // Whole Year Direct/Indirect MOOE
            $year_start, $year_end,            // Current Year MOOE

            $year_start, $year_end, // Whole Year Direct/Indirect PS
            $year_start, $year_end,
            $year,                  // Current Year PS

            // --- PS ADMINISTRATION SECTION ---
            $year_start, $year_end, // Whole Year Direct/Indirect MOOE
            $year_start, $year_end,            // Current Year MOOE

            $year_start, $year_end, // Whole Year Direct/Indirect PS
            $year_start, $year_end,
            $year,                  // Current Year PS
        ];

        /*
        |--------------------------------------------------------------------------
        | Execute Query
        |--------------------------------------------------------------------------
        */
        $query = $this->db->query($sql, $bindings);

        /*
        |--------------------------------------------------------------------------
        | CSV Export
        |--------------------------------------------------------------------------
        */
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="SAOB_REPORT_'.$from.'_to_'.$to.'.csv"');

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, [
        'Program Title',
        'Allotment Class',
        'Object Code',
        'Sub Object Code',
        'UACS Code',
        'Approved Budget',

        'Realignment-January',
        'Realignment-February',
        'Realignment-March',
        '1st Quarter',

        'Realignment-April',
        'Realignment-May',
        'Realignment-June',
        '2nd Quarter',

        'Realignment-July',
        'Realignment-August',
        'Realignment-September',
        '3rd Quarter',

        'Realignment-October',
        'Realignment-November',
        'Realignment-December',
        '4th Quarter',

        'Realignment',
        'Revised Allotment',

        'ORS January',
        'ORS February',
        'ORS March',
        '1st Quarter',

        'ORS April',
        'ORS May',
        'ORS June',
        '2nd Quarter',

        'ORS July',
        'ORS August',
        'ORS September',
        '3rd Quarter',

        'ORS October',
        'ORS November',
        'ORS December',
        '4th Quarter',
        'Obligation To Date',
        'Unobligated Balance'
        ]);

        while ($row = $query->getUnbufferedRow('array')) {
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    private function loadMainView() {

        //budget table dt fetching
        $budgetdtquery = $this->db->query("
        SELECT 
            a.`recid`,
            a.`trxno`,
            a.`project_title`,
            a.`responsibility_code`,
            a.`fund_cluster_code`,
            a.`division_name`,
            a.`is_pending`,
            a.`is_approved`,
            a.`is_disapproved`,
            a.`added_at`,
            (
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_ps_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_mooe_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_indirect_co_dt` WHERE project_id = a.recid), 0) +
                IFNULL((SELECT SUM(`approved_budget`) FROM `tbl_budget_direct_co_dt` WHERE project_id = a.recid), 0)
            ) AS approved_budget
        FROM
            tbl_budget_hd a
        ");
        $budgetdtdata = $budgetdtquery->getResultArray();

        //hd lookup data
        $fundclusterquery = $this->db->query("SELECT `fund_cluster_code` FROM tbl_fundcluster");
        $fundclusterdata = $fundclusterquery->getResultArray();

        $divisionquery = $this->db->query("SELECT `division_name` FROM tbl_division");
        $divisiondata = $divisionquery->getResultArray();

        $psuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Personnel Services' ORDER BY TRIM(sub_object_code) ASC");
        $psuacsdata = $psuacsquery->getResultArray();

        $psobjectquery = $this->db->query("SELECT DISTINCT object_code FROM mst_uacs WHERE allotment_class = 'Personnel Services'  ORDER BY TRIM(object_code) ASC");
        $psobjectdata = $psobjectquery->getResultArray();

        $mooeuacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Maintenance and Other Operating Expenses' ORDER BY TRIM(sub_object_code) ASC");
        $mooeuacsdata = $mooeuacsquery->getResultArray();

        $mooeobjectquery = $this->db->query("SELECT DISTINCT object_code FROM mst_uacs WHERE allotment_class = 'Maintenance and Other Operating Expenses' ORDER BY TRIM(object_code) ASC");
        $mooeobjectdata = $mooeobjectquery->getResultArray();
        $mooeobjectdata[] = ['object_code' => 'General Services'];

        $couacsquery = $this->db->query("SELECT * FROM mst_uacs WHERE allotment_class = 'Capital Outlay' ORDER BY TRIM(sub_object_code) ASC");
        $couacsdata = $couacsquery->getResultArray();

        $coobjectquery = $this->db->query("SELECT DISTINCT object_code FROM mst_uacs WHERE allotment_class = 'Capital Outlay' ORDER BY TRIM(object_code) ASC");
        $coobjectdata = $coobjectquery->getResultArray();

        // $programtitlequery = $this->db->query("SELECT program_title FROM tbl_budget_hd WHERE fund_cluster_code = '01'  GROUP BY program_title ORDER BY recid DESC");
        // $programtitledata = $programtitlequery->getResultArray();

        $saobhdquery = $this->db->query("SELECT * FROM tbl_saob_hd ORDER BY recid DESC");
        $saobhddata = $saobhdquery->getResultArray();


        //reference/project title lookup
        $projectquery = $this->db->query("
        SELECT
            a.`fundcluster_id`,
            b.`fund_cluster_code`,
            a.`division_id`,
            c.`division_name`,
            a.`responsibility_code`,
            a.`project_title`
        FROM
            `tbl_reference_project` a
        JOIN
            `tbl_fundcluster`b
        ON 
            a.fundcluster_id = b.`recid`
        JOIN
            `tbl_division` c
        ON
            a.`division_id` = c.recid
        ORDER BY a.`project_title` ASC
        ");
        $projectdata = $projectquery->getResultArray();


        return view('report/saob-main', [
            'fundclusterdata' => $fundclusterdata,
            'divisiondata' => $divisiondata,
            'psobjectdata' => $psobjectdata,
            'mooeobjectdata' => $mooeobjectdata,
            'coobjectdata' => $coobjectdata,
            'psuacsdata' => $psuacsdata,
            'mooeuacsdata' => $mooeuacsdata,
            'couacsdata' => $couacsdata,
            'budgetdtdata' => $budgetdtdata,
            'projectdata' => $projectdata,
            // 'programtitledata' => $programtitledata,
            'saobhddata' => $saobhddata,
        ]);
    }
    
    
}
