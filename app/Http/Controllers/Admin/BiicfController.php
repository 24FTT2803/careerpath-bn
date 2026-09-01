<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiicfSubSector;
use App\Models\BiicfJobRole;
use App\Models\BiicfCompetency;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfTraining;
use App\Models\BiicfEntryRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BiicfController extends Controller
{
    // ============================================
    // SUBSECTORS
    // ============================================
    public function subSectors()
    {
        $subSectors = BiicfSubSector::withCount('jobRoles')
            ->orderBy('sort_order')
            ->paginate(15);
        return view('admin.biicf.sub-sectors.index', compact('subSectors'));
    }

    public function subSectorCreate()
    {
        return view('admin.biicf.sub-sectors.create');
    }

    public function subSectorStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:biicf_sub_sectors',
            'description' => 'nullable|string',
            'lead_organisation' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        BiicfSubSector::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'lead_organisation' => $request->lead_organisation,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.biicf.sub-sectors')
            ->with('success', 'Sub-sector created successfully.');
    }

    public function subSectorEdit(BiicfSubSector $subSector)
    {
        return view('admin.biicf.sub-sectors.edit', compact('subSector'));
    }

    public function subSectorUpdate(Request $request, BiicfSubSector $subSector)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('biicf_sub_sectors')->ignore($subSector->id)],
            'description' => 'nullable|string',
            'lead_organisation' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $subSector->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'lead_organisation' => $request->lead_organisation,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.biicf.sub-sectors')
            ->with('success', 'Sub-sector updated successfully.');
    }

    public function subSectorDestroy(BiicfSubSector $subSector)
    {
        if ($subSector->jobRoles()->count() > 0) {
            return redirect()->route('admin.biicf.sub-sectors')
                ->with('error', 'Cannot delete sub-sector with associated job roles.');
        }
        $subSector->delete();
        return redirect()->route('admin.biicf.sub-sectors')
            ->with('success', 'Sub-sector deleted successfully.');
    }

    // ============================================
    // JOB ROLES
    // ============================================
    public function jobRoles(Request $request)
    {
        $query = BiicfJobRole::with('subSector');
        
        if ($request->has('sub_sector') && $request->sub_sector) {
            $query->where('sub_sector_id', $request->sub_sector);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('functional_group', 'LIKE', "%{$search}%");
            });
        }
        
        $jobRoles = $query->orderBy('title')->paginate(15);
        $subSectors = BiicfSubSector::orderBy('name')->get();
        
        return view('admin.biicf.job-roles.index', compact('jobRoles', 'subSectors'));
    }

    public function jobRoleCreate()
    {
        $subSectors = BiicfSubSector::orderBy('name')->get();
        $competencies = BiicfCompetency::orderBy('name')->get();
        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get();
        $trainings = BiicfTraining::orderBy('name')->get();
        
        return view('admin.biicf.job-roles.create', compact('subSectors', 'competencies', 'proficiencyLevels', 'trainings'));
    }

    public function jobRoleStore(Request $request)
    {
        $request->validate([
            'sub_sector_id' => 'required|exists:biicf_sub_sectors,id',
            'title' => 'required|string|max:255|unique:biicf_job_roles',
            'functional_group' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'critical_work_function' => 'nullable|string',
            'alternative_titles' => 'nullable|array',
            'career_path_level' => 'nullable|integer|min:0',
            'box_colour' => 'nullable|string|max:20',
            'competencies' => 'nullable|array',
            'competencies.*.id' => 'exists:biicf_competencies,id',
            'competencies.*.proficiency_level_id' => 'exists:biicf_proficiency_levels,id',
            'competencies.*.is_core' => 'boolean',
            'trainings' => 'nullable|array',
            'trainings.*' => 'exists:biicf_trainings,id',
        ]);

        $jobRole = BiicfJobRole::create([
            'sub_sector_id' => $request->sub_sector_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'functional_group' => $request->functional_group,
            'job_description' => $request->job_description,
            'critical_work_function' => $request->critical_work_function,
            'alternative_titles' => $request->alternative_titles ?? [],
            'career_path_level' => $request->career_path_level ?? 0,
            'box_colour' => $request->box_colour ?? 'primary',
        ]);

        if ($request->has('competencies')) {
            foreach ($request->competencies as $comp) {
                $jobRole->competencies()->attach($comp['id'], [
                    'proficiency_level_id' => $comp['proficiency_level_id'],
                    'is_core' => $comp['is_core'] ?? true,
                ]);
            }
        }

        if ($request->has('trainings')) {
            $jobRole->trainings()->sync($request->trainings);
        }

        return redirect()->route('admin.biicf.job-roles')
            ->with('success', 'Job role created successfully.');
    }

    public function jobRoleShow(BiicfJobRole $jobRole)
    {
        $jobRole->load(['subSector', 'competencies', 'trainings', 'entryRequirement']);
        return view('admin.biicf.job-roles.show', compact('jobRole'));
    }

    public function jobRoleEdit(BiicfJobRole $jobRole)
    {
        $subSectors = BiicfSubSector::orderBy('name')->get();
        $competencies = BiicfCompetency::orderBy('name')->get();
        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get();
        $trainings = BiicfTraining::orderBy('name')->get();
        
        $jobRole->load(['competencies', 'trainings']);
        
        return view('admin.biicf.job-roles.edit', compact('jobRole', 'subSectors', 'competencies', 'proficiencyLevels', 'trainings'));
    }

    public function jobRoleUpdate(Request $request, BiicfJobRole $jobRole)
    {
        $request->validate([
            'sub_sector_id' => 'required|exists:biicf_sub_sectors,id',
            'title' => ['required', 'string', 'max:255', Rule::unique('biicf_job_roles')->ignore($jobRole->id)],
            'functional_group' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'critical_work_function' => 'nullable|string',
            'alternative_titles' => 'nullable|array',
            'career_path_level' => 'nullable|integer|min:0',
            'box_colour' => 'nullable|string|max:20',
            'competencies' => 'nullable|array',
            'competencies.*.id' => 'exists:biicf_competencies,id',
            'competencies.*.proficiency_level_id' => 'exists:biicf_proficiency_levels,id',
            'competencies.*.is_core' => 'boolean',
            'trainings' => 'nullable|array',
            'trainings.*' => 'exists:biicf_trainings,id',
        ]);

        $jobRole->update([
            'sub_sector_id' => $request->sub_sector_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'functional_group' => $request->functional_group,
            'job_description' => $request->job_description,
            'critical_work_function' => $request->critical_work_function,
            'alternative_titles' => $request->alternative_titles ?? [],
            'career_path_level' => $request->career_path_level ?? 0,
            'box_colour' => $request->box_colour ?? 'primary',
        ]);

        $jobRole->competencies()->detach();
        if ($request->has('competencies')) {
            foreach ($request->competencies as $comp) {
                $jobRole->competencies()->attach($comp['id'], [
                    'proficiency_level_id' => $comp['proficiency_level_id'],
                    'is_core' => $comp['is_core'] ?? true,
                ]);
            }
        }

        if ($request->has('trainings')) {
            $jobRole->trainings()->sync($request->trainings);
        } else {
            $jobRole->trainings()->sync([]);
        }

        return redirect()->route('admin.biicf.job-roles')
            ->with('success', 'Job role updated successfully.');
    }

    public function jobRoleDestroy(BiicfJobRole $jobRole)
    {
        $jobRole->competencies()->detach();
        $jobRole->trainings()->detach();
        $jobRole->delete();
        
        return redirect()->route('admin.biicf.job-roles')
            ->with('success', 'Job role deleted successfully.');
    }

    // ============================================
    // COMPETENCIES
    // ============================================
    public function competencies(Request $request)
    {
        $query = BiicfCompetency::query();
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        $competencies = $query->orderBy('name')->paginate(15);
        
        return view('admin.biicf.competencies.index', compact('competencies'));
    }

    public function competencyCreate()
    {
        return view('admin.biicf.competencies.create');
    }

    public function competencyStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:biicf_competencies',
            'type' => 'required|in:technical,soft_skill',
            'description' => 'nullable|string',
        ]);

        BiicfCompetency::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.biicf.competencies')
            ->with('success', 'Competency created successfully.');
    }

    public function competencyEdit(BiicfCompetency $competency)
    {
        return view('admin.biicf.competencies.edit', compact('competency'));
    }

    public function competencyUpdate(Request $request, BiicfCompetency $competency)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('biicf_competencies')->ignore($competency->id)],
            'type' => 'required|in:technical,soft_skill',
            'description' => 'nullable|string',
        ]);

        $competency->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.biicf.competencies')
            ->with('success', 'Competency updated successfully.');
    }

    public function competencyDestroy(BiicfCompetency $competency)
    {
        $competency->delete();
        return redirect()->route('admin.biicf.competencies')
            ->with('success', 'Competency deleted successfully.');
    }

    // ============================================
    // PROFICIENCY LEVELS
    // ============================================
    public function proficiencyLevels()
    {
        $levels = BiicfProficiencyLevel::orderBy('level_number')->paginate(10);
        return view('admin.biicf.proficiency-levels.index', compact('levels'));
    }

    public function proficiencyLevelCreate()
    {
        return view('admin.biicf.proficiency-levels.create');
    }

    public function proficiencyLevelStore(Request $request)
    {
        $request->validate([
            'level_number' => 'required|integer|min:1|max:5|unique:biicf_proficiency_levels',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        BiicfProficiencyLevel::create($request->all());

        return redirect()->route('admin.biicf.proficiency-levels')
            ->with('success', 'Proficiency level created successfully.');
    }

    public function proficiencyLevelEdit(BiicfProficiencyLevel $level)
    {
        return view('admin.biicf.proficiency-levels.edit', compact('level'));
    }

    public function proficiencyLevelUpdate(Request $request, BiicfProficiencyLevel $level)
    {
        $request->validate([
            'level_number' => ['required', 'integer', 'min:1', 'max:5', Rule::unique('biicf_proficiency_levels')->ignore($level->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $level->update($request->all());

        return redirect()->route('admin.biicf.proficiency-levels')
            ->with('success', 'Proficiency level updated successfully.');
    }

    public function proficiencyLevelDestroy(BiicfProficiencyLevel $level)
    {
        $level->delete();
        return redirect()->route('admin.biicf.proficiency-levels')
            ->with('success', 'Proficiency level deleted successfully.');
    }

    // ============================================
    // TRAININGS
    // ============================================
    public function trainings(Request $request)
    {
        $query = BiicfTraining::query();
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('provider', 'LIKE', "%{$search}%");
        }
        
        $trainings = $query->orderBy('name')->paginate(15);
        
        return view('admin.biicf.trainings.index', compact('trainings'));
    }

    public function trainingCreate()
    {
        return view('admin.biicf.trainings.create');
    }

    public function trainingStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'certification_body' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        BiicfTraining::create($request->all());

        return redirect()->route('admin.biicf.trainings')
            ->with('success', 'Training created successfully.');
    }

    public function trainingEdit(BiicfTraining $training)
    {
        return view('admin.biicf.trainings.edit', compact('training'));
    }

    public function trainingUpdate(Request $request, BiicfTraining $training)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'certification_body' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        $training->update($request->all());

        return redirect()->route('admin.biicf.trainings')
            ->with('success', 'Training updated successfully.');
    }

    public function trainingDestroy(BiicfTraining $training)
    {
        $training->delete();
        return redirect()->route('admin.biicf.trainings')
            ->with('success', 'Training deleted successfully.');
    }
}