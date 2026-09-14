<?php

use App\Models\Advertisement;
use App\Models\User;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedPlansForRendering(): void
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

function optedInStudent(): User
{
    return User::factory()->create([
        'role' => 'student',
        'show_ads' => true,
    ]);
}

function makeRenderableAd(string $position): Advertisement
{
    return Advertisement::create([
        'title' => 'Sponsor '.$position,
        'type' => Advertisement::TYPE_IMAGE,
        'external_url' => 'https://example.com/'.$position.'.png',
        'alt_text' => 'Sponsor banner '.$position,
        'position' => $position,
        'is_active' => true,
    ]);
}

test(
    'an opted in student sees advertisements on a page',
    function () {
        seedPlansForRendering();

        makeRenderableAd(Advertisement::POSITION_ONE);
        makeRenderableAd(Advertisement::POSITION_TWO);

        $this->actingAs(optedInStudent())
            ->get(route('student.settings'))
            ->assertOk()
            ->assertSee('Sponsor banner one')
            ->assertSee('Sponsor banner two');
    }
);

test(
    'both advertisements render as banners',
    function () {
        seedPlansForRendering();

        makeRenderableAd(Advertisement::POSITION_ONE);
        makeRenderableAd(Advertisement::POSITION_TWO);

        $response = $this->actingAs(optedInStudent())
            ->get(route('student.settings'))
            ->assertOk();

        /*
         * Above and below the page at every size. Side columns
         * were removed: the page is capped at 1200px, so a rail
         * either narrowed the content or left a tall slot no
         * banner image suits.
         */
        expect(
            substr_count($response->getContent(), 'class="ad-slot"')
        )->toBe(2);
    }
);

test(
    'one advertisement renders on its own',
    function () {
        seedPlansForRendering();

        makeRenderableAd(Advertisement::POSITION_ONE);

        $response = $this->actingAs(optedInStudent())
            ->get(route('student.settings'))
            ->assertOk();

        $response->assertSee('Sponsor banner one');

        expect(
            substr_count($response->getContent(), 'class="ad-slot"')
        )->toBe(1);
    }
);

test(
    'a student who has not opted in sees no advertising markup',
    function () {
        seedPlansForRendering();

        makeRenderableAd(Advertisement::POSITION_ONE);
        makeRenderableAd(Advertisement::POSITION_TWO);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.settings'))
            ->assertOk();

        /*
         * The columns must collapse entirely rather than
         * reserving space nobody fills. Matched on the rendered
         * element, since the class names also appear in the
         * stylesheet on every page.
         */
        $response->assertDontSee('aria-label="Advertisement"', false);
    }
);

test(
    'a student can turn advertising on and off again',
    function () {
        seedPlansForRendering();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->put(
                route('student.settings.preferences'),
                ['show_ads' => '1']
            )
            ->assertRedirect(route('student.settings'));

        expect($student->fresh()->show_ads)->toBeTrue();

        $this->actingAs($student)
            ->put(
                route('student.settings.preferences'),
                []
            )
            ->assertRedirect(route('student.settings'));

        expect($student->fresh()->show_ads)->toBeFalse();
    }
);

test(
    'staff never see advertising markup',
    function () {
        seedPlansForRendering();

        makeRenderableAd(Advertisement::POSITION_ONE);
        makeRenderableAd(Advertisement::POSITION_TWO);

        $lecturer = User::factory()->create([
            'role' => 'lecturer',
            'show_ads' => true,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.dashboard'))
            ->assertOk()
            ->assertDontSee('aria-label="Advertisement"', false);
    }
);
