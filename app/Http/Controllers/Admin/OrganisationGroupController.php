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
    public function index(Request $request): View
    {
        $organisation = $this->chosenOrganisation(
            $request->integer('organisation')
        );

        /*
         * Without an organisation this is a list of institutions
         * to choose from. A single structure spanning all of
         * them would be a list nobody can read, and archived
         * groups would mix across institutions that have
         * nothing to do with each other.
         */
        if ($organisation === null) {
            return view(
                'admin.business.groups.index',
                [
                    'organisation' => null,

                    'allOrganisations' => $this->filteredOrganisations(
                        $request->string('org_status')->toString()
                    ),

                    'organisationCounts' => $this->organisationCounts(),

                    'organisationFilter' => $request
                        ->string('org_status')
                        ->toString(),
                ]
            );
        }

        $groups = $this->allGroups($organisation->id);

        $filter = $request->string('status')->toString();

        $counts = [
            'all' => $groups->count(),
            'active' => $groups->where('is_active', true)->count(),
            'archived' => $groups->where('is_active', false)->count(),
        ];

        /*
         * Filtering hides rows rather than branches, so a kept
         * child still shows the path it sits in.
         */
        $visibleIds = match ($filter) {
            'active' => $groups->where('is_active', true)
                ->pluck('id')
                ->all(),

            'archived' => $groups->where('is_active', false)
                ->pluck('id')
                ->all(),

            default => null,
        };

        return view(
            'admin.business.groups.index',
            [
                'organisation' => $organisation,
                'groups' => $groups,

                'roots' => $groups->filter(
                    fn (OrganisationGroup $group) => $group->parents->isEmpty()
                )->values(),

                'childrenByParent' => $this->childrenByParent(
                    $groups
                ),

                'blockers' => $this->blockersFor($groups),
                'reach' => $this->reachFor($groups),

                'types' => $this->types($organisation->id),
                'typeUsage' => $this->typeUsage(),

                'allOrganisations' => $this->filteredOrganisations(''),

                'moveOptions' => $this->moveOptions($groups),

                'filter' => $filter,
                'counts' => $counts,
                'visibleIds' => $visibleIds,
            ]
        );
    }

    /**
     * Every group in this organisation, labelled by path.
     *
     * @param  Collection<int, OrganisationGroup>  $groups
     * @return Collection<int, object>
     */
    private function moveOptions(Collection $groups): Collection
    {
        $keyed = $groups->keyBy('id');

        return $groups
            ->map(fn (OrganisationGroup $group) => (object) [
                'id' => $group->id,
                'path' => $this->pathFor($group, $keyed),
            ])
            ->sortBy('path')
            ->values();
    }

    /**
     * The organisation being worked in, if one was chosen.
     */
    private function chosenOrganisation(?int $chosen): ?Organisation
    {
        return $chosen
            ? Organisation::find($chosen)
            : null;
    }

    /**
     * @return array<string, int>
     */
    private function organisationCounts(): array
    {
        return [
            'all' => Organisation::count(),

            'active' => Organisation::where('is_active', true)
                ->count(),

            'archived' => Organisation::where('is_active', false)
                ->count(),
        ];
    }

    public function create(Request $request): View
    {
        $parent = $request->integer('parent') ?: null;

        /*
         * Either the parent settles which institution this
         * belongs to, or the page it was opened from does.
         */
        $organisationId = $this->organisationForParent($parent)
            ?? ($request->integer('organisation') ?: null);

        return view(
            'admin.business.groups.form',
            [
                'group' => new OrganisationGroup([
                    'is_active' => true,
                ]),
                'types' => $this->types($organisationId),

                'parents' => $this->possibleParents(
                    null,
                    $organisationId
                ),
                'currentParentIds' => [],
                'primaryParentId' => $parent,
                'organisations' => $this->organisations(),

                'lockedOrganisationId' => $organisationId,
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $parentIds = $this->parentIdsFrom($validated);

        /*
         * A group belongs to whatever its parent belongs to. A
         * group with no parent is a root, so the organisation
         * has to be chosen rather than assumed.
         */
        $organisationId = $parentIds === []
            ? (int) $validated['organisation_id']
            : OrganisationGroup::findOrFail($parentIds[0])
                ->organisation_id;

        $group = OrganisationGroup::create([
            'organisation_id' => $organisationId,

            'group_type_id' => $validated['group_type_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        $this->syncParents($group, $parentIds);

        return redirect()
            ->route('admin.business.groups.index', [
                'organisation' => $organisationId,
            ])
            ->with('success', 'Group created.');
    }

    public function edit(OrganisationGroup $group): View
    {
        $group->load('parents');

        return view(
            'admin.business.groups.form',
            [
                'group' => $group,
                'types' => $this->types($group->organisation_id),
                'parents' => $this->possibleParents(
                    $group,
                    $group->organisation_id
                ),

                'currentParentIds' => $group->parents
                    ->pluck('id')
                    ->all(),

                'primaryParentId' => $group
                    ->primaryParent()
                    ?->id,
                'organisations' => $this->organisations(),
                'lockedOrganisationId' => $group->organisation_id,
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
            ->route('admin.business.groups.index', [
                'organisation' => $group->organisation_id,
            ])
            ->with('success', 'Group updated.');
    }

    /**
     * Move a group to a different parent.
     *
     * Only the branch it was moved from changes. A group that
     * sits in two places, such as a class belonging to both a
     * programme and an intake, keeps the other edge.
     */
    public function move(Request $request, OrganisationGroup $group)
    {
        $validated = $request->validate([
            'from_parent_id' => [
                'nullable',
                'exists:organisation_groups,id',
            ],

            'to_parent_id' => [
                'nullable',
                'exists:organisation_groups,id',
            ],
        ]);

        $target = $validated['to_parent_id'] ?? null;

        $refusal = $this->moveRefusal($group, $target);

        if ($refusal !== null) {
            return $this->backToTree($group, $refusal);
        }

        $from = $validated['from_parent_id'] ?? null;

        $wasPrimary = $from === null
            || $group->parents()
                ->wherePivot('is_primary', true)
                ->whereKey($from)
                ->exists();

        if ($from !== null) {
            $group->parents()->detach($from);
        }

        if ($target !== null) {
            $group->parents()->syncWithoutDetaching([
                $target => ['is_primary' => $wasPrimary],
            ]);
        }

        return redirect()
            ->route('admin.business.groups.index', [
                'organisation' => $group->organisation_id,
            ])
            ->with('success', $group->name.' moved.');
    }

    /**
     * Why a move cannot happen, or null when it can.
     */
    private function moveRefusal(
        OrganisationGroup $group,
        ?int $target
    ): ?string {
        $isRoot = Organisation::where(
            'root_group_id',
            $group->id
        )->exists();

        if ($isRoot) {
            return $group->name
                .' is the organisation\'s own group and cannot be moved.';
        }

        if ($target === null) {
            return null;
        }

        if ($target === $group->id) {
            return 'A group cannot sit inside itself.';
        }

        $parent = OrganisationGroup::find($target);

        if ($parent === null) {
            return 'That group no longer exists.';
        }

        if ($parent->organisation_id !== $group->organisation_id) {
            return 'A group cannot move to another organisation.';
        }

        if ($this->descendantIds($group)->contains($target)) {
            return 'A group cannot sit inside one of its own groups.';
        }

        return null;
    }

    private function backToTree(
        OrganisationGroup $group,
        string $message
    ) {
        return redirect()
            ->route('admin.business.groups.index', [
                'organisation' => $group->organisation_id,
            ])
            ->withErrors(['group' => $message]);
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
            ->route('admin.business.groups.index', [
                'organisation' => $group->organisation_id,
            ])
            ->with('success', 'Group archived.');
    }

    public function restore(OrganisationGroup $group)
    {
        $group->update(['is_active' => true]);

        return redirect()
            ->route('admin.business.groups.index', [
                'organisation' => $group->organisation_id,
            ])
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

        $organisationId = $group->organisation_id;

        $group->parents()->detach();
        $group->delete();

        return redirect()
            ->route('admin.business.groups.index', [
                'organisation' => $organisationId,
            ])
            ->with('success', 'Group deleted.');
    }

    public function storeType(Request $request)
    {
        $organisation = Organisation::findOrFail(
            $request->integer('organisation_id')
        );

        $validated = $request->validate([
            'organisation_id' => [
                'required',
                'exists:organisations,id',
            ],

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
            ->route('admin.business.groups.index', [
                'organisation' => $organisation->id,
            ])
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

        $organisationId = $type->organisation_id;

        $type->delete();

        return redirect()
            ->route('admin.business.groups.index', [
                'organisation' => $organisationId,
            ])
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
            ?? $request->integer('organisation_id')
            ?: null;

        $forbidden = $group === null
            ? []
            : $this->descendantIds($group)
                ->push($group->id)
                ->all();

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:120'],

                /*
                 * Only a group with no parent needs one. Any
                 * other group takes its parent's organisation,
                 * so asking would invite a contradiction.
                 */
                'organisation_id' => [
                    'required_without:primary_parent_id',
                    'nullable',
                    'exists:organisations,id',
                ],

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
        /*
         * An organisation's root is not the tree's to remove.
         * Deleting it here would leave the institution with no
         * structure and nothing saying so.
         */
        $isRoot = Organisation::where(
            'root_group_id',
            $group->id
        )->exists();

        if ($isRoot) {
            return 'it is the organisation\'s own group';
        }

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
    private function allGroups(int $organisationId): Collection
    {
        return OrganisationGroup::query()
            ->where('organisation_id', $organisationId)
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
        ?OrganisationGroup $group = null,
        ?int $organisationId = null
    ): Collection {
        /*
         * A group never crosses institutions, so offering
         * another organisation's groups as a parent would only
         * produce a structure that cannot be saved.
         */
        $all = OrganisationGroup::query()
            ->with(['type', 'parents'])
            ->when(
                $organisationId !== null,
                fn ($query) => $query->where(
                    'organisation_id',
                    $organisationId
                )
            )
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
     * Organisations for the panel, narrowed by its own tabs.
     *
     * @return Collection<int, Organisation>
     */
    private function filteredOrganisations(string $filter): Collection
    {
        return Organisation::query()
            ->withCount(['groups', 'groupTypes'])
            ->when(
                $filter === 'active',
                fn ($query) => $query->where('is_active', true)
            )
            ->when(
                $filter === 'archived',
                fn ($query) => $query->where('is_active', false)
            )
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Organisations an administrator may put a group in.
     *
     * @return Collection<int, Organisation>
     */
    private function organisations(): Collection
    {
        return Organisation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, OrganisationGroupType>
     */
    private function types(?int $organisationId = null): Collection
    {
        return OrganisationGroupType::query()
            ->when(
                $organisationId !== null,
                fn ($query) => $query->where(
                    'organisation_id',
                    $organisationId
                )
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * The organisation a new group will belong to.
     *
     * Taken from the parent when there is one, so the type list
     * can be narrowed to that organisation's own vocabulary
     * rather than offering another institution's.
     */
    private function organisationForParent(?int $parentId): ?int
    {
        if ($parentId === null) {
            return null;
        }

        return OrganisationGroup::whereKey($parentId)
            ->value('organisation_id');
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
