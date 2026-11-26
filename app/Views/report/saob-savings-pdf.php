<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$program_title = $this->request->getPostGet('program_title');
$current_year = $this->request->getPostGet('current_year');
$monthInput    = $this->request->getPostGet('month');
$monthName = 'test';
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));
$disp_program_title = '';
$disp_responsiblity_code = '';
if(!empty($monthInput)) {

    // First day of month
    $start_date = date("Y-m-01", strtotime($monthInput));

    // Last day of month
    $end_date = date("Y-m-t", strtotime($monthInput));

    // Extract year if needed
    $current_year = date("Y", strtotime($monthInput));

   $status_month = date("F d, Y", strtotime($end_date));

   $monthName = date("F", strtotime($monthInput));

} else {
    die("Month is required.");
}



// Convert full name to initials + last name (e.g. "Carl Vincent D. Cabanilla" → "CVDCabanilla")
function abbreviate_project_leader($fullName) {
    $s = trim((string) $fullName);
    if ($s === '') return $s;
    $parts = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) <= 1) return $s;
    $last = array_pop($parts);
    $initials = '';
    foreach ($parts as $p) {
        $p = trim($p, '. ');
        if ($p !== '') $initials .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $initials . $last;
}

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



// Draw the 5-row table header (reused on first page and on each new page)
function draw_saob_savings_table_header($pdf, $monthName, $startY = null) {
    if ($startY !== null) {
        $pdf->SetY($startY);
    }

    //mock ROW
    $Y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetFillColor(220, 235, 255);
    $pdf->Cell(32, 3, '' , 'LT', 1, 'C',true);
    $pdf->SetXY(42, $Y);
    $pdf->Cell(37, 3, '' , 'LT', 1, 'C',true);
    $pdf->SetXY(79, $Y);
    $pdf->Cell(17, 3, '' , 'LT', 1, 'C',true);
    $pdf->SetXY(96, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetXY(120, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetFillColor(255, 220, 220);
    $pdf->SetXY(144, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetFillColor(220, 235, 255);
    $pdf->SetXY(168, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetXY(192, $Y);
    $pdf->Cell(48, 3, 'OBLIGATIONS' , 'LT', 1, 'C', true);

    $pdf->SetXY(240, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetXY(264, $Y);
    $pdf->Cell(24, 3, '' , 'RLT', 1, 'C', true);

    //2ND
    $Y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetFillColor(220, 235, 255);
    $pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(42, $Y);
    $pdf->Cell(37, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(79, $Y);
    $pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(96, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(120, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetFillColor(255, 220, 220);
    $pdf->SetXY(144, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetFillColor(220, 235, 255);
    $pdf->SetXY(168, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetXY(192, $Y);
    $pdf->Cell(24, 3, '' , 'TL', 1, 'C', true);

    $pdf->SetXY(216, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetXY(240, $Y);
    $pdf->Cell(24, 3, '' , 'LT', 1, 'C', true);

    $pdf->SetXY(264, $Y);
    $pdf->Cell(24, 3, '' , 'RL', 1, 'C', true);

    //3RD
    $Y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetFillColor(220, 235, 255);
    $pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(42, $Y);
    $pdf->Cell(37, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(79, $Y);
    $pdf->Cell(17, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(96, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(120, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetFillColor(255, 220, 220);
    $pdf->SetXY(144, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetFillColor(220, 235, 255);
    $pdf->SetXY(168, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(192, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(216, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(240, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(264, $Y);
    $pdf->Cell(24, 3, '' , 'RL', 1, 'C', true);

    //4TH
    $Y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetFillColor(220, 235, 255);
    $pdf->Cell(32, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(42, $Y);
    $pdf->Cell(37, 3, '' , 'L', 1, 'C',true);
    $pdf->SetXY(79, $Y);
    $pdf->Cell(17, 3, 'Implementing' , 'L', 1, 'C',true);
    $pdf->SetXY(96, $Y);
    $pdf->Cell(24, 3, 'Project' , 'L', 1, 'C', true);

    $pdf->SetXY(120, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetFillColor(255, 220, 220);
    $pdf->SetXY(144, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetFillColor(220, 235, 255);
    $pdf->SetXY(168, $Y);
    $pdf->Cell(24, 3, 'Revised' , 'L', 1, 'C', true);

    $pdf->SetXY(192, $Y);
    $pdf->Cell(24, 3, 'This month' , 'L', 1, 'C', true);

    $pdf->SetXY(216, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(240, $Y);
    $pdf->Cell(24, 3, '' , 'L', 1, 'C', true);

    $pdf->SetXY(264, $Y);
    $pdf->Cell(24, 3, 'Percentage' , 'RL', 1, 'C', true);

    //5TH
    $Y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetFillColor(220, 235, 255);
    $pdf->Cell(32, 3, 'Responsibility Center' , 'BL', 1, 'C',true);
    $pdf->SetXY(42, $Y);
    $pdf->Cell(37, 3, 'Program/Project Title' , 'BL', 1, 'C',true);
    $pdf->SetXY(79, $Y);
    $pdf->Cell(17, 3, 'Unit/Division' , 'BL', 1, 'C',true);
    $pdf->SetXY(96, $Y);
    $pdf->Cell(24, 3, 'Leader' , 'BL', 1, 'C', true);

    $pdf->SetXY(120, $Y);
    $pdf->Cell(24, 3, 'Allotment' , 'BL', 1, 'C', true);

    $pdf->SetFillColor(255, 220, 220);
    $pdf->SetXY(144, $Y);
    $pdf->Cell(24, 3, 'Admin. Cost' , 'BL', 1, 'C', true);

    $pdf->SetFillColor(220, 235, 255);
    $pdf->SetXY(168, $Y);
    $pdf->Cell(24, 3, 'Allotment' , 'BL', 1, 'C', true);

    $pdf->SetXY(192, $Y);
    $pdf->Cell(24, 3, $monthName , 'BL', 1, 'C', true);

    $pdf->SetXY(216, $Y);
    $pdf->Cell(24, 3, 'To date' , 'BL', 1, 'C', true);

    $pdf->SetXY(240, $Y);
    $pdf->Cell(24, 3, 'Balance' , 'BL', 1, 'C', true);

    $pdf->SetXY(264, $Y);
    $pdf->Cell(24, 3, 'of utilization' , 'BRL', 1, 'C', true);

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
$pdf->Cell(30, 4, $status_month , 0, 1, 'L');

draw_saob_savings_table_header($pdf, $monthName);

// 6TH ROW (DATA)
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);

//SUM UP OF DATA

// totals of the program row (order does not matter here)
$rw = model('App\Models\MySaobReportModel')->monthly_status_rows($program_title, $monthInput); // same rows as the status dashboard
$total_allotment = 0;
$total_revised = 0;
$total_thismonth = 0;
$total_todate = 0;
$total_balance = 0;
$total_percent = 0;
$total_admin = 0;
foreach ($rw as $data) {

   $allotment  = $data['allotment'];
   $admin_cost = $data['admin_cost'];
   $thismonth  = $data['this_month'];
   $todate     = $data['todate'];

   $revised  = $allotment + $admin_cost;
   $balance  = $revised - $todate;
   

   $total_allotment += $allotment;
   $total_revised += $revised;
   $total_thismonth += $thismonth;
   $total_admin += $admin_cost;
   $total_todate += $todate;
   $total_balance += $balance;

}
$total_percent  = ($total_revised > 0) ? round(($total_todate/$total_revised)*100,2).'%' : '0%';


if ($program_title == 'Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition') {
   $disp_program_title = 'Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition';
   $disp_responsiblity_code = 'II.a.1';
}elseif ($program_title == 'Technical Services on Food and Nutrition') {
   $disp_program_title = 'Food and Nutrition Technology and Knowledge Diffusion Program';
   $disp_responsiblity_code = 'II.b.2';
}elseif ($program_title == 'Nutritional Assessment and Monitoring on Food and Nutrition') {
   $disp_program_title = 'Nutritional Assessment and Monitoring on Food and Nutrition';
   $disp_responsiblity_code = 'II.b.1';
}elseif ($program_title == 'Expanded National Nutrition Survey') {
   $disp_program_title = 'Locally Funded Project (LFP) - NAMD';
   $disp_responsiblity_code = 'LFP';
}elseif ($program_title == "Expanding the FNRI's Nutrigenomics Laboratory: Towards Establishment of a World Class Philippines Nutrigenomics Center") {
   $disp_program_title = 'Locally Funded Project (LFP) - R&D on Nutrigenomics';
   $disp_responsiblity_code = 'LFP';
}

/* =========================
   PROGRAM / PROJECT TITLE
   (Draw first to detect height)
========================= */
$pdf->SetXY(42, $Y);
$pdf->MultiCell(
    78,   // MUST match header width
    3,
    $disp_program_title,
    'BL',
    'L'
);

// Calculate row height based on multicell
$rowHeight = $pdf->GetY() - $Y;

/* =========================
   RESPONSIBILITY CENTER
========================= */
$pdf->SetXY(10, $Y);
$pdf->Cell(32, $rowHeight, $disp_responsiblity_code, 'BL', 0, 'C');

/* =========================
   ALLOTMENT
========================= */
$pdf->SetXY(120, $Y);
$pdf->Cell(24, $rowHeight,  number_format($total_allotment,2), 'BL', 0, 'R');

/* =========================
   ADMIN COST (RED COLUMN)
========================= */
$pdf->SetFillColor(255, 220, 220);
$pdf->SetXY(144, $Y);
$pdf->Cell(24, $rowHeight, number_format($total_admin,2), 'BL', 0, 'R');

/* =========================
   REVISED ALLOTMENT
========================= */
$pdf->SetXY(168, $Y);
$pdf->Cell(24, $rowHeight, number_format($total_revised,2), 'BL', 0, 'R');

/* =========================
   THIS MONTH
========================= */
$pdf->SetXY(192, $Y);
$pdf->Cell(24, $rowHeight, number_format($total_thismonth,2), 'BL', 0, 'R');

/* =========================
   TO DATE
========================= */
$pdf->SetXY(216, $Y);
$pdf->Cell(24, $rowHeight, number_format($total_todate,2), 'BL', 0, 'R');

/* =========================
   BALANCE
========================= */
$pdf->SetXY(240, $Y);
$pdf->Cell(24, $rowHeight, number_format($total_balance,2), 'BL', 0, 'R');

/* =========================
   % UTILIZATION
========================= */
$pdf->SetXY(264, $Y);
$pdf->Cell(24, $rowHeight, $total_percent, 'BLR', 0, 'R');

/* =========================
   MOVE TO NEXT ROW
========================= */
$pdf->SetY($Y + $rowHeight);

$pdf->SetY($pdf->GetY());


//THIS FOR LOOPING
// 6TH ROW (DATA)
// $Y = $pdf->GetY();
// $pdf->SetFont('Arial', '', 6);

// /* =========================
//    PROGRAM / PROJECT TITLE
//    (Draw first to detect height)
// ========================= */
// $pdf->SetXY(42, $Y);
// $pdf->MultiCell(
//     37,   // MUST match header width
//     3,
//     'Project Title value value value value value value value value value value value',
//     'BL',
//     'L'
// );

// // Calculate row height based on multicell
// $rowHeight = $pdf->GetY() - $Y;


// /* =========================
//    RESPONSIBILITY CENTER
// ========================= */
// $pdf->SetXY(10, $Y);
// $pdf->Cell(32, $rowHeight, 'II.a.1', 'BL', 0, 'C');


// /* =========================
//    UNIT / DIVISION
// ========================= */
// $pdf->SetXY(79, $Y);
// $pdf->Cell(17, $rowHeight, 'NFRDD', 'BL', 0, 'C');


// /* =========================
//    PROJECT LEADER
// ========================= */
// $pdf->SetXY(96, $Y);
// $pdf->Cell(24, $rowHeight, 'VI Ramas', 'BL', 0, 'C');


// /* =========================
//    ALLOTMENT
// ========================= */
// $pdf->SetXY(120, $Y);
// $pdf->Cell(24, $rowHeight, '0', 'BL', 0, 'R');


// /* =========================
//    ADMIN COST (RED COLUMN)
// ========================= */
// $pdf->SetFillColor(255, 220, 220);
// $pdf->SetXY(144, $Y);
// $pdf->Cell(24, $rowHeight, '0', 'BL', 0, 'R', true);


// /* =========================
//    REVISED ALLOTMENT
// ========================= */
// $pdf->SetFillColor(255,255,255);
// $pdf->SetXY(168, $Y);
// $pdf->Cell(24, $rowHeight, '0', 'BL', 0, 'R');


// /* =========================
//    THIS MONTH
// ========================= */
// $pdf->SetXY(192, $Y);
// $pdf->Cell(24, $rowHeight, '0', 'BL', 0, 'R');


// /* =========================
//    TO DATE
// ========================= */
// $pdf->SetXY(216, $Y);
// $pdf->Cell(24, $rowHeight, '0', 'BL', 0, 'R');


// /* =========================
//    BALANCE
// ========================= */
// $pdf->SetXY(240, $Y);
// $pdf->Cell(24, $rowHeight, '0', 'BL', 0, 'R');


// /* =========================
//    % UTILIZATION
// ========================= */
// $pdf->SetXY(264, $Y);
// $pdf->Cell(24, $rowHeight, '0%', 'BLR', 0, 'R');


// /* =========================
//    MOVE TO NEXT ROW
// ========================= */
// $pdf->SetY($Y + $rowHeight);



// Build date bounds in PHP so the query gets valid SQL (e.g. 2026-01-01, 2026-12-31)
$year_start = $current_year . '-01-01';
$year_end   = $current_year . '-12-31';
$dec_start  = $current_year . '-12-01';
$dec_end    = ($current_year + 1) . '-01-01'; // first day of next year, for ORs range

$rw = model('App\Models\MySaobReportModel')->monthly_status_rows($program_title, $monthInput); // same rows as the status dashboard
$pdf->SetFont('Arial', '', 6);
$page_break_y = 195; // A4 landscape height 210mm - margin; add new page before row would exceed this
// ============================
// LOOP DATA ROWS
// ============================


foreach ($rw as $data) {
    // 1. Enhanced Page Break Check
    // We check if there's enough room for a standard row (approx 15-20 units)
    if ($pdf->GetY() + 25 > $page_break_y) {
        $pdf->AddPage();
        draw_saob_savings_table_header($pdf, $monthName, 10);
    }

    $responsibility_code = $data['responsibility_code'];
    $project_title       = $data['project_title'];
    $project_leader      = abbreviate_project_leader($data['project_leader']);

    // Handle the "Unprogrammed" display for the Responsibility Center column
    $display_code = ($project_title == 'Unprogrammed') ? '-' : $responsibility_code;

    $allotment  = (float)$data['allotment'];
    $admin_cost = (float)$data['admin_cost'];
    $thismonth  = (float)$data['this_month'];
    $todate     = (float)$data['todate'];
    
    $revised    = $allotment + $admin_cost;
    $balance    = $revised - $todate;
    $percent    = ($revised > 0) ? round(($todate / $revised) * 100, 2) . '%' : '0%';

    $Y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 6);

    // Get Division (Optimization: Consider joining this in your main SQL query to avoid N+1 queries)
    $query = $this->db->query("SELECT division_name FROM tbl_budget_hd WHERE project_title = ".$this->db->escape($project_title));
    $rowDiv = $query->getRowArray();
    $unit_division = $rowDiv['unit_division'] ?? $data['unit_division'];

    /* ==========================================================
       STEP 1: HEIGHT DRIVER (MultiCell for Project Title)
    ========================================================== */
    $pdf->SetXY(42, $Y);
    // Use a small line height (3) for the MultiCell
    $pdf->MultiCell(37, 3, $project_title, 0, 'L'); 
    $rowHeight = $pdf->GetY() - $Y;
    
    // Ensure a minimum height for the row for better spacing
    if ($rowHeight < 6) $rowHeight = 6;

    /* ==========================================================
       STEP 2: DRAW BORDERS AND CELLS
    ========================================================== */
    // We go back to the start of the row and draw everything with the same $rowHeight
    
    // Responsibility Center
    $pdf->SetXY(10, $Y);
    $pdf->Cell(32, $rowHeight, $display_code, 'BL', 0, 'C');

    // Title Border (We already drew the text, now we draw the box)
    $pdf->SetXY(42, $Y);
    $pdf->Cell(37, $rowHeight, '', 'BL', 0, 'L');

    // Unit / Division
    $pdf->SetXY(79, $Y);
    $pdf->Cell(17, $rowHeight, $unit_division, 'BL', 0, 'C');

    // Project Leader
    $pdf->SetXY(96, $Y);
    $pdf->Cell(24, $rowHeight, $project_leader, 'BL', 0, 'C');

    // Allotment
    $pdf->SetXY(120, $Y);
    $pdf->Cell(24, $rowHeight, number_format($allotment, 2), 'BL', 0, 'R');

    // Admin Cost (Red Handling)
    $formatted_admin_cost = ($admin_cost < 0)
        ? '(' . number_format(abs($admin_cost), 2) . ')'
        : number_format($admin_cost, 2);

    $pdf->SetXY(144, $Y);
    $pdf->Cell(24, $rowHeight, $formatted_admin_cost, 'BL', 0, 'R');

    // Revised, This Month, To Date, Balance
    $pdf->SetXY(168, $Y);
    $pdf->Cell(24, $rowHeight, number_format($revised, 2), 'BL', 0, 'R');
    
    $pdf->SetXY(192, $Y);
    $pdf->Cell(24, $rowHeight, number_format($thismonth, 2), 'BL', 0, 'R');
    
    $pdf->SetXY(216, $Y);
    $pdf->Cell(24, $rowHeight, number_format($todate, 2), 'BL', 0, 'R');
    
    $pdf->SetXY(240, $Y);
    $pdf->Cell(24, $rowHeight, number_format($balance, 2), 'BL', 0, 'R');

    // % Utilization (End the row with 'BLR' to close the right border)
    $pdf->SetXY(264, $Y);
    $pdf->Cell(24, $rowHeight, $percent, 'BLR', 0, 'R');

    // Move Y to the bottom of this row
    $pdf->SetY($Y + $rowHeight);
}

/* ==========================================================
   FOOTER SECTION (Signatures)
========================================================== */
// Check if signatures will fit on current page (need about 30-40 units)
if ($pdf->GetY() > 160) { // Standard A4/Legal threshold
    $pdf->AddPage();
}

$Y = $pdf->GetY() + 10;
$pdf->SetFont('Arial', '', 8);

// "Prepared by" and "Certified Correct" Labels
$pdf->SetXY(10, $Y);
$pdf->Cell(60, 4, 'Prepared by:', 0, 0, 'L');
$pdf->SetXY(120, $Y); // Shifted right for better spacing
$pdf->Cell(60, 4, 'Certified Correct:', 0, 0, 'L');

$Y += 8; // Space for signature
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(60, 4, 'MILDRED D. VILLANUEVA', 0, 0, 'L');
$pdf->SetXY(120, $Y);
$pdf->Cell(60, 4, 'ROMANA L. LLAMAS', 0, 0, 'L');

$Y += 4;
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(60, 4, 'Administrative Officer IV', 0, 0, 'L');
$pdf->SetXY(120, $Y);
$pdf->Cell(60, 4, 'Administrative Officer V', 0, 0, 'L');


$pdf->Output();
exit;
?>