<?php

namespace App\Services\Biicf;

/**
 * Deciding when two spellings are the same competency.
 *
 * The book is not consistent with itself, let alone with what
 * the framework already holds. One page prints "Creativity &
 * Innovation" where another says "Creativity and Innovation";
 * "Systems Integration" and "System Integration" both appear;
 * "Decision-making / Judgement", "Decision-making/ Judgement"
 * and "Decision-Making" are three spellings of one thing.
 *
 * Read literally, every one of those is a competency the
 * framework has never heard of, and the edition proposes adding
 * it and removing the one it already has — two entries to think
 * about, over an ampersand. Forty-odd roles' worth of that is
 * what makes a review unreadable.
 *
 * Most of it is mechanical and is handled by rule: an ampersand
 * is "and", a plural is its singular, an abbreviation in
 * brackets is not part of the name. What is left is a short
 * list of pairs that only a reader can match, written down
 * here where it can be argued with.
 *
 * Deliberately not a general-purpose fuzzy match. Two names
 * that merely look similar are two competencies until somebody
 * says otherwise — "Data Analytics" and "Digital Analytics" are
 * different things, and a framework that quietly merged them
 * would be telling students the wrong thing about both.
 */
class CompetencyName
{
    /**
     * Pairs no rule could be expected to find.
     *
     * Keyed by the book's spelling, lower-cased; the value is
     * the name the framework knows it by.
     *
     * "Data Ethics and Governance" is deliberately absent. The
     * framework holds "Data Governance", and the two are close
     * enough to look like a rename — but ethics is a subject of
     * its own, and treating the book as having merely reworded
     * something would hide a competency it may have added. It
     * is better raised for a decision.
     *
     * @var array<string, string>
     */
    private const SAME = [
        'data analysis' => 'Data Analytics',
        'decision-making / judgement' => 'Decision-Making',
        'decision-making/ judgement' => 'Decision-Making',
        'infrastructure strategy, deployment and support' => 'Infrastructure Strategy, Development and Support',
        'marketing communications plan development' => 'Marketing Communication Plan',
        'network administrative and maintenance' => 'Network Administration and Maintenance',
        'results/achievement orientation' => 'Results Orientation',
    ];

    /**
     * A name reduced to what two spellings of it have in common.
     *
     * Two names matching here are the same competency; two that
     * do not are two competencies.
     */
    public static function key(string $name): string
    {
        $name = self::SAME[mb_strtolower(trim($name))] ?? $name;

        $name = str_replace('&', ' and ', mb_strtolower($name));

        /* An abbreviation in brackets is a gloss, not the name. */
        $name = (string) preg_replace('/\([^)]*\)/u', ' ', $name);

        $name = (string) preg_replace('/[^a-z ]/u', ' ', $name);

        $words = [];

        foreach (preg_split('/\s+/u', trim($name)) ?: [] as $word) {
            if ($word === '') {
                continue;
            }

            /*
             * "Systems Integration" and "System Integration",
             * "Applications Development" and "Application
             * Development". The book uses both throughout and
             * means one thing by each pair.
             */
            $words[] = rtrim($word, 's');
        }

        return implode('', $words);
    }

    public static function same(string $one, string $other): bool
    {
        return self::key($one) === self::key($other);
    }
}
