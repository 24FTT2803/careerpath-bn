<?php

use App\Models\Notification;
use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use App\Services\Lecturer\LecturerScopeResolver;
use App\Services\Student\StudentAttention;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(FeatureDefinitionSeeder::class);
    $this->seed(PlanSeeder::class);
});

function attentionStudent(array $attributes = []): User
{
    return User::factory()->create(array_merge(['role' => 'student'], $attributes));
}

function makePremium(User $student): void
{
    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ]);
}

function addGeneration(User $student, int $number, string $status = RecommendationGeneration::STATUS_CURRENT): RecommendationGeneration
{
    return $student->recommendationGenerations()->create([
        'generation_number' => $number,
        'status' => $status,
        'driver' => 'mock',
        'schema_version' => '1.0',
        'recommendation_count' => 3,
        'generated_at' => now(),
    ]);
}

test('a new student is nudged to finish their profile, not to generate yet', function () {
    $student = attentionStudent();

    $state = app(StudentAttention::class)->for($student);

    expect($state['profile'])->toBeTrue()
        ->and($state['recommendations'])->toBeNull()
        ->and($state['history'])->toBe(0)
        ->and($state['any'])->toBeFalse();
});

test('a finished profile with no careers yet shows "ready" on recommendations', function () {
    $student = attentionStudent();

    $service = Mockery::mock(StudentAttention::class, [
        app(EntitlementService::class),
        app(LecturerScopeResolver::class),
    ])->makePartial()->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('profileIsComplete')->andReturn(true);

    $state = $service->for($student);

    expect($state['recommendations'])->toBe('ready')
        ->and($state['profile'])->toBeFalse()
        ->and($state['any'])->toBeTrue();
});

test('outdated results show "update" on recommendations', function () {
    $student = attentionStudent();
    addGeneration($student, 1, RecommendationGeneration::STATUS_OUTDATED);

    expect(app(StudentAttention::class)->for($student)['recommendations'])->toBe('outdated');
});

test('current results show no recommendation badge', function () {
    $student = attentionStudent();
    addGeneration($student, 1);

    expect(app(StudentAttention::class)->for($student)['recommendations'])->toBeNull();
});

test('history counts new results until the student opens history', function () {
    $student = attentionStudent();
    makePremium($student);
    addGeneration($student, 1);
    addGeneration($student, 2);

    expect(app(StudentAttention::class)->for($student)['history'])->toBe(2);

    $this->actingAs($student)->get(route('student.history'))->assertOk();

    expect((new StudentAttention(
        app(EntitlementService::class),
        app(LecturerScopeResolver::class)
    ))->for($student->fresh())['history'])->toBe(0);

    $this->travel(5)->minutes();
    addGeneration($student, 3);

    expect((new StudentAttention(
        app(EntitlementService::class),
        app(LecturerScopeResolver::class)
    ))->for($student->fresh())['history'])->toBe(1);
});

test('free students get no history badge because history is a premium feature', function () {
    $student = attentionStudent();
    addGeneration($student, 1);

    expect(app(StudentAttention::class)->for($student)['history'])->toBe(0);
});

test('the menu shows the history badge and the avatar dot', function () {
    $student = attentionStudent();
    makePremium($student);
    addGeneration($student, 1);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('1 new')
        ->assertSee('Something new for you');
});

test('the bell rings when there are unread notifications', function () {
    $student = attentionStudent();

    Notification::create([
        'user_id' => $student->id,
        'type' => 'system',
        'title' => 'Hello',
        'message' => 'Test',
    ]);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('fa-bell attn-ring', false);
});

test('the dashboard nudges an unfinished profile', function () {
    $student = attentionStudent();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Complete your profile so we can match you with the right careers!');
});

test('staff see how many student IDs are waiting for verification', function () {
    $this->seed(OrganisationGroupSeeder::class);

    attentionStudent(['student_id' => '24FTT0001']);
    attentionStudent(['student_id' => '24FTT0002']);
    attentionStudent(['student_id' => '24FTT0003'])->forceFill(['student_id_verified_at' => now()])->save();

    $admin = User::factory()->create(['role' => 'admin']);

    expect(app(StudentAttention::class)->pendingStudentIds($admin))->toBe(2);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('2 students need their student ID verified.')
        ->assertSee('data-attn-auto="pending-ids-2"', false);

    /* A lecturer only counts students in the classes they teach. */
    $class = OrganisationGroup::where('name', 'DADT04')->firstOrFail();
    $lecturer = User::factory()->create(['role' => 'lecturer']);
    $lecturer->assignedGroups()->attach($class->id);

    $mine = attentionStudent(['student_id' => '24FTT0004']);
    $mine->groupMemberships()->create(['organisation_group_id' => $class->id]);

    expect(app(StudentAttention::class)->pendingStudentIds($lecturer))->toBe(1);
});

test('lecturers are told about pending IDs in their own classes', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $class = OrganisationGroup::where('name', 'DADT04')->firstOrFail();
    $lecturer = User::factory()->create(['role' => 'lecturer']);
    $lecturer->assignedGroups()->attach($class->id);

    $student = attentionStudent(['student_id' => '24FTT0005']);
    $student->groupMemberships()->create(['organisation_group_id' => $class->id]);

    $this->actingAs($lecturer)
        ->get(route('lecturer.dashboard'))
        ->assertOk()
        ->assertSee('1 student needs their student ID verified in your classes.');
});

test('the BIICF counts in the admin sidebar explain themselves', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('ICT sub-sectors available in the BIICF framework.', false)
        ->assertSee('available for career matching.', false)
        ->assertSee('available for students to choose from.', false)
        ->assertSee('available to suggest to students.', false);
});
