<?php

use App\Contracts\CareerAiClient;
use App\Models\BIICFCareer;
use App\Models\ProfileSnapshot;
use App\Models\User;
use App\Services\AI\CareerAiPayloadBuilder;
use App\Services\AI\CareerRecommendationContextBuilder;
use App\Services\AI\CareerRecommendationEnricher;
use App\Services\AI\CareerRecommendationService;
use App\Services\AI\ProfileSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Create a temporary BIICF career for recommendation tests.
 */
function createCareerForRecommendationTest(
    string $jobTitle,
    string $subsector = 'Test Subsector'
): BIICFCareer {
    return BIICFCareer::create([
        'job_title' => $jobTitle,
        'subsector' => $subsector,
        'technical_skills' => [],
        'soft_skills' => [],
        'entry_requirements' => [],
        'recommended_training' => [],
        'certifications' => [],
        'job_description' => 'Test career description.',
        'demand_level' => 'Test',
    ]);
}

/**
 * Create a fake Career AI client that returns a fixed response.
 */
function careerAiClientReturning(
    array $response
): CareerAiClient {
    return new class($response) implements CareerAiClient
    {
        public function __construct(
            private array $response
        ) {}

        public function recommend(array $payload): array
        {
            return $this->response;
        }
    };
}

/**
 * Create a fake Career AI client that always fails.
 */
function failingCareerAiClient(): CareerAiClient
{
    return new class implements CareerAiClient
    {
        public function recommend(array $payload): array
        {
            throw new RuntimeException(
                'Career AI service is unavailable.'
            );
        }
    };
}

/**
 * Build the Career Recommendation service with
 * all dependencies required by the current implementation.
 */
function makeCareerRecommendationService(
    CareerAiClient $careerAi
): CareerRecommendationService {
    /*
     * These tests exercise the existing legacy/mock
     * recommendation contract, regardless of the
     * developer's local CAREER_AI_DRIVER setting.
     */
    config([
        'career-ai.driver' => 'mock',
    ]);

    $payloadBuilder =
        new CareerAiPayloadBuilder;

    return new CareerRecommendationService(
        $careerAi,
        $payloadBuilder,
        new CareerRecommendationContextBuilder(
            $payloadBuilder
        ),
        new CareerRecommendationEnricher,
        new ProfileSnapshotService(
            $payloadBuilder
        )
    );
}

/**
 * Build one valid recommendation response item.
 */
function validRecommendationItem(
    BIICFCareer $career,
    int $rank,
    float $matchScore
): array {
    return [
        'biicf_career_id' => $career->id,
        'rank' => $rank,
        'match_score' => $matchScore,

        'matched_skills' => [
            'Test Competency',
        ],

        'skill_gaps' => [
            [
                'skill_name' => 'Test Skill Gap',
                'skill_type' => 'technical',
                'current_level' => 'Assist',
                'current_level_value' => 2,
                'recommended_level' => 'Apply',
                'required_level' => 3,
                'required_label' => 'Apply',
                'gap' => 1,
            ],
        ],

        'development_plan' => [
            'Complete a test development activity.',
        ],

        'career_readiness_score' => 70,

        'explanation' => 'Test career recommendation explanation.',
    ];
}

test(
    'invalid AI response preserves existing recommendations',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $career = createCareerForRecommendationTest(
            'Existing Career'
        );

        $existingRecommendation = $student
            ->careerRecommendations()
            ->create([
                'biicf_career_id' => $career->id,
                'rank' => 1,
                'match_score' => 88,
                'matched_skills' => [
                    'Existing Skill',
                ],
                'skill_gaps' => [],
                'development_plan' => [
                    'Existing development plan.',
                ],
                'career_readiness_score' => 76,
                'explanation' => 'Existing valid recommendation.',
            ]);

        /*
         * Only one recommendation is returned.
         * The current contract requires exactly three,
         * so validation must fail.
         */
        $invalidResponse = [
            'schema_version' => '1.0',
            'status' => 'completed',

            'recommendations' => [
                validRecommendationItem(
                    $career,
                    1,
                    90
                ),
            ],
        ];

        $service = makeCareerRecommendationService(
            careerAiClientReturning(
                $invalidResponse
            )
        );

        expect(
            fn () => $service->generateFor($student)
        )->toThrow(
            ValidationException::class
        );

        /*
         * The old recommendation must still exist because
         * validation happens before the replacement
         * transaction begins.
         */
        $this->assertDatabaseHas(
            'career_recommendations',
            [
                'id' => $existingRecommendation->id,
                'user_id' => $student->id,
                'biicf_career_id' => $career->id,
                'rank' => 1,
                'explanation' => 'Existing valid recommendation.',
            ]
        );

        expect(
            $student
                ->careerRecommendations()
                ->count()
        )->toBe(1);
    }
);

test(
    'AI client failure preserves existing recommendations',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $career = createCareerForRecommendationTest(
            'Existing Career'
        );

        $existingRecommendation = $student
            ->careerRecommendations()
            ->create([
                'biicf_career_id' => $career->id,
                'rank' => 1,
                'match_score' => 85,
                'matched_skills' => [
                    'Existing Skill',
                ],
                'skill_gaps' => [],
                'development_plan' => [
                    'Existing development plan.',
                ],
                'career_readiness_score' => 74,
                'explanation' => 'Recommendation before API failure.',
            ]);

        $service = makeCareerRecommendationService(
            failingCareerAiClient()
        );

        expect(
            fn () => $service->generateFor($student)
        )->toThrow(
            RuntimeException::class,
            'Career AI service is unavailable.'
        );

        $this->assertDatabaseHas(
            'career_recommendations',
            [
                'id' => $existingRecommendation->id,
                'user_id' => $student->id,
                'explanation' => 'Recommendation before API failure.',
            ]
        );

        expect(
            $student
                ->careerRecommendations()
                ->count()
        )->toBe(1);
    }
);

/**
 * Build a valid three-recommendation response.
 *
 * @param  array<int, BIICFCareer>  $careers
 * @return array<string, mixed>
 */
function validRecommendationResponse(
    array $careers
): array {
    return [
        'schema_version' => '1.0',
        'status' => 'completed',

        'recommendations' => [
            validRecommendationItem($careers[0], 1, 91),
            validRecommendationItem($careers[1], 2, 82),
            validRecommendationItem($careers[2], 3, 73),
        ],
    ];
}

/**
 * Create the three careers a valid response needs.
 *
 * @return array<int, BIICFCareer>
 */
function careersForRecommendationTest(
    string $prefix
): array {
    return [
        createCareerForRecommendationTest($prefix.' One'),
        createCareerForRecommendationTest($prefix.' Two'),
        createCareerForRecommendationTest($prefix.' Three'),
    ];
}

test(
    'valid AI response stores a current generation',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $careers = careersForRecommendationTest('Career');

        $service = makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($careers)
            )
        );

        $recommendations = $service
            ->generateFor($student);

        expect($recommendations)
            ->toHaveCount(3)
            ->and($recommendations[0]->rank)
            ->toBe(1)
            ->and($recommendations[1]->rank)
            ->toBe(2)
            ->and($recommendations[2]->rank)
            ->toBe(3);

        $generation = $student
            ->currentRecommendationGeneration()
            ->first();

        expect($generation)
            ->not->toBeNull()
            ->and($generation->generation_number)
            ->toBe(1)
            ->and($generation->recommendation_count)
            ->toBe(3);

        foreach ($careers as $index => $career) {
            $this->assertDatabaseHas(
                'career_recommendations',
                [
                    'user_id' => $student->id,
                    'recommendation_generation_id' => $generation->id,
                    'biicf_career_id' => $career->id,
                    'rank' => $index + 1,
                ]
            );
        }
    }
);

test(
    'generating again preserves the previous generation',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $firstCareers = careersForRecommendationTest('First');
        $secondCareers = careersForRecommendationTest('Second');

        makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($firstCareers)
            )
        )->generateFor($student);

        $firstGeneration = $student
            ->currentRecommendationGeneration()
            ->first();

        makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($secondCareers)
            )
        )->generateFor($student);

        /*
         * The point of Task 6: the earlier generation and its
         * recommendations must survive.
         */
        expect(
            $student->recommendationGenerations()->count()
        )->toBe(2);

        expect(
            $student->careerRecommendations()->count()
        )->toBe(6);

        $this->assertDatabaseHas(
            'career_recommendations',
            [
                'recommendation_generation_id' => $firstGeneration->id,
                'biicf_career_id' => $firstCareers[0]->id,
            ]
        );

        $secondGeneration = $student
            ->currentRecommendationGeneration()
            ->first();

        expect($firstGeneration->fresh()->status)
            ->toBe('previous')
            ->and($secondGeneration->status)
            ->toBe('current')
            ->and($secondGeneration->generation_number)
            ->toBe(2);
    }
);

test(
    'current recommendations exclude older generations',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $firstCareers = careersForRecommendationTest('Old');
        $secondCareers = careersForRecommendationTest('New');

        makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($firstCareers)
            )
        )->generateFor($student);

        makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($secondCareers)
            )
        )->generateFor($student);

        $current = $student
            ->currentRecommendations()
            ->orderBy('rank')
            ->get();

        expect($current)->toHaveCount(3);

        expect(
            $current->pluck('biicf_career_id')->all()
        )->toBe(
            collect($secondCareers)->pluck('id')->all()
        );
    }
);

test(
    'generations are numbered per student',
    function () {
        $studentOne = User::factory()->create([
            'role' => 'student',
        ]);

        $studentTwo = User::factory()->create([
            'role' => 'student',
        ]);

        $careers = careersForRecommendationTest('Shared');

        $service = makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($careers)
            )
        );

        $service->generateFor($studentOne);
        $service->generateFor($studentOne);
        $service->generateFor($studentTwo);

        expect(
            $studentOne
                ->currentRecommendationGeneration()
                ->first()
                ->generation_number
        )->toBe(2);

        expect(
            $studentTwo
                ->currentRecommendationGeneration()
                ->first()
                ->generation_number
        )->toBe(1);
    }
);

test(
    'a generation records the profile it was produced from',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->competencies()->create([
            'skill_name' => 'PHP',
            'category' => 'technical',
            'proficiency_level' => 'advanced',
        ]);

        $careers = careersForRecommendationTest('Snapshot');

        makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($careers)
            )
        )->generateFor($student);

        $generation = $student
            ->currentRecommendationGeneration()
            ->first();

        $snapshot = $generation->profileSnapshot;

        expect($snapshot)
            ->not->toBeNull()
            ->and($snapshot->user_id)
            ->toBe($student->id)
            ->and($snapshot->snapshot_hash)
            ->toHaveLength(64);

        /*
         * The snapshot must hold the profile the AI was given,
         * not an empty placeholder.
         */
        expect(
            collect($snapshot->snapshot_data['competencies'])
                ->pluck('skill_name')
                ->all()
        )->toContain('PHP');
    }
);

test(
    'an unchanged profile reuses its existing snapshot',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $careers = careersForRecommendationTest('Reuse');

        $service = makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($careers)
            )
        );

        $service->generateFor($student);
        $service->generateFor($student);

        expect(
            ProfileSnapshot::where(
                'user_id',
                $student->id
            )->count()
        )->toBe(1);

        $snapshotIds = $student
            ->recommendationGenerations()
            ->pluck('profile_snapshot_id')
            ->unique();

        expect($snapshotIds)->toHaveCount(1);
    }
);

test(
    'editing the profile produces a different snapshot',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $careers = careersForRecommendationTest('Changed');

        $service = makeCareerRecommendationService(
            careerAiClientReturning(
                validRecommendationResponse($careers)
            )
        );

        $service->generateFor($student);

        $firstHash = $student
            ->currentRecommendationGeneration()
            ->first()
            ->profileSnapshot
            ->snapshot_hash;

        $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        $service->generateFor($student->fresh());

        $secondHash = $student
            ->currentRecommendationGeneration()
            ->first()
            ->profileSnapshot
            ->snapshot_hash;

        expect($secondHash)->not->toBe($firstHash);

        expect(
            ProfileSnapshot::where(
                'user_id',
                $student->id
            )->count()
        )->toBe(2);
    }
);

test(
    'a failed generation records no snapshot',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $service = makeCareerRecommendationService(
            failingCareerAiClient()
        );

        expect(
            fn () => $service->generateFor($student)
        )->toThrow(RuntimeException::class);

        expect(
            ProfileSnapshot::where(
                'user_id',
                $student->id
            )->count()
        )->toBe(0);
    }
);
