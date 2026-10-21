<?php

namespace App\Services\Biicf\Pdf;

/**
 * One printed line, within one column.
 *
 * What matters about a line is not only what it says but where
 * it begins, because that is what tells a bullet from the line
 * continuing it. The book is consistent about this: a bullet
 * glyph sits roughly fourteen points to the left of the text it
 * opens, and every line wrapping under that bullet begins at the
 * text's position rather than the bullet's.
 *
 * So three kinds of line, and the whole of the reading rests on
 * telling them apart: one that opens a bullet, one that
 * continues the bullet above it, and one that is flush left and
 * opens nothing — a heading.
 */
readonly class PageLine
{
    /**
     * @param  array<int, TextRun>  $runs
     * @param  int|null  $page  the page this line is printed on, as the
     *                          reader was handed it — kept so a value
     *                          stored in the framework can be pointed back
     *                          at the place in the book it was read from
     */
    public function __construct(
        public string $text,
        public float $indent,
        public float $y,
        public bool $opensBullet,
        public array $runs,
        public ?int $page = null,
    ) {}

    /**
     * @param  array<int, TextRun>  $runs  every run sharing one printed line, in reading order
     */
    public static function of(array $runs, ?int $page = null): self
    {
        usort($runs, fn (TextRun $a, TextRun $b) => $a->x <=> $b->x);

        $opens = $runs !== [] && $runs[0]->isBullet();

        /*
         * The bullet glyph is dropped from the text. It is
         * structure, not wording, and leaving it in would put a
         * stray character at the front of every stored sentence.
         */
        $words = [];

        foreach ($runs as $run) {
            if ($run->isBullet()) {
                continue;
            }

            $words[] = trim($run->text);
        }

        $spoken = array_values(array_filter($words, fn (string $word) => $word !== ''));

        /*
         * Indent is where the words start, never where the
         * bullet does — otherwise a bullet line and the line
         * wrapping under it would read as different indents and
         * the fold below would never join them.
         */
        $firstSpoken = null;

        foreach ($runs as $run) {
            if (! $run->isBullet() && trim($run->text) !== '') {
                $firstSpoken = $run;
                break;
            }
        }

        return new self(
            text: implode(' ', $spoken),
            indent: $firstSpoken?->x ?? ($runs[0]->x ?? 0.0),
            y: $runs[0]->y ?? 0.0,
            opensBullet: $opens,
            runs: $runs,
            page: $page,
        );
    }

    public function isBlank(): bool
    {
        return trim($this->text) === '';
    }

    /**
     * Whether the line ends mid-sentence.
     *
     * Only ever used to describe a line, never to decide whether
     * the next one continues it. Deciding that by wording is how
     * thirty-four wrapped course names were once stored as
     * separate courses; indentation decides it now.
     */
    public function looksUnfinished(): bool
    {
        return ! preg_match('/[.!?:;]$/', rtrim($this->text));
    }
}
