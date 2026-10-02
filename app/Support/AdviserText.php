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

        $nested = [];

        /*
         * Bullets that follow a numbered point belong to it. The
         * model writes "4. Practical next steps:" and then its
         * sub-points as bullets, so closing the numbered list and
         * starting a separate bulleted one puts them beside the
         * point they expand on rather than under it.
         */
        $flushNested = function () use (&$nested, &$list) {
            if ($nested === [] || $list === []) {
                return;
            }

            $lastIndex = array_key_last($list);

            $list[$lastIndex] = preg_replace(
                '/<\/li>$/',
                '<ul class="adviser-list adviser-sublist">'
                    .implode('', $nested)
                    .'</ul></li>',
                $list[$lastIndex]
            ) ?? $list[$lastIndex];

            $nested = [];
        };

        /*
         * The number the model gave the first item. A list broken
         * by a paragraph has to resume where it left off, or the
         * second half starts again at one.
         */
        $listStart = null;

        $flushList = function () use (
            &$list,
            &$listTag,
            &$listStart,
            &$html,
            $flushNested
        ) {
            $flushNested();

            if ($list === []) {
                return;
            }

            $html .= '<'.$listTag.' class="adviser-list"'
                .(
                    $listTag === 'ol'
                        && $listStart !== null
                        && $listStart > 1
                            ? ' start="'.$listStart.'"'
                            : ''
                )
                .'>'
                .implode('', $list)
                .'</'.$listTag.'>';

            $list = [];
            $listTag = null;
            $listStart = null;
        };

        foreach ($lines as $line) {
            $line = trim(
                preg_replace('/^\x{00A0}+|\x{00A0}+$/u', '', $line)
                    ?? $line
            );

            /*
             * A blank line ends a paragraph but not a list. The
             * model puts one between numbered sections, and
             * closing the list there started the next section at
             * one again. Real prose or a heading still closes it.
             */
            if ($line === '') {
                $flushParagraph();

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

                /*
                 * Closes any sub-points gathered under the
                 * previous number before starting the next one.
                 */
                $flushNested();

                if ($listTag !== 'ol' || $list === []) {
                    $listStart = (int) $match[1];
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

                /*
                 * Inside a numbered list these are sub-points of
                 * the number above, not a list of their own.
                 */
                if ($listTag === 'ol' && $list !== []) {
                    $nested[] = '<li>'
                        .self::inline($match[1])
                        .'</li>';

                    continue;
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
