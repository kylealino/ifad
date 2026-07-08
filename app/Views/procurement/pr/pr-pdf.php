<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));

$query = $this->db->query("
SELECT
    `entity_name`,
    `office`,
    `prno`,
    `responsibility_code`,
    `fund_cluster`,
    `pr_date`,
    `end_user`,
    `charge_to`,
    `purpose`,
    `estimated_cost`
FROM
    `tbl_pr_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$entity_name = $data['entity_name'];
$office = $data['office'];
$prno = $data['prno'];
$responsibility_code = $data['responsibility_code'];
$fund_cluster = $data['fund_cluster'];
$pr_date = $data['pr_date'];
$end_user = $data['end_user'];
$charge_to = $data['charge_to'];
$purpose = $data['purpose'];
$estimated_cost = $data['estimated_cost'];

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
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('Purchase Request - Print');
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;

$pdf->SetXY(170, $Y);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(30, 4, 'Appendix 60' , 0, 1, 'R');

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'FAD-PS-005' , 'TRL', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(90, $Y);
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(30, 4, 'PURCHASE REQUEST' , 0, 1, 'C');

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'Revision 0' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, '08 July 2025' , 'BRL', 1, 'L');

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(10, $Y);
$pdf->Cell(25, 10, 'Entity Name:' , 0, 1, 'R');

$pdf->SetXY(35, $Y);
$pdf->Cell(90, 5, 'DEPARTMENT OF SCIENCE AND TECHNOLOGY' , 'B', 1, 'L');

$pdf->SetXY(140, $Y);
$pdf->Cell(30, 5, 'Estimated Cost:' , 0, 1, 'R');

$pdf->SetXY(170, $Y);
$pdf->Cell(30, 5, number_format($estimated_cost,2) , 'B', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(35, $Y);
$pdf->Cell(90, 5, $entity_name , 'B', 1, 'L');

$pdf->SetXY(140, $Y);
$pdf->Cell(30, 5, 'Fund Cluster:' , 0, 1, 'R');

$pdf->SetXY(170, $Y);
$pdf->Cell(30, 5, $fund_cluster , 'B', 1, 'L');

$Y = $pdf->GetY()+5;

$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, 'Office/Section:' , 'TL', 1, 'L');

$pdf->SetXY(38, $Y);
$pdf->Cell(20, 5, $office , 'TBR', 1, 'L');

$pdf->SetXY(58, $Y);
$pdf->Cell(15, 5, 'PR NO.:' , 'T', 1, 'C');

$pdf->SetXY(73, $Y);
$pdf->Cell(79, 5, $prno , 'TBR', 1, 'L');

$pdf->SetXY(152, $Y);
$pdf->Cell(20, 5, 'Date:' , 'T', 1, 'L');

$pdf->SetXY(172, $Y);
$pdf->Cell(28, 5, $pr_date , 'TBR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(48, 15, '' , 'LR', 1, 'L');

$pdf->SetXY(58, $Y);
$pdf->Cell(47, 5, 'Responsibility Center' , 0, 1, 'c');

$Y = $pdf->GetY();
$pdf->SetXY(58, $Y);
$pdf->Cell(47, 5, 'Code:' , 0, 1, 'c');

$Y = $pdf->GetY();
$pdf->SetXY(58, $Y);
$pdf->Cell(94, 5, '' , 0, 1, 'c');

$Y = $pdf->GetY()-15;
$pdf->SetFont('Arial', '',9);
$pdf->SetXY(105, $Y);
$pdf->Cell(47, 10, $responsibility_code , 'B', 1, 'L');
$pdf->SetFont('Arial', '', 10);

$pdf->SetXY(152, $Y);
$pdf->Cell(48, 15, 'test' , 'LR', 1, 'c');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, 'Stock/' , 'LT', 1, 'C');
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, 'Property No.' , 'LB', 1, 'C');

$Y = $pdf->GetY()-10;
$pdf->SetXY(38, $Y);
$pdf->Cell(20, 10, 'Unit' , 1, 1, 'C');

$pdf->SetXY(58, $Y);
$pdf->Cell(74, 10, 'Item Description' , 1, 1, 'C');

$pdf->SetXY(132, $Y);
$pdf->Cell(20, 10, 'Quantity' , 1, 1, 'C');

$pdf->SetXY(152, $Y);
$pdf->Cell(20, 10, 'Unit Cost' , 1, 1, 'C');

$pdf->SetXY(172, $Y);
$pdf->Cell(28, 10, 'Total Cost' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetLineWidth(0.2); // normal thickness
$pdf->SetFont('Arial', '', 9);
$query = $this->db->query("
SELECT
    `item_desc`,
    `unit`,
    `quantity`,
    `unit_cost`,
    `total_cost`,
    `added_by`,
    `added_at`
FROM
    `tbl_pr_dt`
WHERE 
    `pr_id` = '$recid'
");

$rw = $query->getResultArray();
foreach ($rw as $data) {
    $item_desc  = $data['item_desc'];
    $unit       = $data['unit'];
    $quantity   = $data['quantity'];
    $unit_cost  = $data['unit_cost'];
    $total_cost = $data['total_cost'];

    if ($pdf->GetY() > 230) { // adjust threshold as needed
        $pdf->AddPage();

        // (optional) reprint your table header on new page here:
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY(10, 10); // adjust header Y
        $pdf->Cell(28, 7, 'Stock/Property No.', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Unit', 1, 0, 'C');
        $pdf->Cell(74, 7, 'Item Description', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Quantity', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Unit Cost', 1, 0, 'C');
        $pdf->Cell(28, 7, 'Total Cost', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 9);

       
    }

    $startY = $pdf->GetY();

    // Item Description (MultiCell)
    $pdf->SetXY(58, $startY);
    $pdf->MultiCell(74, 5, $item_desc, 0, 'L');

    // Compute total height of the multicell row
    $endY = $pdf->GetY();
    $totalRowHeight = $endY - $startY;

    // Draw borders (without the bold look)
    

    $pdf->SetXY(10, $startY);  $pdf->Cell(28, $totalRowHeight, '', 1, 0, 'C'); // Office/Section
    $pdf->SetXY(38, $startY);  $pdf->Cell(20, $totalRowHeight, '', 1, 0, 'C'); // Unit
    $pdf->SetXY(58, $startY);  $pdf->Cell(74, $totalRowHeight, '', 1, 0, 'L'); // Item Desc (border only)
    $pdf->SetXY(132, $startY); $pdf->Cell(20, $totalRowHeight, '', 1, 0, 'C'); // Quantity
    $pdf->SetXY(152, $startY); $pdf->Cell(20, $totalRowHeight, '', 1, 0, 'R'); // Unit Cost
    $pdf->SetXY(172, $startY); $pdf->Cell(28, $totalRowHeight, '', 1, 0, 'R'); // Total Cost

    // Vertically center single-line cells
    $middleY = $startY + ($totalRowHeight / 2) - 2.5;

    $pdf->SetXY(10, $middleY);  $pdf->Cell(28, 5, '', 0, 0, 'C'); // optional section label
    $pdf->SetXY(38, $middleY);  $pdf->Cell(20, 5, $unit, 0, 0, 'C');
    $pdf->SetXY(132, $middleY); $pdf->Cell(20, 5, $quantity, 0, 0, 'C');
    $pdf->SetXY(152, $middleY); $pdf->Cell(20, 5, number_format($unit_cost, 2), 0, 0, 'R');
    $pdf->SetXY(172, $middleY); $pdf->Cell(28, 5, number_format($total_cost, 2), 0, 0, 'R');

    // Move to next row
    $pdf->SetY($endY);
}

$currentY = $pdf->GetY();

// Only apply empty rows logic on the first page
if ($pdf->PageNo() == 1) {

    $currentY = $pdf->GetY();

    // Keep adding empty rows until Y reaches 200 (first page only)
    while ($currentY < 200) {
        $pdf->SetXY(10, $currentY);
        $pdf->Cell(28, 5, '', 1, 0, 'C'); // Office/Section
        $pdf->Cell(20, 5, '', 1, 0, 'C'); // Unit
        $pdf->Cell(74, 5, '', 1, 0, 'L'); // Item Desc
        $pdf->Cell(20, 5, '', 1, 0, 'C'); // Quantity
        $pdf->Cell(20, 5, '', 1, 0, 'R'); // Unit Cost
        $pdf->Cell(28, 5, '', 1, 1, 'R'); // Total Cost

        $currentY = $pdf->GetY();
    }
}


//----------------------------------------------------------------------------   TOTAL SECTION   -----------------------------------------------------------------------------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, '' , 1, 1, 'C');

$pdf->SetXY(38, $Y);
$pdf->Cell(20, 5, '' , 1, 1, 'C');

$pdf->SetXY(58, $Y);
$pdf->Cell(74, 5, '' , 1, 1, 'C');

$pdf->SetXY(132, $Y);
$pdf->Cell(20, 5, '' , 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(152, $Y);
$pdf->Cell(20, 5, 'Total:' , 1, 1, 'R');


$pdf->SetXY(172, $Y);
$pdf->Cell(28, 5, number_format($estimated_cost,2) , 1, 1, 'C');
$pdf->SetFont('Arial', '', 10);
//----------------------------------------------------------------------------   SIGNATORIES 1  -------------------------------------------------------------------------------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 10, 'End-User:' , 1, 1, 'L');

$pdf->SetXY(38, $Y);
$pdf->Cell(162, 5, '' , 'TR', 1, 'L');
$Y = $pdf->GetY();
$pdf->SetXY(38, $Y);
$pdf->Cell(162, 5, $end_user , 'BR', 1, 'L');

// ---------- CHARGE TO ----------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 10, 'Charge To:', 1, 0, 'L');

$x = 38;
$y = $Y;
$w = 162;
$lineHeight = 5;

// Measure height only (without printing text)
$pdf->SetXY($x, $y);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell($w, $lineHeight, '', 0, 'L');
$endY = $pdf->GetY();
$textHeight = $endY - $y;
$totalHeight = max($textHeight, 10);

// Draw the border
$pdf->Rect($x, $y, $w, $totalHeight);

// Print actual text once
$pdf->SetXY($x, $y);
$pdf->MultiCell($w, $lineHeight, $charge_to, 0, 'L');

// Move to next line
$pdf->SetY($y + $totalHeight);


// ---------- PURPOSE ----------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 10, 'Purpose:', 1, 0, 'L');

$x = 38;
$y = $Y;

// Measure height only (no text output)
$pdf->SetXY($x, $y);
$pdf->MultiCell($w, $lineHeight, '', 0, 'L');
$endY = $pdf->GetY();
$textHeight = $endY - $y;
$totalHeight = max($textHeight, 10);

// Draw the border
$pdf->Rect($x, $y, $w, $totalHeight);

// Print the text once
$pdf->SetXY($x, $y);
$pdf->MultiCell($w, $lineHeight, $purpose, 0, 'L');

$pdf->SetY($y + $totalHeight);


//----------------------------------------------------------------------------   SIGNATORIES 2  -------------------------------------------------------------------------------
if ($Y >250) {
    $pdf->AddPage();
}

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, '' , 'TL', 1, 'C');

$pdf->SetXY(38, $Y);
$pdf->Cell(20, 5, '' ,'TR' , 1, 'C');

$pdf->SetXY(58, $Y);
$pdf->Cell(74, 5, 'Requested by:' , 'TLR', 1, 'L');

$pdf->SetXY(132, $Y);
$pdf->Cell(68, 5, 'Approved by:' , 'TR', 1, 'l');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, 'Signature:' , 'L', 1, 'L');

$pdf->SetXY(38, $Y);
$pdf->Cell(20, 5, '' ,'R' , 1, 'C');

$pdf->SetXY(58, $Y);
$pdf->Cell(74, 5, '' , 'LR', 1, 'L');

$pdf->SetXY(132, $Y);
$pdf->Cell(68, 5, '' , 'R', 1, 'l');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, 'Printed Name:' , 'L', 1, 'L');

$pdf->SetXY(38, $Y);
$pdf->Cell(20, 5, '' ,'R' , 1, 'C');

$pdf->SetFont('Arial', 'B',10);
$pdf->SetXY(58, $Y);
$pdf->Cell(74, 5, 'MILFLOR S. GONZALES, Ph.D.' , 'LR', 1, 'C');

$pdf->SetXY(132, $Y);
$pdf->Cell(68, 5, 'ALEXIS M. ORTIZ' , 'R', 1, 'C');
$pdf->SetFont('Arial', '',10);
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(28, 5, 'Designation:' , 'BL', 1, 'L');

$pdf->SetXY(38, $Y);
$pdf->Cell(20, 5, '' ,'BR' , 1, 'C');

$pdf->SetXY(58, $Y);
$pdf->Cell(74, 5, 'Chief, TDSTSD' , 'BLR', 1, 'C');

$pdf->SetXY(132, $Y);
$pdf->Cell(68, 5, 'Chief Administrative Officer, FAD' , 'BR', 1, 'C');

$Y = $pdf->GetY()+5;

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