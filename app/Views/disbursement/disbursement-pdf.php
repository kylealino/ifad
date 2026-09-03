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

function numberToWords($number) {

    $hyphen      = '-';
    $conjunction = ' ';
    $separator   = ' ';
    $negative    = 'negative ';
    $decimal     = ' & ';

    $dictionary  = [
        0                   => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Forty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        1000000             => 'Million',
        1000000000          => 'Billion'
    ];

    if (!is_numeric($number)) {
        return false;
    }

    if ($number < 0) {
        return $negative . numberToWords(abs($number));
    }

    $string = '';
    $fraction = null;

    if (strpos((string)$number, '.') !== false) {
        list($number, $fraction) = explode('.', (string)$number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;

        case $number < 100:
            $tens   = ((int)($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= " " . $dictionary[$units];
            }
            break;

        case $number < 1000:
            $hundreds  = floor($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' Hundred';
            if ($remainder) {
                $string .= $conjunction . numberToWords($remainder);
            }
            break;

        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = floor($number / $baseUnit);
            $remainder = $number % $baseUnit;

            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];

            if ($remainder) {
                $string .= $separator . numberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction) {
        $fraction = substr($fraction, 0, 2);
        $string .= $decimal . str_pad($fraction, 2, '0') . '/100';
    }

    return $string;
}


$query = $this->db->query("
SELECT
    `recid`,
    `serialno`,
    `particulars`,
    `funding_source`,
    `payee_name`,
    `payee_office`,
    `payee_address`,
    `certified_a`,
    `position_a`,
    `certified_c`,
    `position_c`,
    `is_pending`,
    `is_approved_certa`,
    `is_disapproved_certa`,
    `is_approved_certb`,
    `is_disapproved_certb`,
    `certa_remarks`,
    `certb_remarks`,
    `certa_approver`,
    `certb_approver`,
    `added_at`,
    `added_by`,
    `disbursement_date`,
    `dvno`,
    `fund_cluster_code`,
    `is_vatable`,
    `vat_percent`,
    `ewt_percent`,
    `pt_percent`,
    `category`
FROM
    `tbl_disbursement_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$serialno = $data['serialno'];
$particulars = $data['particulars'];
$funding_source = $data['funding_source'];
$payee_name = $data['payee_name'];
$payee_office = $data['payee_office'];
$payee_address = $data['payee_address'];

$certified_a = $data['certified_a'];
$position_a = $data['position_a'];
$certified_c = $data['certified_c'];
$position_c = $data['position_c'];

$is_pending = $data['is_pending'];
$is_approved_certa = $data['is_approved_certa'];
$is_disapproved_certa = $data['is_disapproved_certa'];
$is_approved_certb = $data['is_approved_certb'];
$is_disapproved_certb = $data['is_disapproved_certb'];

$certa_remarks = $data['certa_remarks'];
$certb_remarks = $data['certb_remarks'];

$certa_approver = $data['certa_approver'];
$certb_approver = $data['certb_approver'];

$added_at = $data['added_at'];
$added_by = $data['added_by'];

$disbursement_date = $data['disbursement_date'];
$dvno = $data['dvno'];
$fund_cluster_code = $data['fund_cluster_code'];

$is_vatable = $data['is_vatable'];
$vat_percent = $data['vat_percent'];
$ewt_percent = $data['ewt_percent'];
$pt_percent = $data['pt_percent'];
$category = $data['category'];

//certify a division
$query = $this->db->query("
SELECT
    `position`
FROM
    `myua_user`
WHERE 
    `full_name` = '$certified_a'"
);
$data = $query->getRowArray();
$position_a = $data['position'];

$query = $this->db->query("
SELECT
    `payee_tin`
FROM
    `tbl_payee`
WHERE 
    `payee_name` = '$payee_name'"
);
$data = $query->getRowArray();
$payee_tin = $data['payee_tin'];


$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('DV Print');
$pdf->SetFont('Times', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;

// Draw the cell first
$pdf->SetXY(10, $Y);
$pdf->Cell(150, 12, '' , 'TRL', 1, 'L');

// Insert image INSIDE the cell
$imagePath = FCPATH . 'assets/images/logos/fnri-header2.png';
if(file_exists($imagePath)) {
    $pdf->Image(
        $imagePath,
        12,        // X (10 + padding)
        $Y + 3.5,    // Y inside the cell
        147        // width
    );
}

$pdf->SetFont('Times', '', 8);
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, 'FAD-AS-005' , 'TRL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, 'Revision 4' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, '24 Oct 2025' , 'BRL', 1, 'L');

$Y = $pdf->GetY();
$pdf->Cell(150, 12, '' , 'RLB', 1, 'L');

$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, 'Fund Cluster:' , 'TRL', 1, 'L');
$pdf->SetFont('Times', '', 11);
$Y = $pdf->GetY();
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, $fund_cluster_code , 'RL', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, '' , 'BRL', 1, 'L');

$pdf->SetFont('Times', 'B', 13);
$Y = $pdf->GetY();
$pdf->Cell(150, 12, 'DISBURSEMENT VOUCHER' , 1, 1, 'C');

$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(160, $Y);
$pdf->Cell(10, 4, 'Date: ' , 'TL', 1, 'L');
$pdf->SetXY(170, $Y);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(30, 4, $disbursement_date , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(160, $Y);
$pdf->Cell(10, 4, 'DV.No.: ' , 'L', 1, 'L');
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, '' , 'R', 1, 'L');
$pdf->SetFont('Times', 'B', 11);
$Y = $pdf->GetY();
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, $dvno , 'BRL', 1, 'C');

$pdf->SetFont('Times', 'B', 8);

$Y = $pdf->GetY();

// LEFT (Mode of / Payment)
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 6, 'Mode of', 'TRL', 2, 'C');

$pdf->SetX(10);
$pdf->Cell(15, 6, 'Payment', 'BRL', 0, 'C');
$pdf->SetFont('Times', '', 8);
// RIGHT CELL (container)
$pdf->SetXY(25, $Y);
$pdf->Cell(175, 12, '', 1, 0); // empty container

$startX = 27;
$startY = $Y + 4;

$pdf->SetXY($startX, $startY);

// MDS Check
$pdf->Cell(4, 4, '', 1);
$pdf->Cell(30, 4, ' MDS Check', 0);

// Commercial Check
$pdf->Cell(4, 4, '', 1);
$pdf->Cell(45, 4, ' Commercial Check', 0);

// ADA
$pdf->Cell(4, 4, '', 1);
$pdf->Cell(20, 4, ' ADA', 0);

// Others checkbox
$pdf->Cell(4, 4, '', 1);

// TEXT
$pdf->Cell(35, 4, ' Others (Please specify)', 0);

// LINE AFTER TEXT
$pdf->Cell(25, 4, '', 'B',1); // underline (blank)

$Y = $pdf->GetY()+4;
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3, '' , 'LR', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(85, 3, '' , 0, 1, 'L');

$pdf->SetXY(110, $Y);
$pdf->Cell(50, 3, 'TIN/Employee No.:' , 'L', 1, 'L');

$pdf->SetXY(160, $Y);
$pdf->Cell(40, 3, 'ORS/BURS No.:' , 'LR', 1, 'L');

$Y = $pdf->GetY();
$cellHeight = 7;

// Left border
$pdf->Line(10, $Y, 10, $Y + $cellHeight);

// Right border
$pdf->Line(25, $Y, 25, $Y + $cellHeight);

$pdf->Line(110, $Y, 110, $Y + $cellHeight);

// Right border
$pdf->Line(160, $Y, 160, $Y + $cellHeight);

// Label at the top
$pdf->SetXY(10, $Y + 1);
$pdf->SetFont('Times', 'B', 8);
$pdf->Cell(15, 3, 'Payee', 0, 0, 'C');
$pdf->SetFont('Times', '', 8);

$pdf->SetXY(25, $Y+1);
$pdf->SetFont('Times', 'B', 8);
$pdf->MultiCell(85, 3, $payee_name, 0, 'L');

$pdf->SetFont('Times', '', 8);
$pdf->SetXY(110, $Y);
$pdf->Cell(50, 7, $payee_tin , 'L', 1, 'L');

$pdf->SetXY(160, $Y);
$pdf->Cell(40, 7, $serialno , 'LR', 1, 'L');

$Y = $pdf->GetY()+0.3;
$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 6, 'Address' , 1, 1, 'C');
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(25, $Y);
$pdf->Cell(175, 6, $payee_address , 1, 1, 'L');

//PARTICULARS HEADER----------------------------------------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 6, 'Particulars' , 1, 1, 'C');

$pdf->SetXY(100, $Y);
$pdf->Cell(30, 6, 'Responsibility' , 1, 1, 'C');

$pdf->SetXY(130, $Y);
$pdf->Cell(30, 6, 'MFO/PAP' , 1, 1, 'C');

$pdf->SetXY(160, $Y);
$pdf->Cell(40, 6, 'Amount' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 66, '' , 'TRL', 1, 'C');

$pdf->SetXY(100, $Y);
$pdf->Cell(30, 66, '' , 'TRL', 1, 'C');

$pdf->SetXY(130, $Y);
$pdf->Cell(30, 66, '' , 'TRL', 1, 'C');

$pdf->SetXY(160, $Y);
$pdf->Cell(40, 66, '' , 'TRL', 1, 'C');

$pdf->SetXY(10, $Y + 20); // Move it lower (adjust 15 to your needs)
$pdf->MultiCell(90, 6, $particulars, 0, 'C'); // Reduce cell height from 20 to 6

$query = $this->db->query("
    SELECT 
        responsibility_code,
        mfopaps_code,
        SUM(amount) AS amount
    FROM (
        SELECT responsibility_code, mfopaps_code, amount
        FROM tbl_disbursement_direct_ps_dt WHERE project_id = ?

        UNION ALL

        SELECT responsibility_code, mfopaps_code, amount
        FROM tbl_disbursement_direct_mooe_dt WHERE project_id = ?

        UNION ALL

        SELECT responsibility_code, mfopaps_code, amount
        FROM tbl_disbursement_direct_co_dt WHERE project_id = ?

        UNION ALL

        SELECT responsibility_code, mfopaps_code, amount
        FROM tbl_disbursement_indirect_ps_dt WHERE project_id = ?

        UNION ALL

        SELECT responsibility_code, mfopaps_code, amount
        FROM tbl_disbursement_indirect_mooe_dt WHERE project_id = ?

        UNION ALL

        SELECT responsibility_code, mfopaps_code, amount
        FROM tbl_disbursement_indirect_co_dt WHERE project_id = ?
    ) AS combined
    LIMIT 1
", [
    $recid,
    $recid,
    $recid,
    $recid,
    $recid,
    $recid
]);

$result = $query->getResultArray();
$total_amount = 0;

foreach ($result as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $amount = $row['amount'];

    $pdf->SetFont('Times', '', 8);
    $total_amount += $amount;

    $startY = $Y;

    // Responsibility
    $pdf->SetXY(100, $startY+1);
    $pdf->MultiCell(30, 4, $responsibility_code, 'LR', 'C');
    $endY = $pdf->GetY();

    // Draw borders for the remaining columns
    $height = $endY - $startY;


    // Print text aligned to the first row
    $pdf->SetXY(130, $startY);
    $pdf->Cell(30, 6, $mfopaps_code, 0, 0, 'C');

    $pdf->SetXY(160, $startY);
    $pdf->Cell(40, 6, number_format($amount,2), 0, 0, 'R');

    $Y = $endY;
}
$vat_amount = 0;
$ewt_amount = 0;
$pt_amount  = 0;

$gross = $total_amount;

// ==============================
// DETERMINE BASE AMOUNT
// ==============================

if ($is_vatable == 1) {
    $base_amount = $gross / 1.12;
} else {
    $base_amount = $gross;
}

// ==============================
// CATEGORY LOGIC
// ==============================

if (empty($category)) {

    // ✅ NO CATEGORY
    $net = $gross;

} elseif ($category == "Manual") {

    // ✅ MANUAL (use input values only)
    if ($is_vatable == 1) {

        $vat_amount = $base_amount * ($vat_percent / 100);
        $ewt_amount = $base_amount * ($ewt_percent / 100);

        $net = $gross - $vat_amount - $ewt_amount;

    } else {

        $ewt_amount = $base_amount * ($ewt_percent / 100);
        $pt_amount  = $base_amount * ($pt_percent / 100);

        $net = $gross - $ewt_amount - $pt_amount;
    }

} else {

    // ✅ GOODS / SERVICES (AUTO DEFAULTS)

    if ($is_vatable == 1) {

        // VAT
        $vat_amount = $base_amount * ($vat_percent / 100);

        // EWT
        if ($category == "Goods") {
            $ewt_percent = 1;
        } elseif ($category == "Services") {
            $ewt_percent = 2;
        }

        $ewt_amount = $base_amount * ($ewt_percent / 100);

        $net = $gross - $vat_amount - $ewt_amount;

    } else {

        // NON-VAT
        if ($category == "Goods") {
            $ewt_percent = 1;
        } elseif ($category == "Services") {
            $ewt_percent = 2;
        }

        $pt_percent = 3;

        $ewt_amount = $base_amount * ($ewt_percent / 100);
        $pt_amount  = $base_amount * ($pt_percent / 100);

        $net = $gross - $ewt_amount - $pt_amount;
    }
}

if ($is_vatable == 1) {

    // VAT
    $Y = 120;
    $pdf->SetXY(10, $Y);
    $pdf->Cell(75, 4, 'LESS '. $vat_percent .'% VAT', 0, 1, 'R');

    $pdf->SetXY(85, $Y);
    $pdf->Cell(15, 4, number_format($vat_amount,2), 0, 1, 'R');

    // EWT
    $Y = 124;
    $pdf->SetXY(10, $Y);
    $pdf->Cell(75, 4, $ewt_percent .'% EWT', 0, 1, 'R');

    $pdf->SetXY(85, $Y);
    $pdf->Cell(15, 4, number_format($ewt_amount,2), 0, 1, 'R');

    $pdf->SetXY(160, $Y);
    $pdf->Cell(40, 4, number_format($vat_amount + $ewt_amount,2), 'B', 1, 'R');
}else {

    // EWT
    $Y = 120;
    $pdf->SetXY(10, $Y);
    $pdf->Cell(75, 4, 'LESS ' . $ewt_percent .'% EWT', 0, 1, 'R');

    $pdf->SetXY(85, $Y);
    $pdf->Cell(15, 4, number_format($ewt_amount,2), 0, 1, 'L');

    // PT
    $Y = 124;
    $pdf->SetXY(10, $Y);
    $pdf->Cell(75, 4, $pt_percent .'% PT', 0, 1, 'R');

    $pdf->SetXY(85, $Y);
    $pdf->Cell(15, 4, number_format($pt_amount,2), 0, 1, 'L');

    $pdf->SetXY(160, $Y);
    $pdf->Cell(40, 4, number_format($ewt_amount + $pt_amount,2), 'B', 1, 'R');
}

$Y = 128;
$pdf->SetXY(10, $Y);
$pdf->Cell(75, 4, 'NET', 0, 1, 'R');

$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, number_format($net,2), 0, 1, 'R');


$Y = 144;

$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 8);
$pdf->Cell(90, 6, 'Amount Due' , 'L', 1, 'C');

$pdf->SetXY(100, $Y);
$pdf->Cell(30, 6, '' , 'L', 1, 'C');

$pdf->SetXY(130, $Y);
$pdf->Cell(30, 6, '' , 'L', 1, 'C');

$pdf->SetXY(160, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(40, 6, number_format($net,2) , 'LTR', 1, 'R');
$pdf->SetFont('Times', '', 8);
//CERTIFY BOX A --------------------------------------------
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 8);
$pdf->Cell(4, 4, 'A.' , 1, 1, 'C');
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(14, $Y);
$pdf->Cell(186, 4, 'Certified: Expenses/Cash Advance necessary, lawful and incurred under my direct supervision.' , 'RT', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(40, 8, '' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(110, 8, '' , 0, 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 8, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Times', 'B', 10);
$pdf->SetXY(10, $Y);
$pdf->Cell(40, 4, '' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(110, 4, $certified_a , 'B', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(40, 4, '' , 'L', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(110, 4, $position_a , 0, 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, '' , 'R', 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(40, 4, '' , 'BL', 1, 'C');
$pdf->SetXY(50, $Y);
$pdf->Cell(110, 4, 'Printed Name, Designation and Signature of Supervisor' , 'B', 1, 'C');
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 4, '' , 'RB', 1, 'C');

//BOX B-----------------------------------------------
$Y = $pdf->GetY();
$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(4, 4, 'B.' , 1, 1, 'C');
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(14, $Y);
$pdf->Cell(186, 4, 'Accounting Entry:' , 'R', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(91, 4, 'Account Title' , 1, 1, 'C');

$pdf->SetXY(101, $Y);
$pdf->Cell(33, 4, 'UACS Code' , 1, 1, 'C');

$pdf->SetXY(134, $Y);
$pdf->Cell(33, 4, 'Debit' , 1, 1, 'C');

$pdf->SetXY(167, $Y);
$pdf->Cell(33, 4, 'Credit' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(91, 6, '' , 1, 1, 'C');

$pdf->SetXY(101, $Y);
$pdf->Cell(33, 6, '' , 1, 1, 'C');

$pdf->SetXY(134, $Y);
$pdf->Cell(33, 6, '' , 1, 1, 'C');

$pdf->SetXY(167, $Y);
$pdf->Cell(33, 6, '' , 1, 1, 'C');

//BOX C ----------------------------------

// ================= BOX C & D =================

$pdf->SetFont('Times', '', 8);
$Y = $pdf->GetY();

// ---- LEFT SIDE (C) ----
$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(4, 4, 'C.', 1, 0, 'C');

$pdf->SetXY(14, $Y);
$pdf->Cell(87, 4, 'Certified:', 1, 0, 'L');

// ---- RIGHT SIDE (D) ----
$pdf->SetXY(101, $Y);
$pdf->Cell(4, 4, 'D.', 1, 0, 'C');

$pdf->SetXY(105, $Y);
$pdf->Cell(95, 4, 'Approved for Payment', 1, 1, 'L');
$pdf->SetFont('Times', '', 8);

// ================= CONTENT ROW =================
$startY = $pdf->GetY();

// LEFT BOX (C content)
$pdf->SetXY(10, $startY);
$pdf->Cell(91, 20, '', 1); // container

// RIGHT BOX (D content)
$pdf->SetXY(101, $startY);
$pdf->Cell(99, 20, '', 1);


// ================= CHECKBOXES (C) =================
$x = 12;
$y = $startY + 3;

// 1st checkbox
$pdf->SetXY($x, $y);
$pdf->Cell(4, 4, '', 1);
$pdf->Cell(70, 4, ' Cash available', 0, 1);

// 2nd checkbox
$pdf->SetXY($x, $y + 6);
$pdf->Cell(4, 4, '', 1);
$pdf->Cell(70, 4, ' Subject to Authority to Debit Account (when applicable)', 0, 1);

// 3rd checkbox
$pdf->SetXY($x, $y + 12);
$pdf->Cell(4, 4, '', 1);
$pdf->MultiCell(80, 4, ' Supporting documents complete and amount claimed proper', 0, 'L');


// ================= AMOUNT IN WORDS (D) =================

// Example value (replace with your dynamic value)
$pdf->SetFont('Times', '', 10);
$amountWords = numberToWords(number_format($net, 2, '.', '')) . " Pesos";

$pdf->SetXY(105, $startY + 5);
$pdf->MultiCell(90, 5, $amountWords, 0, 'C');


//SIGNATURES -----------------------------------------------
$pdf->SetFont('Times', '', 8);
$Y = 212;

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 8, 'Signature', 1, 0, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(76, 8, '', 1, 0, 'L');

$pdf->SetXY(101, $Y);
$pdf->Cell(15, 8, 'Signature', 1, 0, 'C');

$pdf->SetXY(116, $Y);
$pdf->Cell(84, 8, '', 1, 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 4, 'Printed', 'LR', 0, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(76, 4, '', 0, 0, 'L');

$pdf->SetXY(101, $Y);
$pdf->Cell(15, 4, 'Printed', 'LR', 0, 'C');

$pdf->SetXY(116, $Y);
$pdf->Cell(84, 4, '', 'R', 1, 'L');

$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 4, 'Name', 'BLR', 0, 'C');

$pdf->SetXY(25, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(76, 4, $certified_c, 0, 0, 'C');

$pdf->SetXY(101, $Y);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(15, 4, 'Name', 'BRL', 0, 'C');

$pdf->SetXY(116, $Y);
$pdf->SetFont('Times', 'B',10);
$pdf->Cell(84, 4, 'Atty. LUCIEDEN G. RAZ', 'R', 1, 'C');

// ================= POSITION =================
$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 8);

// LEFT
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 8, 'Position', 'BLR', 0, 'C');

$pdf->SetXY(25, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(76, 4, $position_c, 1, 0, 'C');

// RIGHT
$pdf->SetXY(101, $Y);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(15, 8, 'Position', 1, 0, 'C');

$pdf->SetXY(116, $Y);
$pdf->Cell(84, 4, 'Director III /OIC - Officer of the Director', 1, 1, 'C');



// ================= DESIGNATION =================
$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 4, '', 'BL', 0);

$pdf->SetXY(25, $Y);
$pdf->Cell(76, 4, 'Head, Accounting Unit/Authorized Representative', 'B', 0, 'C');

$pdf->SetXY(101, $Y);
$pdf->Cell(15, 4, '', 'BR', 0);

$pdf->SetXY(116, $Y);
$pdf->Cell(84, 4, 'Agency Head/Authorized Representative', 'BR', 1, 'C');


// ================= DATE =================
$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, 'Date', 'LR', 0, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(76, 5, '', 1, 0, 'C');

$pdf->SetXY(101, $Y);
$pdf->Cell(15, 5, 'Date', 1, 0, 'C');

$pdf->SetXY(116, $Y);
$pdf->Cell(84, 5, '', 1, 1, 'C');


// ================= BOX E =================
$Y = $pdf->GetY();
$pdf->SetFont('Times', 'B', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(4, 4, 'E.', 1, 0, 'C');

$pdf->SetXY(14, $Y);
$pdf->Cell(145, 4, 'Receipt of Payment', 1, 1, 'L');
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(159, $Y);
$pdf->Cell(41, 4, 'JEV No.', 'LR', 1, 'L');


// ================= ROW 1 =================
$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 4, 'Check/', 'LR', 0, 'L');

$pdf->SetXY(25, $Y);
$pdf->Cell(45, 4, '', 0, 0, 'C');

$pdf->SetXY(70, $Y);
$pdf->Cell(31, 4, 'Date:', 'L', 0, 'L');

$pdf->SetXY(101, $Y);
$pdf->Cell(58, 4, 'Bank Name & Account Number:', 'L', 0, 'L');


$pdf->SetXY(159, $Y);
$pdf->Cell(41, 4, '', 'LR', 1, 'L');

// ================= ROW 2 =================
$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 4, 'ADA No.:', 'LR', 0, 'L');

$pdf->SetXY(25, $Y);
$pdf->Cell(45, 4, '', 0, 0, 'C');

$pdf->SetXY(70, $Y);
$pdf->Cell(31, 4, '', 'L', 0, 'L');

$pdf->SetXY(101, $Y);
$pdf->Cell(58, 4, '', 'L', 0, 'C');

$pdf->SetXY(159, $Y);
$pdf->Cell(41, 4, '', 'LR', 1, 'L');


// ================= ROW 3 =================
$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(15, 6, 'Signature:', 1, 0, 'L');

$pdf->SetXY(25, $Y);
$pdf->Cell(45, 6, '', 1, 0, 'C');

$pdf->SetXY(70, $Y);
$pdf->Cell(31, 6, 'Date:', 1, 0, 'L');

$pdf->SetXY(101, $Y);
$pdf->Cell(58, 6, 'Printed Name:', 1, 0, 'L');

$pdf->SetXY(159, $Y);
$pdf->Cell(41, 6, 'Date:', 'LR', 1, 'L');


// ================= FINAL =================
$Y = $pdf->GetY();

$pdf->SetXY(10, $Y);
$pdf->Cell(149, 5, 'Official Receipt No. & Date/Other Documents', 1, 1, 'C');

$pdf->SetXY(159, $Y);
$pdf->Cell(41, 5, '', 'LBR', 1, 'L');

$Y = 268.5;

$pdf->SetFont('Times', '', 8);
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
    $pdf->SetFont('Times', $part[1], 8);
    $totalWidth += $pdf->GetStringWidth($part[0]);
}

// Center the combined text in 190mm width
$startX = 10 + (190 - $totalWidth) / 2;
$pdf->SetX($startX);

// Print parts
foreach ($text1_parts as $part) {
    $pdf->SetFont('Times', $part[1], 8);
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
    $pdf->SetFont('Times', $part[1], 8);
    $totalWidth += $pdf->GetStringWidth($part[0]);
}

// Center the combined text in 190mm width
$startX = 10 + (190 - $totalWidth) / 2;
$pdf->SetX($startX);

// Print parts
foreach ($text2_parts as $part) {
    $pdf->SetFont('Times', $part[1], 8);
    $pdf->Cell($pdf->GetStringWidth($part[0]), 4, $part[0], 0, 0);
}

$pdf->Output();
exit;
?>