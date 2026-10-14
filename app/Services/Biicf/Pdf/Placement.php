<?php

namespace App\Services\Biicf\Pdf;

/**
 * Where in the book a value was read from.
 *
 * The reader knows this for every value it extracts and, until
 * now, threw all of it away: a page of positioned text went in
 * and a list of strings came out. An administrator reviewing
 * "the book says Advanced, the framework says Intermediate" was
 * then left to find the sentence in three hundred pages to see
 * which of the two was right.
 *
 * So the page and the printed position travel with the value,
 * and the review screen can open the book at the place and draw
 * a box around the words.
 *
 * Positions are recorded exactly as the reader was handed them,
 * in the book's own units and nothing else: whoever draws the
 * box knows how its own viewer measures a page, and a reader
 * that converted would only be guessing on their behalf. The
 * spans carry their text as well, which is what makes a match
 * possible at all when two readers of the same PDF disagree
 * about the origin by a page height.
 */
readonly class Placement
{
    /**
     * @param  int  $page  the page as the reader was handed it
     * @param  array<int, array{x: float, y: float, text: string}>  $spans
     */
    public function __construct(
        public int $page,
        public array $spans,
    ) {}

    /**
     * The place a run of printed lines occupies.
     *
     * Lines from more than one page keep only the first page's,
     * since a box is drawn on one page at a time and the first
     * is where a reader looking for the value would start. A
     * section broken over a page break is the common case, not
     * the exception.
     *
     * @param  array<int, PageLine>  $lines
     */
    public static function ofLines(array $lines): ?self
    {
        $lines = array_values(array_filter(
            $lines,
            fn (PageLine $line) => $line->page !== null && ! $line->isBlank()
        ));

        if ($lines === []) {
            return null;
        }

        $page = $lines[0]->page;

        $spans = [];

        foreach ($lines as $line) {
            if ($line->page !== $page) {
                continue;
            }

            /*
             * The line's own runs, not its indent: a line the
             * reader marked as a heading within its column has
             * had its indent replaced by a flag, and a flag is
             * not a position to draw a box at.
             */
            $spans[] = [
                'x' => $line->runs[0]->x ?? $line->indent,
                'y' => $line->y,
                'text' => $line->text,
            ];
        }

        return new self((int) $page, self::capped($spans));
    }

    /**
     * The place a handful of runs occupies, on a page named by
     * the caller.
     *
     * Used for the block of labels at the head of a role, which
     * is read by position across the whole page rather than
     * within a column, and so never becomes a PageLine at all.
     *
     * @param  array<int, TextRun>  $runs
     */
    public static function ofRuns(int $page, array $runs): ?self
    {
        $spans = [];

        foreach ($runs as $run) {
            if ($run->isBlank()) {
                continue;
            }

            $spans[] = ['x' => $run->x, 'y' => $run->y, 'text' => $run->text];
        }

        if ($spans === []) {
            return null;
        }

        return new self($page, self::capped($spans));
    }

    /**
     * One span, built by hand.
     *
     * @param  array{x: float, y: float, text: string}  $span
     */
    public static function at(int $page, array $span): self
    {
        return new self($page, [$span]);
    }

    /**
     * @return array{page: int, spans: array<int, array{x: float, y: float, text: string}>}
     */
    public function toArray(): array
    {
        return ['page' => $this->page, 'spans' => $this->spans];
    }

    /**
     * How many spans one placement keeps.
     *
     * A role's critical work functions run to sixty lines, and
     * every one of them stored against every field of every role
     * is a column of JSON larger than the framework it
     * describes. The first few are what a box needs to be drawn
     * around: they say where on the page to look, and the rest
     * of the section is underneath them.
     */
    private const SPAN_LIMIT = 12;

    /**
     * @param  array<int, array{x: float, y: float, text: string}>  $spans
     * @return array<int, array{x: float, y: float, text: string}>
     */
    private static function capped(array $spans): array
    {
        return array_slice($spans, 0, self::SPAN_LIMIT);
    }
}
