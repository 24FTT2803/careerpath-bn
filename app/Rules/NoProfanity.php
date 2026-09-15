<?php

namespace App\Rules;

use App\Support\ProfanityList;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects text containing blocked profanity.
 *
 * Intended only for identity and label fields that appear on
 * reports, dashboards and cards. It is deliberately NOT applied
 * to narrative fields such as bio, vision_statement or project
 * descriptions, because academic and career vocabulary collides
 * with blocklists too often to make filtering those fields safe.
 */
class NoProfanity implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        if (! $this->containsProfanity($value)) {
            return;
        }

        $fail(
            'The :attribute field contains language that is not allowed.'
        );
    }

    /**
     * Determine whether the given text contains blocked language.
     */
    private function containsProfanity(string $value): bool
    {
        $normalised = $this->normalise($value);

        /*
         * Substring match against the outright blocked list using
         * the normalised form. This catches words that already
         * appear whole, such as "asshole" and "bullshit".
         */
        if ($this->matchesBlocked($normalised)) {
            return true;
        }

        /*
         * Also try the same text with runs of the same character
         * collapsed. This catches evasions such as "fuuuuuck"
         * and "fuuck" without breaking legitimate double letters
         * in words like "asshole".
         */
        $collapsed = preg_replace(
            '/(.)\1+/u',
            '$1',
            $normalised
        ) ?? $normalised;

        if ($collapsed !== $normalised) {
            if ($this->matchesBlocked($collapsed)) {
                return true;
            }
        }

        /*
         * Strip common word-separators and try again. This catches
         * evasions such as "f_u_c_k" and "f.u.c.k".
         */
        $compact = preg_replace(
            '/[\s._*\-]+/u',
            '',
            $normalised
        ) ?? $normalised;

        if ($this->matchesBlocked($compact)) {
            return true;
        }

        $compactCollapsed = preg_replace(
            '/(.)\1+/u',
            '$1',
            $compact
        ) ?? $compact;

        if ($compactCollapsed !== $compact) {
            if ($this->matchesBlocked($compactCollapsed)) {
                return true;
            }
        }

        /*
         * Whole-word match against the false-positive-prone list.
         * Split the separator-stripped form so that "hell" inside
         * "Michelle" is not matched, but "hell" on its own is.
         */
        $tokens = preg_split(
            '/[^a-z0-9]+/',
            $compact,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        foreach ($tokens as $token) {
            if (
                in_array(
                    $token,
                    ProfanityList::BLOCKED_WHOLE_WORD,
                    true
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether the given string contains any word from the
     * outright blocked list.
     */
    private function matchesBlocked(string $value): bool
    {
        foreach (ProfanityList::BLOCKED as $word) {
            if (str_contains($value, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalise text so common evasions are still caught.
     *
     * - lowercase
     * - zero-width characters stripped
     * - common leetspeak substitutions converted back to letters
     *
     * Character-run collapsing is NOT done here. It is applied
     * separately in containsProfanity() so that legitimate double
     * letters (such as the "ss" in "asshole") survive the primary
     * check.
     */
    private function normalise(string $value): string
    {
        $value = mb_strtolower($value);

        // Strip zero-width characters commonly used to break up
        // words without changing how they look on screen.
        $value = preg_replace(
            '/[\x{200B}-\x{200D}\x{FEFF}]/u',
            '',
            $value
        ) ?? $value;

        // Leetspeak substitutions.
        $value = strtr($value, [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '@' => 'a',
            '$' => 's',
        ]);

        return $value;
    }
}