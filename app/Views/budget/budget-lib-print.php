<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$realign_id = $this->request->getPostGet('realign_id');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';

$query = $this->db->query("
SELECT
    `project_title`,
    `project_leader`,
    `program_title`,
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
$project_title = $data['project_title'];
$project_leader = $data['project_leader'];
$program_title = $data['program_title'];
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

$pdf = new \FPDF();
$pdf->AddPage();
$pdf->SetTitle('Project Line-Item Budget Print');
$pdf->SetFont('Arial', 'B', 16);



$pdf->SetXY(8, 10);

// Add image
$x = 10; // X position
$y = 12;   // Y position
$width = 16; // Width of the image
$height = 16; // Height of the image (you can adjust this based on your needs)

$pdf->Image(ROOTPATH . 'public/assets/images/logos/fnrilogo.png', $x, $y, $width, $height);

$X = 0;
$Y = 8;
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($X,$Y,'DOST Form 4',0,1,'C');

$pdf->SetFont('Arial', 'B', 7.5);
$Y = 4;
$pdf->Cell($X,$Y,'DEPARTMENT OF SCIENCE AND TECHNOLOGY',0,1,'C');
$pdf->Cell($X,$Y,'Project Line-Item Budget',0,1,'C');
$pdf->Cell(0, $Y, utf8_decode($cy_range), 0, 1,'C');

//spacer
$pdf->Cell($X,4,'',0,1,'L');

$pdf->SetFont('Arial', '', 7);

// Program Title (wraps if long, ':' separated)
$pdf->Cell(40, 3.5, 'Program Title', 0, 0, 'L'); // Label
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->MultiCell(145, 3.5, $program_title, 0, 'L');

// Optional: add a small line break or spacing after to separate rows
$pdf->Ln(1);

// Project Title (wraps if long)
$pdf->Cell(40, 3.5, 'Project Title', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->MultiCell(145, 3.5, $project_title, 0, 'L');


$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y
// Implementing Agency (below the wrapped Project Title)
$pdf->Cell(40, 3.5, 'Implementing Agency', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $implementing_agency, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Total Duration', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $project_duration, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Project Duration', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $duration_from . ' - ' . $duration_to, 0, 1, 'L');

if ($with_extension == '1') {
    $pdf->Cell(40, 3.5, 'Extended Duration', 0, 0, 'L');
    $pdf->Cell(5, 3.5, ':', 0, 0, 'L');
    $pdf->Cell(60, 3.5, $extended_from . ' - ' . $extended_to, 0, 1, 'L');
}

$pdf->Cell(40, 3.5, 'Collaborating Agency', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->MultiCell(145, 3.5, $collaborating_agencies, 0, 'L');

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y
$pdf->Cell(40, 3.5, 'Program Leader', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $program_leader, 0, 1, 'L');

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y
$pdf->Cell(40, 3.5, 'Project Leader', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $project_leader, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Funding Agency', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $funding_agency, 0, 1, 'L');

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY(100, $Y);
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(100, 3.5, $implementing_agency, 'B', 0, 'C');

$Y+= 3.5;

$pdf->SetXY(100, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, 'As Approved', 'B', 0, 'C');

$pdf->SetXY(120, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, '1st reprogramming', 'B', 0, 'C');

$pdf->SetXY(140, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, '2nd reprogramming', 'B', 0, 'C');

$pdf->SetXY(160, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, '3rd reprogramming', 'B', 0, 'C');

$pdf->SetXY(180, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, 'Proposed Realignment', 'B', 0, 'C');

//START OF PS LOGIC
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(5, 3.5, 'I.', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'PERSONAL SERVICES (PS)' , 0, 1, 'L');
$Y += 3;
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Direct Cost:', 0, 1, 'L');
$Y += 2;
$query = $this->db->query("
SELECT
    a.`particulars`,
    a.`approved_budget`,
    (SELECT `object_code` FROM mst_uacs WHERE `uacs_code` = a.`code` LIMIT 1) object_code,
    a.`expense_item`,
    r1_approved_budget,
    r2_approved_budget,
    r3_approved_budget,
    proposed_realignment
FROM
    `tbl_budget_direct_ps_dt` a
WHERE 
    `project_id` = '$recid'
ORDER BY
a.`recid` ASC"
);

$data = $query->getResultArray();
$total_direct_ps = 0;
$total_direct_proposed_ps = 0;
$last_particulars = '';

$last_object_code = '';

foreach ($data as $row) {
    $object_code = $row['object_code'];
    $expense_item = $row['expense_item'];
    $approved_budget = $row['approved_budget'];
    $particulars = $row['particulars'];
    $r1_approved_budget = $row['r1_approved_budget'];
    $r2_approved_budget = $row['r2_approved_budget'];
    $r3_approved_budget = $row['r3_approved_budget'];
    $proposed_realignment = $row['proposed_realignment'];

    $Y += 2.5;
    if ($object_code !== $last_object_code && $object_code !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(10, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $object_code, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_object_code = $object_code;
    }
    
    // Print Expenditure Category if it changes
    if ($particulars !== $last_particulars && $particulars !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(15, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $particulars, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_particulars = $particulars;
    }

    $Y = $pdf->GetY();
    $Y += 1;
    // Print Particulars
    $pdf->SetFont('Arial', 'I', 7);
    $expense_item = str_replace(["\r", "\n"], '', $expense_item);
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetXY(25, $Y);
    $pdf->MultiCell(75, 2.5, $expense_item, 0, 'L'); // full width usage
    $Y += 3;
 
    if (empty($expense_item)) {
        $Y = $pdf->GetY() -5;
    }else{
        $Y = $pdf->GetY() -2.5;
    }

    // Print Budget
    $pdf->SetXY(100, $Y);
    $pdf->Cell(20, 2, ($approved_budget == 0.00 || !is_numeric($approved_budget)) ? '---' : number_format((float)$approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(120, $Y);
    $pdf->Cell(20, 2, ($r1_approved_budget == 0.00 || !is_numeric($r1_approved_budget)) ? '---' : number_format((float)$r1_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(140, $Y);
    $pdf->Cell(20, 2, ($r2_approved_budget == 0.00 || !is_numeric($r2_approved_budget)) ? '---' : number_format((float)$r2_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(160, $Y);
    $pdf->Cell(20, 2, ($r3_approved_budget == 0.00 || !is_numeric($r3_approved_budget)) ? '---' : number_format((float)$r3_approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(180, $Y);
    $pdf->Cell(20, 2, number_format($proposed_realignment, 2), 0, 1, 'C');

    $total_direct_ps += $approved_budget;
    $total_direct_proposed_ps += $proposed_realignment;
    $Y = $pdf->GetY();

}
$Y += 2;
//INDIRECT COST
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Indirect Cost:', 0, 1, 'L');
$Y += 2;
$query = $this->db->query("
SELECT
    a.`particulars`,
    a.`expense_item`,
    (SELECT `object_code` FROM mst_uacs WHERE `uacs_code` = a.`code` LIMIT 1) object_code,
    a.`approved_budget`,
     r1_approved_budget,
     r2_approved_budget,
     r3_approved_budget,
     proposed_realignment
FROM
    `tbl_budget_indirect_ps_dt` a
WHERE 
    `project_id` = '$recid'
ORDER BY
particulars ASC"
);

$data = $query->getResultArray();
$total_indirect_ps = 0;
$total_indirect_proposed_ps = 0;
$last_particulars = '';
$last_object_code = '';
foreach ($data as $row) {
    $expense_item = $row['expense_item'];
    $object_code = $row['object_code'];
    $approved_budget = $row['approved_budget'];
    $particulars = $row['particulars'];
    $r1_approved_budget = $row['r1_approved_budget'];
    $r2_approved_budget = $row['r2_approved_budget'];
    $r3_approved_budget = $row['r3_approved_budget'];
    $proposed_realignment = $row['proposed_realignment'];

    $Y += 2.5;
    if ($object_code !== $last_object_code && $object_code !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(10, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $object_code, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_object_code = $object_code;
    }

    // Print Expenditure Category if it changes
    if ($particulars !== $last_particulars && $particulars !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(15, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $particulars, 0, 'L'); // full width usage
        $Y += 2.5;
        $last_particulars = $particulars;
    }
    $Y = $pdf->GetY();
    $Y += 1;

    $pdf->SetFont('Arial', 'I', 7);
    $expense_item = str_replace(["\r", "\n"], '', $expense_item);
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetXY(25, $Y);
    $pdf->MultiCell(80, 2.5, $expense_item, 0, 'L'); // full width usage
    $Y += 3;
 
    if (empty($expense_item)) {
        $Y = $pdf->GetY() -5;
    }else{
        $Y = $pdf->GetY() -2.5;
    }
    // Print Budget
    $pdf->SetXY(100, $Y);
    $pdf->Cell(20, 2, ($approved_budget == 0.00 || !is_numeric($approved_budget)) ? '---' : number_format((float)$approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(120, $Y);
    $pdf->Cell(20, 2, ($r1_approved_budget == 0.00 || !is_numeric($r1_approved_budget)) ? '---' : number_format((float)$r1_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(140, $Y);
    $pdf->Cell(20, 2, ($r2_approved_budget == 0.00 || !is_numeric($r2_approved_budget)) ? '---' : number_format((float)$r2_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(160, $Y);
    $pdf->Cell(20, 2, ($r3_approved_budget == 0.00 || !is_numeric($r3_approved_budget)) ? '---' : number_format((float)$r3_approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(180, $Y);
    $pdf->Cell(20, 2, number_format($proposed_realignment, 2), 0, 1, 'C');

    $total_indirect_ps += $approved_budget;
    $total_indirect_proposed_ps += $proposed_realignment;
    $Y = $pdf->GetY();
}

$total_ps = $total_direct_ps + $total_indirect_ps;
$total_proposed_ps = $total_direct_proposed_ps + $total_indirect_proposed_ps;
$Y+= 3.5;

//P IN HONORARIA

$pdf->SetXY(100, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(120, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(140, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(160, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(180, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(73, $Y);
$pdf->Cell(20, 3.5, 'Sub-total for PS' , 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(97, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(100, $Y);
$pdf->Cell(20, 3.5, number_format($total_ps,2) , 0, 1, 'C');
$pdf->SetXY(177, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(180, $Y);
$pdf->Cell(20, 3.5, number_format($total_proposed_ps,2) , 0, 1, 'C');

$Y+= 3.5;

// MOOE LOGIC START -------------------------------------------------------------------------------------------------------------------
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(5, 3.5, 'II.', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'MAINTENANCE AND OTHER OPERATING EXPENSES (MOOE)' , 0, 1, 'L');
$Y += 3;
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Direct Cost:', 0, 1, 'L');
$Y += 3.5;
$query = $this->db->query("
    SELECT
        b.`particulars`,
        b.`approved_budget`,
        (SELECT `object_code` FROM mst_uacs WHERE `uacs_code` = b.`code` LIMIT 1) object_code,
        b.`expense_item`,
        r1_approved_budget,
        r2_approved_budget,
        r3_approved_budget,
        proposed_realignment
         
    FROM
        `tbl_budget_direct_mooe_dt` b
    WHERE 
        `project_id` = '$recid'
    ORDER BY
       b.`recid` ASC
");

$data = $query->getResultArray();
$total_direct_mooe = 0;
$total_direct_proposed_mooe = 0;

$last_particulars = '';
$last_object_code = '';
foreach ($data as $row) {
    $object_code = $row['object_code'];
    $expense_item = $row['expense_item'];
    $particulars = $row['particulars'];
    $approved_budget = $row['approved_budget'];
    $r1_approved_budget = $row['r1_approved_budget'];
    $r2_approved_budget = $row['r2_approved_budget'];
    $r3_approved_budget = $row['r3_approved_budget'];
    $proposed_realignment = $row['proposed_realignment'];

    if ($Y > 270) {
        $pdf->AddPage();
        $Y = $pdf->GetY(); // or set manually, e.g., $Y = 10;
    }

    $Y += 2.5;
    if ($object_code !== $last_object_code && $object_code !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(10, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $object_code, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_object_code = $object_code;
    }
    // Print Expenditure Category if it changes
    if ($particulars !== $last_particulars && $particulars !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(15, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $particulars, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_particulars = $particulars;
    }
    $Y = $pdf->GetY();
    $Y += 1;
    // Print Particulars
    $pdf->SetFont('Arial', 'I', 7);
    $expense_item = str_replace(["\r", "\n"], '', $expense_item);
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetXY(25, $Y);
    $pdf->MultiCell(80, 2.5, $expense_item, 0, 'L'); // full width usage
    $Y += 3;
 
    if (empty($expense_item)) {
        $Y = $pdf->GetY() -5;
    }else{
        $Y = $pdf->GetY() -2.5;
    }

    // Print Budget
    $pdf->SetXY(100, $Y);
    $pdf->Cell(20, 2, ($approved_budget == 0.00 || !is_numeric($approved_budget)) ? '---' : number_format((float)$approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(120, $Y);
    $pdf->Cell(20, 2, ($r1_approved_budget == 0.00 || !is_numeric($r1_approved_budget)) ? '---' : number_format((float)$r1_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(140, $Y);
    $pdf->Cell(20, 2, ($r2_approved_budget == 0.00 || !is_numeric($r2_approved_budget)) ? '---' : number_format((float)$r2_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(160, $Y);
    $pdf->Cell(20, 2, ($r3_approved_budget == 0.00 || !is_numeric($r3_approved_budget)) ? '---' : number_format((float)$r3_approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(180, $Y);
    $pdf->Cell(20, 2, number_format($proposed_realignment, 2), 0, 1, 'C');

    $total_direct_mooe += $approved_budget;
    $total_direct_proposed_mooe += $proposed_realignment;
    $Y = $pdf->GetY();
}

$Y += 2;
if ($Y > 270) {
    $pdf->AddPage();
    $Y = $pdf->GetY(); // or set manually, e.g., $Y = 10;
}
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Indirect Cost:', 0, 1, 'L');
$Y += 3.5;
$query = $this->db->query("
    SELECT
        b.`particulars`,
        b.`approved_budget`,
        (SELECT `object_code` FROM mst_uacs WHERE `uacs_code` = b.`code` LIMIT 1) object_code,
        b.`expense_item`,
        r1_approved_budget,
        r2_approved_budget,
        r3_approved_budget,
        proposed_realignment
    FROM
        `tbl_budget_indirect_mooe_dt` b
    WHERE 
        `project_id` = '$recid'
    ORDER BY
        b.`recid` ASC
");

$data = $query->getResultArray();
$total_indirect_mooe = 0;
$total_indirect_proposed_mooe = 0;
$last_particulars = '';
$last_object_code = '';
foreach ($data as $row) {
    $object_code = $row['object_code'];
    $expense_item = $row['expense_item'];
    $particulars = $row['particulars'];
    $approved_budget = $row['approved_budget'];
    $r1_approved_budget = $row['r1_approved_budget'];
    $r2_approved_budget = $row['r2_approved_budget'];
    $r3_approved_budget = $row['r3_approved_budget'];
    $proposed_realignment = $row['proposed_realignment'];

    if ($Y > 270) {
        $pdf->AddPage();
        $Y = $pdf->GetY(); // or set manually, e.g., $Y = 10;
    }

    $Y += 2.5;
    if ($object_code !== $last_object_code && $object_code !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(10, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $object_code, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_object_code = $object_code;
    }
   // Print Expenditure Category if it changes
    if ($particulars !== $last_particulars && $particulars !== null) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(15, $Y);
        $pdf->Cell(5, 3.5, '', 0, 0, 'L');
        $pdf->MultiCell(80, 2.5, $particulars, 0, 'L'); // full width usage
        $Y += 3.5;
        $last_particulars = $particulars;
    }
    $Y = $pdf->GetY();
    $Y += 1;
    // Print Particulars
    $pdf->SetFont('Arial', 'I', 7);
    $expense_item = str_replace(["\r", "\n"], '', $expense_item);
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetXY(25, $Y);
    $pdf->MultiCell(80, 2.5, $expense_item, 0, 'L'); // full width usage
    $Y += 3;
 
    if (empty($expense_item)) {
        $Y = $pdf->GetY() -5;
    }else{
        $Y = $pdf->GetY() -2.5;
    }

    // Print Budget
    $pdf->SetXY(100, $Y);
    $pdf->Cell(20, 2, ($approved_budget == 0.00 || !is_numeric($approved_budget)) ? '---' : number_format((float)$approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(120, $Y);
    $pdf->Cell(20, 2, ($r1_approved_budget == 0.00 || !is_numeric($r1_approved_budget)) ? '---' : number_format((float)$r1_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(140, $Y);
    $pdf->Cell(20, 2, ($r2_approved_budget == 0.00 || !is_numeric($r2_approved_budget)) ? '---' : number_format((float)$r2_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(160, $Y);
    $pdf->Cell(20, 2, ($r3_approved_budget == 0.00 || !is_numeric($r3_approved_budget)) ? '---' : number_format((float)$r3_approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(180, $Y);
    $pdf->Cell(20, 2, number_format($proposed_realignment, 2), 0, 1, 'C');

    $total_indirect_mooe += $approved_budget;
    $total_indirect_proposed_mooe += $proposed_realignment;
    $Y = $pdf->GetY();
}

$total_mooe = $total_direct_mooe + $total_indirect_mooe;
$total_proposed_mooe = $total_direct_proposed_mooe + $total_indirect_proposed_mooe;
$Y += 3.5;

$pdf->SetXY(100, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(120, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(140, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(160, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(180, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(73, $Y);
$pdf->Cell(20, 3.5, 'Sub-total for MOOE' , 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(97, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(100, $Y);
$pdf->Cell(20, 3.5, number_format($total_mooe,2) , 0, 1, 'C');
$pdf->SetXY(177, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(180, $Y);
$pdf->Cell(20, 3.5, number_format($total_proposed_mooe,2) , 0, 1, 'C');


//CO LOGIC START ------------------------------------------------------------------------------------------------------------------------------------------------

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(8, 10);

// Add image
$x = 10; // X position
$y = 12;   // Y position
$width = 16; // Width of the image
$height = 16; // Height of the image (you can adjust this based on your needs)

$pdf->Image(ROOTPATH . 'public/assets/images/logos/fnrilogo.png', $x, $y, $width, $height);

$X = 0;
$Y = 8;
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($X,$Y,'DOST Form 4',0,1,'C');

$pdf->SetFont('Arial', 'B', 7.5);
$Y = 4;
$pdf->Cell($X,$Y,'DEPARTMENT OF SCIENCE AND TECHNOLOGY',0,1,'C');
$pdf->Cell($X,$Y,'Project Line-Item Budget',0,1,'C');
$pdf->Cell($X,$Y,'CY _____',0,1,'C');

//spacer
$pdf->Cell($X,4,'',0,1,'L');

$pdf->SetFont('Arial', '', 7);

// Program Title (wraps if long, ':' separated)
$pdf->Cell(40, 3.5, 'Program Title', 0, 0, 'L'); // Label
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->MultiCell(145, 3.5, $program_title, 0, 'L');

// Optional: add a small line break or spacing after to separate rows
$pdf->Ln(1);

// Project Title (wraps if long)
$pdf->Cell(40, 3.5, 'Project Title', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');              // Colon

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY($X, $Y); // Set cursor at value position
$pdf->MultiCell(145, 3.5, $project_title, 0, 'L');


$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y
// Implementing Agency (below the wrapped Project Title)
$pdf->Cell(40, 3.5, 'Implementing Agency', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $implementing_agency, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Total Duration', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $project_duration, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Current Duration', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $duration_from . ' - ' . $duration_to, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Collaborating Agency', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');              // Colon
$pdf->Cell(60, 3.5, $collaborating_agencies, 0, 1, 'L');

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$Y+= 3.5;

$pdf->SetXY(100, $Y);
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(100, 3.5, $implementing_agency, 'B', 0, 'C');

$Y+= 3.5;

$pdf->SetXY(100, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, 'As Approved', 'B', 0, 'C');

$pdf->SetXY(120, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, '1st reprogramming', 'B', 0, 'C');

$pdf->SetXY(140, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, '2nd reprogramming', 'B', 0, 'C');

$pdf->SetXY(160, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, '3rd reprogramming', 'B', 0, 'C');

$pdf->SetXY(180, $Y);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(20, 3.5, 'Proposed Realignment', 'B', 0, 'C');

$Y+= 3.5;

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(5, 3.5, 'III.', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'CAPITAL OUTLAY (CO)' , 0, 1, 'L');

$Y += 3;
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Direct Cost:', 0, 1, 'L');
$query = $this->db->query("
SELECT
    b.`particulars`,
    b.`approved_budget`,
    b.`r1_approved_budget`,
    b.`r2_approved_budget`,
    b.`r3_approved_budget`,
    b.`proposed_realignment`
FROM
    `tbl_budget_direct_co_dt` b
WHERE 
    `project_id` = '$recid'

");

$data = $query->getResultArray();
$total_direct_co = 0;
$total_direct_proposed_co = 0;

foreach ($data as $row) {
    $particulars = $row['particulars'];
    $approved_budget = $row['approved_budget'];
    $r1_approved_budget = $row['r1_approved_budget'];
    $r2_approved_budget = $row['r2_approved_budget'];
    $r3_approved_budget = $row['r3_approved_budget'];
    $proposed_realignment = $row['proposed_realignment'];

    if ($Y > 270) {
        $pdf->AddPage();
        $Y = $pdf->GetY(); // or set manually, e.g., $Y = 10;
    }
    // Print the item line
    $Y += 3.5;
    // Print Particulars
    $pdf->SetFont('Arial', 'I', 7);
    $particulars = str_replace(["\r", "\n"], '', $particulars);
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetXY(20, $Y);
    $pdf->MultiCell(80, 2.5, $particulars, 0, 'L'); // full width usage

    // Print Budget
    $pdf->SetXY(100, $Y);
    $pdf->Cell(20, 2, ($approved_budget == 0.00 || !is_numeric($approved_budget)) ? '---' : number_format((float)$approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(120, $Y);
    $pdf->Cell(20, 2, ($r1_approved_budget == 0.00 || !is_numeric($r1_approved_budget)) ? '---' : number_format((float)$r1_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(140, $Y);
    $pdf->Cell(20, 2, ($r2_approved_budget == 0.00 || !is_numeric($r2_approved_budget)) ? '---' : number_format((float)$r2_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(160, $Y);
    $pdf->Cell(20, 2, ($r3_approved_budget == 0.00 || !is_numeric($r3_approved_budget)) ? '---' : number_format((float)$r3_approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(180, $Y);
    $pdf->Cell(20, 2, number_format($proposed_realignment, 2), 0, 1, 'C');

    $total_direct_co += $approved_budget;
    $total_direct_proposed_co += $proposed_realignment;
    $Y = $pdf->GetY();
    $Y += 3.5;
}

$Y += 3;
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(5, 3.5, '', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Indirect Cost:', 0, 1, 'L');
$query = $this->db->query("
SELECT
    b.`particulars`,
    b.`approved_budget`,
    b.`r1_approved_budget`,
    b.`r2_approved_budget`,
    b.`r3_approved_budget`,
    b.`proposed_realignment`
FROM
    `tbl_budget_indirect_co_dt` b
WHERE 
    `project_id` = '$recid'

");

$data = $query->getResultArray();
$total_indirect_co = 0;
$total_indirect_proposed_co = 0;

foreach ($data as $row) {
    $particulars = $row['particulars'];
    $approved_budget = $row['approved_budget'];
    $r1_approved_budget = $row['r1_approved_budget'];
    $r2_approved_budget = $row['r2_approved_budget'];
    $r3_approved_budget = $row['r3_approved_budget'];
    $proposed_realignment = $row['proposed_realignment'];

    if ($Y > 270) {
        $pdf->AddPage();
        $Y = $pdf->GetY(); // or set manually, e.g., $Y = 10;
    }
    // Print the item line
    $Y += 3.5;

    $pdf->SetFont('Arial', 'I', 7);
    $particulars = str_replace(["\r", "\n"], '', $particulars);
    $pdf->SetXY(20, $Y);
    $pdf->MultiCell(80, 2.5, $particulars, 0, 'L'); // full width usage

    // Print Budget
    $pdf->SetXY(100, $Y);
    $pdf->Cell(20, 2, ($approved_budget == 0.00 || !is_numeric($approved_budget)) ? '---' : number_format((float)$approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(120, $Y);
    $pdf->Cell(20, 2, ($r1_approved_budget == 0.00 || !is_numeric($r1_approved_budget)) ? '---' : number_format((float)$r1_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(140, $Y);
    $pdf->Cell(20, 2, ($r2_approved_budget == 0.00 || !is_numeric($r2_approved_budget)) ? '---' : number_format((float)$r2_approved_budget, 2), 0, 1, 'C');
    
    $pdf->SetXY(160, $Y);
    $pdf->Cell(20, 2, ($r3_approved_budget == 0.00 || !is_numeric($r3_approved_budget)) ? '---' : number_format((float)$r3_approved_budget, 2), 0, 1, 'C');

    $pdf->SetXY(180, $Y);
    $pdf->Cell(20, 2, number_format($proposed_realignment, 2), 0, 1, 'C');

    $total_indirect_co += $approved_budget;
    $total_indirect_proposed_co += $proposed_realignment;
    $Y = $pdf->GetY();
    $Y += 3.5;
}
$total_co = $total_direct_co + $total_indirect_co;
$total_proposed_co = $total_direct_proposed_co + $total_indirect_proposed_co;
$Y+= 3.5;

//P IN HONORARIA
$pdf->SetXY(100, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(120, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(140, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(160, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetXY(180, $Y);
$pdf->Cell(18, 3.5, '' , 'T', 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(73, $Y);
$pdf->Cell(20, 3.5, 'Sub-total for CO' , 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(97, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(100, $Y);
$pdf->Cell(20, 3.5, number_format($total_co,2) , 0, 1, 'C');
$pdf->SetXY(177, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(180, $Y);
$pdf->Cell(20, 3.5, number_format($total_proposed_co,2) , 0, 1, 'C');


$Y+= 10;

$grand_total = $total_ps + $total_mooe + $total_co;
$grand_proposed_total = $total_proposed_ps + $total_proposed_mooe + $total_proposed_co;
//P IN HONORARIA
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(105, $Y);
$pdf->Cell(20, 3.5, 'GRAND TOTAL' , 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(126, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(32, 3.5, number_format($grand_total,2) , 'B', 1, 'C');
$pdf->SetXY(163, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(168, $Y);
$pdf->Cell(32, 3.5, number_format($grand_proposed_total,2) , 'B', 1, 'C');

$Y += 4;

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(130, $Y);
$pdf->Cell(32, 3.5, '' , 'T', 1, 'C');
$pdf->SetXY(168, $Y);
$pdf->Cell(32, 3.5, '' , 'T', 1, 'C');

$Y += 7;

$pdf->SetXY(10, $Y);
DrawDottedLine($pdf, 10, $Y, 200.5, $Y);
$pdf->SetFont('Arial', 'IB', 7);
$pdf->Cell(20, 3.5, '(To be filled up by DOST)' , 0, 1, 'L');

$Y += 10;

$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', 'I', 7);
$pdf->Cell(20, 3.5, '* Chargeable against the CY ____ DOST-GIA ____' , 0, 1, 'L');

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(90, $Y);

$pdf->SetXY(126, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(32, 3.5, '' , 'B', 1, 'C');
$pdf->SetXY(163, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(168, $Y);
$pdf->Cell(32, 3.5, '' , 'B', 1, 'L');

$Y += 20;

$pdf->SetXY(10, $Y);
$pdf->Cell(20, 3.5, 'Certified Correct:' , 0, 1, 'L');
$pdf->SetXY(126, $Y);
$pdf->Cell(5, 3.5, 'Approved by DOST-EXECOM:' , 0, 1, 'L');

$Y += 20;

$pdf->SetXY(10, $Y);
$pdf->Cell(20, 3.5, '(Position)' , 0, 1, 'L');
$pdf->SetXY(126, $Y);
$pdf->Cell(20, 3.5, '(Position)' , 0, 1, 'L');

$Y += 20;

$pdf->SetXY(10, $Y);
$pdf->Cell(20, 3.5, 'DOST-EXECOM Approval: _______________________' , 0, 1, 'L');

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(8, 10);

// Add image
$x = 10; // X position
$y = 12;   // Y position
$width = 16; // Width of the image
$height = 16; // Height of the image (you can adjust this based on your needs)

$X = 0;
$Y = 8;
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($X,$Y,'DOST Form 4',0,1,'C');

$pdf->SetFont('Arial', 'B', 7.5);
$Y += 7;
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3.5, 'PROJECT LINE-ITEM BUDGET' , 0, 1, 'C');

$Y += 7;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Write(3.5, "I. General Instruction:");

$pdf->SetFont('Arial', '', 7);
$pdf->Write(3.5, " Submit through the DOST Project Management Information System (DPMIS), http://dpmis.dost.gov.ph, the project line-item budget (LIB) for the component project.  Also, submit four (4) copies of the LIB. Use Arial font, 11 font size.");

$Y += 10;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Write(3.5, "II. Specific Instructions:");

$pdf->SetFont('Arial', '', 7);
$pdf->Write(3.5, " 1. Itemize MOOE expense items above P100,000.00.  Expense items under the GAM may be allowed.");
$Y += 3.5;
$pdf->SetXY(43, $Y);
$pdf->Write(3.5, " 2. For Equipment, attach quotations and justification.");

$Y += 7;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Write(3.5, "III. Definitions of Major Expense Items:");

$Y += 3.5;

$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "1. Personnel Services (PS) - includes salaries and wages, honoraria, fees, and other compensation to consultants and specialists",0, 'J');
  
$Y += 3.5;

$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "2. Maintenance and Other Operating Expenses (MOOE) - shall be in accordance with the Government Accounting Manual (GAM) and shall be broken down/itemized as follows:",0, 'J');
  

$Y += 7;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "i. Traveling Expenses- costs of: (1) movement of persons locally and abroad, such as transportation, travel insurance for researchers exposed to hazard/risks, subsistence, lodging and travel allowances, fees for guides or patrol; (2) transportation of personal baggage or household effects; (3) bus, railroad, airline, and ship fares, trips, transfers, etc. of persons while traveling; (4) charter of boats, launches, automobiles, etc. non-commutable transportation allowances, road tolls; and (5) parking fees and similar reasonable expenses. For foreign travel, include the name(s), designation of program/project personnel who will travel, possible country of destination, purpose and duration of the travel.",0, 'J');
         
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "ii. Communication Expenses- include costs of telephone, telegraph, mobile/wireless and tolls, fax transmission, postage and delivery charges, data communication services, internet expenses, cable, satellite, radio and telegraph messenger services, among others;",0, 'J');
      
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "iii. Repair and Maintenance of Facilities- include costs of repair and maintenance of office equipment, furniture and fixtures, machinery and equipment, IT equipment and software, building, office and laboratory facilities, and other S&T structures directly needed by the project;",0, 'J');
    
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "iv. Repair and Maintenance of Vehicles- include costs of repair and maintenance of vehicles directly needed by the project except for cost of spare parts, gasoline and oil that shall fall under Supplies and Materials;",0, 'J');
      
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "v. Transportation and Delivery Services- include the costs of commercial transportation of mail, hauling of equipment or materials, including porterage, if any. Not included in this account are: costs of transportation of equipment, supplies and materials purchased for operation. Instead, these costs shall be included as part of the cost of the equipment/supplies and materials;",0, 'J');
      
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "vi. Supplies and Materials- include costs of items to be used in specialized S&T work (e.g. office supplies, accountable forms, zoological supplies, food supplies, drugs and medicine, laboratory supplies, gasoline, oil and lubricants, agricultural supplies, textbooks/instructional materials, and other supplies). It also includes all expendable commodities (delivery cost included as needed/required) acquired or ordered for use in connection with project implementation such as spare parts, fuel, and oil;",0, 'J');
      
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "vii. Utilities- include costs of water, electricity or cooking fuel consumed by  the implementing agency directly related to the project;",0, 'J');
      
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "viii. Training and Scholarship Expenses- include training fees and other expenses, and scholarship expenses such as tuition fees, stipends, book allowance, and other benefits;",0, 'J');
      
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "ix. Advertising Expenses- include costs of authorized advertising and publication of notices in newspapers and magazines of general circulation, television, radio, and other forms of media necessary for the implementation of the project;",0, 'J');
  
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "x. Printing and Publication Expenses- include costs of producing, printing, and binding materials such as books, reports, catalogues, documents, and other reading materials necessary for the implementation of the project;",0, 'J');

$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xi. Rent Expenses- rental fees for the use of facilities, equipment, and vehicles directly used in the implementation of the project;",0, 'J');
  
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xii. Representation Expenses- include costs of meal/food for the conduct of workshops/meetings, conferences, and other official functions related to the project; ",0, 'J');

$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xiii. Subscription Expenses- include costs of subscription to library materials, such as magazines, periodicals, other reading materials and software (including online software) necessary for the implementation of the project; ",0, 'J');
  
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xiv. Survey Expenses- include costs incurred in the conduct of survey related to the project;",0, 'J');
  
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xxv. Professional Services- as defined in GAM, but only those items that are relevant and appropriate to the proposed program/project;",0, 'J');
  
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xvi. Taxes, Insurance Premiums and other Fees- include costs of accident insurance of the project personnel for the performance of duties that involve hazardous activities during project duration, taxes, duties and licenses, fidelity bond premiums, and  insurance expenses of equipment acquired under the project; and",0, 'J');
  
$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(20, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "xvii. Other Maintenance and Operating Expenses- additional items not included above such as cost of submission of scientific paper for peer reviewed journals. ",0, 'J');
  

$Y = $pdf->GetY() + 1.5;

$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "3. Capital Outlay (CO) - includes all equipment necessary for the implementation of the project, which shall be enumerated in the proposed LIB.  This also includes infrastructure that are integral part of the R&D, which are crucial in the attainment of the project objectives.",0, 'J');
  

$Y = $pdf->GetY() + 3.5;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Write(3.5, "IV. Counterpart Funding");

$Y += 3.5;

$pdf->SetXY(15, $Y);
$pdf->SetFont('Arial', '', 7);
$pdf->multicell(182,3.5, "1. A counterpart fund, in kind and/or in cash, shall be required from the Implementing Agency.  Projects must have a minimum of 15% counterpart contribution (except for projects involving public good). \n 2. Indicate the detailed breakdown of the required fund assistance to indicate the counterpart of the Implementing Agency and other agencies cooperating in the project.",0, 'J');
  
$pdf->Output();
exit;
?>