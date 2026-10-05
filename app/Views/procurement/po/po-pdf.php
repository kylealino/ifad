<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';

//PO DATA
$po = $this->db->query("SELECT * FROM tbl_po_hd WHERE `recid` = ?", [$recid])->getRowArray();
if (empty($po)) {
    die('Purchase order not found.');
}
$items = $this->db->query("
SELECT `stock_no`, `unit`, `item_desc`, `quantity`, `unit_cost`, `amount`
FROM tbl_po_dt
WHERE `po_id` = ?
ORDER BY `recid` ASC
", [$recid])->getResultArray();

// Dates as on the printed form: PO date 14-Aug-2025, PR / ORS dates 07-28-2025
$fmtDate = function ($d, $format) {
    return (empty($d) || strpos($d, '0000') === 0) ? '' : date($format, strtotime($d));
};
$money = fn ($n) => number_format((float) $n, 2);

$po_date = $fmtDate($po['po_date'], 'd-M-Y');
$pr_ref = trim($po['prno']) === '' ? '' : $po['prno'] . ($po['pr_date'] ? ' dtd. ' . $fmtDate($po['pr_date'], 'm-d-Y') : '');
$ors_date = $fmtDate($po['ors_burs_date'], 'm/d/Y');

// Total amount in words: "Eighteen Thousand Pesos", centavos as "and 50/100"
if (!function_exists('po_amount_in_words')) {
    function po_number_words($n) {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($n < 20) {
            return $ones[$n];
        }
        if ($n < 100) {
            return $tens[intdiv($n, 10)] . ($n % 10 ? '-' . $ones[$n % 10] : '');
        }
        if ($n < 1000) {
            return $ones[intdiv($n, 100)] . ' Hundred' . ($n % 100 ? ' ' . po_number_words($n % 100) : '');
        }
        foreach ([1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand'] as $size => $word) {
            if ($n >= $size) {
                return po_number_words(intdiv($n, $size)) . ' ' . $word . ($n % $size ? ' ' . po_number_words($n % $size) : '');
            }
        }
        return '';
    }
    function po_amount_in_words($amount) {
        $amount = round((float) $amount, 2);
        $pesos = (int) floor($amount);
        $centavos = (int) round(($amount - $pesos) * 100);
        $words = ($pesos === 0 ? 'Zero' : po_number_words($pesos)) . ' Peso' . ($pesos === 1 ? '' : 's');
        return $words . ($centavos ? ' and ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100' : '');
    }
}

// FPDF with UTF-8 text: names are stored in UTF-8 (ñ / Ñ are two bytes) but
// FPDF's core fonts read Windows-1252, so text is converted here once.
if (!class_exists('PoPDF')) {
    class PoPDF extends \FPDF {
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
        // A one-line cell whose font shrinks (down to 6 pt) until the text fits
        function FitCell($w, $h, $txt, $border = 0, $ln = 0, $align = 'L') {
            $family = $this->FontFamily;
            $style = $this->FontStyle;
            $size = $this->FontSizePt;
            $fit = $size;
            while ($fit > 6 && $this->GetStringWidth($txt) > $w - 2 * $this->cMargin) {
                $fit -= 0.5;
                $this->SetFont($family, $style, $fit);
            }
            $this->Cell($w, $h, $txt, $border, $ln, $align);
            $this->SetFont($family, $style, $size);
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
        // Controlled-document notice and page number on every page
        function Footer() {
            $this->SetY(-24);
            $lines = [
                [['Reproduction of this ', ''], ['CONTROLLED DOCUMENT', 'B'], [' is ', ''], ['STRICTLY PROHIBITED', 'B'], [' without permission from the Document Custodian. This', '']],
                [['DOCUMENTED INFORMATION WHEN PRINTED', 'B'], [' is deemed ', ''], ['UNCONTROLLED.', 'B']],
            ];
            foreach ($lines as $parts) {
                $total = 0;
                foreach ($parts as $part) {
                    $this->SetFont('Arial', $part[1], 8);
                    $total += $this->GetStringWidth($part[0]);
                }
                $this->SetX(5 + (205 - $total) / 2);
                foreach ($parts as $part) {
                    $this->SetFont('Arial', $part[1], 8);
                    $this->Cell($this->GetStringWidth($part[0]), 4, $part[0], 0, 0);
                }
                $this->Ln(4);
            }
            $this->SetFont('Arial', '', 8);
            $this->SetXY(5, -14);
            $this->Cell(205, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
        }
    }
}

$pdf = new PoPDF('P', 'mm', 'LEGAL');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 26);
$pdf->AddPage();
$pdf->SetTitle('PO - PRINT');

// Item table columns: x position and width (5 to 210 mm across the page)
$cols = [
    'stock' => [5, 25],
    'unit'  => [30, 15],
    'desc'  => [45, 80],
    'qty'   => [125, 15],
    'cost'  => [140, 35],
    'amt'   => [175, 35],
];

//HEADER: letterhead, form number, title --------------------------------------
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetXY(5, 5);
$pdf->Cell(205, 5, 'Appendix 61', 'TRL', 1, 'R');
$pdf->SetXY(5, 10);
$pdf->Cell(205, 22, '', 'RL', 1, 'R');
$pdf->Image(ROOTPATH . 'public/assets/images/logos/fnri-header.png', 8, 11, 160, 0);

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(184, 12);
$pdf->Cell(24, 3.5, 'FAD-PS-006', 'TRL', 1, 'L');
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(184, 15.5);
$pdf->Cell(24, 3.5, 'Revision 0', 'RL', 1, 'L');
$pdf->SetXY(184, 19);
$pdf->Cell(24, 3.5, '08 July 2025', 'BRL', 1, 'L');

$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY(5, 32);
$pdf->Cell(205, 9, 'P U R C H A S E   O R D E R', 'BRL', 1, 'C');

//SUPPLIER / PO DETAILS ---------------------------------------------------------
$pdf->SetFont('Arial', '', 9);
$infoRow = function ($label, $value, $rlabel, $rvalue, $rlabelW, $last = false) use ($pdf) {
    $Y = $pdf->GetY();
    $pdf->SetXY(5, $Y);
    $pdf->Cell(25, 5, $label, $last ? 'LB' : 'L', 0, 'L');
    // the value line runs up to the divider, and on the right it runs under
    // the label too, so each row reads as one ruled line like the printed form
    $pdf->SetXY(30, $Y);
    $pdf->FitCell(95, 5, $value, 'B', 0, 'L');
    $pdf->SetXY(125, $Y);
    $pdf->Cell($rlabelW, 5, $rlabel, 'LB', 0, 'L');
    $pdf->SetXY(125 + $rlabelW, $Y);
    $pdf->FitCell(85 - $rlabelW, 5, $rvalue, 'BR', 0, 'L');
    $pdf->SetY($Y + 5);
};
$infoRow('Supplier:', $po['supplier_name'], 'PO.No.', $po['pono'], 15);
$infoRow('Address:', $po['supplier_address'], 'Date', $po_date, 15);
$infoRow('TEL#', $po['supplier_tel'], 'Mode of Procurement:', $po['mode_of_procurement'], 35);
$infoRow('TIN:', $po['supplier_tin'], 'PR#', $pr_ref, 15, true);

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(205, 5, 'Gentlemen:', 'LR', 1, 'L');
$pdf->SetX(5);
$pdf->Cell(205, 5, '      Please furnish this office the following articles subject to the terms and conditions contained herein:', 'LRB', 1, 'L');

//DELIVERY ----------------------------------------------------------------------
// value printed on up to two lines inside an 8 mm cell (long terms wrap like the form)
$twoLines = function ($x, $Y, $w, $txt, $border) use ($pdf) {
    $pdf->SetXY($x, $Y);
    $pdf->Cell($w, 8, '', $border, 0, 'L');
    $size = 9;
    while ($size > 6 && $pdf->NbLines($w, $txt) > 2) {
        $size -= 0.5;
        $pdf->SetFontSize($size);
    }
    $lines = $pdf->NbLines($w, $txt);
    $pdf->SetXY($x, $Y + ($lines > 1 ? 0 : 2));
    $pdf->MultiCell($w, 4, $txt, 0, 'L');
    $pdf->SetFontSize(9);
};

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(40, 8, 'Place of Delivery:', 'L', 0, 'L');
$twoLines(45, $Y, 80, $po['place_of_delivery'], 'B');
$pdf->SetXY(125, $Y);
$pdf->MultiCell(18, 4, "Delivery\nTerm:", 'L', 'L');
$twoLines(143, $Y, 67, $po['delivery_term'], 'BR');

$Y += 8;
$pdf->SetXY(5, $Y);
$pdf->Cell(40, 8, 'Date of Delivery:', 'LB', 0, 'L');
$twoLines(45, $Y, 80, $po['date_of_delivery'], 'B');
$pdf->SetXY(125, $Y);
$pdf->Cell(40, 8, 'Payment Term:', 'LB', 0, 'L');
$twoLines(165, $Y, 45, $po['payment_term'], 'BR');
$pdf->SetXY(5, $Y);
$pdf->Cell(120, 8, '', 'B', 0, 'L');
$pdf->SetY($Y + 8);

//ITEM TABLE --------------------------------------------------------------------
$tableHeader = function ($Y) use ($pdf, $cols) {
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetXY($cols['stock'][0], $Y);
    $pdf->MultiCell($cols['stock'][1], 4, "Stock/\nProperty No.", 1, 'C');
    foreach (['unit' => 'Unit', 'desc' => 'Description', 'qty' => 'Quantity', 'cost' => 'Unit Cost', 'amt' => 'Amount'] as $key => $label) {
        $pdf->SetXY($cols[$key][0], $Y);
        $pdf->Cell($cols[$key][1], 8, $label, 1, 0, 'C');
    }
    $pdf->SetY($Y + 8);
};

// column lines only (the form has no lines between items)
$columnLines = function ($Y, $h, $bottom = false) use ($pdf, $cols) {
    foreach ($cols as $key => [$x, $w]) {
        $pdf->SetXY($x, $Y);
        $pdf->Cell($w, $h, '', 'L' . ($key === 'amt' ? 'R' : '') . ($bottom ? 'B' : ''), 0);
    }
    $pdf->SetY($Y + $h);
};

$tableHeader($pdf->GetY());
$itemsTop = $pdf->GetY();
$pdf->SetFont('Arial', '', 9);
$columnLines($itemsTop, 3);

foreach ($items as $it) {
    $desc = trim((string) $it['item_desc']);
    $rowHeight = 4.5 * max(1, $pdf->NbLines($cols['desc'][1], $desc)) + 1.5;

    // start a new page (with the column header) when the row would not fit
    if ($pdf->GetY() + $rowHeight > 320) {
        $columnLines($pdf->GetY(), 0.1, true);
        $pdf->AddPage();
        $tableHeader(10);
        $pdf->SetFont('Arial', '', 9);
        $itemsTop = 0;
    }

    $startY = $pdf->GetY();
    $columnLines($startY, $rowHeight);
    $pdf->SetXY($cols['desc'][0], $startY);
    $pdf->MultiCell($cols['desc'][1], 4.5, $desc, 0, 'L');

    // single-line values sit in the middle of the row
    $middleY = $startY + ($rowHeight - 4.5) / 2;
    foreach ([
        'stock' => [$it['stock_no'], 'C'],
        'unit' => [$it['unit'], 'C'],
        'qty' => [rtrim(rtrim(number_format((float) $it['quantity'], 2, '.', ','), '0'), '.'), 'C'],
        'cost' => [$money($it['unit_cost']), 'R'],
        'amt' => [$money($it['amount']), 'R'],
    ] as $key => [$value, $align]) {
        $pdf->SetXY($cols[$key][0], $middleY);
        $pdf->FitCell($cols[$key][1], 4.5, $value, 0, 0, $align);
    }
    $pdf->SetY($startY + $rowHeight);
}

// on the first page the item area keeps the form's size even with few items
if ($itemsTop > 0 && $pdf->GetY() < $itemsTop + 95) {
    $columnLines($pdf->GetY(), $itemsTop + 95 - $pdf->GetY());
}

// the bottom of the form (charged to ... funds) needs about 120 mm
if ($pdf->GetY() + 120 > 330) {
    $columnLines($pdf->GetY(), 0.1, true);
    $pdf->AddPage();
    $pdf->SetXY(5, 10);
    $pdf->Cell(205, 0.1, '', 'T', 1);
    $pdf->SetY(10);
}

// total under the amount column
$Y = $pdf->GetY();
$columnLines($Y, 6);
$pdf->SetXY($cols['amt'][0], $Y);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($cols['amt'][1], 6, $money($po['total_amount']), 'LRT', 0, 'R');
$pdf->SetFont('Arial', '', 9);
$pdf->SetY($Y + 6);

//CHARGED TO / END-USER --------------------------------------------------------
$Y = $pdf->GetY();
$chargeH = max(8, 4 * $pdf->NbLines(140, $po['charge_to']) + 2);
$pdf->SetXY(5, $Y);
$pdf->Cell(25, $chargeH, 'Charged to:', 'L', 0, 'L');
$pdf->SetXY(30, $Y);
$pdf->Cell(145, $chargeH, '', 'L', 0, 'L');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, $chargeH, '', 'LR', 0, 'L');
$pdf->SetXY(30, $Y + ($chargeH - 4 * $pdf->NbLines(140, $po['charge_to'])) / 2);
$pdf->MultiCell(140, 4, $po['charge_to'], 0, 'L');
$pdf->SetY($Y + $chargeH);

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(25, 8, 'End-User:', 'L', 0, 'L');
$pdf->SetXY(30, $Y);
$pdf->FitCell(145, 8, $po['end_user'], 'L', 0, 'L');
$pdf->SetXY(175, $Y);
$pdf->Cell(35, 8, '', 'LR', 0, 'L');
$pdf->SetY($Y + 8);

$Y = $pdf->GetY();
$pdf->SetXY(5, $Y);
$pdf->Cell(45, 6, 'Total Amount in Words', 'LTB', 0, 'L');
$pdf->SetXY(50, $Y);
$pdf->FitCell(160, 6, po_amount_in_words($po['total_amount']), 'RTB', 0, 'C');
$pdf->SetY($Y + 6);

//PENALTY / CONFORME / SIGNATORY ------------------------------------------------
$panelTop = $pdf->GetY();
$pdf->SetXY(10, $panelTop + 3);
$pdf->MultiCell(195, 4.5, '      In case of failure to make the full delivery within the specified time, a penalty of one-tenth(1/10) of one percent for every day of delay shall be imposed.', 0, 'L');

$Y = $pdf->GetY() + 5;
$pdf->SetXY(8, $Y);
$pdf->Cell(40, 5, 'Conforme:', 0, 0, 'L');
$pdf->SetXY(145, $Y);
$pdf->Cell(60, 5, 'Very truly yours,', 0, 0, 'L');

// supplier signs on the left; the approving officer on the right
$Y += 14;
$pdf->SetXY(30, $Y);
$pdf->Cell(90, 5, '', 'B', 0, 'C');
$pdf->SetXY(130, $Y);
$pdf->SetFont('Arial', 'B', 9.5);
$pdf->FitCell(75, 5, strtoupper($po['approved_by']), 0, 0, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(30, $Y + 5);
$pdf->Cell(90, 5, '(Signature over printed name)', 0, 0, 'C');
$pdf->SetXY(130, $Y + 5);
$pdf->FitCell(75, 5, $po['approved_position'], 0, 0, 'C');

$Y += 15;
$pdf->SetXY(30, $Y);
$pdf->Cell(90, 5, '', 'B', 0, 'C');
$pdf->SetXY(30, $Y + 5);
$pdf->Cell(90, 5, '(Date)', 0, 0, 'C');
$panelBottom = $Y + 13;
$pdf->Line(5, $panelTop, 5, $panelBottom);
$pdf->Line(210, $panelTop, 210, $panelBottom);

//FUNDS / OBLIGATION ------------------------------------------------------------
$top = $panelBottom;
$pdf->Rect(5, $top, 205, 34);
$pdf->Line(125, $top, 125, $top + 34);

$fundRow = function ($Y, $label, $value, $x, $labelW, $valueW, $align = 'C') use ($pdf) {
    $pdf->SetXY($x, $Y);
    $pdf->Cell($labelW, 5, $label, 0, 0, 'L');
    $pdf->SetXY($x + $labelW, $Y);
    $pdf->FitCell($valueW, 5, $value, 'B', 0, $align);
};
$fundRow($top + 2, 'Fund Cluster:', $po['fund_cluster'], 7, 33, 40);
$fundRow($top + 8, 'Funds Available:', (float) $po['funds_available'] ? $money($po['funds_available']) : '', 7, 33, 40);
$fundRow($top + 2, 'ORS/BURS No.:', $po['ors_burs_no'], 127, 27, 54);
$fundRow($top + 8, 'Date of the ORS/BURS:', $ors_date, 127, 37, 44);
$fundRow($top + 14, 'Amount :', (float) $po['ors_burs_amount'] ? $money($po['ors_burs_amount']) : '', 127, 17, 64);

// accountant certifying funds available
$pdf->SetXY(30, $top + 23);
$pdf->SetFont('Arial', 'B', 9.5);
$pdf->FitCell(90, 5, $po['accountant'], 0, 0, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(30, $top + 28);
$pdf->FitCell(90, 5, $po['accountant_position'], 0, 0, 'C');

$pdf->Output('I', 'PO-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $po['pono'] ?: $po['recid']) . '.pdf');
exit;
?>
