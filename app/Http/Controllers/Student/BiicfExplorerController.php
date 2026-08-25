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

        return view('biicf.explorer', [
            'subSectors' => $subSectors,
            'proficiencyLevels' => $proficiencyLevels,
            'competencyCount' => BiicfCompetency::count(),
            'jobRoleCount' => BiicfJobRole::count(),
            'trainingCount' => BiicfTraining::count(),
        ]);
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
            'competencies' => function ($q) {
                $q->orderBy('type');
            },
            'progressesTo:id,title,slug',
            'progressesFrom:id,title,slug',
        ]);

        return response()->json([
            'job_role' => $jobRole,
            'competencies_by_type' => $jobRole->competencies->groupBy('type'),
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
        $jobRole->load(['competencies' => fn ($q) => $q->orderBy('type')]);

        $levelMap = [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3,
            'expert' => 4,
        ];

        $studentSkills = StudentCompetency::where('user_id', auth()->id())->get()
            ->keyBy(fn ($s) => Str::slug($s->skill_name));

        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get()->keyBy('id');

        $comparison = $jobRole->competencies->map(function ($competency) use ($studentSkills, $levelMap, $proficiencyLevels) {
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