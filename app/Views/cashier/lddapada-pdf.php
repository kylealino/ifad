<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$month = $this->request->getPostGet('month');
$year = $this->request->getPostGet('year');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));

$currentYear = date('Y');
$lastYear = $currentYear - 1;
$query = $this->db->query("
SELECT
    a.`ckno`,
    a.`ada_approver`,
    (select position from myua_user where full_name = a.`ada_approver`) AS `position`
FROM
    `tbl_lddapada_hd` a
WHERE 
    a.`recid` = '$recid'"
);

$data = $query->getRowArray();
$ckno = $data['ckno'];
$ada_approver = $data['ada_approver'];
$position = $data['position'];


$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('LDDAP-ADA Print');
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;

// Draw the cell first
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 4, '' , 'LTR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(20, 4, 'Department:' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(100, 4, 'Department of Science and Technology' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, 'LDDAP-ADA No.' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, '184-04-145-2026' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(20, 4, 'Entity Name:' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(100, 4, 'Food and Nutrition Research Institute' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, 'Date:' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, $formattedDate , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(20, 4, '' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(100, 4, '' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, 'Fund Cluster:' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, '07' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(20, 4, '' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(100, 4, '' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, '' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, '' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(45, 4, 'MDS-GSB BRANCH:' , 'L', 1, 'L');
$pdf->SetXY(55, $Y);
$pdf->Cell(100, 4, 'Land Bank of the Philippines - DOST Branch' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, '' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, '' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(45, 4, 'MDS SUB ACCOUNT NO.:' , 'L', 1, 'L');
$pdf->SetXY(55, $Y);
$pdf->Cell(100, 4, '2182-9006-16' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, '' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, '' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(20, 4, '' , 'L', 1, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(100, 4, '' , 0, 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->Cell(25, 4, '' , 0, 1, 'R');
$pdf->SetXY(155, $Y);
$pdf->Cell(45, 4, '' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(190, 4, 'I. LIST OF DUE AND DEMANDABLE ACCOUNTS PAYABLE (LDDAP)' , 1, 1, 'C');
$pdf->SetFont('Arial', 'B', 4.5);
$Y = $pdf->GetY();
$pdf->Cell(40, 2.5, '' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 2.5, '' , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 2.5, '' , 'L', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 2.5, '' , 'LR', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 2.5, '' , 'R', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 2.5, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->Cell(40, 2.5, 'CREDITOR' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 2.5, 'PREFERRED SERVICING' , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 2.5, 'Obligation Request and' , 'L', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 2.5, 'ALLOTMENT' , 'L', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 2.5, 'In Pesos' , 'L', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 2.5, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->Cell(40, 2.5, 'NAME' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 2.5, 'BANKS/' , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 2.5, 'Status No.' , 'L', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 2.5, 'CLASS per' , 'L', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 2.5, '' , 'LT', 1, 'C');
$pdf->SetXY(145, $Y);
$pdf->Cell(15, 2.5, '' , 'LT', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(25, 2.5, '' , 'LT', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 2.5, 'REMARKS' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->Cell(40, 2.5, '' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 2.5, 'SAVINGS/CURRENT' , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 2.5, '' , 'L', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 2.5, '(UACS)' , 'L', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 2.5, 'GROSS AMOUNT' , 'L', 1, 'C');
$pdf->SetXY(145, $Y);
$pdf->Cell(15, 2.5, 'WITHOLDING TAX' , 'L', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(25, 2.5, 'NET AMOUNT' , 'L', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 2.5, '' , 'LR', 1, 'C');

$Y = $pdf->GetY();
$pdf->Cell(40, 2.5, '' , 'LB', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 2.5, 'ACCOUNT NO.' , 'LB', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 2.5, '' , 'LB', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 2.5, '' , 'LB', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 2.5, '' , 'LB', 1, 'C');
$pdf->SetXY(145, $Y);
$pdf->Cell(15, 2.5, '' , 'LB', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(25, 2.5, '' , 'LB', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 2.5, '' , 'LRB', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6);
// your row cells
$pdf->Cell(40, 4, 'I. Current Year A/Ps', 'L', 0, 'L');
$pdf->Cell(25, 4, '', 'L', 0, 'C');
$pdf->Cell(30, 4, '', 'L', 0, 'C');
$pdf->Cell(15, 4, '', 'L', 0, 'C');
$pdf->Cell(25, 4, '', 'L', 0, 'C');
$pdf->Cell(15, 4, '', 'L', 0, 'C');
$pdf->Cell(25, 4, '', 'LR', 0, 'C');

// save X before writing last column
$X = $pdf->GetX();

// move back to top Y
$pdf->SetXY($X, $Y);
$pdf->SetFont('Arial', '', 4);
// MultiCell for 2-line text with only top & bottom border
$pdf->MultiCell(15, 2, "FOR MDS-GSB\nUSE ONLY", 'LR', 'C');

$Y = $pdf->GetY();

$query = $this->db->query("
SELECT
    a.`dvno`,
    a.`payee_name`,
    a.`payee_account_num`,
    a.`serialno`,
    a.`uacs_code`,
    a.`gross_amount`,
    a.`total_deduction`,
    a.`net_amount`,
    b.`disbursement_date`,
    a.`remarks`
FROM
    `tbl_lddapada_dt` a
LEFT JOIN
    `tbl_disbursement_hd` b
ON
    a.`dvno` = b.`dvno`
WHERE 
    `lddapada_id` = '$recid' AND  YEAR(b.disbursement_date)  = '$currentYear'
ORDER BY
    a.`recid` ASC
");

$data = $query->getResultArray();

$pdf->SetFont('Arial', '', 6.5);

$subtotal_gross_cy = 0;
$subtotal_deduction_cy = 0;
$subtotal_net_cy = 0;
foreach ($data as $row) {

    $payee_name = str_replace(["\r","\n"], '', $row['payee_name']);
    $account_no = $row['payee_account_num'];
    $dvno       = $row['dvno'];
    $uacs       = $row['uacs_code'];
    $gross      = $row['gross_amount'];
    $deduction  = $row['total_deduction'];
    $net        = $row['net_amount'];
    $remarks    = trim($row['remarks']);

    // ============================================
    // START POSITION
    // ============================================
    $startY = $pdf->GetY();

    // ============================================
    // GET PAYEE HEIGHT
    // ============================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(40, 4, $payee_name, 0, 'L');

    $payeeHeight = $pdf->GetY() - $startY;

    // ============================================
    // GET REMARKS HEIGHT
    // ============================================
    $pdf->SetXY(185, $startY);

    if (!empty($remarks)) {
        $pdf->MultiCell(15, 4, $remarks, 0, 'C');
    } else {
        $pdf->Cell(15, 4, '', 0, 0, 'C');
    }

    $remarksHeight = $pdf->GetY() - $startY;

    // ============================================
    // FINAL ROW HEIGHT
    // ============================================
    $rowHeight = max($payeeHeight, $remarksHeight);

    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // ============================================
    // PAYEE NAME
    // ============================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(40, 4, '', 'L', 'L');

    $currentY = $pdf->GetY();

    if ($currentY < ($startY + $rowHeight)) {

        $remaining = ($startY + $rowHeight) - $currentY;

        $pdf->SetXY(10, $currentY);
        $pdf->Cell(40, $remaining, '', 'L', 0, 'L');
    }

    // ============================================
    // ACCOUNT NO
    // ============================================
    $pdf->SetXY(50, $startY);
    $pdf->Cell(25, $rowHeight, $account_no, 'L', 0, 'C');

    // ============================================
    // DV NO
    // ============================================
    $pdf->SetXY(75, $startY);
    $pdf->Cell(30, $rowHeight, $dvno, 'L', 0, 'C');

    // ============================================
    // UACS
    // ============================================
    $pdf->SetXY(105, $startY);
    $pdf->Cell(15, $rowHeight, $uacs, 'L', 0, 'C');

    // ============================================
    // GROSS
    // ============================================
    $pdf->SetXY(120, $startY);
    $pdf->Cell(25, $rowHeight, number_format($gross,2), 'L', 0, 'C');

    // ============================================
    // DEDUCTION
    // ============================================
    $pdf->SetXY(145, $startY);
    $pdf->Cell(15, $rowHeight, number_format($deduction,2), 'L', 0, 'C');

    // ============================================
    // NET
    // ============================================
    $pdf->SetXY(160, $startY);
    $pdf->Cell(25, $rowHeight, number_format($net,2), 'L', 0, 'C');

    // ============================================
    // REMARKS
    // ============================================
    $pdf->SetXY(185, $startY);

    if (!empty($remarks)) {

        $pdf->MultiCell(15, 4, '', 'LR', 'C');

    } else {

        $pdf->Cell(15, 4, '', 'LR', 0, 'C');
    }

    $currentY = $pdf->GetY();

    if ($currentY < ($startY + $rowHeight)) {

        $remaining = ($startY + $rowHeight) - $currentY;

        $pdf->SetXY(185, $currentY);
        $pdf->Cell(15, $remaining, '', 'LR', 0, 'C');
    }

    // ============================================
    // MOVE TO NEXT ROW
    // ============================================
    $pdf->SetY($startY + $rowHeight);

    // ============================================
    // TOTALS
    // ============================================
    $subtotal_gross_cy += $gross;
    $subtotal_deduction_cy += $deduction;
    $subtotal_net_cy += $net;
}

$targetY = 90;

// keep adding empty rows
while ($pdf->GetY() < $targetY) {

    $startY = $pdf->GetY();
    $rowHeight = 4; // same as your minimum row height

    // PAYEE NAME
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(40, 4, '', 'L', 'L');

    // ACCOUNT NO
    $pdf->SetXY(50, $startY);
    $pdf->Cell(25, $rowHeight, '', 'L', 0, 'C');

    // DV NO
    $pdf->SetXY(75, $startY);
    $pdf->Cell(30, $rowHeight, '', 'L', 0, 'C');

    // UACS
    $pdf->SetXY(105, $startY);
    $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');

    // GROSS
    $pdf->SetXY(120, $startY);
    $pdf->Cell(25, $rowHeight, '', 'L', 0, 'C');

    // DEDUCTION
    $pdf->SetXY(145, $startY);
    $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');

    // NET
    $pdf->SetXY(160, $startY);
    $pdf->Cell(25, $rowHeight, '', 'L', 0, 'C');

    // REMARKS
    $pdf->SetXY(185, $startY);
    $pdf->Cell(15, $rowHeight, '', 'LR', 0, 'C');

    // move next row
    $pdf->SetY($startY + $rowHeight);
}

$pdf->SetFont('Arial', 'B', 6.5);
$Y = $pdf->GetY();
$X = 10;
$pdf->SetXY(10, $Y);
$pdf->Cell(40, 4, 'Sub-total' , 'LB', 1, 'L');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 4, '' , 'LB', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 4, '' , 'LB', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 4, '' , 'LB', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 4, 'P'. number_format($subtotal_gross_cy,2) , 'LB', 1, 'C');
$pdf->SetXY(145, $Y);
$pdf->Cell(15, 4, 'P'. number_format($subtotal_deduction_cy,2) , 'LB', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(25, 4, 'P'. number_format($subtotal_net_cy,2) , 'LB', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 4, '' , 'LRB', 1, 'C');

$Y = $pdf->GetY($Y);
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetXY(10, $Y);
$pdf->Cell(40, 4, "II. Prior Year's A/Ps", 'L', 0, 'L');
$pdf->Cell(25, 4, '', 'L', 0, 'C');
$pdf->Cell(30, 4, '', 'L', 0, 'C');
$pdf->Cell(15, 4, '', 'L', 0, 'C');
$pdf->Cell(25, 4, '', 'L', 0, 'C');
$pdf->Cell(15, 4, '', 'L', 0, 'C');
$pdf->Cell(25, 4, '', 'L', 0, 'C');
$pdf->Cell(15, 4, '', 'LR', 0, 'C');

$Y = $pdf->GetY()+4;
$pdf->SetXY(10, $Y);
$query = $this->db->query("
SELECT
    a.`dvno`,
    a.`payee_name`,
    a.`payee_account_num`,
    a.`serialno`,
    a.`uacs_code`,
    a.`gross_amount`,
    a.`total_deduction`,
    a.`net_amount`,
    b.`disbursement_date`,
    a.`remarks`
FROM
    `tbl_lddapada_dt` a
LEFT JOIN
    `tbl_disbursement_hd` b
ON
    a.`dvno` = b.`dvno`
WHERE 
    `lddapada_id` = '$recid' AND  YEAR(b.disbursement_date)  = '$lastYear'
ORDER BY
    a.`recid` ASC
");

$data = $query->getResultArray();

$pdf->SetFont('Arial', '', 6.5);
$subtotal_gross_py = 0;
$subtotal_deduction_py = 0;
$subtotal_net_py = 0;
foreach ($data as $row) {

    $payee_name = str_replace(["\r","\n"], '', $row['payee_name']);
    $account_no = $row['payee_account_num'];
    $dvno       = $row['dvno'];
    $uacs       = $row['uacs_code'];
    $gross      = $row['gross_amount'];
    $deduction  = $row['total_deduction'];
    $net        = $row['net_amount'];
    $remarks    = trim($row['remarks']);

    // ============================================
    // START POSITION
    // ============================================
    $startY = $pdf->GetY();

    // ============================================
    // GET PAYEE HEIGHT
    // ============================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(40, 4, $payee_name, 0, 'L');

    $payeeHeight = $pdf->GetY() - $startY;

    // ============================================
    // GET REMARKS HEIGHT
    // ============================================
    $pdf->SetXY(185, $startY);

    if (!empty($remarks)) {
        $pdf->MultiCell(15, 4, $remarks, 0, 'C');
    } else {
        $pdf->Cell(15, 4, '', 0, 0, 'C');
    }

    $remarksHeight = $pdf->GetY() - $startY;

    // ============================================
    // FINAL ROW HEIGHT
    // ============================================
    $rowHeight = max($payeeHeight, $remarksHeight);

    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // ============================================
    // PAYEE NAME
    // ============================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(40, 4, '', 'L', 'L');

    $currentY = $pdf->GetY();

    if ($currentY < ($startY + $rowHeight)) {

        $remaining = ($startY + $rowHeight) - $currentY;

        $pdf->SetXY(10, $currentY);
        $pdf->Cell(40, $remaining, '', 'L', 0, 'L');
    }

    // ============================================
    // ACCOUNT NO
    // ============================================
    $pdf->SetXY(50, $startY);
    $pdf->Cell(25, $rowHeight, $account_no, 'L', 0, 'C');

    // ============================================
    // DV NO
    // ============================================
    $pdf->SetXY(75, $startY);
    $pdf->Cell(30, $rowHeight, $dvno, 'L', 0, 'C');

    // ============================================
    // UACS
    // ============================================
    $pdf->SetXY(105, $startY);
    $pdf->Cell(15, $rowHeight, $uacs, 'L', 0, 'C');

    // ============================================
    // GROSS
    // ============================================
    $pdf->SetXY(120, $startY);
    $pdf->Cell(25, $rowHeight, number_format($gross,2), 'L', 0, 'C');

    // ============================================
    // DEDUCTION
    // ============================================
    $pdf->SetXY(145, $startY);
    $pdf->Cell(15, $rowHeight, number_format($deduction,2), 'L', 0, 'C');

    // ============================================
    // NET
    // ============================================
    $pdf->SetXY(160, $startY);
    $pdf->Cell(25, $rowHeight, number_format($net,2), 'L', 0, 'C');

    // ============================================
    // REMARKS
    // ============================================
    $pdf->SetXY(185, $startY);

    if (!empty($remarks)) {

        $pdf->MultiCell(15, 4, '', 'LR', 'C');

    } else {

        $pdf->Cell(15, 4, '', 'LR', 0, 'C');
    }

    $currentY = $pdf->GetY();

    if ($currentY < ($startY + $rowHeight)) {

        $remaining = ($startY + $rowHeight) - $currentY;

        $pdf->SetXY(185, $currentY);
        $pdf->Cell(15, $remaining, '', 'LR', 0, 'C');
    }

    // ============================================
    // MOVE TO NEXT ROW
    // ============================================
    $pdf->SetY($startY + $rowHeight);

    // ============================================
    // TOTALS
    // ============================================
    $subtotal_gross_py += $gross;
    $subtotal_deduction_py += $deduction;
    $subtotal_net_py += $net;
}

$targetYY = 130;

// keep adding empty rows
while ($pdf->GetY() < $targetYY) {

    $startY = $pdf->GetY();
    $rowHeight = 4; // same as your minimum row height

    // PAYEE NAME
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(40, 4, '', 'L', 'L');

    // ACCOUNT NO
    $pdf->SetXY(50, $startY);
    $pdf->Cell(25, $rowHeight, '', 'L', 0, 'C');

    // DV NO
    $pdf->SetXY(75, $startY);
    $pdf->Cell(30, $rowHeight, '', 'L', 0, 'C');

    // UACS
    $pdf->SetXY(105, $startY);
    $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');

    // GROSS
    $pdf->SetXY(120, $startY);
    $pdf->Cell(25, $rowHeight, '', 'L', 0, 'C');

    // DEDUCTION
    $pdf->SetXY(145, $startY);
    $pdf->Cell(15, $rowHeight, '', 'L', 0, 'C');

    // NET
    $pdf->SetXY(160, $startY);
    $pdf->Cell(25, $rowHeight, '', 'L', 0, 'C');

    // REMARKS
    $pdf->SetXY(185, $startY);
    $pdf->Cell(15, $rowHeight, '', 'LR', 0, 'C');

    // move next row
    $pdf->SetY($startY + $rowHeight);
}
//SUB TOTAL PRIOR YEAR ----------------------
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6.5);
$X = 10;
$pdf->SetXY(10, $Y);
$pdf->Cell(40, 4, 'Sub-total' , 'LR', 1, 'L');
$pdf->SetXY(50, $Y);
$pdf->Cell(25, 4, '' , 'LR', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 4, '' , 'LR', 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 4, '' , 'LR', 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 4, 'P'. number_format($subtotal_gross_py,2) , 'LR', 1, 'C');
$pdf->SetXY(145, $Y);
$pdf->Cell(15, 4, 'P'. number_format($subtotal_deduction_py,2) , 'LR', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(25, 4, 'P'. number_format($subtotal_net_py,2) , 'LR', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 4, '' , 'LR', 1, 'C');


$grandtotal_gross_py = $subtotal_gross_cy + $subtotal_gross_py;
$grandtotal_deduction_py = $subtotal_deduction_cy + $subtotal_deduction_py;
$grandtotal_net_py = $subtotal_net_cy + $subtotal_net_py;

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6.5);
$X = 10;
$pdf->SetXY(10, $Y);
$pdf->Cell(110, 8, 'Total' , 1, 1, 'L');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 8, 'P'. number_format($grandtotal_gross_py,2) , 1, 1, 'C');
$pdf->SetXY(145, $Y);
$pdf->Cell(15, 8, 'P'. number_format($grandtotal_deduction_py,2) , 1, 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(25, 8, 'P'. number_format($grandtotal_net_py,2) , 1, 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 8, '' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 4, '' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 2, '' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 3, 'I hereby warrant that the above List of Due and Demandable' , 'L', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 3, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 3, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(80, 3, 'I hereby assume full responsibility for the veracity and' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 3, 'A/Ps was prepared in accordance with existing budgeting,' , 'L', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 3, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 3, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(80, 3, 'accuracy of the listed claims, and the authenticity of the' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 3, 'accounting and auditing rules and regulations.' , 'L', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 3, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 3, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(80, 3, 'supporting documents as submitted by the claimants.' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, '' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 3, 'Certified Correct:' , 'L', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 3, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 3, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(80, 3, 'Approved:' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 9, '' , 'LB', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 9, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 9, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 9, '' , 'B', 1, 'L');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 9, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 4, 'BONJOBIE F. CAJANO, CPA, CTT' , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 4, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 4, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 4, 'ALEXIS M. ORTIZ' , 0, 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 4, 'Accountant III' , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 4, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 4, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 4, 'Chief Administrative Officer' , 0, 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 6, '' , 'LR', 1, 'L');


$Y = $pdf->GetY();
$pdf->Cell(190, 4, 'II. ADVICE TO DEBIT ACCOUNT (ADA)' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(190, 4, 'To: MDS-GSB of the Agency' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(42, 4, 'Please debit MDS Sub-Account Number:' , 'L', 1, 'L');
$pdf->SetXY(52, $Y);
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(148, 4, '2182-9006-16' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->Cell(190, 4, 'Please credit the accounts of the above listed creditors to cover payment of accounts payable' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 6, '' , 'LR', 1, 'L');
//NUMBER FORMATTER
$amount = number_format($grandtotal_net_py, 2, '.', '');

list($pesos, $centavos) = explode('.', $amount);

$formatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

// FIRST LETTERS CAPITALIZED
$amount_words = ucwords($formatter->format($pesos));

$amount_words .= ' Pesos & ' . $centavos . '/100';


$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 6, 'TOTAL AMOUNT:' , 'L', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 6, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 6, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(25, 6, 'Pesos:' , '', 1, 'R');
$pdf->SetXY(145, $Y);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 6, number_format($grandtotal_net_py,2) , 'B', 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 6, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 6, $amount_words , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 6, '' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, 'Agency Authorized Signatories' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 9, '' , 'LB', 1, 'L');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 9, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 9, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 9, '' , 'B', 1, 'L');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 9, '' , 'R', 1, 'C');

$ada_approver_converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $ada_approver);
$position_converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $position);

$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 4, $ada_approver_converted , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 4, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 4, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 4, 'ALEXIS M. ORTIZ' , 0, 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 4, $position_converted , 'L', 1, 'C');
$pdf->SetXY(75, $Y);
$pdf->Cell(30, 4, '' , 0, 1, 'C');
$pdf->SetXY(105, $Y);
$pdf->Cell(15, 4, '' , 0, 1, 'C');
$pdf->SetXY(120, $Y);
$pdf->Cell(65, 4, 'Chief Administrative Officer' , 0, 1, 'C');
$pdf->SetXY(185, $Y);
$pdf->Cell(15, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 15, '(Erasures shall invalidate this document)' , 'RBL', 1, 'C');

$Y = $pdf->GetY()+3;
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, 'Approver of LDDAP - one (1) approver' , 0, 1, 'L');
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, 'Approver of ADA - two (2) approver' , 0, 1, 'L');
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, 'Certifier of LDDAP - one (1) certifier' , 0, 1, 'L');

$Y = 267;
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 3, 'CK#'.$ckno , 0, 1, 'L');
$pdf->SetFont('Arial', '', 9);
$Y = $pdf->GetY()+2;
$pdf->SetXY(170, $Y);
$pdf->Cell(40, 3, $formattedDate , 0, 1, 'L');

$pdf->Output();
exit;
?>