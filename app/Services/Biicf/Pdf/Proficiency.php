<?php

namespace App\Services\Biicf\Pdf;

/**
 * The two scales a proficiency can be written on.
 *
 * A closed vocabulary, and a useful one twice over: it is what
 * makes the competency tables findable at all, since a column in
 * which every entry is one of these is a column of
 * proficiencies and nothing else in the book looks like that;
 * and it is what lets the tables be kept out of the way of
 * reading the text around them.
 *
 * Which scale a value is on also settles what kind of
 * competency it belongs to. Technical competencies are rated one
 * to five, soft skills on four named rungs.
 */
class Proficiency
{
    public const SCALES = [
        'technical' => ['1', '2', '3', '4', '5'],
        'soft_skill' => ['Basic', 'Intermediate', 'Advanced', 'Expert'],
    ];

    /**
     * How far apart two figures can be and still be in the same
     * column.
     *
     * They are centred in their column, so they wander by under
     * a point down the page; the columns themselves are
     * thirty-five points apart and more.
     */
    public const JITTER = 10.0;

    public static function written(string $text): bool
    {
        foreach (self::SCALES as $values) {
            if (in_array(trim($text), $values, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The runs that are figures in a table rather than text.
     *
     * A figure only counts as one when another lines up beneath
     * it. A lone "5" in a sentence is part of the sentence, and
     * dropping it would quietly reword the book.
     *
     * @param  array<int, TextRun>  $runs
     * @return array<string, true> keyed by the run's position
     */
    public static function inColumns(array $runs): array
    {
        $figures = array_values(array_filter(
            $runs,
            fn (TextRun $run) => self::written($run->text)
        ));

        usort($figures, fn (TextRun $a, TextRun $b) => $a->x <=> $b->x);

        $found = [];
        $group = [];

        $close = function (array $group) use (&$found) {
            if (count($group) < 2) {
                return;
            }

            foreach ($group as $run) {
                $found[round($run->x, 1).':'.round($run->y, 1)] = true;
            }
        };

        foreach ($figures as $run) {
            if ($group !== [] && $run->x - end($group)->x > self::JITTER) {
                $close($group);
                $group = [];
            }

            $group[] = $run;
        }

        $close($group);

        return $found;
    }
}
