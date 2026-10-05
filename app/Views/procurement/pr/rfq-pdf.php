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
    `recid` = ?"
, [$recid]);

$data = $query->getRowArray();
if (empty($data)) {
    die('Purchase request not found.');
}
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
    `recid` = ?"
, [$rfq_recid]);

$data = $query->getRowArray();
if (empty($data)) {
    die('Request for quotation not found.');
}
$quotation_no = $data['quotation_no'];
$company_name = $data['company_name'];
$company_address = $data['company_address'];
$delivery_period = $data['delivery_period'];
$terms = $data['terms'];
$quotation_date = $data['quotation_date'];
// printed as "May 11, 2026"; a blank date stays blank
$quotation_date_text = (empty($quotation_date) || strpos($quotation_date, '0000') === 0) ? '' : date('F j, Y', strtotime($quotation_date));

// FPDF with UTF-8 text: names are stored in UTF-8 (ñ / Ñ are two bytes) but
// FPDF's core fonts read Windows-1252, so text is converted here once.
if (!class_exists('RfqPDF')) {
    class RfqPDF extends \FPDF {
        // Converts only valid UTF-8 with non-ASCII characters, so text that
        // is already Windows-1252 is left as is
        public static function toPdf($text) {
            $text = (string) $text;
            if (!preg_match('/[\x80-\xFF]/', $text) || !mb_check_encoding($text, 'UTF-8')) {
                return $text;
            }
            $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
            return $out === false ? $text : $out;
        }
        function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
            parent::Cell($w, $h, self::toPdf($txt), $border, $ln, $align, $fill, $link);
        }
        function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false) {
            parent::MultiCell($w, $h, self::toPdf($txt), $border, $align, $fill);
        }
        function GetStringWidth($s) {
            return parent::GetStringWidth(self::toPdf($s));
        }
        // Lines a MultiCell of width $w needs for $txt, so a row's height is
        // known before it is drawn (and it is never split across pages)
        function NbLines($w, $txt) {
            $cw = $this->CurrentFont['cw'];
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', self::toPdf($txt));
            $nb = strlen($s);
            if ($nb > 0 && $s[$nb - 1] == "\n") {
                $nb--;
            }
            $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
            while ($i < $nb) {
                $c = $s[$i];
                if ($c == "\n") {
                    $i++; $sep = -1; $j = $i; $l = 0; $nl++;
                    continue;
                }
                if ($c == ' ') {
                    $sep = $i;
                }
                $l += $cw[$c] ?? 0;
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j) {
                            $i++;
                        }
                    } else {
                        $i = $sep + 1;
                    }
                    $sep = -1; $j = $i; $l = 0; $nl++;
                } else {
                    $i++;
                }
            }
            return $nl;
        }
        function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Page number in center
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
        }
    }
}

$pdf = new RfqPDF('P', 'mm', 'A4');
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
$pdf->Cell(30, 5, $quotation_date_text , 'B', 1, 'L');

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


// Table columns: x position and width (10 to 200 mm across the page)
$cols = [
    'no'    => [10, 15],
    'abc'   => [25, 20],
    'desc'  => [45, 65],
    'offer' => [110, 35],
    'qty'   => [145, 10],
    'unit'  => [155, 15],
    'price' => [170, 15],
    'total' => [185, 15],
];

// One table line: $texts per column key, with the same $border on every cell
// (the last column also closes the table on the right)
$tableLine = function ($y, $h, array $texts, $border, $align = 'C') use ($pdf, $cols) {
    foreach ($cols as $key => [$x, $w]) {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $texts[$key] ?? '', $border . ($key === 'total' ? 'R' : ''), 0, $align);
    }
    $pdf->SetY($y + $h);
};

// Three-line column header, also repeated at the top of every new page
$tableHeader = function ($y) use ($pdf, $tableLine) {
    $pdf->SetFont('Arial', '', 8);
    $tableLine($y, 3.5, ['offer' => "BIDDER'S"], 'LT');
    $tableLine($pdf->GetY(), 3.5, ['no' => 'ITEM NO.', 'abc' => 'ABC', 'desc' => "ITEM & DESCRIPTION/END-USER'S", 'offer' => 'SPECIFICATIONS', 'qty' => 'QTY', 'unit' => 'UNIT', 'price' => 'UNIT', 'total' => 'TOTAL'], 'L');
    $tableLine($pdf->GetY(), 3.5, ['abc' => 'PER ITEM', 'desc' => 'SPECIFICATIONS', 'offer' => 'OFFER', 'price' => 'PRICE', 'total' => 'PRICE'], 'LB');
};

$tableHeader($pdf->GetY() + 5);

//ITEMS ------------------------------------------------------------------------
$pdf->SetLineWidth(0.2); // normal thickness
$pdf->SetFont('Arial', '', 9);
$query = $this->db->query("
SELECT
    `item_desc`,
    `unit`,
    `quantity`,
    `unit_cost`,
    `total_cost`
FROM
    `tbl_pr_dt`
WHERE
    `pr_id` = ?
ORDER BY `recid` ASC
", [$recid]);

$item_no = 0;
foreach ($query->getResultArray() as $data) {
    $item_desc = trim((string) $data['item_desc']);
    $quantity = $data['quantity'];
    $unit_cost = $data['unit_cost'];
    $unit = $data['unit'];

    // blank lines saved on older PRs are not items
    if ($item_desc === '' && (float) $quantity == 0 && (float) $unit_cost == 0) {
        continue;
    }
    $item_no++;

    // The row is as tall as its description; start a new page when it would not fit
    $rowHeight = 5 * $pdf->NbLines($cols['desc'][1], $item_desc);
    if ($pdf->GetY() + $rowHeight > 262) {
        $pdf->AddPage();
        $tableHeader(10);
        $pdf->SetFont('Arial', '', 9);
    }

    $startY = $pdf->GetY();

    // Borders for the whole row, then the description on top of its cell
    $tableLine($startY, $rowHeight, [], 'LB');
    $pdf->SetXY($cols['desc'][0], $startY);
    $pdf->MultiCell($cols['desc'][1], 5, $item_desc, 0, 'L');

    // Single-line cells sit in the middle of the row
    $middleY = $startY + ($rowHeight / 2) - 2.5;
    $pdf->SetXY($cols['no'][0], $middleY);   $pdf->Cell($cols['no'][1], 5, $item_no, 0, 0, 'C');
    $pdf->SetXY($cols['abc'][0], $middleY);  $pdf->Cell($cols['abc'][1], 5, number_format((float) $unit_cost, 2), 0, 0, 'R');
    $pdf->SetXY($cols['qty'][0], $middleY);  $pdf->Cell($cols['qty'][1], 5, $quantity, 0, 0, 'C');
    $pdf->SetXY($cols['unit'][0], $middleY); $pdf->Cell($cols['unit'][1], 5, $unit, 0, 0, 'C');

    // Move to the next line
    $pdf->SetY($startY + $rowHeight);
}

// Blank ruled rows fill the rest of the table on the first page
if ($pdf->PageNo() == 1) {
    while ($pdf->GetY() < 160) {
        $tableLine($pdf->GetY(), 5, [], 'LB');
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
$pdf->Cell(65, 3.5, 'Terms of Payment:', 'LB', 0, 'L');  // ITEM DESCRIPTION

$pdf->SetXY(75, $Y);
$pdf->Cell(30, 3.5, $terms, 'B', 0, 'L');  // ITEM DESCRIPTION

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