<?php

use App\Models\InnovationLabNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function labUser(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

test('admin can post a note', function () {
    $admin = labUser('admin');

    $this->actingAs($admin)
        ->post(route('admin.business.innovation-lab.store'), [
            'title' => 'New AI adviser',
            'body' => 'We are improving the adviser.',
            'is_published' => '1',
        ])
        ->assertRedirect(route('admin.business.innovation-lab.index'));

    $this->assertDatabaseHas('innovation_lab_notes', [
        'title' => 'New AI adviser',
        'created_by' => $admin->id,
        'is_published' => true,
    ]);
});

test('admin can edit and delete a note', function () {
    $note = InnovationLabNote::create([
        'title' => 'Old',
        'body' => 'Old body',
        'is_published' => true,
    ]);

    $this->actingAs(labUser('admin'))
        ->put(route('admin.business.innovation-lab.update', $note), [
            'title' => 'Updated',
            'body' => 'Updated body',
        ])
        ->assertRedirect();

    expect($note->fresh())
        ->title->toBe('Updated')
        ->is_published->toBeFalse();

    $this->actingAs(labUser('admin'))
        ->delete(route('admin.business.innovation-lab.destroy', $note))
        ->assertRedirect();

    $this->assertDatabaseMissing('innovation_lab_notes', ['id' => $note->id]);
});

test('title and body are required', function () {
    $this->actingAs(labUser('admin'))
        ->post(route('admin.business.innovation-lab.store'), [])
        ->assertSessionHasErrors(['title', 'body']);
});

test('students cannot manage notes', function () {
    $this->actingAs(labUser('student'))
        ->get(route('admin.business.innovation-lab.index'))
        ->assertForbidden();

    $this->actingAs(labUser('student'))
        ->post(route('admin.business.innovation-lab.store'), [
            'title' => 'x',
            'body' => 'y',
        ])
        ->assertForbidden();
});

test('students see published notes only', function () {
    InnovationLabNote::create([
        'title' => 'Visible note',
        'body' => 'Shown',
        'is_published' => true,
    ]);

    InnovationLabNote::create([
        'title' => 'Hidden draft',
        'body' => 'Not shown',
        'is_published' => false,
    ]);

    $this->actingAs(labUser('student'))
        ->get(route('student.innovation-lab'))
        ->assertOk()
        ->assertSee('Visible note')
        ->assertDontSee('Hidden draft');
});
