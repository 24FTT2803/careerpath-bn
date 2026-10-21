<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Models\StudentCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BiicfExplorerController extends Controller
{
    /**
     * Main explorer page: sub-sector tree + summary counts for each side-nav section.
     */
    public function index(): View
    {
        $subSectors = BiicfSubSector::withCount('jobRoles')->orderBy('sort_order')->get();
        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get();

        /*
         * Shown as two scales rather than one list. The framework
         * rates technical skills over five levels and soft skills
         * over four, so a single run of levels would restart its
         * numbering halfway down with nothing to say why.
         */
        $proficiencyScales = [
            'Technical skills' => $proficiencyLevels
                ->where('scale', BiicfProficiencyLevel::SCALE_TECHNICAL)
                ->sortBy('level_number')
                ->values(),
            'Soft skills' => $proficiencyLevels
                ->where('scale', BiicfProficiencyLevel::SCALE_SOFT)
                ->sortBy('level_number')
                ->values(),
        ];

        return view('biicf.explorer', [
            'subSectors' => $subSectors,
            'proficiencyLevels' => $proficiencyLevels,
            'proficiencyScales' => $proficiencyScales,
            'competencyCount' => BiicfCompetency::count(),
            'jobRoleCount' => BiicfJobRole::count(),
            'trainingCount' => BiicfTraining::count(),
        ]);
    }

    /**
     * All job roles across every sub-sector, for browsing/searching without
     * first picking a sub-sector. Used by the Job Roles tab's "browse all" view.
     */
    public function allRoles()
    {
        $roles = BiicfJobRole::with('subSector:id,name,slug')
            ->orderBy('sub_sector_id')
            ->orderBy('career_path_level')
            ->get(['id', 'sub_sector_id', 'title', 'slug', 'career_path_level', 'box_colour']);

        return response()->json($roles);
    }

    /**
     * Job roles + career path edges for a given sub-sector (used to render the tree/diagram).
     */
    public function subSectorRoles(BiicfSubSector $subSector)
    {
        $roles = $subSector->jobRoles()
            ->with(['progressesTo:id,title,slug', 'progressesFrom:id,title,slug'])
            ->get(['id', 'sub_sector_id', 'title', 'slug', 'career_path_level', 'box_colour']);

        return response()->json($roles);
    }

    /**
     * Full detail for a single job role: description, competencies (grouped by type),
     * proficiency levels, entry requirements, and recommended trainings.
     */
    public function jobRole(BiicfJobRole $jobRole)
    {
        $jobRole->load([
            'subSector:id,name,slug',
            'entryRequirement',
            'trainings',
            'tiers.competencies' => function ($q) {
                $q->orderBy('type')->orderBy('name');
            },
            'tiers.jobLevel:id,name',
            'tiers.entryRequirements',
            'progressesTo:id,title,slug',
            'progressesFrom:id,title,slug',
            'descriptionPoints',
            'programmingLanguages',
            /*
             * Three levels deep: the book groups duties under
             * headings of its own and nests a further level
             * under some of them.
             */
            'genericWorkFunctions.children.children',
            'specificWorkFunctions.jobFamily',
            'specificWorkFunctions.children.children',
        ]);

        /*
         * Specific functions are grouped here rather than in the
         * page, because the book prints them under their job
         * family as a heading and rebuilding that grouping in
         * the template would be more work than it is worth.
         */
        $specificByFamily = $jobRole->specificWorkFunctions
            ->groupBy(fn ($function) => $function->jobFamily?->name ?? 'Other')
            ->map(fn ($functions, $family) => [
                'family' => $family,
                'functions' => $functions->values(),
            ])
            ->values();

        /*
         * One tier per column of the book's table, each with its
         * own proficiencies: Application Development is wanted at
         * 1, 2 and 2 across IT Technician's three levels. The
         * page lets a student switch between them.
         */
        $levels = BiicfProficiencyLevel::all()->keyBy('id');

        $tiers = $jobRole->tiers->map(fn ($tier) => [
            'id' => $tier->id,
            'label' => $tier->label,
            'job_level' => $tier->jobLevel?->name,
            'entry_requirements' => $tier->entryRequirements
                ->map(fn ($requirement) => [
                    'alternative_number' => $requirement->alternative_number,
                    'text' => $requirement->raw_text,
                    'bdqf_level' => $requirement->bdqf_level,
                    'field_of_study' => $requirement->field_of_study,
                    'conditions' => $requirement->conditions(),
                    'measurable' => $requirement->isMeasurable(),
                ])
                ->values(),
            /*
             * The requirement, and no verdict on it. The page
             * used to say where the student stood against each
             * tier, which went wrong in both directions: a
             * student part way through a diploma was told their
             * profile could not be checked, and a way in reading
             * "BDQF Level 2 in IT or related field with relevant
             * industry experience" was announced as asking for
             * experience rather than a qualification. The book's
             * own sentence says it plainly, and a student can
             * read it.
             */
            'competencies_by_type' => $this->competenciesByType($tier, $levels),
        ])->values();

        /*
         * The entry tier stays at the top level: it is what it
         * takes to enter the role, and it is what the page shows
         * before anyone picks a tier.
         */
        $entryCompetencies = $jobRole->tiers->first()?->competencies
            ?? collect();

        return response()->json([
            'job_role' => $jobRole,
            'tiers' => $tiers,
            'competencies_by_type' => $entryCompetencies->groupBy('type'),
            'specific_work_functions_by_family' => $specificByFamily,
        ]);
    }

    /**
     * One tier's competencies, grouped as the book groups them.
     *
     * The proficiency is resolved here rather than in the page:
     * the pivot carries an id, and the two scales number their
     * rungs separately, so "3" means Apply on a technical
     * competency and Advanced on a soft skill.
     *
     * @param  Collection<int, BiicfProficiencyLevel>  $levels
     */
    private function competenciesByType($tier, $levels)
    {
        return $tier->competencies
            ->groupBy('type')
            ->map(fn ($group) => $group->map(function ($competency) use ($levels) {
                $level = $levels->get($competency->pivot->proficiency_level_id);

                return [
                    'id' => $competency->id,
                    'name' => $competency->name,
                    'type' => $competency->type,
                    /*
                     * Shown where the competency is opened, so the
                     * screen does not ask a student to recognise a
                     * name with nothing beside it.
                     */
                    'description' => $competency->inEssence() ?? $competency->description,
                    'is_core' => (bool) $competency->pivot->is_core,
                    'level' => $level ? [
                        'number' => $level->level_number,
                        'name' => $level->name,
                        'scale' => $level->scale,
                        /*
                         * How many rungs this competency's scale
                         * has. A bar drawn to a fixed five shows a
                         * soft skill at Expert as four of five and
                         * reads as a shortfall.
                         */
                        'of' => $level->scaleLength(),
                    ] : null,
                ];
            })->values());
    }

    /**
     * One competency: what it is, and what each of its levels
     * asks of someone.
     *
     * The book defines every competency once and then describes
     * it rung by rung, which is the part a student actually
     * needs — "Apply" alone says nothing about what applying
     * Cloud Computing looks like.
     *
     * Four competencies are described differently depending on
     * the role, so the descriptions come back as sets: the
     * general one first, then any that name the roles they
     * cover.
     */
    public function competency(BiicfCompetency $competency)
    {
        $competency->load('levelDescriptors.jobRoles:id,title,slug');

        $scale = BiicfProficiencyLevel::where('scale', $competency->scale())
            ->orderBy('level_number')
            ->get();

        $sets = $competency->levelDescriptors
            ->groupBy(fn ($descriptor) => $descriptor->scope_label ?? '')
            ->sortKeys()
            ->map(fn ($descriptors, $scope) => [
                'scope' => $scope ?: null,
                'job_roles' => $descriptors->first()?->jobRoles
                    ->pluck('title')
                    ->values() ?? collect(),
                'levels' => $scale->map(function ($level) use ($descriptors) {
                    $descriptor = $descriptors->firstWhere(
                        'proficiency_level_id',
                        $level->id
                    );

                    return [
                        'number' => $level->level_number,
                        'name' => $level->name,
                        'summary' => $level->description,
                        'statements' => $descriptor->statements ?? [],
                        /*
                         * A level the book leaves out is not the
                         * same as one it leaves empty: the
                         * competency does not reach that rung at
                         * all.
                         */
                        'described' => $descriptor !== null,
                    ];
                })->values(),
            ])
            ->values();

        return response()->json([
            'competency' => [
                'id' => $competency->id,
                'name' => $competency->name,
                'type' => $competency->type,
                'description' => $competency->description,
                'scale' => $competency->scale(),
            ],
            'sets' => $sets,
        ]);
    }

    /**
     * Competencies glossary, filterable by type (technical|soft_skill) and search term.
     */
    public function competencies(Request $request)
    {
        $query = BiicfCompetency::query();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Compare the logged-in student's own competencies (student_competencies table)
     * against what this job role requires, matching by skill/competency name.
     *
     * Students pick BIICF levels on their profile (Follow, Assist, Apply, Ensure,
     * Strategise), so those names are read first. Older free-text levels
     * (beginner/intermediate/advanced/expert) are still understood.
     */
    public function compareToMe(Request $request, BiicfJobRole $jobRole)
    {
        $jobRole->load(['tiers.competencies' => fn ($q) => $q->orderBy('type')]);

        $levels = BiicfProficiencyLevel::orderBy('level_number')->get();

        $byId = $levels->keyBy('id');

        /*
         * A rung's name, within its own scale.
         *
         * Flat, the two scales read as one and the comparison is
         * wrong without ever looking it: a soft skill's Advanced
         * is three of four and a technical Apply is three of
         * five, and the soft scale's rungs are named Basic,
         * Intermediate, Advanced and Expert — the same four
         * words the profile used before BIICF levels existed.
         * So a student's wording is resolved against the scale
         * the requirement is on, never across both.
         */
        $byScale = $levels
            ->groupBy('scale')
            ->map(fn ($rungs) => $rungs
                ->mapWithKeys(fn ($rung) => [mb_strtolower($rung->name) => (int) $rung->level_number])
                ->all());

        /*
         * What the profile recorded before it offered BIICF
         * levels. Read only where the scale itself does not
         * recognise the wording.
         */
        $legacy = [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3,
            'expert' => 4,
        ];

        $studentSkills = StudentCompetency::where('user_id', auth()->id())->get()
            ->keyBy(fn ($s) => Str::slug($s->skill_name));

        /*
         * The tier being looked at, and only that one.
         *
         * A requirement belongs to a tier rather than to the
         * role — Application Development is wanted at 1, 2 and 2
         * across IT Technician's three levels — so comparing
         * against the role returns every competency once per
         * tier and counts each of them again in the summary.
         */
        $tier = $jobRole->tiers->firstWhere('id', $request->integer('tier'))
            ?? $jobRole->tiers->firstWhere('label', $request->string('tier')->toString())
            ?? $jobRole->tiers->first();

        $comparison = ($tier?->competencies ?? collect())
            ->map(function ($competency) use ($studentSkills, $byId, $byScale, $legacy) {
                $required = $byId->get($competency->pivot->proficiency_level_id);
                $match = $studentSkills->get(Str::slug($competency->name));

                $scale = $required?->scale ?? BiicfProficiencyLevel::SCALE_TECHNICAL;

                $said = mb_strtolower(trim((string) $match?->proficiency_level));

                $studentLevelNumber = $match
                    ? ($byScale[$scale][$said] ?? $legacy[$said] ?? null)
                    : null;

                return [
                    'competency' => Arr::only($competency->toArray(), ['id', 'name', 'type']),
                    'is_core' => (bool) $competency->pivot->is_core,
                    'required_level' => $required
                        ? Arr::only($required->toArray(), ['level_number', 'name', 'scale'])
                        : null,
                    'scale_of' => $required?->scaleLength(),
                    'student_has_skill' => (bool) $match,
                    'student_level' => $match?->proficiency_level,
                    'student_level_number' => $studentLevelNumber,
                    'meets_requirement' => $match
                        && $required
                        && $studentLevelNumber !== null
                        && $studentLevelNumber >= $required->level_number,
                ];
            });

        return response()->json([
            'job_role' => Arr::only($jobRole->toArray(), ['id', 'title', 'slug']),
            'tier' => $tier ? ['id' => $tier->id, 'label' => $tier->label] : null,
            'tiers' => $jobRole->tiers
                ->map(fn ($one) => ['id' => $one->id, 'label' => $one->label])
                ->values(),
            'comparison' => $comparison->values(),
            'summary' => [
                'total' => $comparison->count(),
                'met' => $comparison->where('meets_requirement', true)->count(),
                'below' => $comparison
                    ->where('student_has_skill', true)
                    ->where('meets_requirement', false)
                    ->count(),
                'missing' => $comparison->where('student_has_skill', false)->count(),
            ],
        ]);
    }
}
