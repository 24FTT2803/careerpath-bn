<?php

use App\Models\CareerAdviserMessage;
use App\Models\Plan;
use App\Models\RecommendationGeneration;
use App\Models\User;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * History visibility is an entitlement, and a database with no
 * plans grants nothing. Seeding the real plans keeps these tests
 * honest about what a student actually sees.
 */
function seedPlansForHistory(): void
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

/**
 * Put a student on the plan that includes history.
 */
function grantHistoryAccess(User $student): void
{
    $student->planGrants()->create([
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ]);
}

function studentWithHistory(bool $withAccess = true): User
{
    seedPlansForHistory();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    if ($withAccess) {
        grantHistoryAccess($student);
    }

    $student->recommendationGenerations()->create([
        'generation_number' => 1,
        'status' => RecommendationGeneration::STATUS_PREVIOUS,
        'driver' => 'mock',
        'recommendation_count' => 3,
        'generated_at' => now()->subWeek(),
    ]);

    $student->recommendationGenerations()->create([
        'generation_number' => 2,
        'status' => RecommendationGeneration::STATUS_CURRENT,
        'driver' => 'groq',
        'recommendation_count' => 3,
        'generated_at' => now(),
    ]);

    $conversation = $student->careerAdviserConversation()->create([
        'message_count' => 2,
        'last_message_at' => now(),
    ]);

    $conversation->messages()->create([
        'role' => CareerAdviserMessage::ROLE_USER,
        'content' => 'A private question about my future.',
    ]);

    $conversation->messages()->create([
        'role' => CareerAdviserMessage::ROLE_ASSISTANT,
        'content' => 'A private answer.',
    ]);

    return $student->fresh();
}

test(
    'the history page lists every generation',
    function () {
        $student = studentWithHistory();

        $this->actingAs($student)
            ->get(route('student.history'))
            ->assertOk()
            ->assertSee('Generation #2')
            ->assertSee('Generation #1');
    }
);

test(
    'the history page shows adviser activity',
    function () {
        $student = studentWithHistory();

        $this->actingAs($student)
            ->get(route('student.history'))
            ->assertOk()
            ->assertSee('2 messages');
    }
);

test(
    'history is empty for a student who has generated nothing',
    function () {
        seedPlansForHistory();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        grantHistoryAccess($student);

        $this->actingAs($student)
            ->get(route('student.history'))
            ->assertOk()
            ->assertSee('have not generated any career recommendations', false);
    }
);

test(
    'a student never sees another student history',
    function () {
        $owner = studentWithHistory();

        $intruder = User::factory()->create([
            'role' => 'student',
        ]);

        grantHistoryAccess($intruder);

        /*
         * The page is per-account rather than addressable by id,
         * so the check is that nothing belonging to the other
         * student leaks into it.
         */
        $this->actingAs($intruder)
            ->get(route('student.history'))
            ->assertOk()
            ->assertDontSee('Generation #2');

        $ownerGeneration = $owner
            ->recommendationGenerations()
            ->first();

        $this->actingAs($intruder)
            ->get(
                route(
                    'student.recommendations.report',
                    $ownerGeneration
                )
            )
            ->assertNotFound();
    }
);

test(
    'staff see a student generation history',
    function () {
        $student = studentWithHistory();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.show', $student->id))
            ->assertOk()
            ->assertSee('Generation #2');
    }
);

test(
    'staff see adviser activity but never the messages',
    function () {
        $student = studentWithHistory();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.students.show', $student->id))
            ->assertOk();

        $response->assertSee('2 messages');

        /*
         * A student must be able to speak freely to the adviser.
         * Staff see that it is used, not what was said.
         */
        $response->assertDontSee('A private question about my future.');
        $response->assertDontSee('A private answer.');
    }
);
