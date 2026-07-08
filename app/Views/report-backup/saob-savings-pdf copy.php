<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$program_title = $this->request->getPostGet('program_title');
$savings_date = $this->request->getPostGet('savings_date');

$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));

// $query = $this->db->query("
// SELECT
//     `recid`,
//     `ppmpno`,
//     `end_user`,
//     `fiscal_year`,
//     `project_title`,
//     `responsibility_code`
// FROM
//     `tbl_ppmp_hd`
// WHERE 
//     `recid` = '$recid'"
// );

// $data = $query->getRowArray();
// $ppmpno = $data['ppmpno'];
// $end_user = $data['end_user'];
// $fiscal_year = $data['fiscal_year'];
// $project_title = $data['project_title'];
// $responsibility_code = $data['responsibility_code'];


class PDF extends \FPDF {
    function Footer() {
        // Position 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);

        // Right-aligned page number
        $this->Cell(330, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }
}


$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('MONTHY STATUS - PRINT');

$pdf->SetXY(10, 8);

$Y = 4;

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 4, 'FAD-PS-003' , 'TRL', 1, 'L');

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 4, 'Department of Science and Technology', 0, 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 4, 'FOOD AND NUTRITION RESEARCH INSTITUTE' , 0, 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(30, 4, 'MONTHLY STATUS OF OPERATIONS FUNDS' , 0, 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(30, 4, 'December 31,2025' , 0, 1, 'L');

//1ST ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(32, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(42, $Y);
$pdf->Cell(36, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(78, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(95, $Y);
$pdf->Cell(23, 3, '' , 'LT', 1, 'C',true);
$pdf->SetFillColor(255, 220, 220);
$pdf->SetXY(118, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(135, $Y);
$pdf->Cell(17, 3, '' , 'T', 1, 'C',true);
$pdf->SetXY(152, $Y);
$pdf->Cell(17, 3, '' , 'T', 1, 'C',true);
$pdf->SetXY(169, $Y);
$pdf->Cell(17, 3, '' , 'T', 1, 'C',true);
$pdf->SetXY(186, $Y);
$pdf->Cell(17, 3, '' , 'RT', 1, 'C',true);
$pdf->SetXY(203, $Y);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(17, 3, 'Revised' , 'LT', 1, 'C',true);
$pdf->SetXY(220, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(237, $Y);
$pdf->Cell(17, 3, '' , 'T', 1, 'C',true);
$pdf->SetXY(254, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(271, $Y);
$pdf->Cell(17, 3, '' , 'LTR', 1, 'C',true);

//2ND ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(42, $Y);
$pdf->Cell(36, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(78, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(95, $Y);
$pdf->Cell(23, 3, '' , 'L', 1, 'C',true);
$pdf->SetFillColor(255, 220, 220);
$pdf->SetXY(118, $Y);
$pdf->Cell(17, 3, '' , 'LB', 1, 'C',true);
$pdf->SetXY(135, $Y);
$pdf->Cell(17, 3, '' , 'B', 1, 'C',true);
$pdf->SetXY(152, $Y);
$pdf->Cell(17, 3, 'Savings' , 'B', 1, 'C',true);
$pdf->SetXY(169, $Y);
$pdf->Cell(17, 3, '' , 'B', 1, 'C',true);
$pdf->SetXY(186, $Y);
$pdf->Cell(17, 3, '' , 'B', 1, 'C',true);
$pdf->SetXY(203, $Y);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(17, 3, 'Allotment' , 'LB', 1, 'C',true);
$pdf->SetXY(220, $Y);
$pdf->Cell(34, 3, 'Obligations' , 'LB', 1, 'C',true);
$pdf->SetXY(254, $Y);
$pdf->Cell(17, 3, '' , 'LB', 1, 'C',true);
$pdf->SetXY(271, $Y);
$pdf->Cell(17, 3, '' , 'LR', 1, 'C',true);

//3RD ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(42, $Y);
$pdf->Cell(36, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(78, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(95, $Y);
$pdf->Cell(23, 3, '' , 'LT', 1, 'C',true);
$pdf->SetFillColor(255, 220, 220);
$pdf->SetXY(118, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(135, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(152, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(169, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(186, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(203, $Y);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(220, $Y);
$pdf->Cell(34, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(237, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(254, $Y);
$pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
$pdf->SetXY(271, $Y);
$pdf->Cell(17, 3, '' , 'LRT', 1, 'C',true);

//3RD ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(42, $Y);
$pdf->Cell(36, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(78, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(95, $Y);
$pdf->Cell(23, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(118, $Y);
$pdf->SetFillColor(255, 220, 220);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(135, $Y);
$pdf->Cell(17, 3, 'Other Expenses' , 'L', 1, 'C',true);
$pdf->SetXY(152, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(169, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(186, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(203, $Y);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(220, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(237, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(254, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(271, $Y);
$pdf->Cell(17, 3, 'Percentage' , 'LR', 1, 'C',true);

//4th ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(42, $Y);
$pdf->Cell(36, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(78, $Y);
$pdf->Cell(17, 3, 'Project' , 'L', 1, 'C',true);
$pdf->SetXY(95, $Y);
$pdf->Cell(23, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(118, $Y);
$pdf->SetFillColor(255, 220, 220);
$pdf->Cell(17, 3, 'Declared' , 'L', 1, 'C',true);
$pdf->SetXY(135, $Y);
$pdf->Cell(17, 3, 'Charged to' , 'L', 1, 'C',true);
$pdf->SetXY(152, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(169, $Y);
$pdf->Cell(17, 3, 'Total' , 'L', 1, 'C',true);
$pdf->SetXY(186, $Y);
$pdf->Cell(17, 3, 'Balance of' , 'L', 1, 'C',true);
$pdf->SetXY(203, $Y);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(17, 3, 'Revised' , 'L', 1, 'C',true);
$pdf->SetXY(220, $Y);
$pdf->Cell(17, 3, 'This month' , 'L', 1, 'C',true);
$pdf->SetXY(237, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(254, $Y);
$pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
$pdf->SetXY(271, $Y);
$pdf->Cell(17, 3, 'of' , 'LR', 1, 'C',true);

//5th ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(32, 3, 'Responsibility Center' , 'BL', 1, 'C',true);
$pdf->SetXY(42, $Y);
$pdf->Cell(36, 3, 'Program/Project Title' , 'BL', 1, 'C',true);
$pdf->SetXY(78, $Y);
$pdf->Cell(17, 3, 'Leader' , 'BL', 1, 'C',true);
$pdf->SetXY(95, $Y);
$pdf->Cell(23, 3, 'Allotment' , 'BL', 1, 'C',true);
$pdf->SetXY(118, $Y);
$pdf->SetFillColor(255, 220, 220);
$pdf->Cell(17, 3, 'Savings' , 'BL', 1, 'C',true);
$pdf->SetXY(135, $Y);
$pdf->Cell(17, 3, 'Savings' , 'BL', 1, 'C',true);
$pdf->SetXY(152, $Y);
$pdf->Cell(17, 3, 'CNA' , 'BL', 1, 'C',true);
$pdf->SetXY(169, $Y);
$pdf->Cell(17, 3, 'Obligations' , 'BL', 1, 'C',true);
$pdf->SetXY(186, $Y);
$pdf->Cell(17, 3, 'Savings' , 'BL', 1, 'C',true);
$pdf->SetXY(203, $Y);
$pdf->SetFillColor(220, 235, 255);
$pdf->Cell(17, 3, 'Allotment' , 'BL', 1, 'C',true);
$pdf->SetXY(220, $Y);
$pdf->Cell(17, 3, 'December' , 'BL', 1, 'C',true);
$pdf->SetXY(237, $Y);
$pdf->Cell(17, 3, 'To date' , 'BL', 1, 'C',true);
$pdf->SetXY(254, $Y);
$pdf->Cell(17, 3, 'Balance' , 'BL', 1, 'C',true);
$pdf->SetXY(271, $Y);
$pdf->Cell(17, 3, 'utilization' , 'BLR', 1, 'C',true);


// 6th ROW
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6);

// Project Title (draw first to know height)
$pdf->SetXY(42, $Y);
$pdf->MultiCell(
    53,
    3,
    'Project Title value value value value value value value value value value value',
    'BL',
    'L'
);

// Get height used by multicell
$rowHeight = $pdf->GetY() - $Y;

// Date (same height)
$pdf->SetXY(10, $Y);
$pdf->Cell(32, $rowHeight, 'II.a.1', 'BL', 0, 'C');

// Other columns
$pdf->SetXY(95, $Y);
$pdf->Cell(23, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(118, $Y);
$pdf->SetFillColor(255, 220, 220);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(135, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(152, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(169, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(186, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(203, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(220, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(237, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(254, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BL', 0, 'C');

$pdf->SetXY(271, $Y);
$pdf->Cell(17, $rowHeight, '0', 'BLR', 0, 'C');

// Move to next row
$pdf->SetY($Y + $rowHeight);



$pdf->SetY($pdf->GetY());

$query = $this->db->query("
SELECT
    svngs.`recid`,
    svngs.`project_id`,
    svngs.`program_title`,
    svngs.`project_title`,
    svngs.`responsibility_code`,
    svngs.`project_leader`,
    svngs.`declared_savings`,
    svngs.`other_expenses`,
    svngs.`cna`,
    svngs.`total_obligations`,
    svngs.`balance_savings`,
    svngs.`savings_date`,
    svngs.`added_by`,
    svngs.`added_at`,
    (
        COALESCE((
            SELECT SUM(dps.approved_budget)
            FROM tbl_budget_direct_ps_dt dps
            JOIN tbl_budget_hd hd
            ON dps.project_id = hd.recid
            WHERE hd.project_title = svngs.project_title
        ),0)

        + COALESCE((
            SELECT SUM(idps.approved_budget)
            FROM tbl_budget_indirect_ps_dt idps
            JOIN tbl_budget_hd hd
            ON idps.project_id = hd.recid
            WHERE hd.project_title = svngs.project_title
        ),0)

        + COALESCE((
            SELECT SUM(dmooe.approved_budget)
            FROM tbl_budget_direct_mooe_dt dmooe
            JOIN tbl_budget_hd hd
            ON dmooe.project_id = hd.recid
            WHERE hd.project_title = svngs.project_title
        ),0)

        + COALESCE((
            SELECT SUM(idmooe.approved_budget)
            FROM tbl_budget_indirect_mooe_dt idmooe
            JOIN tbl_budget_hd hd
            ON idmooe.project_id = hd.recid
            WHERE hd.project_title = svngs.project_title
        ),0)

        + COALESCE((
            SELECT SUM(dco.approved_budget)
            FROM tbl_budget_direct_co_dt dco
            JOIN tbl_budget_hd hd
            ON dco.project_id = hd.recid
            WHERE hd.project_title = svngs.project_title
        ),0)

        + COALESCE((
            SELECT SUM(idco.approved_budget)
            FROM tbl_budget_indirect_co_dt idco
            JOIN tbl_budget_hd hd
            ON idco.project_id = hd.recid
            WHERE hd.project_title = svngs.project_title
        ),0)

    ) AS allotment,
    (
        COALESCE((
            SELECT SUM(dps.amount)
            FROM tbl_ors_direct_ps_dt dps
            JOIN tbl_ors_hd hd
            ON dps.project_id = hd.recid
            WHERE dps.project_title = svngs.project_title AND hd.ors_date >= '2025-12-01' AND hd.ors_date < '2026-01-01'
        ),0)

        + COALESCE((
            SELECT SUM(idps.amount)
            FROM tbl_ors_indirect_ps_dt idps
            JOIN tbl_ors_hd hd
            ON idps.project_id = hd.recid
            WHERE idps.project_title = svngs.project_title AND hd.ors_date >= '2025-12-01' AND hd.ors_date < '2026-01-01'
        ),0)

        + COALESCE((
            SELECT SUM(dmooe.amount)
            FROM tbl_ors_direct_mooe_dt dmooe
            JOIN tbl_ors_hd hd
            ON dmooe.project_id = hd.recid
            WHERE dmooe.project_title = svngs.project_title AND hd.ors_date >= '2025-12-01' AND hd.ors_date < '2026-01-01'
        ),0)

        + COALESCE((
            SELECT SUM(idmooe.amount)
            FROM tbl_ors_indirect_mooe_dt idmooe
            JOIN tbl_ors_hd hd
            ON idmooe.project_id = hd.recid
            WHERE idmooe.project_title = svngs.project_title AND hd.ors_date >= '2025-12-01' AND hd.ors_date < '2026-01-01'
        ),0)

        + COALESCE((
            SELECT SUM(dco.amount)
            FROM tbl_ors_direct_co_dt dco
            JOIN tbl_ors_hd hd
            ON dco.project_id = hd.recid
            WHERE dco.project_title = svngs.project_title AND hd.ors_date >= '2025-12-01' AND hd.ors_date < '2026-01-01'
        ),0)

        + COALESCE((
            SELECT SUM(idco.amount)
            FROM tbl_ors_indirect_co_dt idco
            JOIN tbl_ors_hd hd
            ON idco.project_id = hd.recid
            WHERE idco.project_title = svngs.project_title AND hd.ors_date >= '2025-12-01' AND hd.ors_date < '2026-01-01'
        ),0)
    ) AS thismonth



FROM tbl_saob_savings svngs
WHERE svngs.program_title = '$program_title';

");

$rw = $query->getResultArray();
$pdf->SetFont('Arial', '', 6);
foreach ($rw as $data) {
    $responsibility_code = $data['responsibility_code'];
    $project_title       = $data['project_title'];   // MULTICELL
    $project_leader      = $data['project_leader'];
    $allotment           = $data['allotment'];
    $declared_savings    = $data['declared_savings'];
    $other_expenses      = $data['other_expenses'];
    $cna                 = $data['cna'];
    $total_obligations   = $data['total_obligations'];
    $balance_savings     = $data['balance_savings'];
    $revised_allotment   = $allotment - $declared_savings;
    $thismonth           = $data['thismonth'];
    $todate              = 0;
    $balance             = 0;
    $utilization         = 0;

    $startY = $pdf->GetY();
    /* ===== PROJECT TITLE (MULTICELL DRIVER) ===== */
    $pdf->SetXY(42, $startY);
    $pdf->MultiCell(36, 3, $project_title, 0, 'L');

    /* ===== ROW HEIGHT ===== */
    $endY = $pdf->GetY();
    $totalRowHeight = $endY - $startY;

    /* ===== DRAW BORDERS ===== */
    $pdf->SetXY(10,  $startY); $pdf->Cell(32, $totalRowHeight, '', 1);
    $pdf->SetXY(42,  $startY); $pdf->Cell(36, $totalRowHeight, '', 1);
    $pdf->SetXY(78,  $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(95,  $startY); $pdf->Cell(23, $totalRowHeight, '', 1);
    $pdf->SetXY(118, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(135, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(152, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(169, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(186, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(203, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(220, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(237, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(254, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);
    $pdf->SetXY(271, $startY); $pdf->Cell(17, $totalRowHeight, '', 1);

    /* ===== BOTTOM-ALIGNED TEXT ===== */
    $bottomPadding = 1;
    $textY = $startY + $totalRowHeight - 5 - $bottomPadding;

    $pdf->SetXY(10,  $textY); $pdf->Cell(32, 5, $responsibility_code, 0, 0, 'C');
    $pdf->SetXY(78,  $textY); $pdf->Cell(17, 5, $project_leader, 0, 0, 'C');
    $pdf->SetXY(95,  $textY); $pdf->Cell(23, 5, number_format($allotment,2), 0, 0, 'R');
    $pdf->SetXY(118, $textY); $pdf->Cell(17, 5, number_format($declared_savings,2), 0, 0, 'R');
    $pdf->SetXY(135, $textY); $pdf->Cell(17, 5, number_format($other_expenses,2), 0, 0, 'R');
    $pdf->SetXY(152, $textY); $pdf->Cell(17, 5, number_format($cna,2), 0, 0, 'R');
    $pdf->SetXY(169, $textY); $pdf->Cell(17, 5, number_format($total_obligations,2), 0, 0, 'R');
    $pdf->SetXY(186, $textY); $pdf->Cell(17, 5, number_format($balance_savings,2), 0, 0, 'R');
    $pdf->SetXY(203, $textY); $pdf->Cell(17, 5, number_format($revised_allotment,2), 0, 0, 'R');
    $pdf->SetXY(220, $textY); $pdf->Cell(17, 5, number_format($thismonth,2), 0, 0, 'R');
    $pdf->SetXY(237, $textY); $pdf->Cell(17, 5, number_format($todate,2), 0, 0, 'R');
    $pdf->SetXY(254, $textY); $pdf->Cell(17, 5, number_format($balance,2), 0, 0, 'R');
    $pdf->SetXY(271, $textY); $pdf->Cell(17, 5, number_format($utilization,2), 0, 0, 'R');

    /* ===== NEXT ROW ===== */
    $pdf->SetY($endY);
}



$pdf->Output();
exit;
?>