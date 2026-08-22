<?php

namespace App\Services;

class AnnouncementContent
{
    /**
     * The editor stores only a small allow-list of formatting tags. Attributes
     * are discarded so announcement content cannot carry executable markup.
     */
    public static function sanitizeEditorHtml(?string $message): string
    {
        $html = strip_tags((string) $message, '<p><div><br><strong><b><em><i><a><ul><li>');

        $html = preg_replace_callback(
            '/<\s*(\/?)\s*(p|div|br|strong|b|em|i|a|ul|li)\b[^>]*>/i',
            static function (array $matches): string {
                $closing = $matches[1] === '/';
                $tag = strtolower($matches[2]);
                $tag = match ($tag) {
                    'b' => 'strong',
                    'div' => 'p',
                    'i' => 'em',
                    default => $tag,
                };

                if ($tag === 'br') {
                    return $closing ? '' : '<br>';
                }

                if ($tag === 'a') {
                    if ($closing) {
                        return '</a>';
                    }

                    preg_match('/\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $matches[0], $hrefMatches);
                    $href = html_entity_decode(
                        $hrefMatches[1] ?? $hrefMatches[2] ?? $hrefMatches[3] ?? '',
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );
                    $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

                    if (! in_array($scheme, ['http', 'https'], true)) {
                        return '<a>';
                    }

                    return '<a href="' . e($href) . '" target="_blank" rel="noopener noreferrer">';
                }

                return '<' . ($closing ? '/' : '') . $tag . '>';
            },
            $html
        );

        return trim((string) $html);
    }

    /**
     * Render safe editor HTML or support the legacy Markdown stored by earlier
     * announcements. Raw user input is always escaped before Markdown parsing.
     */
    public static function toHtml(?string $message): string
    {
        if (preg_match('/<\/?(?:p|div|br|strong|b|em|i|a|ul|li)\b/i', (string) $message) === 1) {
            return self::sanitizeEditorHtml($message);
        }

        $formatInline = static function (string $line): string {
            $line = e($line);
            $line = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $line);

            return preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $line);
        };

        $lines = preg_split('/\r\n|\r|\n/', trim((string) $message));
        $html = '';
        $paragraph = [];
        $inList = false;
        $flushParagraph = static function () use (&$html, &$paragraph, $formatInline): void {
            if ($paragraph === []) {
                return;
            }

            $html .= '<p>' . implode('<br>', array_map($formatInline, $paragraph)) . '</p>';
            $paragraph = [];
        };

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $flushParagraph();
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^\s*[-*]\s+(.+)$/', $line, $matches)) {
                $flushParagraph();
                if (! $inList) {
                    $html .= '<ul>';
                    $inList = true;
                }
                $html .= '<li>' . $formatInline($matches[1]) . '</li>';
                continue;
            }

            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
            $paragraph[] = $line;
        }

        $flushParagraph();
        if ($inList) {
            $html .= '</ul>';
        }

        return $html;
    }

    public static function toPlainText(?string $message): string
    {
        $html = self::toHtml($message);
        $text = str_ireplace(['</p>', '</li>', '<br>'], "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+\n|\n{3,}/', "\n", $text);

        return trim((string) $text);
    }
}
