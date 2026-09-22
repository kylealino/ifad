<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$dvno = $this->request->getPostGet('dvno');
$month = $this->request->getPostGet('month');
$year = $this->request->getPostGet('year');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));
$total_payroll = 0;

// --- NUMBER TO WORDS CONVERTER FUNCTION ---
function convertNumberToWords($number) {
    $number = (float) $number;
    $whole  = (int) floor($number);
    $cents  = (int) round(($number - $whole) * 100);

    $words = trim(convertWholeNumber($whole));
    if ($words === '') {
        $words = 'ZERO';
    }

    $result = $words . ' PESOS';

    if ($cents > 0) {
        $centWords = trim(convertWholeNumber($cents));
        $result .= ' AND ' . $centWords . ' CENTAVO' . ($cents == 1 ? '' : 'S');
    }

    return strtoupper($result);
}

function convertWholeNumber($n) {
    $ones = [
        0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR',
        5 => 'FIVE', 6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE',
        10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN',
        14 => 'FOURTEEN', 15 => 'FIFTEEN', 16 => 'SIXTEEN',
        17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN'
    ];
    $tens = [
        2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY',
        6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY'
    ];

    if ($n < 20) {
        return $ones[$n];
    } elseif ($n < 100) {
        $t = intdiv($n, 10);
        $o = $n % 10;
        return $tens[$t] . ($o ? ' ' . $ones[$o] : '');
    } elseif ($n < 1000) {
        $h = intdiv($n, 100);
        $r = $n % 100;
        return $ones[$h] . ' HUNDRED' . ($r ? ' ' . convertWholeNumber($r) : '');
    } elseif ($n < 1000000) {
        $th = intdiv($n, 1000);
        $r  = $n % 1000;
        return convertWholeNumber($th) . ' THOUSAND' . ($r ? ' ' . convertWholeNumber($r) : '');
    } elseif ($n < 1000000000) {
        $m = intdiv($n, 1000000);
        $r = $n % 1000000;
        return convertWholeNumber($m) . ' MILLION' . ($r ? ' ' . convertWholeNumber($r) : '');
    } elseif ($n < 1000000000000) {
        $b = intdiv($n, 1000000000);
        $r = $n % 1000000000;
        return convertWholeNumber($b) . ' BILLION' . ($r ? ' ' . convertWholeNumber($r) : '');
    }
    return '';
}

$query = $this->db->query("
    SELECT
        `recid`,
        `adano`,
        `dvno`,
        `particulars`,
        `payee_name`,
        `payee_accountno`,
        `ada_date`,
        `net_amount`,
        `certified_a`,
        `position_a`,
        `certified_b`,
        `position_b`
    FROM
        `tbl_ada`
    WHERE 
        `dvno` = '$dvno'
");

$data = $query->getRowArray();

// Assign variables based ONLY on the tbl_ada columns
$recid           = $data['recid'];
$adano           = $data['adano'];
$dvno            = $data['dvno'];
$particulars     = $data['particulars'];
$payee_name      = $data['payee_name'];
$payee_accountno = $data['payee_accountno'];
$ada_date        = date("F j, Y", strtotime($data['ada_date'])); 
$net_amount      = $data['net_amount'];
$certified_a     = $data['certified_a'];
$position_a      = $data['position_a'];
$certified_b     = $data['certified_b'];
$position_b      = $data['position_b'];

// --- CONVERT NET AMOUNT TO WORDS AND FORMAT CURRENCY ---
$amount_in_words = convertNumberToWords($net_amount);
$amount_formatted = number_format($net_amount, 2);

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('ADA Print');
$pdf->SetFont('Times', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;

// Insert image INSIDE the cell
$imagePath = FCPATH . 'assets/images/logos/fnri-header2.png';
if(file_exists($imagePath)) {
    $pdf->Image(
        $imagePath,
        12,        // X (10 + padding)
        $Y,    // Y inside the cell
        185        // width
    );
}

$Y = 35;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 5, $ada_date , 0, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetXY(160, $Y);
$pdf->Cell(40, 5, 'ADA ' . $adano , 0, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(10, 5, 'TO:', 0, 1, 'L');

$pdf->SetXY(20, $Y);
$pdf->Cell(40, 5, 'LANDBANK OF THE PHILIPPINES', 0, 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(10, 5, '', 0, 1, 'L');

$pdf->SetXY(20, $Y);
$pdf->Cell(40, 5, 'Bicutan DOST Branch', 0, 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetXY(10, $Y);
$pdf->Cell(20, 5, 'SUBJECT:', 0, 1, 'L');

$pdf->SetFont('Times', 'B', 11);
$pdf->SetXY(30, $Y);
$pdf->Cell(40, 5, 'AUTHORITY TO DEBIT/CREDIT ACCOUNT', 0, 1, 'L');
$pdf->SetFont('Times', '', 11);

$Y = $pdf->GetY()+10;

// --- DYNAMIC PARAGRAPH CONSTRUCTION ---
// Replaced hardcoded values with variables from your database ($amount_in_words, $amount_formatted, $payee_name, $payee_accountno)
$paragraph = "Please debit the FNRI Project Account with account number 1822-1032-40 in the amount of " 
           . strtoupper($amount_in_words) 
           . " (P" . $amount_formatted . "), inclusive of applicable bank charge and credit account names below representing " .$particulars . ", subject to deduction of applicable bank charges, if any.";

// MultiCell(width, height, text, border, align, fill)
// Setting width to $total_width makes it span across all 3 columns.
$pdf->SetXY(10, $Y);
$pdf->MultiCell(190, 6, $paragraph, 0, 'J'); 

$Y = $pdf->GetY()+5;
$pdf->SetFont('Times', 'B', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 5, 'Account Name', 1, 1, 'C');

$pdf->SetXY(100, $Y);
$pdf->Cell(50, 5, 'Account Number', 1, 1, 'C');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, 'Amount', 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
// $pdf->Cell(90, 5, $payee_name, 1, 1, 'L');
$pdf->MultiCell(90, 5, $payee_name, 1, 'L');

$pdf->SetXY(100, $Y);
$pdf->Cell(50, 5, $payee_accountno, 1, 1, 'C');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, 'P'.number_format($net_amount,2), 1, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 5, '', 1, 1, 'L');

$pdf->SetXY(100, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'C');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 5, '', 1, 1, 'L');

$pdf->SetXY(100, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'C');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 5, '', 1, 1, 'L');

$pdf->SetXY(100, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'C');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(90, 5, '', 1, 1, 'L');

$pdf->SetXY(100, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'C');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '', 1, 1, 'R');

$Y = $pdf->GetY();
$pdf->SetFont('Times', 'B', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(65, 5, '', 'TLB', 1, 'L');

$pdf->SetXY(75, $Y);
$pdf->Cell(75, 5, 'TOTAL', 'TRB', 1, 'L');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, 'P'.number_format($net_amount,2), 1, 1, 'R');

$Y = $pdf->GetY()+15;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(70, 5, '', 'B', 1, 'L');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, '', 0, 1, 'C');

$pdf->SetXY(130, $Y);
$pdf->Cell(70, 5, '', 'B', 1, 'R');

$certified_a_converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $certified_a);
$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(70, 5, $certified_a_converted, 0, 1, 'C');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, '', 0, 1, 'C');

$pdf->SetXY(130, $Y);
$pdf->Cell(70, 5, $certified_b, 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 9);
$pdf->SetXY(10, $Y);
$pdf->Cell(70, 5, $position_a, 0, 1, 'C');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, '', 0, 1, 'C');

$pdf->SetXY(130, $Y);
$pdf->Cell(70, 5, $position_b, 0, 1, 'C');

$Y = $pdf->GetY()+5;

$pdf->SetLineWidth(0.5);   // in mm — normal is 0.2
$pdf->Line(10, $pdf->GetY()+10, 200, $pdf->GetY()+10);
$pdf->SetLineWidth(0.2);   // reset back to normal

$Y = 190;
$pdf->SetFont('Times', 'B', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(70, 5, 'FOR BANK USE ONLY', 0, 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 5, 'Signature verified by:', 0, 1, 'L');

$pdf->SetXY(60, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, 'Checked by:', 0, 1, 'L');

$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, 'Approved by:', 0, 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 5, '', 'B', 1, 'L');

$pdf->SetXY(60, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, '', 'B', 1, 'L');

$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '', 'B', 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(70, 5, 'We have debited/credited accounts as instructed:', 0, 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 5, 'Posted by:', 0, 1, 'L');

$pdf->SetXY(60, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, 'Checked by:', 0, 1, 'L');

$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, 'Approved by:', 0, 1, 'L');

$Y = $pdf->GetY()+5;
$pdf->SetFont('Times', '', 11);
$pdf->SetXY(10, $Y);
$pdf->Cell(50, 5, '', 'B', 1, 'L');

$pdf->SetXY(60, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(80, $Y);
$pdf->Cell(50, 5, '', 'B', 1, 'L');

$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, '', 0, 1, 'L');

$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '', 'B', 1, 'L');

$Y = 267;
$pdf->SetFont('Times', 'BI', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, '"Fight Malnutrition with Accurate Data, Correct Information and Innovative Technologies"', 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, 'Tel. #: 8837-8113 to 13, 8837-1839, 8837-2071 to 82 loc. 2296', 0, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetFont('Times', '', 8);
$pdf->SetXY(10, $Y);
$pdf->Cell(190, 3, 'Fax #: (632) 8837-3164l (632) 8837-2934', 0, 1, 'C');


$pdf->Output();
exit;
?>