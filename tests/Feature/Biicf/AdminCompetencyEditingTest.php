<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfCompetencyLevelDescriptor;
use App\Models\BiicfProficiencyLevel;
use App\Models\User;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Support\Str;

/*
 * Editing a competency in full.
 *
 * The form held a name, a type and a definition, so the part a
 * student actually reads — what the competency means at each
 * rung — could be imported and never corrected. And there was
 * no view action at all, which left the book's statements
 * visible nowhere in the admin.
 */

beforeEach(function () {
    $this->seed(BiicfReferenceDataSeeder::class);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function technicalCompetency(string $name = 'Cloud Computing'): BiicfCompetency
{
    return BiicfCompetency::create([
        'name' => $name,
        'slug' => Str::slug($name),
        'type' => 'technical',
        'description' => 'The ability to design and operate services on shared infrastructure.',
    ]);
}

function rung(int $number, string $scale = BiicfProficiencyLevel::SCALE_TECHNICAL): BiicfProficiencyLevel
{
    return BiicfProficiencyLevel::where('scale', $scale)
        ->where('level_number', $number)
        ->sole();
}

describe('the view action', function () {
    test('a competency has a page of its own', function () {
        $competency = technicalCompetency();

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => rung(2)->id,
            'statements' => ['Assist with provisioning a virtual machine.'],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.competencies.show', $competency))
            ->assertOk()
            ->assertSee('Cloud Computing')
            ->assertSee('Assist with provisioning a virtual machine.')
            ->assertSee('shared infrastructure', false);
    });

    test('a level the framework leaves out is shown as left out', function () {
        /*
         * N/A is an absence in the book, and the page has to say
         * so rather than leaving a silent gap in the ladder.
         */
        $competency = technicalCompetency();

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => rung(1)->id,
            'statements' => ['Follow instructions.'],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.competencies.show', $competency))
            ->assertOk()
            ->assertSee('N/A')
            ->assertSee('does not describe this competency at this level');
    });

    test('a soft skill is shown on its own four rungs', function () {
        $competency = BiicfCompetency::create([
            'name' => 'Resilience',
            'slug' => 'resilience',
            'type' => 'soft_skill',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.competencies.show', $competency))
            ->assertOk()
            ->assertSee('rated over 4 levels')
            ->assertDontSee('5. Strategise');
    });
});

describe('writing what a level means', function () {
    test('statements are saved one per line', function () {
        $competency = technicalCompetency();

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.competencies.update', $competency), [
                'name' => $competency->name,
                'type' => 'technical',
                'descriptors' => [
                    ['scope' => '', 'levels' => [
                        rung(3)->id => "Apply cloud patterns to a service.\n\nSize an instance for a workload.",
                    ]],
                ],
            ])
            ->assertRedirect(route('admin.biicf.competencies.show', $competency));

        $descriptor = BiicfCompetencyLevelDescriptor::where('competency_id', $competency->id)->sole();

        expect($descriptor->proficiency_level_id)->toBe(rung(3)->id)
            ->and($descriptor->statements)->toBe([
                'Apply cloud patterns to a service.',
                'Size an instance for a workload.',
            ]);
    });

    test('clearing a level takes it back to N/A rather than storing nothing', function () {
        /*
         * An empty list is not what the book means by N/A, and a
         * row with no statements would show as a described level
         * with nothing under it.
         */
        $competency = technicalCompetency();

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => rung(2)->id,
            'statements' => ['Assist with provisioning.'],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.competencies.update', $competency), [
                'name' => $competency->name,
                'type' => 'technical',
                'descriptors' => [
                    ['scope' => '', 'levels' => [rung(2)->id => '   ']],
                ],
            ])
            ->assertRedirect();

        expect(BiicfCompetencyLevelDescriptor::where('competency_id', $competency->id)->count())
            ->toBe(0);
    });

    test('a scoped set is written without touching the general one', function () {
        /*
         * Four competencies are described differently depending
         * on the role. Writing one set must not overwrite its
         * neighbour at the same level.
         */
        $competency = technicalCompetency('Infrastructure Management');

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => rung(2)->id,
            'scope_label' => '',
            'statements' => ['The general reading.'],
        ]);

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => rung(2)->id,
            'scope_label' => 'For IT Technician',
            'statements' => ['The technician reading.'],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.competencies.update', $competency), [
                'name' => $competency->name,
                'type' => 'technical',
                'descriptors' => [
                    ['scope' => '', 'levels' => [rung(2)->id => 'The general reading.']],
                    ['scope' => 'For IT Technician', 'levels' => [rung(2)->id => 'Rewritten for technicians.']],
                ],
            ])
            ->assertRedirect();

        $scoped = BiicfCompetencyLevelDescriptor::where('competency_id', $competency->id)
            ->where('scope_label', 'For IT Technician')
            ->sole();

        $general = BiicfCompetencyLevelDescriptor::where('competency_id', $competency->id)
            ->where('scope_label', '')
            ->sole();

        expect($scoped->statements)->toBe(['Rewritten for technicians.'])
            ->and($general->statements)->toBe(['The general reading.']);
    });

    test('a save that carries no descriptors leaves them alone', function () {
        /*
         * The same trap as the role form: a request without the
         * field read as an empty one would delete every statement
         * the book supplied.
         */
        $competency = technicalCompetency();

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => rung(2)->id,
            'statements' => ['Assist with provisioning.'],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.competencies.update', $competency), [
                'name' => 'Cloud Services',
                'type' => 'technical',
            ])
            ->assertRedirect();

        expect(BiicfCompetencyLevelDescriptor::where('competency_id', $competency->id)->count())
            ->toBe(1)
            ->and($competency->refresh()->name)->toBe('Cloud Services');
    });
});

describe('the competency in one sentence', function () {
    test('it is saved and shown in the listing', function () {
        $competency = technicalCompetency();

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.competencies.update', $competency), [
                'name' => $competency->name,
                'type' => 'technical',
                'short_definition' => 'Running services on infrastructure someone else owns.',
            ])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.competencies'))
            ->assertOk()
            ->assertSee('Running services on infrastructure someone else owns.');
    });

    test('a competency without one says so instead of showing half a sentence', function () {
        technicalCompetency();

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.competencies'))
            ->assertOk()
            ->assertSee('not written yet')
            ->assertDontSee('The ability to design and operate');
    });
});
