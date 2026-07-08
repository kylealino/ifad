<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';

$query = $this->db->query("
SELECT
    `project_title`,
    `project_leader`,
    `program_title`,
    `total_duration`,
    `duration_from`,
    `duration_to`,
    `program_leader`,
    `monitoring_agency`,
    `collaborating_agencies`,
    `implementing_agency`
FROM
    `tbl_budget_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$project_title = $data['project_title'];
$project_leader = $data['project_leader'];
$program_title = $data['program_title'];
$total_duration = $data['total_duration'];
$duration_from = $data['duration_from'];
$duration_to = $data['duration_to'];
$program_leader = $data['program_leader'];
$monitoring_agency = $data['monitoring_agency'];
$collaborating_agencies = $data['collaborating_agencies'];
$implementing_agency = $data['implementing_agency'];

$pdf = new \FPDF();
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
$pdf->Cell(60, 3.5, $total_duration, 0, 1, 'L');

$pdf->Cell(40, 3.5, 'Current Duration', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $duration_from . ' - ' . $duration_to, 0, 1, 'L');

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

$pdf->Cell(40, 3.5, 'Monitoring Agency', 0, 0, 'L');
$pdf->Cell(5, 3.5, ':', 0, 0, 'L');
$pdf->Cell(60, 3.5, $monitoring_agency, 0, 1, 'L');

$X = $pdf->GetX(); // Save current X
$Y = $pdf->GetY(); // Save current Y

$pdf->SetXY(130, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(70, 3.5, 'Counterpart Funding', 'B', 0, 'C');

$Y+= 3.5;

$pdf->SetXY(130, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(35, 3.5, $implementing_agency, 0, 0, 'C');

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(35, 3.5, 'Cooperating Agency', 0, 0, 'C');

$Y+= 3;
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(126, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(32, 3.5, '' , 'B', 1, 'L');

$pdf->SetXY(163, $Y);
$pdf->Cell(5, 3.5, 'P' , 0, 1, 'L');
$pdf->SetXY(168, $Y);
$pdf->Cell(32, 3.5, '' , 'B', 1, 'L');

$Y-= 3.5;

$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(5, 3.5, 'I.', 0, 0, 'L');
$pdf->Cell(60, 3.5, 'Personal Services' , 0, 1, 'L');

$Y+= 3;

$query = $this->db->query("
SELECT
    `particulars`,
    `approved_budget`
FROM
    `tbl_budget_ps_dt`
WHERE 
    `project_id` = '$recid'"
);

$data = $query->getResultArray();
foreach ($data as $row) {
    $particulars = $row['particulars'];
    $approved_budget = $row['approved_budget'];

    $pdf->SetFont('Arial', '', 7);
    $pdf->SetXY(10, $Y);
    $pdf->Cell(5, 3.5, '', 0, 0, 'L');
    $pdf->Cell(15, 3.5, 'Direct Cost' , 'B', 1, 'L');

    $pdf->SetFont('Arial', 'I', 7);
    $pdf->Cell(5, 3.5, '', 0, 0, 'L');
    $pdf->Cell(15, 3.5, $particulars , 0, 1, 'L');
}


$pdf->Output();
exit;
?>