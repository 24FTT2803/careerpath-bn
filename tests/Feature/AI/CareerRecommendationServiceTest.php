<?php

use App\Contracts\CareerAiClient;
use App\Models\BIICFCareer;
use App\Models\User;
use App\Services\AI\CareerAiPayloadBuilder;
use App\Services\AI\CareerRecommendationService;
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
    return new class($response) implements CareerAiClient {
        public function __construct(
            private array $response
        ) {
        }

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
    return new class implements CareerAiClient {
        public function recommend(array $payload): array
        {
            throw new RuntimeException(
                'Career AI service is unavailable.'
            );
        }
    };
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

        'explanation' =>
            'Test career recommendation explanation.',
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
                'explanation' =>
                    'Existing valid recommendation.',
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

        $service = new CareerRecommendationService(
            careerAiClientReturning(
                $invalidResponse
            ),
            new CareerAiPayloadBuilder()
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
                'explanation' =>
                    'Existing valid recommendation.',
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
                'explanation' =>
                    'Recommendation before API failure.',
            ]);

        $service = new CareerRecommendationService(
            failingCareerAiClient(),
            new CareerAiPayloadBuilder()
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
                'explanation' =>
                    'Recommendation before API failure.',
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
    'valid AI response replaces existing recommendations',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $oldCareer = createCareerForRecommendationTest(
            'Old Career'
        );

        $student
            ->careerRecommendations()
            ->create([
                'biicf_career_id' => $oldCareer->id,
                'rank' => 1,
                'match_score' => 50,
                'matched_skills' => [],
                'skill_gaps' => [],
                'development_plan' => [],
                'career_readiness_score' => 40,
                'explanation' =>
                    'Old recommendation to replace.',
            ]);

        $careerOne = createCareerForRecommendationTest(
            'Career One'
        );

        $careerTwo = createCareerForRecommendationTest(
            'Career Two'
        );

        $careerThree = createCareerForRecommendationTest(
            'Career Three'
        );

        $validResponse = [
            'schema_version' => '1.0',
            'status' => 'completed',

            'recommendations' => [
                validRecommendationItem(
                    $careerOne,
                    1,
                    91
                ),

                validRecommendationItem(
                    $careerTwo,
                    2,
                    82
                ),

                validRecommendationItem(
                    $careerThree,
                    3,
                    73
                ),
            ],
        ];

        $service = new CareerRecommendationService(
            careerAiClientReturning(
                $validResponse
            ),
            new CareerAiPayloadBuilder()
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

        $this->assertDatabaseMissing(
            'career_recommendations',
            [
                'user_id' => $student->id,
                'explanation' =>
                    'Old recommendation to replace.',
            ]
        );

        $this->assertDatabaseHas(
            'career_recommendations',
            [
                'user_id' => $student->id,
                'biicf_career_id' => $careerOne->id,
                'rank' => 1,
            ]
        );

        $this->assertDatabaseHas(
            'career_recommendations',
            [
                'user_id' => $student->id,
                'biicf_career_id' => $careerTwo->id,
                'rank' => 2,
            ]
        );

        $this->assertDatabaseHas(
            'career_recommendations',
            [
                'user_id' => $student->id,
                'biicf_career_id' => $careerThree->id,
                'rank' => 3,
            ]
        );
    }
);