<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
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

        $this->role->competencies()->attach($competency->id, [
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
        ->assertSee('Compare to my profile');
});
