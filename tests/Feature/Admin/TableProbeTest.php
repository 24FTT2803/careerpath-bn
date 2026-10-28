<?php

use App\Models\CareerRecommendation;
use App\Models\RecommendationGeneration;
use App\Models\User;

/*
 * The admin dashboard's and the student page's own counts.
 *
 * Both guarded their recommendation sections with a probe that
 * queried information_schema for the table, using a database
 * name read from env('DB_DATABASE'). That is null once
 * config:cache has run, which is the first thing a live
 * deployment does — so the comparison matched nothing, the probe
 * reported the table missing, and the whole section was replaced
 * by zeros on a server where the data was there all along. Under
 * SQLite there is no information_schema at all, so the query
 * threw and the catch arrived at the same zeros, which is why no
 * test ever saw a number here.
 *
 * Schema::hasTable asks the connection in use. These assert the
 * figures reach the page, which is what the probe was deciding.
 */

function adminViewer(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function studentWithRecommendation(float $score = 82.0): User
{
    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Nurul Hakim',
        'programme' => 'Higher National Diploma in Computing',
    ]);

    $generation = RecommendationGeneration::create([
        'user_id' => $student->id,
        'generation_number' => 1,
        'status' => RecommendationGeneration::STATUS_CURRENT,
        'driver' => 'mock',
        'schema_version' => '2.0',
        'recommendation_count' => 1,
        'generated_at' => now(),
    ]);

    CareerRecommendation::create([
        'user_id' => $student->id,
        'recommendation_generation_id' => $generation->id,
        'rank' => 1,
        'match_score' => 91.5,
        'career_readiness_score' => $score,
        'matched_skills' => ['Programming'],
        'skill_gaps' => [],
        'development_plan' => ['Keep going.'],
        'explanation' => 'A recommendation.',
    ]);

    return $student;
}

test('the dashboard counts recommendations that exist', function () {
    studentWithRecommendation();

    /*
     * Two more students, so the student count cannot be mistaken
     * for the recommendation count: with one of each, the tile
     * this is about and the tile beside it both read 1 and the
     * assertion would pass on either.
     */
    User::factory()->count(2)->create(['role' => 'student']);

    $this->actingAs(adminViewer())
        ->get(route('admin.dashboard'))
        ->assertOk()
        /*
         * The figure itself, in the tile that reported zero
         * whenever the probe failed.
         */
        ->assertShows('<span class="stat-value">1</span>')
        ->assertShows('<span class="stat-value">3</span>');
});

test('the dashboard reports average readiness rather than zero', function () {
    studentWithRecommendation(82.0);

    $this->actingAs(adminViewer())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertShows('<span class="stat-value">82%</span>');
});

test('a student page shows the recommendations held against them', function () {
    $student = studentWithRecommendation();

    $this->actingAs(adminViewer())
        ->get(route('admin.students.show', $student))
        ->assertOk()
        ->assertShows('91.5% Match')
        ->assertShows('Programming')
        /*
         * The message the page fell back to, which said the data
         * was missing on a server that had it.
         */
        ->assertHides('No career recommendations yet.');
});

test('an empty database still renders both pages', function () {
    /*
     * The zeros were the right answer once, when the tables had
     * not been migrated yet. Reading nothing must still not throw.
     */
    $student = User::factory()->create(['role' => 'student']);

    $admin = adminViewer();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.students.show', $student))
        ->assertOk();
});
