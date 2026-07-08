<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$realign_id = $this->request->getPostGet('realign_id');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';

class PDF extends \FPDF {
    function Footer() {
        $this->SetY(-15); // 15 mm from bottom
        $this->SetFont('Arial', 'I', 8);

        // Use page width instead of fixed number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }
}

$pdf = new PDF('P', 'mm', 'LEGAL');
$pdf->AliasNbPages(); // REQUIRED
$pdf->AddPage();      // REQUIRED
$pdf->SetTitle('PO - PRINT');
$pdf->SetFont('Arial', 'B', 16);



$pdf->SetXY(8, 10);

// Add image
$x = 5; // X position
$y = 5;   // Y position
$width = 180; // Width of the image
$height = 22; // Height of the image (you can adjust this based on your needs)

$pdf->Image(ROOTPATH . 'public/assets/images/logos/fnri-header.png', $x, $y, $width, $height);


$Y = 5;
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 4, 'Appendix 61' , 'TRL', 1, 'R');
$Y = $pdf->GetY();
$YY = $pdf->GetY()+4;
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 20, '' , 'RL', 1, 'R');


$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(191, $YY);
$pdf->Cell(18, 3, 'FAD-PS-006' , 'TRL', 1, 'L');
$YY = $pdf->GetY();
$pdf->SetXY(191, $YY);
$pdf->Cell(18, 3, 'Revision 0' , 'RL', 1, 'L');
$YY = $pdf->GetY();
$pdf->SetXY(191, $YY);
$pdf->Cell(18, 3, '08 July 2025' , 'BRL', 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 10, 'P U R C H A S E   O R D E R' , 'BRL', 1, 'C');

$pdf->SetFont('Arial', '', 9);
$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, 'Supplier:' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 4, '' , 'B', 1, 'L');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 4, 'PO.No.' , 'L', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'BR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, 'Address:' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 4, '' , 'B', 1, 'L');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 4, 'Date:' , 'L', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'BR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, 'TEL#' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 4, '' , 'B', 1, 'L');
$pdf->SetXY(125, $Y);
$pdf->Cell(30, 4, 'Mode of Procurement:' , 'L', 1, 'L');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 4, '' , 'BR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, 'TIN:' , 'LB', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 4, '' , 'B', 1, 'L');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 4, 'PR#' , 'LB', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'BR', 1, 'L');

$pdf->SetFont('Arial', '', 9);
$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 2, '' , 'LR', 1, 'L');
$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 4, 'Gentlemen:' ,'LR', 1, 'L');
$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(5, 4, '' , 'LB', 1, 'L');
$pdf->SetXY(10, $Y);
$pdf->Cell(200, 4, 'Please furnish this office the following articles subject to the terms and conditions contained herein:' , 'RB', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(40, 8, 'Place of Delivery:' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 8, '' , 'B', 1, 'L');

$pdf->SetXY(125, $Y);
$pdf->MultiCell(
    15,     // width
    4,      // line height
    "Delivery\nTerm:",
    'L',      // border
    'L'     // alignment
);

$pdf->SetXY(140, $Y);
$pdf->Cell(70, 8, '' , 'BR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(40, 8, 'Date of Delivery:' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 8, '' , 'B', 1, 'L');

$pdf->SetXY(125, $Y);
$pdf->Cell(50, 8, 'Payment Term:' , 'L', 1, 'L');

$pdf->SetXY(175, $Y);
$pdf->Cell(35, 8, '' ,'BR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(120, 2, '' , 'LRB', 1, 'L');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 2, '' , 'RB', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->MultiCell(
    25,     // width
    4,      // line height
    "Stock/\nProperty No.",
    1,      // border
    'C'     // alignment
);

$pdf->SetXY(30, $Y);
$pdf->Cell(15, 8, 'Unit' , 1, 1, 'C');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 8, 'Description' , 1, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 8, 'Quantity' , 1, 1, 'C');
$pdf->SetXY(140, $Y);
$pdf->Cell(35, 8, 'Unit Cost' , 1, 1, 'C');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 8, 'Amount' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 80, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(15, 80, '' , 'L', 1, 'C');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 80, '' , 'L', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 80, '' , 'L', 1, 'C');
$pdf->SetXY(140, $Y);
$pdf->Cell(35, 80, '' , 'L', 1, 'C');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 80, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 8, 'Charged to:' , 'L', 1, 'R');
$pdf->SetXY(30, $Y);
$pdf->Cell(145, 8, '' , 'LR', 1, 'C');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 8, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 8, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(15, 8, '' , 'L', 1, 'C');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 8, '' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 8, '' , 'L', 1, 'C');
$pdf->SetXY(140, $Y);
$pdf->Cell(35, 8, '' , 'L', 1, 'C');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 8, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 8, 'End-user:' , 'L', 1, 'R');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 8, '' , 'LR', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 8, '' , 'L', 1, 'C');
$pdf->SetXY(140, $Y);
$pdf->Cell(35, 8, '' , 'L', 1, 'C');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 8, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'R');
$pdf->SetXY(30, $Y);
$pdf->Cell(15, 4, '' , 'LR', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(15, 4, '' , 'L', 1, 'C');
$pdf->SetXY(140, $Y);
$pdf->Cell(35, 4, '' , 'L', 1, 'C');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 4, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(40, 4, 'Total Amount in Words' , 'LTB', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(165, 4, 'Total Amount in Words' , 'RTB', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 4, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(5, $Y);
$pdf->Cell(5, 4, '' , 'L', 1, 'C');
$pdf->SetXY(10, $Y);
$pdf->Cell(200, 4, 'In case of failure to make the full delivery within the specified time, a penalty of one-tenth(1/10) of the percent for every day of delay shall be' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 4, 'imposed.' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 8, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(120, 4, 'Conforme:' , 'L', 1, 'L');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 4, 'Very truly yours,' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 8, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 1, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 1, '' , 'B', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 1, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 6, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 6, '(Signature over printed name)' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 6, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 4, '' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 1, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 1, '' , 'B', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 1, 'ALEXIS M. ORTIZ' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 6, '' , 'L', 1, 'C');
$pdf->SetXY(30, $Y);
$pdf->Cell(95, 6, '(Date)' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(85, 6, 'Chief AO, FAD' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 8, '' , 'LBR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, 'Fund Cluster:' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(35, 4, '' , 'B', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(25, 4, 'ORS/BURS No.:' , 'L', 1, 'L');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 4, '' , 'BR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, 'Fund Available:' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(35, 4, '' , 'B', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(25, 4, 'Date of the ORS/BURS:' , 'L', 1, 'L');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 4, '' , 'BR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(35, 4, '' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(25, 4, 'Amount:' , 'L', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'BR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(35, 4, '' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 4, 'BONJOBIE F. CAJANO CPA, CTT' , 0, 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(25, 4, '' , 'L', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 4, '' , 'BL', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(15, 4, '' , 'B', 1, 'L');
$pdf->SetXY(45, $Y);
$pdf->Cell(80, 4, 'Accountant III' , 'B', 1, 'C');
$pdf->SetXY(125, $Y);
$pdf->Cell(25, 4, '' , 'BL', 1, 'L');
$pdf->SetXY(140, $Y);
$pdf->Cell(70, 4, '' , 'BR', 1, 'C');

$Y = $pdf->GetY()+40;

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(10, $Y);

// ----- LINE 1 -----
$text1_parts = [
    ['Reproduction of this ', ''],
    ['CONTROLLED DOCUMENT', 'B'],
    [' is ', ''],
    ['STRICTLY PROHIBITED', 'B'],
    [' without permission from the Document Custodian. This', '']
];

// Calculate total width of text
$totalWidth = 0;
foreach ($text1_parts as $part) {
    $pdf->SetFont('Arial', $part[1], 8);
    $totalWidth += $pdf->GetStringWidth($part[0]);
}

// Center the combined text in 190mm width
$startX = 10 + (190 - $totalWidth) / 2;
$pdf->SetX($startX);

// Print parts
foreach ($text1_parts as $part) {
    $pdf->SetFont('Arial', $part[1], 8);
    $pdf->Cell($pdf->GetStringWidth($part[0]), 4, $part[0], 0, 0);
}
$pdf->Ln(4);

// ----- LINE 2 -----
$text2_parts = [
    ['DOCUMENTED INFORMATION WHEN PRINTED', 'B'],
    [' is deemed ', ''],
    ['UNCONTROLLED.', 'B']
];

$totalWidth = 0;
foreach ($text2_parts as $part) {
    $pdf->SetFont('Arial', $part[1], 8);
    $totalWidth += $pdf->GetStringWidth($part[0]);
}

// Center the combined text in 190mm width
$startX = 10 + (190 - $totalWidth) / 2;
$pdf->SetX($startX);

// Print parts
foreach ($text2_parts as $part) {
    $pdf->SetFont('Arial', $part[1], 8);
    $pdf->Cell($pdf->GetStringWidth($part[0]), 4, $part[0], 0, 0);
}
$pdf->Ln(4);



$pdf->Output();
exit;
?>