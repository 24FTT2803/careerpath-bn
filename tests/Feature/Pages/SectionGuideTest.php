<?php

use App\Models\SectionGuide;
use App\Models\User;
use App\Support\SectionGuideDefaults;

/*
 * The '?' beside each section of Edit Profile and Career
 * Analysis, the help it opens, and the admin page that edits it.
 */

function guideAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

test('every guide starts with its default text', function () {
    expect(SectionGuide::count())->toBe(count(SectionGuideDefaults::all()));

    foreach (SectionGuideDefaults::all() as $key => $default) {
        expect(SectionGuide::where('key', $key)->sole()->title)->toBe($default['title']);
    }
});

test('every section of both pages has its icon', function () {
    $views = file_get_contents(resource_path('views/student/profile/edit.blade.php'))
        .file_get_contents(resource_path('views/student/recommendations/analysis.blade.php'));

    foreach (array_keys(SectionGuideDefaults::all()) as $key) {
        expect($views)->toContain('<x-section-guide guide="'.$key.'" />');
    }
});

test('edit profile shows the icons and their help', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('data-guide-open="profile.qualifications"', false)
        ->assertSee('id="cpbn-guide-profile.skills"', false)
        ->assertSee('Choose the BIICF competencies you have');
});

test('the help window is written once however many icons there are', function () {
    $html = $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.profile.edit'))
        ->getContent();

    expect(substr_count($html, 'id="cpbnGuideOverlay"'))->toBe(1)
        ->and(substr_count($html, 'data-guide-open='))->toBe(7);
});

test('a guide with a YouTube link embeds the video', function () {
    SectionGuide::where('key', 'analysis.readiness')
        ->update(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

    $this->blade('<x-section-guide guide="analysis.readiness" />')
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
});

test('a guide with no video is text only', function () {
    $this->blade('<x-section-guide guide="analysis.readiness" />')
        ->assertSee('Calculated by CareerPath')
        ->assertDontSee('<iframe', false)
        ->assertDontSee('<video', false);
});

test('help text is escaped and keeps its paragraphs', function () {
    SectionGuide::where('key', 'analysis.plan')->update([
        'body' => "First <b>part</b>.\n\nSecond part.",
    ]);

    $this->blade('<x-section-guide guide="analysis.plan" />')
        ->assertSee('<p class="cpbn-guide-text">First &lt;b&gt;part&lt;/b&gt;.</p>', false)
        ->assertSee('<p class="cpbn-guide-text">Second part.</p>', false);
});

test('an unknown guide renders nothing', function () {
    $this->blade('<x-section-guide guide="nowhere.at-all" />')
        ->assertDontSee('data-guide-open', false);
});

test('admins see every guide', function () {
    $this->actingAs(guideAdmin())
        ->get(route('admin.business.guides.index'))
        ->assertOk()
        ->assertSee('Edit Profile')
        ->assertSee('Career Analysis')
        ->assertSee('Skill Gaps');
});

test('an admin can rewrite a guide and add a video', function () {
    $guide = SectionGuide::where('key', 'profile.skills')->sole();

    $this->actingAs(guideAdmin())
        ->put(route('admin.business.guides.update', $guide), [
            'title' => 'Your skills',
            'body' => 'New help text.',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])
        ->assertRedirect(route('admin.business.guides.index'));

    expect($guide->fresh())
        ->title->toBe('Your skills')
        ->body->toBe('New help text.')
        ->video_url->toBe('https://youtu.be/dQw4w9WgXcQ');

    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.profile.edit'))
        ->assertSee('New help text.')
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
});

test('a video link must be a full https address', function () {
    $guide = SectionGuide::where('key', 'profile.skills')->sole();

    $this->actingAs(guideAdmin())
        ->put(route('admin.business.guides.update', $guide), [
            'title' => 'Skills',
            'body' => 'Text.',
            'video_url' => 'javascript:alert(1)',
        ])
        ->assertSessionHasErrors('video_url');
});

test('restoring a guide brings back its default text but keeps the video', function () {
    $guide = SectionGuide::where('key', 'profile.skills')->sole();

    $guide->update([
        'title' => 'Changed',
        'body' => 'Changed.',
        'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
    ]);

    $this->actingAs(guideAdmin())
        ->post(route('admin.business.guides.restore', $guide))
        ->assertRedirect(route('admin.business.guides.edit', $guide));

    expect($guide->fresh())
        ->title->toBe(SectionGuideDefaults::all()['profile.skills']['title'])
        ->body->toBe(SectionGuideDefaults::all()['profile.skills']['body'])
        ->video_url->toBe('https://youtu.be/dQw4w9WgXcQ');
});

test('students cannot edit guides', function () {
    $guide = SectionGuide::where('key', 'profile.skills')->sole();

    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->put(route('admin.business.guides.update', $guide), [
            'title' => 'Hacked',
            'body' => 'Hacked.',
        ]);

    expect($guide->fresh()->title)->toBe('Skills & Competencies');
});
