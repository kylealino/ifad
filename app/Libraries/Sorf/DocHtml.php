<?php

namespace App\Libraries\Sorf;

/**
 * Cleans the HTML the document editor sends before it is stored.
 *
 * What HR types is shown to the requester, printed and exported to Word, so
 * only plain document markup survives: text, lists, tables and a short list
 * of formatting styles. Scripts, images, links, forms and event handlers
 * never reach the table, whatever the browser sent.
 */
class DocHtml
{
    public const MAX_BYTES = 400000;

    private const ALLOWED = [
        'p', 'br', 'div', 'span', 'b', 'strong', 'i', 'em', 'u', 's', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'hr', 'blockquote',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'colgroup', 'col',
    ];
    // removed together with everything inside them
    private const DROPPED = [
        'script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'base', 'head', 'title', 'noscript', 'template',
        'form', 'input', 'button', 'select', 'textarea', 'svg', 'math', 'img', 'picture', 'video', 'audio', 'canvas', 'frame', 'frameset',
    ];
    private const STYLES = [
        'text-align', 'font-weight', 'font-style', 'text-decoration', 'text-decoration-line', 'font-size', 'color',
        'width', 'vertical-align', 'margin-left', 'padding-left', 'text-indent', 'line-height',
    ];

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="doc-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = (new \DOMXPath($doc))->query('//div[@id="doc-root"]')->item(0);
        if ($root === null) {
            return '';
        }
        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return trim($out);
    }

    private static function walk(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }
            if (!$child instanceof \DOMElement) {
                // comments, processing instructions, CDATA
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROPPED, true)) {
                $node->removeChild($child);
                continue;
            }

            self::walk($child);

            if (!in_array($tag, self::ALLOWED, true)) {
                // an unknown wrapper (font, a, section...): keep what it holds, drop the tag
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            self::attributes($child, $tag);
        }
    }

    private static function attributes(\DOMElement $el, string $tag): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = $attr->value;
            $el->removeAttribute($attr->name);

            if (($name === 'colspan' || $name === 'rowspan') && in_array($tag, ['td', 'th'], true) && ctype_digit($value) && (int) $value >= 1 && (int) $value <= 20) {
                $el->setAttribute($name, $value);
            } elseif ($name === 'class') {
                // only the document's own classes (doc-table, doc-fill, ...)
                $classes = array_filter(preg_split('/\s+/', $value), fn ($c) => preg_match('/^doc-[a-z0-9-]{1,30}$/', $c));
                if (!empty($classes)) {
                    $el->setAttribute('class', implode(' ', $classes));
                }
            } elseif ($name === 'style') {
                $style = self::style($value);
                if ($style !== '') {
                    $el->setAttribute('style', $style);
                }
            }
        }
    }

    private static function style(string $css): string
    {
        $keep = [];
        foreach (explode(';', $css) as $rule) {
            $parts = explode(':', $rule, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $prop = strtolower(trim($parts[0]));
            $value = trim($parts[1]);
            if (in_array($prop, self::STYLES, true) && strlen($value) <= 60 && preg_match('/^[a-z0-9#%., ()\-]+$/i', $value) && !preg_match('/url|expression|javascript/i', $value)) {
                $keep[] = $prop . ': ' . $value;
            }
        }
        return implode('; ', $keep);
    }
}
