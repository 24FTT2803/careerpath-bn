<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * What the explorer now reads.
 *
 * The book gives a role one column of required proficiencies per
 * tier and describes every competency rung by rung. The page
 * showed neither: one tier's worth of names, and a proficiency
 * as a bare number. These cover the two endpoints behind the
 * tier switcher and the competency a student can open.
 */

function explorerStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

describe('a role carries every tier', function () {
    beforeEach(function () {
        $this->seed(BiicfJobRoleSeeder::class);
    });

    test('each tier brings its own column of the table', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $response = $this->actingAs(explorerStudent())
            ->getJson(route('student.biicf-explorer.job-role.show', $role));

        $response->assertSuccessful()
            ->assertJsonCount(3, 'tiers')
            ->assertJsonPath('tiers.0.label', 'Level 1')
            ->assertJsonPath('tiers.2.label', 'Level 3');

        $levels = collect($response->json('tiers'))
            ->map(fn (array $tier) => collect($tier['competencies_by_type']['technical'])
                ->firstWhere('name', 'Application Development')['level']['number']);

        expect($levels->all())->toBe([1, 2, 2]);
    });

    test('a proficiency arrives named, not as a bare number', function () {
        /*
         * The two scales number their rungs separately, so "3"
         * alone means Apply on a technical competency and
         * Advanced on a soft skill. The page cannot tell them
         * apart from the number.
         */
        $role = BiicfJobRole::where('title', 'Data Scientist')->sole();

        $response = $this->actingAs(explorerStudent())
            ->getJson(route('student.biicf-explorer.job-role.show', $role));

        $soft = collect($response->json('tiers.0.competencies_by_type.soft_skill'));

        expect($soft->pluck('level.name')->unique()->all())
            ->not->toContain(null)
            ->and($soft->pluck('level.name')->intersect(['Basic', 'Intermediate', 'Advanced', 'Expert']))
            ->not->toBeEmpty();
    });

    test('the entry requirement comes through as the book words it', function () {
        $role = BiicfJobRole::where('title', 'Chief Technology Officer')->sole();

        $response = $this->actingAs(explorerStudent())
            ->getJson(route('student.biicf-explorer.job-role.show', $role));

        expect($response->json('tiers.0.entry_requirements.0.text'))
            ->toContain('BDQF Level 6')
            ->and($response->json('tiers.0.entry_requirements.0.measurable'))
            ->toBeFalse();
    });

    test('the entry tier is still at the top level', function () {
        /*
         * What the page shows before anyone picks a tier, and
         * what the comparison reads.
         */
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $this->actingAs(explorerStudent())
            ->getJson(route('student.biicf-explorer.job-role.show', $role))
            ->assertSuccessful()
            ->assertJsonStructure(['competencies_by_type' => ['technical', 'soft_skill']]);
    });
});

describe('opening a competency', function () {
    beforeEach(function () {
        $this->seed(BiicfJobRoleSeeder::class);
    });

    test('it brings the definition and every level of its scale', function () {
        /*
         * A technical competency is rated over five rungs, so
         * five come back whether or not the book describes the
         * competency at each.
         */
        $competency = BiicfCompetency::where('name', 'Application Development')
            ->where('type', 'technical')
            ->sole();

        $response = $this->actingAs(explorerStudent())
            ->getJson(route('student.biicf-explorer.competency.show', $competency));

        $response->assertSuccessful()
            ->assertJsonPath('competency.name', 'Application Development')
            ->assertJsonPath('competency.scale', 'technical')
            ->assertJsonCount(5, 'sets.0.levels');

        expect($response->json('competency.description'))->not->toBeEmpty();
    });

    test('a soft skill is rated over four rungs, not five', function () {
        $competency = BiicfCompetency::where('name', 'Communication')
            ->where('type', 'soft_skill')
            ->sole();

        /*
         * The scale is "soft" where the competency's type is
         * "soft_skill": the four rungs are the scale's, not the
         * competency's.
         */
        $this->actingAs(explorerStudent())
            ->getJson(route('student.biicf-explorer.competency.show', $competency))
            ->assertSuccessful()
            ->assertJsonPath('competency.scale', 'soft')
            ->assertJsonCount(4, 'sets.0.levels');
    });

    test('a level carries the book\'s own statements', function () {
        $competency = BiicfCompetency::where('name', 'Application Development')
            ->where('type', 'technical')
            ->sole();

        $levels = collect(
            $this->actingAs(explorerStudent())
                ->getJson(route('student.biicf-explorer.competency.show', $competency))
                ->json('sets.0.levels')
        );

        expect($levels->pluck('statements')->flatten())->not->toBeEmpty();
    });

    test('a level the book leaves out says so', function () {
        /*
         * A missing descriptor is the book's N/A — the
         * competency does not reach that rung — which is not the
         * same as a rung described with nothing in it.
         */
        $competency = BiicfCompetency::where('name', 'Cloud Computing')
            ->where('type', 'technical')
            ->sole();

        $levels = collect(
            $this->actingAs(explorerStudent())
                ->getJson(route('student.biicf-explorer.competency.show', $competency))
                ->json('sets.0.levels')
        );

        expect($levels->where('described', false))->not->toBeEmpty()
            ->and($levels->firstWhere('described', false)['statements'])->toBe([]);
    });

    test('a competency described per role comes back as separate sets', function () {
        /*
         * The book describes four competencies differently
         * depending on the role, and flattening those into one
         * list would read as contradictions.
         */
        $competency = BiicfCompetency::where('name', 'Infrastructure Management')
            ->where('type', 'technical')
            ->sole();

        $sets = collect(
            $this->actingAs(explorerStudent())
                ->getJson(route('student.biicf-explorer.competency.show', $competency))
                ->json('sets')
        );

        expect($sets->count())->toBe(3)
            ->and($sets->pluck('scope')->filter()->values()->all())
            ->toContain('IT Technician');
    });
});
