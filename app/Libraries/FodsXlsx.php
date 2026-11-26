<?php

namespace App\Libraries;

/**
 * Minimal Excel (.xlsx) writer for report downloads: one sheet, optional title
 * lines, a bold header row (frozen, with filters), typed columns and an optional
 * total row. Rows are streamed to a temp file, so large exports stay light on
 * memory. Needs only PHP's zip extension (no Composer).
 *
 *   $x = new FodsXlsx('ORS lines', ['ORS date' => 'd', 'Payee' => 's', 'Amount' => 'n']);
 *   $x->title('ORS extraction', 'January 2026 to March 2026');
 *   foreach ($rows as $r) { $x->row([$r['ors_date'], $r['payee_name'], $r['amount']]); }
 *   $x->download('ORS_EXTRACTION.xlsx');   // sends the file and exits
 *
 * Column types: 's' text, 'n' amount (#,##0.00), 'i' whole number, 'd' date
 * (Y-m-d text in, real Excel date out), 'p' percent (pass the percent itself:
 * 59.52 or "59.52%", shown as 59.52%).
 * A value that does not fit its type is written as text, never dropped.
 */
class FodsXlsx
{
    // cellXfs indexes in styles()
    private const ST_TEXT = 0, ST_HEAD = 1, ST_MONEY = 2, ST_DATE = 3, ST_PCT = 4,
        ST_TITLE = 5, ST_SUB = 6, ST_TOT_TEXT = 7, ST_TOT_MONEY = 8, ST_TOT_PCT = 9, ST_INT = 10, ST_TOT_INT = 11;

    private string $sheet;
    private array $labels;
    private array $types;
    private array $widths = [];
    private array $titles = [];
    private $fh;
    private string $tmp;
    private int $rows = 0;      // data rows written
    private ?array $total = null;

    public function __construct(string $sheet, array $columns)
    {
        $this->sheet = mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheet), 0, 31) ?: 'Sheet1';
        // ['Label' => type, ...], or [['Label', type], ...] when labels repeat
        if (array_is_list($columns)) {
            $this->labels = array_column($columns, 0);
            $this->types = array_column($columns, 1);
        } else {
            $this->labels = array_keys($columns);
            $this->types = array_values($columns);
        }
        foreach ($this->labels as $i => $label) {
            $this->widths[$i] = max(10, mb_strlen($label) + 2);
        }
        $this->tmp = tempnam(sys_get_temp_dir(), 'fxl');
        $this->fh = fopen($this->tmp, 'w');
    }

    /** Title lines above the header row (first one bold and larger). */
    public function title(string ...$lines): self
    {
        $this->titles = array_values(array_filter($lines, fn ($l) => $l !== ''));
        return $this;
    }

    public function row(array $values): self
    {
        $this->rows++;
        fwrite($this->fh, $this->rowXml(array_values($values), false, $this->firstDataRow() + $this->rows - 1));
        return $this;
    }

    /** A bold total row under the data; pass null for blank cells. */
    public function total(array $values): self
    {
        $this->total = array_values($values);
        return $this;
    }

    /** Sends the workbook as a download and ends the request. */
    public function download(string $filename): void
    {
        fclose($this->fh);
        $file = $this->build();
        @unlink($this->tmp);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . str_replace(['"', "\r", "\n"], '', $filename) . '"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($file);
        @unlink($file);
        exit;
    }

    // ---------------------------------------------------------------------

    private function headerRow(): int { return count($this->titles) ? count($this->titles) + 2 : 1; }
    private function firstDataRow(): int { return $this->headerRow() + 1; }

    private static function col(int $i): string
    {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $s = chr(65 + ($i - 1) % 26) . $s;
        }
        return $s;
    }

    private static function esc($v): string
    {
        $v = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', (string) $v) ?? '';
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function textCell(string $ref, $v, int $style): string
    {
        $v = (string) $v;
        if ($v === '') {
            return $style ? '<c r="' . $ref . '" s="' . $style . '"/>' : '';
        }
        return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc($v) . '</t></is></c>';
    }

    private function rowXml(array $values, bool $isTotal, int $r): string
    {
        $cells = '';
        foreach ($this->types as $i => $type) {
            $v = $values[$i] ?? null;
            $ref = self::col($i) . $r;
            if ($v === null || $v === '') {
                $cells .= $isTotal ? '<c r="' . $ref . '" s="' . self::ST_TOT_TEXT . '"/>' : '';
                continue;
            }
            $num = null;
            $style = $isTotal ? self::ST_TOT_TEXT : self::ST_TEXT;
            if ($type === 'n' || $type === 'i') {
                $t = str_replace(',', '', trim((string) $v));
                if (is_numeric($t)) {
                    $num = (float) $t;
                    $style = $type === 'n' ? ($isTotal ? self::ST_TOT_MONEY : self::ST_MONEY) : ($isTotal ? self::ST_TOT_INT : self::ST_INT);
                }
            } elseif ($type === 'p') {
                $t = rtrim(trim((string) $v), '%');
                if (is_numeric($t)) {
                    $num = (float) $t / 100;
                    $style = $isTotal ? self::ST_TOT_PCT : self::ST_PCT;
                }
            } elseif ($type === 'd') {
                if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string) $v, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                    $num = gmmktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]) / 86400 + 25569;
                    $style = self::ST_DATE;
                }
            }
            if ($num !== null) {
                $cells .= '<c r="' . $ref . '" s="' . $style . '"><v>' . rtrim(rtrim(sprintf('%.6F', $num), '0'), '.') . '</v></c>';
                $shown = $type === 'd' ? 10 : strlen(number_format($num * ($type === 'p' ? 100 : 1), $type === 'i' ? 0 : 2)) + 1;
            } else {
                $cells .= $this->textCell($ref, $v, $style);
                $shown = max(array_map('mb_strlen', explode("\n", (string) $v)));
            }
            $this->widths[$i] = max($this->widths[$i], min(60, $shown + 2));
        }
        return '<row r="' . $r . '">' . $cells . '</row>';
    }

    private function build(): string
    {
        $head = $this->headerRow();
        $last = self::col(count($this->labels) - 1);
        $lastRow = $head + $this->rows + ($this->total ? 1 : 0);

        $cols = '';
        foreach ($this->widths as $i => $w) {
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
        }

        $top = '';
        foreach ($this->titles as $k => $line) {
            $top .= '<row r="' . ($k + 1) . '">' . $this->textCell('A' . ($k + 1), $line, $k === 0 ? self::ST_TITLE : self::ST_SUB) . '</row>';
        }
        $hdr = '<row r="' . $head . '">';
        foreach ($this->labels as $i => $label) {
            $hdr .= $this->textCell(self::col($i) . $head, $label, self::ST_HEAD);
        }
        $hdr .= '</row>';

        $sheetFile = tempnam(sys_get_temp_dir(), 'fxs');
        $out = fopen($sheetFile, 'w');
        fwrite($out, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr><dimension ref="A1:' . $last . $lastRow . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . $head . '" topLeftCell="A' . ($head + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/><cols>' . $cols . '</cols><sheetData>' . $top . $hdr);
        $in = fopen($this->tmp, 'r');
        stream_copy_to_stream($in, $out);
        fclose($in);
        if ($this->total) {
            fwrite($out, $this->rowXml($this->total, true, $head + $this->rows + 1));
        }
        fwrite($out, '</sheetData>'
            . ($this->rows ? '<autoFilter ref="A' . $head . ':' . $last . ($head + $this->rows) . '"/>' : '')
            . '<pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            . '<pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0"/></worksheet>');
        fclose($out);

        $file = tempnam(sys_get_temp_dir(), 'fxz');
        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '</Relationships>');
        $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>' . self::esc($this->titles[0] ?? $this->sheet) . '</dc:title><dc:creator>FODS</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created></cp:coreProperties>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::esc($this->sheet) . '" sheetId="1" r:id="rId1"/></sheets>'
            . ($this->rows ? '<definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">\'' . str_replace("'", "''", self::esc($this->sheet)) . '\'!$A$' . $head . ':$' . $last . '$' . ($head + $this->rows) . '</definedName></definedNames>' : '')
            . '</workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFile($sheetFile, 'xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($sheetFile);
        return $file;
    }

    private static function styles(): string
    {
        // fonts: 0 normal, 1 bold, 2 bold 13pt, 3 grey; fills: 2 = light teal header
        // borders: 1 thin bottom (header), 2 thin top (total)
        $xf = fn ($numFmt, $font, $fill, $border, $align = '') =>
            '<xf numFmtId="' . $numFmt . '" fontId="' . $font . '" fillId="' . $fill . '" borderId="' . $border . '" xfId="0"'
            . ($numFmt ? ' applyNumberFormat="1"' : '') . ($font ? ' applyFont="1"' : '') . ($fill ? ' applyFill="1"' : '') . ($border ? ' applyBorder="1"' : '')
            . ($align ? ' applyAlignment="1"><alignment ' . $align . '/></xf>' : '/>');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2"><numFmt numFmtId="164" formatCode="yyyy-mm-dd"/><numFmt numFmtId="165" formatCode="0.00%"/></numFmts>'
            . '<fonts count="4"><font><sz val="10"/><name val="Arial"/></font><font><b/><sz val="10"/><name val="Arial"/></font>'
            . '<font><b/><sz val="13"/><name val="Arial"/></font><font><sz val="10"/><color rgb="FF475569"/><name val="Arial"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE6F4F4"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="3"><border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left/><right/><top/><bottom style="thin"><color rgb="FF94A3B8"/></bottom><diagonal/></border>'
            . '<border><left/><right/><top style="thin"><color rgb="FF94A3B8"/></top><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="12">'
            . $xf(0, 0, 0, 0)                                   // 0 text
            . $xf(0, 1, 2, 1, 'vertical="center" wrapText="1"') // 1 header
            . $xf(4, 0, 0, 0)                                   // 2 amount
            . $xf(164, 0, 0, 0)                                 // 3 date
            . $xf(165, 0, 0, 0)                                 // 4 percent
            . $xf(0, 2, 0, 0)                                   // 5 title
            . $xf(0, 3, 0, 0)                                   // 6 sub title
            . $xf(0, 1, 0, 2)                                   // 7 total text
            . $xf(4, 1, 0, 2)                                   // 8 total amount
            . $xf(165, 1, 0, 2)                                 // 9 total percent
            . $xf(3, 0, 0, 0)                                   // 10 whole number
            . $xf(3, 1, 0, 2)                                   // 11 total whole number
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
