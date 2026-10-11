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
                    'is_core' => (bool) $competency->pivot->is_core,
                    'level' => $level ? [
                        'number' => $level->level_number,
                        'name' => $level->name,
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
     * student_competencies.proficiency_level is a free-text string (beginner/intermediate/
     * advanced/expert); we map it onto BIICF's numeric level_number scale to compare.
     */
    public function compareToMe(BiicfJobRole $jobRole)
    {
        $jobRole->load(['tiers.competencies' => fn ($q) => $q->orderBy('type')]);

        $levelMap = [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3,
            'expert' => 4,
        ];

        $studentSkills = StudentCompetency::where('user_id', auth()->id())->get()
            ->keyBy(fn ($s) => Str::slug($s->skill_name));

        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get()->keyBy('id');

        $entryCompetencies = $jobRole->tiers->first()?->competencies
            ?? collect();

        $comparison = $entryCompetencies->map(function ($competency) use ($studentSkills, $levelMap, $proficiencyLevels) {
            $required = $proficiencyLevels->get($competency->pivot->proficiency_level_id);
            $match = $studentSkills->get(Str::slug($competency->name));

            $studentLevelNumber = $match ? ($levelMap[strtolower($match->proficiency_level)] ?? null) : null;

            return [
                'competency' => Arr::only($competency->toArray(), ['id', 'name', 'type']),
                'is_core' => (bool) $competency->pivot->is_core,
                'required_level' => $required ? Arr::only($required->toArray(), ['level_number', 'name']) : null,
                'student_has_skill' => (bool) $match,
                'student_level' => $match?->proficiency_level,
                'student_level_number' => $studentLevelNumber,
                'meets_requirement' => $match && $required && $studentLevelNumber !== null && $studentLevelNumber >= $required->level_number,
            ];
        });

        return response()->json([
            'job_role' => Arr::only($jobRole->toArray(), ['id', 'title', 'slug']),
            'comparison' => $comparison,
            'summary' => [
                'total' => $comparison->count(),
                'met' => $comparison->where('meets_requirement', true)->count(),
                'missing' => $comparison->where('student_has_skill', false)->count(),
            ],
        ]);
    }
}
