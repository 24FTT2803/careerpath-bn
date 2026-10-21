<?php

use App\Models\InnovationLabNote;
use App\Models\Notification;
use App\Models\User;
use App\Services\Student\StudentAttention;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(FeatureDefinitionSeeder::class);
    $this->seed(PlanSeeder::class);

    $this->admin = User::factory()->create(['role' => 'admin']);
});

function postLabNote(User $admin, array $overrides = []): void
{
    test()->actingAs($admin)
        ->post(route('admin.business.innovation-lab.store'), array_merge([
            'title' => 'Career Adviser now remembers your goals',
            'body' => 'A short update from the team.',
            'is_published' => '1',
        ], $overrides))
        ->assertSessionHasNoErrors();
}

function labAttention(User $student): array
{
    /* The service caches per request, so start fresh each time. */
    app()->forgetScopedInstances();

    return app(StudentAttention::class)->for($student->fresh());
}

test('publishing a post notifies every student once', function () {
    $students = User::factory()->count(3)->create(['role' => 'student', 'created_at' => now()->subDay()]);
    $lecturer = User::factory()->create(['role' => 'lecturer']);

    postLabNote($this->admin);

    $note = InnovationLabNote::firstOrFail();

    expect($note->published_at)->not->toBeNull();

    foreach ($students as $student) {
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'innovation',
            'title' => 'New in the Innovation Lab',
            'message' => 'New post: Career Adviser now remembers your goals',
            'is_read' => false,
        ]);
    }

    expect(Notification::where('user_id', $lecturer->id)->count())->toBe(0)
        ->and(Notification::where('user_id', $this->admin->id)->count())->toBe(0);

    /* Editing the live post does not send it again. */
    $this->actingAs($this->admin)
        ->put(route('admin.business.innovation-lab.update', $note), [
            'title' => 'Edited title',
            'body' => 'Edited body',
            'is_published' => '1',
        ])
        ->assertSessionHas('success', 'Note updated.');

    expect(Notification::where('type', 'innovation')->count())->toBe(3);
});

test('a draft notifies nobody until it is published', function () {
    User::factory()->create(['role' => 'student', 'created_at' => now()->subDay()]);

    postLabNote($this->admin, ['is_published' => '0']);

    $note = InnovationLabNote::firstOrFail();

    expect($note->published_at)->toBeNull()
        ->and(Notification::where('type', 'innovation')->count())->toBe(0);

    $this->actingAs($this->admin)
        ->put(route('admin.business.innovation-lab.update', $note), [
            'title' => $note->title,
            'body' => $note->body,
            'is_published' => '1',
        ])
        ->assertSessionHas('success', 'Note published. 1 student was notified.');

    expect(Notification::where('type', 'innovation')->count())->toBe(1);

    /* Unpublishing and publishing again does not repeat it. */
    $this->actingAs($this->admin)->put(route('admin.business.innovation-lab.update', $note), ['title' => $note->title, 'body' => $note->body]);
    $this->actingAs($this->admin)->put(route('admin.business.innovation-lab.update', $note), ['title' => $note->title, 'body' => $note->body, 'is_published' => '1']);

    expect(Notification::where('type', 'innovation')->count())->toBe(1);
});

test('new posts show a badge until the student opens the lab', function () {
    $student = User::factory()->create(['role' => 'student', 'created_at' => now()->subDay()]);

    expect(labAttention($student)['innovation'])->toBe(0);

    postLabNote($this->admin);

    $attention = labAttention($student);

    expect($attention['innovation'])->toBe(1)
        ->and($attention['any'])->toBeTrue();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('There is a new post in the Innovation Lab. Take a look!')
        ->assertSee('1 new');

    $this->actingAs($student)
        ->get(route('student.innovation-lab'))
        ->assertOk()
        ->assertSee('Posted since your last visit');

    expect(labAttention($student)['innovation'])->toBe(0)
        ->and(Notification::where('user_id', $student->id)->where('type', 'innovation')->where('is_read', false)->count())->toBe(0);

    $this->actingAs($student)
        ->get(route('student.innovation-lab'))
        ->assertDontSee('Posted since your last visit');
});

test('posts from before a student joined are not flagged as new', function () {
    InnovationLabNote::create([
        'title' => 'Old news',
        'body' => 'From last month.',
        'is_published' => true,
        'published_at' => now()->subMonth(),
    ]);

    $student = User::factory()->create(['role' => 'student']);

    expect(labAttention($student)['innovation'])->toBe(0);
});

test('students can mark innovation notifications as read', function () {
    $student = User::factory()->create(['role' => 'student', 'created_at' => now()->subDay()]);

    postLabNote($this->admin);

    $notification = Notification::where('user_id', $student->id)->firstOrFail();

    $this->actingAs($student)
        ->post(route('student.notifications.read', $notification->id))
        ->assertRedirect();

    expect($notification->fresh()->is_read)->toBeTrue();
});
