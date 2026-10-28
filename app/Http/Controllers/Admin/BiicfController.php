<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiicfCompetency;
use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobLevel;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Services\Biicf\CompetencyDescriptorUpdater;
use App\Services\Biicf\JobRoleContentUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            ->get();

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
    /**
     * Every job role, for a listing that sorts and pages itself.
     *
     * Sent whole rather than a page at a time. There are
     * thirty-eight of them, and server paging meant each page of
     * results was a round trip that reloaded the screen and lost
     * whatever sorting and filtering had been set.
     *
     * The job levels come with them, because a role is held at
     * one or more of the framework's levels and that is what an
     * administrator looks for — not the career path number the
     * Level column used to show.
     */
    public function jobRoles()
    {
        $jobRoles = BiicfJobRole::with(['subSector:id,name', 'tiers.jobLevel:id,name,sort_order'])
            ->orderBy('title')
            ->get();

        $subSectors = BiicfSubSector::orderBy('name')->get();
        $jobLevels = BiicfJobLevel::orderBy('sort_order')->get();

        return view('admin.biicf.job-roles.index', compact('jobRoles', 'subSectors', 'jobLevels'));
    }

    public function jobRoleCreate()
    {
        $subSectors = BiicfSubSector::orderBy('name')->get();
        $competencies = BiicfCompetency::orderBy('name')->get();
        $proficiencyLevels = BiicfProficiencyLevel::orderBy('level_number')->get();
        $trainings = BiicfTraining::orderBy('name')->get();

        /*
         * Nothing is stored yet, so only a resubmitted form has
         * anything to restore.
         */
        $candidateRoles = $this->careerPathCandidates();
        $selectedFrom = $this->chosenRoleIds('progresses_from');
        $selectedTo = $this->chosenRoleIds('progresses_to');

        return view('admin.biicf.job-roles.create', compact(
            'subSectors',
            'competencies',
            'proficiencyLevels',
            'trainings',
            'candidateRoles',
            'selectedFrom',
            'selectedTo'
        ));
    }

    /**
     * The roles a career path may be drawn to or from.
     *
     * Everything except the role being edited, which cannot
     * progress into itself.
     */
    private function careerPathCandidates(
        ?BiicfJobRole $except = null
    ): Collection {
        return BiicfJobRole::query()
            ->with('subSector:id,name')
            ->when(
                $except !== null,
                fn ($query) => $query->whereKeyNot($except->id)
            )
            ->orderBy('title')
            ->get(['id', 'sub_sector_id', 'title', 'career_path_level']);
    }

    /**
     * Role ids posted back after a failed validation pass.
     *
     * @return array<int, int>
     */
    private function chosenRoleIds(string $field): array
    {
        return collect(old($field, []))
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Write this role's progression edges, both directions.
     *
     * Edges unchanged by this save are left alone rather than
     * resynced, because the pivot carries a note — how long the
     * step usually takes — that this form has no field for and
     * must not quietly discard.
     *
     * The role's own id is dropped from both lists: a self-edge
     * would draw a role as its own next step, and the unique key
     * would not catch it.
     */
    private function syncCareerPath(
        BiicfJobRole $jobRole,
        Request $request
    ): void {
        foreach ([
            'progresses_from' => 'progressesFrom',
            'progresses_to' => 'progressesTo',
        ] as $field => $relation) {
            $wanted = collect($request->input($field, []))
                ->map(fn ($id) => (int) $id)
                ->reject(fn (int $id) => $id === $jobRole->id)
                ->unique()
                ->values();

            $existing = $jobRole->{$relation}()
                ->pluck('biicf_job_roles.id');

            $jobRole->{$relation}()->detach(
                $existing->diff($wanted)->all()
            );

            $jobRole->{$relation}()->attach(
                $wanted->diff($existing)->all()
            );
        }
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

            /*
             * A level per tier, since the book gives a role one
             * column per tier. The create form has no tiers to
             * name yet and sends a single level instead.
             *
             * A tier left empty is "not required there", which is
             * what an empty cell in the book's table says — so it
             * is skipped rather than refused. What is refused is a
             * level from the wrong ladder: Advanced is the third
             * of a soft skill's four rungs and means nothing on a
             * technical competency's five.
             */
            $wanted = array_key_exists('levels', $selection)
                ? (array) $selection['levels']
                : [$selection['proficiency_level_id'] ?? null];

            $given = 0;

            foreach ($wanted as $levelId) {
                if (blank($levelId)) {
                    continue;
                }

                $level = $levels->get((int) $levelId);

                if (! $level || $level->scale !== $competency->scale()) {
                    throw ValidationException::withMessages([
                        'competencies' => $competency->name
                            .' needs a proficiency level from its own scale.',
                    ]);
                }

                $given++;
            }

            if ($given === 0) {
                throw ValidationException::withMessages([
                    'competencies' => $competency->name
                        .' is ticked but asked for at no level. Choose one, or untick it.',
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
            'work_functions' => 'nullable|json',
            'programming_languages' => 'nullable|string',
            'alternative_titles' => 'nullable|array',
            'career_path_level' => 'nullable|integer|min:0',
            'competencies' => 'nullable|array',
            'competencies.*.id' => 'exists:biicf_competencies,id',
            'competencies.*.proficiency_level_id' => 'nullable|exists:biicf_proficiency_levels,id',
            'competencies.*.levels' => 'nullable|array',
            'competencies.*.levels.*' => 'nullable|exists:biicf_proficiency_levels,id',
            'competencies.*.is_core' => 'boolean',
            'trainings' => 'nullable|array',
            'trainings.*' => 'exists:biicf_trainings,id',
            'progresses_from' => 'nullable|array',
            'progresses_from.*' => 'integer|exists:biicf_job_roles,id',
            'progresses_to' => 'nullable|array',
            'progresses_to.*' => 'integer|exists:biicf_job_roles,id',
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
        BiicfJobRoleTier::create([
            'job_role_id' => $jobRole->id,
            'label' => 'All levels',
            'sort_order' => 1,
        ]);

        $this->syncCompetencyTable($jobRole, $request);

        $this->syncCareerPath($jobRole, $request);

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

        /*
         * The tree if the form drew one, the old textarea
         * otherwise. The two cannot both be sent: the tree holds
         * every duty the role has, and the textarea only the
         * generic ones, so running both would write the role's
         * duties twice over from two different readings.
         */
        if ($request->filled('work_functions')) {
            $updater->replaceWorkFunctions(
                $jobRole,
                json_decode($request->input('work_functions'), true) ?: []
            );
        } elseif ($request->has('critical_work_function')) {
            $updater->replaceGenericWorkFunctions(
                $jobRole,
                $request->input('critical_work_function')
            );
        }

        if ($request->has('programming_languages')) {
            $updater->replaceProgrammingLanguages(
                $jobRole,
                $request->input('programming_languages')
            );
        }
    }

    /**
     * Write the role's competency table, a column per tier.
     *
     * The book prints one column for each of a role's tiers, and
     * the same competency can be wanted at a different level in
     * each — Application Development at 1, 2 and 2 across IT
     * Technician's three levels. The form used to offer the entry
     * tier alone, so the other columns could be read on the
     * role's page and corrected nowhere.
     *
     * A tier left at "not required" gets no row, which is what an
     * empty cell in the book's table says.
     */
    private function syncCompetencyTable(BiicfJobRole $jobRole, Request $request): void
    {
        /*
         * Only when the form carries it. A request that says
         * nothing about competencies is editing something else,
         * and reading silence as an empty table would strip the
         * role of every proficiency the book gives it.
         */
        if (! $request->has('competencies')) {
            return;
        }

        $tiers = $jobRole->tiers()->get()->keyBy('id');

        if ($tiers->isEmpty()) {
            return;
        }

        foreach ($tiers as $tier) {
            $tier->competencies()->detach();
        }

        foreach ($request->input('competencies', []) as $selection) {
            if (! is_array($selection) || ! isset($selection['id'])) {
                continue;
            }

            $core = (bool) ($selection['is_core'] ?? true);

            /*
             * A form with no tier columns — the create page,
             * where the role's single tier does not exist yet —
             * sends one level for the role instead.
             */
            $levels = $selection['levels']
                ?? [$tiers->keys()->first() => $selection['proficiency_level_id'] ?? null];

            foreach ($levels as $tierId => $levelId) {
                if (blank($levelId) || ! $tiers->has((int) $tierId)) {
                    continue;
                }

                $tiers->get((int) $tierId)->competencies()->attach($selection['id'], [
                    'proficiency_level_id' => $levelId,
                    'is_core' => $core,
                ]);
            }
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
            'progressesFrom:id,title',
            'progressesTo:id,title',
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
            'programmingLanguages',
            /*
             * Every duty, nested as it is stored, and both kinds
             * of it. The form used to be given the generic ones
             * alone, which is why deleting a duplicate there left
             * its copies standing: they were nested, or they
             * belonged to a job family, and either way the form
             * could not see them.
             */
            'criticalWorkFunctions.children.children',
            'tiers.competencies',
        ]);

        /*
         * The role's own families, since a family-specific duty
         * belongs to one of them. Offering every family in the
         * framework would invite filing a duty under a family the
         * role is not practised in.
         */
        $jobFamilies = $jobRole->jobFamilies;

        $workFunctionTree = $this->workFunctionTree($jobRole);

        $candidateRoles = $this->careerPathCandidates($jobRole);

        /*
         * What is stored, unless a failed save is being shown
         * back — in which case the admin's own ticks are, so a
         * validation error elsewhere on the form does not throw
         * away the progression they just set.
         *
         * Decided by the form's own marker rather than by whether
         * the lists came back. An admin who clears every tick
         * submits neither field at all, and reading that as
         * "nothing posted" would restore the edges they had just
         * removed.
         */
        $resubmitted = old('career_path_submitted') !== null;

        $selectedFrom = $resubmitted
            ? $this->chosenRoleIds('progresses_from')
            : $jobRole->progressesFrom()->pluck('biicf_job_roles.id')->all();

        $selectedTo = $resubmitted
            ? $this->chosenRoleIds('progresses_to')
            : $jobRole->progressesTo()->pluck('biicf_job_roles.id')->all();

        return view('admin.biicf.job-roles.edit', compact(
            'jobRole',
            'subSectors',
            'competencies',
            'proficiencyLevels',
            'trainings',
            'jobFamilies',
            'workFunctionTree',
            'candidateRoles',
            'selectedFrom',
            'selectedTo'
        ));
    }

    /**
     * A role's duties as a tree, for the form to draw.
     *
     * Starting from the rows that sit at the top of a list and
     * going down through their children, rather than handing over
     * a flat collection: the nesting is the thing being edited.
     *
     * @return array<int, array<string, mixed>>
     */
    private function workFunctionTree(BiicfJobRole $jobRole): array
    {
        return $this->workFunctionBranch(
            $jobRole->criticalWorkFunctions->whereNull('parent_id')
        );
    }

    /**
     * @param  Collection<int, BiicfCriticalWorkFunction>  $functions
     * @return array<int, array<string, mixed>>
     */
    private function workFunctionBranch($functions): array
    {
        return $functions
            ->map(fn ($function) => [
                'text' => $function->text,
                'kind' => $function->kind,
                'job_family_id' => $function->job_family_id,
                /*
                 * Only as deep as was loaded. The framework goes
                 * three levels and the form writes three, so a
                 * fourth would be something this page cannot show
                 * — better that it is not quietly half-read.
                 */
                'children' => $function->relationLoaded('children')
                    ? $this->workFunctionBranch($function->children)
                    : [],
            ])
            ->values()
            ->all();
    }

    public function jobRoleUpdate(Request $request, BiicfJobRole $jobRole)
    {
        $request->validate([
            'sub_sector_id' => 'required|exists:biicf_sub_sectors,id',
            'title' => ['required', 'string', 'max:255', Rule::unique('biicf_job_roles')->ignore($jobRole->id)],
            'functional_group' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'critical_work_function' => 'nullable|string',
            'work_functions' => 'nullable|json',
            'programming_languages' => 'nullable|string',
            'alternative_titles' => 'nullable|array',
            'career_path_level' => 'nullable|integer|min:0',
            'competencies' => 'nullable|array',
            'competencies.*.id' => 'exists:biicf_competencies,id',
            'competencies.*.proficiency_level_id' => 'nullable|exists:biicf_proficiency_levels,id',
            'competencies.*.levels' => 'nullable|array',
            'competencies.*.levels.*' => 'nullable|exists:biicf_proficiency_levels,id',
            'competencies.*.is_core' => 'boolean',
            'trainings' => 'nullable|array',
            'trainings.*' => 'exists:biicf_trainings,id',
            'progresses_from' => 'nullable|array',
            'progresses_from.*' => 'integer|exists:biicf_job_roles,id',
            'progresses_to' => 'nullable|array',
            'progresses_to.*' => 'integer|exists:biicf_job_roles,id',
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

        /*
         * A role has to have a tier: entry requirements and
         * required proficiencies hang off one. A role from the
         * book brings its own.
         */
        if ($jobRole->tiers()->doesntExist()) {
            BiicfJobRoleTier::create([
                'job_role_id' => $jobRole->id,
                'label' => 'All levels',
                'sort_order' => 1,
            ]);
        }

        $this->syncCompetencyTable($jobRole, $request);

        $this->syncCareerPath($jobRole, $request);

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
    /**
     * The whole glossary, for a listing that sorts and pages
     * itself.
     *
     * A hundred and thirty-nine rows, which is small enough to
     * send once and large enough that paging it on the server —
     * reloading the screen and dropping the sort each time — was
     * the worst of both.
     */
    public function competencies()
    {
        $competencies = BiicfCompetency::withCount('levelDescriptors')
            ->orderBy('name')
            ->get();

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
            'short_definition' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        BiicfCompetency::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'short_definition' => $request->short_definition,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.biicf.competencies')
            ->with('success', 'Competency created successfully.');
    }

    /**
     * One competency, in full.
     *
     * The listing could only ever show a name and a type, so
     * until this page there was nowhere to read what the book
     * says a competency means at each of its levels, or to see
     * which roles ask for it.
     */
    public function competencyShow(BiicfCompetency $competency)
    {
        $competency->load([
            'levelDescriptors.proficiencyLevel',
            'levelDescriptors.jobRoles:id,title,slug',
        ]);

        return view('admin.biicf.competencies.show', [
            'competency' => $competency,
            'scale' => $this->scaleFor($competency),
            'sets' => $this->descriptorSets($competency),
            'requiredBy' => $this->rolesRequiring($competency),
        ]);
    }

    public function competencyEdit(BiicfCompetency $competency)
    {
        $competency->load('levelDescriptors');

        return view('admin.biicf.competencies.edit', [
            'competency' => $competency,
            'scale' => $this->scaleFor($competency),
            'sets' => $this->descriptorSets($competency),
        ]);
    }

    public function competencyUpdate(Request $request, BiicfCompetency $competency)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('biicf_competencies')->ignore($competency->id)],
            'type' => 'required|in:technical,soft_skill',
            'short_definition' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'descriptors' => 'nullable|array',
            'descriptors.*.scope' => 'nullable|string|max:255',
            'descriptors.*.levels' => 'nullable|array',
            'descriptors.*.levels.*' => 'nullable|string',
        ]);

        $competency->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'short_definition' => $request->short_definition,
            'description' => $request->description,
        ]);

        /*
         * Only when the form carries them. A request without the
         * field is editing something else, and reading that as
         * "every level is now empty" would delete the book's
         * statements for the whole competency.
         */
        if ($request->has('descriptors')) {
            (new CompetencyDescriptorUpdater)->write(
                $competency,
                $request->input('descriptors')
            );
        }

        return redirect()->route('admin.biicf.competencies.show', $competency)
            ->with('success', 'Competency updated successfully.');
    }

    /**
     * The rungs this competency is rated on.
     *
     * Five for a technical competency, four for a soft skill.
     * Reading one scale for both is what once showed a soft
     * skill as needing a fifth level that does not exist.
     *
     * @return Collection<int, BiicfProficiencyLevel>
     */
    private function scaleFor(BiicfCompetency $competency)
    {
        return BiicfProficiencyLevel::where('scale', $competency->scale())
            ->orderBy('level_number')
            ->get();
    }

    /**
     * A competency's descriptors, grouped by the scope they
     * apply to, with a row for every rung of the scale.
     *
     * The general set comes first, then any that name the roles
     * they cover. A rung with no descriptor is kept in the list
     * and marked: the book's N/A is an absence, and the form has
     * to be able to show it as one.
     *
     * @return array<int, array<string, mixed>>
     */
    private function descriptorSets(BiicfCompetency $competency): array
    {
        $scale = $this->scaleFor($competency);

        $sets = $competency->levelDescriptors
            ->groupBy(fn ($descriptor) => $descriptor->scope_label ?? '')
            ->sortKeys()
            ->map(fn ($descriptors, $scope) => [
                'scope' => $scope,
                'job_roles' => $descriptors->first()?->jobRoles ?? collect(),
                'levels' => $scale->map(fn ($level) => [
                    'level' => $level,
                    'descriptor' => $descriptors->firstWhere('proficiency_level_id', $level->id),
                ])->all(),
            ])
            ->values()
            ->all();

        if ($sets === []) {
            $sets = [[
                'scope' => '',
                'job_roles' => collect(),
                'levels' => $scale->map(fn ($level) => [
                    'level' => $level,
                    'descriptor' => null,
                ])->all(),
            ]];
        }

        return $sets;
    }

    /**
     * Which roles ask for this competency, and at what level.
     *
     * Through the tier, because that is where the proficiency
     * lives: IT Technician wants Application Development at 1,
     * 2 and 2 across its three levels.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rolesRequiring(BiicfCompetency $competency): array
    {
        $levels = BiicfProficiencyLevel::all()->keyBy('id');

        $tiers = BiicfJobRoleTier::with(['jobRole:id,title,sub_sector_id', 'jobRole.subSector:id,name'])
            ->get()
            ->keyBy('id');

        /*
         * Read off the pivot rather than through the relation: a
         * competency the whole framework asks for appears on
         * eight hundred rows, and loading each tier's full
         * competency list to find one of them is a query per
         * role.
         */
        return DB::table('biicf_job_role_competency')
            ->where('competency_id', $competency->id)
            ->whereNotNull('job_role_tier_id')
            ->get(['job_role_tier_id', 'proficiency_level_id'])
            ->map(fn ($row) => [
                'role' => $tiers->get($row->job_role_tier_id)?->jobRole,
                'tier' => $tiers->get($row->job_role_tier_id)?->label,
                'level' => $levels->get($row->proficiency_level_id),
            ])
            ->filter(fn ($row) => $row['role'] !== null)
            ->sortBy(fn ($row) => $row['role']->title.' '.$row['tier'])
            ->values()
            ->all();
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
    /**
     * Every recommended course, with how many roles ask for it.
     *
     * Sent whole, like the other listings, so sorting and
     * searching cost nothing and survive paging.
     */
    public function trainings()
    {
        $trainings = BiicfTraining::withCount('jobRoles')
            ->orderBy('name')
            ->get();

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
