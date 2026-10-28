<?php

use App\Services\AI\MockCareerAiClient;

/*
 * The offline driver, on its own.
 *
 * These used to assert a keyword table: "Linux" and "Networking"
 * in a profile had to come back as career id 2, out of the five
 * rows in biicf_careers. The mock now answers in schema 2.0
 * against whichever BIICF job roles it is handed, so there are no
 * fixed ids to assert and the profile is matched against the
 * framework's own competency requirements instead.
 *
 * MockRecommendationSchemaTest covers the same client through the
 * service and the database. These cover the response on its own,
 * which is where the shape the service validates against and the
 * promise of a stable answer actually live. The client reads only
 * its payload, so none of this needs the framework seeded.
 */

/**
 * @return array<string, array<int, string>>
 */
function competencyCatalogue(): array
{
    return [
        '1' => ['Programming', 'technical'],
        '2' => ['Software Testing', 'technical'],
        '3' => ['Network Administration', 'technical'],
        '4' => ['Systems Monitoring', 'technical'],
        '5' => ['Data Analysis', 'technical'],
        '6' => ['Statistical Reporting', 'technical'],
    ];
}

/**
 * One entry in the payload's eligible_roles.
 *
 * @param  array<int, array<int, int>>  $requirements  [competency id, level]
 * @return array<string, mixed>
 */
function contextRole(
    int $id,
    string $title,
    string $subSector,
    int $pathLevel,
    array $requirements
): array {
    return [
        'id' => $id,
        'title' => $title,
        'sub_sector' => $subSector,
        'career_path_level' => $pathLevel,
        'requirements' => $requirements,
    ];
}

/**
 * The three roles these tests choose between.
 *
 * @return array<int, array<string, mixed>>
 */
function contextRoles(): array
{
    return [
        contextRole(11, 'Application Developer', 'Software Development', 0, [
            [1, 3],
            [2, 2],
        ]),

        contextRole(12, 'Network Administrator', 'Infrastructure', 0, [
            [3, 3],
            [4, 2],
        ]),

        contextRole(13, 'Data Analyst', 'Data and Analytics', 0, [
            [5, 3],
            [6, 2],
        ]),
    ];
}

/**
 * A context payload shaped as CareerRecommendationContextBuilder
 * builds it.
 *
 * @param  array<int, string>  $competencies
 * @param  array<int, string>  $interests
 * @param  array<int, array<string, mixed>>|null  $roles
 * @return array<string, mixed>
 */
function contextPayload(
    array $competencies,
    array $interests = [],
    ?array $roles = null
): array {
    $roles ??= contextRoles();

    return [
        'schema_version' => '2.0',

        'student_profile' => [
            'programme' => 'Higher National Diploma in Computing',

            'competencies' => array_map(
                fn (string $name): array => [
                    'skill_name' => $name,
                    'category' => 'technical',
                    'proficiency_level' => 'Apply',
                ],
                $competencies
            ),

            'interests' => array_map(
                fn (string $name): array => [
                    'interest_name' => $name,
                    'category' => 'technical',
                ],
                $interests
            ),
        ],

        'eligible_roles' => $roles,

        'reference' => [
            'eligible_role_count' => count($roles),

            'competency_catalogue' => competencyCatalogue(),

            'proficiency_scale' => [
                '1' => 'Follow',
                '2' => 'Assist',
                '3' => 'Apply',
            ],
        ],
    ];
}

function mockClient(): MockCareerAiClient
{
    return new MockCareerAiClient;
}

describe('the response', function () {
    test('it answers in the schema the service validates', function () {
        $response = mockClient()->recommend(
            contextPayload(['Programming'])
        );

        expect($response['schema_version'])->toBe('2.0')
            ->and($response['status'])->toBe('completed')
            ->and($response['recommendations'])->toHaveCount(3);

        $recommendations = $response['recommendations'];

        expect(array_column($recommendations, 'rank'))
            ->toBe([1, 2, 3])
            ->and(array_unique(
                array_column($recommendations, 'biicf_job_role_id')
            ))
            ->toHaveCount(3);
    });

    test('every recommendation carries what the rules require', function () {
        /*
         * match_score between 0 and 100, a development plan of
         * exactly three strings, and an explanation. Each of
         * these is a validation rule in
         * CareerRecommendationService, and a response missing any
         * of them throws rather than degrading.
         */
        $response = mockClient()->recommend(
            contextPayload(['Programming', 'Software Testing'])
        );

        foreach ($response['recommendations'] as $recommendation) {
            expect($recommendation['biicf_job_role_id'])
                ->toBeInt()
                ->and($recommendation['match_score'])
                ->toBeGreaterThanOrEqual(0)
                ->toBeLessThanOrEqual(100)
                ->and($recommendation['explanation'])
                ->toBeString()
                ->not->toBeEmpty();

            expect($recommendation['development_plan'])
                ->toHaveCount(3)
                ->each->toBeString();
        }
    });

    test('it sends no competency analysis of its own', function () {
        /*
         * The gaps and the readiness score are the enricher's,
         * computed from the role's tiers against both proficiency
         * scales. A mock that sent its own would have them
         * silently overwritten, which is how an illustrative
         * number ends up being read as a real one.
         */
        $response = mockClient()->recommend(
            contextPayload(['Programming'])
        );

        foreach ($response['recommendations'] as $recommendation) {
            expect($recommendation)
                ->not->toHaveKey('skill_gaps')
                ->not->toHaveKey('matched_skills')
                ->not->toHaveKey('career_readiness_score');
        }
    });
});

describe('what it recommends', function () {
    test('the role whose requirements the student already meets comes first', function () {
        $response = mockClient()->recommend(
            contextPayload(
                ['Network Administration', 'Systems Monitoring'],
                ['Networking']
            )
        );

        expect($response['recommendations'][0]['biicf_job_role_id'])
            ->toBe(12)
            ->and($response['recommendations'][0]['explanation'])
            ->toContain('Network Administrator');
    });

    test('a different profile gets a different first role', function () {
        $developer = mockClient()->recommend(
            contextPayload(['Programming', 'Software Testing'])
        );

        $analyst = mockClient()->recommend(
            contextPayload(['Data Analysis', 'Statistical Reporting'])
        );

        expect($developer['recommendations'][0]['biicf_job_role_id'])
            ->toBe(11)
            ->and($analyst['recommendations'][0]['biicf_job_role_id'])
            ->toBe(13);
    });

    test('a profile that matches nothing still gets three roles', function () {
        /*
         * A first-year with nothing recorded yet is the common
         * case, not an error. They are shown the entry points.
         */
        $response = mockClient()->recommend(
            contextPayload([])
        );

        expect($response['recommendations'])->toHaveCount(3)
            ->and($response['recommendations'][0]['match_score'])
            ->toBeLessThan(
                /* Well below someone who meets a role in full. */
                mockClient()->recommend(
                    contextPayload(['Programming', 'Software Testing'])
                )['recommendations'][0]['match_score']
            );
    });

    test('the plan names the competencies the role asks for', function () {
        $response = mockClient()->recommend(
            contextPayload(['Programming'])
        );

        $developer = $response['recommendations'][0];

        expect($developer['biicf_job_role_id'])->toBe(11)
            ->and(implode(' ', $developer['development_plan']))
            /* Required at level 2 and not recorded. */
            ->toContain('Software Testing');
    });
});

describe('the same answer twice', function () {
    test('the same payload gives the same answer', function () {
        /*
         * A student regenerating without changing anything should
         * not watch their matches reshuffle.
         */
        $payload = contextPayload(['Programming']);

        expect(mockClient()->recommend($payload))
            ->toBe(mockClient()->recommend($payload));
    });

    test('the order the roles arrive in does not change it', function () {
        /*
         * Roles a student matches equally well are separated by
         * career path level and then by id, so the answer is a
         * property of the framework rather than of the order a
         * query happened to return rows in.
         */
        $roles = contextRoles();

        $reversed = array_reverse($roles);

        expect(
            mockClient()->recommend(
                contextPayload(['Programming'], [], $reversed)
            )
        )->toBe(
            mockClient()->recommend(
                contextPayload(['Programming'], [], $roles)
            )
        );
    });
});

describe('awkward input', function () {
    test('fewer than three roles is refused', function () {
        /*
         * Rather than returning two. The service asks for exactly
         * three and would refuse the response anyway, further
         * from the cause.
         */
        expect(
            fn () => mockClient()->recommend(
                contextPayload(['Programming'], [], array_slice(contextRoles(), 0, 2))
            )
        )->toThrow(
            RuntimeException::class,
            'Career recommendation context must contain at least three eligible BIICF roles.'
        );
    });

    test('a role with no requirements recorded is still explained', function () {
        /*
         * The framework lists roles the competency tables have not
         * caught up with. Saying "0 of the 0 competencies" would
         * read as a fault in the student's profile.
         */
        $roles = contextRoles();

        $roles[] = contextRole(14, 'Bare Role', 'Software Development', 0, []);

        $response = mockClient()->recommend(
            contextPayload([], [], [$roles[3], $roles[0], $roles[1]])
        );

        $bare = collect($response['recommendations'])
            ->firstWhere('biicf_job_role_id', 14);

        expect($bare)->not->toBeNull()
            ->and($bare['explanation'])
            ->toContain('without entry competencies recorded')
            ->and($bare['development_plan'])
            ->toHaveCount(3);
    });
});
