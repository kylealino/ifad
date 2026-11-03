<?php

namespace App\Libraries\Sorf;

require_once APPPATH . 'ThirdParty/fpdf/fpdf.php';

/**
 * The printed request form of one request (A4, portrait).
 *
 * Table forms (SO01-SO05, SO07) print two copies on a page like the paper
 * form, when both fit. The Local Travel Order (SO06) prints once (Annex B).
 * $form / $ltoExpenses come from Config\RequestForms.
 */
class RequestPdf extends \FPDF
{
    private const MARGIN = 14;
    private const PAGE_W = 210;
    private const PAGE_BOTTOM = 285;

    private array $form;
    private array $ltoExpenses;
    private array $signs = [];

    public function __construct(array $form, array $ltoExpenses)
    {
        parent::__construct('P', 'mm', 'A4');
        $this->form = $form;
        $this->ltoExpenses = $ltoExpenses;
        $this->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $this->SetAutoPageBreak(false);
        $this->SetLineWidth(0.2);
    }

    /**
     * @param array $record MyRequestModel::request_load()
     * @param array $signs  signature image paths: ['requester' => ?string, 'noted' => ?string]
     *
     * @return string the PDF file
     */
    public function render(array $record, array $signs = []): string
    {
        $this->signs = $signs;
        $this->SetTitle($this->t($record['hd']['request_no'] . ' - ' . $this->form['title']));
        $this->AddPage();

        if ($this->form['layout'] === 'lto') {
            $this->drawLto($record);
            return $this->Output('S');
        }

        $top = $this->GetY();
        $this->drawCopy($record);
        $height = $this->GetY() - $top;

        // second copy under the first, or on its own page when it does not fit
        if ($this->PageNo() === 1 && $this->GetY() + 8 + $height <= self::PAGE_BOTTOM + 6) {
            $this->SetY($this->GetY() + 8);
        } else {
            $this->AddPage();
        }
        $this->drawCopy($record);

        return $this->Output('S');
    }

    // FPDF's built-in fonts are Windows-1252, the data is UTF-8
    private function t($text): string
    {
        $text = (string) $text;
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
        return $converted === false ? $text : $converted;
    }

    private function longDate(string $date): string
    {
        $time = strtotime($date);
        return $time ? date('F j, Y', $time) : '';
    }

    /**
     * $text cut into lines that fit $w with the current font.
     *
     * @return list<string>
     */
    private function wrapLines(float $w, string $text): array
    {
        $lines = [];
        foreach (explode("\n", str_replace("\r", '', $text)) as $paragraph) {
            $line = '';
            foreach (explode(' ', $paragraph) as $word) {
                $try = $line === '' ? $word : $line . ' ' . $word;
                if ($this->GetStringWidth($try) <= $w) {
                    $line = $try;
                    continue;
                }
                if ($line !== '') {
                    $lines[] = $line;
                }
                // a single word wider than the column is cut to fit
                while (strlen($word) > 1 && $this->GetStringWidth($word) > $w) {
                    $cut = strlen($word) - 1;
                    while ($cut > 1 && $this->GetStringWidth(substr($word, 0, $cut)) > $w) {
                        $cut--;
                    }
                    $lines[] = substr($word, 0, $cut);
                    $word = substr($word, $cut);
                }
                $line = $word;
            }
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Fill-in lines side by side: each column is [x, width, text, align].
     * Text wraps onto more lines; at least $minLines are drawn.
     *
     * @return float the y under the last line
     */
    private function linedCols(array $cols, float $y, int $minLines = 1, float $lineH = 5.2): float
    {
        $sets = [];
        $count = $minLines;
        foreach ($cols as $i => $col) {
            $sets[$i] = $col[2] === '' ? [] : $this->wrapLines($col[1] - 2, $this->t($col[2]));
            $count = max($count, count($sets[$i]));
        }
        for ($line = 0; $line < $count; $line++) {
            foreach ($cols as $i => $col) {
                $this->SetXY($col[0], $y);
                $this->Cell($col[1], $lineH, $sets[$i][$line] ?? '', 'B', 0, $col[3] ?? 'L');
            }
            $y += $lineH;
        }
        return $y;
    }

    // A signature resting on its line: $x/$w are the line, $lineY its height on the page
    private function signature(string $who, float $x, float $lineY, float $w): void
    {
        $path = $this->signs[$who] ?? null;
        $size = $path === null ? false : @getimagesize($path);
        if ($size === false) {
            return;
        }
        $scale = min(($w - 6) / $size[0], 13 / $size[1]);
        $width = $size[0] * $scale;
        $height = $size[1] * $scale;
        $this->Image($path, $x + ($w - $width) / 2, $lineY - $height + 1.5, $width, $height, 'PNG');
    }

    private function checkbox(float $x, float $y, bool $checked): void
    {
        $size = 3.4;
        $this->Rect($x, $y, $size, $size);
        if ($checked) {
            $this->SetLineWidth(0.45);
            $this->Line($x + 0.6, $y + 0.6, $x + $size - 0.6, $y + $size - 0.6);
            $this->Line($x + $size - 0.6, $y + 0.6, $x + 0.6, $y + $size - 0.6);
            $this->SetLineWidth(0.2);
        }
    }

    // =====================================================================
    // Table forms
    // =====================================================================

    /**
     * Printed columns: a field marked 'merge' prints under the one before it.
     *
     * @return list<array{label: string, keys: list<string>, w: float, money: bool}>
     */
    private function columns(float $tableW): array
    {
        $cols = [];
        foreach ($this->form['fields'] as $field) {
            if (!empty($field['merge']) && !empty($cols)) {
                $cols[count($cols) - 1]['keys'][] = $field['key'];
                continue;
            }
            $cols[] = [
                'label' => $field['print_label'] ?? $field['label'],
                'keys' => [$field['key']],
                'w' => (float) ($field['w'] ?? 10),
                'money' => $field['type'] === 'money',
            ];
        }
        $total = array_sum(array_column($cols, 'w'));
        foreach ($cols as &$col) {
            $col['w'] = $tableW * $col['w'] / $total;
        }
        unset($col);
        return $cols;
    }

    private function tableHeader(array $cols): void
    {
        $this->SetFont('Helvetica', 'B', 9);
        $lines = 1;
        foreach ($cols as $col) {
            $lines = max($lines, substr_count($col['label'], "\n") + 1);
        }
        $height = $lines * 4.2 + 2;
        $x = self::MARGIN;
        $y = $this->GetY();
        foreach ($cols as $col) {
            $this->Rect($x, $y, $col['w'], $height);
            $this->SetXY($x, $y + 1);
            $this->MultiCell($col['w'], 4.2, $this->t($col['label']), 0, 'C');
            $x += $col['w'];
        }
        $this->SetY($y + $height);
    }

    // One box per column from $top down to $bottom, like the paper form
    private function tableBody(array $cols, float $top, float $bottom): void
    {
        $x = self::MARGIN;
        foreach ($cols as $col) {
            $this->Rect($x, $top, $col['w'], $bottom - $top);
            $x += $col['w'];
        }
    }

    private function drawCopy(array $record): void
    {
        $hd = $record['hd'];
        $x = self::MARGIN;
        $w = self::PAGE_W - 2 * self::MARGIN;

        //HEADER
        $y = $this->GetY();
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetXY($x + $w - 42, $y);
        $this->Cell(42, 5, $this->t('Request No.: ' . $hd['request_no']), 0, 0, 'R');
        $this->SetFont('Helvetica', 'B', 11.5);
        $this->SetXY($x, $y);
        $this->MultiCell($w - 44, 5.2, $this->t($this->form['title']), 0, 'L');

        $this->Ln(2.2);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($w, 4.5, 'To: Human Resource Management Section', 0, 1);

        $this->Ln(2.2);
        $this->Cell(28, 4.8, 'Date of Request:');
        $this->SetFont('Helvetica', '', 9);
        $this->Cell(46, 4.8, $this->t($this->longDate($hd['request_date'])), 'B', 1);

        $this->Ln(2.2);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell(16, 4.8, 'Subject:');
        $this->SetFont('Helvetica', '', 9);
        $this->SetY($this->linedCols([[$x + 16, $w * 0.72, $hd['subject']]], $this->GetY(), 1, 4.8));

        //TABLE
        $this->Ln(4);
        $cols = $this->columns($w);
        $this->tableHeader($cols);
        $top = $this->GetY();
        $lineH = 4.3;

        foreach ($record['rows'] as $n => $row) {
            $this->SetFont('Helvetica', '', 9);
            $cells = [];
            $lines = 1;
            foreach ($cols as $i => $col) {
                $parts = [];
                foreach ($col['keys'] as $key) {
                    $value = $col['money'] ? drf_money($row[$key]) : (string) $row[$key];
                    if ($value !== '') {
                        $parts[] = $value;
                    }
                }
                $cells[$i] = empty($parts) ? [] : $this->wrapLines($col['w'] - 3, $this->t(implode("\n", $parts)));
                $lines = max($lines, count($cells[$i]));
            }
            $height = $lines * $lineH + 2;

            // continue the table on a new page
            if ($this->GetY() + $height > self::PAGE_BOTTOM) {
                $this->tableBody($cols, $top, $this->GetY());
                $this->AddPage();
                $this->tableHeader($cols);
                $top = $this->GetY();
            } elseif ($n > 0) {
                $this->SetDrawColor(205);
                $this->Line($x, $this->GetY(), $x + $w, $this->GetY());
                $this->SetDrawColor(0);
            }

            $y = $this->GetY();
            $cx = $x;
            foreach ($cols as $i => $col) {
                foreach ($cells[$i] as $l => $text) {
                    $this->SetXY($cx + 1.5, $y + 1 + $l * $lineH);
                    $this->Cell($col['w'] - 3, $lineH, $text, 0, 0, $col['money'] ? 'R' : 'L');
                }
                $cx += $col['w'];
            }
            $this->SetY($y + $height);
        }

        // the paper form leaves room to write even when there is one line
        $bottom = max($this->GetY(), $top + 18);
        $this->tableBody($cols, $top, $bottom);
        $this->SetY($bottom);

        //SIGNATORIES
        if ($this->GetY() + 49 > self::PAGE_BOTTOM) {
            $this->AddPage();
        }
        $this->Ln(6);
        $y = $this->GetY();
        $lineW = 54;
        $notedX = $x + $w * 0.36;
        $remarksX = $x + $w - $lineW;

        $this->SetFont('Helvetica', 'B', 9);
        $this->SetXY($x, $y);
        $this->Cell($lineW, 4.5, 'Requested by:');
        $this->SetXY($notedX, $y);
        $this->Cell($lineW, 4.5, $this->t($this->form['noted_label']));
        $this->SetXY($remarksX, $y);
        $this->Cell($lineW, 4.5, 'Remarks: (by HRMS)');

        $this->SetFont('Helvetica', '', 9);
        $y += 14;
        $this->SetXY($x, $y);
        $this->Cell($lineW, 5, $this->t($hd['full_name']), 'B', 0, 'C');
        $this->SetXY($notedX, $y);
        $this->Cell($lineW, 5, $this->t($hd['noted_by']), 'B', 0, 'C');
        $this->signature('requester', $x, $y + 5, $lineW);
        $this->signature('noted', $notedX, $y + 5, $lineW);

        $this->SetFont('Helvetica', '', 8);
        $remarks = $hd['hr_remarks'];
        if ($hd['so_no'] !== '') {
            $remarks = trim('S.O. No.: ' . $hd['so_no'] . "\n" . $remarks);
        }
        $remarksEnd = $this->linedCols([[$remarksX, $lineW, $remarks]], $y, 3, 5);

        $this->SetFont('Helvetica', '', 8.5);
        $this->SetXY($x, $y + 5.3);
        $this->Cell($lineW, 4.5, 'Signature over printed name');
        $this->SetXY($notedX, $y + 5.3);
        $this->Cell($lineW, 4.5, 'Signature over Printed Name');

        //NOTE
        $this->SetFont('Helvetica', 'I', 8.5);
        $this->SetXY($x, max($remarksEnd, $y + 10) + 4);
        $this->Cell($w, 4.5, $this->t($this->form['note']), 0, 1);

        $this->Ln(4);
        $this->Line($x, $this->GetY(), $x + $w, $this->GetY());
    }

    // =====================================================================
    // Local Travel Order (Annex B)
    // =====================================================================

    // The next block needs $height; start a new page when it does not fit
    private function need(float $y, float $height): float
    {
        if ($y + $height <= self::PAGE_BOTTOM) {
            return $y;
        }
        $this->AddPage();
        return self::MARGIN;
    }

    private function drawLto(array $record): void
    {
        $hd = $record['hd'];
        $lto = $record['lto'] ?? ['destination' => '', 'travel_dates' => '', 'purpose' => '', 'is_general_fund' => 0, 'project_fund' => '', 'other_fund' => '', 'remarks' => ''];
        $expenses = $record['expenses'];
        $x = self::MARGIN;
        $w = self::PAGE_W - 2 * self::MARGIN;
        $right = $x + $w;

        //HEADER
        $this->SetFont('Times', '', 10.5);
        $this->SetXY($right - 22, 12);
        $this->Cell(22, 7, 'Annex B', 1, 0, 'C');

        $boxW = 50;
        $this->Rect($right - $boxW, 22.5, $boxW, 19.5);
        $this->SetFont('Times', '', 10);
        foreach (['For HRMS', 'Received by: ____________', 'Date received: __________', 'LTO No.: ' . ($hd['so_no'] !== '' ? $hd['so_no'] : '______________')] as $i => $text) {
            $this->SetXY($right - $boxW + 1.5, 23.5 + $i * 4.4);
            $this->Cell($boxW - 3, 4.4, $this->t($text));
        }

        $this->SetFont('Times', 'B', 14);
        $this->SetXY($x, 27);
        $this->Cell(110, 6, 'Request for Local Travel Order');
        $this->SetFont('Times', '', 8);
        $this->SetXY($x, 21.5);
        $this->Cell(60, 4, $this->t('Request No.: ' . $hd['request_no']));

        $this->SetFont('Times', '', 11);
        $this->SetXY($x, 36);
        $this->Cell(30, 5, 'Date of Request:');
        $this->Cell(42, 5, $this->t($this->longDate($hd['request_date'])), 'B');

        $this->SetLineWidth(0.5);
        $this->Line($x, 44.5, $right, 44.5);
        $this->SetLineWidth(0.2);

        //TRAVELLERS
        $gap = 9;
        $colW = ($w - 2 * $gap) / 3;
        $colX = [$x, $x + $colW + $gap, $x + 2 * ($colW + $gap)];

        $y = 49;
        $this->SetFont('Times', '', 10.5);
        $this->SetXY($x + 1.5, $y);
        $this->Cell($w, 5, 'Authority to travel is hereby granted to:');

        $y += 8;
        $this->SetFont('Times', 'B', 10);
        foreach (['NAME', 'POSITION', 'DIVISION/AGENCY'] as $i => $label) {
            $this->SetXY($colX[$i], $y);
            $this->Cell($colW, 5, $label, 0, 0, 'C');
        }
        $y += 5.5;
        $this->SetFont('Times', '', 10);
        $rows = $record['rows'];
        for ($i = 0, $count = max(3, count($rows)); $i < $count; $i++) {
            $row = $rows[$i] ?? ['staff_name' => '', 'position' => '', 'division_agency' => ''];
            $y = $this->need($y, 6);
            $y = $this->linedCols([
                [$colX[0], $colW, $row['staff_name']],
                [$colX[1], $colW, $row['position']],
                [$colX[2], $colW, $row['division_agency']],
            ], $y, 1, 5);
        }

        //DESTINATION
        $y = $this->need($y + 4, 26);
        $this->SetFont('Times', 'B', 10);
        foreach (['Destination/s', 'Inclusive Date/s of Travel', 'Purpose(s) of the Travel'] as $i => $label) {
            $this->SetXY($colX[$i], $y);
            $this->Cell($colW, 5, $label, 0, 0, 'C');
        }
        $this->SetFont('Times', '', 10);
        $y = $this->linedCols([
            [$colX[0], $colW, $lto['destination']],
            [$colX[1], $colW, $lto['travel_dates']],
            [$colX[2], $colW, $lto['purpose']],
        ], $y + 5.5, 3, 5);

        //EXPENSES
        $labelW = 43;
        $fundGap = 6;
        $fundW = ($w - $labelW - 2 * $fundGap) / 3;
        $fundX = [$x + $labelW, $x + $labelW + $fundW + $fundGap, $x + $labelW + 2 * ($fundW + $fundGap)];
        $funds = ['general_fund', 'project_fund', 'other_fund'];

        $used = array_fill_keys($funds, false);
        foreach ($expenses as $line) {
            foreach ($funds as $fund) {
                $used[$fund] = $used[$fund] || trim((string) $line[$fund]) !== '';
            }
        }

        $y = $this->need($y + 5, 110);
        $this->SetFont('Times', 'B', 9.5);
        $this->SetXY($x, $y);
        $this->Cell($labelW + 18, 5, 'Travel Expenses to be Incurred:');
        $this->SetXY($fundX[0] + 30, $y);
        $this->Cell(100, 5, 'Appropriate/Fund to which travel expenses would be charged to:');

        $y += 8;
        $heads = ["General Fund", "Project Funds\n(Pls. specify)", "Others: (e.g. sponsor/\nrequesting agency)"];
        $ticked = [
            (int) $lto['is_general_fund'] === 1 || $used['general_fund'],
            $lto['project_fund'] !== '' || $used['project_fund'],
            $lto['other_fund'] !== '' || $used['other_fund'],
        ];
        foreach ($heads as $i => $label) {
            $this->SetXY($fundX[$i], $y);
            $this->MultiCell($fundW, 4, $label, 0, 'C');
            $this->checkbox($fundX[$i] + $fundW / 2 - 1.7, $y + 9.5, $ticked[$i]);
        }
        $this->SetFont('Times', '', 10);
        $y = $this->linedCols([
            [$fundX[0], $fundW, ''],
            [$fundX[1], $fundW, $lto['project_fund'], 'C'],
            [$fundX[2], $fundW, $lto['other_fund'], 'C'],
        ], $y + 14.5, 1, 5);
        $y = $this->linedCols([[$fundX[1], $fundW, ''], [$fundX[2], $fundW, '']], $y, 2, 5);

        $expenseLine = function (string $code, float $y) use ($expenses, $fundX, $fundW, $funds): float {
            $cols = [];
            foreach ($funds as $i => $fund) {
                $cols[] = [$fundX[$i], $fundW, (string) ($expenses[$code][$fund] ?? ''), 'C'];
            }
            return $this->linedCols($cols, $y, 1, 5.4);
        };

        $y += 0.5;
        foreach ($this->ltoExpenses as $group) {
            $codes = array_merge($group['code'] !== '' ? [$group['code']] : [], array_keys($group['items']));
            $y = $this->need($y, 6 * (count($group['items']) + 1));

            $this->checkbox($x, $y + 1.2, !empty(array_intersect($codes, array_keys($expenses))));
            $this->SetXY($x + 5, $y);
            $this->Cell($labelW - 5, 5.4, $this->t($group['label']));
            $y = $group['code'] !== '' ? $expenseLine($group['code'], $y) : $y + 5.4;

            foreach ($group['items'] as $code => $label) {
                // "Public conveyance (Airplane, Bus, Taxi)" takes two lines on the form
                $parts = explode(' (', $label, 2);
                $this->SetXY($x + 9, $y);
                $this->Cell($labelW - 9, 5.4, $this->t($parts[0]));
                $y = $expenseLine($code, $y);
                if (isset($parts[1])) {
                    $this->SetXY($x + 9, $y);
                    $this->Cell($labelW - 9, 5, $this->t('(' . $parts[1]));
                    $y += 5;
                }
            }
        }

        //REMARKS
        $y = $this->need($y + 1.5, 14);
        $this->SetXY($x, $y);
        $this->Cell(45, 5.2, 'Remarks/Special Instructions');
        $y = $this->linedCols([[$x + 47, $w - 47, $lto['remarks']]], $y, 2, 5.2);

        //SIGNATORIES
        $y = $this->need($y + 7, 36);
        $lineW = 60;
        $notedX = $x + $w * 0.44;
        $this->SetFont('Times', 'B', 9.5);
        $this->SetXY($x, $y);
        $this->Cell($lineW, 5, 'Requested by:');
        $this->SetXY($notedX, $y);
        $this->Cell($lineW + 20, 5, $this->t($this->form['noted_label']));

        $this->SetFont('Times', '', 10);
        $y += 14;
        $this->SetXY($x, $y);
        $this->Cell($lineW, 5, $this->t($hd['full_name']), 'B', 0, 'C');
        $this->SetXY($notedX, $y);
        $this->Cell($lineW, 5, $this->t($hd['noted_by']), 'B', 0, 'C');
        $this->signature('requester', $x, $y + 5, $lineW);
        $this->signature('noted', $notedX, $y + 5, $lineW);
        $this->SetFont('Times', '', 9.5);
        $this->SetXY($x, $y + 5.2);
        $this->Cell($lineW, 4.5, 'Signature over Printed Name');
        $this->SetXY($notedX, $y + 5.2);
        $this->Cell($lineW, 4.5, 'Signature Over Printed Name');

        //NOTE
        $this->SetFont('Times', 'I', 8.5);
        $this->SetXY($x, $y + 16);
        $this->Cell($w, 4.5, $this->t($this->form['note']));

        if ($hd['hr_remarks'] !== '') {
            $this->SetFont('Times', '', 9);
            $this->SetXY($x, $y + 23);
            $this->MultiCell($w, 4.5, $this->t('HRMS remarks: ' . $hd['hr_remarks']));
        }
    }
}
