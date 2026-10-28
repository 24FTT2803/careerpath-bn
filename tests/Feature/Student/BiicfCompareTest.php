<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\StudentCompetency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['Follow', 'Assist', 'Apply', 'Ensure', 'Strategise'] as $index => $name) {
        BiicfProficiencyLevel::forceCreate(['level_number' => $index + 1, 'name' => $name, 'description' => "$name level"]);
    }

    $sector = BiicfSubSector::forceCreate(['name' => 'IT Services', 'slug' => 'it-services', 'sort_order' => 1]);

    $this->role = BiicfJobRole::forceCreate([
        'sub_sector_id' => $sector->id,
        'title' => 'IT Technician',
        'slug' => 'it-technician',
        'career_path_level' => 0,
        'box_colour' => 'primary',
    ]);

    $level = fn (string $name) => BiicfProficiencyLevel::where('name', $name)->value('id');

    /*
     * A required proficiency belongs to a tier rather than to
     * the role: the book prints a column per level a role is
     * entered at, and wants Application Development at 1, 2 and
     * 2 across IT Technician's three. Attached to the role
     * alone, every competency comes back once per tier and is
     * counted again in the summary.
     */
    $this->tier = BiicfJobRoleTier::forceCreate([
        'job_role_id' => $this->role->id,
        'label' => 'Entrant',
        'sort_order' => 1,
    ]);

    foreach ([
        ['Application Development', 'technical', 'Assist', true],
        ['Infrastructure Management', 'technical', 'Assist', true],
        ['Information Security Management', 'technical', 'Apply', true],
        ['Communication', 'soft_skill', 'Assist', false],
    ] as [$name, $type, $required, $core]) {
        $competency = BiicfCompetency::forceCreate([
            'name' => $name,
            'slug' => str($name)->slug(),
            'type' => $type,
            'description' => "What $name means.",
        ]);

        $this->tier->competencies()->attach($competency->id, [
            'proficiency_level_id' => $level($required),
            'is_core' => $core,
        ]);
    }

    $this->student = User::factory()->create(['role' => 'student']);
});

function giveSkill(User $student, string $name, string $level): void
{
    StudentCompetency::forceCreate([
        'user_id' => $student->id,
        'skill_name' => $name,
        'category' => 'biicf',
        'proficiency_level' => $level,
    ]);
}

test('compare counts BIICF levels the student picked on their profile', function () {
    giveSkill($this->student, 'Application Development', 'Assist');
    giveSkill($this->student, 'Communication', 'Apply');
    giveSkill($this->student, 'Infrastructure Management', 'Follow');

    $response = $this->actingAs($this->student)
        ->getJson(route('student.biicf-explorer.job-role.compare', $this->role))
        ->assertOk()
        ->assertJsonPath('summary.total', 4)
        ->assertJsonPath('summary.met', 2)
        ->assertJsonPath('summary.below', 1)
        ->assertJsonPath('summary.missing', 1);

    $rows = collect($response->json('comparison'))->keyBy('competency.name');

    expect($rows['Application Development']['meets_requirement'])->toBeTrue()
        ->and($rows['Communication']['student_level_number'])->toBe(3)
        ->and($rows['Infrastructure Management']['meets_requirement'])->toBeFalse()
        ->and($rows['Infrastructure Management']['required_level']['name'])->toBe('Assist')
        ->and($rows['Information Security Management']['student_has_skill'])->toBeFalse();
});

test('older beginner to expert levels are still understood', function () {
    giveSkill($this->student, 'Application Development', 'intermediate');

    $this->actingAs($this->student)
        ->getJson(route('student.biicf-explorer.job-role.compare', $this->role))
        ->assertOk()
        ->assertJsonPath('summary.met', 1);
});

test('role details include competency descriptions for students', function () {
    $this->actingAs($this->student)
        ->getJson(route('student.biicf-explorer.job-role.show', $this->role))
        ->assertOk()
        ->assertJsonFragment(['description' => 'What Communication means.']);
});

test('the explorer page renders', function () {
    $this->actingAs($this->student)
        ->get(route('student.biicf-explorer.index'))
        ->assertOk()
        ->assertShows('Compare to my profile');
});

test('a role entered at several levels is compared one level at a time', function () {
    /*
     * The failure this guards against counted every competency
     * once per tier: a three-tier role read as twelve
     * requirements rather than four, and a student who met all
     * of them was told they met a third.
     */
    $specialist = BiicfJobRoleTier::forceCreate([
        'job_role_id' => $this->role->id,
        'label' => 'Specialist',
        'sort_order' => 2,
    ]);

    $ensure = BiicfProficiencyLevel::where('name', 'Ensure')->value('id');

    foreach (BiicfCompetency::all() as $competency) {
        $specialist->competencies()->attach($competency->id, [
            'proficiency_level_id' => $ensure,
            'is_core' => false,
        ]);
    }

    giveSkill($this->student, 'Application Development', 'Assist');

    /* No tier named: the one a student enters at. */
    $this->actingAs($this->student)
        ->getJson(route('student.biicf-explorer.job-role.compare', $this->role))
        ->assertOk()
        ->assertJsonPath('summary.total', 4)
        ->assertJsonPath('tier.label', 'Entrant')
        ->assertJsonPath('summary.met', 1);

    /* The tier asked for, and what it asks of them there. */
    $this->actingAs($this->student)
        ->getJson(route('student.biicf-explorer.job-role.compare', $this->role).'?tier='.$specialist->id)
        ->assertOk()
        ->assertJsonPath('summary.total', 4)
        ->assertJsonPath('tier.label', 'Specialist')
        ->assertJsonPath('summary.met', 0)
        ->assertJsonPath('summary.below', 1);
});

test('a soft skill is judged on its own four rungs', function () {
    /*
     * The two scales name their rungs differently and the soft
     * one borrowed the words the profile used before BIICF
     * levels existed — Basic, Intermediate, Advanced, Expert. A
     * flat map of names across both scales reads a soft skill's
     * Advanced, three of four, as a technical three of five.
     */
    $soft = BiicfProficiencyLevel::forceCreate([
        'level_number' => 3,
        'name' => 'Advanced',
        'description' => 'Advanced on the soft scale.',
        'scale' => BiicfProficiencyLevel::SCALE_SOFT,
    ]);

    $communication = BiicfCompetency::where('name', 'Communication')->sole();

    $this->tier->competencies()->updateExistingPivot($communication->id, [
        'proficiency_level_id' => $soft->id,
    ]);

    giveSkill($this->student, 'Communication', 'Advanced');

    $response = $this->actingAs($this->student)
        ->getJson(route('student.biicf-explorer.job-role.compare', $this->role))
        ->assertOk();

    $rows = collect($response->json('comparison'))->keyBy('competency.name');

    expect($rows['Communication']['required_level']['scale'])
        ->toBe(BiicfProficiencyLevel::SCALE_SOFT)
        ->and($rows['Communication']['scale_of'])->toBe(4)
        ->and($rows['Communication']['student_level_number'])->toBe(3)
        ->and($rows['Communication']['meets_requirement'])->toBeTrue();
});
