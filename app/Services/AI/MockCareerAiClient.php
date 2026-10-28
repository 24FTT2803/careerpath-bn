<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;
use RuntimeException;

/**
 * The offline stand-in for the recommendation model.
 *
 * It used to answer in schema 1.0, against the five careers in
 * biicf_careers and a hand-written template per career. That made
 * the mock a second dataset rather than a second driver: whether a
 * student's recommendations were BIICF job roles at all came down
 * to whether CAREER_AI_DRIVER happened to say groq, and nothing on
 * the page said which had happened.
 *
 * So this now answers in schema 2.0, against the same job roles
 * and the same context payload the real client is given. What it
 * is still a mock of is the judgement: a model weighs a profile
 * and explains itself, whereas this counts how much of each role's
 * entry requirement the student already records and says so
 * plainly. Everything downstream — the competency comparison, the
 * gaps, the readiness score — is computed by
 * CareerRecommendationEnricher from the framework itself, for this
 * driver exactly as for the real one.
 *
 * It reads only the payload, never the database, so what it
 * recommends can be reasoned about from its input alone.
 */
class MockCareerAiClient implements CareerAiClient
{
    /**
     * How many roles a generation asks for.
     */
    private const WANTED = 3;

    /**
     * Choose and rank job roles from the BIICF context payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function recommend(array $payload): array
    {
        $roles = $payload['eligible_roles'] ?? [];

        if (
            ! is_array($roles)
            || count($roles) < self::WANTED
        ) {
            throw new RuntimeException(
                'Career recommendation context must contain at least three eligible BIICF roles.'
            );
        }

        $catalogue =
            $payload['reference']['competency_catalogue']
            ?? [];

        $held = $this->heldCompetencies($payload);

        /*
         * The student's half of the payload only. Read over the
         * whole of it, every role title and every competency name
         * in the framework is in the haystack, so each role
         * "matches" and the term is a constant that ranks nothing.
         */
        $profileText = $this->searchableText(
            $payload['student_profile'] ?? []
        );

        $ranked = $this->rank(
            $roles,
            $catalogue,
            $held,
            $profileText
        );

        $recommendations = [];

        foreach (
            array_slice($ranked, 0, self::WANTED) as $index => $role
        ) {
            $recommendations[] = $this->recommendationFor(
                $role,
                $index + 1,
                $catalogue,
                $held
            );
        }

        return [
            'schema_version' => '2.0',
            'status' => 'completed',
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Order the eligible roles, best fit first.
     *
     * Sorted on more than the score so the same profile and the
     * same framework always produce the same three roles in the
     * same order. Two roles a student matches equally well would
     * otherwise swap places between generations for no reason a
     * student could see.
     *
     * @param  array<int, array<string, mixed>>  $roles
     * @param  array<string, array<int, string>>  $catalogue
     * @param  array<string, true>  $held
     * @return array<int, array<string, mixed>>
     */
    private function rank(
        array $roles,
        array $catalogue,
        array $held,
        string $profileText
    ): array {
        $scored = array_map(
            fn (array $role): array => [
                ...$role,

                'score' => $this->scoreRole(
                    $role,
                    $catalogue,
                    $held,
                    $profileText
                ),
            ],
            array_values($roles)
        );

        usort(
            $scored,
            /*
             * Score descending, then the lower career path level,
             * then the lower id. Each position takes its left
             * operand from one role and its right from the other,
             * so reversing only the score is what makes that one
             * column descending and the other two ascending.
             */
            fn (array $left, array $right): int => [
                $right['score'],
                (int) ($left['career_path_level'] ?? 0),
                (int) ($left['id'] ?? 0),
            ] <=> [
                $left['score'],
                (int) ($right['career_path_level'] ?? 0),
                (int) ($right['id'] ?? 0),
            ]
        );

        return $scored;
    }

    /**
     * What this role is worth to this student, out of 100.
     *
     * Mostly how much of the role's entry requirement the student
     * already records, because that is the one part of a fit that
     * can be read off the framework rather than guessed. The rest
     * is whether the role or its sub-sector is anywhere in what
     * the student wrote about themselves, and a small preference
     * for the bottom of a career path, which is where a student
     * leaving a polytechnic actually starts.
     *
     * @param  array<string, mixed>  $role
     * @param  array<string, array<int, string>>  $catalogue
     * @param  array<string, true>  $held
     */
    private function scoreRole(
        array $role,
        array $catalogue,
        array $held,
        string $profileText
    ): float {
        $coverage = $this->coverage($role, $catalogue, $held);

        $affinity = $this->affinity($role, $profileText);

        /*
         * Level 0 is an entry point and scores the whole of this;
         * it tails off rather than ruling senior roles out, since
         * a student with the competencies for one should still be
         * shown it.
         */
        $entry = max(
            0.0,
            1.0 - ((int) ($role['career_path_level'] ?? 0) / 5)
        );

        return round(
            (70 * $coverage) + (25 * $affinity) + (5 * $entry),
            2
        );
    }

    /**
     * The share of the role's entry competencies the student has.
     *
     * By name only. Whether the student reaches the level the role
     * asks for is the enricher's arithmetic, against both
     * proficiency scales, and repeating a worse version of it here
     * would only disagree with the gaps shown on the same page.
     *
     * @param  array<string, mixed>  $role
     * @param  array<string, array<int, string>>  $catalogue
     * @param  array<string, true>  $held
     */
    private function coverage(
        array $role,
        array $catalogue,
        array $held
    ): float {
        $required = $this->requirementNames($role, $catalogue);

        if ($required === []) {
            return 0.0;
        }

        $met = 0;

        foreach ($required as $name) {
            if (isset($held[$name])) {
                $met++;
            }
        }

        return $met / count($required);
    }

    /**
     * Whether the student's own words point at this role.
     *
     * @param  array<string, mixed>  $role
     */
    private function affinity(
        array $role,
        string $profileText
    ): float {
        $phrases = array_filter([
            $this->normalise((string) ($role['title'] ?? '')),
            $this->normalise((string) ($role['sub_sector'] ?? '')),
        ]);

        if ($phrases === []) {
            return 0.0;
        }

        /*
         * The whole title first, because "data analyst" written in
         * a student's interests says far more than "data" matching
         * somewhere in a paragraph.
         */
        foreach ($phrases as $phrase) {
            if (str_contains($profileText, $phrase)) {
                return 1.0;
            }
        }

        $words = [];

        foreach ($phrases as $phrase) {
            foreach (explode(' ', $phrase) as $word) {
                /* Short words match everything and mean nothing. */
                if (mb_strlen($word) > 3) {
                    $words[$word] = true;
                }
            }
        }

        if ($words === []) {
            return 0.0;
        }

        $hits = 0;

        foreach (array_keys($words) as $word) {
            if (str_contains($profileText, $word)) {
                $hits++;
            }
        }

        return $hits / count($words);
    }

    /**
     * One recommendation, in the shape schema 2.0 asks for.
     *
     * matched_skills, skill_gaps and career_readiness_score are
     * deliberately absent: the enricher computes all three from
     * the framework and would overwrite anything sent here.
     *
     * @param  array<string, mixed>  $role
     * @param  array<string, array<int, string>>  $catalogue
     * @param  array<string, true>  $held
     * @return array<string, mixed>
     */
    private function recommendationFor(
        array $role,
        int $rank,
        array $catalogue,
        array $held
    ): array {
        $required = $this->requirementNames($role, $catalogue);

        $missing = array_values(
            array_filter(
                $required,
                fn (string $name): bool => ! isset($held[$name])
            )
        );

        return [
            'biicf_job_role_id' => (int) $role['id'],

            'rank' => $rank,

            'match_score' => min(
                100.0,
                max(0.0, (float) $role['score'])
            ),

            'development_plan' => $this->developmentPlan(
                $role,
                $catalogue,
                $missing
            ),

            'explanation' => $this->explanation(
                $role,
                count($required),
                count($required) - count($missing)
            ),
        ];
    }

    /**
     * Three things to do about this role.
     *
     * Named competencies first, since those are the role's own
     * entry requirement rather than advice. Padding only when the
     * student already has most of what the role asks for, which
     * is the good case and still needs three lines.
     *
     * @param  array<string, mixed>  $role
     * @param  array<string, array<int, string>>  $catalogue
     * @param  array<int, string>  $missing
     * @return array<int, string>
     */
    private function developmentPlan(
        array $role,
        array $catalogue,
        array $missing
    ): array {
        $title = (string) ($role['title'] ?? 'this role');

        $labels = $this->requirementLabels($role, $catalogue);

        $plan = [];

        foreach ($missing as $name) {
            $plan[] = 'Work towards '
                .($labels[$name] ?? $name)
                .', which '
                .$title
                .' asks for on entry.';
        }

        $padding = [
            'Record evidence against the competencies you already '
            .'have, so the match reflects them.',

            'Ask about '
            .$title
            .' work experience in '
            .((string) ($role['sub_sector'] ?? 'the sector'))
            .'.',

            'Add a project that shows what you can already do '
            .'towards '
            .$title
            .'.',
        ];

        return array_slice(
            [...$plan, ...$padding],
            0,
            3
        );
    }

    /**
     * @param  array<string, mixed>  $role
     */
    private function explanation(
        array $role,
        int $requirementCount,
        int $metCount
    ): string {
        $title = (string) ($role['title'] ?? 'This role');

        if ($requirementCount < 1) {
            return $title
                .' is listed in the framework without entry '
                .'competencies recorded against it, so this match '
                .'rests on the profile alone.';
        }

        return 'Your profile records '
            .$metCount
            .' of the '
            .$requirementCount
            .' competencies '
            .$title
            .' asks for on entry'
            .(
                ($role['sub_sector'] ?? null)
                    ? ', within '.$role['sub_sector']
                    : ''
            )
            .'.';
    }

    /**
     * The normalised names of a role's entry competencies.
     *
     * @param  array<string, mixed>  $role
     * @param  array<string, array<int, string>>  $catalogue
     * @return array<int, string>
     */
    private function requirementNames(
        array $role,
        array $catalogue
    ): array {
        return array_values(
            array_keys(
                $this->requirementLabels($role, $catalogue)
            )
        );
    }

    /**
     * Normalised name => the name as the framework writes it.
     *
     * @param  array<string, mixed>  $role
     * @param  array<string, array<int, string>>  $catalogue
     * @return array<string, string>
     */
    private function requirementLabels(
        array $role,
        array $catalogue
    ): array {
        $labels = [];

        foreach ($role['requirements'] ?? [] as $requirement) {
            $id = (string) ($requirement[0] ?? '');

            $name = $catalogue[$id][0] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            $labels[$this->normalise($name)] = $name;
        }

        return $labels;
    }

    /**
     * What the student has recorded, by normalised name.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, true>
     */
    private function heldCompetencies(array $payload): array
    {
        $competencies =
            $payload['student_profile']['competencies']
            ?? [];

        $held = [];

        if (! is_array($competencies)) {
            return $held;
        }

        foreach ($competencies as $competency) {
            $name = is_array($competency)
                ? ($competency['skill_name'] ?? null)
                : null;

            if (! is_string($name) || trim($name) === '') {
                continue;
            }

            $held[$this->normalise($name)] = true;
        }

        return $held;
    }

    /**
     * Every string in the student's profile, lowercased into one
     * haystack.
     *
     * @param  array<string, mixed>  $profile
     */
    private function searchableText(array $profile): string
    {
        $values = [];

        array_walk_recursive(
            $profile,
            function ($value) use (&$values) {
                if (
                    is_string($value)
                    || is_numeric($value)
                ) {
                    $values[] = mb_strtolower((string) $value);
                }
            }
        );

        return implode(' ', $values);
    }

    private function normalise(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
