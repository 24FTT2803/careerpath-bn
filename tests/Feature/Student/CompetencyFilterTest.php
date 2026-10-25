<?php

use App\Models\BiicfCompetency;
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
