<?php
namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Pembersih HTML untuk isi berita (QA12): hanya tag yang diizinkan yang lolos.
 * Diizinkan: p, h2, h3, ul, ol, li, strong, em, a, blockquote, br, img.
 * Semua atribut dibuang kecuali href (a) dan src/alt (img). Skrip, gaya, iframe, dan
 * atribut on* tidak pernah lolos. Dipakai saat menyimpan dari admin dan saat impor WordPress.
 */
class Html
{
    /** Tag yang disimpan apa adanya. */
    protected const KEEP = ['p', 'h2', 'h3', 'ul', 'ol', 'li', 'strong', 'em', 'a', 'blockquote', 'br', 'img'];

    /** Tag yang diganti ke tag lain. */
    protected const RENAME = [
        'b'   => 'strong', 'i'  => 'em', 'h1'     => 'h2', 'h4'    => 'h3', 'h5' => 'h3', 'h6' => 'h3',
        'div' => 'p', 'section' => 'p', 'article' => 'p', 'figure' => 'p',
    ];

    /** Tag yang dibuang beserta isinya. */
    protected const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'noscript', 'svg', 'video', 'audio', 'link', 'meta', 'head', 'title', 'figcaption'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $doc  = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        self::walk($doc, $body);
        self::wrapLooseText($doc, $body);

        $out = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        // Buang paragraf kosong dan rapikan spasi.
        $out = preg_replace('#<p>(\s|&nbsp;|<br\s*/?>)*</p>#iu', '', $out);
        $out = preg_replace('#<(h2|h3|li|blockquote)>(\s|&nbsp;|<br\s*/?>)*</\1>#iu', '', $out);

        return trim(preg_replace('/\n{2,}/', "\n", $out));
    }

    /** Teks polos (untuk hitung kata, ringkasan, dan pencarian). */
    public static function text(?string $html): string
    {
        $spaced = preg_replace('#</(p|h[1-6]|li|blockquote)>|<br\s*/?>#i', ' ', (string) $html);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    protected static function walk(DOMDocument $doc, DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($doc, $child);

            if (isset(self::RENAME[$tag])) {
                $child = self::rename($doc, $child, self::RENAME[$tag]);
                $tag   = strtolower($child->tagName);
            }

            if (! in_array($tag, self::KEEP, true)) {
                // Tag tak dikenal (span, font, dst.): buang tag, pertahankan isinya.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, $tag);

            // Gambar tanpa sumber yang sah dibuang; tautan tanpa href dilepas.
            if ($tag === 'img' && ! $child->hasAttribute('src')) {
                $node->removeChild($child);
            } elseif ($tag === 'a' && ! $child->hasAttribute('href')) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
            }
        }
    }

    protected static function rename(DOMDocument $doc, DOMElement $el, string $to): DOMElement
    {
        $new = $doc->createElement($to);
        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }
        $el->parentNode->replaceChild($new, $el);

        return $new;
    }

    protected static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $keep = [];

        if ($tag === 'a') {
            $href = self::safeUrl($el->getAttribute('href'), ['http', 'https', 'mailto', 'tel'], true);
            if ($href !== null) {
                $keep['href'] = $href;
                if (preg_match('#^https?://#i', $href) && ! self::isOwnHost($href)) {
                    $keep['target'] = '_blank';
                    $keep['rel']    = 'noopener noreferrer';
                }
            }
        } elseif ($tag === 'img') {
            $src = self::safeUrl($el->getAttribute('src'), ['http', 'https'], true);
            if ($src !== null) {
                $keep['src']     = $src;
                $keep['alt']     = trim($el->getAttribute('alt'));
                $keep['loading'] = 'lazy';
            }
        }

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttributeNode($attr);
        }
        foreach ($keep as $name => $value) {
            $el->setAttribute($name, $value);
        }
    }

    /** Hanya skema yang diizinkan, atau path relatif ke situs sendiri. */
    protected static function safeUrl(string $url, array $schemes, bool $allowRelative): ?string
    {
        $url = trim($url);
        // Buang karakter kontrol yang biasa dipakai menyamarkan "javascript:".
        $probe = preg_replace('/[\x00-\x20\x7f]+/u', '', $url);

        if ($probe === '') {
            return null;
        }

        if (preg_match('#^([a-z][a-z0-9+.\-]*):#i', $probe, $m)) {
            return in_array(strtolower($m[1]), $schemes, true) ? $url : null;
        }

        if ($allowRelative && (str_starts_with($probe, '/') && ! str_starts_with($probe, '//') || str_starts_with($probe, '#'))) {
            return $url;
        }

        return null;
    }

    protected static function isOwnHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $own  = function_exists('request') && request() ? request()->getHost() : null;

        return $host && $own && strcasecmp($host, $own) === 0;
    }

    /** Teks atau tag inline yang tergeletak di akar dibungkus <p>. */
    protected static function wrapLooseText(DOMDocument $doc, DOMElement $body): void
    {
        $inline = ['strong', 'em', 'a', 'br', 'img'];
        $run    = [];

        $flush = function () use (&$run, $doc, $body) {
            if (! $run) {
                return;
            }
            $hasContent = false;
            foreach ($run as $n) {
                if (($n->nodeType === XML_TEXT_NODE && trim($n->textContent) !== '') || $n instanceof DOMElement && $n->tagName !== 'br') {
                    $hasContent = true;
                }
            }
            if ($hasContent) {
                $p = $doc->createElement('p');
                $body->insertBefore($p, $run[0]);
                foreach ($run as $n) {
                    $p->appendChild($n);
                }
            } else {
                foreach ($run as $n) {
                    $body->removeChild($n);
                }
            }
            $run = [];
        };

        foreach (iterator_to_array($body->childNodes) as $child) {
            $isLoose = $child->nodeType === XML_TEXT_NODE || ($child instanceof DOMElement && in_array($child->tagName, $inline, true));
            if ($isLoose) {
                $run[] = $child;
            } else {
                $flush();
            }
        }
        $flush();
    }
}
