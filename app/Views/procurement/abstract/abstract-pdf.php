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

//PR DATA
$query = $this->db->query("
SELECT
    `recid`,
    `prno`,
    `transaction_no`,
    `abstract_date`,
    `bidder_1`,
    `bidder_2`,
    `bidder_3`,
    `bidder_4`,
    `bidder_5`
FROM
    `tbl_abstract_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$abstract_date = $data['abstract_date'];
$prno = $data['prno'];
$bidder_1 = $data['bidder_1'];
$bidder_2 = $data['bidder_2'];
$bidder_3 = $data['bidder_3'];
$bidder_4 = $data['bidder_4'];
$bidder_5 = $data['bidder_5'];


class PDF extends \FPDF {
    function Footer() {
        // Position 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);

        // Right-aligned page number
        $this->Cell(330, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }
}


$pdf = new PDF('L', 'mm', 'LEGAL');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('Abstract - Print');

$pdf->SetXY(0, 8);

$Y = 4;

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 5, 'FAD-PS-002' , 'TRL', 1, 'L');

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 5, 'Department of Science and Technology' .$recid , 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(30, 5, 'FOOD AND NUTRITION RESEARCH INSTITUTE' , 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 5, 'Revision 2' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 5, '08 July 2025' , 'BRL', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(30, 5, 'ABSTRACT OF CANVASS' , 0, 1, 'C');

$Y = $pdf->GetY()+4;
$pdf->SetFont('Arial', '', 9);

$pdf->SetXY(20, $Y);
$pdf->Cell(15, 5, 'Date:' , 0, 1, 'L');

$pdf->SetXY(35, $Y);
$pdf->Cell(30, 5, $abstract_date , 'B', 1, 'L');

$pdf->SetXY(280, $Y);
$pdf->Cell(20, 5, 'PR #:' , 0, 1, 'R');

$pdf->SetXY(300, $Y);
$pdf->Cell(35, 5, $prno , 'B', 1, 'L');

$Y = $pdf->GetY()+5;

$startY = $pdf->GetY();

// Column widths
$w_qty   = 15;
$w_unit  = 30;
$w_desc  = 60;
$w_bid   = 35;   // each bidder column width
$w_empty = 35;

// X positions
$x_qty   = 20;
$x_unit  = 35;
$x_desc  = 65;
$x_bid1  = 125;
$x_bid2  = 160;
$x_bid3  = 195;
$x_bid4  = 230;
$x_bid5  = 265;
$x_empty = 300;

// --- 1) Print all bidder MultiCells and measure heights --- //

$heights = [];

// bidder 1
$pdf->SetXY($x_bid1, $startY);
$pdf->MultiCell($w_bid, 5, $bidder_1, 0, 'C');
$heights[] = $pdf->GetY() - $startY;

// bidder 2
$pdf->SetXY($x_bid2, $startY);
$pdf->MultiCell($w_bid, 5, $bidder_2, 0, 'C');
$heights[] = $pdf->GetY() - $startY;

// bidder 3
$pdf->SetXY($x_bid3, $startY);
$pdf->MultiCell($w_bid, 5, $bidder_3, 0, 'C');
$heights[] = $pdf->GetY() - $startY;

// bidder 4
$pdf->SetXY($x_bid4, $startY);
$pdf->MultiCell($w_bid, 5, $bidder_4, 0, 'C');
$heights[] = $pdf->GetY() - $startY;

// bidder 5
$pdf->SetXY($x_bid5, $startY);
$pdf->MultiCell($w_bid, 5, $bidder_5, 0, 'C');
$heights[] = $pdf->GetY() - $startY;

// Determine tallest bidder cell
$rowHeight = max($heights);

// --- 2) Draw borders for whole row using max height --- //

$pdf->Rect($x_qty,   $startY, $w_qty,   $rowHeight);
$pdf->Rect($x_unit,  $startY, $w_unit,  $rowHeight);
$pdf->Rect($x_desc,  $startY, $w_desc,  $rowHeight);
$pdf->Rect($x_bid1,  $startY, $w_bid,   $rowHeight);
$pdf->Rect($x_bid2,  $startY, $w_bid,   $rowHeight);
$pdf->Rect($x_bid3,  $startY, $w_bid,   $rowHeight);
$pdf->Rect($x_bid4,  $startY, $w_bid,   $rowHeight);
$pdf->Rect($x_bid5,  $startY, $w_bid,   $rowHeight);
$pdf->Rect($x_empty, $startY, $w_empty, $rowHeight);

// --- 3) Print normal single-line values centered vertically --- //

$midY = $startY + ($rowHeight / 2) - 2.5;

$pdf->SetXY($x_qty,  $midY);  $pdf->Cell($w_qty, 5,  'Quantity', 0, 0, 'C');
$pdf->SetXY($x_unit, $midY);  $pdf->Cell($w_unit, 5,  'Unit',     0, 0, 'C');
$pdf->SetXY($x_desc, $midY);  $pdf->Cell($w_desc, 5,  'Article/Description' ?? '', 0, 0, 'C'); // Optional short text preview

// --- (Optional) If you want the bidders' text centered vertically, skip because MultiCell prints directly at top --- //

// Move cursor to the end of the row
$pdf->SetY($startY + $rowHeight);


$query = $this->db->query("
SELECT
    `recid`,
    `pr_id`,
    `prno`,
    `quantity`,
    `unit`,
    `item_desc`,
    `bidder_dt1`,
    `bidder_dt2`,
    `bidder_dt3`,
    `bidder_dt4`,
    `bidder_dt5`
FROM
    `tbl_abstract_dt`
WHERE 
    `pr_id` = '$recid'
");

$rw = $query->getResultArray();
foreach ($rw as $data) {
    $quantity = $data['quantity'];
    $unit = $data['unit'];
    $item_desc = $data['item_desc'];
    $bidder_dt1 = $data['bidder_dt1'];
    $bidder_dt2 = $data['bidder_dt2'];
    $bidder_dt3 = $data['bidder_dt3'];
    $bidder_dt4 = $data['bidder_dt4'];
    $bidder_dt5 = $data['bidder_dt5'];
    
    $bidder_dt1 = ($bidder_dt1 == 0) ? '' : $bidder_dt1;
    $bidder_dt2 = ($bidder_dt2 == 0) ? '' : $bidder_dt2;
    $bidder_dt3 = ($bidder_dt3 == 0) ? '' : $bidder_dt3;
    $bidder_dt4 = ($bidder_dt4 == 0) ? '' : $bidder_dt4;
    $bidder_dt5 = ($bidder_dt5 == 0) ? '' : $bidder_dt5;

    if ($pdf->GetY() > 135) { // adjust threshold as needed
        $pdf->AddPage();

        // (optional) reprint your table header on new page here:
        $pdf->SetFont('Arial', '', 9);

        $Y =10;
        $pdf->SetXY(20, $Y);
        $pdf->Cell(15, 5, 'Quantity' , 1, 1, 'C');

        $pdf->SetXY(35, $Y);
        $pdf->Cell(30, 5, 'Unit' , 1, 1, 'C');

        $pdf->SetXY(65, $Y);
        $pdf->Cell(60, 5, 'Article/Description' , 1, 1, 'C');

        $pdf->SetXY(125, $Y);
        $pdf->Cell(35, 5, '' , 1, 1, 'C');

        $pdf->SetXY(160, $Y);
        $pdf->Cell(35, 5, '' , 1, 1, 'C');

        $pdf->SetXY(195, $Y);
        $pdf->Cell(35, 5, '' , 1, 1, 'C');

        $pdf->SetXY(230, $Y);
        $pdf->Cell(35, 5, '' , 1, 1, 'C');

        $pdf->SetXY(265, $Y);
        $pdf->Cell(35, 5, '' , 1, 1, 'C');

        $pdf->SetXY(300, $Y);
        $pdf->Cell(35, 5, '' , 1, 1, 'C');

       
    }

    $startY = $pdf->GetY();

    // ITEM & DESCRIPTION MultiCell
    $pdf->SetXY(65, $startY);
    $pdf->MultiCell(60, 5, $item_desc, 0, 'L');

    // Calculate height of MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Draw borders for all cells (using same height as multicell)
    $pdf->SetXY(20, $startY);  $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');  // ITEM NO.
    $pdf->SetXY(35, $startY);  $pdf->Cell(30, $rowHeight, '', 'L', 0, 'C');  // ABC
    $pdf->SetXY(65, $startY);  $pdf->Cell(60, $rowHeight, '', 'L', 0, 'C');  // Item Desc border only (already printed MultiCell)
    $pdf->SetXY(125, $startY); $pdf->Cell(35, $rowHeight, '', 'L', 0, 'C');  // Specifications
    $pdf->SetXY(160, $startY); $pdf->Cell(35, $rowHeight, '', 'L', 0, 'C');  // QTY
    $pdf->SetXY(195, $startY); $pdf->Cell(35, $rowHeight, '', 'L', 0, 'C');  // UNIT1
    $pdf->SetXY(230, $startY); $pdf->Cell(35, $rowHeight, '', 'L', 0, 'C');  // UNIT2
    $pdf->SetXY(265, $startY); $pdf->Cell(35, $rowHeight, '', 'L', 0, 'C');  // TOTAL
    $pdf->SetXY(300, $startY); $pdf->Cell(35, $rowHeight, '', 'LR', 0, 'C');  // TOTAL

    // Vertically center text for single line cells
    $middleY = $startY + ($rowHeight / 2) - 2.5;

    $pdf->SetXY(20, $middleY);  $pdf->Cell(15, 5, $quantity, 0, 0, 'C');
    $pdf->SetXY(35, $middleY);  $pdf->Cell(30, 5, $unit, 0, 0, 'C');
    // No need to print itemDesc here because MultiCell already did it
    $pdf->SetXY(125, $middleY);  $pdf->Cell(35, 5, $bidder_dt1, 0, 0, 'C');
    $pdf->SetXY(160, $middleY); $pdf->Cell(35, 5, $bidder_dt2, 0, 0, 'C');
    $pdf->SetXY(195, $middleY); $pdf->Cell(35, 5, $bidder_dt3, 0, 0, 'C');
    $pdf->SetXY(230, $middleY); $pdf->Cell(35, 5, $bidder_dt4, 0, 0, 'C');
    $pdf->SetXY(265, $middleY); $pdf->Cell(35, 5, $bidder_dt5, 0, 0, 'C');
    $pdf->SetXY(300, $middleY); $pdf->Cell(35, 5, '', 0, 0, 'C');

    // Move to the next line
    $pdf->SetY($endY);
}

$currentY = $pdf->GetY();
if ($pdf->PageNo() == 1) {

    // Keep adding empty rows until Y reaches 200 (first page only)
    while ($currentY < 135) {
        $pdf->SetXY(20, $currentY);
        $pdf->Cell(15, 5, '', 'L', 0, 'C');  // ITEM NO.
        $pdf->SetXY(35, $currentY);
        $pdf->Cell(30, 5, '', 'L', 0, 'C');  // ABC
        $pdf->SetXY(65, $currentY);
        $pdf->Cell(60, 5, '', 'L', 0, 'L');  // ITEM & DESCRIPTION (empty)
        $pdf->SetXY(125, $currentY);
        $pdf->Cell(35, 5, '', 'L', 0, 'L');  // SPECIFICATIONS
        $pdf->SetXY(160, $currentY);
        $pdf->Cell(35, 5, '', 'L', 0, 'C');  // QTY
        $pdf->SetXY(195, $currentY);
        $pdf->Cell(35, 5, '', 'L', 0, 'C');  // UNIT 1
        $pdf->SetXY(230, $currentY);
        $pdf->Cell(35, 5, '', 'L', 0, 'C');  // UNIT 2
        $pdf->SetXY(265, $currentY);
        $pdf->Cell(35, 5, '', 'L', 0, 'C');  // TOTAL (the last cell with ln=1 for line break)
        $pdf->SetXY(300, $currentY);
        $pdf->Cell(35, 5, '', 'LR', 1, 'C');  // TOTAL (the last cell with ln=1 for line break)

        $currentY = $pdf->GetY();

    }
}
$pdf->SetXY(20, $currentY);
$pdf->Cell(315, 5, '', 'T', 0, 'C');  // ITEM NO.



$Y = $pdf->GetY()+10;

$pdf->SetXY(20, $Y);
$pdf->Cell(15, 5, 'Note:' , 0, 1, 'L');

$pdf->SetXY(35, $Y);
$pdf->Cell(70, 5, '* Exceeded the ABC:, please DO NOT SELECT' , 0, 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(35, $Y);
$pdf->Cell(70, 5, 'Please select the lowest calculated RESPONSIVE/COMPLAINT BID and attach justification if not the lowest bid.' , 0, 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(35, $Y);
$pdf->Cell(70, 5, 'Please see attached Request for Quotation/Canvass for specifications' , 0, 1, 'L');

$Y = $pdf->GetY()+10;

$pdf->SetXY(20, $Y);
$pdf->Cell(15, 5, 'OPENNED IN THE PRESENCE OF DOST-FNRI Bids and Awards Commitee:' , 0, 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(20, $Y);
$pdf->Cell(39.375, 5, 'ALEXIS M. ORTIZ' , 0, 1, 'C');

$pdf->SetXY(59.375, $Y);
$pdf->Cell(39.375, 5, 'LEAH C. DAJAY' , 0, 1, 'C');

$pdf->SetXY(98.75, $Y);
$pdf->Cell(39.375, 5, 'ENGR. EUGENIO M. RAMIREZ' , 0, 1, 'C');

$pdf->SetXY(138.125, $Y);
$pdf->Cell(39.375, 5, 'MARITES V. ALIBAYAN' , 0, 1, 'C');

$pdf->SetXY(177.5, $Y);
$pdf->Cell(39.375, 5, 'MYRNA F. MOE' , 0, 1, 'C');

$pdf->SetXY(216.875, $Y);
$pdf->Cell(39.375, 5, 'LUCILA C. HUELAR' , 0, 1, 'C');

$pdf->SetXY(256.25, $Y);
$pdf->Cell(39.375, 5, 'SHANNEN FAYE Q. AREVALO' , 0, 1, 'C');

$pdf->SetXY(295.625, $Y);
$pdf->Cell(39.375, 5, 'TEST' , 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetFont('Arial', '', 6.5);
$pdf->SetXY(20, $Y);
$pdf->Cell(39.375, 3, 'Chairman-Alternative Method' , 0, 1, 'C');

$pdf->SetXY(59.375, $Y);
$pdf->Cell(39.375, 3, 'Vice Chairman' , 0, 1, 'C');

$pdf->SetXY(98.75, $Y);
$pdf->Cell(39.375, 3, 'Regular Member' , 0, 1, 'C');

$pdf->SetXY(138.125, $Y);
$pdf->Cell(39.375, 3, 'Regular Member' , 0, 1, 'C');

$pdf->SetXY(177.5, $Y);
$pdf->Cell(39.375, 3, 'Regular Member' , 0, 1, 'C');

$pdf->SetXY(216.875, $Y);
$pdf->Cell(39.375, 3, 'Regular Member' , 0, 1, 'C');

$pdf->SetXY(256.25, $Y);
$pdf->Cell(39.375, 3, 'Regular Member' , 0, 1, 'C');

$pdf->SetXY(295.625, $Y);
$pdf->Cell(39.375, 3, 'End-User/Provisional' , 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetFont('Arial', '', 6.5);
$pdf->SetXY(20, $Y);
$pdf->Cell(39.375, 3, 'of Procurement' , 0, 1, 'C');

$pdf->SetXY(59.375, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(98.75, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(138.125, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(177.5, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(216.875, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(256.25, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(295.625, $Y);
$pdf->Cell(39.375, 3, 'Member' , 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(20, $Y);
$pdf->Cell(39.375, 3, '(SEALED Bids)' , 0, 1, 'C');

$pdf->SetXY(59.375, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(98.75, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');
 
$pdf->SetXY(138.125, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(177.5, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(216.875, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(256.25, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');

$pdf->SetXY(295.625, $Y);
$pdf->Cell(39.375, 3, '' , 0, 1, 'C');






// $currentY = $pdf->GetY();


// // Only apply empty rows logic on the first page
// if ($pdf->PageNo() == 1) {

//     $currentY = $pdf->GetY();

//     // Keep adding empty rows until Y reaches 200 (first page only)
//     while ($currentY < 160) {
//         $pdf->SetXY(20, $currentY);
//         $pdf->Cell(15, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(35, $currentY);
//         $pdf->Cell(30, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(65, $currentY);
//         $pdf->Cell(60, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(125, $currentY);
//         $pdf->Cell(35, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(160, $currentY);
//         $pdf->Cell(35, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(195, $currentY);
//         $pdf->Cell(35, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(230, $currentY);
//         $pdf->Cell(35, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(265, $currentY);
//         $pdf->Cell(35, 5, '' , 'L', 1, 'C');

//         $pdf->SetXY(300, $currentY);
//         $pdf->Cell(35, 5, '' , 'LR', 1, 'C');

//         $currentY = $pdf->GetY();
//     }
// }

//     $pdf->SetXY(10, $currentY);
//     $pdf->Cell(270, 5, 'tetetete' , 'lrb', 1, 'C');


$pdf->Output();
exit;
?>