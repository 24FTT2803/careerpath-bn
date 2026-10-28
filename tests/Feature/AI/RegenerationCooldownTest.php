<?php

use App\Models\FeatureDefinition;
use App\Models\Plan;
use App\Models\User;
use App\Services\AI\RegenerationCooldown;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedPlansForCooldown(): void
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

function cooldown(): RegenerationCooldown
{
    return app(RegenerationCooldown::class);
}

function studentWithGeneration(
    ?string $generatedAt = null
): User {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    if ($generatedAt !== null) {
        $student->recommendationGenerations()->create([
            'generation_number' => 1,
            'status' => 'current',
            'driver' => 'mock',
            'recommendation_count' => 3,
            'generated_at' => $generatedAt,
        ]);
    }

    return $student;
}

test('a student who has never generated may generate now', function () {
    seedPlansForCooldown();

    expect(cooldown()->blocks(studentWithGeneration()))
        ->toBeFalse();
});

test('a second attempt straight away is refused', function () {
    seedPlansForCooldown();

    $student = studentWithGeneration(now()->toDateTimeString());

    /*
     * Generation takes several seconds and the button stays
     * live throughout, which is how a student spends a quota
     * use on a request they did not mean to make.
     */
    expect(cooldown()->blocks($student))
        ->toBeTrue()
        ->and(cooldown()->secondsRemaining($student))
        ->toBeGreaterThan(0);
});

test('the pause expires on its own', function () {
    seedPlansForCooldown();

    $student = studentWithGeneration(
        now()->subMinutes(5)->toDateTimeString()
    );

    expect(cooldown()->blocks($student))->toBeFalse();
});

test(
    'switching the feature off globally removes the pause',
    function () {
        seedPlansForCooldown();

        $student = studentWithGeneration(
            now()->toDateTimeString()
        );

        expect(cooldown()->blocks($student))->toBeTrue();

        FeatureDefinition::where(
            'key',
            RegenerationCooldown::FEATURE_KEY
        )->update(['global_enabled' => false]);

        /*
         * An administrator switching it off should take effect
         * everywhere without editing each plan in turn.
         */
        expect(cooldown()->blocks($student->fresh()))
            ->toBeFalse();
    }
);

test('a plan may set the pause to zero', function () {
    seedPlansForCooldown();

    $student = studentWithGeneration(now()->toDateTimeString());

    Plan::where('code', 'free')
        ->firstOrFail()
        ->features()
        ->where('key', RegenerationCooldown::FEATURE_KEY)
        ->update(['value' => json_encode(0)]);

    expect(cooldown()->blocks($student->fresh()))->toBeFalse();
});

test(
    'generating twice in a row does not spend a second quota use',
    function () {
        seedPlansForCooldown();

        $student = studentWithGeneration(
            now()->toDateTimeString()
        );

        $response = $this->actingAs($student)
            ->post(route('student.recommendations.generate'));

        $response->assertRedirect(
            route('student.recommendations.index')
        );

        /*
         * The pause is checked before the quota, so an
         * accidental second press costs the student nothing.
         */
        $response->assertSessionHas('warning');

        expect(session('warning'))
            ->toContain('Please wait');
    }
);

test(
    'the pause counts itself down in front of the student',
    function () {
        /*
         * The countdown sat below @endsection, and Blade throws
         * away everything after it. So the label read "Please
         * wait 30s" and stayed there: the pause expired on the
         * server while the button in front of the student stayed
         * dead until they thought to reload the page.
         *
         * Asserted on a line of the script itself rather than on
         * the wording, which the server also prints onto the
         * button and which would therefore pass with the script
         * discarded.
         */
        seedPlansForCooldown();

        $student = studentWithGeneration(
            now()->toDateTimeString()
        );

        $student->profile()->create([]);

        $this->actingAs($student)
            ->get(route('student.recommendations.index'))
            ->assertOk()
            ->assertShows('button.dataset.cooldown')
            ->assertHides('data-cooldown="0"');
    }
);
