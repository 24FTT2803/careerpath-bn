<?php

namespace App\Services\Biicf;

/**
 * Showing what actually differs between two values.
 *
 * A difference is presented as two panes, what the framework
 * holds and what the edition says, and most of the time they
 * are nearly the same: a list of twenty courses where one has
 * gained a hyphen, a paragraph where the book added a full
 * stop. Read side by side, finding that takes a careful minute
 * per difference and there are over a hundred of them — which
 * is how a review stops being read and starts being clicked
 * through.
 *
 * So the panes are marked: what is only on the left, and what
 * is only on the right. Whole lines where a line was added or
 * dropped, and single words where a line was reworded, since
 * "a course was renamed" and "a course was replaced" are
 * different things and only the words show which.
 *
 * Marking is a reading aid and nothing else. It never changes
 * what is stored, and a value with no marks on it is a value
 * that genuinely matches.
 */
class ValueDiff
{
    /**
     * How alike two lines must be to count as one line reworded
     * rather than one dropped and another added.
     *
     * Below this they are shown as a removal and an addition,
     * which is what they are: "Node.js" did not become
     * "Oracle Database Administrator (DBA)".
     */
    private const REWORDED_ABOVE = 0.55;

    /**
     * The two panes, as HTML, with the differences marked.
     *
     * @return array{current: string, proposed: string}
     */
    public static function panes(mixed $current, mixed $proposed): array
    {
        $left = self::lines($current);
        $right = self::lines($proposed);

        if ($left === [] && $right === []) {
            return ['current' => '—', 'proposed' => '—'];
        }

        $leftOut = [];
        $rightOut = [];

        $gone = [];
        $came = [];

        $flush = function () use (&$gone, &$came, &$leftOut, &$rightOut) {
            foreach (self::pairUp($gone, $came) as $pair) {
                [$was, $now] = $pair;

                if ($was !== null && $now !== null) {
                    [$a, $b] = self::words($was, $now);
                    $leftOut[] = $a;
                    $rightOut[] = $b;

                    continue;
                }

                if ($was !== null) {
                    $leftOut[] = self::mark($was, 'gone');
                }

                if ($now !== null) {
                    $rightOut[] = self::mark($now, 'came');
                }
            }

            $gone = [];
            $came = [];
        };

        foreach (self::align($left, $right) as [$op, $text]) {
            if ($op === '=') {
                $flush();
                $leftOut[] = e($text);
                $rightOut[] = e($text);

                continue;
            }

            if ($op === '-') {
                $gone[] = $text;

                continue;
            }

            $came[] = $text;
        }

        $flush();

        return [
            'current' => $leftOut === [] ? '—' : implode("\n", $leftOut),
            'proposed' => $rightOut === [] ? '—' : implode("\n", $rightOut),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function lines(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_map(
                fn ($line) => is_scalar($line) ? (string) $line : (string) json_encode($line),
                $value
            ));
        }

        return preg_split('/\r?\n/', (string) $value) ?: [];
    }

    /**
     * Line up two lists, saying which lines they share.
     *
     * @param  array<int, string>  $a
     * @param  array<int, string>  $b
     * @return array<int, array{0: string, 1: string}>
     */
    private static function align(array $a, array $b): array
    {
        $rows = count($a);
        $columns = count($b);

        /* Longest common subsequence, which is small here: a
         * difference is a handful of lines, never a document. */
        $table = array_fill(0, $rows + 1, array_fill(0, $columns + 1, 0));

        for ($i = $rows - 1; $i >= 0; $i--) {
            for ($j = $columns - 1; $j >= 0; $j--) {
                $table[$i][$j] = $a[$i] === $b[$j]
                    ? $table[$i + 1][$j + 1] + 1
                    : max($table[$i + 1][$j], $table[$i][$j + 1]);
            }
        }

        $ops = [];
        $i = 0;
        $j = 0;

        while ($i < $rows && $j < $columns) {
            if ($a[$i] === $b[$j]) {
                $ops[] = ['=', $a[$i]];
                $i++;
                $j++;
            } elseif ($table[$i + 1][$j] >= $table[$i][$j + 1]) {
                $ops[] = ['-', $a[$i]];
                $i++;
            } else {
                $ops[] = ['+', $b[$j]];
                $j++;
            }
        }

        while ($i < $rows) {
            $ops[] = ['-', $a[$i]];
            $i++;
        }

        while ($j < $columns) {
            $ops[] = ['+', $b[$j]];
            $j++;
        }

        return $ops;
    }

    /**
     * Match up the lines that went with the lines that arrived,
     * where one is clearly a rewording of the other.
     *
     * @param  array<int, string>  $gone
     * @param  array<int, string>  $came
     * @return array<int, array{0: string|null, 1: string|null}>
     */
    private static function pairUp(array $gone, array $came): array
    {
        $pairs = [];
        $taken = [];

        foreach ($gone as $was) {
            $best = null;
            $score = self::REWORDED_ABOVE;

            foreach ($came as $at => $now) {
                if (isset($taken[$at])) {
                    continue;
                }

                similar_text($was, $now, $percent);

                if ($percent / 100 > $score) {
                    $score = $percent / 100;
                    $best = $at;
                }
            }

            if ($best === null) {
                $pairs[] = [$was, null];

                continue;
            }

            $taken[$best] = true;
            $pairs[] = [$was, $came[$best]];
        }

        foreach ($came as $at => $now) {
            if (! isset($taken[$at])) {
                $pairs[] = [null, $now];
            }
        }

        return $pairs;
    }

    /**
     * Mark the words that differ between two versions of a line.
     *
     * @return array{0: string, 1: string}
     */
    private static function words(string $was, string $now): array
    {
        $a = preg_split('/(\s+)/u', $was, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $b = preg_split('/(\s+)/u', $now, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        $left = '';
        $right = '';

        foreach (self::align($a, $b) as [$op, $text]) {
            match ($op) {
                '=' => [$left .= e($text), $right .= e($text)],
                '-' => $left .= self::mark($text, 'gone'),
                '+' => $right .= self::mark($text, 'came'),
            };
        }

        return [$left, $right];
    }

    /**
     * Whitespace is marked as a visible space, so that a
     * difference which is only a space is still something you
     * can see.
     */
    private static function mark(string $text, string $as): string
    {
        if ($text === '') {
            return '';
        }

        if (trim($text) === '') {
            return '<mark class="diff-'.$as.' diff-space">'.e($text).'</mark>';
        }

        return '<mark class="diff-'.$as.'">'.e($text).'</mark>';
    }
}
