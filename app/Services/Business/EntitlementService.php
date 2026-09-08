<?php

namespace App\Services\Business;

use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;

class EntitlementService
{
    /**
     * Resolve the plan currently applying to a user.
     *
     * Priority:
     * 1. Direct user grant
     * 2. Sponsored organisation/group grant
     * 3. System default plan
     */
    public function planFor(
        User $user
    ): ?Plan {
        $directGrant = UserPlanGrant::query()
            ->currentlyActive()
            ->where(
                'user_id',
                $user->id
            )
            ->whereHas(
                'plan',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        true
                    )
            )
            ->with('plan.features')
            ->orderByDesc('id')
            ->first();

        if ($directGrant) {
            return $directGrant->plan;
        }

        $sponsoredGrant =
            $this->sponsoredGrantFor(
                $user
            );

        if ($sponsoredGrant) {
            return $sponsoredGrant->plan;
        }

        return Plan::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->with('features')
            ->first();
    }

    /**
     * Resolve the active sponsored grant applying
     * to the user through organisation membership.
     */
    public function sponsoredGrantFor(
        User $user
    ): ?SponsoredAccessGrant {
        [
            $organisationIds,
            $groupIds,
        ] = $this->scopeIdsFor(
            $user
        );

        if (empty($organisationIds)) {
            return null;
        }

        return SponsoredAccessGrant::query()
            ->currentlyActive()
            ->whereIn(
                'organisation_id',
                $organisationIds
            )
            ->whereHas(
                'sponsor',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        true
                    )
            )
            ->whereHas(
                'plan',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        true
                    )
            )
            ->whereHas(
                'organisation',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        true
                    )
            )
            ->where(function ($query) use (
                $groupIds
            ) {
                $query
                    ->whereNull(
                        'organisation_group_id'
                    );

                if (! empty($groupIds)) {
                    $query->orWhereIn(
                        'organisation_group_id',
                        $groupIds
                    );
                }
            })
            ->where(function ($query) {
                $query
                    ->whereNull(
                        'organisation_group_id'
                    )
                    ->orWhereHas(
                        'organisationGroup',
                        fn ($groupQuery) =>
                            $groupQuery->where(
                                'is_active',
                                true
                            )
                    );
            })
            ->with([
                'plan.features',
                'sponsor',
                'organisation',
                'organisationGroup',
            ])
            ->orderByDesc('priority')
            /*
             * At equal priority, a group-specific
             * sponsorship is more specific than an
             * organisation-wide sponsorship.
             */
            ->orderByRaw(
                'organisation_group_id IS NULL ASC'
            )
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Build the organisation and ancestor-group
     * scope represented by a user's memberships.
     *
     * A user assigned only to DADT04 therefore also
     * falls within its parent groups for sponsorship
     * targeting purposes.
     *
     * @return array{
     *     0: array<int>,
     *     1: array<int>
     * }
     */
    private function scopeIdsFor(
        User $user
    ): array {
        $membershipGroups = $user
            ->organisationGroups()
            ->where(
                'organisation_groups.is_active',
                true
            )
            ->get([
                'organisation_groups.id',
                'organisation_groups.organisation_id',
                'organisation_groups.parent_id',
            ]);

        if ($membershipGroups->isEmpty()) {
            return [
                [],
                [],
            ];
        }

        $organisationIds = $membershipGroups
            ->pluck('organisation_id')
            ->unique()
            ->values()
            ->all();

        /*
         * Load the relevant hierarchy once, rather
         * than querying the database for each parent.
         */
        $groups = OrganisationGroup::query()
            ->whereIn(
                'organisation_id',
                $organisationIds
            )
            ->get([
                'id',
                'organisation_id',
                'parent_id',
            ])
            ->keyBy('id');

        $scopeGroupIds = [];

        foreach ($membershipGroups as $membership) {
            $currentId = $membership->id;
            $visited = [];

            while ($currentId) {
                /*
                 * Protect against malformed circular
                 * parent relationships.
                 */
                if (isset($visited[$currentId])) {
                    break;
                }

                $visited[$currentId] = true;

                $group = $groups->get(
                    $currentId
                );

                if (! $group) {
                    break;
                }

                $scopeGroupIds[
                    $group->id
                ] = true;

                $currentId =
                    $group->parent_id;
            }
        }

        return [
            $organisationIds,
            array_keys(
                $scopeGroupIds
            ),
        ];
    }

    /**
     * Read a configurable feature value for the
     * user's current plan.
     */
    public function value(
        User $user,
        string $key,
        mixed $default = null
    ): mixed {
        $plan = $this->planFor(
            $user
        );

        if (! $plan) {
            return $default;
        }

        $feature = $plan
            ->features
            ->firstWhere(
                'key',
                $key
            );

        if (! $feature) {
            return $default;
        }

        return $feature->value;
    }

    /**
     * Determine whether a boolean plan feature
     * is enabled for the user.
     */
    public function allows(
        User $user,
        string $key,
        bool $default = false
    ): bool {
        return (bool) $this->value(
            $user,
            $key,
            $default
        );
    }

    /**
     * Determine whether advertisements may be
     * rendered for this user.
     *
     * CareerPath currently treats advertisements
     * as student-facing only. Students must also
     * explicitly opt in.
     */
    public function shouldShowAds(
        User $user
    ): bool {
        if (! $user->isStudent()) {
            return false;
        }

        if (! $user->show_ads) {
            return false;
        }

        return $this->allows(
            $user,
            'ads.available'
        );
    }
}