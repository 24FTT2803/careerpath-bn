<?php

use App\Models\InnovationLabNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('admin can attach, replace and remove a picture', function () {
    Storage::fake('public');

    $admin = labUser('admin');

    $this->actingAs($admin)
        ->post(route('admin.business.innovation-lab.store'), [
            'title' => 'With picture',
            'body' => 'Body',
            'image' => UploadedFile::fake()->image('one.jpg'),
        ])
        ->assertSessionHasNoErrors();

    $note = InnovationLabNote::firstOrFail();
    $first = $note->image_path;

    expect($first)->not->toBeNull();
    Storage::disk('public')->assertExists($first);

    $this->actingAs($admin)
        ->put(route('admin.business.innovation-lab.update', $note), [
            'title' => 'With picture',
            'body' => 'Body',
            'image' => UploadedFile::fake()->image('two.png'),
        ]);

    Storage::disk('public')->assertMissing($first);
    $second = $note->fresh()->image_path;
    Storage::disk('public')->assertExists($second);

    $this->actingAs($admin)
        ->put(route('admin.business.innovation-lab.update', $note), [
            'title' => 'With picture',
            'body' => 'Body',
            'remove_image' => '1',
        ]);

    Storage::disk('public')->assertMissing($second);
    expect($note->fresh()->image_path)->toBeNull();
});

test('non-image uploads are rejected', function () {
    Storage::fake('public');

    $this->actingAs(labUser('admin'))
        ->post(route('admin.business.innovation-lab.store'), [
            'title' => 'Bad file',
            'body' => 'Body',
            'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors('image');
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

test('web addresses in a note become links without trailing punctuation', function () {
    $note = new InnovationLabNote([
        'body' => 'Register: https://forms.gle/abc123. Also see http://example.com/a?x=1&y=2, thanks',
    ]);

    $html = (string) $note->bodyHtml();

    expect($html)
        ->toContain('<a href="https://forms.gle/abc123" target="_blank" rel="noopener noreferrer nofollow">https://forms.gle/abc123</a>.')
        ->toContain('<a href="http://example.com/a?x=1&amp;y=2" target="_blank" rel="noopener noreferrer nofollow">http://example.com/a?x=1&amp;y=2</a>,');
});

test('note text cannot inject markup', function () {
    $note = new InnovationLabNote([
        'body' => '<script>alert(1)</script> https://a.com/"onmouseover="x',
    ]);

    $html = (string) $note->bodyHtml();

    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;')
        ->not->toContain('"onmouseover');
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
