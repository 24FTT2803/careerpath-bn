<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSponsor;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SponsorshipController extends Controller
{
    public function index(): View
    {
        return view(
            'admin.business.sponsorship.index',
            [
                'sponsors' => BusinessSponsor::query()
                    ->withCount('sponsoredAccessGrants')
                    ->orderBy('name')
                    ->get(),

                'grants' => SponsoredAccessGrant::query()
                    ->with(['sponsor', 'plan', 'organisationGroup'])
                    ->orderByDesc('is_active')
                    ->orderByDesc('priority')
                    ->orderByDesc('id')
                    ->get(),

                'activeSponsors' => BusinessSponsor::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'plans' => Plan::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'groupOptions' => $this->groupOptions(),
            ]
        );
    }

    public function storeSponsor(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],

            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('business_sponsors', 'code'),
            ],
        ]);

        BusinessSponsor::create(
            $validated + ['is_active' => true]
        );

        return redirect()
            ->route('admin.business.sponsorship.index')
            ->with('success', 'Sponsor added.');
    }

    /**
     * Switch a sponsor off without removing it.
     *
     * Their grants stay on record, and the entitlement layer
     * already ignores grants belonging to an inactive sponsor,
     * so this suspends funding rather than erasing its history.
     */
    public function toggleSponsor(BusinessSponsor $sponsor)
    {
        $sponsor->update([
            'is_active' => ! $sponsor->is_active,
        ]);

        return redirect()
            ->route('admin.business.sponsorship.index')
            ->with(
                'success',
                $sponsor->is_active
                    ? 'Sponsor reactivated.'
                    : 'Sponsor suspended.'
            );
    }

    public function destroySponsor(BusinessSponsor $sponsor)
    {
        if ($sponsor->sponsoredAccessGrants()->exists()) {
            return redirect()
                ->route('admin.business.sponsorship.index')
                ->withErrors([
                    'sponsor' => $sponsor->name
                        .' cannot be deleted because they still fund access.'
                        .' Suspend them instead.',
                ]);
        }

        $sponsor->delete();

        return redirect()
            ->route('admin.business.sponsorship.index')
            ->with('success', 'Sponsor deleted.');
    }

    public function storeGrant(Request $request)
    {
        $validated = $request->validate(
            [
                'business_sponsor_id' => [
                    'required',
                    'exists:business_sponsors,id',
                ],

                'plan_id' => ['required', 'exists:plans,id'],

                'organisation_group_id' => [
                    'nullable',
                    'exists:organisation_groups,id',
                ],

                'priority' => [
                    'nullable',
                    'integer',
                    'between:0,100',
                ],

                'starts_at' => ['nullable', 'date'],

                'ends_at' => [
                    'nullable',
                    'date',
                    'after_or_equal:starts_at',
                ],
            ],
            [
                'ends_at.after_or_equal' => 'The end date must not be before the start date.',
            ]
        );

        $groupId = $validated['organisation_group_id'] ?? null;

        /*
         * A grant aimed at a group belongs to that group's
         * organisation. Without a group it covers the whole
         * institution.
         */
        $organisationId = $groupId === null
            ? $this->defaultOrganisation()->id
            : OrganisationGroup::findOrFail($groupId)
                ->organisation_id;

        SponsoredAccessGrant::create(
            $this->withWindow($validated) + [
                'organisation_id' => $organisationId,
                'priority' => $validated['priority'] ?? 0,
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('admin.business.sponsorship.index')
            ->with('success', 'Sponsored access added.');
    }

    public function revokeGrant(SponsoredAccessGrant $grant)
    {
        $grant->update([
            'is_active' => false,
            'ends_at' => $grant->ends_at ?? now(),
        ]);

        return redirect()
            ->route('admin.business.sponsorship.index')
            ->with('success', 'Sponsored access withdrawn.');
    }

    /**
     * Read the dates as local days.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withWindow(array $validated): array
    {
        $zone = config('app.business_timezone');
        $storage = config('app.timezone');

        if (! empty($validated['starts_at'])) {
            $validated['starts_at'] = Carbon::parse(
                $validated['starts_at'],
                $zone
            )
                ->startOfDay()
                ->setTimezone($storage);
        }

        if (! empty($validated['ends_at'])) {
            $validated['ends_at'] = Carbon::parse(
                $validated['ends_at'],
                $zone
            )
                ->endOfDay()
                ->setTimezone($storage);
        }

        return $validated;
    }

    private function defaultOrganisation(): Organisation
    {
        return Organisation::query()
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * Groups labelled with their full path.
     *
     * A sponsorship aimed at "January" is meaningless once every
     * intake has one, so each option reads as its whole route.
     *
     * @return Collection<int, object>
     */
    private function groupOptions(): Collection
    {
        $all = OrganisationGroup::query()
            ->with('parents')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return $all
            ->map(fn (OrganisationGroup $group) => (object) [
                'id' => $group->id,
                'path' => $this->pathFor($group, $all),
            ])
            ->sortBy('path')
            ->values();
    }

    /**
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
}
