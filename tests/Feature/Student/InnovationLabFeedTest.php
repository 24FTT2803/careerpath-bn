<?php

use App\Models\InnovationLabNote;
use App\Models\User;
use App\Services\Student\StudentAttention;

/*
 * The lab as a feed rather than a changelog.
 *
 * Every post led with its own title and a bare date, which is
 * how release notes read — a list nobody is in. These are
 * written by people to the students using what they built, so a
 * post says who wrote it and how long ago before it says
 * anything else.
 */

function labNoteBy(?User $author, array $overrides = []): InnovationLabNote
{
    $publishedAt = $overrides['published_at'] ?? now()->subDays(4);

    unset($overrides['published_at']);

    $note = InnovationLabNote::create(array_merge([
        'title' => 'Programming languages have their own section now',
        'body' => 'A short update from the team.',
        'created_by' => $author?->id,
        'is_published' => true,
    ], $overrides));

    /*
     * forceFill, as the notifier does. published_at is not
     * fillable — it is stamped once, when a post is first
     * published — so passing it to create drops it silently and
     * leaves every fixture looking like it was posted this
     * second.
     */
    $note->forceFill(['published_at' => $publishedAt])->saveQuietly();

    return $note->fresh();
}

function labReader(): User
{
    return User::factory()->create([
        'role' => 'student',
        'created_at' => now()->subMonths(2),
    ]);
}

describe('a post', function () {
    test('says who wrote it and how long ago', function () {
        $author = User::factory()->create([
            'role' => 'admin',
            'name' => 'Hamiz Suhaimi',
        ]);

        labNoteBy($author);

        $this->actingAs(labReader())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('Hamiz Suhaimi')
            ->assertShows('4 days ago')
            /* The day itself is still there, on hover. */
            ->assertShows(now()->subDays(4)->format('d M Y'));
    });

    test('a post whose author has gone is still signed', function () {
        /*
         * created_by is nulled when an admin account is removed.
         * A post with no name against it reads as though nobody
         * wrote it.
         */
        labNoteBy(null);

        $this->actingAs(labReader())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('CareerPath BN');
    });

    test('its picture runs to the edges of the card', function () {
        /*
         * It used to sit in a black letterboxed frame, which is
         * how a video player presents a still, not how a feed
         * presents a picture.
         */
        labNoteBy(User::factory()->create(['role' => 'admin']), [
            'image_path' => 'innovation-lab/example.png',
        ]);

        $response = $this->actingAs(labReader())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('class="lab-media"')
            ->assertHides('lab-image-frame');

        /*
         * And before the words. A feed shows the thing and
         * captions it; the other way round is a report.
         */
        $html = $response->getContent();

        expect(mb_strpos($html, 'class="lab-media"'))
            ->toBeLessThan(mb_strpos($html, 'class="lab-title"'));
    });
});

describe('what the student has not seen', function () {
    test('an unseen post is marked, once', function () {
        /*
         * One dot on the byline. It was a pill beside the title
         * and a gold ring around the whole card, which on a page
         * where most posts are unread outlined most of the page.
         */
        labNoteBy(User::factory()->create(['role' => 'admin']));

        $response = $this->actingAs(labReader())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('class="lab-fresh"');

        expect(substr_count($response->getContent(), 'class="lab-fresh"'))->toBe(1);
    });

    test('a post they have already seen is not marked', function () {
        $student = labReader();

        labNoteBy(
            User::factory()->create(['role' => 'admin']),
            ['published_at' => now()->subDays(10)]
        );

        app(StudentAttention::class)->markSeen($student, StudentAttention::INNOVATION);

        app()->forgetScopedInstances();

        $this->actingAs($student->fresh())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertHides('class="lab-fresh"');
    });
});

describe('an empty lab', function () {
    test('it says what the page is for rather than that it is empty', function () {
        $this->actingAs(labReader())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            /* One line of the template's own wrapping, not a sentence that spans two. */
            ->assertShows('Nothing posted yet.');
    });
});
