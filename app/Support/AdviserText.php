<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Turn an adviser reply into readable HTML.
 *
 * The model writes headings, numbered points and bold text in
 * Markdown. It used to be printed as one paragraph, so students
 * read walls of prose with literal asterisks in them.
 *
 * Everything is escaped first and only a small set of patterns
 * is allowed back, rather than running a full Markdown parser
 * over text a language model produced.
 */
class AdviserText
{
    public static function toHtml(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $prepared = str_replace("\r\n", "\n", e($text));

        /*
         * The model often runs several points onto one line,
         * separated only by a bullet. Without this they arrive
         * as a single paragraph with bullets buried inside it.
         *
         * The spacing is matched explicitly because the model
         * writes non-breaking spaces, which PHP's \s does not
         * match under /u without UCP.
         */
        $space = '[\s\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}\x{3000}]';

        $prepared = preg_replace(
            '/(?<!^)(?<!\n)'.$space.'+(\x{2022}|\x{00B7}|\x{25CF}|\x{25AA})'.$space.'+/u',
            "\n• ",
            $prepared
        ) ?? $prepared;

        $prepared = preg_replace(
            '/(?<!^)(?<!\n)'.$space.'+(\d+)\.'.$space.'+(?=[A-Z])/u',
            "\n$1. ",
            $prepared
        ) ?? $prepared;

        $lines = explode("\n", $prepared);

        $html = '';
        $paragraph = [];
        $list = [];
        $listTag = null;

        $flushParagraph = function () use (&$paragraph, &$html) {
            if ($paragraph === []) {
                return;
            }

            $html .= '<p>'
                .self::inline(implode(' ', $paragraph))
                .'</p>';

            $paragraph = [];
        };

        $flushList = function () use (&$list, &$listTag, &$html) {
            if ($list === []) {
                return;
            }

            $html .= '<'.$listTag.' class="adviser-list">'
                .implode('', $list)
                .'</'.$listTag.'>';

            $list = [];
            $listTag = null;
        };

        foreach ($lines as $line) {
            $line = trim(
                preg_replace('/^\x{00A0}+|\x{00A0}+$/u', '', $line)
                    ?? $line
            );

            if ($line === '') {
                $flushParagraph();
                $flushList();

                continue;
            }

            /*
             * Numbered and bulleted points are recognised line
             * by line. Requiring a whole block of them meant a
             * reply written as one run of lines collapsed into
             * a single paragraph.
             */
            if (preg_match('/^(\d+)[.)]\s+(.*)$/u', $line, $match)) {
                $flushParagraph();

                if ($listTag === 'ul') {
                    $flushList();
                }

                $listTag = 'ol';
                $list[] = '<li>'.self::inline($match[2]).'</li>';

                continue;
            }

            /*
             * The /u matters. Without it the bullet is read as
             * three separate bytes, so a line starting with one
             * never matched and every point stayed in a
             * paragraph.
             */
            if (preg_match('/^(?:-|\*|\x{2022}|\x{00B7}|\x{25CF}|\x{25AA})\s+(.*)$/u', $line, $match)) {
                $flushParagraph();

                if ($listTag === 'ol') {
                    $flushList();
                }

                $listTag = 'ul';
                $list[] = '<li>'.self::inline($match[1]).'</li>';

                continue;
            }

            $flushList();

            if (self::looksLikeHeading($line)) {
                $flushParagraph();

                $html .= '<h4 class="adviser-heading">'
                    .self::inline(trim($line, '*# '))
                    .'</h4>';

                continue;
            }

            $paragraph[] = $line;
        }

        $flushParagraph();
        $flushList();

        return $html;
    }

    private static function looksLikeHeading(string $line): bool
    {
        if (Str::startsWith($line, '#')) {
            return true;
        }

        $bare = trim($line, '*: ');

        return $bare !== ''
            && Str::length($bare) <= 60
            && ! Str::endsWith($line, ['.', '?', '!'])
            && (
                Str::startsWith($line, '**')
                || preg_match('/^[A-Z][A-Za-z &\'-]+:?$/', $bare) === 1
            );
    }

    /**
     * Bold only. Everything else stays as written.
     */
    private static function inline(string $text): string
    {
        $text = preg_replace(
            '/\*\*(.+?)\*\*/s',
            '<strong>$1</strong>',
            $text
        ) ?? $text;

        return preg_replace(
            '/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s',
            '<em>$1</em>',
            $text
        ) ?? $text;
    }
}
