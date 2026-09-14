<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganisationGroupController extends Controller
{
    public function index(): View
    {
        $groups = $this->allGroups();

        return view(
            'admin.business.groups.index',
            [
                'groups' => $groups,

                'roots' => $groups->filter(
                    fn (OrganisationGroup $group) => $group->parents->isEmpty()
                )->values(),

                'childrenByParent' => $this->childrenByParent(
                    $groups
                ),

                'blockers' => $this->blockersFor($groups),
                'reach' => $this->reachFor($groups),
                'types' => $this->types(),
                'typeUsage' => $this->typeUsage(),
            ]
        );
    }

    public function create(Request $request): View
    {
        $parent = $request->integer('parent') ?: null;

        return view(
            'admin.business.groups.form',
            [
                'group' => new OrganisationGroup([
                    'is_active' => true,
                ]),
                'types' => $this->types(),
                'parents' => $this->possibleParents(),
                'currentParentIds' => [],
                'primaryParentId' => $parent,
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $parentIds = $this->parentIdsFrom($validated);

        $group = OrganisationGroup::create([
            'organisation_id' => $this
                ->defaultOrganisation()
                ->id,

            'group_type_id' => $validated['group_type_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        $this->syncParents($group, $parentIds);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group created.');
    }

    public function edit(OrganisationGroup $group): View
    {
        $group->load('parents');

        return view(
            'admin.business.groups.form',
            [
                'group' => $group,
                'types' => $this->types(),
                'parents' => $this->possibleParents($group),

                'currentParentIds' => $group->parents
                    ->pluck('id')
                    ->all(),

                'primaryParentId' => $group
                    ->primaryParent()
                    ?->id,
            ]
        );
    }

    public function update(
        Request $request,
        OrganisationGroup $group
    ) {
        $validated = $this->validated($request, $group);

        $parentIds = $this->parentIdsFrom($validated);

        $group->update([
            'group_type_id' => $validated['group_type_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncParents($group, $parentIds);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group updated.');
    }

    /**
     * Archive a group rather than removing it.
     *
     * Suitable for a class that has finished but whose students
     * and sponsorships still refer to it.
     */
    public function archive(OrganisationGroup $group)
    {
        $group->update(['is_active' => false]);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group archived.');
    }

    public function restore(OrganisationGroup $group)
    {
        $group->update(['is_active' => true]);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group restored.');
    }

    /**
     * Remove a group entirely.
     *
     * Refused while anything depends on it. Deleting a group
     * with children would strand that whole branch, and one
     * with members would quietly drop those students out of
     * every sponsorship aimed at it.
     */
    public function destroy(OrganisationGroup $group)
    {
        $blocker = $this->blockerFor($group);

        if ($blocker !== null) {
            return redirect()
                ->route('admin.business.groups.index')
                ->withErrors([
                    'group' => $group->name
                        .' cannot be deleted because '
                        .$blocker.'.',
                ]);
        }

        $group->parents()->detach();
        $group->delete();

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group deleted.');
    }

    public function storeType(Request $request)
    {
        $organisation = $this->defaultOrganisation();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique(
                    'organisation_group_types',
                    'name'
                )->where(
                    'organisation_id',
                    $organisation->id
                ),
            ],
        ]);

        OrganisationGroupType::create([
            'organisation_id' => $organisation->id,
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Type added.');
    }

    public function destroyType(OrganisationGroupType $type)
    {
        if ($type->groups()->exists()) {
            return redirect()
                ->route('admin.business.groups.index')
                ->withErrors([
                    'type' => $type->name
                        .' is still used by at least one group.',
                ]);
        }

        $type->delete();

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Type removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(
        Request $request,
        ?OrganisationGroup $group = null
    ): array {
        $organisationId = $group?->organisation_id
            ?? $this->defaultOrganisation()->id;

        $forbidden = $group === null
            ? []
            : $this->descendantIds($group)
                ->push($group->id)
                ->all();

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:120'],

                'group_type_id' => [
                    'required',
                    'exists:organisation_group_types,id',
                ],

                /*
                 * A group may not sit inside itself or inside
                 * anything beneath it, which would cut that
                 * branch out of the structure entirely.
                 */
                'primary_parent_id' => [
                    'nullable',
                    'exists:organisation_groups,id',
                    Rule::notIn($forbidden),
                ],

                'parent_ids' => ['nullable', 'array'],

                'parent_ids.*' => [
                    'exists:organisation_groups,id',
                    Rule::notIn($forbidden),
                ],

                'code' => [
                    'nullable',
                    'string',
                    'max:40',
                    Rule::unique(
                        'organisation_groups',
                        'code'
                    )
                        ->where(
                            'organisation_id',
                            $organisationId
                        )
                        ->ignore($group?->id),
                ],
            ],
            [
                'primary_parent_id.not_in' => 'A group cannot sit inside itself or inside one of its own groups.',

                'parent_ids.*.not_in' => 'A group cannot sit inside itself or inside one of its own groups.',

                'code.unique' => 'That code is already used by another group.',
            ]
        );
    }

    /**
     * The primary parent first, then any others.
     *
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function parentIdsFrom(array $validated): array
    {
        $primary = $validated['primary_parent_id'] ?? null;

        $others = $validated['parent_ids'] ?? [];

        return array_values(
            array_unique(
                array_filter(
                    array_merge([$primary], $others)
                )
            )
        );
    }

    /**
     * @param  array<int, int>  $parentIds
     */
    private function syncParents(
        OrganisationGroup $group,
        array $parentIds
    ): void {
        $payload = [];

        foreach ($parentIds as $index => $parentId) {
            $payload[$parentId] = [
                'is_primary' => $index === 0,
            ];
        }

        $group->parents()->sync($payload);
    }

    /**
     * Why a group cannot be deleted, or null when it can.
     */
    private function blockerFor(OrganisationGroup $group): ?string
    {
        $hasChildren = DB::table('organisation_group_parents')
            ->where('parent_id', $group->id)
            ->exists();

        if ($hasChildren) {
            return 'it has groups inside it';
        }

        if ($group->memberships()->exists()) {
            return 'students belong to it';
        }

        if ($group->sponsoredAccessGrants()->exists()) {
            return 'a sponsorship points at it';
        }

        $targeted = Advertisement::where(
            'organisation_group_id',
            $group->id
        )->exists();

        if ($targeted) {
            return 'an advertisement targets it';
        }

        return null;
    }

    /**
     * Students reachable through each group, counting everyone
     * inside it.
     *
     * The tree used to show direct members only, which
     * disagreed with what advertising said the same group
     * reached.
     *
     * @param  Collection<int, OrganisationGroup>  $groups
     * @return array<int, int>
     */
    private function reachFor(Collection $groups): array
    {
        $reach = [];

        foreach ($groups as $group) {
            $ids = $this->descendantIds($group)
                ->push($group->id)
                ->all();

            $reach[$group->id] = DB::table('group_memberships')
                ->whereIn('organisation_group_id', $ids)
                ->distinct()
                ->count('user_id');
        }

        return $reach;
    }

    /**
     * @param  Collection<int, OrganisationGroup>  $groups
     * @return array<int, string|null>
     */
    private function blockersFor(Collection $groups): array
    {
        $blockers = [];

        foreach ($groups as $group) {
            $blockers[$group->id] = $this->blockerFor($group);
        }

        return $blockers;
    }

    /**
     * @return Collection<int, OrganisationGroup>
     */
    private function allGroups(): Collection
    {
        return OrganisationGroup::query()
            ->with(['type', 'parents'])
            ->withCount('memberships')
            ->orderBy('name')
            ->get();
    }

    /**
     * Children keyed by their parent, so the tree renders
     * without a query per branch.
     *
     * @param  Collection<int, OrganisationGroup>  $groups
     * @return array<int, array<int, OrganisationGroup>>
     */
    private function childrenByParent(Collection $groups): array
    {
        $map = [];

        foreach ($groups as $group) {
            foreach ($group->parents as $parent) {
                $map[$parent->id][] = $group;
            }
        }

        return $map;
    }

    private function defaultOrganisation(): Organisation
    {
        return Organisation::query()
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * Groups that may be chosen as a parent, each labelled with
     * its full path.
     *
     * A bare name is ambiguous once there is a January in every
     * intake, so an option reads as its whole route through the
     * structure.
     *
     * @return Collection<int, object>
     */
    private function possibleParents(
        ?OrganisationGroup $group = null
    ): Collection {
        $all = OrganisationGroup::query()
            ->with(['type', 'parents'])
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $excluded = $group === null
            ? collect()
            : $this->descendantIds($group)->push($group->id);

        return $all
            ->reject(
                fn (OrganisationGroup $candidate) => $excluded->contains($candidate->id)
            )
            ->map(fn (OrganisationGroup $candidate) => (object) [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'type' => $candidate->type?->name,
                'path' => $this->pathFor($candidate, $all),
            ])
            ->sortBy('path')
            ->values();
    }

    /**
     * Build a group's path from the already loaded set.
     *
     * @param  Collection<int, OrganisationGroup>  $all
     */
    private function pathFor(
        OrganisationGroup $group,
        Collection $all
    ): string {
        $names = [$group->name];
        $seen = [$group->id => true];
        $current = $group;

        while (true) {
            $parent = $current->parents
                ->firstWhere('pivot.is_primary', true)
                ?? $current->parents->first();

            if ($parent === null || isset($seen[$parent->id])) {
                break;
            }

            $seen[$parent->id] = true;
            array_unshift($names, $parent->name);

            $current = $all->get($parent->id) ?? $parent;
        }

        return implode(' › ', $names);
    }

    /**
     * Every group beneath this one, through any branch.
     *
     * @return Collection<int, int>
     */
    private function descendantIds(
        OrganisationGroup $group
    ): Collection {
        $found = [];
        $pending = [$group->id];

        while ($pending !== []) {
            $currentId = array_shift($pending);

            /*
             * Read from the pivot rather than through whereHas.
             * The relation points at the same table, so Eloquent
             * aliases it and a column named organisation_groups
             * would resolve to the outer query instead of the
             * joined one, silently matching nothing.
             */
            $childIds = DB::table('organisation_group_parents')
                ->where('parent_id', $currentId)
                ->pluck('group_id');

            foreach ($childIds as $childId) {
                if (isset($found[$childId])) {
                    continue;
                }

                $found[$childId] = true;
                $pending[] = $childId;
            }
        }

        return collect(array_keys($found));
    }

    /**
     * @return Collection<int, OrganisationGroupType>
     */
    private function types(): Collection
    {
        return OrganisationGroupType::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * How many groups use each type, so one that is in use can
     * be shown as undeletable rather than failing on click.
     *
     * @return array<int, int>
     */
    private function typeUsage(): array
    {
        return OrganisationGroupType::query()
            ->withCount('groups')
            ->pluck('groups_count', 'id')
            ->all();
    }
}
