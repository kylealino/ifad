<?php
// report_checks_issued.php
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

// Get header data from database
$query = $this->db->query("
    SELECT 
        mds_branch,
        mds_accountno,
        fund_cluster_code,
        reportno
    FROM tbl_rci_hd 
    WHERE recid = '$recid'
");
$headerData = $query->getRowArray();

$mds_branch = $headerData['mds_branch'] ?? 'LBP Bicutan';
$mds_accountno = $headerData['mds_accountno'] ?? '2182-9006-16';
$fund_cluster_code = $headerData['fund_cluster_code'] ?? '184';
$reportno = $headerData['reportno'] ?? '5-4-2026';

// Get line items from database
$query = $this->db->query("
    SELECT
        `lddapadano`,
        `lddapada_date`,
        `ckno`,
        `dvno`,
        `serialno`,
        `responsibility_code`,
        `payee_name`,
        `particulars`,
        `gross_amount`,
        `philhealth_amount`,
        `tax_amount`,
        `net_amount`
    FROM tbl_rci_dt
    WHERE hd_rid = '$recid'
    ORDER BY recid ASC
");
$data = $query->getResultArray();

// If no data, use sample data from image
if (empty($data)) {
    $data = [
        [
            'lddapadano' => '184-05-225-2026',
            'lddapada_date' => '2026-05-14',
            'ckno' => '2026050225',
            'dvno' => '20260906',
            'serialno' => '02-308601-2026-02-53',
            'responsibility_code' => 'T2025-26',
            'payee_name' => 'BIOSYN HEALTHCARE SYSTEMS, INC.',
            'particulars' => 'PO# 24-2026 dtd 4-FEB-2026 w/sales invoice# 0354 dtd 30-MAR-2026',
            'gross_amount' => 270000.00,
            'philhealth_amount' => 0.00,
            'tax_amount' => 16875.00,
            'net_amount' => 253125.00
        ],
        [
            'lddapadano' => '184-05-226-2026',
            'lddapada_date' => '2026-05-14',
            'ckno' => '9926050226',
            'dvno' => '20261022',
            'serialno' => 'VARIOUS BURS',
            'responsibility_code' => 'Various',
            'payee_name' => 'SOCIAL SECURITY SYSTEM',
            'particulars' => 'SSS Contributions for the month of APRIL 2026 (COS-184)',
            'gross_amount' => 30900.00,
            'philhealth_amount' => 0.00,
            'tax_amount' => 0.00,
            'net_amount' => 30900.00
        ]
    ];
}

// Calculate totals
$totalGross = 0;
$totalPhilhealth = 0;
$totalTax = 0;
$totalNet = 0;

foreach ($data as $row) {
    $totalGross += $row['gross_amount'];
    $totalPhilhealth += $row['philhealth_amount'];
    $totalTax += $row['tax_amount'];
    $totalNet += $row['net_amount'];
}

// Get first and last check numbers
$firstCheck = !empty($data) ? $data[0]['ckno'] : '';
$lastCheck = !empty($data) ? end($data)['ckno'] : '';

// Create PDF in Landscape
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('Report of Checks Issued');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false);

$pageWidth = 297;
$leftMargin = 10;
$rightMargin = 10;

// ========================= HEADER SECTION =========================
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, 'FOOD AND NUTRITION RESEARCH INSTITUTE', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'REPORT OF CHECKS ISSUED', 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'Period Covered: May 14 to 21, 2026', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(80, 4, 'Bank Name/Account No: ' . 'LBP Bicutan' . ' / ' . $mds_accountno, 0, 0, 'L');
$pdf->Cell(60, 4, 'Fund Cluster: ' . $fund_cluster_code, 0, 0, 'C');
$pdf->Cell(105, 4, 'Report No.      ' . $reportno, 0, 0, 'R');
$pdf->Cell(30, 4, 'Sheet #: 1', 0, 1, 'R');

/*
|--------------------------------------------------------------------------
| COLUMN WIDTHS (Total = 277mm with 10mm margins)
|--------------------------------------------------------------------------
*/
$w = [
    'date'      => 14,   // Date
    'check'     => 18,   // CHECK NO.
    'lddap'     => 24,   // LDDAP-ADA #
    'dv'        => 14,   // DV #
    'ors'       => 24,   // ORS/BURS #
    'resp'      => 18,   // RESPONSIBILITY CENTER CODE
    'payee'     => 34,   // PAYEE
    'nature'    => 40,   // NATURE OF PAYMENT
    'gross'     => 18,   // Gross
    'phil'      => 13,   // Philhealth
    'tax'       => 14,   // Tax
    'net'       => 18,   // Net
    'jev'       => 13,   // JEV NO.
    'date2'     => 13    // DATE
];

/*
|--------------------------------------------------------------------------
| TWO-ROW TABLE HEADER (Using your exact coding style - FIXED)
|--------------------------------------------------------------------------
*/
$Y = $pdf->GetY();

// Row 1: Main title row - FIXED widths to match columns below
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->SetXY(10, $Y);
$pdf->Cell(32, 4, 'CHECK', 1, 0, 'C');        // 14+18 = 32mm
$pdf->Cell(24, 4, '', 'LT', 0, 'C');  // 24mm
$pdf->Cell(14, 4, '', 'LT', 0, 'C');         // 14mm
$pdf->Cell(24, 4, '', 'LT', 0, 'C');   // 24mm
$pdf->Cell(18, 4, "RESPONSIBILITY", 'LT', 0, 'C'); // 18mm
$pdf->Cell(34, 4, '', 'LT', 0, 'C');        // 34mm
$pdf->Cell(40, 4, '', 'LT', 0, 'C'); // 40mm
$pdf->Cell(63, 4, 'AMOUNT', 1, 0, 'C');       // 18+13+14+18 = 63mm
$pdf->Cell(13, 4, '', 'LT', 0, 'C');      // 13mm
$pdf->Cell(13, 4, '', 'LRT', 1, 'C');         // 13mm

// Row 2: Sub-headers under AMOUNT
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 5);
$pdf->SetXY(10, $Y);
$pdf->Cell(14, 4, 'Date', 1, 0, 'C');
$pdf->Cell(18, 4, 'No.', 1, 0, 'C');
$pdf->Cell(24, 4, 'LDDAP-ADA #', 'L', 0, 'C');
$pdf->Cell(14, 4, 'DV #', 'L', 0, 'C');
$pdf->Cell(24, 4, 'ORS/BURS #', 'L', 0, 'C');
$pdf->Cell(18, 4, 'CENTER CODE', 'L', 0, 'C');
$pdf->Cell(34, 4, 'PAYEE', 'L', 0, 'C');
$pdf->Cell(40, 4, 'NATURE OF PAYMENT', 'L', 0, 'C');
$pdf->Cell(18, 4, 'Gross', 1, 0, 'C');
$pdf->Cell(13, 4, 'Philhealth', 1, 0, 'C');
$pdf->Cell(14, 4, 'Tax', 1, 0, 'C');
$pdf->Cell(18, 4, 'Net', 1, 0, 'C');
$pdf->Cell(13, 4, 'JEV NO.', 'L', 0, 'C');
$pdf->Cell(13, 4, 'DATE', 'LR', 1, 'C');

$Y = $pdf->GetY();

/*
|--------------------------------------------------------------------------
| DATA ROWS (Using YOUR exact coding style - two-pass approach)
|--------------------------------------------------------------------------
*/
$pdf->SetFont('Arial', '', 6);
$rowCounter = 0;

foreach ($data as $row) {
    $startY = $pdf->GetY();
    $formattedDate = date('d-M-Y', strtotime($row['lddapada_date']));
    
    // FIRST PASS: Calculate row height based on payee and nature (MULTICELL WITHOUT BORDER)
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'] + $w['resp'], $startY);
    $pdf->MultiCell($w['payee'], 4, $row['payee_name'], 0, 'L');
    $h1 = $pdf->GetY();
    
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'] + $w['resp'] + $w['payee'], $startY);
    $pdf->MultiCell($w['nature'], 4, $row['particulars'], 0, 'L');
    $h2 = $pdf->GetY();
    
    // Get the maximum height needed
    $endY = max($h1, $h2);
    $rowHeight = $endY - $startY;
    if ($rowHeight < 8) {
        $rowHeight = 8;
        $endY = $startY + $rowHeight;
    }
    
    // SECOND PASS: DRAW ALL CELL BORDERS with the calculated row height
    $pdf->SetXY(10, $startY);
    $pdf->Cell($w['date'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['check'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['lddap'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['dv'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['ors'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['resp'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['payee'], $rowHeight, '', 1, 0, 'L');
    $pdf->Cell($w['nature'], $rowHeight, '', 1, 0, 'L');
    $pdf->Cell($w['gross'], $rowHeight, '', 1, 0, 'R');
    $pdf->Cell($w['phil'], $rowHeight, '', 1, 0, 'R');
    $pdf->Cell($w['tax'], $rowHeight, '', 1, 0, 'R');
    $pdf->Cell($w['net'], $rowHeight, '', 1, 0, 'R');
    $pdf->Cell($w['jev'], $rowHeight, '', 1, 0, 'C');
    $pdf->Cell($w['date2'], $rowHeight, '', 1, 1, 'C');
    
    // THIRD PASS: DRAW TEXT (using MultiCell for payee and nature to handle wrapping)
    
    // Payee column - MultiCell with text (border 0)
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'] + $w['resp'], $startY);
    $pdf->MultiCell($w['payee'], 4, '', 0, 'L');
    
    // Nature column - MultiCell with text (border 0)
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'] + $w['resp'] + $w['payee'], $startY);
    $pdf->MultiCell($w['nature'], 4, '', 0, 'L');
    
    // For single-line columns, use vertical centering
    $middleY = $startY + ($rowHeight / 2) - 2.5;
    
    $pdf->SetXY(10, $middleY);
    $pdf->Cell($w['date'], 5, $formattedDate, 0, 0, 'C');
    
    $pdf->SetXY(10 + $w['date'], $middleY);
    $pdf->Cell($w['check'], 5, $row['ckno'], 0, 0, 'C');
    
    $pdf->SetXY(10 + $w['date'] + $w['check'], $middleY);
    $pdf->Cell($w['lddap'], 5, $row['lddapadano'], 0, 0, 'C');
    
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'], $middleY);
    $pdf->Cell($w['dv'], 5, $row['dvno'], 0, 0, 'C');
    
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'], $middleY);
    $pdf->Cell($w['ors'], 5, $row['serialno'], 0, 0, 'C');
    
    $pdf->SetXY(10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'], $middleY);
    $pdf->Cell($w['resp'], 5, $row['responsibility_code'], 0, 0, 'C');
    
    // Amount columns (vertically centered)
    $amountX = 10 + $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'] + $w['resp'] + $w['payee'] + $w['nature'];
    
    $pdf->SetXY($amountX, $middleY);
    $pdf->Cell($w['gross'], 5, number_format($row['gross_amount'], 2), 0, 0, 'R');
    
    $pdf->SetXY($amountX + $w['gross'], $middleY);
    $pdf->Cell($w['phil'], 5, number_format($row['philhealth_amount'], 2), 0, 0, 'R');
    
    $pdf->SetXY($amountX + $w['gross'] + $w['phil'], $middleY);
    $pdf->Cell($w['tax'], 5, number_format($row['tax_amount'], 2), 0, 0, 'R');
    
    $pdf->SetXY($amountX + $w['gross'] + $w['phil'] + $w['tax'], $middleY);
    $pdf->Cell($w['net'], 5, number_format($row['net_amount'], 2), 0, 0, 'R');
    
    // JEV columns
    $jevX = $amountX + $w['gross'] + $w['phil'] + $w['tax'] + $w['net'];
    
    $pdf->SetXY($jevX, $middleY);
    $pdf->Cell($w['jev'], 5, '', 0, 0, 'C');
    
    $pdf->SetXY($jevX + $w['jev'], $middleY);
    $pdf->Cell($w['date2'], 5, '', 0, 0, 'C');
    
    // MOVE CURSOR TO NEXT ROW
    $pdf->SetY($endY);
    $rowCounter++;
}

/*
|--------------------------------------------------------------------------
| SUBTOTAL ROW
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| SUBTOTAL ROW
|--------------------------------------------------------------------------
*/
$Y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $Y);

$subtotalWidth = $w['date'] + $w['check'] + $w['lddap'] + $w['dv'] + $w['ors'] + $w['resp'] + $w['payee'] + $w['nature'];
$pdf->Cell($subtotalWidth, 5, 'SUBTOTAL', 1, 0, 'C');
$pdf->Cell($w['gross'], 5, number_format($totalGross, 2), 1, 0, 'R');
$pdf->Cell($w['phil'], 5, number_format($totalPhilhealth, 2), 1, 0, 'R');
$pdf->Cell($w['tax'], 5, number_format($totalTax, 2), 1, 0, 'R');
$pdf->Cell($w['net'], 5, number_format($totalNet, 2), 1, 0, 'R');
$pdf->Cell($w['jev'], 5, '', 1, 0, 'C');
$pdf->Cell($w['date2'], 5, '', 1, 1, 'C');

$Y = $pdf->GetY();

/*
|--------------------------------------------------------------------------
| GRAND TOTAL (Directly below SUBTOTAL values)
|--------------------------------------------------------------------------
*/
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY($subtotalWidth -25, $Y);
$pdf->Cell(35, 5, 'Grand Total', 0, 0, 'R');
$pdf->Cell($w['gross'], 5, number_format($totalGross, 2), 0, 0, 'R');
$pdf->Cell($w['phil'], 5, number_format($totalPhilhealth, 2), 0, 0, 'R');
$pdf->Cell($w['tax'], 5, number_format($totalTax, 2), 0, 0, 'R');
$pdf->Cell($w['net'], 5, number_format($totalNet, 2), 0, 1, 'R');

$Y = $pdf->GetY();
$pdf->Ln(8);

/*
|--------------------------------------------------------------------------
| CERTIFICATION STATEMENT
|--------------------------------------------------------------------------
*/
$pdf->SetFont('Arial', '', 8);
$sheetCount = ceil($rowCounter / 20) > 0 ? ceil($rowCounter / 20) : 1;
$certificationText = 'I hereby certify on my official oath that this Report of Checks Issued in ' . $sheetCount . ' sheet(s) is a full, true and correct statement of all checks issued by me during the period stated above for which Check Nos. ' . $firstCheck . ' to ' . $lastCheck . ' inclusive, were actually issued by me in payment for obligations shown in the attached disbursement vouchers/payroll.';

$pdf->MultiCell(0, 5, $certificationText, 0, 'L');

$pdf->Ln(20);

/*
|--------------------------------------------------------------------------
| SIGNATURE SECTION
|--------------------------------------------------------------------------
*/
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(70, 6, '_____________________________', 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(70, 5, 'JOVY S. MEDINA', 0, 1, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(70, 5, 'Administrative Officer V', 0, 1, 'L');

// Output PDF
$pdf->Output('RCI_Report_' . $reportno . '.pdf', 'I');
exit;
?>