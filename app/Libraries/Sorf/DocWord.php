<?php

namespace App\Libraries\Sorf;

/**
 * A Special Order as a file Microsoft Word opens (.doc).
 *
 * It is a "single file web page" (MHTML): the document's HTML plus the
 * letterhead and footer images as parts of one file, which Word reads as a
 * normal document that can be edited and saved as .docx.
 */
class DocWord
{
    private const BOUNDARY = '----=_DRF_SpecialOrder';

    /**
     * @param bool $draft a DRAFT notice heads the document (no final release yet)
     */
    public function build(string $title, string $bodyHtml, bool $draft = false): string
    {
        if ($draft) {
            $bodyHtml = '<p style="text-align: center; border: 1.5pt solid #dc2626; color: #dc2626; font-weight: bold; padding: 4pt; margin-bottom: 12pt">'
                . 'DRAFT &mdash; NOT THE FINAL SPECIAL ORDER</p>' . $bodyHtml;
        }
        $css = (string) @file_get_contents(FCPATH . 'assets/css/drf-doc.css');
        // screen-only rules mean nothing to Word; the page setup below replaces them
        $css = preg_replace('/@media[^{]+\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/', '', $css);

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">'
            . '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>' . esc($title) . '</title>'
            . '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml><![endif]-->'
            . '<style>' . $css . "\n"
            . '@page WordSection1 { size: 21cm 29.7cm; margin: 1.2cm 1.8cm 1.2cm 1.8cm; }' . "\n"
            . 'div.WordSection1 { page: WordSection1; }' . "\n"
            . 'body { font-family: Arial, sans-serif; font-size: 11pt; }' . "\n"
            . 'td, th { font-family: Arial, sans-serif; font-size: 11pt; }' . "\n"
            . '.doc-fill { background: none; color: #000; }' . "\n"
            // Word ignores % margins and border-spacing: say the same things its way
            . 'p.doc-sign { margin-left: 9.5cm; margin-top: 18pt; margin-bottom: 12pt; text-align: center; }' . "\n"
            . 'p { margin-top: 0; margin-bottom: 8pt; }' . "\n"
            . 'table.doc-lines td { padding: 0 4pt; }' . "\n"
            . '</style></head><body><div class="WordSection1">'
            . '<p><img src="doc-header.png" width="640" height="74" alt=""></p>'
            . $bodyHtml
            . '<br><table width="100%" style="border-collapse: collapse"><tr>'
            . '<td style="font-size: 8pt; color: #808080; vertical-align: middle"><b><i>&ldquo;Fight Malnutrition with Accurate Data, Correct Information and Innovative Technologies&rdquo;</i></b><br>'
            . 'DOST Compound, General Santos Avenue,<br>Central Bicutan, Taguig City 1631<br>Tel. #: 8837-8113/8114 | Fax #: 837-2934<br>Email: dostfnri47@fnri.dost.gov.ph</td>'
            . '<td width="170" style="text-align: right; vertical-align: middle"><img src="doc-footer-logos.png" width="160" height="60" alt=""></td>'
            . '</tr></table></div></body></html>';

        $out = "MIME-Version: 1.0\r\n"
            . 'Content-Type: multipart/related; boundary="' . self::BOUNDARY . '"; type="text/html"' . "\r\n\r\n"
            . $this->part('text/html; charset="utf-8"', 'file:///C:/drf/document.htm', $html);

        foreach (['doc-header.png', 'doc-footer-logos.png'] as $image) {
            $bytes = @file_get_contents(FCPATH . 'assets/images/logos/' . $image);
            if ($bytes !== false) {
                $out .= $this->part('image/png', 'file:///C:/drf/' . $image, $bytes);
            }
        }
        return $out . '--' . self::BOUNDARY . "--\r\n";
    }

    private function part(string $type, string $location, string $content): string
    {
        return '--' . self::BOUNDARY . "\r\n"
            . 'Content-Location: ' . $location . "\r\n"
            . 'Content-Transfer-Encoding: base64' . "\r\n"
            . 'Content-Type: ' . $type . "\r\n\r\n"
            . chunk_split(base64_encode($content), 76, "\r\n") . "\r\n";
    }
}
