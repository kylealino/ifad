<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = '101';
$month = $this->request->getPostGet('month');
$year = $this->request->getPostGet('year');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';

$date_from = '';
$date_to = '';
$og_date_from = '';
$og_date_to = '';

if ($month == 'January') {
    $date_from = $year . '-01-01';
    $date_to = $year . '-02-01';
}elseif ($month == 'February') {
    $date_from = $year . '-02-01';
    $date_to = $year . '-03-01';
}elseif ($month == 'March') {
    $date_from = $year . '-03-01';
    $date_to = $year . '-04-01';
}elseif ($month == 'April') {
    $date_from = $year . '-04-01';
    $date_to = $year . '-05-01';
}elseif ($month == 'May') {
    $date_from = $year . '-05-01';
    $date_to = $year . '-06-01';
}elseif ($month == 'June') {
    $date_from = $year . '-06-01';
    $date_to = $year . '-07-01';
}elseif ($month == 'July') {
    $date_from = $year . '-07-01';
    $date_to = $year . '-08-01';
}elseif ($month == 'August') {
    $date_from = $year . '-08-01';
    $date_to = $year . '-09-01';
}elseif ($month == 'September') {
    $date_from = $year . '-09-01';
    $date_to = $year . '-10-01';
}elseif ($month == 'October') {
    $date_from = $year . '-10-01';
    $date_to = $year . '-11-01';
}elseif ($month == 'November') {
    $date_from = $year . '-11-01';
    $date_to = $year . '-12-01';
}elseif ($month == 'December') {
    $date_from = $year . '-12-01';
    $date_to = $year . '-12-31';
}else{
    $date_from = $year . '-01-01';
    $date_to = $year . '-02-01';
}

if ($month == 'January') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-02-01';
}elseif ($month == 'February') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-03-01';
}elseif ($month == 'March') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-04-01';
}elseif ($month == 'April') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-05-01';
}elseif ($month == 'May') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-06-01';
}elseif ($month == 'June') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-07-01';
}elseif ($month == 'July') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-08-01';
}elseif ($month == 'August') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-09-01';
}elseif ($month == 'September') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-10-01';
}elseif ($month == 'October') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-11-01';
}elseif ($month == 'November') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-12-01';
}elseif ($month == 'December') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-12-31';
}

$query = $this->db->query("
SELECT
    `project_leader`,
    `project_duration`,
    `duration_from`,
    `duration_to`,
    `program_leader`,
    `project_leader`,
    `monitoring_agency`,
    `funding_agency`,
    `collaborating_agencies`,
    `implementing_agency`,
    `with_extension`,
    `extended_from`,
    `extended_to`
FROM
    `tbl_budget_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$project_leader = $data['project_leader'];
$project_duration = $data['project_duration'];
$duration_from = $data['duration_from'];
$duration_to = $data['duration_to'];
$program_leader = $data['program_leader'];
$project_leader = $data['project_leader'];
$monitoring_agency = $data['monitoring_agency'];
$funding_agency = $data['funding_agency'];
$collaborating_agencies = $data['collaborating_agencies'];
$implementing_agency = $data['implementing_agency'];
$with_extension = $data['with_extension'];
$extended_from = $data['extended_from'];
$extended_to = $data['extended_to'];

if ($with_extension == '1') {
    $toYear = date('Y', strtotime($data['extended_to']));
    $cy_range = "CY ". "{$toYear}";
}else{
    $toYear = date('Y', strtotime($data['duration_to']));
    $cy_range = "CY "."{$toYear}";
}

function DrawDottedLine($pdf, $x1, $y1, $x2, $y2, $dotLength = 1, $gap = 1) {
    $totalLength = sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    $dx = ($x2 - $x1) / $totalLength;
    $dy = ($y2 - $y1) / $totalLength;

    $currentLength = 0;
    while ($currentLength < $totalLength) {
        $startX = $x1 + $dx * $currentLength;
        $startY = $y1 + $dy * $currentLength;
        $endX = $x1 + $dx * ($currentLength + $dotLength);
        $endY = $y1 + $dy * ($currentLength + $dotLength);

        $pdf->Line($startX, $startY, $endX, $endY);
        $currentLength += ($dotLength + $gap);
    }
}

class PDF extends \FPDF {
    function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number in center
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }
}


$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages(); // Enables total page number
$pdf->AddPage();
$pdf->SetTitle('SAOB Print');
$pdf->SetFont('Arial', 'B', 16);



$pdf->SetXY(8, 10);

$X = 0;
$Y = 8;
$pdf->SetFont('Arial', 'B', 7.5);
$Y = 4;
$pdf->Cell($X,$Y,'STATEMENT OF ALLOTMENTS, OBLIGATIONS AND BALANCE',0,1,'C');
$pdf->Cell($X,$Y,'As of ' . $month . ' ' . $year,0,1,'C');
$pdf->Cell(0, $Y, '(In Pesos)', 0, 1,'C');

//spacer
$pdf->Cell($X,4,'',0,1,'L');

$pdf->SetFont('Arial', '', 7);

// Program Title (wraps if long, ':' separated)
$pdf->Cell(15, 3.5, 'Department', 0, 0, 'L'); // Label

$pdf->Cell(2, 3.5, ':', 0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->SetFont('Arial', 'B', 7);
$pdf->MultiCell(105, 3.5, 'DEPARTMENT OF SCIENCE AND TECHNOLOGY', 0, 'L');

// Optional: add a small line break or spacing after to separate rows
$pdf->Ln(1);

// Project Title (wraps if long)
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(15, 3.5, 'Agency', 0, 0, 'L');
$pdf->Cell(2, 3.5, ':',0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->SetFont('Arial', 'B', 7);
$pdf->MultiCell(105, 3.5, 'FOOD AND NUTRITION RESEARCH INSTITUTE', 0, 'L');

$Y+= 7;

function printTableHeader($pdf, $month) {
    $Y = $pdf->GetY() + 2; // adjust as needed

    // Reset Y if close to bottom of page
    if ($Y < 270) {

        $Y = 42.5;

    }else{
        $pdf->AddPage();
        $Y = $pdf->GetY()+4.5;
    }

    $pdf->SetXY(10, $Y);

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
    $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
    $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
    $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
    $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
    $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
    $Y+= 3.5;

    $pdf->SetXY(10, $Y);
    $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
    $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
    $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
    $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
    $Y+= 3.5;

    $pdf->SetXY(10, $Y);
    $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
    $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
    $Y+= 3.5;

    $pdf->SetXY(10, $Y);
    $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
    $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
    $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
    $Y+= 3.5;

    $pdf->SetXY(10, $Y);
    $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
    $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
    $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
    $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
    $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
    $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
    $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');
    $Y+= 3.5;

    // $pdf->SetXY(10, $Y);
    // $pdf->SetFont('Arial', 'B', 8);
    // $pdf->Cell(64, 7, 'CURRENT YEAR BUDGET', 'BRL', 0, 'L');
    // $pdf->Cell(22, 7, '', 'BRL', 0, 'C');
    // $pdf->Cell(22, 7, '', 'BRL', 0, 'C');
    // $pdf->Cell(22, 7, '', 'BRL', 0, 'C');
    // $pdf->Cell(22, 7, '', 'BRL', 0, 'C');
    // $pdf->Cell(22, 7, '', 'BRL', 0, 'C');
    // $pdf->Cell(17, 7, '', 'BRL', 0, 'C');

    


    return $Y + 10; // return new Y position
}


$Y = printTableHeader($pdf, $month);


$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';
//CURRENT YEAR BUDGET

$pdf->SetXY(10, $Y -10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(64, 5, 'CURRENT YEAR BUDGET', 'L', 0, 'L');

$pdf->SetXY(74, $Y - 10);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(22, 5, '', 'LR', 1, 'L');

$pdf->SetXY(96, $Y - 10);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(22, 5, '', 'R', 1, 'L');

$pdf->SetXY(118, $Y - 10);
$pdf->Cell(22, 5, '', 'R', 1, 'R'); // This month column

$pdf->SetXY(140, $Y - 10);
$pdf->Cell(22, 5, '', 'R', 1, 'R'); // To date column

$pdf->SetXY(162, $Y - 10);
$pdf->Cell(22, 5, '', 'R', 1, 'R'); // Unobligated column

$pdf->SetXY(184, $Y - 10);
$pdf->Cell(17, 5, '', 'R', 1, 'R'); // Percentage column

$pdf->SetXY(10, $Y -5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(64, 5, 'A. Programs', 'L', 1, 'L');

$pdf->SetXY(74, $Y -5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(22, 5, '', 'LR', 1, 'L');

$pdf->SetXY(96, $Y -5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(22, 5, '', 'R', 1, 'L');

$pdf->SetXY(118, $Y -5);
$pdf->Cell(22, 5, '', 'R', 1, 'R'); // This month column

$pdf->SetXY(140, $Y -5);
$pdf->Cell(22, 5, '', 'R', 1, 'R'); // To date column

$pdf->SetXY(162, $Y -5);
$pdf->Cell(22, 5, '', 'R', 1, 'R'); // Unobligated column

$pdf->SetXY(184, $Y -5);
$pdf->Cell(17, 5, '', 'R', 1, 'R'); // Percentage column

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND code != '50103010-00' AND `code` != '50102990-14' AND `code` != '50104990-06' AND `code` != '50104990-14'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`program_title` like '%General Administration and%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;


//UP TO DATE GRAND TOTAL

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`project_title` LIKE '%General Administration and support%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`project_title` LIKE '%General Administration and support%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}

$general_total_project_budget = $total_project_budget;
$general_thismonth_grand_total = $thismonth_grand_total;
$general_todate_grand_total = $todate_grand_total;
$general_grand_unobligated = $grand_unobligated;
$general_grand_percentage_minus = $grand_percentage_minus;

$Y = $pdf->GetY();
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00' AND `code` != '50102990-14' AND `code` != '50104990-06' AND `code` != '50104990-14') AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`) AS total_co,
        (
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00' AND `code` != '50102990-14' AND `code` != '50104990-06' AND `code` != '50104990-14'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`program_title` like '%General Administration and%' AND a.`current_year`= '$year'
    ORDER BY a.`recid`
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == 'General Administration and Support Services') {
        $program_tagging = 'General Administration and Support';
        $program_like = 'General Administration';
    }elseif ($project_title == 'Relocation and Construction of New DOST-FNRI') {
        $program_tagging = 'Relocation and Construction of New DOST-FNRI';
        $program_like = 'Relocation and Construction';
    }elseif($project_title == 'Administration of Personnel Benefits'){
        $program_tagging = 'Administration of Personnel Benefits';
        $program_like = 'Administration of Personnel';
    }


    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`project_title` like '%$program_tagging%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`project_title` like '%$program_tagging%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.project_title LIKE '%$program_tagging%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.project_title LIKE '%$program_tagging%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`project_title` like '%$program_tagging%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`project_title` like '%$program_tagging%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.project_title LIKE '%$program_tagging%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.project_title LIKE '%$program_tagging%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        GROUP BY
            b.`code`, b.object_code
        ORDER BY 
            CASE 
                WHEN b.object_code = 'Traveling Expenses' THEN 1
                WHEN b.object_code = 'Training and Scholarship Expenses' THEN 2
                WHEN b.object_code = 'Supplies and Materials Expenses' THEN 3
                WHEN b.object_code = 'Utility Expenses' THEN 4
                WHEN b.object_code = 'Communication Expenses ' THEN 5
                WHEN b.object_code = 'Awards/Rewards, Prizes and Indemnities' THEN 6
                WHEN b.object_code = 'Research, Exploration and Development Expenses' THEN 7
                WHEN b.object_code = 'Confidential, Intelligence and Extraordinary Expenses' THEN 8
                WHEN b.object_code = 'Professional Services' THEN 9
                WHEN b.object_code = 'General Services' THEN 10
                WHEN b.object_code = 'Repairs and Maintenance' THEN 11
                WHEN b.object_code = 'Taxes, Insurance Premiums and Other Fees' THEN 12
                WHEN b.object_code = 'Other Maintenance and Operating Expenses' THEN 13
                ELSE 14
            END,
            b.recid, b.particulars;

    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`project_title` like '%$program_tagging%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`project_title` like '%$program_tagging%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.project_title LIKE '%$program_tagging%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.project_title LIKE '%$program_tagging%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    if ($program_tagging == 'General Administration and Support') {
        // First, measure the height needed for program_tagging
        $startY = $Y;
        $pdf->SetXY(15, $Y);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(59, 3.5, $program_tagging, 'R', 'L'); // Measure without border
        $afterProgramY = $pdf->GetY();
        $programHeight = $afterProgramY - $startY;

        // Draw complete row with borders
        $pdf->SetXY(10, $startY);
        $pdf->Cell(5, $programHeight, '', 'L', 0, 'L');
        $pdf->SetXY(15, $startY);
        $pdf->Cell(59, $programHeight, '', 0, 0, 'L');
        $pdf->SetXY(67, $startY);
        $pdf->Cell(29, $programHeight, '', 0, 0, 'C'); // Gap column
        $pdf->SetXY(96, $startY);
        $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C');
        $pdf->SetXY(118, $startY);
        $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C');
        $pdf->SetXY(140, $startY);
        $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C');
        $pdf->SetXY(162, $startY);
        $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C');
        $pdf->SetXY(184, $startY);
        $pdf->Cell(17, $programHeight, '', 'RL', 0, 'C');

        // Draw text and values (centered vertically)
        $middleY = $startY + ($programHeight / 2) - 1.5;
        $pdf->SetXY(15, $middleY);
        $pdf->Cell(52, 3, '', 0, 0, 'R');
        
        $pdf->SetXY(96, $middleY);
        $pdf->Cell(22, 3, number_format($total_project_budget, 2), 'BRL', 0, 'R');
        $pdf->SetXY(118, $middleY);
        $pdf->Cell(22, 3, number_format($thismonth_grand_total, 2), 'BRL', 0, 'R');
        $pdf->SetXY(140, $middleY);
        $pdf->Cell(22, 3, number_format($todate_grand_total, 2), 'BRL', 0, 'R');
        $pdf->SetXY(162, $middleY);
        $pdf->Cell(22, 3, number_format($grand_unobligated, 2), 'BRL', 0, 'R');
        $pdf->SetXY(184, $middleY);
        $pdf->Cell(17, 3, number_format($grand_percentage_minus, 2) . '%', 'BRL', 0, 'R');

        $Y = $afterProgramY;

    } elseif ($project_title == 'Relocation and Construction of New DOST-FNRI') {
        // Measure height for this special case
        $startY = $Y;
        $pdf->SetXY(10, $Y);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(5, 3.5, '', 'TL', 0, 'R');
        $pdf->SetXY(15, $Y);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(59, 3.5, 'Locally-Funder Project', '0', 'L');
        $afterTextY = $pdf->GetY();
        $textHeight = $afterTextY - $startY;

        // Draw borders
        $pdf->SetXY(10, $startY);
        $pdf->Cell(64, $textHeight, '', 'TRL', 0, 'C');

        $pdf->SetXY(74, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Budget column

        $pdf->SetXY(96, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Budget column

        $pdf->SetXY(118, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // This month column

        $pdf->SetXY(140, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // To date column

        $pdf->SetXY(162, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Unobligated column

        $pdf->SetXY(184, $Y);
        $pdf->Cell(17, 5, '', 'TR', 1, 'R'); // Percentage column
        
        // Draw text centered
        $middleY = $startY + ($textHeight / 2) - 1.5;
        $pdf->SetXY(15, $middleY);
        $pdf->Cell(186, 3, '', 0, 0, 'R');

        $Y = $afterTextY;

    } else {

        $startY = $Y;
        $pdf->SetXY(10, $Y);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(5, 3.5, '', 'TL', 0, 'R');
        $pdf->SetXY(15, $Y);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(59, 3.5, $program_tagging, 0, 'L');
        $afterTextY = $pdf->GetY();
        $textHeight = $afterTextY - $startY;

        // Draw borders
        $pdf->SetXY(10, $startY);
        $pdf->Cell(64, $textHeight, '', 'TRL', 0, 'C');

        $pdf->SetXY(74, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Budget column

        $pdf->SetXY(96, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Budget column

        $pdf->SetXY(118, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // This month column

        $pdf->SetXY(140, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // To date column

        $pdf->SetXY(162, $Y);
        $pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Unobligated column

        $pdf->SetXY(184, $Y);
        $pdf->Cell(17, 5, '', 'TR', 1, 'R'); // Percentage column
        
        // Draw text centered
        $middleY = $startY + ($textHeight / 2) - 1.5;
        $pdf->SetXY(15, $middleY);
        $pdf->Cell(186, 3, '', 0, 0, 'R');

        $Y = $afterTextY;
        
    }

    // PROJECT TITLE SECTION (for all cases)
    $startY = $Y;

    // Measure height for project_title
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(59, 3.5, $project_title, 'R', 'L');
    $pdf->SetFont('Arial', '', 8);
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $startY;

    if ($project_title == 'Administration of Personnel Benefits') {
        $pdf->SetXY(10, $startY);
        $pdf->Cell(5, $projectHeight, '', 'BL', 0, 'L');
        $pdf->SetXY(15, $startY);
        $pdf->Cell(70, $projectHeight, '', 'B', 0, 'L');
        $pdf->SetXY(85, $startY);
        $pdf->Cell(11, $projectHeight, '', 'B', 0, 'C');
    }else{
        $pdf->SetXY(10, $startY);
        $pdf->Cell(5, $projectHeight, '', 'L', 0, 'L');
        $pdf->SetXY(15, $startY);
        $pdf->Cell(70, $projectHeight, '', 0, 0, 'L');
        $pdf->SetXY(85, $startY);
        $pdf->Cell(11, $projectHeight, '', 0, 0, 'C');
    }

    // Draw complete row with borders

    $pdf->SetXY(96, $startY);
    $pdf->Cell(22, $projectHeight, '', 'BRL', 0, 'C');
    $pdf->SetXY(118, $startY);
    $pdf->Cell(22, $projectHeight, '', 'BRL', 0, 'C');
    $pdf->SetXY(140, $startY);
    $pdf->Cell(22, $projectHeight, '', 'BRL', 0, 'C');
    $pdf->SetXY(162, $startY);
    $pdf->Cell(22, $projectHeight, '', 'BRL', 0, 'C');
    $pdf->SetXY(184, $startY);
    $pdf->Cell(17, $projectHeight, '', 'BRL', 0, 'C');

    // Draw text and values (centered vertically)
    $middleY = $startY + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(15, $middleY);
    $pdf->Cell(70, 3, '', 0, 0, 'L');

    $pdf->SetXY(96, $middleY);
    $pdf->Cell(22, 3, number_format($total_approved_budget, 2), 0, 0, 'R');
    $pdf->SetXY(118, $middleY);
    $pdf->Cell(22, 3, number_format($total_all_sub_month, 2), 0, 0, 'R');
    $pdf->SetXY(140, $middleY);
    $pdf->Cell(22, 3, number_format($total_all_todate_month, 2), 0, 0, 'R');
    $pdf->SetXY(162, $middleY);
    $pdf->Cell(22, 3, number_format($total_unobligated, 2), 0, 0, 'R');
    $pdf->SetXY(184, $middleY);
    $pdf->Cell(17, 3, number_format($total_percentage_minus, 2) . '%', 0, 0, 'R');

    $Y = $afterProjectY;
    // PROJECT TITLE SECTION (for all cases)
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    
    if ($program_tagging == 'Relocation and Construction of New DOST-FNRI ') {
        $Y = $pdf->GetY();
    }elseif($project_title == 'General Administration and Support Services'){
        $Y = $pdf->GetY()+5;
    }else{
        $Y = $pdf->GetY()+3.5;
    }
    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'  AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >255) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    
    if ($project_title == 'Relocation and Construction of New DOST-FNRI') {
        $Y = $pdf->GetY();
    }else{
        $Y = $pdf->GetY()+4.5;
    }
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            if ($object_code == 'Confidential, Intelligence and Extraordinary Expenses') {
                $object_code = 'Confidential, Intelligence & Extraord. Exps.';
            }
            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >262) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    if ($project_title == 'Relocation and Construction of New DOST-FNRI') {
        $Y = $pdf->GetY()+5;
    }elseif($project_title == 'General Administration and Support Services'){
        $Y = $pdf->GetY()+7;
    }else{
        $Y = $pdf->GetY()+7;
    }
    
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 257) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;
    $ors_total_current_month += $total_all_sub_month;

    $Y = $pdf->GetY() + 5;    

}

// TOTAL CURRENT YEAR BUDGET -------------------------------------------- 
$Y = $pdf->GetY()+5;
$total_curryear_budget = $total_year_ps + $total_year_mooe + $total_year_co;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(191, 3.5, 'Total, General Administration & Support', 'TLR', 1, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, ($total_curryear_budget == 0.00 || !is_numeric($total_curryear_budget)) ? 0.00 : number_format((float)$total_curryear_budget, 2), 'L', 1, 'R');
$pdf->SetXY(118, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(21.8, 3.5, number_format($thismonth_grand_total, 2), 'L', 1, 'R'); // full width usage
$pdf->SetXY(140, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(21.8, 3.5, number_format($todate_grand_total, 2), 'L', 1, 'R'); // full width usage
$pdf->SetXY(162, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(21.8, 3.5, number_format($grand_unobligated, 2), 'L', 1, 'R'); // full width usage
$pdf->SetXY(184, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(17, 3.5, number_format($grand_percentage_minus, 2) . '%', 'L', 1, 'R'); // full width usage

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//---------------------------------------------------- Scientific research and development services on basic ---------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$Y = $pdf->GetY();

$pdf->SetFont('Arial', '', 8);

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 5, 'II. Operations', 'TRL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 5, '', 'TR', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 5, '', 'TR', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 5, '', 'TR', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 5, '', 'TR', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 5, '', 'TR', 1, 'R'); // Percentage column

$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$project_like = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';

$expanding_total_project_budget = 0;
$expanding_ps_grand_total = 0;
$expanding_mooe_grand_total = 0;
$expanding_co_grand_total = 0;
$expanding_thismonth_grand_total = 0;
$expanding_ps_todate_grand_total = 0;
$expanding_mooe_todate_grand_total = 0;
$expanding_co_todate_grand_total = 0;
$expanding_todate_grand_total = 0;
$expanding_grand_unobligated = 0;
$expanding_grand_percentage_minus = 0;

$scientific_total_project_budget = 0;
$scientific_thismonth_grand_total = 0;
$scientific_todate_grand_total = 0;
$scientific_grand_unobligated = 0;
$scientific_grand_percentage_minus = 0;



//CURRENT YEAR BUDGET
$Y = $pdf->GetY() +3.5;

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` and code != '50103010-00' and code != '50102990-14' and code != '50104990-06'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;


//UP TO DATE GRAND TOTAL

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}

//-------------------------------------------------------------------------------------------------- TOTAL EXPANDING THE FNRI'S NUTRIOGENOMICS LABORATORY---------------

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Expanding the FNRI%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$expanding_total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanding_ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanding_mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanding_co_grand_total = $rw['grand_total'];

$expanding_thismonth_grand_total = $expanding_ps_grand_total + $expanding_mooe_grand_total + $expanding_co_grand_total;


//UP TO DATE GRAND TOTAL

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanding_ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanding_mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanding_co_todate_grand_total = $rw['grand_total'];

$expanding_todate_grand_total = $expanding_ps_todate_grand_total + $expanding_mooe_todate_grand_total + $expanding_co_todate_grand_total;
$expanding_grand_unobligated = $expanding_total_project_budget - $expanding_todate_grand_total;
if (!empty($total_project_budget) && $expanding_total_project_budget > 0) {
    $expanding_grand_percentage_minus = ($expanding_todate_grand_total / $expanding_total_project_budget) * 100;
}

$scientific_total_project_budget = $total_project_budget + $expanding_total_project_budget;
$scientific_thismonth_grand_total = $thismonth_grand_total + $expanding_thismonth_grand_total;
$scientific_todate_grand_total = $todate_grand_total + $expanding_todate_grand_total;
$scientific_grand_unobligated = $grand_unobligated + $expanding_grand_unobligated;
$scientific_grand_percentage_minus = ($scientific_todate_grand_total / $scientific_total_project_budget) * 100;


$Y = $pdf->GetY()+3.5;
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` and code != '50103010-00') AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`) AS total_co,
        (
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` and code != '50103010-00'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == "Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition") {
        $program_tagging = 'FOOD AND NUTRITION RESEARCH AND DEVELOPMENT PROGRAM';
        $program_like = 'Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition';
    }


    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$project_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    $Y = $pdf->GetY();
    // PROGRAM TAGGING LOGIC --------------------------------------------------------------------
    $startY = $Y; // Store the starting Y position

    // First, measure the height needed for program_tagging
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(59, 3.5, $program_tagging, 'R', 'L'); // Measure without border
    $afterProgramY = $pdf->GetY();
    $programHeight = $afterProgramY - $Y;

    // Draw complete PROGRAM TAGGING row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $programHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $programHeight, '', 0, 0, 'L'); // Program text column

    // Right-side columns for program tagging
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $programHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $programHeight, '', 'RL', 0, 'C'); // Percentage column

    // Draw program tagging totals (centered vertically)
    $programMiddleY = $Y + ($programHeight / 2) - 1.5;
    $pdf->SetXY(96, $programMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($scientific_total_project_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $programMiddleY);
    $pdf->Cell(22, 3, number_format($scientific_thismonth_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(140, $programMiddleY);
    $pdf->Cell(22, 3, number_format($scientific_todate_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(162, $programMiddleY);
    $pdf->Cell(22, 3, number_format($scientific_grand_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $programMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($scientific_grand_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw program tagging text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // PROJECT TITLE LOGIC --------------------------------------------------------------------
    $Y = $afterProgramY; // Move Y to after program tagging
    $projectStartY = $Y;

    // Measure the height needed for project_title
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(59, 3.5, $project_title, 'R', 'L'); // Measure without border
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $Y;

    // Draw complete PROJECT TITLE row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $projectHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $projectHeight, '', 0, 0, 'L'); // Project text column

    // Right-side columns for project title
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $projectHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $projectHeight, '', 1, 0, 'C'); // Percentage column

    // Draw project title totals (centered vertically)
    $projectMiddleY = $Y + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(96, $projectMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_approved_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_sub_month, 2), 0, 0, 'R');

    $pdf->SetXY(140, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_todate_month, 2), 0, 0, 'R');

    $pdf->SetXY(162, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $projectMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($total_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw project title text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // Update Y position for next content
    $Y = $afterProjectY;

    // Clean up and prepare for next section
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY()+6.3; // Add some spacing before the next section

    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code'  AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'  AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code'  AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >255) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY()+4.5;
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);
            if ($object_code == 'Research, Exploration and Development Expenses' ) {
                $object_code = 'Research, Exploration and Dev. Exps.';
            }
            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY();
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 0, 0, 'C');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'C');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'C');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'C');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'C');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 1, 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 1, 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 1, 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 1, 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;
    $ors_total_current_month += $total_all_sub_month;

    $Y = $pdf->GetY() + 3.5;    

}
$Y = $pdf->GetY() + 7.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(191, 3.5, '', 'TRL', 0, 'C');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//-------------------------- Expanding the FNRI's Nutrigenomics Laboratory: Towards Establishment of a World Class Philippines Nutrigenomics Center ---------------------------
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$project_like = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';
//CURRENT YEAR BUDGET

$Y = $pdf->GetY() +3.5;

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Expanding the FNRI%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;


//UP TO DATE GRAND TOTAL

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanding the FNRI%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanding the FNRI%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanding the FNRI%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}

$Y = $pdf->GetY()+3.5;
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`) AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`) AS total_co,
        (
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Expanding the FNRI%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == "Expanding the FNRI's Nutrigenomics Laboratory: Towards Establishment of a World Class Philippines Nutrigenomics Center") {
        $program_tagging = 'Locally-Funded Project';
        $program_like = 'Expanding the FNRI';
    }


    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$project_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND d.`uacs_code` = b.`code` AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND i.`uacs_code` = b.`code` AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND d.`uacs_code` = b.`code`
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND i.`uacs_code` = b.`code`
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        GROUP BY
        b.particulars,
        b.code
        ORDER BY 
            b.recid, b.particulars



    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    $Y = $pdf->GetY();
    // PROGRAM TAGGING LOGIC --------------------------------------------------------------------
    $startY = $Y; // Store the starting Y position

    // First, measure the height needed for program_tagging
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(59, 3.5, $program_tagging, 'R', 'L'); // Measure without border
    $afterProgramY = $pdf->GetY();
    $programHeight = $afterProgramY - $Y;

    // Draw complete PROGRAM TAGGING row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $programHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $programHeight, '', 0, 0, 'L'); // Program text column

    // Right-side columns for program tagging
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $programHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $programHeight, '', 'TRL', 0, 'C'); // Percentage column

    // Draw program tagging totals (centered vertically)
    $programMiddleY = $Y + ($programHeight / 2) - 1.5;
    $pdf->SetXY(96, $programMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_project_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $programMiddleY);
    $pdf->Cell(22, 3, number_format($thismonth_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(140, $programMiddleY);
    $pdf->Cell(22, 3, number_format($todate_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(162, $programMiddleY);
    $pdf->Cell(22, 3, number_format($grand_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $programMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($grand_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw program tagging text
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // PROJECT TITLE LOGIC --------------------------------------------------------------------
    $Y = $afterProgramY; // Move Y to after program tagging
    $projectStartY = $Y;

    // Measure the height needed for project_title
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(59, 3.5, $project_title, 'R', 'L'); // Measure without border
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $Y;

    // Draw complete PROJECT TITLE row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $projectHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(70, $projectHeight, '', 0, 0, 'L'); // Project text column

    // Right-side columns for project title
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $projectHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $projectHeight, '', 1, 0, 'C'); // Percentage column

    // Draw project title totals (centered vertically)
    $projectMiddleY = $Y + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(96, $projectMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_approved_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_sub_month, 2), 0, 0, 'R');

    $pdf->SetXY(140, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_todate_month, 2), 0, 0, 'R');

    $pdf->SetXY(162, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $projectMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($total_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw project title text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // Update Y position for next content
    $Y = $afterProjectY;

    // Clean up and prepare for next section
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY(); // Add some spacing before the next section

    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code'  AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 1, 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 1, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 1, 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 1, 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 1, 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 1, 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >255) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY()+9.5;
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code'  AND dmooe.`uacs_code` = '$uacs_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND idmooe.`uacs_code` = '$uacs_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND dmooe.`uacs_code` = '$uacs_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND idmooe.`uacs_code` = '$uacs_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY();
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 1, 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 1, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 1, 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 1, 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 1, 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 1, 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;
    $ors_total_current_month += $total_all_sub_month;

    $Y = $pdf->GetY() + 3.5;    

}
$Y = $pdf->GetY() + 7.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(191, 3.5, '', 'TRL', 0, 'C');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//------------------------------------------------------------ NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION ----------------------------------------------------
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$project_like = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';

$nutritional_total_project_budget = 0;
$nutritional_thismonth_grand_total = 0;
$nutritional_todate_grand_total = 0;
$nutritional_grand_unobligated = 0;
$nutritional_grand_percentage_minus = 0;

$expanded_total_project_budget = 0;
$expanded_ps_grand_total = 0;
$expanded_mooe_grand_total = 0;
$expanded_co_grand_total = 0;
$expanded_thismonth_grand_total = 0;
$expanded_ps_todate_grand_total = 0;
$expanded_mooe_todate_grand_total = 0;
$expanded_co_todate_grand_total = 0;
$expanded_todate_grand_total = 0;

//CURRENT YEAR BUDGET
$Y = $pdf->GetY() +3.5;

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Nutritional Assessment and Monitoring on Food and Nutrition%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}

//---------------------------------------------------------------------------------- EXPANDED GRAND TOTAL COMPUTATION ---------------------------------------------------------

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Expanded National Nutrition Survey%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$expanded_total_project_budget = $rw['total_approved_budget'];

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanded_ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanded_mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanded_co_grand_total = $rw['grand_total'];

$expanded_thismonth_grand_total = $expanded_ps_grand_total + $expanded_mooe_grand_total + $expanded_co_grand_total;

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanded_ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanded_mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$expanded_co_todate_grand_total = $rw['grand_total'];

$expanded_todate_grand_total = $expanded_ps_todate_grand_total + $expanded_mooe_todate_grand_total + $expanded_co_todate_grand_total;
$expanded_grand_unobligated = $expanded_total_project_budget - $expanded_todate_grand_total;
if (!empty($expanded_total_project_budget) && $expanded_total_project_budget > 0) {
    $expanded_grand_percentage_minus = ($expanded_todate_grand_total / $expanded_total_project_budget) * 100;
}

$nutritional_total_project_budget = $total_project_budget + $expanded_total_project_budget;
$nutritional_thismonth_grand_total = $thismonth_grand_total + $expanded_thismonth_grand_total;
$nutritional_todate_grand_total = $todate_grand_total + $expanded_todate_grand_total;
$nutritional_grand_unobligated = $grand_unobligated + $expanded_grand_unobligated;
$nutritional_grand_percentage_minus = ($nutritional_todate_grand_total / $nutritional_total_project_budget) * 100;

$Y = $pdf->GetY()+3.5;
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00') AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`) AS total_co,
        (
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND a.`current_year` = '$year'
    ORDER BY a.`recid` 
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == "Nutritional Assessment and Monitoring on Food and Nutrition") {
        $program_tagging = 'NUTRITIONAL ASSESSMENT AND MONITORING PROGRAM';
        $program_like = 'Nutritional Assessment and Monitoring on Food and Nutrition';
    }


    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$project_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    $Y = $pdf->GetY();
    // PROGRAM TAGGING LOGIC --------------------------------------------------------------------
    $startY = $Y; // Store the starting Y position

    // First, measure the height needed for program_tagging
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(59, 3.5, $program_tagging, 'R', 'L'); // Measure without border
    $afterProgramY = $pdf->GetY();
    $programHeight = $afterProgramY - $Y;

    // Draw complete PROGRAM TAGGING row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $programHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $programHeight, '', 0, 0, 'L'); // Program text column

    // Right-side columns for program tagging
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $programHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $programHeight, '', 'TRL', 0, 'C'); // Percentage column

    // Draw program tagging totals (centered vertically)
    $programMiddleY = $Y + ($programHeight / 2) - 1.5;
    $pdf->SetXY(96, $programMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($nutritional_total_project_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $programMiddleY);
    $pdf->Cell(22, 3, number_format($nutritional_thismonth_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(140, $programMiddleY);
    $pdf->Cell(22, 3, number_format($nutritional_todate_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(162, $programMiddleY);
    $pdf->Cell(22, 3, number_format($nutritional_grand_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $programMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($nutritional_grand_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw program tagging text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // PROJECT TITLE LOGIC --------------------------------------------------------------------
    $Y = $afterProgramY; // Move Y to after program tagging
    $projectStartY = $Y;

    // Measure the height needed for project_title
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(59, 3.5, $project_title, 'R', 'L'); // Measure without border
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $Y;

    // Draw complete PROJECT TITLE row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $projectHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $projectHeight, '', 0, 0, 'L'); // Project text column

    // Right-side columns for project title
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $projectHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $projectHeight, '', 1, 0, 'C'); // Percentage column

    // Draw project title totals (centered vertically)
    $projectMiddleY = $Y + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(96, $projectMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_approved_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_sub_month, 2), 0, 0, 'R');

    $pdf->SetXY(140, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_todate_month, 2), 0, 0, 'R');

    $pdf->SetXY(162, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $projectMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($total_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw project title text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // Update Y position for next content
    $Y = $afterProjectY;

    // Clean up and prepare for next section
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY()+2.5; // Add some spacing before the next section

    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'  AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY() +4.5;
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >265) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY();
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 1, 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 1, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'BLR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;
    $ors_total_current_month += $total_all_sub_month;

    $Y = $pdf->GetY() + 3.5;    

}
$Y = $pdf->GetY() + 7.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(191, 3.5, '', 'TRL', 0, 'C');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//------------------------------------------------------------ NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION ----------------------------------------------------
//---------------------------------------------------------------------- Expanded National Nutrition Survey -------------------------------------------------------------------------
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$project_like = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';
//CURRENT YEAR BUDGET

$Y = $pdf->GetY() +3.5;

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Expanded National Nutrition Survey%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;


//UP TO DATE GRAND TOTAL

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Expanded National Nutrition Survey%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Expanded National Nutrition Survey%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}

$Y = $pdf->GetY()+3.5;
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`) AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`) AS total_co,
        (
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Expanded National Nutrition Survey%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == "Expanded National Nutrition Survey") {
        $program_tagging = 'Locally-Funded Project';
        $program_like = 'Expanded National Nutrition Survey';
    }


    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$project_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    $Y = $pdf->GetY();
    // PROGRAM TAGGING LOGIC --------------------------------------------------------------------
    $startY = $Y; // Store the starting Y position

    // First, measure the height needed for program_tagging
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(59, 3.5, $program_tagging, 'R', 'L'); // Measure without border
    $afterProgramY = $pdf->GetY();
    $programHeight = $afterProgramY - $Y;

    // Draw complete PROGRAM TAGGING row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $programHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $programHeight, '', 0, 0, 'L'); // Program text column

    // Right-side columns for program tagging
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $programHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $programHeight, '', 1, 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $programHeight, '', 1, 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $programHeight, '', 1, 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $programHeight, '', 1, 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $programHeight, '', 1, 0, 'C'); // Percentage column

    // Draw program tagging totals (centered vertically)
    $programMiddleY = $Y + ($programHeight / 2) - 1.5;
    $pdf->SetXY(96, $programMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_project_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $programMiddleY);
    $pdf->Cell(22, 3, number_format($thismonth_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(140, $programMiddleY);
    $pdf->Cell(22, 3, number_format($todate_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(162, $programMiddleY);
    $pdf->Cell(22, 3, number_format($grand_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $programMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($grand_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw program tagging text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // PROJECT TITLE LOGIC --------------------------------------------------------------------
    $Y = $afterProgramY; // Move Y to after program tagging
    $projectStartY = $Y;

    // Measure the height needed for project_title
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(59, 3.5, $project_title, 'R', 'L'); // Measure without border
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $Y;

    // Draw complete PROJECT TITLE row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $projectHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(70, $projectHeight, '', 0, 0, 'L'); // Project text column

    // Right-side columns for project title
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $projectHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $projectHeight, '', 1, 0, 'C'); // Percentage column

    // Draw project title totals (centered vertically)
    $projectMiddleY = $Y + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(96, $projectMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_approved_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_sub_month, 2), 0, 0, 'R');

    $pdf->SetXY(140, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_todate_month, 2), 0, 0, 'R');

    $pdf->SetXY(162, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $projectMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($total_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw project title text
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // Update Y position for next content
    $Y = $afterProjectY;

    // Clean up and prepare for next section
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY(); // Add some spacing before the next section

    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'  AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 1, 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 1, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 1, 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 1, 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 1, 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 1, 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >255) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY();
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'C');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY();
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 1, 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 1, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 1, 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 1, 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 1, 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 1, 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, $month_amount, 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, $year_amount, 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;
    $ors_total_current_month += $total_all_sub_month;

    $Y = $pdf->GetY() + 3.5;    

}
$Y = $pdf->GetY() + 7.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(191, 3.5, '', 'TRL', 0, 'C');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//---------------------------------------------------------- FOOD AND NUTRITION TECHNOLOGY AND KNOWLEDGE DIFFUSION PROGRAM ----------------------------------------------------
//------------------------------------------------------------------- Technical Services on Food and Nutrition ----------------------------------------------------------------
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$project_like = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';

$foodnutri_total_project_budget = 0;
$foodnutri_thismonth_grand_total = 0;
$foodnutri_todate_grand_total = 0;
$foodnutri_grand_unobligated = 0;
$foodnutri_grand_percentage_minus = 0;

//CURRENT YEAR BUDGET
$Y = $pdf->GetY() +3.5;

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Technical Services on Food and Nutrition%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Technical Services on Food and Nutrition%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Technical Services on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND d.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
                AND i.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Technical Services on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND i.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Technical Services on Food and Nutrition%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND i.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Technical Services on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`program_title` LIKE '%Technical Services on Food and Nutrition%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.project_title LIKE '%Technical Services on Food and Nutrition%' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}

$foodnutri_total_project_budget = $total_project_budget;
$foodnutri_thismonth_grand_total = $thismonth_grand_total;
$foodnutri_todate_grand_total = $todate_grand_total;
$foodnutri_grand_unobligated = $grand_unobligated;
$foodnutri_grand_percentage_minus = $grand_percentage_minus;

$Y = $pdf->GetY()+3.5;
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00') AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`) AS total_co,
        (
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE 
        a.`project_title` like '%Technical Services on Food and Nutrition%' AND a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == "Technical Services on Food and Nutrition") {
        $program_tagging = $program_title;
        $program_like = 'Technical Services on Food and Nutrition';
    }

    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$project_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            b.approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND d.`program_title` like '%$program_like%'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND i.`program_title` like '%$program_like%'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.program_title LIKE '%$program_like%'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.sub_object_code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.program_title LIKE '%$program_like%'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.project_id = '$recid'
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    $Y = $pdf->GetY();
    // PROGRAM TAGGING LOGIC --------------------------------------------------------------------
    $startY = $Y; // Store the starting Y position

    // First, measure the height needed for program_tagging
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(59, 3.5, $program_tagging, 'R', 'L'); // Measure without border
    $afterProgramY = $pdf->GetY();
    $programHeight = $afterProgramY - $Y;

    // Draw complete PROGRAM TAGGING row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $programHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $programHeight, '', 0, 0, 'L'); // Program text column

    // Right-side columns for program tagging
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $programHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $programHeight, '', 'TRL', 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $programHeight, '', 'TRL', 0, 'C'); // Percentage column

    // Draw program tagging totals (centered vertically)
    $programMiddleY = $Y + ($programHeight / 2) - 1.5;
    $pdf->SetXY(96, $programMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_project_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $programMiddleY);
    $pdf->Cell(22, 3, number_format($thismonth_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(140, $programMiddleY);
    $pdf->Cell(22, 3, number_format($todate_grand_total, 2), 0, 0, 'R');

    $pdf->SetXY(162, $programMiddleY);
    $pdf->Cell(22, 3, number_format($grand_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $programMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($grand_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw program tagging text
    $pdf->SetXY(15, $Y + 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // PROJECT TITLE LOGIC --------------------------------------------------------------------
    $Y = $afterProgramY; // Move Y to after program tagging
    $projectStartY = $Y;

    // Measure the height needed for project_title
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(59, 3.5, $project_title, 'R', 'L'); // Measure without border
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $Y;

    // Draw complete PROJECT TITLE row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $projectHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(59, $projectHeight, '', 0, 0, 'L'); // Project text column

    // Right-side columns for project title
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $projectHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $projectHeight, '', 1, 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $projectHeight, '', 1, 0, 'C'); // Percentage column

    // Draw project title totals (centered vertically)
    $projectMiddleY = $Y + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(96, $projectMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, number_format($total_approved_budget, 2), 0, 0, 'R');

    $pdf->SetXY(118, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_sub_month, 2), 0, 0, 'R');

    $pdf->SetXY(140, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_all_todate_month, 2), 0, 0, 'R');

    $pdf->SetXY(162, $projectMiddleY);
    $pdf->Cell(22, 3, number_format($total_unobligated, 2), 0, 0, 'R');

    $pdf->SetXY(184, $projectMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, number_format($total_percentage_minus, 2) . '%', 0, 0, 'R');

    // Draw project title text
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // Update Y position for next content
    $Y = $afterProjectY;

    // Clean up and prepare for next section
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY(); // Add some spacing before the next section

    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'  AND dps.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idps.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >263) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY() +5;
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dmooe.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idmooe.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY();
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND dco.`program_title` like '%$program_like%'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`sub_object_code` = '$sub_object_code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to' AND idco.`program_title` like '%$program_like%'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', 'B', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'R');

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 1, 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 1, 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 1, 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 1, 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >260) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 0, 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 1, 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;
    $ors_total_current_month += $total_all_sub_month;

    $Y = $pdf->GetY() + 3.5;    

}

$subtotal_total_project_budget = 0;
$subtotal_thismonth_grand_total = 0;
$subtotal_todate_grand_total = 0;
$subtotal_grand_unobligated = 0;
$subtotal_grand_percentage_minus = 0;

$totalprogram_total_project_budget = 0;
$totalprogram_thismonth_grand_total = 0;
$totalprogram_todate_grand_total = 0;
$totalprogram_grand_unobligated = 0;
$totalprogram_grand_percentage_minus = 0;


$subtotal_total_project_budget = $scientific_total_project_budget + $nutritional_total_project_budget + $foodnutri_total_project_budget;
$subtotal_thismonth_grand_total = $scientific_thismonth_grand_total + $nutritional_thismonth_grand_total + $foodnutri_thismonth_grand_total;
$subtotal_todate_grand_total = $scientific_todate_grand_total + $nutritional_todate_grand_total + $foodnutri_todate_grand_total;
$subtotal_grand_unobligated = $scientific_grand_unobligated + $nutritional_grand_unobligated + $foodnutri_grand_unobligated;
$subtotal_grand_percentage_minus = ($subtotal_todate_grand_total / $subtotal_total_project_budget) * 100;


$totalprogram_total_project_budget = $general_total_project_budget + $subtotal_total_project_budget;
$totalprogram_thismonth_grand_total = $general_thismonth_grand_total + $subtotal_thismonth_grand_total;
$totalprogram_todate_grand_total = $general_todate_grand_total + $subtotal_todate_grand_total;
$totalprogram_grand_unobligated = $general_grand_unobligated + $subtotal_grand_unobligated;
$totalprogram_grand_percentage_minus = ($totalprogram_todate_grand_total / $totalprogram_total_project_budget) * 100;

//----------------------------------------------------------------- SUB TOTAL OPERATIONS -----------------------------------------------------------------------------------
$Y = $pdf->GetY() + 7;  
$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, 'Sub-Total, Operations', 'BRL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'BRL', 0, 'R'); // UACS CODE

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($subtotal_total_project_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($subtotal_thismonth_grand_total,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($subtotal_todate_grand_total,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($subtotal_grand_unobligated,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($subtotal_grand_percentage_minus, 2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- TOTAL PROGRAMS AND ACTIVITIES -----------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, 'Total Program and Activities', 'BRL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'BRL', 0, 'R'); // UACS CODE

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_total_project_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_thismonth_grand_total,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_todate_grand_total,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_grand_unobligated,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($totalprogram_grand_percentage_minus, 2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- TOTAL CURRENT YEAR APPROPRIATIONS -----------------------------------------------------------=------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'TOTAL CURRENT YEAR APPROPRIATIONS', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'BRL', 0, 'R'); // UACS CODE

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_total_project_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_thismonth_grand_total,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_todate_grand_total,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($totalprogram_grand_unobligated,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($totalprogram_grand_percentage_minus, 2) . '%', 'BRL', 0, 'R'); // Percentage column


$Y = printTableHeader($pdf, $month);
//----------------------------------------------------------------- SPECIAL PURPOSE FUND MPBF  (SARO-BMB-F-25-0014437 PBB FY 2023)---------------------------------------------------------------------------------
$Y = $pdf->GetY();
//SPF TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        (SELECT sub_object_code FROM mst_uacs WHERE a.`code` = `uacs_code` LIMIT 1) AS `sub_object_code`,
        (SELECT object_code FROM mst_uacs WHERE uacs_code = '50102990-14' AND object_code = 'General Administration And Support' LIMIT 1) AS `object_code`,
        COALESCE(a.`approved_budget`,0.00) approved_budget,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
            ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50102990-14'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");
$rw1 = $query->getRowArray();
if ($rw1) {
    $spf1_particulars = $rw1['particulars'];
    $spf1_code = $rw1['code'];
    $spf1_approved_budget = $rw1['approved_budget'];
    $spf1_thismonth_amount = $rw1['thismonth_amount'];
    $spf1_todate_amount = $rw1['todate_amount'];
    $spf1_sub_object_code = $rw1['sub_object_code'];
    $spf1_object_code = $rw1['object_code'];
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}else{
    $spf1_particulars = 'Performance-Based Bonus(SARO-BMB-F-25-0014437 PBB FY 2023)';
    $spf1_code = '50102990-14';
    $spf1_approved_budget = 0.00;
    $spf1_thismonth_amount = 0.00;
    $spf1_todate_amount = 0.00;
    $spf1_sub_object_code = '';
    $spf1_object_code = 'General Administration and Support';
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}

$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        (SELECT sub_object_code FROM mst_uacs WHERE a.`code` = `uacs_code` LIMIT 1) AS `sub_object_code`,
        (SELECT object_code FROM mst_uacs WHERE uacs_code = '50102990-14' AND object_code = 'General Administration And Support' LIMIT 1) AS `object_code`,
        COALESCE(a.`approved_budget`,0.00) approved_budget,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
            ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50104990-06'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");

$rw2 = $query->getRowArray();
if ($rw2) {
    $spf2_particulars = $rw2['particulars'];
    $spf2_code = $rw2['code'];
    $spf2_approved_budget = $rw2['approved_budget'];
    $spf2_thismonth_amount = $rw2['thismonth_amount'];
    $spf2_todate_amount = $rw2['todate_amount'];
    $spf2_sub_object_code = $rw2['sub_object_code'];
    $spf2_object_code = $rw2['object_code'];
    $spf2_unobligated_amount = $spf2_approved_budget - $spf2_todate_amount;
}else{
    $spf2_particulars = 'Lump-sum for Compensation Adjustment (SARO-BMB-F-25-0003316 2ND Tranche)';
    $spf2_code = '50104990-06';
    $spf2_approved_budget = 0.00;
    $spf2_thismonth_amount = 0.00;
    $spf2_todate_amount = 0.00;
    $spf2_sub_object_code = '';
    $spf2_object_code = '';
    $spf2_unobligated_amount = $spf2_approved_budget - $spf2_todate_amount;
}

$spf_approved_budget = $spf1_approved_budget + $spf2_approved_budget;
$spf_thismonth_amount = $spf1_thismonth_amount + $spf2_thismonth_amount;
$spf_todate_amount = $spf1_todate_amount + $spf2_todate_amount;
$spf_unobligated_amount = $spf1_unobligated_amount + $spf2_unobligated_amount;
if (!$rw1 && !$rw2) {
    $spf_percentage = 0.00;
}else{
    $spf_percentage = ($spf_todate_amount / $spf_approved_budget) * 100;
}

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(5, 3.5, 'SPECIAL PURPOSE FUND', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(5, 3.5, 'MPBF', 0, 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column


$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, $spf1_object_code, 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($spf_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($spf_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($spf_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($spf_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($spf_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
// First column (just borders, aligned with row height later)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L');

// Multicell for particulars
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(54, 4.5, $spf1_particulars, 'R', 'L');

// Get how much height was actually used
$endY = $pdf->GetY();
$rowHeight = $endY - $Y;  // dynamic height

$pdf->SetXY(10, $Y);
$pdf->Cell(15, $rowHeight, '', 'L', 0, 'L');

// Now apply same row height to the rest of the cells
$pdf->SetFont('Arial', '', 8);

// Code column
$pdf->SetXY(74, $Y);
$pdf->Cell(22, $rowHeight, $spf1_code, 'RL', 0, 'R');

// Approved budget
$pdf->SetXY(96, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_approved_budget,2), 'RL', 0, 'R');

// This month
$pdf->SetXY(118, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_thismonth_amount,2), 'RL', 0, 'R');

// To date
$pdf->SetXY(140, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_todate_amount,2), 'RL', 0, 'R');

// Unobligated
$pdf->SetXY(162, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_unobligated_amount,2), 'RL', 0, 'R');

$pdf->SetXY(184, $Y);
$pdf->Cell(17, $rowHeight, '', 'RL', 0, 'R'); // Percentage column

// Move cursor to end of row (so next row continues properly)
$pdf->SetY($endY);

//----------------------------------------------------------------- SPECIAL PURPOSE FUND (SARO-BMB-F-25-0003316 2ND Tranche)---------------------------------------------------------------------------------
//SPF2 TOTAL
$Y = $pdf->GetY();  
// First column (just borders, aligned with row height later)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L');

// Multicell for particulars
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(54, 4.5, $spf2_particulars, 'BR', 'L');

// Get how much height was actually used
$endY = $pdf->GetY();
$rowHeight = $endY - $Y;  // dynamic height

$pdf->SetXY(10, $Y);
$pdf->Cell(15, $rowHeight, '', 'BL', 0, 'L');

// Now apply same row height to the rest of the cells
$pdf->SetFont('Arial', '', 8);

// Code column
$pdf->SetXY(74, $Y);
$pdf->Cell(22, $rowHeight, $spf2_code, 'BRL', 0, 'R');

// Approved budget
$pdf->SetXY(96, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_approved_budget,2), 'BRL', 0, 'R');

// This month
$pdf->SetXY(118, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_thismonth_amount,2), 'BRL', 0, 'R');

// To date
$pdf->SetXY(140, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_todate_amount,2), 'BRL', 0, 'R');

// Unobligated
$pdf->SetXY(162, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_unobligated_amount,2), 'BRL', 0, 'R');

$pdf->SetXY(184, $Y);
$pdf->Cell(17, $rowHeight, '', 'BRL', 0, 'R'); // Percentage column

// Move cursor to end of row (so next row continues properly)
$pdf->SetY($endY);

//----------------------------------------------------------------- TOTAL SPECIAL PURPOSE FUND -----------------------------------------------------------=------------

$Y = $pdf->GetY();  
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'TL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TR', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'TOTAL SPECIAL PURPOSE FUND', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($spf_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($spf_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($spf_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($spf_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($spf_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column


//----------------------------------------------------------------- UNPROGRAMMED APPROPRIATIONS ---------------------------------------------------------------------------

$Y = $pdf->GetY()+3.5;
//SPF TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        (SELECT sub_object_code FROM mst_uacs WHERE a.`code` = `uacs_code` LIMIT 1) AS `sub_object_code`,
        (SELECT object_code FROM mst_uacs WHERE uacs_code = '50104990-99' AND object_code = 'General Administration And Support' LIMIT 1) AS `object_code`,
        COALESCE(a.`approved_budget`,0.00) approved_budget,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
            ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50104990-99'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");
$rw1 = $query->getRowArray();
if ($rw1) {
    $spf1_particulars = $rw1['particulars'];
    $spf1_code = $rw1['code'];
    $spf1_approved_budget = $rw1['approved_budget'];
    $spf1_thismonth_amount = $rw1['thismonth_amount'];
    $spf1_todate_amount = $rw1['todate_amount'];
    $spf1_sub_object_code = $rw1['sub_object_code'];
    $spf1_object_code = $rw1['object_code'];
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}else{
    $spf1_particulars = 'General Management and Supervision(SARO-BMB-F-24-0012643/001508)';
    $spf1_code = '50104990-14';
    $spf1_approved_budget = 0.00;
    $spf1_thismonth_amount = 0.00;
    $spf1_todate_amount = 0.00;
    $spf1_sub_object_code = '';
    $spf1_object_code = 'General Administration and Support';
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}

$unprogrammed_approved_budget = $spf1_approved_budget;
$unprogrammed_thismonth_amount = $spf1_thismonth_amount;
$unprogrammed_todate_amount = $spf1_todate_amount;
$unprogrammed_unobligated_amount = $spf1_unobligated_amount;
if (!$rw1) {
    $unprogrammed_percentage = 0.00;
}else{
    $unprogrammed_percentage = ($unprogrammed_todate_amount / $unprogrammed_approved_budget) * 100;
}

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(5, 3.5, 'UNPROGRAMMED APPROPRIATIONS', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, $spf1_object_code, 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($unprogrammed_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
// First column (just borders, aligned with row height later)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L');

// Multicell for particulars
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(54, 4.5, $spf1_particulars, 'R', 'L');

// Get how much height was actually used
$endY = $pdf->GetY();
$rowHeight = $endY - $Y;  // dynamic height

$pdf->SetXY(10, $Y);
$pdf->Cell(15, $rowHeight, '', 'L', 0, 'L');

// Now apply same row height to the rest of the cells
$pdf->SetFont('Arial', '', 8);

// Code column
$pdf->SetXY(74, $Y);
$pdf->Cell(22, $rowHeight, $spf1_code, 'RL', 0, 'R');

// Approved budget
$pdf->SetXY(96, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_approved_budget,2), 'RL', 0, 'R');

// This month
$pdf->SetXY(118, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_thismonth_amount,2), 'RL', 0, 'R');

// To date
$pdf->SetXY(140, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_todate_amount,2), 'RL', 0, 'R');

// Unobligated
$pdf->SetXY(162, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_unobligated_amount,2), 'RL', 0, 'R');

$pdf->SetXY(184, $Y);
$pdf->Cell(17, $rowHeight, '', 'RL', 0, 'R'); // Percentage column

// Move cursor to end of row (so next row continues properly)
$pdf->SetY($endY);

//----------------------------------------------------------------- TOTAL UNPROGRAMMED APPROPRIATIONS -----------------------------------------------------------------------
$Y = $pdf->GetY();  
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'TL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TR', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'TOTAL UNPROGRAMMED APPROPRIATIONS', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($unprogrammed_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- AUTOMATIC  APPROPRIATIONS ---------------------------------------------------------------------------------
//GENERAL TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$general_particulars = $rw['particulars'];
$general_code = $rw['code'];
$general_approved_budget = $rw['approved_budget'];
$general_thismonth_amount = $rw['thismonth_amount'];
$general_todate_amount = $rw['todate_amount'];
$general_unobligated_amount = $general_approved_budget - $general_todate_amount;

//SCIENTIFIC TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$scientific_particulars = $rw['particulars'];
$scientific_code = $rw['code'];
$scientific_approved_budget = $rw['approved_budget'];
$scientific_thismonth_amount = $rw['thismonth_amount'];
$scientific_todate_amount = $rw['todate_amount'];
$scientific_unobligated_amount = $scientific_approved_budget - $scientific_todate_amount;

//NUTRITIONAL TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`project_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`project_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`project_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$nutritional_particulars = $rw['particulars'];
$nutritional_code = $rw['code'];
$nutritional_approved_budget = $rw['approved_budget'];
$nutritional_thismonth_amount = $rw['thismonth_amount'];
$nutritional_todate_amount = $rw['todate_amount'];
$nutritional_unobligated_amount = $nutritional_approved_budget - $nutritional_todate_amount;

//TECHNICAL TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`project_title` LIKE '%Technical Services on Food and Nutrition%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`project_title` LIKE '%Technical Services on Food and Nutrition%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`project_title` LIKE '%Technical Services on Food and Nutrition%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$technical_particulars = $rw['particulars'];
$technical_code = $rw['code'];
$technical_approved_budget = $rw['approved_budget'];
$technical_thismonth_amount = $rw['thismonth_amount'];
$technical_todate_amount = $rw['todate_amount'];
$technical_unobligated_amount = $technical_approved_budget - $technical_todate_amount;

$automatic_approved_budget = $general_approved_budget + $scientific_approved_budget + $nutritional_approved_budget + $technical_approved_budget;
$automatic_thismonth_amount = $general_thismonth_amount + $scientific_thismonth_amount + $nutritional_thismonth_amount + $technical_thismonth_amount;
$automatic_todate_amount = $general_todate_amount + $scientific_todate_amount + $nutritional_todate_amount + $technical_todate_amount;
$automatic_unobligated_amount = $general_unobligated_amount + $scientific_unobligated_amount + $nutritional_unobligated_amount + $technical_unobligated_amount;
$automatic_percentage = ($automatic_todate_amount / $automatic_approved_budget) * 100;


$Y = $pdf->GetY()+3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, 'AUTOMATIC APPROPRIATIONS', 'LR', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'L'); // First column

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

//TOTAL OBJECT CODE GENERAL DATA
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(10, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, 'Retirement and Life Insurance Premium', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($automatic_percentage,2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- GENERAL DATA FETCHING ---------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(59, 3.5, 'I.a.1 Retirement and Life Insurance Premium', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, $general_code, 'LR', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($general_approved_budget,2), 'R', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($general_thismonth_amount,2), 'R', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($general_todate_amount,2), 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($general_unobligated_amount,2), 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- SCIENTIFIC DATA FETCHING ---------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(59, 3.5, 'II.a.1 Retirement and Life Insurance Premium', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, $scientific_code, 'LR', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($scientific_approved_budget,2), 'R', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($scientific_thismonth_amount,2), 'R', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($scientific_todate_amount,2), 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($scientific_unobligated_amount,2), 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- NUTRITIONAL DATA FETCHING ---------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(59, 3.5, 'II.b.1 Retirement and Life Insurance Premium', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, $nutritional_code, 'LR', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($nutritional_approved_budget,2), 'R', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($nutritional_thismonth_amount,2), 'R', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($nutritional_todate_amount,2), 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($nutritional_unobligated_amount,2), 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- TECHNICAL DATA FETCHING ---------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(64, 3.5, 'II.b.1 Retirement and Life Insurance Premium', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, $technical_code, 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($technical_approved_budget,2), 'BR', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($technical_thismonth_amount,2), 'BR', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($technical_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($technical_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- TOTAL AUTOMATIC APPROPRIATIONS -----------------------------------------------------------=------------
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'TOTAL AUTOMATIC APPROPRIATIONS', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($automatic_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- GRAND TOTAL CURRENT YEAR BUDGET ---------------------------------------------------------------------------
$grandtotal_total_project_budget = $totalprogram_total_project_budget + $automatic_approved_budget + $spf_approved_budget + $unprogrammed_approved_budget;
$grandtotal_thismonth_grand_total = $totalprogram_thismonth_grand_total + $automatic_thismonth_amount + $spf_thismonth_amount + $unprogrammed_thismonth_amount;
$grandtotal_todate_grand_total = $totalprogram_todate_grand_total + $automatic_todate_amount + $spf_todate_amount + $unprogrammed_todate_amount;
$grandtotal_grand_unobligated = $totalprogram_grand_unobligated + $automatic_unobligated_amount + $spf_unobligated_amount + $unprogrammed_unobligated_amount;
$grandtotal_grand_percentage_minus = ($grandtotal_todate_grand_total / $grandtotal_total_project_budget) * 100;

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'GRAND TOTAL, CURRENT YEAR BUDGET' . $unprogrammed_approved_budget, 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_total_project_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_thismonth_grand_total,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_todate_grand_total,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_grand_unobligated,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($grandtotal_grand_percentage_minus, 2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------------- SIGNATORIES -----------------------------------------------------------------------------------------

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, 'Certified Correct:', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, 'Approved by:', 0, 'L'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

//-------------------------------------------------------------------- BLANK SPACES -----------------------------------------------------------------------------------------

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 14, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 14, '', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'L'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 14, '', 'R', 0, 'R'); // Percentage column

//---------------------------------------------------------------------- SIGNATURE -------------------------------------------------------------------------------------------
$Y = $pdf->GetY() + 14; 
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, 'ROMANA L. LLAMAS', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(44, 3.5, 'ATTY. LUCIEDEN G. RAZ', 0, 0, 'L'); // Budget column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, 'DATE', 0, 0, 'L'); // Unobligated column
$pdf->SetFont('Arial', '', 8);

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

//---------------------------------------------------------------------- POSITION -------------------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5; 
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, 'Administrative Officer V', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(44, 3.5, 'Deputy Director / Director III', 0, 0, 'L'); // Budget column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 0, 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

//---------------------------------------------------------------------- POSITION -------------------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5; 
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'LB', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, '', 'B', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(44, 3.5, 'Officer-in-Charge, Office of the Director', 'B', 0, 'L'); // Budget column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'B', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'B', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RB', 0, 'R'); // Percentage column


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//----------------------------------------------------------------------- SUMMARY CURRENT YEAR BUDGET -------------------------------------------------------------------------
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$pdf->AddPage();

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
$pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
$Y+= 3.5;

$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
$pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
$pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
$pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
$Y+= 3.5;

$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
$pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
$Y+= 3.5;

$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
$pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
$pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
$Y+= 3.5;

$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
$pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
$pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
$pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
$pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
$pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
$pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

$Y = $pdf->GetY()+3.5;

$total_curryear_budget = 0;
$total_year_ps = 0;
$total_year_mooe = 0;
$total_year_co = 0;
$total_sub_month_amount = 0;
$total_sub_year_amount = 0;
$program_tagging = "";
$project_like = "";
$program_class = "";

$total_todate_month = 0;
$ps_total_unobligated = 0;
$mooe_total_unobligated = 0;
$co_total_unobligated = 0;
$ps_percentage_minus = 0;
$mooe_percentage_minus = 0;
$co_percentage_minus = 0;
$total_project_budget = 0;
$ps_grand_total = 0;
$mooe_grand_total = 0;
$co_grand_total = 0;
$thismonth_grand_total = 0;

$ps_todate_grand_total = 0;
$mooe_todate_grand_total = 0;
$co_todate_grand_total = 0;
$todate_grand_total = 0;

$grand_unobligated = 0;
$grand_percentage_minus = 0;
$printed_once = '';
$printed_after_mooe = '';

$summary_total_project_budget = 0;
$summary_thismonth_grand_total = 0;
$summary_todate_grand_total = 0;
$summary_grand_unobligated = 0;
$summary_grand_percentage_minus = 0;

//CURRENT YEAR BUDGET
$Y = $pdf->GetY() +3.5;

$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` AND `code` != '50103010-00'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE
        a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$rw = $query->getRowArray();
$total_project_budget = $rw['total_approved_budget'];

//CURRENT DATE GRAND TOTAL

//PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.uacs_code = b.code 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.uacs_code = b.code 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14' AND saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_grand_total = $rw['grand_total'];

//MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.uacs_code = b.code 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.uacs_code = b.code 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_grand_total = $rw['grand_total'];

//CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.uacs_code = b.code 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.uacs_code = b.code 
                AND hd.`ors_date` >= '$date_from' 
                AND hd.`ors_date` < '$date_to'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
         saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_grand_total = $rw['grand_total'];

$thismonth_grand_total = $ps_grand_total + $mooe_grand_total + $co_grand_total;

//UP TO DATE PS GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.`current_year` = '$year'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;
$grand_unobligated = $total_project_budget - $todate_grand_total;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $grand_percentage_minus = ($todate_grand_total / $total_project_budget) * 100;
}


$Y = $pdf->GetY()+3.5;
$query = $this->db->query("
    SELECT
        a.`program_title`,
        a.`project_title`,
        a.`recid`,
        (SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE `code` != '50103010-00' AND `code` != '50102990-14' AND `code` != '50104990-06' AND `code` != '50104990-14') AS total_ps,
        (SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt) AS total_mooe,
        (SELECT SUM(approved_budget) FROM tbl_saob_co_dt) AS total_co,
        SUM((
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE `code` != '50103010-00' AND `code` != '50102990-14' AND `code` != '50104990-06' AND `code` != '50104990-14'), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt), 0)
        )) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    WHERE
        a.`current_year` = '$year'
    ORDER BY a.`recid`
");
$hd_data = $query->getResultArray();
$total_all_sub_month = 0;
$total_all_todate_month = 0;
$ors_total_current_month = 0;
$ors_total_todate_month = 0;

foreach ($hd_data as $hd_row) {
    $ps_current_month = 0;
    $mooe_current_month = 0;
    $co_current_month = 0;
    $ps_tolatest_month = 0;
    $mooe_tolatest_month = 0;
    $co_tolatest_month = 0;
    $total_unobligated = 0;
    $total_percentage_minus = 0;
    $program_title = $hd_row['program_title'];
    $project_title = $hd_row['project_title'];
    $recid = $hd_row['recid'];
    $total_ps = $hd_row['total_ps'];
    $total_mooe = $hd_row['total_mooe'];
    $total_co = $hd_row['total_co'];
    $total_approved_budget = $hd_row['total_approved_budget'];

    if ($project_title == "Technical Services on Food and Nutrition") {
        $program_tagging = $program_title;
        $program_like = 'Technical Services on Food and Nutrition';
    }

    //PS--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            SUM(b.approved_budget) approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.uacs_code = b.code AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.uacs_code = b.code = b.particulars AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.uacs_code = b.code 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.uacs_code = b.code = b.particulars
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_ps_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        WHERE 
            b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14'
        GROUP BY 
            b.`code`, b.object_code
        ORDER BY 
            CASE 
                WHEN b.object_code = 'Salaries and Wages' THEN 1
                WHEN b.object_code = 'Other Compensation' THEN 2
                WHEN b.object_code = 'Personnel Benefit Contributions' THEN 3
                ELSE 4
            END,
            b.recid, b.particulars;

    ");
    $ps_data = $query->getResultArray();
    //total ps object code fetching
    $ps_object_code_totals = [];
    $ps_sub_month_totals = [];
    $ps_sub_year_totals = [];
    $last_object_code = '';
    
    $ps_total_current_month = 0;
    $ps_total_todate_month = 0;
    foreach ($ps_data as $ps_row) {
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $approved_budget = floatval($ps_row['approved_budget']);
        $total_sub_month = floatval($ps_row['total_sub_month']);
        $total_sub_all = floatval($ps_row['total_sub_all']);

        if (!isset($ps_object_code_totals[$object_code])) {
            $ps_object_code_totals[$object_code] = 0;
        }
        $ps_object_code_totals[$object_code] += $approved_budget;

        if (!isset($ps_sub_month_totals[$object_code])) {
            $ps_sub_month_totals[$object_code] = 0;
        }
        $ps_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($ps_sub_year_totals[$object_code])) {
            $ps_sub_year_totals[$object_code] = 0;
        }
        $ps_sub_year_totals[$object_code] += $total_sub_all;

        $ps_total_current_month += $total_sub_month;
        $ps_total_todate_month += $total_sub_all;

        $ps_total_unobligated = ($total_ps ?? 0) - ($ps_total_todate_month ?? 0);
        
        
        if (!empty($total_ps) && $total_ps > 0) {
            $ps_percentage_minus = ($ps_total_todate_month / $total_ps) * 100;   
        }


    }

    $ps_current_month += $ps_total_current_month;
    $ps_tolatest_month += $ps_total_todate_month;

    //MOOE------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            SUM(b.approved_budget) approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.uacs_code = b.code AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.uacs_code = b.code AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.uacs_code = b.code
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.uacs_code = b.code
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_mooe_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        GROUP BY
            b.`code`, b.object_code
        ORDER BY 
            CASE 
                WHEN b.object_code = 'Traveling Expenses' THEN 1
                WHEN b.object_code = 'Training and Scholarship Expenses' THEN 2
                WHEN b.object_code = 'Supplies and Materials Expenses' THEN 3
                WHEN b.object_code = 'Utility Expenses' THEN 4
                WHEN b.object_code = 'Communication Expenses ' THEN 5
                WHEN b.object_code = 'Awards/Rewards, Prizes and Indemnities' THEN 6
                WHEN b.object_code = 'Research, Exploration and Development Expenses' THEN 7
                WHEN b.object_code = 'Confidential, Intelligence and Extraordinary Expenses' THEN 8
                WHEN b.object_code = 'Professional Services' THEN 9
                WHEN b.object_code = 'General Services' THEN 10
                WHEN b.object_code = 'Repairs and Maintenance' THEN 11
                WHEN b.object_code = 'Taxes, Insurance Premiums and Other Fees' THEN 12
                WHEN b.object_code = 'Other Maintenance and Operating Expenses' THEN 13
                ELSE 14
            END,
            b.recid, b.particulars;

    ");
    $mooe_data = $query->getResultArray();
    //total mooe object code fetching
    $mooe_object_code_totals = [];
    $mooe_sub_month_totals = [];
    $mooe_sub_year_totals = [];
    $last_object_code = '';
    $mooe_total_current_month = 0;
    $mooe_total_todate_month = 0;
    foreach ($mooe_data as $mooe_row) {
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $approved_budget = floatval($mooe_row['approved_budget']);
        $total_sub_month = floatval($mooe_row['total_sub_month']);
        $total_sub_all = floatval($mooe_row['total_sub_all']);

        if (!isset($mooe_object_code_totals[$object_code])) {
            $mooe_object_code_totals[$object_code] = 0;
        }
        $mooe_object_code_totals[$object_code] += $approved_budget;

        if (!isset($mooe_sub_month_totals[$object_code])) {
            $mooe_sub_month_totals[$object_code] = 0;
        }
        $mooe_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($mooe_sub_year_totals[$object_code])) {
            $mooe_sub_year_totals[$object_code] = 0;
        }
        $mooe_sub_year_totals[$object_code] += $total_sub_all;

        $mooe_total_current_month += $total_sub_month;
        $mooe_total_todate_month += $total_sub_all;

        $mooe_total_unobligated = ($total_mooe ?? 0) - ($mooe_total_todate_month ?? 0);
        
        
        if (!empty($total_mooe) && $total_mooe > 0) {
            $mooe_percentage_minus = ($mooe_total_todate_month / $total_mooe) * 100;
        }
          
    }
    $mooe_current_month += $mooe_total_current_month;
    $mooe_tolatest_month += $mooe_total_todate_month;

    //CO--------------------------------------------
    $query = $this->db->query("
        SELECT
            u.allotment_class,
            b.object_code,
            b.code,
            b.particulars AS sub_object_code,
            b.code AS uacs_code,
            SUM(b.approved_budget) approved_budget,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN
                tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.uacs_code = b.code AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ), 0) + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN
                tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.uacs_code = b.code AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ), 0) AS total_sub_month,
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.project_id = hd.recid
                WHERE d.uacs_code = b.code
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0)
            + COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.project_id = hd.recid
                WHERE i.uacs_code = b.code
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
            ), 0) AS total_sub_all
        FROM 
            tbl_saob_co_dt AS b
        LEFT JOIN 
            mst_uacs AS u ON b.code = u.uacs_code
        GROUP BY
            b.`code`, b.object_code
        ORDER BY 
            b.recid, b.particulars;

    ");
    $co_data = $query->getResultArray();
    //total co object code fetching
    $co_object_code_totals = [];
    $co_sub_month_totals = [];
    $co_sub_year_totals = [];
    $last_object_code = '';
    $co_total_current_month = 0;
    $co_total_todate_month = 0;
    foreach ($co_data as $co_row) {
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $approved_budget = floatval($co_row['approved_budget']);
        $total_sub_month = floatval($co_row['total_sub_month']);
        $total_sub_all = floatval($co_row['total_sub_all']);

        if (!isset($co_object_code_totals[$object_code])) {
            $co_object_code_totals[$object_code] = 0;
        }
        $co_object_code_totals[$object_code] += $approved_budget;

        if (!isset($co_sub_month_totals[$object_code])) {
            $co_sub_month_totals[$object_code] = 0;
        }
        $co_sub_month_totals[$object_code] += $total_sub_month;

        if (!isset($co_sub_year_totals[$object_code])) {
            $co_sub_year_totals[$object_code] = 0;
        }
        $co_sub_year_totals[$object_code] += $total_sub_all;

        $co_total_current_month += $total_sub_month;
        $co_total_todate_month += $total_sub_all;

        $co_total_unobligated = ($total_co ?? 0) - ($co_total_todate_month ?? 0);
        
        
        if (!empty($total_co) && $total_co > 0) {
            $co_percentage_minus = ($co_total_todate_month / $total_co) * 100;
        }
          
    }

    $co_current_month += $co_total_current_month;
    $co_tolatest_month += $co_total_todate_month;

    $total_all_sub_month = $ps_current_month + $mooe_current_month + $co_current_month;
    $total_all_todate_month = $ps_tolatest_month + $mooe_tolatest_month + $co_tolatest_month;
    $total_unobligated = $total_approved_budget - $total_all_todate_month;
    if (!empty($total_approved_budget) && $total_approved_budget > 0) {
        $total_percentage_minus = ($total_all_todate_month / $total_approved_budget) * 100;
    }

    $Y = $pdf->GetY() +3.5;
    // PROGRAM TAGGING LOGIC --------------------------------------------------------------------
    $startY = $Y; // Store the starting Y position

    // First, measure the height needed for program_tagging
    $pdf->SetXY(10, $Y);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->MultiCell(64, 5, 'SUMMARY:', 'R', 'L'); // Measure without border
    $afterProgramY = $pdf->GetY();
    $programHeight = $afterProgramY - $Y;

    // Draw complete PROGRAM TAGGING row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $programHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(64, $programHeight, '', 0, 0, 'L'); // Program text column

    // Right-side columns for program tagging
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $programHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $programHeight, '', 'RL', 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $programHeight, '', 'RL', 0, 'C'); // Percentage column



    // Draw program tagging text
    $pdf->SetXY(5, $Y + 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // PROJECT TITLE LOGIC --------------------------------------------------------------------
    $Y = $afterProgramY; // Move Y to after program tagging
    $projectStartY = $Y;

    // Measure the height needed for project_title
    $pdf->SetXY(10, $Y);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->MultiCell(64, 3.5, 'CURRENT YEAR BUDGET', 'R', 'L'); // Measure without border
    $afterProjectY = $pdf->GetY();
    $projectHeight = $afterProjectY - $Y;

    // Draw complete PROJECT TITLE row with ALL borders
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, $projectHeight, '', 'L', 0, 'C'); // First column

    $pdf->SetXY(15, $Y);
    $pdf->Cell(64, $projectHeight, '', 0, 0, 'L'); // Project text column

    // Right-side columns for project title
    $pdf->SetXY(85, $Y);
    $pdf->Cell(11, $projectHeight, '', 0, 0, 'C'); // UACS column

    $pdf->SetXY(96, $Y);
    $pdf->Cell(22, $projectHeight, '', 'RL', 0, 'C'); // Budget column

    $pdf->SetXY(118, $Y);
    $pdf->Cell(22, $projectHeight, '', 'RL', 0, 'C'); // This month column

    $pdf->SetXY(140, $Y);
    $pdf->Cell(22, $projectHeight, '', 'RL', 0, 'C'); // To date column

    $pdf->SetXY(162, $Y);
    $pdf->Cell(22, $projectHeight, '', 'RL', 0, 'C'); // Unobligated column

    $pdf->SetXY(184, $Y);
    $pdf->Cell(17, $projectHeight, '', 'RL', 0, 'C'); // Percentage column

    // Draw project title totals (centered vertically)
    $projectMiddleY = $Y + ($projectHeight / 2) - 1.5;
    $pdf->SetXY(96, $projectMiddleY);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(22, 3, '', 0, 0, 'R');

    $pdf->SetXY(118, $projectMiddleY);
    $pdf->Cell(22, 3, '', 0, 0, 'R');

    $pdf->SetXY(140, $projectMiddleY);
    $pdf->Cell(22, 3, '', 0, 0, 'R');

    $pdf->SetXY(162, $projectMiddleY);
    $pdf->Cell(22, 3, '', 0, 0, 'R');

    $pdf->SetXY(184, $projectMiddleY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 3, '', 0, 0, 'R');

    // Draw project title text
    $pdf->SetXY(15, $Y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(70, 3.5, '', 0, 'L');

    // Update Y position for next content
    $Y = $afterProjectY;

    // Clean up and prepare for next section
    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY(); // Add some spacing before the next section

    foreach ($ps_data as $ps_row) {
        $allotment_class = $ps_row['allotment_class'];
        $object_code = $ps_row['object_code'];
        $sub_object_code = $ps_row['sub_object_code'];
        $uacs_code = $ps_row['uacs_code'];
        $approved_budget = $ps_row['approved_budget'];
        $code = $ps_row['code'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`uacs_code` = '$code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`uacs_code` = '$code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dps.`amount` FROM tbl_ors_direct_ps_dt dps JOIN tbl_ors_hd hd on dps.`project_id` = hd.`recid` WHERE dps.`uacs_code` = '$code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'
                UNION ALL
                SELECT idps.`amount` FROM tbl_ors_indirect_ps_dt idps JOIN tbl_ors_hd hd on idps.`project_id` = hd.`recid` WHERE idps.`uacs_code` = '$code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_ps, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($ps_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($ps_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($ps_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($ps_percentage_minus, 2) . '%', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 8);

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $ps_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $ps_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $ps_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >263) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY() +5;
    foreach ($mooe_data as $mooe_row) {
        $allotment_class = $mooe_row['allotment_class'];
        $object_code = $mooe_row['object_code'];
        $sub_object_code = $mooe_row['sub_object_code'];
        $uacs_code = $mooe_row['uacs_code'];
        $approved_budget = $mooe_row['approved_budget'];
        $code = $mooe_row['code'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`uacs_code` = '$code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`uacs_code` = '$code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dmooe.`amount` FROM tbl_ors_direct_mooe_dt dmooe JOIN tbl_ors_hd hd on dmooe.`project_id` = hd.`recid` WHERE dmooe.`uacs_code` = '$code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'
                UNION ALL
                SELECT idmooe.`amount` FROM tbl_ors_indirect_mooe_dt idmooe JOIN tbl_ors_hd hd on idmooe.`project_id` = hd.`recid` WHERE idmooe.`uacs_code` = '$code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 255) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_mooe, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($mooe_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($mooe_percentage_minus, 2) . '%', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 8);

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Totals
            $total_for_object_code = $mooe_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $mooe_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $mooe_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            if ($object_code == 'Research, Exploration and Development Expenses') {
                $object_code = 'Research, Exploration and Dev. Exps.';
            }

            if ($object_code == 'Confidential, Intelligence and Extraordinary Expenses') {
                $object_code = 'Confidential, Intelligence and Extraord. Exps.';
            }

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');



            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >250) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LRB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'B', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $last_allotment_class = '';
    $last_sub_object_code = '';
    $last_object_code = '';

    $Y = $pdf->GetY()+5;
    foreach ($co_data as $co_row) {
        $allotment_class = $co_row['allotment_class'];
        $object_code = $co_row['object_code'];
        $sub_object_code = $co_row['sub_object_code'];
        $uacs_code = $co_row['uacs_code'];
        $approved_budget = $co_row['approved_budget'];
        $code = $co_row['code'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`uacs_code` = '$code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`uacs_code` = '$code' AND hd.`ors_date` >= '$date_from' AND hd.`ors_date` < '$date_to'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $month_amount = $result['total_amount'];

        $query = $this->db->query("
            SELECT COALESCE(SUM(amount),0) AS total_amount  
            FROM (
                SELECT dco.`amount` FROM tbl_ors_direct_co_dt dco JOIN tbl_ors_hd hd on dco.`project_id` = hd.`recid` WHERE dco.`uacs_code` = '$code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'
                UNION ALL
                SELECT idco.`amount` FROM tbl_ors_indirect_co_dt idco JOIN tbl_ors_hd hd on idco.`project_id` = hd.`recid` WHERE idco.`uacs_code` = '$code' AND hd.`ors_date` >= '$og_date_from' AND hd.`ors_date` < '$og_date_to'
            ) AS combined
        ");

        $result = $query->getRowArray();
        $year_amount = $result['total_amount'];

        if ($Y > 265) {
            $pdf->AddPage();
            $Y = $pdf->GetY();
            $pdf->SetXY(10, $Y);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(64, 3.5, '', 'TRL', 0, 'C');//ROW 1
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(44, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'TRL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'TRL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(44, 3.5, 'Obligation Incurred', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Unobligated', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, '', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, 'OBJECT OF EXPENDITURE', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Revised', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'This month', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Obligation', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Balance of', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Percent', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'UACS', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, $month, 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'To Date', 'RL', 0, 'C');
            $pdf->Cell(22, 3.5, 'Allotment', 'RL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Of', 'RL', 0, 'C');
            $Y+= 3.5;

            $pdf->SetXY(10, $Y);
            $pdf->Cell(64, 3.5, '', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(1)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(4)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(5)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(6)', 'BRL', 0, 'C');
            $pdf->Cell(22, 3.5, '(7)=(4)-(6)', 'BRL', 0, 'C');
            $pdf->Cell(17, 3.5, 'Utilization', 'BRL', 0, 'C');

            $Y = $pdf->GetY()+3.5;
            // $pdf->SetXY(10, $Y);
            // $pdf->Cell(64, 215.5, '', 1, 0); 
            // $pdf->SetXY(74, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(96, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(118, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(140, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(162, $Y);
            // $pdf->Cell(22, 215.5, '', 1, 0); 
            // $pdf->SetXY(184, $Y);
            // $pdf->Cell(17, 215.5, '', 1, 0); 
        }

        // ================= ALLOTMENT CLASS ===================
        if ($allotment_class !== $last_allotment_class && $allotment_class !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Save startY
            $startY = $Y;

            // Print text with wrapping
            $pdf->SetXY(10, $Y);
            $pdf->MultiCell(64, 4.5, $allotment_class, 0, 'L');

            // Measure row height
            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            // Draw borders
            $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
            $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
            $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BRL', 0, 'C');
            $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'BRL', 0, 'C');

            // Center values vertically
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;
            $pdf->SetFont('Arial', '', 8);

            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($total_co, 2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($co_total_current_month, 2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($co_total_todate_month, 2), 0, 0, 'R');
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($co_total_unobligated, 2), 0, 0, 'R');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, number_format($co_percentage_minus, 2) . '%', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 8);

            $Y = $endY;
            $last_allotment_class = $allotment_class;
        }

        // ================= OBJECT CODE ===================
        if ($object_code !== $last_object_code && $object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            // Object Code: Start at X=15 (no blank cell before)
            $pdf->SetXY(10, $Y);
            $pdf->Cell(5, 4.5, '', 'L', 1, 'C');
            $pdf->SetXY(15, $Y);
            $pdf->MultiCell(59, 4.5, $object_code, 0, 'L');

            // Totals
            $total_for_object_code = $co_object_code_totals[$object_code] ?? 0;
            $total_sub_month_amount = $co_sub_month_totals[$object_code] ?? 0;
            $total_sub_year_amount = $co_sub_year_totals[$object_code] ?? 0;
            $total_sub_unobligated = ($total_for_object_code ?? 0) - ($total_sub_year_amount ?? 0);

            // Columns
            $pdf->SetXY(74, $Y);
            $pdf->Cell(22, 4.5, '', 'LR', 1, 'R');
            $pdf->SetXY(96, $Y);
            $pdf->Cell(22, 4.5, number_format($total_for_object_code, 2), 'BLR', 1, 'R');
            $pdf->SetXY(118, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_month_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(140, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_year_amount, 2), 'BLR', 1, 'R');
            $pdf->SetXY(162, $Y);
            $pdf->Cell(22, 4.5, number_format($total_sub_unobligated, 2), 'BLR', 1, 'R');
            $pdf->SetXY(184, $Y);
            $pdf->Cell(17, 4.5, '', 'R', 1, 'C');

            // Move down
            $Y += 4.5;
            $last_object_code = $object_code;
        }

        // ================= SUB OBJECT CODE ===================
        if ($sub_object_code !== $last_sub_object_code && $sub_object_code !== null) {
            $pdf->SetFont('Arial', '', 8);

            $startY = $Y;
            $pdf->SetXY(20, $Y);
            $pdf->MultiCell(55, 4.5, $sub_object_code, 0, 'L');

            $endY = $pdf->GetY();
            $totalRowHeight = $endY - $startY;

            if ($Y >100) {
                // Borders
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'LB', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LRB', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'BLR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'RB', 0, 'C');
            }else{
                $pdf->SetXY(10, $startY); $pdf->Cell(64, $totalRowHeight, '', 'L', 0, 'L');
                $pdf->SetXY(74, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(96, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(118, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(140, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(162, $startY); $pdf->Cell(22, $totalRowHeight, '', 'LR', 0, 'C');
                $pdf->SetXY(184, $startY); $pdf->Cell(17, $totalRowHeight, '', 'R', 0, 'C');
            }

            // Center values
            $middleY = $startY + ($totalRowHeight / 2) - 2.5;

            $pdf->SetXY(74, $middleY); $pdf->Cell(22, 5, $uacs_code, 0, 0, 'C');
            $pdf->SetXY(96, $middleY); $pdf->Cell(22, 5, number_format($approved_budget,2), 0, 0, 'R');
            $pdf->SetXY(118, $middleY); $pdf->Cell(22, 5, number_format($month_amount,2), 0, 0, 'R');
            $pdf->SetXY(140, $middleY); $pdf->Cell(22, 5, number_format($year_amount,2), 0, 0, 'R');

            $subobligated_amount = $approved_budget - $year_amount;
            $pdf->SetXY(162, $middleY); $pdf->Cell(22, 5, number_format($subobligated_amount,2), 0, 0, 'R');
            $pdf->SetXY(184, $middleY); $pdf->Cell(17, 5, '', 0, 0, 'C');

            $Y = $endY;
            $last_sub_object_code = $sub_object_code;
        }

    }

    $total_year_ps += $total_ps;
    $total_year_mooe += $total_mooe;
    $total_year_co += $total_co;


    $ors_total_current_month += $total_all_sub_month;
    $ors_total_todate_month += $total_all_todate_month;
    $Y = $pdf->GetY() + 3.5;    

}

$Y = $pdf->GetY()+5;
$summary_total_curryear_budget = $total_year_ps + $total_year_mooe + $total_year_co;
$summary_grand_unobligated = $summary_total_curryear_budget - $ors_total_todate_month;
if (!empty($total_project_budget) && $total_project_budget > 0) {
    $summary_grand_percentage_minus = ($ors_total_todate_month / $summary_total_curryear_budget) * 100;
}
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(191, 3.5, 'TOTAL CURRENT YEAR BUDGET', 1, 1, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, ($summary_total_curryear_budget == 0.00 || !is_numeric($summary_total_curryear_budget)) ? 0.00 : number_format((float)$summary_total_curryear_budget, 2), 'L', 1, 'R');
$pdf->SetXY(118, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(21.8, 3.5, number_format($ors_total_current_month, 2), 'BL', 1, 'R'); // full width usage
$pdf->SetXY(140, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(21.8, 3.5, number_format($ors_total_todate_month, 2), 'BL', 1, 'R'); // full width usage
$pdf->SetXY(162, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(21.8, 3.5, number_format($summary_grand_unobligated, 2), 'BL', 1, 'R'); // full width usage
$pdf->SetXY(184, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(17, 3.5, number_format($summary_grand_percentage_minus, 2) . '%', 'BL', 1, 'R'); // full width usage

//------------------------------------------------------------------- TOTAL SPECIAL PURPOSE FUNDS ---------------------------------------------------------------------------
$Y = $pdf->GetY();
//SPF TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        (SELECT sub_object_code FROM mst_uacs WHERE a.`code` = `uacs_code` LIMIT 1) AS `sub_object_code`,
        (SELECT object_code FROM mst_uacs WHERE uacs_code = '50102990-14' AND object_code = 'General Administration And Support' LIMIT 1) AS `object_code`,
        COALESCE(a.`approved_budget`,0.00) approved_budget,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
            ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50102990-14'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");
$rw1 = $query->getRowArray();
if ($rw1) {
    $spf1_particulars = $rw1['particulars'];
    $spf1_code = $rw1['code'];
    $spf1_approved_budget = $rw1['approved_budget'];
    $spf1_thismonth_amount = $rw1['thismonth_amount'];
    $spf1_todate_amount = $rw1['todate_amount'];
    $spf1_sub_object_code = $rw1['sub_object_code'];
    $spf1_object_code = $rw1['object_code'];
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}else{
    $spf1_particulars = 'Performance-Based Bonus(SARO-BMB-F-25-0014437 PBB FY 2023)';
    $spf1_code = '50102990-14';
    $spf1_approved_budget = 0.00;
    $spf1_thismonth_amount = 0.00;
    $spf1_todate_amount = 0.00;
    $spf1_sub_object_code = '';
    $spf1_object_code = 'General Administration and Support';
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}

$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        (SELECT sub_object_code FROM mst_uacs WHERE a.`code` = `uacs_code` LIMIT 1) AS `sub_object_code`,
        (SELECT object_code FROM mst_uacs WHERE uacs_code = '50102990-14' AND object_code = 'General Administration And Support' LIMIT 1) AS `object_code`,
        COALESCE(a.`approved_budget`,0.00) approved_budget,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
            ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50104990-06'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");

$rw2 = $query->getRowArray();
if ($rw2) {
    $spf2_particulars = $rw2['particulars'];
    $spf2_code = $rw2['code'];
    $spf2_approved_budget = $rw2['approved_budget'];
    $spf2_thismonth_amount = $rw2['thismonth_amount'];
    $spf2_todate_amount = $rw2['todate_amount'];
    $spf2_sub_object_code = $rw2['sub_object_code'];
    $spf2_object_code = $rw2['object_code'];
    $spf2_unobligated_amount = $spf2_approved_budget - $spf2_todate_amount;
}else{
    $spf2_particulars = 'Lump-sum for Compensation Adjustment (SARO-BMB-F-25-0003316 2ND Tranche)';
    $spf2_code = '50104990-06';
    $spf2_approved_budget = 0.00;
    $spf2_thismonth_amount = 0.00;
    $spf2_todate_amount = 0.00;
    $spf2_sub_object_code = '';
    $spf2_object_code = '';
    $spf2_unobligated_amount = $spf2_approved_budget - $spf2_todate_amount;
}

$spf_approved_budget = $spf1_approved_budget + $spf2_approved_budget;
$spf_thismonth_amount = $spf1_thismonth_amount + $spf2_thismonth_amount;
$spf_todate_amount = $spf1_todate_amount + $spf2_todate_amount;
$spf_unobligated_amount = $spf1_unobligated_amount + $spf2_unobligated_amount;
if (!$rw1 && !$rw2) {
    $spf_percentage = 0.00;
}else{
    $spf_percentage = ($spf_todate_amount / $spf_approved_budget) * 100;
}

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(5, 3.5, 'SPECIAL PURPOSE FUND', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(5, 3.5, 'MPBF', 0, 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column


$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, $spf1_object_code, 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($spf_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($spf_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($spf_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($spf_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($spf_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
// First column (just borders, aligned with row height later)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L');

// Multicell for particulars
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(54, 4.5, $spf1_particulars, 'R', 'L');

// Get how much height was actually used
$endY = $pdf->GetY();
$rowHeight = $endY - $Y;  // dynamic height

$pdf->SetXY(10, $Y);
$pdf->Cell(15, $rowHeight, '', 'L', 0, 'L');

// Now apply same row height to the rest of the cells
$pdf->SetFont('Arial', '', 8);

// Code column
$pdf->SetXY(74, $Y);
$pdf->Cell(22, $rowHeight, $spf1_code, 'RL', 0, 'R');

// Approved budget
$pdf->SetXY(96, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_approved_budget,2), 'RL', 0, 'R');

// This month
$pdf->SetXY(118, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_thismonth_amount,2), 'RL', 0, 'R');

// To date
$pdf->SetXY(140, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_todate_amount,2), 'RL', 0, 'R');

// Unobligated
$pdf->SetXY(162, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_unobligated_amount,2), 'RL', 0, 'R');

$pdf->SetXY(184, $Y);
$pdf->Cell(17, $rowHeight, '', 'RL', 0, 'R'); // Percentage column

// Move cursor to end of row (so next row continues properly)
$pdf->SetY($endY);

//----------------------------------------------------------------- SPECIAL PURPOSE FUND (SARO-BMB-F-25-0003316 2ND Tranche)---------------------------------------------------------------------------------
//SPF2 TOTAL
$Y = $pdf->GetY();  
// First column (just borders, aligned with row height later)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L');

// Multicell for particulars
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(54, 4.5, $spf2_particulars, 'BR', 'L');

// Get how much height was actually used
$endY = $pdf->GetY();
$rowHeight = $endY - $Y;  // dynamic height

$pdf->SetXY(10, $Y);
$pdf->Cell(15, $rowHeight, '', 'BL', 0, 'L');

// Now apply same row height to the rest of the cells
$pdf->SetFont('Arial', '', 8);

// Code column
$pdf->SetXY(74, $Y);
$pdf->Cell(22, $rowHeight, $spf2_code, 'BRL', 0, 'R');

// Approved budget
$pdf->SetXY(96, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_approved_budget,2), 'BRL', 0, 'R');

// This month
$pdf->SetXY(118, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_thismonth_amount,2), 'BRL', 0, 'R');

// To date
$pdf->SetXY(140, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_todate_amount,2), 'BRL', 0, 'R');

// Unobligated
$pdf->SetXY(162, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf2_unobligated_amount,2), 'BRL', 0, 'R');

$pdf->SetXY(184, $Y);
$pdf->Cell(17, $rowHeight, '', 'BRL', 0, 'R'); // Percentage column

// Move cursor to end of row (so next row continues properly)
$pdf->SetY($endY);

//----------------------------------------------------------------- TOTAL SPECIAL PURPOSE FUND -----------------------------------------------------------=------------

$Y = $pdf->GetY();  
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'TL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TR', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'TOTAL SPECIAL PURPOSE FUND', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($spf_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($spf_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($spf_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($spf_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($spf_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column


//----------------------------------------------------------------- UNPROGRAMMED APPROPRIATIONS ---------------------------------------------------------------------------

$Y = $pdf->GetY()+3.5;
//SPF TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        (SELECT sub_object_code FROM mst_uacs WHERE a.`code` = `uacs_code` LIMIT 1) AS `sub_object_code`,
        (SELECT object_code FROM mst_uacs WHERE uacs_code = '50104990-99' AND object_code = 'General Administration And Support' LIMIT 1) AS `object_code`,
        COALESCE(a.`approved_budget`,0.00) approved_budget,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
            ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50104990-99'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");
$rw1 = $query->getRowArray();
if ($rw1) {
    $spf1_particulars = $rw1['particulars'];
    $spf1_code = $rw1['code'];
    $spf1_approved_budget = $rw1['approved_budget'];
    $spf1_thismonth_amount = $rw1['thismonth_amount'];
    $spf1_todate_amount = $rw1['todate_amount'];
    $spf1_sub_object_code = $rw1['sub_object_code'];
    $spf1_object_code = $rw1['object_code'];
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}else{
    $spf1_particulars = 'General Management and Supervision(SARO-BMB-F-24-0012643/001508)';
    $spf1_code = '50104990-14';
    $spf1_approved_budget = 0.00;
    $spf1_thismonth_amount = 0.00;
    $spf1_todate_amount = 0.00;
    $spf1_sub_object_code = '';
    $spf1_object_code = 'General Administration and Support';
    $spf1_unobligated_amount = $spf1_approved_budget - $spf1_todate_amount;
}

$unprogrammed_approved_budget = $spf1_approved_budget;
$unprogrammed_thismonth_amount = $spf1_thismonth_amount;
$unprogrammed_todate_amount = $spf1_todate_amount;
$unprogrammed_unobligated_amount = $spf1_unobligated_amount;
if (!$rw1) {
    $unprogrammed_percentage = 0.00;
}else{
    $unprogrammed_percentage = ($unprogrammed_todate_amount / $unprogrammed_approved_budget) * 100;
}

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(5, 3.5, 'UNPROGRAMMED APPROPRIATIONS', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->Cell(15, 3.5, $spf1_object_code, 0, 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, '', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($unprogrammed_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
// First column (just borders, aligned with row height later)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L');

// Multicell for particulars
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(54, 4.5, $spf1_particulars, 'R', 'L');

// Get how much height was actually used
$endY = $pdf->GetY();
$rowHeight = $endY - $Y;  // dynamic height

$pdf->SetXY(10, $Y);
$pdf->Cell(15, $rowHeight, '', 'L', 0, 'L');

// Now apply same row height to the rest of the cells
$pdf->SetFont('Arial', '', 8);

// Code column
$pdf->SetXY(74, $Y);
$pdf->Cell(22, $rowHeight, $spf1_code, 'RL', 0, 'R');

// Approved budget
$pdf->SetXY(96, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_approved_budget,2), 'RL', 0, 'R');

// This month
$pdf->SetXY(118, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_thismonth_amount,2), 'RL', 0, 'R');

// To date
$pdf->SetXY(140, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_todate_amount,2), 'RL', 0, 'R');

// Unobligated
$pdf->SetXY(162, $Y);
$pdf->Cell(22, $rowHeight, number_format($spf1_unobligated_amount,2), 'RL', 0, 'R');

$pdf->SetXY(184, $Y);
$pdf->Cell(17, $rowHeight, '', 'RL', 0, 'R'); // Percentage column

// Move cursor to end of row (so next row continues properly)
$pdf->SetY($endY);

//----------------------------------------------------------------- TOTAL UNPROGRAMMED APPROPRIATIONS -----------------------------------------------------------------------
$Y = $pdf->GetY();  
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'TL', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'TR', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'TOTAL UNPROGRAMMED APPROPRIATIONS', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($unprogrammed_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($unprogrammed_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- AUTOMATIC  APPROPRIATIONS ---------------------------------------------------------------------------------
$Y = $pdf->GetY();
//GENERAL TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%General Administration and%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`program_title` LIKE '%General Administration and%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$general_particulars = $rw['particulars'];
$general_code = $rw['code'];
$general_approved_budget = $rw['approved_budget'];
$general_thismonth_amount = $rw['thismonth_amount'];
$general_todate_amount = $rw['todate_amount'];
$general_unobligated_amount = $general_approved_budget - $general_todate_amount;

//SCIENTIFIC TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`program_title` LIKE '%Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$scientific_particulars = $rw['particulars'];
$scientific_code = $rw['code'];
$scientific_approved_budget = $rw['approved_budget'];
$scientific_thismonth_amount = $rw['thismonth_amount'];
$scientific_todate_amount = $rw['todate_amount'];
$scientific_unobligated_amount = $scientific_approved_budget - $scientific_todate_amount;

//NUTRITIONAL TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`project_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`project_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`project_title` LIKE '%NUTRITIONAL ASSESSMENT AND MONITORING ON FOOD AND NUTRITION%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$nutritional_particulars = $rw['particulars'];
$nutritional_code = $rw['code'];
$nutritional_approved_budget = $rw['approved_budget'];
$nutritional_thismonth_amount = $rw['thismonth_amount'];
$nutritional_todate_amount = $rw['todate_amount'];
$nutritional_unobligated_amount = $nutritional_approved_budget - $nutritional_todate_amount;

//TECHNICAL TOTAL
$query = $this->db->query("
    SELECT
        a.`particulars`,
        a.`code`,
        a.`approved_budget`,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$date_from' 
            AND bhd.`ors_date` < '$date_to'
            AND b.`project_title` LIKE '%Technical Services on Food and Nutrition%'
        ), 0.00) AS thismonth_amount,
        COALESCE((
            SELECT SUM(b.`amount`)
            FROM tbl_ors_direct_ps_dt b
            JOIN tbl_ors_hd bhd 
                ON b.`project_id` = bhd.`recid`
            WHERE b.`uacs_code` = a.`code` 
            AND bhd.`ors_date` >= '$og_date_from' 
            AND bhd.`ors_date` < '$og_date_to'
            AND b.`project_title` LIKE '%Technical Services on Food and Nutrition%'
        ), 0.00) AS todate_amount
    FROM tbl_saob_ps_dt a
    JOIN tbl_saob_hd c
        ON a.`project_id` = c.`recid`
    WHERE 
        a.`code` = '50103010-00'
        AND c.`project_title` LIKE '%Technical Services on Food and Nutrition%' AND c.`current_year` = '$year'
");
$rw = $query->getRowArray();
$technical_particulars = $rw['particulars'];
$technical_code = $rw['code'];
$technical_approved_budget = $rw['approved_budget'];
$technical_thismonth_amount = $rw['thismonth_amount'];
$technical_todate_amount = $rw['todate_amount'];
$technical_unobligated_amount = $technical_approved_budget - $technical_todate_amount;

$automatic_approved_budget = $general_approved_budget + $scientific_approved_budget + $nutritional_approved_budget + $technical_approved_budget;
$automatic_thismonth_amount = $general_thismonth_amount + $scientific_thismonth_amount + $nutritional_thismonth_amount + $technical_thismonth_amount;
$automatic_todate_amount = $general_todate_amount + $scientific_todate_amount + $nutritional_todate_amount + $technical_todate_amount;
$automatic_unobligated_amount = $general_unobligated_amount + $scientific_unobligated_amount + $nutritional_unobligated_amount + $technical_unobligated_amount;
$automatic_percentage = ($automatic_todate_amount / $automatic_approved_budget) * 100;

$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(64, 3.5, 'AUTOMATIC APPROPRIATIONS', 'LR', 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'L'); // First column

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(10, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, 'Retirement and Life Insurance Contribution', 0, 0, 'L'); // First column

$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'L'); // First column

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'RL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RL', 0, 'R'); // Percentage column

//TOTAL OBJECT CODE GENERAL DATA
$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column
$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(54, 3.5, 'Retirement and Life Insurance Premium', 0, 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(74, $Y);
$pdf->Cell(22, 3.5, '50103010-00', 'RL', 0, 'R'); // Budget column
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_approved_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($automatic_percentage,2) . '%', 'BRL', 0, 'R'); // Percentage column

//----------------------------------------------------------------- TOTAL AUTOMATIC APPROPRIATIONS -----------------------------------------------------------=------------
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(64, 3.5, 'TOTAL AUTOMATIC APPROPRIATIONS', 1, 0, 'L'); // First column
$pdf->SetXY(74, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(22, 3.5, '', 1, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_approved_budget,2), 1, 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_thismonth_amount,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_todate_amount,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($automatic_unobligated_amount,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($automatic_percentage, 2) . '%', 'BRL', 0, 'R'); // Percentage column

$grandtotal_total_project_budget = $totalprogram_total_project_budget + $automatic_approved_budget + $spf_approved_budget + $unprogrammed_approved_budget;
$grandtotal_thismonth_grand_total = $totalprogram_thismonth_grand_total + $automatic_thismonth_amount + $spf_thismonth_amount + $unprogrammed_thismonth_amount;
$grandtotal_todate_grand_total = $totalprogram_todate_grand_total + $automatic_todate_amount + $spf_todate_amount + $unprogrammed_todate_amount;
$grandtotal_grand_unobligated = $totalprogram_grand_unobligated + $automatic_unobligated_amount + $spf_unobligated_amount + $unprogrammed_unobligated_amount;
$grandtotal_grand_percentage_minus = ($grandtotal_todate_grand_total / $grandtotal_total_project_budget) * 100;

session()->set([
    'grandtotal_total_project_budget' => $grandtotal_total_project_budget,
    'grandtotal_todate_grand_total'   => $grandtotal_todate_grand_total,
    'grandtotal_grand_unobligated'   => $grandtotal_grand_unobligated,
    'grandtotal_grand_percentage_minus'   => $grandtotal_grand_percentage_minus,
]);

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(86, 3.5, '', 'TRL', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'TRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'TRL', 0, 'R'); // Percentage column
$Y = $pdf->GetY() + 3.5;  

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(86, 3.5, 'GRAND TOTAL, CURRENT YEAR BUDGET', 'BRL', 0, 'L'); // First column
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_total_project_budget,2), 'BRL', 0, 'R'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_thismonth_grand_total,2), 'BRL', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_todate_grand_total,2), 'BRL', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, number_format($grandtotal_grand_unobligated,2), 'BRL', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, number_format($grandtotal_grand_percentage_minus, 2) . '%', 'BRL', 0, 'R'); // Percentage column


//----------------------------------------------------------------------- SIGNATORIES -----------------------------------------------------------------------------------------

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, '', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, '', 0, 'L'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, 'Certified Correct:', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 3.5, 'Approved by:', 0, 'L'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

//-------------------------------------------------------------------- BLANK SPACES -----------------------------------------------------------------------------------------

$Y = $pdf->GetY() + 3.5;  
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 14, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 14, '', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'L'); // Budget column

$pdf->SetXY(118, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'R'); // This month column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 14, '', 0, 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 14, '', 'R', 0, 'R'); // Percentage column

//---------------------------------------------------------------------- SIGNATURE -------------------------------------------------------------------------------------------
$Y = $pdf->GetY() + 14; 
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, 'ROMANA L. LLAMAS', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(44, 3.5, 'ATTY. LUCIEDEN G. RAZ', 0, 0, 'L'); // Budget column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, 'DATE', 0, 0, 'L'); // Unobligated column
$pdf->SetFont('Arial', '', 8);

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

//---------------------------------------------------------------------- POSITION -------------------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5; 
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, 'Administrative Officer V', 0, 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(44, 3.5, 'Deputy Director / Director III', 0, 0, 'L'); // Budget column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 0, 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 0, 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'R', 0, 'R'); // Percentage column

//---------------------------------------------------------------------- POSITION -------------------------------------------------------------------------------------------
$Y = $pdf->GetY() + 3.5; 
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'LB', 0, 'L'); // First column

$pdf->SetXY(25, $Y);
$pdf->Cell(71, 3.5, '', 'B', 0, 'L'); // First column

$pdf->SetXY(96, $Y);
$pdf->Cell(44, 3.5, 'Officer-in-Charge, Office of the Director', 'B', 0, 'L'); // Budget column

$pdf->SetXY(140, $Y);
$pdf->Cell(22, 3.5, '', 'B', 0, 'R'); // To date column

$pdf->SetXY(162, $Y);
$pdf->Cell(22, 3.5, '', 'B', 0, 'R'); // Unobligated column

$pdf->SetXY(184, $Y);
$pdf->Cell(17, 3.5, '', 'RB', 0, 'R'); // Percentage column

$pdf->Output();
exit;
?>