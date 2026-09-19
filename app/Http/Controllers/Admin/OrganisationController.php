<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrganisationController extends Controller
{
    /**
     * The type a newly created organisation's root group uses.
     */
    private const ROOT_TYPE = 'Institution';

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('organisations', 'code'),
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $organisation = Organisation::create(
                $validated + ['is_active' => true]
            );

            $this->createRootGroup($organisation);
        });

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Organisation added.');
    }

    public function update(
        Request $request,
        Organisation $organisation
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('organisations', 'code')
                    ->ignore($organisation->id),
            ],
        ]);

        $organisation->update($validated);

        /*
         * The root carries the organisation's name, so letting
         * them drift apart would reintroduce the confusion of
         * an institution appearing twice under two names.
         */
        $organisation->rootGroup?->update([
            'name' => $organisation->name,
            'code' => $organisation->code,
        ]);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Organisation updated.');
    }

    /**
     * Hide an organisation without disturbing it.
     *
     * Archiving keeps everything inside working. It only takes
     * the organisation out of the lists an administrator picks
     * from, so an institution that is no longer being set up
     * stops cluttering them.
     */
    public function archive(Organisation $organisation)
    {
        $organisation->update(['is_active' => false]);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Organisation archived.');
    }

    public function restore(Organisation $organisation)
    {
        $organisation->update(['is_active' => true]);

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Organisation restored.');
    }

    public function destroy(Organisation $organisation)
    {
        $blocker = $this->blockerFor($organisation);

        if ($blocker !== null) {
            return redirect()
                ->route('admin.business.groups.index')
                ->withErrors([
                    'organisation' => $organisation->name
                        .' cannot be deleted because '
                        .$blocker.'. Archive it instead.',
                ]);
        }

        /*
         * Order matters. Deleting the organisation cascades to
         * its types, but a group still points at one of those
         * types and that foreign key refuses, so the root group
         * has to go first.
         */
        DB::transaction(function () use ($organisation) {
            $organisation->groups()->each(
                function (OrganisationGroup $group) {
                    $group->parents()->detach();
                    $group->children()->detach();
                    $group->delete();
                }
            );

            $organisation->groupTypes()->delete();
            $organisation->delete();
        });

        return redirect()
            ->route('admin.business.groups.index')
            ->with('success', 'Organisation deleted.');
    }

    /**
     * Give a new organisation the node its structure hangs from.
     *
     * Without this the institution exists twice: once as a row
     * nobody can see, and once as whatever somebody happens to
     * create at the top of the tree.
     */
    private function createRootGroup(
        Organisation $organisation
    ): void {
        $type = OrganisationGroupType::firstOrCreate(
            [
                'organisation_id' => $organisation->id,
                'name' => self::ROOT_TYPE,
            ]
        );

        $root = OrganisationGroup::create([
            'organisation_id' => $organisation->id,
            'group_type_id' => $type->id,
            'name' => $organisation->name,
            'code' => $organisation->code,
            'is_active' => true,
        ]);

        $organisation->update(['root_group_id' => $root->id]);
    }

    /**
     * Why an organisation cannot be deleted, or null when it can.
     */
    private function blockerFor(
        Organisation $organisation
    ): ?string {
        /*
         * Its own root group does not count. Deleting an
         * organisation that was only just created should not be
         * blocked by the node created alongside it.
         */
        $realGroups = $organisation->groups()
            ->whereHas(
                'parents'
            )
            ->exists();

        $rootWithChildren = DB::table('organisation_group_parents')
            ->join(
                'organisation_groups',
                'organisation_groups.id',
                '=',
                'organisation_group_parents.parent_id'
            )
            ->where(
                'organisation_groups.organisation_id',
                $organisation->id
            )
            ->exists();

        if ($realGroups || $rootWithChildren) {
            return 'it still has groups';
        }

        if ($organisation->sponsoredAccessGrants()->exists()) {
            return 'a sponsorship points at it';
        }

        return null;
    }
}
