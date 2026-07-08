<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$rfq_recid = $this->request->getPostGet('rfq_recid');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));

//PR DATA
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

//RFQ DATA
$query = $this->db->query("
SELECT
    `quotation_no`,
    `company_name`,
    `company_address`,
    `delivery_period`,
    `terms`,
    `quotation_date`
FROM
    `tbl_pr_rfq`
WHERE 
    `recid` = '$rfq_recid'"
);

$data = $query->getRowArray();
$quotation_no = $data['quotation_no'];
$company_name = $data['company_name'];
$company_address = $data['company_address'];
$delivery_period = $data['delivery_period'];
$terms = $data['terms'];
$quotation_date = $data['quotation_date'];

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
$pdf->SetTitle('Request for Quotation - Print');
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 4;

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'FAD-PS-001' , 'TRL', 1, 'L');

$pdf->SetXY(90, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 4, 'Department of Science and Technology' , 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetXY(90, $Y);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 4, 'FOOD AND NUTRITION RESEARCH INSTITUTE' , 0, 1, 'C');

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'Revision 2' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, '08 July 2025' , 'BRL', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(90, $Y);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 4, 'REQUEST FOR QUOTATION' , 0, 1, 'C');

$Y = $pdf->GetY()+2;

$pdf->SetXY(90, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 4, 'Bids and Award Commitee for' , 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetXY(90, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 4, 'Shopping and Small Value Procurement' , 0, 1, 'C');

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(12, $Y);
$pdf->Cell(25, 5, 'Company Name:' , 0, 1, 'R');

$pdf->SetXY(37, $Y);
$pdf->Cell(90, 5, $company_name , 'B', 1, 'L');

$pdf->SetXY(129, $Y);
$pdf->Cell(30, 5, 'Date:' , 0, 1, 'R');

$pdf->SetXY(170, $Y);
$pdf->Cell(30, 5, $quotation_date , 'B', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(8.5, $Y);
$pdf->Cell(20, 5, 'Address:' , 0, 1, 'L');

$pdf->SetXY(29, $Y);
$pdf->Cell(98, 5, $company_address, 'B', 1, 'L');

$pdf->SetXY(140, $Y);
$pdf->Cell(30, 5, 'Quotation #:' , 0, 1, 'R');

$pdf->SetXY(170, $Y);
$pdf->Cell(30, 5, $quotation_no , 'B', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(9, $Y);
$pdf->Cell(20, 5, '' , 0, 1, 'L');

$pdf->SetXY(29, $Y);
$pdf->Cell(98, 5, '' , 'B', 1, 'L');

$Y = $pdf->GetY() + 4;
$pdf->SetXY(28, $Y);

$text = "Please quote your lowest price on the item/s listed below and submit your quotation duly signed by the bidder or by";

$pdf->SetFont('Arial', '', 9.5);
// Use the full page width minus margins
$page_width = $pdf->GetPageWidth();
$available_width = $page_width - 20; // 20px left + 20px right margins
$pdf->MultiCell($available_width, 5, $text, 0, 'J');

$Y = $pdf->GetY();
$pdf->SetXY(8.5, $Y);

$text = "the bidder's representative not later than _____________/__________ in a sealed envelope. Failure to strictly comply with the deadline and general conditions below shall automatically disqualify the bidder/s from the bidding.";

$pdf->SetFont('Arial', '', 9.5);
// Use the full page width minus margins
$page_width = $pdf->GetPageWidth();
$available_width = $page_width - 18; // 20px left + 20px right margins
$pdf->MultiCell($available_width, 5, $text, 0, 'J');

$pdf->Ln(6);

$Y = $pdf->GetY();
$pdf->SetXY(135, $Y);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 4, 'MARLON O. BALITAON' , 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(135, $Y);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 4, 'ADMINISTRATIVE OFFICER V' , 'T', 1, 'C');

$pdf->SetFont('Arial', '', 8);
//1ST LAYER--------------------------------------------------------------
$Y = $pdf->GetY()+5;
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '' , 'LT', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, '' , 'LT', 1, 'C');

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.5, "" , 'LT', 1, 'C');

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, "BIDDER'S" , 'LT', 1, 'C');

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, '' , 'LT', 1, 'C');

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, '' , 'LT', 1, 'C');

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, '' , 'LT', 1, 'C');

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, '' , 'RLT', 1, 'C');
//2ND LAYER -----------------------------------------------------------------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, 'ITEM NO.' , 'L', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, 'ABC' , 'L', 1, 'C');

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.5, "ITEM & DESCRIPTION/END-USER'S" , 'L', 1, 'C');

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, 'SPECIFICATIONS' , 'L', 1, 'C');

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, 'QTY' , 'L', 1, 'C');

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, 'UNIT' , 'L', 1, 'C');

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, 'UNIT' , 'L', 1, 'C');

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, 'TOTAL' , 'LR', 1, 'C');

//3RD LAYER --------------------------------------------------------------------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '' , 'LB', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, 'PER ITEM' , 'LB', 1, 'C');

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.5, "SPECIFICATIONS" , 'LB', 1, 'C');

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, 'OFFER' , 'LB', 1, 'C');

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, '' , 'LB', 1, 'C');

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, '' , 'LB', 1, 'C');

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, 'PRICE' , 'LB', 1, 'C');

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, 'PRICE' , 'LBR', 1, 'C');




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
    $item_desc = $data['item_desc'];
    $quantity = $data['quantity'];
    $unit_cost = $data['unit_cost'];
    $unit = $data['unit'];


    if ($pdf->GetY() > 240) { // adjust threshold as needed
        $pdf->AddPage();

        // (optional) reprint your table header on new page here:
        $pdf->SetFont('Arial', '', 9);

        $Y =10;
        $pdf->SetXY(10, $Y);
        $pdf->Cell(15, 3.5, '' , 'LT', 1, 'C');

        $pdf->SetXY(25, $Y);
        $pdf->Cell(20, 3.5, '' , 'LT', 1, 'C');

        $pdf->SetXY(45, $Y);
        $pdf->Cell(65, 3.5, "" , 'LT', 1, 'C');

        $pdf->SetXY(110, $Y);
        $pdf->Cell(35, 3.5, "BIDDER'S" , 'LT', 1, 'C');

        $pdf->SetXY(145, $Y);
        $pdf->Cell(10, 3.5, '' , 'LT', 1, 'C');

        $pdf->SetXY(155, $Y);
        $pdf->Cell(15, 3.5, '' , 'LT', 1, 'C');

        $pdf->SetXY(170, $Y);
        $pdf->Cell(15, 3.5, '' , 'LT', 1, 'C');

        $pdf->SetXY(185, $Y);
        $pdf->Cell(15, 3.5, '' , 'RLT', 1, 'C');
        //2ND LAYER -----------------------------------------------------------------
        $Y = $pdf->GetY();
        $pdf->SetXY(10, $Y);
        $pdf->Cell(15, 3.5, 'ITEM NO.' , 'L', 1, 'C');

        $pdf->SetXY(25, $Y);
        $pdf->Cell(20, 3.5, 'ABC' , 'L', 1, 'C');

        $pdf->SetXY(45, $Y);
        $pdf->Cell(65, 3.5, "ITEM & DESCRIPTION/END-USER'S" , 'L', 1, 'C');

        $pdf->SetXY(110, $Y);
        $pdf->Cell(35, 3.5, 'SPECIFICATIONS' , 'L', 1, 'C');

        $pdf->SetXY(145, $Y);
        $pdf->Cell(10, 3.5, 'QTY' , 'L', 1, 'C');

        $pdf->SetXY(155, $Y);
        $pdf->Cell(15, 3.5, 'UNIT' , 'L', 1, 'C');

        $pdf->SetXY(170, $Y);
        $pdf->Cell(15, 3.5, 'UNIT' , 'L', 1, 'C');

        $pdf->SetXY(185, $Y);
        $pdf->Cell(15, 3.5, 'TOTAL' , 'LR', 1, 'C');

        //3RD LAYER --------------------------------------------------------------------
        $Y = $pdf->GetY();
        $pdf->SetXY(10, $Y);
        $pdf->Cell(15, 3.5, '' , 'LB', 1, 'C');

        $pdf->SetXY(25, $Y);
        $pdf->Cell(20, 3.5, 'PER ITEM' , 'LB', 1, 'C');

        $pdf->SetXY(45, $Y);
        $pdf->Cell(65, 3.5, "SPECIFICATIONS" , 'LB', 1, 'C');

        $pdf->SetXY(110, $Y);
        $pdf->Cell(35, 3.5, 'OFFER' , 'LB', 1, 'C');

        $pdf->SetXY(145, $Y);
        $pdf->Cell(10, 3.5, '' , 'LB', 1, 'C');

        $pdf->SetXY(155, $Y);
        $pdf->Cell(15, 3.5, '' , 'LB', 1, 'C');

        $pdf->SetXY(170, $Y);
        $pdf->Cell(15, 3.5, 'PRICE' , 'LB', 1, 'C');

        $pdf->SetXY(185, $Y);
        $pdf->Cell(15, 3.5, 'PRICE' , 'LBR', 1, 'C');

       
    }

    $startY = $pdf->GetY();

    // ITEM & DESCRIPTION MultiCell
    $pdf->SetXY(45, $startY);
    $pdf->MultiCell(65, 5, $item_desc, 0, 'L');

    // Calculate height of MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Draw borders for all cells (using same height as multicell)
    $pdf->SetXY(10, $startY);  $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');  // ITEM NO.
    $pdf->SetXY(25, $startY);  $pdf->Cell(20, $rowHeight, '', 'L', 0, 'C');  // ABC
    $pdf->SetXY(45, $startY);  $pdf->Cell(65, $rowHeight, '', 'L', 0, 'L');  // Item Desc border only (already printed MultiCell)
    $pdf->SetXY(110, $startY); $pdf->Cell(35, $rowHeight, '', 'L', 0, 'L');  // Specifications
    $pdf->SetXY(145, $startY); $pdf->Cell(10, $rowHeight, '', 'L', 0, 'C');  // QTY
    $pdf->SetXY(155, $startY); $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');  // UNIT1
    $pdf->SetXY(170, $startY); $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');  // UNIT2
    $pdf->SetXY(185, $startY); $pdf->Cell(15, $rowHeight, '', 'LR', 0, 'C');  // TOTAL

    // Vertically center text for single line cells
    $middleY = $startY + ($rowHeight / 2) - 2.5;

    $pdf->SetXY(10, $middleY);  $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->SetXY(25, $middleY);  $pdf->Cell(20, 5, $unit_cost, 0, 0, 'C');
    // No need to print itemDesc here because MultiCell already did it
    $pdf->SetXY(110, $middleY); $pdf->Cell(35, 5, '', 0, 0, 'L');
    $pdf->SetXY(145, $middleY); $pdf->Cell(10, 5, $quantity, 0, 0, 'C');
    $pdf->SetXY(155, $middleY); $pdf->Cell(15, 5, $unit, 0, 0, 'C');
    $pdf->SetXY(170, $middleY); $pdf->Cell(15, 5, '', 0, 0, 'C');
    $pdf->SetXY(185, $middleY); $pdf->Cell(15, 5, '', 0, 0, 'R');

    // Move to the next line
    $pdf->SetY($endY);
}

$currentY = $pdf->GetY();

// Only apply empty rows logic on the first page
if ($pdf->PageNo() == 1) {

    $currentY = $pdf->GetY();

    // Keep adding empty rows until Y reaches 200 (first page only)
    while ($currentY < 160) {
        $pdf->SetXY(10, $currentY);
        $pdf->Cell(15, 5, '', 'L', 0, 'C');  // ITEM NO.
        $pdf->SetXY(25, $currentY);
        $pdf->Cell(20, 5, '', 'L', 0, 'C');  // ABC
        $pdf->SetXY(45, $currentY);
        $pdf->Cell(65, 5, '', 'L', 0, 'L');  // ITEM & DESCRIPTION (empty)
        $pdf->SetXY(110, $currentY);
        $pdf->Cell(35, 5, '', 'L', 0, 'L');  // SPECIFICATIONS
        $pdf->SetXY(145, $currentY);
        $pdf->Cell(10, 5, '', 'L', 0, 'C');  // QTY
        $pdf->SetXY(155, $currentY);
        $pdf->Cell(15, 5, '', 'L', 0, 'C');  // UNIT 1
        $pdf->SetXY(170, $currentY);
        $pdf->Cell(15, 5, '', 'L', 0, 'C');  // UNIT 2
        $pdf->SetXY(185, $currentY);
        $pdf->Cell(15, 5, '', 'LR', 0, 'C');  // TOTAL (the last cell with ln=1 for line break)
        $pdf->SetXY(10, $currentY);
        $pdf->Cell(270, 5, '', 'B', 1, 'C');  // TOTAL (the last cell with ln=1 for line break)


        $currentY = $pdf->GetY();
    }
}





//----------------------------------------------------------------------------   END USER SECTION   -----------------------------------------------------------------------------
//1ST NULL DATA SPACING

$Y = $pdf->GetY();

// Empty or label cells (same widths and positions as headers)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'C');  // ITEM NO.

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, '', 'L', 0, 'C');  // ABC

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.5, '', 'L', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, '', 'L', 0, 'C');  // SPECIFICATIONS

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, '', 'L', 0, 'C');  // QTY

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'C');  // UNIT 1

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'R');  // Label "Total:"

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, '', 'LR', 1, 'R');  // Total amount

//ACTUAL END USER DATA ----------------------------------------------------------
$Y = $pdf->GetY();

// Empty or label cells (same widths and positions as headers)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'C');  // ITEM NO.

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, '', 'L', 0, 'C');  // ABC

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.5, 'End-User:', 'L', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(60, $Y);
$pdf->Cell(45, 3.5, $end_user, 'B', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, '', 'L', 0, 'C');  // SPECIFICATIONS

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, '', 'L', 0, 'C');  // QTY

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'C');  // UNIT 1

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'R');  // Label "Total:"

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, '', 'LR', 1, 'R');  // Total amount

//2ND DATA -----------------------------------------------------------------
$Y = $pdf->GetY();
// Empty or label cells (same widths and positions as headers)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'C');  // ITEM NO.

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, '', 'L', 0, 'C');  // ABC

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.5, 'Delivery Period:', 'L', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(70, $Y);
$pdf->Cell(35, 3.5, $delivery_period, 'B', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, '', 'L', 0, 'C');  // SPECIFICATIONS

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, '', 'L', 0, 'C');  // QTY

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'C');  // UNIT 1

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, '', 'L', 0, 'R');  // Label "Total:"

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, '', 'LR', 1, 'R');  // Total amount

//3RD DATA -----------------------------------------------------------------
$Y = $pdf->GetY();
// Empty or label cells (same widths and positions as headers)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3.5, '', 'LB', 0, 'C');  // ITEM NO.

$pdf->SetXY(25, $Y);
$pdf->Cell(20, 3.5, '', 'LB', 0, 'C');  // ABC

$pdf->SetXY(45, $Y);
$pdf->Cell(65, 3.3, 'Terms of Payment:', 'LB', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(75, $Y);
$pdf->Cell(30, 3.3, $terms, 'B', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(110, $Y);
$pdf->Cell(35, 3.5, '', 'LB', 0, 'C');  // SPECIFICATIONS

$pdf->SetXY(145, $Y);
$pdf->Cell(10, 3.5, '', 'LB', 0, 'C');  // QTY

$pdf->SetXY(155, $Y);
$pdf->Cell(15, 3.5, '', 'LB', 0, 'C');  // UNIT 1

$pdf->SetXY(170, $Y);
$pdf->Cell(15, 3.5, '', 'LB', 0, 'R');  // Label "Total:"

$pdf->SetXY(185, $Y);
$pdf->Cell(15, 3.5, '', 'LBR', 1, 'R');  // Total amount


//----------------------------------------------------------------------------   END BORDER  -------------------------------------------------------------------------------
$pdf->SetFont('Arial', 'B', 7);
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 5, 'BIDDERS SHALL SUBMIT THE FOLLOWING :', 0, 1, 'L');

$pdf->SetFont('Arial', '', 7);
$requirements = [
    "1. Eligibility Documents (Mayor's/Business Permit, PhilGEPS Cert., Income/Business Tax Return & Omnibus Sworn Statement)",
    "2. ISO Certificate, PS Mark Certificate, Import Clearance Certification, If applicable.",
    "3. Brochures of equipment being offered.",
    "4. List of Service Centers in key cities nationwide, if applicable .",
    "5. Equipment shall be subjected to performance tests.",
    "6. DOST-FNRI reserves the right to accept or reject any bid, and to annul the bidding process & reject all bids at any time prior to contract award, without thereby incurring, any liability to the affected bidder's.",
    "7. Bidders may bid for any/or all of the items in this request for quotation, provided bids are within the agency budget contract (ABC) per item.",
    "8. Bidders must only use this request for quotation form available at DOST-FNRI procurement office or downloadable at the PhilGEPS and DOST-FNRI websites. For each item, the 'bidder's specifications offer,' 'quantity,' 'unit,' 'unit price,' and 'total price' columns shall be completely filled out. Non-compliance and/or incomplaint fill-out or non filling out shall be grounds for disqualification. Entries such as 'do' and 'same' shall mean that the bidder is offering the same specifications as required, and shall be considered as having complied with the requirements. In case of discrepancy between 'unit price' and 'total price', the 'unit price' shall prevail and 'total price' shall be corrected.",
    "9. The DOST-FNRI may select or award the bidder with the Lowest Calculated Responsive Bid (LCRIS) on a per item basis."
];

foreach ($requirements as $requirement) {
    $pdf->SetX(15); // Indent for bullet points
    $pdf->MultiCell(0, 3, $requirement, 0, 'J');
    $pdf->Ln(1);
}

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, '', 'T', 1, 'L');

// "After having carefully read..." section
$pdf->SetFont('Arial', '', 7);
$pdf->SetX(10);
$pdf->MultiCell(0, 3, 'After having carefully read and accepted your General Conditions , I/We hereby submit best price/quotation our company can offer for the above items', 0, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, '', 'B', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, 'Company Name/Contact Person/', 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, 'Tel.Number', 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, '', 'B', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, 'Printed Name/Signature', 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, '', 'B', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 3, 'Received by the Bidder:', 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, 'Address', 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 3, '', 'B', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, '', 'B', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 3, 'Date', 0, 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 3, 'Date', 0, 1, 'C');

$pdf->Output();
exit;
?>