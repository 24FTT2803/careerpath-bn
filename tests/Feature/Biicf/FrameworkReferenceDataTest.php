<?php

use App\Models\BiicfBdqfLevel;
use App\Models\BiicfCompetency;
use App\Models\BiicfCompetencyLevelDescriptor;
use App\Models\BiicfEdition;
use App\Models\BiicfJobFamily;
use App\Models\BiicfJobLevel;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use Database\Seeders\BiicfExplorerSeeder;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

/*
 * The framework's reference scales, and the structure the role
 * pages actually use.
 *
 * Most of what is asserted here is shape rather than content:
 * that technical and soft proficiency are two scales and not
 * one, that a role can be tiered either by numbered step or by
 * named job level, and that framework data retired by a new
 * edition stays readable instead of disappearing. Those are the
 * assumptions the rest of the BIICF work is built on, and each
 * of them was wrong before.
 */

function biicfSubSector(string $name = 'IT Services'): BiicfSubSector
{
    return BiicfSubSector::create([
        'name' => $name,
        'slug' => Str::slug($name),
    ]);
}

function biicfJobRole(
    ?BiicfSubSector $subSector = null,
    string $title = 'IT Technician'
): BiicfJobRole {
    return BiicfJobRole::create([
        'sub_sector_id' => ($subSector ?? biicfSubSector())->id,
        'title' => $title,
        'slug' => Str::slug($title),
    ]);
}

describe('proficiency scales', function () {
    test('technical and soft are separate scales', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(BiicfProficiencyLevel::technical()->count())->toBe(5)
            ->and(BiicfProficiencyLevel::soft()->count())->toBe(4);
    });

    test('a level number means something different on each scale', function () {
        /*
         * The whole point of the split. Technical level 2 is
         * Assist; soft level 2 is Intermediate, which is the
         * middle of a shorter ladder, not the same rung.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $technical = BiicfProficiencyLevel::technical()
            ->where('level_number', 2)
            ->sole();

        $soft = BiicfProficiencyLevel::soft()
            ->where('level_number', 2)
            ->sole();

        expect($technical->name)->toBe('Assist')
            ->and($soft->name)->toBe('Intermediate')
            ->and($technical->id)->not->toBe($soft->id);
    });

    test('each scale knows its own length', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(BiicfProficiencyLevel::technical()->first()->scaleLength())->toBe(5)
            ->and(BiicfProficiencyLevel::soft()->first()->scaleLength())->toBe(4);
    });

    test('the book wording is kept verbatim', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $follow = BiicfProficiencyLevel::technical()
            ->where('level_number', 1)
            ->sole();

        expect($follow->description)
            ->toContain('introductory understanding')
            ->and($follow->description)
            ->not->toContain('Soft skill equivalent');
    });

    test('a competency is rated on the scale matching its type', function () {
        $technical = BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        $soft = BiicfCompetency::create([
            'name' => 'Decision-Making',
            'slug' => 'decision-making',
            'type' => 'soft_skill',
        ]);

        expect($technical->scale())
            ->toBe(BiicfProficiencyLevel::SCALE_TECHNICAL)
            ->and($soft->scale())
            ->toBe(BiicfProficiencyLevel::SCALE_SOFT);
    });
});

describe('the BDQF ladder', function () {
    test('all eight levels are recorded', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(BiicfBdqfLevel::count())->toBe(8);
    });

    test('a level lists its qualifications by sector', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $four = BiicfBdqfLevel::where('level', 4)->sole();

        expect($four->tvet_sector)->toContain('Diploma')
            ->and($four->schools_sector)->toContain('GCE "A" Level')
            ->and($four->higher_education_sector)->toBeNull();
    });

    test('an award in two sectors is only named once', function () {
        /*
         * Annex 1 lists Advanced Diploma under both the
         * vocational and higher education columns at level 5. A
         * student asking what level 5 is should see it once.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $five = BiicfBdqfLevel::where('level', 5)->sole();

        expect($five->qualifications())
            ->toContain('Advanced Diploma')
            ->and(array_count_values($five->qualifications())['Advanced Diploma'])
            ->toBe(1);
    });
});

describe('job levels', function () {
    test('they are seeded in order of seniority', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(
            BiicfJobLevel::orderBy('sort_order')->pluck('name')->all()
        )->toBe([
            'Entrant',
            'Specialist',
            'Expert/Management',
            'Senior Management',
            'C-Suite',
        ]);
    });

    test('a name containing a slash still slugs readably', function () {
        /*
         * Str::slug removes a slash rather than treating it as a
         * word break, which would turn Expert/Management into
         * "expertmanagement".
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(
            BiicfJobLevel::where('name', 'Expert/Management')->sole()->slug
        )->toBe('expert-management');
    });

    test('re-seeding does not duplicate a level', function () {
        $this->seed(BiicfReferenceDataSeeder::class);
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(BiicfJobLevel::where('name', 'Expert/Management')->count())
            ->toBe(1);
    });

    test('a role can sit at more than one job level', function () {
        /*
         * IT Administrator/Manager reads "Entrant or
         * Expert/Management" in the book, which a single column
         * could not have held.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = biicfJobRole(title: 'IT Administrator/Manager');

        $role->jobLevels()->attach(
            BiicfJobLevel::whereIn('slug', ['entrant', 'expert-management'])
                ->pluck('id')
        );

        expect($role->jobLevels()->pluck('name')->all())
            ->toBe(['Entrant', 'Expert/Management']);
    });
});

describe('role tiers', function () {
    test('a tier can be a numbered step within one job level', function () {
        /*
         * IT Technician's table has columns Level 1, 2 and 3 —
         * a progression inside Entrant, with no job level of
         * their own.
         */
        $role = biicfJobRole();

        foreach (['Level 1', 'Level 2', 'Level 3'] as $index => $label) {
            BiicfJobRoleTier::create([
                'job_role_id' => $role->id,
                'label' => $label,
                'sort_order' => $index + 1,
            ]);
        }

        expect($role->tiers()->pluck('label')->all())
            ->toBe(['Level 1', 'Level 2', 'Level 3'])
            ->and($role->tiers()->first()->isNamedJobLevel())
            ->toBeFalse();
    });

    test('a tier can be one of the named job levels', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $entrant = BiicfJobLevel::where('slug', 'entrant')->sole();

        $tier = BiicfJobRoleTier::create([
            'job_role_id' => biicfJobRole()->id,
            'label' => 'Entrant',
            'job_level_id' => $entrant->id,
            'sort_order' => 1,
        ]);

        expect($tier->isNamedJobLevel())->toBeTrue()
            ->and($tier->jobLevel->name)->toBe('Entrant');
    });

    test('one role cannot have the same tier label twice', function () {
        $role = biicfJobRole();

        BiicfJobRoleTier::create([
            'job_role_id' => $role->id,
            'label' => 'Level 1',
        ]);

        expect(fn () => BiicfJobRoleTier::create([
            'job_role_id' => $role->id,
            'label' => 'Level 1',
        ]))->toThrow(QueryException::class);
    });
});

describe('job families', function () {
    test('a role can belong to several', function () {
        $role = biicfJobRole();

        foreach ([
            'Server and network support',
            'Desktop and peripherals installation and maintenance',
        ] as $index => $name) {
            $family = BiicfJobFamily::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);

            $role->jobFamilies()->attach(
                $family->id,
                ['sort_order' => $index + 1]
            );
        }

        expect($role->jobFamilies)->toHaveCount(2);
    });
});

describe('competency level descriptors', function () {
    test('a competency describes each level separately', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        $statementsByLevel = [
            1 => ['Know where to look for standards.'],
            2 => ['Test/debug programme modules.'],
        ];

        foreach ($statementsByLevel as $number => $statements) {
            BiicfCompetencyLevelDescriptor::create([
                'competency_id' => $competency->id,
                'proficiency_level_id' => BiicfProficiencyLevel::technical()
                    ->where('level_number', $number)
                    ->sole()
                    ->id,
                'statements' => $statements,
            ]);
        }

        $stored = $competency->levelDescriptors
            ->mapWithKeys(fn ($descriptor) => [
                $descriptor->proficiencyLevel->level_number => $descriptor->statements,
            ])
            ->all();

        expect($stored)->toBe($statementsByLevel);
    });

    test('a level the book marks N/A simply has no descriptor', function () {
        /*
         * Application Support and Enhancement has no Follow and
         * no Strategise in the book. An absent row is how that is
         * recorded, so it must stay distinguishable from a row
         * holding an empty list.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Application Support and Enhancement',
            'slug' => 'application-support-and-enhancement',
            'type' => 'technical',
        ]);

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => BiicfProficiencyLevel::technical()
                ->where('level_number', 2)
                ->sole()
                ->id,
            'statements' => ['Perform routine installation and maintenance of applications.'],
        ]);

        $byLevel = $competency->levelDescriptors
            ->keyBy(fn ($descriptor) => $descriptor->proficiencyLevel->level_number);

        expect($byLevel->has(1))->toBeFalse()
            ->and($byLevel->has(2))->toBeTrue();
    });

    test('a competency cannot describe the same level twice', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Cloud Computing',
            'slug' => 'cloud-computing',
            'type' => 'technical',
        ]);

        $level = BiicfProficiencyLevel::technical()
            ->where('level_number', 3)
            ->sole();

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => $level->id,
            'statements' => ['First.'],
        ]);

        expect(fn () => BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => $level->id,
            'statements' => ['Second.'],
        ]))->toThrow(QueryException::class);
    });
});

describe('edition provenance', function () {
    test('data added by hand is marked as such', function () {
        /*
         * Administrators keep adding framework data directly, and
         * an import must be able to tell those entries apart so it
         * never offers to retire one merely because the new book
         * does not mention it.
         */
        expect(biicfJobRole()->fresh()->isManual())->toBeTrue();
    });

    test('a role a new edition drops is retired, not deleted', function () {
        $role = biicfJobRole();

        $edition = BiicfEdition::create([
            'name' => 'Third Edition',
            'slug' => 'third-edition',
            'edition_number' => 3,
        ]);

        $role->update(['retired_in_edition_id' => $edition->id]);

        expect(BiicfJobRole::find($role->id))->not->toBeNull()
            ->and($role->fresh()->isRetired())->toBeTrue()
            ->and(BiicfJobRole::inCurrentFramework()->count())->toBe(0)
            ->and(BiicfJobRole::retired()->count())->toBe(1);
    });

    test('an edited import is flagged for the next edition to resolve', function () {
        $role = biicfJobRole();

        $role->update([
            'origin' => 'imported',
            'hand_edited_at' => now(),
        ]);

        expect($role->fresh()->wasHandEdited())->toBeTrue();
    });

    test('only one edition is current at a time', function () {
        BiicfEdition::create([
            'name' => 'Second Edition',
            'slug' => 'second-edition',
            'edition_number' => 2,
            'is_current' => true,
        ]);

        expect(BiicfEdition::current()?->name)->toBe('Second Edition');
    });

    test('there is no current edition before one has been applied', function () {
        expect(BiicfEdition::current())->toBeNull();
    });
});

describe('existing seeding', function () {
    test('the reference data seeder can be run twice', function () {
        $this->seed(BiicfReferenceDataSeeder::class);
        $this->seed(BiicfReferenceDataSeeder::class);

        expect(BiicfProficiencyLevel::count())->toBe(9)
            ->and(BiicfBdqfLevel::count())->toBe(8)
            ->and(BiicfJobLevel::count())->toBe(5);
    });

    test('the explorer seeder still works alongside both scales', function () {
        /*
         * It used to key proficiency levels on the number alone,
         * which stopped being unique the moment the soft scale
         * joined the table.
         */
        $this->seed(BiicfExplorerSeeder::class);

        expect(BiicfProficiencyLevel::count())->toBe(9)
            ->and(BiicfSubSector::count())->toBe(6)
            ->and(BiicfJobRole::count())->toBeGreaterThan(30);
    });
});
