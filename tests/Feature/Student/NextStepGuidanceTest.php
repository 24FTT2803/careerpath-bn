<?php

use App\Helpers\NotificationHelper;
use App\Models\Notification;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\RecommendationGeneration;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

/*
 * Telling a student what to do next.
 *
 * The app asked them to sign up and then showed them a dashboard
 * of numbers. Nothing said to fill in the profile, and — the
 * worse half — nothing ever said that the recommendations are
 * generated on request, on a page a student has no particular
 * reason to open. A student could finish everything and never
 * find out there was anything to find out.
 */

function freshStudent(): User
{
    $student = User::factory()->create([
        'role' => 'student',
        'student_id' => '24FTT2803',
    ]);

    $student->profile()->create([]);

    return $student;
}

/**
 * A programme the profile form will accept.
 *
 * The form checks the programme against the organisation's own
 * groups, so a student carrying a programme nobody offers cannot
 * save their profile at all — which is a save these tests need to
 * succeed before they can test what happens after it.
 */
function offeredProgramme(string $name): string
{
    $organisation = Organisation::firstOrCreate(
        ['code' => 'NEXT-STEP-TEST'],
        ['name' => 'Next Step Test Institute']
    );

    $type = OrganisationGroupType::firstOrCreate(
        ['organisation_id' => $organisation->id, 'name' => 'Programme']
    );

    OrganisationGroup::firstOrCreate(
        ['organisation_id' => $organisation->id, 'name' => $name],
        ['group_type_id' => $type->id, 'code' => 'NEXT-STEP-PROG']
    );

    return $name;
}

function finishedStudent(): User
{
    $student = User::factory()->create([
        'role' => 'student',
        'student_id' => '24FTT2804',
        'programme' => offeredProgramme('Diploma in Information Technology'),
        'cgpa' => 3.4,
    ]);

    $student->profile()->create(['nationality' => 'Bruneian']);
    $student->aspirations()->create(['vision_statement' => 'To work in network security.']);
    $student->interests()->create(['interest_name' => 'Problem Solving', 'category' => 'career']);
    $student->competencies()->create([
        'skill_name' => 'Python',
        'category' => 'technical',
        'proficiency_level' => 'intermediate',
    ]);

    return $student;
}

/**
 * What the form actually sends.
 *
 * The whole profile, because that is what a save writes: the user
 * row, the profile row, the aspirations, the interests and the
 * skills are all replaced by what arrives, so anything left out
 * of the payload is cleared. A payload naming only the student
 * wipes the very profile these tests are about and leaves them
 * asserting against an empty one.
 *
 * @return array<string, mixed>
 */
function profilePayload(User $student): array
{
    return [
        'name' => $student->name,
        'student_id' => $student->student_id,
        'programme' => $student->programme,
        'cgpa' => $student->cgpa,
        'nationality' => $student->profile?->nationality,
        'vision_statement' => $student->aspirations?->vision_statement,
        'interests' => $student->interests->pluck('interest_name')->all(),
        'skills' => $student->competencies->pluck('skill_name')->all(),
    ];
}

describe('on the dashboard', function () {
    test('a new student is pointed at their profile, and told what is missing', function () {
        $student = freshStudent();

        expect($student->unfinishedProfileSections())
            ->toContain('Academic information')
            ->toContain('Competencies');

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertShows('Finish your profile')
            ->assertShows('Update my profile')
            ->assertShows('Competencies')
            ->assertShows('nextStepPopup');
    });

    test('a student with a finished profile is pointed at the recommendations', function () {
        /*
         * The step nothing used to mention. Career matches and
         * the button that generates them share a page, so a
         * student who never opens it never sees either.
         */
        $this->actingAs(finishedStudent())
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertShows('Your profile is ready')
            ->assertShows('Generate recommendations')
            ->assertHides('Finish your profile');
    });

    test('a student who has done both is told nothing', function () {
        $student = finishedStudent();

        $generation = $student->recommendationGenerations()->create([
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => 'mock',
            'schema_version' => '1.0',
            'recommendation_count' => 1,
            'generated_at' => now(),
        ]);

        $student->careerRecommendations()->create([
            'recommendation_generation_id' => $generation->id,
            'rank' => 1,
            'match_score' => 80,
            'career_readiness_score' => 70,
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertHides('Finish your profile')
            ->assertHides('Your profile is ready');
    });
});

describe('when the profile is finished', function () {
    test('the save says what to do next', function () {
        $student = finishedStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), profilePayload($student))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('generate_prompt', true);
    });

    test('a notification carries the way there', function () {
        $student = finishedStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), profilePayload($student));

        $notification = Notification::where('user_id', $student->id)
            ->where('title', NotificationHelper::PROFILE_COMPLETE)
            ->sole();

        expect($notification->link)->toBe(route('student.recommendations.index', absolute: false));
    });

    test('it is said once, not on every save after it', function () {
        /*
         * A student edits their profile for years after finishing
         * it. Announcing the same news each time is how people
         * learn to ignore the bell.
         */
        $student = finishedStudent();

        foreach (range(1, 3) as $ignored) {
            $this->actingAs($student)
                ->from(route('student.profile.edit'))
                ->put(route('student.profile.update'), profilePayload($student));
        }

        expect(Notification::where('user_id', $student->id)
            ->where('title', NotificationHelper::PROFILE_COMPLETE)
            ->count())->toBe(1);
    });

    test('an unfinished profile is told nothing', function () {
        $student = freshStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), profilePayload($student))
            ->assertSessionMissing('generate_prompt');

        expect(Notification::where('user_id', $student->id)
            ->where('title', NotificationHelper::PROFILE_COMPLETE)
            ->exists())->toBeFalse();
    });
});

describe('when a set is generated', function () {
    /*
     * Generation runs against the mock driver and needs the plans
     * that decide whether it is allowed at all.
     */
    beforeEach(function () {
        $this->seed(FeatureDefinitionSeeder::class);
        $this->seed(PlanSeeder::class);

        /*
         * Generation refuses to run against fewer than three
         * competency-profiled roles, so the framework itself has
         * to be there.
         */
        $this->seed(BiicfJobRoleSeeder::class);
    });

    test('the page is told to make an entrance', function () {
        /*
         * Without this the page reloads looking exactly as it
         * did, with the new matches below the fold — which is how
         * a student presses generate and comes away thinking
         * nothing happened.
         */
        $student = finishedStudent();

        $this->actingAs($student)
            ->post(route('student.recommendations.generate'))
            ->assertRedirect(route('student.recommendations.index'))
            ->assertSessionHas('just_generated', true);
    });

    test('the matches announce themselves once, in front of the student', function () {
        $student = finishedStudent();

        $this->actingAs($student)
            ->followingRedirects()
            ->post(route('student.recommendations.generate'))
            ->assertOk()
            ->assertShows('Here are your recommendations');

        /* Opened again, it is yesterday's news and says nothing. */
        $this->actingAs($student)
            ->get(route('student.recommendations.index'))
            ->assertOk()
            ->assertHides('Here are your recommendations');
    });
});
