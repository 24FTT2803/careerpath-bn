<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Support\Facades\DB;

/*
 * Everything that reads a role's required proficiencies.
 *
 * The pivot now carries a tier, so a role with three tiers holds
 * three rows per competency — one per column of the book's
 * table. Anything still reading the role's competencies directly
 * would list each competency three times, and the AI context
 * would send three contradictory requirement pairs for it.
 *
 * The lowest tier is the answer in every case: the least that
 * gets a student into the role.
 */

function multiTierRole(): BiicfJobRole
{
    $subSector = BiicfSubSector::firstOrCreate(
        ['slug' => 'it-services'],
        ['name' => 'IT Services']
    );

    $role = BiicfJobRole::create([
        'sub_sector_id' => $subSector->id,
        'title' => 'IT Technician',
        'slug' => 'it-technician',
    ]);

    $competency = BiicfCompetency::create([
        'name' => 'Application Development',
        'slug' => 'application-development',
        'type' => 'technical',
    ]);

    /*
     * The book's own figures for this role: level 1 at the first
     * tier, then 2, then 2.
     */
    foreach ([1 => 1, 2 => 2, 3 => 2] as $tierNumber => $levelNumber) {
        BiicfJobRoleTier::create([
            'job_role_id' => $role->id,
            'label' => "Level {$tierNumber}",
            'sort_order' => $tierNumber,
        ])->competencies()->attach($competency->id, [
            'proficiency_level_id' => BiicfProficiencyLevel::technical()
                ->where('level_number', $levelNumber)
                ->sole()
                ->id,
            'is_core' => true,
        ]);
    }

    return $role->fresh();
}

describe('eager loading', function () {
    test('tiers and their competencies load together', function () {
        /*
         * Eager loading builds the relation query from a blank
         * tier instance, so anything on that relation depending
         * on the instance knowing its own role breaks every
         * nested load — which is what happened.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        multiTierRole();

        $roles = BiicfJobRole::with('tiers.competencies')->get();

        expect($roles)->toHaveCount(1)
            ->and($roles->first()->tiers)->toHaveCount(3)
            ->and($roles->first()->tiers->first()->competencies)
            ->toHaveCount(1);
    });

    test('a pivot row attached through a tier records the role', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = multiTierRole();

        expect(
            DB::table('biicf_job_role_competency')
                ->whereNull('job_role_id')
                ->count()
        )->toBe(0)
            ->and($role->competencies)->toHaveCount(3);
    });
});

describe('the explorer', function () {
    test('it lists a competency once, at the entry tier', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = multiTierRole();

        $response = $this->actingAs(
            User::factory()->create(['role' => 'student'])
        )->getJson(route('student.biicf-explorer.job-role.show', $role));

        $response->assertSuccessful()
            ->assertJsonCount(1, 'competencies_by_type.technical');

        $required = $response->json(
            'competencies_by_type.technical.0.pivot.proficiency_level_id'
        );

        expect($required)->toBe(
            BiicfProficiencyLevel::technical()
                ->where('level_number', 1)
                ->sole()
                ->id
        );
    });

    test('the role itself carries no competency list', function () {
        /*
         * The page reads competencies_by_type. It used to read
         * the role's own competencies relation, which went empty
         * the moment that relation stopped being loaded — and
         * which would list every competency once per tier if it
         * were loaded. Asserting its absence keeps the page
         * honest about where the data comes from.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = multiTierRole();

        $response = $this->actingAs(
            User::factory()->create(['role' => 'student'])
        )->getJson(route('student.biicf-explorer.job-role.show', $role));

        $response->assertSuccessful()
            ->assertJsonMissingPath('job_role.competencies')
            ->assertJsonCount(1, 'competencies_by_type.technical');
    });

    test('comparing against a student uses the entry tier', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = multiTierRole();

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->getJson(route('student.biicf-explorer.job-role.compare', $role))
            ->assertSuccessful()
            ->assertJsonCount(1, 'comparison');
    });
});

describe('the admin show page', function () {
    test('it lists a competency once and says which tier', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = multiTierRole();

        /*
         * This asked for the sentence "This role has 3 tiers",
         * which the page stopped saying when it gained a column
         * per tier: one button per tier, each labelled as the
         * book labels it, and the competencies of whichever is
         * chosen. The sentence answered the question for one
         * column out of three, which is why it went.
         *
         * It kept passing on a stale compiled view and only
         * failed once the cache was cleared, so it had been
         * asserting nothing for a fortnight — and
         * AdminCompetencyTableTest asserts the same sentence is
         * absent, so the two have been contradicting each other
         * all along.
         */
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.biicf.job-roles.show', $role))
            ->assertSuccessful()
            ->assertShows('class="tier-buttons"', 'Level 1', 'Level 2', 'Level 3')
            ->assertSeeText('Application Development');
    });

    test('the proficiency badge shows the level, not a question mark', function () {
        /*
         * The pivot holds a level id and there is no pivot class
         * to resolve it, so this badge used to read "Level ?".
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = multiTierRole();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.biicf.job-roles.show', $role))
            ->assertSuccessful()
            ->assertSeeText('Level 1')
            ->assertDontSeeText('Level ?');
    });
});

describe('creating a role by hand', function () {
    test('it gets a tier', function () {
        /*
         * Entry requirements and proficiencies cannot attach to a
         * role without one, and the backfill only covered roles
         * that already existed.
         */
        $subSector = BiicfSubSector::create([
            'name' => 'IT Services',
            'slug' => 'it-services',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.biicf.job-roles.store'), [
                'sub_sector_id' => $subSector->id,
                'title' => 'Service Desk Analyst',
            ])
            ->assertSessionHasNoErrors();

        $role = BiicfJobRole::where('title', 'Service Desk Analyst')->sole();

        expect($role->tiers)->toHaveCount(1)
            ->and($role->entryTier->label)->toBe('All levels');
    });
});
