<?php

use App\Models\BIICFCareer;
use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Services\AI\CareerAiPayloadBuilder;
use App\Services\AI\ProfileSnapshotService;
use App\Services\AI\RecommendationStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeRecommendationStatusService(): RecommendationStatusService
{
    return new RecommendationStatusService(
        new ProfileSnapshotService(
            new CareerAiPayloadBuilder
        )
    );
}

/**
 * Create a student holding one generation built from their
 * current profile, as a real generation would be.
 */
function studentWithSnapshottedGeneration(): array
{
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student->competencies()->create([
        'skill_name' => 'PHP',
        'category' => 'technical',
        'proficiency_level' => 'advanced',
    ]);

    $snapshot = (new ProfileSnapshotService(
        new CareerAiPayloadBuilder
    ))->captureFor($student->fresh());

    $generation = $student->recommendationGenerations()->create([
        'profile_snapshot_id' => $snapshot->id,
        'generation_number' => 1,
        'status' => RecommendationGeneration::STATUS_CURRENT,
        'driver' => 'mock',
        'schema_version' => '1.0',
        'recommendation_count' => 3,
        'generated_at' => now(),
    ]);

    return [$student, $generation];
}

test(
    'an unchanged profile leaves the generation current',
    function () {
        [$student, $generation] = studentWithSnapshottedGeneration();

        makeRecommendationStatusService()
            ->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_CURRENT);
    }
);

test(
    'changing AI-relevant profile data marks the generation outdated',
    function () {
        [$student, $generation] = studentWithSnapshottedGeneration();

        $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        makeRecommendationStatusService()
            ->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_OUTDATED);
    }
);

test(
    'changing a field the AI never sees leaves the generation current',
    function () {
        [$student, $generation] = studentWithSnapshottedGeneration();

        $student->update([
            'first_name' => 'Renamed',
            'phone' => '+6731234567',
        ]);

        makeRecommendationStatusService()
            ->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_CURRENT);
    }
);

test(
    'undoing the edit restores the generation to current',
    function () {
        [$student, $generation] = studentWithSnapshottedGeneration();

        $added = $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        $status = makeRecommendationStatusService();
        $status->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_OUTDATED);

        /*
         * Status is derived from the profile as it stands, so
         * reversing the change that caused the warning must
         * clear the warning.
         */
        $added->delete();
        $status->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_CURRENT);
    }
);

test(
    'an older generation matching the profile becomes previous',
    function () {
        [$student, $first] = studentWithSnapshottedGeneration();

        $snapshots = new ProfileSnapshotService(
            new CareerAiPayloadBuilder
        );

        $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        $secondSnapshot = $snapshots->captureFor(
            $student->fresh()
        );

        $second = $student->recommendationGenerations()->create([
            'profile_snapshot_id' => $secondSnapshot->id,
            'generation_number' => 2,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => 'mock',
            'schema_version' => '1.0',
            'recommendation_count' => 3,
            'generated_at' => now(),
        ]);

        $first->update([
            'status' => RecommendationGeneration::STATUS_OUTDATED,
        ]);

        /*
         * Reverting to the profile the first generation was
         * built from. The first matches again but is no longer
         * newest, so it is previous rather than current, while
         * the newer generation no longer matches at all.
         */
        $student->competencies()
            ->where('skill_name', 'Python')
            ->delete();

        makeRecommendationStatusService()
            ->refreshFor($student->fresh());

        expect($first->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_PREVIOUS)
            ->and($second->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_OUTDATED);
    }
);

test(
    'a generation without a snapshot is never marked outdated',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $generation = $student->recommendationGenerations()->create([
            'profile_snapshot_id' => null,
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => RecommendationGeneration::DRIVER_UNKNOWN,
            'recommendation_count' => 3,
            'generated_at' => now(),
        ]);

        $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        makeRecommendationStatusService()
            ->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_CURRENT);
    }
);

test(
    'an outdated generation still returns the student recommendations',
    function () {
        [$student, $generation] = studentWithSnapshottedGeneration();

        $career = BIICFCareer::create([
            'job_title' => 'Status Test Career',
            'subsector' => 'Test Subsector',
            'technical_skills' => [],
            'soft_skills' => [],
            'entry_requirements' => [],
            'recommended_training' => [],
            'certifications' => [],
            'job_description' => 'Test career description.',
            'demand_level' => 'Test',
        ]);

        $generation->recommendations()->create([
            'user_id' => $student->id,
            'biicf_career_id' => $career->id,
            'rank' => 1,
            'match_score' => 80,
            'matched_skills' => [],
            'skill_gaps' => [],
            'development_plan' => [],
            'career_readiness_score' => 70,
            'explanation' => 'Recommendation that must stay visible.',
        ]);

        $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        makeRecommendationStatusService()
            ->refreshFor($student->fresh());

        expect($generation->fresh()->status)
            ->toBe(RecommendationGeneration::STATUS_OUTDATED);

        /*
         * The point of basing the relation on the newest
         * generation rather than on status: going outdated must
         * not empty the student's screens.
         */
        expect(
            $student->fresh()->currentRecommendations()->count()
        )->toBe(1);

        expect(
            $student
                ->fresh()
                ->currentRecommendationGeneration()
                ->first()
                ->id
        )->toBe($generation->id);
    }
);
