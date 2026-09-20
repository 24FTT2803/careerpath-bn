<?php

use App\Models\Advertisement;
use App\Models\Plan;
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
    /*
     * The free plan carries advertising, so no preference is
     * needed for advertisements to appear.
     */
    return User::factory()->create([
        'role' => 'student',
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
    'a premium student who has not opted in sees no advertising markup',
    function () {
        seedPlansForRendering();

        makeRenderableAd(Advertisement::POSITION_ONE);
        makeRenderableAd(Advertisement::POSITION_TWO);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->planGrants()->create([
            'plan_id' => App\Models\Plan::where('code', 'premium')
                ->value('id'),
            'source' => 'admin',
            'is_active' => true,
        ]);

        $student = $student->fresh();

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
    'a premium student can turn advertising on and off again',
    function () {
        seedPlansForRendering();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->planGrants()->create([
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'source' => 'admin',
            'is_active' => true,
        ]);

        $student = $student->fresh();

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

test(
    'a free student cannot switch advertising off',
    function () {
        seedPlansForRendering();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        /*
         * Advertising funds free access, so the request is
         * refused rather than silently ignored.
         */
        $this->actingAs($student)
            ->put(
                route('student.settings.preferences'),
                []
            )
            ->assertSessionHasErrors('show_ads');
    }
);

test(
    'a student can upgrade to premium without payment',
    function () {
        seedPlansForRendering();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->post(route('student.settings.upgrade'))
            ->assertRedirect(route('student.settings'));

        expect(
            app(App\Services\Business\EntitlementService::class)
                ->planFor($student->fresh())
                ->code
        )->toBe('premium');

        /*
         * Premium starts ad free even for a student who had
         * advertising while on the free plan.
         */
        expect($student->fresh()->show_ads)->toBeFalse();
    }
);

test(
    'the advertising setting is hidden when a plan excludes it',
    function () {
        seedPlansForRendering();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        Plan::where('code', 'free')
            ->firstOrFail()
            ->features()
            ->where('key', 'ads.available')
            ->update(['value' => json_encode(false)]);

        $this->actingAs($student)
            ->get(route('student.settings'))
            ->assertOk()
            ->assertDontSee('Show me advertisements');
    }
);

test(
    'a rotating placement renders every advertisement in it',
    function () {
        seedPlansForRendering();

        App\Models\AdvertisementSlot::where(
            'position',
            Advertisement::POSITION_ONE
        )->update([
            'rotation_enabled' => true,
            'rotation_size' => 2,
        ]);

        makeRenderableAd(Advertisement::POSITION_ONE);

        Advertisement::create([
            'title' => 'Sponsor banner three',
            'type' => Advertisement::TYPE_IMAGE,
            'external_url' => 'https://example.com/three.png',
            'position' => Advertisement::POSITION_ONE,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs(optedInStudent())
            ->get(route('student.settings'))
            ->assertOk();

        /*
         * Everything in the rotation is rendered and cycled in
         * the browser, so both have to be present.
         */
        expect(
            substr_count($response->getContent(), 'ad-slot-item')
        )->toBeGreaterThanOrEqual(2);

        $response->assertSee('data-ad-rotate', false);
    }
);
