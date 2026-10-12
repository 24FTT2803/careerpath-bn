<?php

namespace App\Services\Biicf\Pdf;

/**
 * One page of the book, sorted into columns and lines.
 *
 * The book is set three columns to a page and the text runs
 * across them, so a page read straight down the middle comes out
 * interleaved — which is how one role's duties once ended up
 * filed under another role entirely. Columns first, then lines
 * within a column, is the only order that reads it correctly.
 *
 * Columns are found rather than assumed. They sit at different
 * positions on different pages — the left column starts at 39 on
 * one page, 42.5 on the next — so hardcoding would read most of
 * the book and quietly mangle the rest.
 */
class PageLayout
{
    /**
     * How far apart two starting positions must be to belong to
     * different columns.
     *
     * Within a column, positions vary by about twenty points: a
     * bullet sits fourteen to the left of its text, and headings
     * sit a few points off again. Between columns the step is
     * well over two hundred. Sixty separates the two cases with
     * room to spare in both directions.
     */
    private const COLUMN_GAP = 60.0;

    /** How far apart two runs can be and still share a printed line. */
    private const LINE_TOLERANCE = 2.0;

    /**
     * Where the text block starts, at the earliest.
     *
     * The book carries a decorative mark in the outer margin,
     * which decodes as a stray letter. It is close enough to the
     * first column to be swept into it, and once there it lands
     * in the middle of the role's title — "Chief Technology w
     * Officer".
     *
     * Measured rather than assumed: across all 316 pages exactly
     * five runs begin before this point, and every one of them
     * is that mark. No line of the book's text begins before
     * x=32.
     */
    private const TEXT_BLOCK_STARTS = 25.0;

    /**
     * The furniture every page carries, which is not the book's
     * text.
     *
     * The running foot holds the book's title and page number,
     * and the corner of each page carries a button back to the
     * contents. Both sit inside the text columns, so neither can
     * be dropped by position — left in, the button lands in the
     * middle of a sentence: "holding full executive Go to Table
     * of Contents accountability".
     */
    private const FURNITURE = [
        '/Brunei\s+ICT\s+Industry\s+Competency\s+Framework/i',
        '/^\s*Go\s+to\s+Table\s+of\s+Contents\s*$/i',
    ];

    /** @var array<int, array<int, PageLine>> columns, left to right */
    private array $columns;

    /**
     * @param  array<int, TextRun>  $runs
     */
    public function __construct(
        private readonly array $runs,
        public readonly int $number,
    ) {
        $this->columns = $this->sort($this->strip($runs));
    }

    /**
     * @param  array<int, array{t: string, x: int|float, y: int|float}>  $rows
     */
    public static function from(array $rows, int $number): self
    {
        return new self(array_map(fn (array $row) => TextRun::from($row), $rows), $number);
    }

    /**
     * @return array<int, array<int, PageLine>>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * Every line on the page in reading order: each column in
     * full, then the next.
     *
     * @return array<int, PageLine>
     */
    public function lines(): array
    {
        return array_merge(...array_values($this->columns)) ?: [];
    }

    /**
     * The runs as given, before columns — for the few things read
     * by position across the whole page rather than within a
     * column, like the label-and-value block at the top of a role.
     *
     * @return array<int, TextRun>
     */
    public function runs(): array
    {
        return $this->strip($this->runs);
    }

    public function isEmpty(): bool
    {
        return $this->lines() === [];
    }

    /**
     * Drop the running foot.
     *
     * Every page carries the book's title and page number along
     * the bottom. Left in, it becomes a stray bullet on whatever
     * section happened to end that page.
     *
     * Dropped by what it says rather than where it sits, because
     * its position moves between the left and right pages of a
     * spread.
     *
     * @param  array<int, TextRun>  $runs
     * @return array<int, TextRun>
     */
    private function strip(array $runs): array
    {
        return array_values(array_filter($runs, function (TextRun $run) {
            if ($run->isBlank()) {
                return false;
            }

            if ($run->x < self::TEXT_BLOCK_STARTS) {
                return false;
            }

            foreach (self::FURNITURE as $pattern) {
                if (preg_match($pattern, $run->text)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Group runs into columns, and each column into lines.
     *
     * @param  array<int, TextRun>  $runs
     * @return array<int, array<int, PageLine>>
     */
    private function sort(array $runs): array
    {
        if ($runs === []) {
            return [];
        }

        /*
         * The competency tables' figures take no part in this.
         *
         * They are cells laid out across the page rather than
         * text running down a column, and they sit between the
         * columns: on one page the figures land at x=500 and the
         * training courses beside them at x=559, fifty-eight
         * points apart, which is close enough to chain two
         * columns into one. A proficiency and a course name
         * printed on the same line then merge into a single
         * line that reads as neither — "Advanced IBM Certified
         * Data Architect" — and is dropped. That cost
         * fifty-five courses across seventeen roles, and put
         * stray figures into the entry requirements beside the
         * three-column tables.
         *
         * They are read separately, by position, where their
         * being a table is the point.
         */
        $cells = Proficiency::inColumns($runs);

        $runs = array_values(array_filter(
            $runs,
            fn (TextRun $run) => ! isset($cells[round($run->x, 1).':'.round($run->y, 1)])
        ));

        if ($runs === []) {
            return [];
        }

        $bands = $this->bands($runs);

        $columns = [];

        foreach ($bands as $index => $band) {
            $within = array_values(array_filter(
                $runs,
                fn (TextRun $run) => $run->x >= $band['from'] && $run->x <= $band['to']
            ));

            $lines = $this->lines_($within);

            if ($lines !== []) {
                $columns[$index] = $lines;
            }
        }

        return array_values($columns);
    }

    /**
     * Where the columns are, by clustering the positions lines
     * start at.
     *
     * @param  array<int, TextRun>  $runs
     * @return array<int, array{from: float, to: float}>
     */
    private function bands(array $runs): array
    {
        $starts = array_map(fn (TextRun $run) => $run->x, $runs);
        sort($starts);

        $bands = [];
        $from = $starts[0];
        $previous = $starts[0];

        foreach ($starts as $start) {
            if ($start - $previous > self::COLUMN_GAP) {
                $bands[] = ['from' => $from, 'to' => $previous];
                $from = $start;
            }

            $previous = $start;
        }

        $bands[] = ['from' => $from, 'to' => $previous];

        return $bands;
    }

    /**
     * Group a column's runs into printed lines.
     *
     * @param  array<int, TextRun>  $runs
     * @return array<int, PageLine>
     */
    private function lines_(array $runs): array
    {
        usort($runs, fn (TextRun $a, TextRun $b) => [$a->y, $a->x] <=> [$b->y, $b->x]);

        $lines = [];
        $current = [];

        foreach ($runs as $run) {
            if ($current !== [] && ! $run->sharesLineWith($current[0], self::LINE_TOLERANCE)) {
                $lines[] = PageLine::of($current);
                $current = [];
            }

            $current[] = $run;
        }

        if ($current !== []) {
            $lines[] = PageLine::of($current);
        }

        return array_values(array_filter($lines, fn (PageLine $line) => ! $line->isBlank()));
    }
}
