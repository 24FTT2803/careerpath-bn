<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganisationGroupController extends Controller
{
    public function index(): View
    {
        $groups = OrganisationGroup::query()
            ->with(['type', 'parents'])
            ->withCount('memberships')
            ->orderBy('name')
            ->get();

        return view(
            'admin.business.groups.index',
            [
                'groups' => $groups,
                'types' => $this->types(),
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.business.groups.form',
            [
                'group' => new OrganisationGroup([
                    'is_active' => true,
                ]),
                'types' => $this->types(),
                'parents' => $this->possibleParents(),
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['organisation_id'] = $this
            ->defaultOrganisation()
            ->id;

        $parentId = $validated['parent_id'] ?? null;
        unset($validated['parent_id']);

        $group = OrganisationGroup::create(
            $validated + ['is_active' => true]
        );

        $this->syncPrimaryParent($group, $parentId);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group created.');
    }

    public function edit(OrganisationGroup $group): View
    {
        return view(
            'admin.business.groups.form',
            [
                'group' => $group,
                'types' => $this->types(),
                'parents' => $this->possibleParents($group),
            ]
        );
    }

    public function update(
        Request $request,
        OrganisationGroup $group
    ) {
        $validated = $this->validated($request, $group);

        $parentId = $validated['parent_id'] ?? null;
        unset($validated['parent_id']);

        $group->update(
            $validated + [
                'is_active' => $request->boolean('is_active'),
            ]
        );

        $this->syncPrimaryParent($group, $parentId);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Group updated.');
    }

    /**
     * Archive a group rather than deleting it.
     *
     * Students belong to these, and past recommendations and
     * sponsorships refer to them. Removing one outright would
     * cut those ties, so an unused group is simply switched off.
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
     * @return array<string, mixed>
     */
    private function validated(
        Request $request,
        ?OrganisationGroup $group = null
    ): array {
        $organisationId = $group?->organisation_id
            ?? $this->defaultOrganisation()->id;

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:120'],

                'group_type_id' => [
                    'required',
                    'exists:organisation_group_types,id',
                ],

                /*
                 * A group cannot be its own parent, which would
                 * make the hierarchy impossible to walk.
                 */
                'parent_id' => [
                    'nullable',
                    'exists:organisation_groups,id',
                    Rule::notIn([$group?->id]),
                ],

                'code' => [
                    'nullable',
                    'string',
                    'max:40',
                    Rule::unique('organisation_groups', 'code')
                        ->where('organisation_id', $organisationId)
                        ->ignore($group?->id),
                ],
            ],
            [
                'parent_id.not_in' => 'A group cannot sit inside itself.',

                'code.unique' => 'That code is already used by another group.',
            ]
        );
    }

    /**
     * Point the group at one parent as its primary edge.
     *
     * Additional parents are managed from the tree rather than
     * from this form, so only the primary edge is touched here.
     */
    private function syncPrimaryParent(
        OrganisationGroup $group,
        ?int $parentId
    ): void {
        $group->parents()
            ->wherePivot('is_primary', true)
            ->detach();

        if ($parentId === null) {
            return;
        }

        $group->parents()->syncWithoutDetaching([
            $parentId => ['is_primary' => true],
        ]);
    }

    private function defaultOrganisation(): Organisation
    {
        return Organisation::query()
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * Groups that may be chosen as a parent.
     *
     * The group being edited is excluded along with everything
     * beneath it, since moving a group inside its own descendant
     * would detach that whole branch from the tree.
     */
    private function possibleParents(
        ?OrganisationGroup $group = null
    ) {
        $query = OrganisationGroup::query()
            ->with('type')
            ->orderBy('name');

        if ($group === null) {
            return $query->get();
        }

        $excluded = $this->descendantIds($group)
            ->push($group->id);

        return $query
            ->whereNotIn('id', $excluded)
            ->get();
    }

    /**
     * Every group beneath this one, at any depth.
     */
    private function descendantIds(OrganisationGroup $group)
    {
        $ids = collect();

        $children = $group->children()->get();

        foreach ($children as $child) {
            $ids->push($child->id);

            $ids = $ids->merge(
                $this->descendantIds($child)
            );
        }

        return $ids;
    }

    private function types()
    {
        return OrganisationGroupType::query()
            ->orderBy('name')
            ->get();
    }
}
