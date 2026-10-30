<?php

use App\Models\BusinessSponsor;
use App\Models\FeatureDefinition;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed([FeatureDefinitionSeeder::class, PlanSeeder::class]);
    $this->student = User::factory()->create(['role' => 'student']);
});

test('free students can see feature restrictions and actual quota settings', function () {
    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Your feature access')
        ->assertSeeText('Default plan')
        ->assertSeeText('Not included in your current plan.')
        ->assertSeeText('3 uses every 1 month(s)');
});

test('active premium dates are displayed in the business timezone', function () {
    $this->travelTo(Carbon\Carbon::parse('2026-10-30 12:00:00', 'UTC'));
    UserPlanGrant::create([
        'user_id' => $this->student->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'trial',
        'starts_at' => '2026-10-01 00:00:00',
        'ends_at' => '2026-11-01 15:59:59',
        'is_active' => true,
    ]);

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Premium trial')
        ->assertSeeText('01 Oct 2026, 08:00 AM')
        ->assertSeeText('01 Nov 2026, 11:59 PM')
        ->assertSeeText('Asia/Brunei');
});

test('open ended premium access does not invent an expiry date', function () {
    UserPlanGrant::create([
        'user_id' => $this->student->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Direct account grant')
        ->assertSeeText('No scheduled end date')
        ->assertSeeText('Start date (grant created)')
        ->assertDontSeeText('Not specified; access is currently active');
});

test('maintenance and inherited restrictions explain why a feature is unavailable', function () {
    FeatureDefinition::where('key', 'career_recommendations.enabled')
        ->update(['global_enabled' => false]);

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Temporarily unavailable due to maintenance.')
        ->assertSeeText('is under maintenance.');
});

test('scheduled and expired direct grants appear without activating them', function () {
    foreach ([
        ['starts_at' => now()->subMonths(2), 'ends_at' => now()->subMonth()],
        ['starts_at' => now()->addMonth(), 'ends_at' => now()->addMonths(2)],
    ] as $dates) {
        UserPlanGrant::create($dates + [
            'user_id' => $this->student->id,
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'source' => 'admin',
            'is_active' => true,
        ]);
    }

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Premium Access History')
        ->assertSeeText('Past and upcoming Premium access.')
        ->assertDontSeeText('Expired')
        ->assertDontSeeText('Inactive')
        ->assertSeeText(now()->addMonth()->timezone('Asia/Brunei')->format('d M Y, h:i A'))
        ->assertSeeText('Default plan');
});

test('another students premium dates are not displayed', function () {
    UserPlanGrant::create([
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'starts_at' => now()->addYears(2),
        'ends_at' => now()->addYears(3),
        'is_active' => true,
    ]);

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertDontSeeText('Premium Access History')
        ->assertDontSeeText('Direct account grant');
});

test('sponsored students see the effective sponsorship window', function () {
    $organisation = Organisation::create(['name' => 'Plan Institute', 'code' => 'PLAN-INSTITUTE']);
    $type = OrganisationGroupType::create(['organisation_id' => $organisation->id, 'name' => 'Class']);
    $group = OrganisationGroup::create([
        'organisation_id' => $organisation->id, 'group_type_id' => $type->id,
        'name' => 'Plan Class', 'code' => 'PLAN-CLASS',
    ]);
    $this->student->organisationGroups()->attach($group->id);
    $sponsor = BusinessSponsor::create(['name' => 'Plan Sponsor', 'code' => 'PLAN-SPONSOR']);
    SponsoredAccessGrant::create([
        'business_sponsor_id' => $sponsor->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'organisation_id' => $organisation->id,
        'organisation_group_id' => $group->id,
        'funding_type' => 'partnership',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
    ]);

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Organisation sponsorship')
        ->assertSeeText(now()->addMonth()->timezone('Asia/Brunei')->format('d M Y, h:i A'));
});

test('history omits statuses and labels the creation date fallback honestly', function () {
    $grant = UserPlanGrant::create([
        'user_id' => $this->student->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => false,
        'ends_at' => now()->subDay(),
    ]);

    $this->actingAs($this->student)->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('Premium Access History')
        ->assertSeeText('Start date (grant created)')
        ->assertSeeText($grant->created_at->timezone('Asia/Brunei')->format('d M Y, h:i A'))
        ->assertDontSeeText('Inactive')
        ->assertDontSee('<span class="label">Status</span>', false);
});
