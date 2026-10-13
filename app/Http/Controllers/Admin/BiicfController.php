<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Services\Biicf\JobRoleContentUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'sort_order' => 'nullable|integer|min:0',
        ]);

        BiicfSubSector::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
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
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $subSector->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
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
            $query->where(function ($q) use ($search) {
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

    /**
     * Check each selected competency against the scale it is
     * actually rated on.
     *
     * The framework rates technical competencies over five
     * levels and soft skills over four, so a level borrowed from
     * the other scale is not a weaker answer but a meaningless
     * one. The form only offers the matching scale; this is what
     * holds when a request arrives without going through it.
     *
     * @param  array<int|string, mixed>  $selections
     */
    private function validateCompetencyScales(array $selections): void
    {
        $chosen = collect($selections)
            ->filter(
                fn ($selection) => is_array($selection)
                    && isset($selection['id'])
            );

        if ($chosen->isEmpty()) {
            return;
        }

        $competencies = BiicfCompetency::query()
            ->whereIn('id', $chosen->pluck('id'))
            ->get()
            ->keyBy('id');

        $levels = BiicfProficiencyLevel::all()->keyBy('id');

        foreach ($chosen as $selection) {
            $competency = $competencies->get((int) $selection['id']);

            if (! $competency) {
                continue;
            }

            $levelId = $selection['proficiency_level_id'] ?? null;
            $level = $levelId === null
                ? null
                : $levels->get((int) $levelId);

            if (! $level || $level->scale !== $competency->scale()) {
                throw ValidationException::withMessages([
                    'competencies' => $competency->name
                        .' needs a proficiency level from its own scale.',
                ]);
            }
        }
    }

    public function jobRoleStore(Request $request)
    {
        $request->validate([
            'sub_sector_id' => 'required|exists:biicf_sub_sectors,id',
            'title' => 'required|string|max:255|unique:biicf_job_roles',
            'functional_group' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'critical_work_function' => 'nullable|string',
            'programming_languages' => 'nullable|string',
            'alternative_titles' => 'nullable|array',
            'career_path_level' => 'nullable|integer|min:0',
            'competencies' => 'nullable|array',
            'competencies.*.id' => 'exists:biicf_competencies,id',
            'competencies.*.proficiency_level_id' => 'exists:biicf_proficiency_levels,id',
            'competencies.*.is_core' => 'boolean',
            'trainings' => 'nullable|array',
            'trainings.*' => 'exists:biicf_trainings,id',
        ]);

        $this->validateCompetencyScales(
            $request->input('competencies', [])
        );

        $jobRole = BiicfJobRole::create([
            'sub_sector_id' => $request->sub_sector_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'functional_group' => $request->functional_group,
            'job_description' => $request->job_description,
            'critical_work_function' => $request->critical_work_function,
            'alternative_titles' => $request->alternative_titles ?? [],
            'career_path_level' => $request->career_path_level ?? 0,
        ]);

        $this->writeStructuredContent($jobRole, $request);

        /*
         * Entry requirements and required proficiencies hang off
         * a tier, so a role without one cannot carry either.
         * Roles from the reference book bring the book's own
         * tiers; one added here gets a single tier covering it.
         */
        $tier = BiicfJobRoleTier::create([
            'job_role_id' => $jobRole->id,
            'label' => 'All levels',
            'sort_order' => 1,
        ]);

        $this->syncTierCompetencies($tier, $request);

        if ($request->has('trainings')) {
            $jobRole->trainings()->sync($request->trainings);
        }

        return redirect()->route('admin.biicf.job-roles')
            ->with('success', 'Job role created successfully.');
    }

    /**
     * Store the description and duties as rows.
     *
     * The textareas remain the way these are typed, but the rows
     * are what every page now reads. The old columns are still
     * written so the two cannot disagree; they go when the
     * edition import replaces the seeder that still feeds them.
     */
    private function writeStructuredContent(BiicfJobRole $jobRole, Request $request): void
    {
        $updater = new JobRoleContentUpdater;

        $updater->replaceDescription(
            $jobRole,
            $request->input('job_description')
        );

        $updater->replaceGenericWorkFunctions(
            $jobRole,
            $request->input('critical_work_function')
        );

        if ($request->has('programming_languages')) {
            $updater->replaceProgrammingLanguages(
                $jobRole,
                $request->input('programming_languages')
            );
        }
    }

    /**
     * Replace one tier's column of required proficiencies.
     *
     * Competencies belong to a tier, not to the role, so
     * attaching through the role would leave rows with no tier
     * and they would show up nowhere. Only this tier's column is
     * replaced: a role carrying the book's several tiers must
     * not lose the others because one was edited here.
     */
    private function syncTierCompetencies(
        BiicfJobRoleTier $tier,
        Request $request
    ): void {
        $tier->competencies()->detach();

        foreach ($request->input('competencies', []) as $selection) {
            /*
             * The proficiency select always submits, even when
             * the competency checkbox is unticked, so an entry
             * can arrive without its id.
             */
            if (! is_array($selection) || ! isset($selection['id'])) {
                continue;
            }

            $tier->competencies()->attach($selection['id'], [
                'proficiency_level_id' => $selection['proficiency_level_id'],
                'is_core' => $selection['is_core'] ?? true,
            ]);
        }
    }

    public function jobRoleShow(BiicfJobRole $jobRole)
    {
        $jobRole->load([
            'subSector',
            'trainings',
            'descriptionPoints',
            /*
             * Three levels deep: the book groups duties under
             * headings of its own and nests a further level
             * under some of them.
             */
            'genericWorkFunctions.children.children',
            'specificWorkFunctions.jobFamily',
            'specificWorkFunctions.children.children',
            'tiers.competencies',
            'tiers.entryRequirements',
        ]);

        /*
         * The pivot carries a proficiency level id and there is
         * no pivot class to resolve it, which is why this badge
         * used to read "Level ?". A lookup is cheaper than a
         * custom pivot for one label.
         *
         * The label is the level's own name rather than its
         * number: the two scales number their rungs separately,
         * so a soft skill shown as "Level 3" would read as a
         * technical rung. The book calls it Advanced.
         */
        $levelLabels = BiicfProficiencyLevel::query()
            ->get()
            ->mapWithKeys(fn (BiicfProficiencyLevel $level) => [
                $level->id => $level->level_number.' · '.$level->name,
            ]);

        return view('admin.biicf.job-roles.show', compact('jobRole', 'levelLabels'));
    }

    public function jobRoleEdit(BiicfJobRole $jobRole)
    {
        $subSectors = BiicfSubSector::orderBy('name')->get();
        $competencies = BiicfCompetency::orderBy('name')->get();
        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get();
        $trainings = BiicfTraining::orderBy('name')->get();

        /*
         * The form's two textareas are prefilled from the
         * description and duty rows, not the old text columns, so
         * editing a role whose content came from the reference
         * book shows what is actually stored.
         */
        $jobRole->load([
            'trainings',
            'descriptionPoints',
            'genericWorkFunctions',
            'tiers.competencies',
        ]);

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
            'programming_languages' => 'nullable|string',
            'alternative_titles' => 'nullable|array',
            'career_path_level' => 'nullable|integer|min:0',
            'competencies' => 'nullable|array',
            'competencies.*.id' => 'exists:biicf_competencies,id',
            'competencies.*.proficiency_level_id' => 'exists:biicf_proficiency_levels,id',
            'competencies.*.is_core' => 'boolean',
            'trainings' => 'nullable|array',
            'trainings.*' => 'exists:biicf_trainings,id',
        ]);

        $this->validateCompetencyScales(
            $request->input('competencies', [])
        );

        $jobRole->update([
            'sub_sector_id' => $request->sub_sector_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'functional_group' => $request->functional_group,
            'job_description' => $request->job_description,
            'critical_work_function' => $request->critical_work_function,
            'alternative_titles' => $request->alternative_titles ?? [],
            'career_path_level' => $request->career_path_level ?? 0,
        ]);

        $this->writeStructuredContent($jobRole, $request);

        $tier = $jobRole->tiers()->first()
            ?? BiicfJobRoleTier::create([
                'job_role_id' => $jobRole->id,
                'label' => 'All levels',
                'sort_order' => 1,
            ]);

        $this->syncTierCompetencies($tier, $request);

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
    // TRAININGS
    // ============================================
    public function trainings(Request $request)
    {
        $query = BiicfTraining::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
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
