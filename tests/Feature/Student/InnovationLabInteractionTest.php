<?php

use App\Models\InnovationLabInterest;
use App\Models\InnovationLabNote;
use App\Models\InnovationLabReaction;
use App\Models\User;

/*
 * Reacting to a post, and putting your hand up for one.
 *
 * Reactions accumulate beside each other rather than replacing
 * one another: a student may say a thing is clever and that they
 * have questions about it at the same time, which is the whole
 * difference between a set of reactions and a single like.
 *
 * Interest is only asked for where a post asked for it. A note
 * about a bug fix is not something to join, and a count under
 * one means nothing.
 */

function joinableNote(array $overrides = []): InnovationLabNote
{
    $note = InnovationLabNote::create(array_merge([
        'title' => 'We are building a peer mentoring scheme',
        'body' => 'Second year students pairing with first years.',
        'is_published' => true,
        'accepts_interest' => true,
    ], $overrides));

    $note->forceFill(['published_at' => now()->subDay()])->saveQuietly();

    return $note->fresh();
}

function labStudent(): User
{
    return User::factory()->create([
        'role' => 'student',
        'created_at' => now()->subMonths(2),
    ]);
}

describe('reacting', function () {
    test('a student can put several different emoji on one post', function () {
        $note = joinableNote();
        $student = labStudent();

        foreach (['👍', '💡', '🤔'] as $emoji) {
            $this->actingAs($student)
                ->post(route('student.innovation-lab.react', $note), ['emoji' => $emoji])
                ->assertRedirect();
        }

        expect(InnovationLabReaction::where('note_id', $note->id)
            ->where('user_id', $student->id)
            ->pluck('emoji')
            ->all())
            ->toEqualCanonicalizing(['👍', '💡', '🤔']);
    });

    test('pressing the same one again takes it back', function () {
        $note = joinableNote();
        $student = labStudent();

        $this->actingAs($student)
            ->post(route('student.innovation-lab.react', $note), ['emoji' => '🔥']);

        $this->actingAs($student)
            ->post(route('student.innovation-lab.react', $note), ['emoji' => '🔥']);

        expect(InnovationLabReaction::where('note_id', $note->id)->count())->toBe(0);
    });

    test('an emoji the palette never offered is still allowed', function () {
        /*
         * The palette is an offer, not the rule. 💀 is not one of
         * the thirty-six and a student whose keyboard has it may
         * still use it.
         */
        $note = joinableNote();

        $this->actingAs(labStudent())
            ->post(route('student.innovation-lab.react', $note), ['emoji' => '💀'])
            ->assertSessionHasNoErrors();

        expect(InnovationLabReaction::where('emoji', '💀')->count())->toBe(1);
    });

    test('text is refused', function () {
        /*
         * What the rule is actually for. Without it this column
         * is a short free-text box in every student's reach.
         */
        $note = joinableNote();

        foreach (['hello', 'a', '5', '<script>alert(1)</script>', '👍 nice'] as $notAnEmoji) {
            $this->actingAs(labStudent())
                ->post(route('student.innovation-lab.react', $note), ['emoji' => $notAnEmoji])
                ->assertSessionHasErrors('emoji');
        }

        expect(InnovationLabReaction::count())->toBe(0);
    });

    test('an unpublished post takes no reactions', function () {
        $note = joinableNote(['is_published' => false]);

        $this->actingAs(labStudent())
            ->post(route('student.innovation-lab.react', $note), ['emoji' => '👍'])
            ->assertNotFound();
    });

    test('the script is answered with the counts, not a page', function () {
        $note = joinableNote();
        $student = labStudent();

        InnovationLabReaction::create([
            'note_id' => $note->id,
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'emoji' => '👍',
        ]);

        $this->actingAs($student)
            ->postJson(route('student.innovation-lab.react', $note), ['emoji' => '👍'])
            ->assertOk()
            /*
             * The whole map rather than a dotted path, since the
             * key is an emoji and the path parser splits on dots.
             */
            ->assertJsonPath('reactions', ['👍' => 2])
            ->assertJsonPath('mine', ['👍'])
            ->assertJsonPath('interest_count', 0);
    });
});

describe('putting your hand up', function () {
    test('a student can say they are interested, and take it back', function () {
        $note = joinableNote();
        $student = labStudent();

        $this->actingAs($student)
            ->post(route('student.innovation-lab.interest', $note));

        expect(InnovationLabInterest::where('note_id', $note->id)->count())->toBe(1);

        $this->actingAs($student)
            ->post(route('student.innovation-lab.interest', $note));

        expect(InnovationLabInterest::where('note_id', $note->id)->count())->toBe(0);
    });

    test('a post that did not ask for it cannot be joined', function () {
        /*
         * Not only hidden on the page — the request itself is
         * refused, or the count on a post nobody can join would
         * start moving.
         */
        $note = joinableNote(['accepts_interest' => false]);

        $this->actingAs(labStudent())
            ->post(route('student.innovation-lab.interest', $note))
            ->assertNotFound();

        expect(InnovationLabInterest::count())->toBe(0);
    });

    test('the count is who says yes now, not who ever pressed it', function () {
        $note = joinableNote();

        $staying = labStudent();
        $leaving = labStudent();

        $this->actingAs($staying)->post(route('student.innovation-lab.interest', $note));
        $this->actingAs($leaving)->post(route('student.innovation-lab.interest', $note));
        $this->actingAs($leaving)->post(route('student.innovation-lab.interest', $note));

        expect($note->fresh()->interestCount())->toBe(1);
    });
});

describe('the feed', function () {
    test('it shows the counts, and marks the ones that are yours', function () {
        $note = joinableNote();
        $student = labStudent();

        InnovationLabReaction::create([
            'note_id' => $note->id,
            'user_id' => $student->id,
            'emoji' => '🎉',
        ]);

        InnovationLabReaction::create([
            'note_id' => $note->id,
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'emoji' => '🎉',
        ]);

        $this->actingAs($student)
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('lab-react is-mine')
            ->assertShows('<span class="lab-react-count">2</span>');
    });

    test('a post that is open to joining offers the button', function () {
        /*
         * Asserted by the form, not by its words. The script at
         * the foot of the page writes the same words when it
         * redraws the button, so "I am interested" is on every
         * page whether a post offers it or not — and the absent
         * case would have passed on nothing but that.
         */
        joinableNote();

        $this->actingAs(labStudent())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('class="lab-interest-form"');
    });

    test('a post that is not says nothing about joining', function () {
        joinableNote(['accepts_interest' => false]);

        $this->actingAs(labStudent())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertHides('class="lab-interest-form"');
    });

    test('every post offers the palette, reactions or not', function () {
        joinableNote();

        $this->actingAs(labStudent())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->assertShows('class="lab-palette"', 'class="lab-palette-group"');
    });

    test('the title comes before the picture', function () {
        /*
         * The headline says what you are looking at, then the
         * picture, then the detail.
         */
        $note = joinableNote(['image_path' => 'innovation-lab/example.png']);

        $html = $this->actingAs(labStudent())
            ->get(route('student.innovation-lab'))
            ->assertOk()
            ->getContent();

        expect(mb_strpos($html, 'class="lab-title"'))
            ->toBeLessThan(mb_strpos($html, 'class="lab-media"'));
    });

    test('a reaction a student brought themselves is still drawn', function () {
        /*
         * The palette is an offer, not the rule, so a student may
         * react with anything their own keyboard has. Counted
         * only against the palette, such a reaction would be
         * saved and never shown.
         */
        $note = joinableNote();

        InnovationLabReaction::create([
            'note_id' => $note->id,
            'user_id' => labStudent()->id,
            'emoji' => '🦆',
        ]);

        expect($note->fresh()->reactionTally())->toBe(['🦆' => 1]);
    });
});
