<?php

namespace App\Services\Biicf\Pdf;

/**
 * One piece of text, and where it sits on the page.
 *
 * This is everything a PDF text reader actually gives us: a
 * string and the point it starts at. Not its colour, not its
 * size, and not the lines ruled around it — the first reading of
 * this book leaned on all three, and none of them survive a
 * position-only reader.
 *
 * So the rules downstream are built on what is here, and the
 * book turns out to carry its structure in exactly these terms:
 * a bullet is its own run sitting a fixed step to the left of
 * the text it opens, a column is a cluster of starting
 * positions, and a continuation is a line that begins where the
 * text does rather than where the bullet does.
 *
 * Coordinates are top-down — y grows downward, as it reads —
 * because a reader that has to flip the page in its head gets
 * the comparisons wrong.
 */
readonly class TextRun
{
    public function __construct(
        public string $text,
        public float $x,
        public float $y,
    ) {}

    /**
     * @param  array{t: string, x: int|float, y: int|float}  $row
     */
    public static function from(array $row): self
    {
        return new self(trim($row['t']), (float) $row['x'], (float) $row['y']);
    }

    /**
     * The glyph the book opens every bullet with.
     *
     * It survives as text, which is the single most useful thing
     * about this book's typesetting: bullets do not have to be
     * inferred from indentation or colour, they are written
     * down.
     */
    public const BULLET = '▷';

    public function isBullet(): bool
    {
        return $this->text === self::BULLET;
    }

    public function isBlank(): bool
    {
        return trim($this->text) === '';
    }

    /**
     * Whether this run sits on the same printed line as another.
     *
     * Runs typeset as one line come back a few tenths of a point
     * apart, so this is a tolerance rather than an equality.
     */
    public function sharesLineWith(self $other, float $tolerance = 2.0): bool
    {
        return abs($this->y - $other->y) <= $tolerance;
    }
}
