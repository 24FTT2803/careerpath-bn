<?php

namespace App\Support;

/**
 * A small, deliberately conservative list of unambiguous profanity
 * and slurs. Kept short so it does not reject legitimate names,
 * place names or academic vocabulary.
 *
 * Matching is done on normalised text (see the NoProfanity rule)
 * so common evasions such as spacing, leetspeak and repeated
 * characters are still caught.
 *
 * This rule is intended only for identity and label fields that
 * appear on reports, dashboards and cards. It is deliberately NOT
 * applied to narrative fields such as bio, vision statements or
 * project descriptions, because academic and career vocabulary
 * collides with blocklists too often to make filtering those
 * fields safe.
 */
class ProfanityList
{
    /**
     * Words that are blocked outright. All entries are lowercase.
     *
     * Substring matching is used against this list, so
     * "fuck" also catches "fucker" and "fucking".
     *
     * @var array<int, string>
     */
    public const BLOCKED = [
        // Strong English profanity
        'fuck',
        'fucking',
        'fucked',
        'fucker',
        'fuckers',
        'motherfucker',
        'motherfuckers',
        'shit',
        'shitting',
        'shitty',
        'bullshit',
        'asshole',
        'assholes',
        'bastard',
        'bastards',
        'bitch',
        'bitches',
        'cunt',
        'cunts',
        'dickhead',
        'dickheads',
        'prick',
        'wanker',
        'wankers',
        'twat',
        'twats',

        // Sexual / explicit
        'porn',
        'porno',
        'pornography',
    ];

    /**
     * Words that are only blocked when they appear as a whole word.
     *
     * This prevents false positives on names such as "Dickinson",
     * "Assam", "Michelle" and similar. Splitting on non-alphanumeric
     * characters before comparing avoids matching inside longer words.
     *
     * @var array<int, string>
     */
    public const BLOCKED_WHOLE_WORD = [
        'ass',
        'damn',
        'hell',
        'crap',
        'dick',
        'cock',
        'piss',
    ];
}