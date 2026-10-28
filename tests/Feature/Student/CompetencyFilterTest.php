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

describe('narrowing by job level as well', function () {
    test('the form offers a chip for every level in use', function () {
        /*
         * In use, not every level the framework names. It names
         * seven and its role pages use six, so a chip for the
         * seventh could only ever produce an empty list.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);
        competencyAtLevels('Enterprise Architecture', 'IT Services', ['C-Suite']);

        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        $response->assertShows(
            'id="technical-level-filter"',
            'data-level="Entrant"',
            'data-level="C-Suite"',
            /* All, and on to begin with. */
            'data-level=""'
        );

        $response->assertHides('data-level="Senior Management"');
    });

    test('a competency says every level it is asked for at', function () {
        /*
         * In seniority order rather than the order the rows came
         * back in, so the chips and the attribute agree.
         */
        competencyAtLevels(
            'Cloud Computing',
            'IT Services',
            ['Specialist', 'Entrant']
        );

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('data-levels="Entrant|Specialist"');
    });

    test('a tier with no level of its own falls back to the role', function () {
        /*
         * The case the merge exists for. A tier that is a
         * numbered step inside a level carries no job level, and
         * reading tiers alone would leave the competency filed
         * under nothing — hidden behind every level chip, which
         * is to say unpickable.
         */
        competencyAtLevels(
            'Systems Monitoring',
            'IT Services',
            [],
            ['Specialist']
        );

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('data-levels="Specialist"');
    });

    test('a competency no role asks for carries no level', function () {
        /*
         * It stays on offer. The filter has nothing to file it
         * under, and the page keeps it on screen whichever level
         * is picked rather than putting a competency out of reach
         * for want of a row in the book.
         */
        BiicfCompetency::create([
            'name' => 'Orphan Competency',
            'slug' => 'orphan-competency',
            'type' => 'technical',
        ]);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('Orphan Competency', 'data-levels=""');
    });

    test('both rows of chips say which they are', function () {
        /*
         * Two unlabelled rows of chips read as one row that
         * wrapped, and the second is a different question
         * entirely.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                '<span class="cpbn-filter-label">Sub-sector</span>',
                '<span class="cpbn-filter-label">Job level</span>'
            );
    });

    test('the level row stays away when nothing records a level', function () {
        /*
         * A deployment that has not imported the framework's job
         * levels gets the sub-sector filter on its own rather
         * than a row holding nothing but "All".
         */
        competencyWantedIn('Cloud Computing', ['IT Services']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('id="technical-sector-filter"')
            ->assertHides('id="technical-level-filter"');
    });
});

describe('narrowing by the job itself', function () {
    test('the form offers a chip per role, carrying what filters it', function () {
        /*
         * The sub-sector and level on the chip are what the two
         * rows above it narrow the role row by. Read any other
         * way the three could disagree, and a role offered under
         * a level whose competencies are filed under another
         * would select to an empty grid.
         */
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $role = BiicfJobRole::where('title', 'Cloud Computing Role')->sole();

        $response = $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk();

        /*
         * One attribute at a time: the chip is written an
         * attribute per line, so the three never appear as one
         * run of text.
         */
        $response->assertShows(
            'id="technical-role-filter"',
            'data-role="'.$role->id.'"',
            'data-role-sector="IT Services"',
            'data-role-levels="Entrant"',
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

    test('a role whose tier names no level still carries the role levels', function () {
        /*
         * So the role row and the level row agree. The chip has
         * to be offered under the same level its competencies are
         * filed under, and for a tier that is a numbered step
         * that level only exists on the role.
         */
        competencyAtLevels(
            'Systems Monitoring',
            'IT Services',
            [],
            ['Specialist']
        );

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('data-role-levels="Specialist"');
    });

    test('all three rows are labelled', function () {
        competencyAtLevels('Cloud Computing', 'IT Services', ['Entrant']);

        $this->actingAs(aStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                '<span class="cpbn-filter-label">Sub-sector</span>',
                '<span class="cpbn-filter-label">Job level</span>',
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
