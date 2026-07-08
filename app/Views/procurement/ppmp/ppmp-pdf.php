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
    `recid`,
    `ppmpno`,
    `end_user`,
    `fiscal_year`,
    `project_title`,
    `responsibility_code`,
    `is_indicative`,
    `is_final`,
    `prepared_by`,
    `submitted_by`
FROM
    `tbl_ppmp_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$ppmpno = $data['ppmpno'];
$end_user = $data['end_user'];
$fiscal_year = $data['fiscal_year'];
$project_title = $data['project_title'];
$responsibility_code = $data['responsibility_code'];
$is_indicative = $data['is_indicative'];
$is_final = $data['is_final'];
$prepared_by = $data['prepared_by'];
$submitted_by = $data['submitted_by'];

class PDF extends \FPDF {
    function Footer() {
        // Position from bottom
        $this->SetY(-15);

        // --- Centered disclaimer ---
        $this->SetFont('Arial', '', 6);
        $this->Cell(0, 5, 
            'Reproduction of this CONTROLLED DOCUMENT is STRICTLY PROHIBITED without permission from the Document Custodian. The DOCUMENTED INFORMATION WHEN PRINTED IS deemed UNCONTROLLED.', 
            0, 0, 'C'
        );

        // --- Page number (right side, same line) ---
        $this->SetXY(-40, -15); // move to right edge
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(30, 5, 
            'Page ' . $this->PageNo() . ' of {nb}', 
            0, 0, 'R'
        );
    }
}

$pdf = new PDF('L', 'mm', 'LEGAL');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('PPMP - Print');
$pdf->SetXY(0, 8);

$Y = 4;

$Y = $pdf->GetY()+4;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 4, 'FAD-PS-003' , 'TRL', 1, 'L');

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 4, 'Department of Science and Technology', 0, 1, 'C');

$Y = $pdf->GetY();

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 4, 'FOOD AND NUTRITION RESEARCH INSTITUTE' , 0, 1, 'C');

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 4, 'Revision 1' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(305, $Y);
$pdf->Cell(30, 4, '20 Oct 2025' , 'BRL', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 4, 'PROJECT PROCUREMENT MANAGEMENT PLAN (PPMP)' , 0, 1, 'C');

//CHECKBOX
$Y = $pdf->GetY()+4;
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFont('ZapfDingbats','',10);
$pdf->SetXY(145, $Y);
$pdf->Cell(6, 5, ($is_indicative == 1 ? chr(51) : ''), 1, 0, 'C');

// BACK TO NORMAL FONT for text
$pdf->SetFont('Arial','',10);
$pdf->SetXY(151, $Y);
$pdf->Cell(30, 5, 'INDICATIVE', 0, 0, 'L');


// FINAL checkbox
$pdf->SetFont('ZapfDingbats','',10);
$pdf->SetXY(195, $Y);
$pdf->Cell(6, 5, ($is_final == 1 ? chr(51) : ''), 1, 0, 'C');

// BACK TO NORMAL FONT again
$pdf->SetFont('Arial','',10);
$pdf->SetXY(201, $Y);
$pdf->Cell(30, 5, 'FINAL', 0, 1, 'L');

$Y = $pdf->GetY()+4;
$pdf->SetFont('Arial', '', 9);

$pdf->SetXY(20, $Y);
$pdf->Cell(20, 5, 'Fiscal Year:' , 0, 1, 'L');

$pdf->SetXY(40, $Y);
$pdf->Cell(116.8, 5, $fiscal_year , 'B', 1, 'L');

$pdf->SetXY(280, $Y);
$pdf->Cell(20, 5, 'PPMP No.:' , 0, 1, 'R');

$pdf->SetXY(300, $Y);
$pdf->Cell(35, 5, $ppmpno , 'B', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(20, $Y);
$pdf->Cell(47, 5, 'End-User or Implementing Unit:' , 0, 1, 'L');

$pdf->SetXY(67, $Y);
$pdf->Cell(90, 5, $end_user , 'B', 1, 'L');

$pdf->SetXY(280, $Y);
$pdf->Cell(20, 5, 'RC:' , 0, 1, 'R');
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(300, $Y);
$pdf->Cell(35, 5, $responsibility_code , 'B', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(20, $Y);
$pdf->Cell(48, 5, 'Project, Programs and Activities:' , 0, 1, 'L');

$pdf->SetXY(68, $Y);
$pdf->Cell(100, 5, $project_title , 'B', 1, 'L');

//HEADER
$Y = $pdf->GetY() + 5;
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(20, $Y);
$pdf->Cell(170, 3, 'PROCUREMENT PROJECT DETAILS', 1, 1, 'C');
$pdf->SetXY(190, $Y);
$pdf->Cell(70, 3, 'PROJECTED TIMELINE (MM/YYYY)', 1, 1, 'C');
$pdf->SetXY(260, $Y);
$pdf->Cell(35, 3, 'FUNDING DETAILS', 1, 1, 'C');
$pdf->SetXY(295, $Y);
$pdf->Cell(20, 3, '', 'TL', 1, 'C');
$pdf->SetXY(315, $Y);
$pdf->Cell(20, 3, '', 'TRL', 1, 'C');

//COLUMN 1
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 4);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 2, 'General Description and Objective' , 'LT', 1, 'C');
$pdf->SetXY(80, $Y);
$pdf->Cell(20, 2, 'Type of the' , 'LT', 1, 'C');
$pdf->SetXY(100, $Y);
$pdf->Cell(15, 2, '' , 'LT', 1, 'C');
$pdf->SetXY(115, $Y);
$pdf->Cell(15, 2, '' , 'LT', 1, 'C');
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 2, '' , 'LT', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(20, 2, '' , 'LT', 1, 'C');
$pdf->SetXY(170, $Y);
$pdf->Cell(20, 2, 'Pre-Procurement' , 'LT', 1, 'C');
$pdf->SetXY(190, $Y);
$pdf->Cell(20, 2, 'Start of' , 'LT', 1, 'C');
$pdf->SetXY(210, $Y);
$pdf->Cell(20, 2, 'End of' , 'LT', 1, 'C');
$pdf->SetXY(230, $Y);
$pdf->Cell(30, 2, '' , 'LT', 1, 'C');
$pdf->SetXY(260, $Y);
$pdf->Cell(15, 2, '' , 'LT', 1, 'C');
$pdf->SetXY(275, $Y);
$pdf->Cell(20, 2, 'Estimated Budget /' , 'LT', 1, 'C');
$pdf->SetXY(295, $Y);
$pdf->Cell(20, 2, 'ATTACHED' , 'L', 1, 'C');
$pdf->SetXY(315, $Y);
$pdf->Cell(20, 2, '' , 'LR', 1, 'C');

//COLUMN 2
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 4);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 2, 'of the Project to be Procured' , 'L', 1, 'C');
$pdf->SetXY(80, $Y);
$pdf->Cell(20, 2, 'Project' , 'L', 1, 'C');
$pdf->SetXY(100, $Y);
$pdf->Cell(15, 2, '' , 'L', 1, 'C');
$pdf->SetXY(115, $Y);
$pdf->Cell(15, 2, '' , 'L', 1, 'C');
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 2, '' , 'L', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(20, 2, 'Mode of' , 'L', 1, 'C');
$pdf->SetXY(170, $Y);
$pdf->Cell(20, 2, 'Conference, if' , 'L', 1, 'C');
$pdf->SetXY(190, $Y);
$pdf->Cell(20, 2, 'Procurement' , 'L', 1, 'C');
$pdf->SetXY(210, $Y);
$pdf->Cell(20, 2, 'Procurement' , 'L', 1, 'C');
$pdf->SetXY(230, $Y);
$pdf->Cell(30, 2, 'Expected Delivery' , 'L', 1, 'C');
$pdf->SetXY(260, $Y);
$pdf->Cell(15, 2, 'Source of' , 'L', 1, 'C');
$pdf->SetXY(275, $Y);
$pdf->Cell(20, 2, 'Authorized Budgetary' , 'L', 1, 'C');
$pdf->SetXY(295, $Y);
$pdf->Cell(20, 2, 'SUPPORTING' , 'L', 1, 'C');
$pdf->SetXY(315, $Y);
$pdf->Cell(20, 2, 'REMARKS' , 'LR', 1, 'C');

//COLUMN 3
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 4);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 2, '' , 'L', 1, 'C');
$pdf->SetXY(80, $Y);
$pdf->Cell(20, 2, 'to be Procured' , 'L', 1, 'C');
$pdf->SetXY(100, $Y);
$pdf->Cell(15, 2, 'Quantity' , 'L', 1, 'C');
$pdf->SetXY(115, $Y);
$pdf->Cell(15, 2, 'Size' , 'L', 1, 'C');
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 2, 'Unit Cost' , 'L', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(20, 2, 'Procurement' , 'L', 1, 'C');
$pdf->SetXY(170, $Y);
$pdf->Cell(20, 2, 'applicable(Yes/No)' , 'L', 1, 'C');
$pdf->SetXY(190, $Y);
$pdf->Cell(20, 2, 'Activity' , 'L', 1, 'C');
$pdf->SetXY(210, $Y);
$pdf->Cell(20, 2, 'Activity' , 'L', 1, 'C');
$pdf->SetXY(230, $Y);
$pdf->Cell(30, 2, 'Implementation Period' , 'L', 1, 'C');
$pdf->SetXY(260, $Y);
$pdf->Cell(15, 2, 'Funds' , 'L', 1, 'C');
$pdf->SetXY(275, $Y);
$pdf->Cell(20, 2, 'Allocation(PhP)' , 'L', 1, 'C');
$pdf->SetXY(295, $Y);
$pdf->Cell(20, 2, 'DOCUMENTS' , 'L', 1, 'C');
$pdf->SetXY(315, $Y);
$pdf->Cell(20, 2, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 4);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(80, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(100, $Y);
$pdf->Cell(15, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(115, $Y);
$pdf->Cell(15, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(170, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(190, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(210, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(230, $Y);
$pdf->Cell(30, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(260, $Y);
$pdf->Cell(15, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(275, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(295, $Y);
$pdf->Cell(20, 2, '' , 'LB', 1, 'C');
$pdf->SetXY(315, $Y);
$pdf->Cell(20, 2, '' , 'LBR', 1, 'C');

$Y = $pdf->GetY();

$pdf->SetFont('Arial', '', 5);

// Set grey background
$pdf->SetFillColor(220, 220, 220); // light grey (RGB)

$pdf->SetXY(20, $Y);
$pdf->Cell(60, 3, 'Column 1', 1, 1, 'C', true);
$pdf->SetXY(80, $Y);
$pdf->Cell(20, 3, 'Column 2', 1, 1, 'C', true);
$pdf->SetXY(100, $Y);
$pdf->Cell(50, 3, 'Column 3', 1, 1, 'C', true);
$pdf->SetXY(150, $Y);
$pdf->Cell(20, 3, 'Column 4', 1, 1, 'C', true);
$pdf->SetXY(170, $Y);
$pdf->Cell(20, 3, 'Column 5', 1, 1, 'C', true);
$pdf->SetXY(190, $Y);
$pdf->Cell(20, 3, 'Column 6', 1, 1, 'C', true);
$pdf->SetXY(210, $Y);
$pdf->Cell(20, 3, 'Column 7', 1, 1, 'C', true);
$pdf->SetXY(230, $Y);
$pdf->Cell(30, 3, 'Column 8', 1, 1, 'C', true);
$pdf->SetXY(260, $Y);
$pdf->Cell(15, 3, 'Column 9', 1, 1, 'C', true);
$pdf->SetXY(275, $Y);
$pdf->Cell(20, 3, 'Column 10', 1, 1, 'C', true);
$pdf->SetXY(295, $Y);
$pdf->Cell(20, 3, 'Column 11', 1, 1, 'C', true);
$pdf->SetXY(315, $Y);
$pdf->Cell(20, 3, 'Column 12', 1, 1, 'C', true);


// $Y = $pdf->GetY();
// $pdf->SetXY(20, $Y);
// $pdf->Cell(60, 5, 'AIR FRESHENER' , 1, 1, 'C');
// $pdf->SetXY(80, $Y);
// $pdf->Cell(20, 5, 'Goods' , 1, 1, 'C');
// $pdf->SetXY(100, $Y);
// $pdf->Cell(15, 5, '1' , 1, 1, 'C');
// $pdf->SetXY(115, $Y);
// $pdf->Cell(15, 5, 'pc' , 1, 1, 'C');
// $pdf->SetXY(130, $Y);
// $pdf->Cell(20, 5, '750.00' , 1, 1, 'C');
// $pdf->SetXY(150, $Y);
// $pdf->Cell(20, 5, 'Direct Acquisition' , 1, 1, 'C');
// $pdf->SetXY(170, $Y);
// $pdf->Cell(20, 5, 'No' , 1, 1, 'C');
// $pdf->SetXY(190, $Y);
// $pdf->Cell(20, 5, '12/2025' , 1, 1, 'C');
// $pdf->SetXY(210, $Y);
// $pdf->Cell(20, 5, '02/2026' , 1, 1, 'C');
// $pdf->SetXY(230, $Y);
// $pdf->Cell(30, 5, '03/2025 to 04/2026' , 1, 1, 'C');
// $pdf->SetXY(260, $Y);
// $pdf->Cell(15, 5, '101' , 1, 1, 'C');
// $pdf->SetXY(275, $Y);
// $pdf->Cell(20, 5, '750.00' , 1, 1, 'C');
// $pdf->SetXY(295, $Y);
// $pdf->Cell(20, 5, 'Market Scoping' , 1, 1, 'C');
// $pdf->SetXY(315, $Y);
// $pdf->Cell(20, 5, '' , 1, 1, 'C');

//SUM OF DT DATA
$query = $this->db->query("
SELECT
    SUM(`estimated_budget`) total_budget
FROM
    `tbl_ppmp_dt`
WHERE 
    `ppmp_id` = '$recid'
");
$rw = $query->getRowArray();
$total_budget = $rw['total_budget'];

$query = $this->db->query("
SELECT
    `item_desc`,
    `item_type`,
    `quantity`,
    `size`,
    `unit_cost`,
    `mop`,
    `is_preproc`,
    `proc_start`,
    `proc_end`,
    `expected_delivery_from`,
    `expected_delivery_to`,
    `funding_source`,
    `estimated_budget`,
    `attached_document`,
    `remarks`
FROM
    `tbl_ppmp_dt`
WHERE 
    `ppmp_id` = '$recid'
");

$rw = $query->getResultArray();
$expected_delivery_period = '';
foreach ($rw as $data) {
    $item_desc = $data['item_desc'];
    $item_type = $data['item_type'];
    $quantity = $data['quantity'];
    $size = $data['size'];
    $unit_cost = $data['unit_cost'];
    $mop = $data['mop'];
    $is_preproc = $data['is_preproc'];
    $proc_start = $data['proc_start'];
    $proc_end = $data['proc_end'];
    $expected_delivery_from = $data['expected_delivery_from'];
    $expected_delivery_to = $data['expected_delivery_to'];
    $funding_source = $data['funding_source'];
    $estimated_budget = $data['estimated_budget'];
    $attached_document = $data['attached_document'];
    $remarks = $data['remarks'];

    if (!empty($expected_delivery_from) && !empty($expected_delivery_to)) {
        $expected_delivery_period =
            date('m/Y', strtotime($expected_delivery_from)) .
            ' to ' .
            date('m/Y', strtotime($expected_delivery_to));
    } else {
        $expected_delivery_period = '';
    }

    $proc_start = date('m/Y', strtotime($proc_start));
    $proc_end = date('m/Y', strtotime($proc_end));

    if ($funding_source == 'General Fund') {
        $funding_source = '101';
    }elseif ($funding_source == 'Trust Fund') {
        $funding_source = '184';
    }elseif($funding_source == 'Project Fund (Externally Funded)'){
        $funding_source = '174';
    }else{
        $funding_source = '';
    }

    if ($pdf->GetY() > 155) { // adjust threshold as needed
        $pdf->AddPage();
    }

    
    $startY = $pdf->GetY();

    // MAIN MULTICELL (AIR FRESHENER)
    $pdf->SetXY(20, $startY);
    $pdf->MultiCell(60, 5, $item_desc, 0, 'L');

    // Compute dynamic row height
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // DRAW CELL BORDERS
    $pdf->SetXY(20,  $startY); $pdf->Cell(60, $rowHeight, '', 1);  // AIR FRESHENER
    $pdf->SetXY(80,  $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Goods
    $pdf->SetXY(100, $startY); $pdf->Cell(15, $rowHeight, '', 1);  // Qty
    $pdf->SetXY(115, $startY); $pdf->Cell(15, $rowHeight, '', 1);  // Unit
    $pdf->SetXY(130, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Price
    $pdf->SetXY(150, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Mode
    $pdf->SetXY(170, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Framework
    $pdf->SetXY(190, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Start
    $pdf->SetXY(210, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // End
    $pdf->SetXY(230, $startY); $pdf->Cell(30, $rowHeight, '', 1);  // Period
    $pdf->SetXY(260, $startY); $pdf->Cell(15, $rowHeight, '', 1);  // Code
    $pdf->SetXY(275, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Amount
    $pdf->SetXY(295, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Method
    $pdf->SetXY(315, $startY); $pdf->Cell(20, $rowHeight, '', 1);  // Remarks

    // VERTICAL CENTERING FOR SINGLE-LINE CELLS
    $middleY = $startY + ($rowHeight / 2) - 2.5;

    $pdf->SetXY(80,  $middleY); $pdf->Cell(20, 5, $item_type, 0, 0, 'C');
    $pdf->SetXY(100, $middleY); $pdf->Cell(15, 5, $quantity, 0, 0, 'C');
    $pdf->SetXY(115, $middleY); $pdf->Cell(15, 5, $size, 0, 0, 'C');
    $pdf->SetXY(130, $middleY); $pdf->Cell(20, 5, $unit_cost, 0, 0, 'C');
    $pdf->SetXY(150, $middleY); $pdf->Cell(20, 5, $mop, 0, 0, 'C');
    $pdf->SetXY(170, $middleY); $pdf->Cell(20, 5, $is_preproc, 0, 0, 'C');
    $pdf->SetXY(190, $middleY); $pdf->Cell(20, 5, $proc_start, 0, 0, 'C');
    $pdf->SetXY(210, $middleY); $pdf->Cell(20, 5, $proc_end, 0, 0, 'C');
    $pdf->SetXY(230, $middleY); $pdf->Cell(30, 5, $expected_delivery_period, 0, 0, 'C');
    $pdf->SetXY(260, $middleY); $pdf->Cell(15, 5, $funding_source, 0, 0, 'C');
    $pdf->SetXY(275, $middleY); $pdf->Cell(20, 5, $estimated_budget, 0, 0, 'C');
    $pdf->SetXY(295, $middleY); $pdf->Cell(20, 5, $attached_document, 0, 0, 'C');
    $pdf->SetXY(315, $middleY); $pdf->Cell(20, 5, $remarks, 0, 0, 'C');

    // MOVE CURSOR TO NEXT ROW
    $pdf->SetY($endY);

}

$currentY = $pdf->GetY();

if ($pdf->PageNo() == 1) {

    // Fill empty rows until Y reaches 135 (FIRST PAGE ONLY)
    while ($currentY < 155) {

        $pdf->SetXY(20,  $currentY); $pdf->Cell(60, 5, '', 1, 0, 'C'); // AIR FRESHENER
        $pdf->SetXY(80,  $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Goods
        $pdf->SetXY(100, $currentY); $pdf->Cell(15, 5, '', 1, 0, 'C'); // Qty
        $pdf->SetXY(115, $currentY); $pdf->Cell(15, 5, '', 1, 0, 'C'); // Unit
        $pdf->SetXY(130, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Unit Cost
        $pdf->SetXY(150, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // MOP
        $pdf->SetXY(170, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Preproc
        $pdf->SetXY(190, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Proc Start
        $pdf->SetXY(210, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Proc End
        $pdf->SetXY(230, $currentY); $pdf->Cell(30, 5, '', 1, 0, 'C'); // Expected Delivery
        $pdf->SetXY(260, $currentY); $pdf->Cell(15, 5, '', 1, 0, 'C'); // Funding Source
        $pdf->SetXY(275, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Estimated Budget
        $pdf->SetXY(295, $currentY); $pdf->Cell(20, 5, '', 1, 0, 'C'); // Attached Doc
        $pdf->SetXY(315, $currentY); $pdf->Cell(20, 5, '', 1, 1, 'C'); // Remarks (LAST)

        $currentY = $pdf->GetY();
    }
}

// Draw bottom border to close table
$pdf->SetXY(20, $currentY);
$pdf->Cell(315, 0, '', 'T');

$pdf->SetFont('Arial', 'B', 7);
$Y = $currentY;
$pdf->SetXY(230, $Y);
$pdf->Cell(45, 5, 'TOTAL BUDGET:' , 1, 1, 'C');

$pdf->SetXY(275, $Y);
$pdf->Cell(20, 5, number_format($total_budget,2) , 1, 1, 'C');

$Y = $pdf->GetY() +5;
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 5, 'Prepared by:' , 0, 1, 'L');

$pdf->SetXY(190, $Y);
$pdf->Cell(65, 5, 'Submitted by:' , 0, 1, 'L');

$Y = $pdf->GetY()+3;
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 3, $prepared_by , 'B', 1, 'C');

$pdf->SetXY(190, $Y);
$pdf->Cell(65, 3, $submitted_by , 'B', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 5.5);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 3, 'Signature over Printed Name' , 0, 1, 'C');

$pdf->SetXY(190, $Y);
$pdf->Cell(65, 3, 'Signature over Printed Name' , 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(20, $Y);
$pdf->Cell(60, 3, 'End-User' , 0, 1, 'C');

$pdf->SetXY(190, $Y);
$pdf->Cell(65, 3, 'Division/Section Chief/Project Leader' , 0, 1, 'C');

$pdf->Output();
exit;
?>