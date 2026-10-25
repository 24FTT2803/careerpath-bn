<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * One emoji, and nothing that is not an emoji.
 *
 * The palette on the page is a convenience rather than the rule:
 * a student may react with anything their own keyboard offers,
 * which is what "all the emojis" means and what a fixed list of
 * six could never be. So the column is guarded by what a
 * character is rather than by a list of the ones we thought of.
 *
 * An emoji is not one character. A flag is two regional
 * indicators, a profession is a person, a joiner and an object,
 * and a skin tone is a modifier after the hand — so a run of
 * codepoints is allowed, provided every one of them belongs to
 * an emoji and at least one of them is pictographic. Letters,
 * digits and punctuation are refused, which is the part that
 * matters: without it this field is a short free-text box in
 * every student's reach.
 */
class IsEmoji implements ValidationRule
{
    /**
     * How many codepoints one reaction may run to.
     *
     * A family of four with skin tones is eleven. Beyond that is
     * somebody assembling a sentence out of joiners.
     */
    private const MOST_CODEPOINTS = 12;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('Pick an emoji to react with.');

            return;
        }

        $codepoints = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

        if ($codepoints === false || count($codepoints) > self::MOST_CODEPOINTS) {
            $fail('That is longer than a single emoji.');

            return;
        }

        $pictographic = 0;

        foreach ($codepoints as $character) {
            if (preg_match('/\p{Extended_Pictographic}/u', $character)) {
                $pictographic++;

                continue;
            }

            /*
             * The pieces an emoji is assembled from: the joiner,
             * the variation selectors, skin tones, the keycap
             * mark, the letters a flag is spelled with, and the
             * tags a subdivision flag carries.
             */
            $isAssembly = preg_match(
                '/^[\x{200D}\x{FE0E}\x{FE0F}\x{20E3}\x{1F3FB}-\x{1F3FF}\x{1F1E6}-\x{1F1FF}\x{E0020}-\x{E007F}]$/u',
                $character
            );

            /* The digits and # and * that keycaps are built on. */
            $isKeycapBase = preg_match('/^[0-9#*]$/', $character)
                && in_array("\u{20E3}", $codepoints, true);

            if (! $isAssembly && ! $isKeycapBase) {
                $fail('React with an emoji rather than text.');

                return;
            }
        }

        /*
         * Two kinds of emoji carry no pictographic character at
         * all: a flag, which is two regional indicators, and a
         * keycap, which is a digit wearing the keycap mark.
         */
        $isFlag = count($codepoints) === 2
            && ! preg_match('/[^\x{1F1E6}-\x{1F1FF}]/u', $value);

        $isKeycap = in_array("\u{20E3}", $codepoints, true);

        if ($pictographic === 0 && ! $isFlag && ! $isKeycap) {
            $fail('React with an emoji rather than text.');
        }
    }
}
