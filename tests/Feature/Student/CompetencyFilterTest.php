<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobLevel;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use Database\Seeders\BiicfReferenceDataSeeder;

/*
 * Choosing competencies, and understanding what a BDQF level is.
 *
 * The framework names a hundred and twenty-seven technical
 * competencies. Offered as one list they are something a student
 * reads rather than chooses from, so the list narrows to the
 * sub-sector they are heading for — and because a competency is
 * wanted by roles across several sub-sectors, it appears under
 * each of them.
 *
 * The qualifications picker asks for a BDQF level, which the
 * form had never explained anywhere. Entry requirements are
 * written in those levels, so a student who has not met the
 * framework was being asked to take the whole thing on trust.
 */

/**
 * A competency wanted by a role in each of the named
 * sub-sectors, as the book's tables want it.
 *
 * @param  array<int, string>  $sectorNames
 */
function competencyWantedIn(string $name, array $sectorNames): BiicfCompetency
{
    $competency = BiicfCompetency::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'type' => 'technical',
    ]);

    foreach ($sectorNames as $index => $sectorName) {
        $sector = BiicfSubSector::firstOrCreate(
            ['slug' => str($sectorName)->slug()],
            ['name' => $sectorName, 'sort_order' => $index + 1]
        );

        $role = BiicfJobRole::create([
            'sub_sector_id' => $sector->id,
            'title' => "{$name} Specialist in {$sectorName}",
            'slug' => str("{$name}-{$sectorName}")->slug(),
        ]);

        BiicfJobRoleTier::create([
            'job_role_id' => $role->id,
            'label' => 'Entrant',
            'sort_order' => 1,
        ])->competencies()->attach($competency->id, [
            'proficiency_level_id' => BiicfProficiencyLevel::technical()
                ->where('level_number', 2)
                ->sole()
                ->id,
            'is_core' => true,
        ]);
    }

    return $competency;
}

function aStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

beforeEach(function () {
    $this->seed(BiicfReferenceDataSeeder::class);
});

describe('narrowing the competencies', function () {
    test('the form offers a chip for every sub-sector', function () {
        competencyWantedIn('Cloud Computing', ['IT Services', 'ICT Security']);

        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        $response->assertShows(
            'cpbn-sector-chip',
            'data-sector="IT Services"',
            'data-sector="ICT Security"',
            /* All, and selected to begin with. */
            'data-sector=""'
        );
    });

    test('a competency says every sub-sector that wants it', function () {
        /*
         * One name, not the first one found. Most of the
         * framework's competencies are wanted in several
         * sub-sectors, and a filter that knew about only one
         * would hide them from the rest.
         */
        competencyWantedIn('Cloud Computing', ['IT Services', 'ICT Security']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('data-sectors="ICT Security|IT Services"');
    });

    test('a competency no role asks for carries no sub-sector', function () {
        /*
         * The library holds competencies that no role currently
         * requires. They stay on offer — a student may well have
         * one — and the filter simply has nothing to file them
         * under.
         */
        BiicfCompetency::create([
            'name' => 'Orphan Competency',
            'slug' => 'orphan-competency',
            'type' => 'technical',
        ]);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('Orphan Competency', 'data-sectors=""');
    });

    test('the soft skills are left alone', function () {
        /*
         * Eleven of them, and every role wants most of the
         * eleven. Filtering a list that short would be a control
         * that never changes anything.
         *
         * A technical competency is here too, because the chips
         * only exist once something has a sub-sector to be
         * filed under — without one there would be no filter to
         * find, and this test would pass by the technical one
         * being missing as well.
         */
        competencyWantedIn('Cloud Computing', ['IT Services']);

        BiicfCompetency::create([
            'name' => 'Communication',
            'slug' => 'communication',
            'type' => 'soft_skill',
        ]);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('id="technical-sector-filter"')
            ->assertHides('id="soft-sector-filter"');
    });
});

describe('explaining the ladder', function () {
    test('the qualifications section says what a BDQF level is', function () {
        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                'What are BDQF levels?',
                'Brunei Darussalam Qualifications Framework'
            );
    });

    test('it names the awards at each level, by sector', function () {
        /*
         * Annex 1's own three columns. A student finds their own
         * award in the list and reads its level off, which is
         * the only way the picker below is answerable by
         * somebody who has not met the framework.
         */
        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        $response->assertShows(
            'Level 8',
            'Level 1',
            'Doctoral Degree',
            'Higher National Diploma (HND)',
            'Technical and vocational'
        );
    });

    test('it is shut until asked for', function () {
        /*
         * Anybody who knows their level does not need it open,
         * and it is eight rows long.
         */
        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        expect($response->getContent())
            ->toContain('<details class="bdqf-help">')
            ->not->toContain('<details class="bdqf-help" open');
    });
});

/**
 * A competency wanted at named job levels.
 *
 * $tierLevels names the framework's level for each tier the
 * competency is required at. $roleLevels names the levels the
 * role itself is listed at, which is the coarser statement the
 * book's role pages make and the only one available where a tier
 * is a numbered step inside a level rather than a level of its
 * own.
 *
 * @param  array<int, string>  $tierLevels
 * @param  array<int, string>  $roleLevels
 */
function competencyAtLevels(
    string $name,
    string $sectorName,
    array $tierLevels,
    array $roleLevels = []
): BiicfCompetency {
    $competency = BiicfCompetency::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'type' => 'technical',
    ]);

    $sector = BiicfSubSector::firstOrCreate(
        ['slug' => str($sectorName)->slug()],
        ['name' => $sectorName, 'sort_order' => 1]
    );

    $role = BiicfJobRole::create([
        'sub_sector_id' => $sector->id,
        'title' => "{$name} Role",
        'slug' => str("{$name}-role")->slug(),
    ]);

    $proficiency = BiicfProficiencyLevel::technical()
        ->where('level_number', 2)
        ->sole()
        ->id;

    /*
     * A tier per named level, or one unnamed tier when none are
     * given — which is the shape the fallback exists for.
     */
    $labels = $tierLevels === [] ? [null] : $tierLevels;

    foreach (array_values($labels) as $index => $levelName) {
        BiicfJobRoleTier::create([
            'job_role_id' => $role->id,
            'label' => $levelName ?? 'Level '.($index + 1),
            'sort_order' => $index + 1,
            'job_level_id' => $levelName === null
                ? null
                : BiicfJobLevel::where('name', $levelName)->sole()->id,
        ])->competencies()->attach($competency->id, [
            'proficiency_level_id' => $proficiency,
            'is_core' => true,
        ]);
    }

    foreach ($roleLevels as $levelName) {
        $role->jobLevels()->attach(
            BiicfJobLevel::where('name', $levelName)->sole()->id
        );
    }

    return $competency;
}

describe('the job level filter', function () {
    test('is gone, and the other two rows are not', function () {
        /*
         * It sat between the sub-sector and the role and barely
         * narrowed anything: Specialist covers twenty-three of the
         * thirty-eight roles, so crossing a sub-sector with it
         * still left as many as thirty-nine competencies. The role
         * row does the work, and the level row was taking a line
         * of screen for nothing.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                'id="technical-sector-filter"',
                'id="technical-role-filter"'
            )
            ->assertHides(
                'id="technical-level-filter"',
                'data-levels=',
                'data-role-levels=',
                '<span class="cpbn-filter-label">Job level</span>'
            );
    });
});

describe('narrowing by the job itself', function () {
    test('the form offers a chip per role, carrying what filters it', function () {
        /*
         * The sub-sector on the chip is what the row above it
         * narrows the role row by.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $role = BiicfJobRole::where('title', 'Cloud Computing Role')->sole();

        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        /*
         * One attribute at a time: the chip is written an
         * attribute per line, so the two never appear as one run
         * of text.
         */
        $response->assertShows(
            'id="technical-role-filter"',
            'data-role="'.$role->id.'"',
            'data-role-sector="IT Services"',
            'Cloud Computing Role',
            /* All, and on to begin with. */
            'data-role=""'
        );
    });

    test('a competency names the roles that ask for it', function () {
        /*
         * By id, not title. The book prints Chief Technology
         * Officer under two sub-sectors, and a title is long
         * enough to bloat every row of a hundred and
         * twenty-seven.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $role = BiicfJobRole::where('title', 'Cloud Computing Role')->sole();

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('data-roles="'.$role->id.'"');
    });

    test('a role nothing is filed against is not offered', function () {
        /*
         * A chip that can only ever produce an empty list is not
         * a filter. The framework carries roles whose competency
         * tables have not been entered yet.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $bare = BiicfJobRole::create([
            'sub_sector_id' => BiicfSubSector::where('name', 'IT Services')->sole()->id,
            'title' => 'Role With No Competencies',
            'slug' => 'role-with-no-competencies',
        ]);

        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        $response->assertHides('data-role="'.$bare->id.'"');
        $response->assertHides('Role With No Competencies');
    });

    test('a competency no role asks for names no role', function () {
        BiicfCompetency::create([
            'name' => 'Orphan Competency',
            'slug' => 'orphan-competency',
            'type' => 'technical',
        ]);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('Orphan Competency', 'data-roles=""');
    });

    test('both rows are labelled', function () {
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                '<span class="cpbn-filter-label">Sub-sector</span>',
                '<span class="cpbn-filter-label">Job role</span>'
            );
    });

    test('the role row stays away when no role has competencies', function () {
        BiicfCompetency::create([
            'name' => 'Orphan Competency',
            'slug' => 'orphan-competency',
            'type' => 'technical',
        ]);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertHides('id="technical-role-filter"');
    });
});

describe('putting the filters away', function () {
    test('the rows sit in a block that can be shut', function () {
        /*
         * Three rows of chips is a lot of furniture above a list
         * and plenty of people never want any of it.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                'id="technical-filter-toggle"',
                'id="technical-filters"',
                'aria-controls="technical-filters"'
            );
    });

    test('it starts open', function () {
        /*
         * A filter nobody finds is no better than one that is not
         * there. The point is being able to put it away, not to
         * hide it.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        $response->assertShows('aria-expanded="true"');
        $response->assertHides('<div class="cpbn-filters" id="technical-filters" hidden>');
    });

    test('there is somewhere to say what is filtered and why', function () {
        /*
         * A competency already ticked stays on screen whatever is
         * selected — which, with every page open at once, reads
         * as the filter having failed. Both lines are written by
         * the script, so what the page has to carry is the two
         * places they go.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                'id="technical-filter-summary"',
                'id="technical-filter-active"'
            );
    });

    test('the summary says nothing until something is filtered', function () {
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                '<p class="cpbn-filter-summary" id="technical-filter-summary" hidden></p>'
            );
    });
});
