<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Models\CareerRecommendation;
use App\Models\User;
use Database\Seeders\BiicfExplorerSeeder;
use Database\Seeders\BiicfJobRoleSeeder;
use Illuminate\Support\Facades\DB;

/*
 * Loading the job roles as the reference book states them.
 *
 * The pages the student reads were previously filled with a
 * paraphrase: one description sentence, a handful of duties, no
 * tiers and no entry requirements. These tests check that what
 * now arrives is the book's own content, and that loading it does
 * not break the recommendations and competency rows already
 * pointing at the roles.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
});

describe('the roles themselves', function () {
    test('every role in the book is loaded', function () {
        /*
         * Thirty-eight roles over thirty-nine listings: Chief
         * Technology Officer is printed under two sub-sectors and
         * is one role, not two.
         */
        expect(BiicfJobRole::count())->toBe(38);
    });

    test('they are spread across the book\'s sub-sectors', function () {
        expect(BiicfSubSector::has('jobRoles')->count())->toBe(6);
    });

    test('a role carries its alternate titles', function () {
        $role = BiicfJobRole::where('title', 'Application System Engineer')->sole();

        expect($role->alternative_titles)
            ->toContain('Computer Systems Engineer')
            ->toContain('Server Engineer');
    });

    test('several functional groups are kept, not one', function () {
        /*
         * IT Technician is printed under three. The column holds
         * one string, so they are joined rather than dropped.
         */
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        expect($role->functional_group)
            ->toContain('Infrastructure')
            ->toContain('Hardware Support')
            ->toContain('Software and Systems');
    });

    test('job families are a relation and keep their order', function () {
        $role = BiicfJobRole::where('title', 'Application System Engineer')->sole();

        expect($role->jobFamilies->pluck('name')->first())
            ->toBe('Server and Network Support');
    });

    test('a role can sit at more than one job level', function () {
        $role = BiicfJobRole::where('title', 'IT Administrator/Manager')->sole();

        expect($role->jobLevels->pluck('name')->all())
            ->toBe(['Entrant', 'Expert/Management']);
    });

    test('a new role is placed on the map by its seniority', function () {
        /*
         * The book draws the progression as a diagram and states
         * no number, so a role arriving without one is placed by
         * its job level. A C-Suite role sitting at zero would
         * otherwise be drawn below an entrant.
         */
        $entrant = BiicfJobRole::where('title', 'Junior Programmer')->sole();
        $chief = BiicfJobRole::where('title', 'Chief Information Officer')->sole();

        expect($chief->career_path_level)
            ->toBeGreaterThan($entrant->career_path_level);
    });

    test('a role already placed keeps its position', function () {
        /*
         * The earlier seeder's numbers draw maps that read
         * correctly; renumbering them would redraw those.
         */
        $this->seed(BiicfExplorerSeeder::class);

        $before = BiicfJobRole::where('title', 'IT Technician')->value('career_path_level');

        $this->seed(BiicfJobRoleSeeder::class);

        expect(BiicfJobRole::where('title', 'IT Technician')->value('career_path_level'))
            ->toBe($before);
    });
});

describe('the content on a role page', function () {
    test('the description is the book\'s list, not one sentence', function () {
        $role = BiicfJobRole::where('title', 'Application System Engineer')->sole();

        expect($role->descriptionPoints->count())->toBeGreaterThan(3)
            ->and($role->descriptionPoints->first()->text)
            ->toStartWith('Analyse, design and develop roadmaps');
    });

    test('the duties come through as rows', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        expect($role->criticalWorkFunctions->count())->toBeGreaterThan(5);
    });

    test('loaded duties are generic until a family is named', function () {
        /*
         * The book attributes duties to a job family on one role
         * page out of thirty-nine. Everywhere else it prints a
         * flat list, and claiming a family for those would be an
         * invention.
         */
        expect(
            BiicfCriticalWorkFunction::where('kind', BiicfCriticalWorkFunction::KIND_SPECIFIC)
                ->count()
        )->toBe(0);
    });

    test('every role has a description and duties', function () {
        $thin = BiicfJobRole::query()
            ->doesntHave('descriptionPoints')
            ->orDoesntHave('criticalWorkFunctions')
            ->pluck('title')
            ->all();

        expect($thin)->toBe([]);
    });

    test('trainings are attached and shared between roles', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        expect($role->trainings)->not->toBeEmpty()
            ->and(BiicfTraining::has('jobRoles', '>', 1)->count())
            ->toBeGreaterThan(0);
    });

    test('a training is a course name, not a duty sentence', function () {
        /*
         * The first pass read this column out of the flattened
         * page text, which interleaves it with the duties beside
         * it: "Update records of installed hardware." arrived as
         * a training course, and one entry was long enough that
         * the database refused it. Reading the column by its
         * position on the page is what separates them.
         */
        $names = BiicfTraining::pluck('name');

        /*
         * 160 characters is well past the longest course the book
         * names and well short of the duty sentences that used to
         * land here. A few courses do carry the book's own gloss
         * — "…: Validates Azure cloud management skills." — so a
         * trailing full stop is not the test.
         */
        expect($names->filter(fn (string $name) => mb_strlen($name) > 160))
            ->toBeEmpty();
    });

    test('the running footer is not a training course', function () {
        expect(
            BiicfTraining::where('name', 'like', '%Table of Contents%')
                ->orWhere('name', 'like', '%Competency Framework%')
                ->count()
        )->toBe(0);
    });
});

describe('tiers', function () {
    test('a single-tier role still gets a tier', function () {
        /*
         * Required competencies hang off a tier, so a role
         * without one could hold none.
         */
        expect(BiicfJobRole::doesntHave('tiers')->count())->toBe(0);
    });

    test('a multi-tier role gets them in the book\'s order', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        expect($role->tiers->pluck('label')->all())
            ->toBe(['Level 1', 'Level 2', 'Level 3']);
    });

    test('a tier labelled with a job level is linked to it', function () {
        $role = BiicfJobRole::where('title', 'Network Engineer')->sole();

        expect($role->tiers->pluck('jobLevel.name')->all())
            ->toBe(['Specialist', 'Expert/Management']);
    });

    test('a numbered tier is linked to no job level', function () {
        /*
         * IT Technician's three levels are steps inside Entrant,
         * not job levels of their own.
         */
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        expect($role->tiers->pluck('job_level_id')->filter()->all())->toBe([]);
    });

    test('the entry tier is the lowest one', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        expect($role->entryTier->label)->toBe('Level 1');
    });
});

describe('the competency tables', function () {
    test('every name in the tables is a competency in the library', function () {
        /*
         * Name and type together: the two lists are separate in
         * the book and the type is half the key. Writing the soft
         * skills' type as "soft" where the library says
         * "soft_skill" made every one of them unresolvable, and
         * the failure named only the first.
         */
        $library = BiicfCompetency::all()
            ->map(fn (BiicfCompetency $competency) => $competency->name.'|'.$competency->type);

        $rows = collect(json_decode(
            (string) file_get_contents(database_path('data/biicf-job-roles.json')),
            true
        ))
            ->pluck('competencies')
            ->flatten(1)
            ->map(fn (array $row) => $row['name'].'|'.$row['type'])
            ->unique();

        expect($rows->diff($library)->values()->all())->toBe([]);
    });

    test('every role carries both tables', function () {
        $thin = BiicfJobRole::all()
            ->filter(function (BiicfJobRole $role) {
                $types = $role->entryTier->competencies->pluck('type')->unique();

                return $types->count() < 2;
            })
            ->pluck('title')
            ->all();

        expect($thin)->toBe([]);
    });

    test('the soft skills are the book\'s eleven', function () {
        /*
         * The same eleven are printed for every role in the book,
         * at whatever proficiency the role calls for. A role
         * showing fewer means a table that continues in a second
         * column on the page was read only as far as its first.
         */
        $role = BiicfJobRole::where('title', 'Marketing Manager')->sole();

        expect(
            $role->entryTier->competencies->where('type', '!=', 'technical')
        )->toHaveCount(11);
    });

    test('a tier gets its own column of the table', function () {
        /*
         * IT Technician's Application Development runs 1, 2, 2
         * across its three levels. A single proficiency per role
         * could not have held that.
         */
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $numbers = $role->tiers->map(
            fn ($tier) => BiicfProficiencyLevel::find(
                $tier->competencies
                    ->firstWhere('name', 'Application Development')
                    ->pivot
                    ->proficiency_level_id
            )->level_number
        )->all();

        expect($numbers)->toBe([1, 2, 2]);
    });

    test('each scale is rated on its own levels', function () {
        /*
         * The technical scale has five rungs and the soft four,
         * and they share a table. A soft skill pointed at a
         * technical level would read as the wrong word entirely.
         */
        $tier = BiicfJobRole::where('title', 'Data Scientist')->sole()->entryTier;

        $scales = $tier->competencies->map(fn ($competency) => [
            $competency->type === 'technical',
            BiicfProficiencyLevel::find($competency->pivot->proficiency_level_id)->scale
                === BiicfProficiencyLevel::SCALE_TECHNICAL,
        ]);

        expect($scales->filter(fn ($pair) => $pair[0] !== $pair[1]))->toBeEmpty();
    });

    test('a name the table spells differently still finds the glossary entry', function () {
        /*
         * Sixteen role tables print "Creativity & Innovation" and
         * twenty-three "Creativity and Innovation". The glossary
         * lists one competency, and both are it.
         */
        expect(BiicfCompetency::where('name', 'like', 'Creativity%')->count())
            ->toBe(1)
            ->and(
                BiicfCompetency::where('name', 'Creativity and Innovation')
                    ->sole()
                    ->jobRoles()
                    ->count()
            )->toBeGreaterThan(30);
    });

    test('re-running does not double the table', function () {
        $before = DB::table('biicf_job_role_competency')->count();

        $this->seed(BiicfJobRoleSeeder::class);

        expect(DB::table('biicf_job_role_competency')->count())->toBe($before);
    });

    test('every proficiency belongs to a tier', function () {
        /*
         * A row written against the role but no tier is invisible
         * to every page that reads a tier's column, which is all
         * of them.
         */
        expect(
            DB::table('biicf_job_role_competency')
                ->whereNull('job_role_tier_id')
                ->count()
        )->toBe(0);
    });
});

describe('entry requirements', function () {
    test('the book\'s wording is kept as written', function () {
        $role = BiicfJobRole::where('title', 'Chief Technology Officer')->sole();

        expect($role->entryRequirement->raw_text)
            ->toContain('BDQF Level 6')
            ->toContain('10 years');
    });

    test('a requirement naming several levels is not reduced to one', function () {
        /*
         * Chief Technology Officer accepts BDQF 6 with ten years
         * or BDQF 5 with twelve. Picking a number out of that
         * would make the match wrong rather than undetermined.
         */
        $role = BiicfJobRole::where('title', 'Chief Technology Officer')->sole();

        expect($role->entryRequirement->bdqf_level)->toBeNull()
            ->and($role->entryRequirement->isMeasurable())->toBeFalse();
    });

    test('an unmeasurable requirement still prints', function () {
        $role = BiicfJobRole::where('title', 'Chief Technology Officer')->sole();

        expect($role->entryRequirement->level_label)->not->toBeEmpty();
    });

    test('a role the book gives no requirement for has none', function () {
        /*
         * Four role pages print no entry requirement. An invented
         * one would be worse than its absence.
         */
        $role = BiicfJobRole::where('title', 'Junior Programmer')->sole();

        expect($role->entryRequirement)->toBeNull();
    });

    test('the requirement hangs off a tier and knows its role', function () {
        $requirement = BiicfJobRole::where('title', 'IT Technician')
            ->sole()
            ->entryRequirement;

        expect($requirement->job_role_tier_id)->not->toBeNull()
            ->and($requirement->job_role_id)->not->toBeNull();
    });
});

describe('re-running the load', function () {
    test('it does not duplicate anything', function () {
        $before = [
            BiicfJobRole::count(),
            BiicfJobRoleTier::count(),
            BiicfEntryRequirement::count(),
            BiicfCriticalWorkFunction::count(),
        ];

        $this->seed(BiicfJobRoleSeeder::class);

        expect([
            BiicfJobRole::count(),
            BiicfJobRoleTier::count(),
            BiicfEntryRequirement::count(),
            BiicfCriticalWorkFunction::count(),
        ])->toBe($before);
    });

    test('a role keeps its id, so what points at it survives', function () {
        /*
         * Recommendations, competency requirements and saved
         * history all reference a role by id. Matching on the
         * title rather than the slug is what keeps them: the
         * earlier seeder slugged "IT Administrator/Manager" with
         * the slash dropped, so the slugs do not agree even where
         * the roles do.
         */
        $id = BiicfJobRole::where('title', 'IT Administrator/Manager')->value('id');

        $this->seed(BiicfJobRoleSeeder::class);

        expect(BiicfJobRole::where('title', 'IT Administrator/Manager')->value('id'))
            ->toBe($id);
    });

    test('a placeholder tier hands its rows to the book\'s tier', function () {
        /*
         * On a re-run the book's tier already exists, so the
         * placeholder cannot take its name and what hangs off it
         * has to move across instead. The competency rows are
         * replaced by the book's own table either way, but a
         * role's entry requirement is not, so that is what would
         * be lost with the tier.
         */
        $this->seed(BiicfExplorerSeeder::class);

        $role = BiicfJobRole::where('title', 'IT Technician')->sole();
        $placeholder = $role->tiers()->where('label', 'All levels')->sole();

        $placeholder->entryRequirements()->delete();
        BiicfEntryRequirement::create([
            'job_role_tier_id' => $placeholder->id,
            'alternative_number' => 2,
            'raw_text' => 'An alternative an administrator added.',
        ]);

        $this->seed(BiicfJobRoleSeeder::class);

        expect(
            BiicfEntryRequirement::where('raw_text', 'An alternative an administrator added.')
                ->first()?->tier?->label
        )->toBe('Level 1');
    });

    test('the placeholder tier becomes the book\'s first tier', function () {
        $this->seed(BiicfExplorerSeeder::class);
        $this->seed(BiicfJobRoleSeeder::class);

        expect(
            BiicfJobRoleTier::where('label', 'All levels')->count()
        )->toBe(0);
    });

    test('the book\'s wording replaces the approximated requirement', function () {
        /*
         * The old row held a level and a field of study nobody
         * read out of the book. Keeping those beside the real
         * sentence would present a guess as the book's own.
         */
        $this->seed(BiicfExplorerSeeder::class);
        $this->seed(BiicfJobRoleSeeder::class);

        $requirement = BiicfJobRole::where('title', 'IT Technician')
            ->sole()
            ->entryRequirement;

        expect($requirement->raw_text)->toContain('IT Technician Level 1')
            ->and($requirement->bdqf_level)->toBeNull()
            ->and($requirement->field_of_study)->toBeNull();
    });

    test('a competency the earlier seeder spelled differently is folded in', function () {
        /*
         * The earlier seeder writes "Application Integration"
         * where the book writes "Applications Integration". The
         * book's entry is created on every load, so by the second
         * one both exist, and slug and type are unique together:
         * renaming the old onto the new has to merge rather than
         * collide.
         */
        $this->seed(BiicfExplorerSeeder::class);
        $this->seed(BiicfJobRoleSeeder::class);

        expect(BiicfCompetency::where('slug', 'application-integration')->count())
            ->toBe(0)
            ->and(BiicfCompetency::where('slug', 'applications-integration')->count())
            ->toBe(1);
    });

    test('a role the book prints once is not left as two rows', function () {
        /*
         * The earlier seeder created a row per listing, so Chief
         * Technology Officer and Business Intelligence Director
         * each existed twice. Loading the book has to merge those
         * rather than leave a hollow copy with no tiers showing in
         * the explorer.
         */
        $this->seed(BiicfExplorerSeeder::class);
        $this->seed(BiicfJobRoleSeeder::class);

        expect(BiicfJobRole::where('title', 'Chief Technology Officer')->count())
            ->toBe(1)
            ->and(BiicfJobRole::where('title', 'Business Intelligence Director')->count())
            ->toBe(1);
    });

    test('merging a duplicate keeps a student\'s recommendation', function () {
        /*
         * The reference cascades on delete, so a merge that did
         * not repoint it first would take the saved result with
         * the row.
         */
        $this->seed(BiicfExplorerSeeder::class);

        $duplicate = BiicfJobRole::where('title', 'Chief Technology Officer')
            ->orderByDesc('id')
            ->first();

        $recommendation = CareerRecommendation::create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'biicf_job_role_id' => $duplicate->id,
            'rank' => 1,
            'match_score' => 80,
            'career_readiness_score' => 70,
        ]);

        $this->seed(BiicfJobRoleSeeder::class);

        expect($recommendation->fresh()->biicf_job_role_id)
            ->toBe(BiicfJobRole::where('title', 'Chief Technology Officer')->value('id'));
    });

    test('a tier removed from the book takes its rows with it', function () {
        /*
         * No database cascade can do this: the column carries no
         * foreign key, because SQLite cannot drop one. The model
         * hook is the cascade, and a reload is what exercises it.
         */
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $extra = BiicfJobRoleTier::create([
            'job_role_id' => $role->id,
            'label' => 'Level 4',
            'sort_order' => 4,
        ]);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $extra->id,
            'alternative_number' => 1,
            'raw_text' => 'Invented.',
        ]);

        $this->seed(BiicfJobRoleSeeder::class);

        expect(BiicfJobRoleTier::find($extra->id))->toBeNull()
            ->and(BiicfEntryRequirement::where('raw_text', 'Invented.')->count())
            ->toBe(0);
    });
});
