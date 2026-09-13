<?php

use App\Models\ProfileSnapshot;
use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Services\AI\CareerAiPayloadBuilder;
use App\Services\AI\CareerReportBuilder;
use App\Services\AI\ProfileSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function snapshotServiceForReports(): ProfileSnapshotService
{
    return new ProfileSnapshotService(
        new CareerAiPayloadBuilder
    );
}

test(
    'a generation report shows the profile that produced it',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
            'programme' => 'Diploma in ICT',
        ]);

        $student->competencies()->create([
            'skill_name' => 'PHP',
            'category' => 'technical',
            'proficiency_level' => 'advanced',
        ]);

        $snapshot = snapshotServiceForReports()
            ->captureFor($student->fresh());

        $generation = $student->recommendationGenerations()->create([
            'profile_snapshot_id' => $snapshot->id,
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => 'mock',
            'recommendation_count' => 0,
            'generated_at' => now()->subDays(5),
        ]);

        /*
         * The profile moves on after the generation.
         */
        $student->competencies()->create([
            'skill_name' => 'Python',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        $data = (new CareerReportBuilder)->for(
            $student->fresh(),
            $generation
        );

        $skills = $data['user']
            ->competencies
            ->pluck('skill_name')
            ->all();

        expect($skills)
            ->toContain('PHP')
            ->not->toContain('Python');

        expect($data['snapshotAvailable'])->toBeTrue();

        expect(
            $data['reportDate']->toDateString()
        )->toBe(
            $generation->generated_at->toDateString()
        );
    }
);

test(
    'a report without a generation uses the live profile',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->competencies()->create([
            'skill_name' => 'Laravel',
            'category' => 'technical',
            'proficiency_level' => 'advanced',
        ]);

        $data = (new CareerReportBuilder)->for(
            $student->fresh()
        );

        expect(
            $data['user']->competencies->pluck('skill_name')->all()
        )->toContain('Laravel');

        expect($data['snapshotAvailable'])->toBeFalse();
    }
);

test(
    'a generation without a snapshot falls back to live data',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->competencies()->create([
            'skill_name' => 'MySQL',
            'category' => 'technical',
            'proficiency_level' => 'intermediate',
        ]);

        $generation = $student->recommendationGenerations()->create([
            'profile_snapshot_id' => null,
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => RecommendationGeneration::DRIVER_UNKNOWN,
            'recommendation_count' => 0,
            'generated_at' => now()->subMonth(),
        ]);

        $data = (new CareerReportBuilder)->for(
            $student->fresh(),
            $generation
        );

        /*
         * Nothing was recorded at the time, so the report still
         * renders rather than showing the student an empty
         * archive entry.
         */
        expect($data['snapshotAvailable'])->toBeFalse();

        expect(
            $data['user']->competencies->pluck('skill_name')->all()
        )->toContain('MySQL');
    }
);

test(
    'identity details are never frozen',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
            'first_name' => 'Old',
            'last_name' => 'Name',
        ]);

        $snapshot = snapshotServiceForReports()
            ->captureFor($student->fresh());

        $generation = $student->recommendationGenerations()->create([
            'profile_snapshot_id' => $snapshot->id,
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => 'mock',
            'recommendation_count' => 0,
            'generated_at' => now(),
        ]);

        $student->update([
            'first_name' => 'Corrected',
        ]);

        $data = (new CareerReportBuilder)->for(
            $student->fresh(),
            $generation
        );

        expect($data['user']->first_name)
            ->toBe('Corrected');
    }
);

test(
    'a student cannot download another student report',
    function () {
        $owner = User::factory()->create([
            'role' => 'student',
        ]);

        $intruder = User::factory()->create([
            'role' => 'student',
        ]);

        $generation = $owner->recommendationGenerations()->create([
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => 'mock',
            'recommendation_count' => 0,
            'generated_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->get(
                route(
                    'student.recommendations.report',
                    $generation
                )
            )
            ->assertNotFound();
    }
);

test(
    'snapshots are reused rather than duplicated per report',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $service = snapshotServiceForReports();

        $service->captureFor($student->fresh());
        $service->captureFor($student->fresh());

        expect(
            ProfileSnapshot::where('user_id', $student->id)->count()
        )->toBe(1);
    }
);
