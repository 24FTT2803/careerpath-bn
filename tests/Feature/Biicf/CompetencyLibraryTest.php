<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfCompetencyLevelDescriptor;
use App\Models\BiicfProficiencyLevel;
use Database\Seeders\BiicfCompetencySeeder;
use Database\Seeders\BiicfExplorerSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/*
 * The competency library as the reference book states it.
 *
 * What the app held before was an approximation: 62 names, no
 * definitions, no level content. The book gives 139 with a
 * definition each and 704 rows of level detail, and three
 * shapes the schema could not previously hold — a name that is
 * both a technical competency and a soft skill, a competency
 * described differently depending on the role holding it, and
 * levels the book marks N/A.
 */

describe('loading the library', function () {
    test('every competency the book lists is loaded', function () {
        $this->seed(BiicfCompetencySeeder::class);

        expect(BiicfCompetency::count())->toBe(139)
            ->and(BiicfCompetency::where('type', 'technical')->count())->toBe(128)
            ->and(BiicfCompetency::where('type', 'soft_skill')->count())->toBe(11);
    });

    test('every competency has the book\'s definition', function () {
        /*
         * Every description was null before. A competency with no
         * definition is a label a student cannot act on.
         */
        $this->seed(BiicfCompetencySeeder::class);

        expect(
            BiicfCompetency::whereNull('description')
                ->orWhere('description', '')
                ->count()
        )->toBe(0);

        expect(
            BiicfCompetency::where('name', 'Application Development')->sole()->description
        )->toContain('design, define, construct, enhance, support, and maintain application software');
    });

    test('the level detail is loaded in full', function () {
        $this->seed(BiicfCompetencySeeder::class);

        /*
         * 704 level rows are printed; 84 of them read N/A and are
         * recorded by their absence, which is how the schema says
         * a competency does not reach a level.
         */
        expect(BiicfCompetencyLevelDescriptor::count())->toBe(620);
    });

    test('it can be run twice', function () {
        $this->seed(BiicfCompetencySeeder::class);
        $this->seed(BiicfCompetencySeeder::class);

        expect(BiicfCompetency::count())->toBe(139)
            ->and(BiicfCompetencyLevelDescriptor::count())->toBe(620);
    });
});

describe('a name used by both scales', function () {
    test('Service Orientation exists as both', function () {
        /*
         * The book lists it in the technical glossary and the
         * soft skills glossary, with a different definition in
         * each. A unique slug allowed only one.
         */
        $this->seed(BiicfCompetencySeeder::class);

        $both = BiicfCompetency::where('name', 'Service Orientation')
            ->orderBy('type')
            ->get();

        expect($both)->toHaveCount(2)
            ->and($both->pluck('type')->all())
            ->toBe(['soft_skill', 'technical'])
            ->and($both->first()->description)
            ->not->toBe($both->last()->description);
    });

    test('the same slug cannot repeat within one type', function () {
        $this->seed(BiicfCompetencySeeder::class);

        expect(fn () => BiicfCompetency::create([
            'name' => 'Service Orientation',
            'slug' => 'service-orientation',
            'type' => 'technical',
        ]))->toThrow(QueryException::class);
    });
});

describe('role-scoped descriptors', function () {
    test('one competency can be described per role', function () {
        /*
         * Infrastructure Management at a given level means
         * something different for an IT Technician than for a
         * Systems Engineer. The book prints three sets.
         */
        $this->seed(BiicfCompetencySeeder::class);

        $scopes = BiicfCompetency::where('name', 'Infrastructure Management')
            ->sole()
            ->levelDescriptors
            ->pluck('scope_label')
            ->unique()
            ->sort()
            ->values()
            ->all();

        expect($scopes)->toBe([
            'IT Technician',
            'Network Engineer & Network Team Lead',
            'Systems Engineer',
        ]);
    });

    test('the scoped sets differ from each other', function () {
        $this->seed(BiicfCompetencySeeder::class);

        $level = BiicfProficiencyLevel::technical()
            ->where('level_number', 3)
            ->sole();

        $atLevel = BiicfCompetency::where('name', 'Software Design')
            ->sole()
            ->levelDescriptors
            ->where('proficiency_level_id', $level->id);

        expect($atLevel->count())->toBe(2)
            ->and($atLevel->first()->statements)
            ->not->toBe($atLevel->last()->statements);
    });

    test('an unscoped descriptor applies everywhere', function () {
        $this->seed(BiicfCompetencySeeder::class);

        $descriptor = BiicfCompetency::where('name', 'Application Development')
            ->sole()
            ->levelDescriptors
            ->first();

        expect($descriptor->scope_label)->toBe('')
            ->and($descriptor->isScoped())->toBeFalse();
    });

    test('a competency and level can be described once per scope only', function () {
        $this->seed(BiicfCompetencySeeder::class);

        $competency = BiicfCompetency::where('name', 'Application Development')
            ->where('type', 'technical')
            ->sole();

        $level = BiicfProficiencyLevel::technical()
            ->where('level_number', 1)
            ->sole();

        expect(fn () => BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => $level->id,
            'statements' => ['Another one.'],
            'scope_label' => '',
        ]))->toThrow(QueryException::class);
    });
});

describe('levels the book marks N/A', function () {
    test('they are absent rather than empty', function () {
        /*
         * Application Support and Enhancement reaches neither
         * Follow nor Strategise. Absence is the record of that;
         * an empty list would claim the level exists and asks
         * nothing.
         */
        $this->seed(BiicfCompetencySeeder::class);

        $byLevel = BiicfCompetency::where('name', 'Application Support and Enhancement')
            ->sole()
            ->levelDescriptors
            ->load('proficiencyLevel')
            ->keyBy(fn ($d) => $d->proficiencyLevel->level_number);

        expect($byLevel->has(1))->toBeFalse()
            ->and($byLevel->has(5))->toBeFalse()
            ->and($byLevel->has(2))->toBeTrue()
            ->and($byLevel->has(4))->toBeTrue();
    });
});

describe('what the roles already require', function () {
    test('renamed competencies keep their role requirements', function () {
        /*
         * The earlier seeder called these "Application
         * Integration" and "Media and Platform Management"; the
         * book spells both differently. Creating the book's
         * spelling afresh would leave the roles pointing at
         * entries the framework no longer lists.
         */
        $this->seed(BiicfExplorerSeeder::class);

        $before = BiicfCompetency::where('slug', 'application-integration')
            ->sole()
            ->jobRoles()
            ->count();

        expect($before)->toBeGreaterThan(0);

        $this->seed(BiicfCompetencySeeder::class);

        $renamed = BiicfCompetency::where('slug', 'applications-integration')->sole();

        expect($renamed->name)->toBe('Applications Integration')
            ->and($renamed->jobRoles()->count())->toBe($before)
            ->and(BiicfCompetency::where('slug', 'application-integration')->exists())
            ->toBeFalse();
    });

    test('loading the book leaves role requirements standing', function () {
        $this->seed(BiicfExplorerSeeder::class);

        $before = DB::table('biicf_job_role_competency')->count();

        $this->seed(BiicfCompetencySeeder::class);

        expect(DB::table('biicf_job_role_competency')->count())->toBe($before);
    });
});
