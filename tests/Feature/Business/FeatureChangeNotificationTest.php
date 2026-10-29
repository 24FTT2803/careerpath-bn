<?php

use App\Models\BusinessSponsor;
use App\Models\FeatureDefinition;
use App\Models\Notification;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\Plan;
use App\Models\SponsoredAccessFeatureOverride;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\FeatureChangeNotifier;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Database\Eloquent\Collection;

beforeEach(function () {
    $this->seed([FeatureDefinitionSeeder::class, PlanSeeder::class]);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->premium = User::factory()->create(['role' => 'student']);
    UserPlanGrant::create([
        'user_id' => $this->premium->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ]);
});

test('global closure and restoration notify only students whose access changes', function () {
    $feature = FeatureDefinition::where('key', 'career_recommendations.download.enabled')->firstOrFail();
    $url = route('admin.business.features.global', $feature);

    $this->actingAs($this->admin)->put($url, ['global_enabled' => 0])
        ->assertSessionHasNoErrors();

    $notice = Notification::where('user_id', $this->premium->id)->sole();
    expect($notice->message)->toContain($feature->name, 'maintenance')
        ->and($notice->is_read)->toBeFalse()
                ->and($notice->link)->toBe(route('student.settings', absolute: false).'#your-plan');
    expect(Notification::where('user_id', $this->student->id)->count())->toBe(0);
    expect(Notification::where('user_id', $this->admin->id)->count())->toBe(0);

    $this->actingAs($this->premium)->getJson(route('student.notifications.recent'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('notifications.0.title', 'Feature access updated');

    $this->actingAs($this->admin)->put($url, ['global_enabled' => 0]);
    expect(Notification::count())->toBe(1);

    $this->actingAs($this->admin)->put($url, ['global_enabled' => 1]);
    expect(Notification::where('user_id', $this->premium->id)->count())->toBe(2)
        ->and(Notification::where('user_id', $this->student->id)->count())->toBe(0);
    expect(Notification::latest('id')->first()->message)->toContain('now available');
});

test('a plan save combines related changes and does not notify another plan', function () {
    $free = Plan::where('code', 'free')->firstOrFail();
    $analysis = FeatureDefinition::where('key', 'career_recommendations.detailed_analysis.enabled')->firstOrFail();
    $adviser = FeatureDefinition::where('key', 'career_adviser.enabled')->firstOrFail();
    $payload = ['features' => [
        $analysis->id => ['value' => 0],
        $adviser->id => ['value' => 0],
    ]];

    $this->actingAs($this->admin)->put(route('admin.business.plans.update', $free), $payload)
        ->assertSessionHasNoErrors();

    $notice = Notification::where('user_id', $this->student->id)->sole();
    expect($notice->message)->toContain($analysis->name, $adviser->name, 'Not included in your current plan.')
        ->and(Notification::where('user_id', $this->premium->id)->count())->toBe(0);

    $this->actingAs($this->admin)->put(route('admin.business.plans.update', $free), $payload);
    expect(Notification::count())->toBe(1);
});

test('quota changes notify affected students with the updated allowance', function () {
    $free = Plan::where('code', 'free')->firstOrFail();
    $quota = FeatureDefinition::where('key', 'career_recommendations.generation_quota')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.business.plans.update', $free), [
        'features' => [$quota->id => [
            'mode' => 'recurring', 'amount' => 5, 'period_value' => 1, 'period_unit' => 'month',
        ]],
    ])->assertSessionHasNoErrors();

    expect(Notification::where('user_id', $this->student->id)->sole()->message)
        ->toContain('5 uses every 1 month(s)');
    expect(Notification::where('user_id', $this->premium->id)->count())->toBe(0);
});

test('edits to features hidden by a parent do not notify students', function () {
    $free = Plan::where('code', 'free')->firstOrFail();
    $parent = FeatureDefinition::where('key', 'career_recommendations.enabled')->firstOrFail();
    $parent->update(['global_enabled' => false]);
    $feature = FeatureDefinition::where('key', 'career_recommendations.detailed_analysis.enabled')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.business.plans.update', $free), [
        'features' => [$feature->id => ['value' => 0]],
    ])->assertSessionHasNoErrors();

    expect(Notification::count())->toBe(0);
});

test('invalid feature edits do not send notifications or change access', function () {
    $feature = FeatureDefinition::where('key', 'career_adviser.enabled')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.business.features.global', $feature), [
        'global_enabled' => 'invalid',
    ])->assertSessionHasErrors('global_enabled');

    expect(Notification::count())->toBe(0)
        ->and($feature->fresh()->global_enabled)->toBeTrue();
});

test('a sponsorship override prevents an irrelevant plan change announcement', function () {
    $organisation = Organisation::create(['name' => 'Notice Test Institute', 'code' => 'NOTICE-INSTITUTE']);
    $type = OrganisationGroupType::create(['organisation_id' => $organisation->id, 'name' => 'Class']);
    $group = OrganisationGroup::create([
        'organisation_id' => $organisation->id, 'group_type_id' => $type->id,
        'name' => 'Notice Class', 'code' => 'NOTICE-CLASS',
    ]);
    $sponsored = User::factory()->create(['role' => 'student']);
    $sponsored->organisationGroups()->attach($group->id);
    $sponsor = BusinessSponsor::create(['name' => 'Notice Sponsor', 'code' => 'NOTICE-SPONSOR']);
    $free = Plan::where('code', 'free')->firstOrFail();
    $grant = SponsoredAccessGrant::create([
        'business_sponsor_id' => $sponsor->id, 'plan_id' => $free->id,
        'organisation_id' => $organisation->id, 'organisation_group_id' => $group->id,
        'funding_type' => 'partnership',
    ]);
    $feature = FeatureDefinition::where('key', 'career_adviser.enabled')->firstOrFail();
    SponsoredAccessFeatureOverride::create([
        'sponsored_access_grant_id' => $grant->id,
        'feature_definition_id' => $feature->id, 'value' => true,
    ]);

    $this->actingAs($this->admin)->put(route('admin.business.plans.update', $free), [
        'features' => [$feature->id => ['value' => 0]],
    ])->assertSessionHasNoErrors();

    expect(Notification::where('user_id', $sponsored->id)->count())->toBe(0)
        ->and(Notification::where('user_id', $this->student->id)->count())->toBe(1);
});

test('failed configuration saves roll back access changes and announcements', function () {
    $feature = FeatureDefinition::where('key', 'career_adviser.enabled')->firstOrFail();

    expect(fn () => app(FeatureChangeNotifier::class)->during(
        new Collection([$feature]),
        function () use ($feature): void {
            $feature->update(['global_enabled' => false]);
            throw new RuntimeException('Simulated save failure');
        }
    ))->toThrow(RuntimeException::class, 'Simulated save failure');

    expect($feature->fresh()->global_enabled)->toBeTrue()
        ->and(Notification::count())->toBe(0);
});
