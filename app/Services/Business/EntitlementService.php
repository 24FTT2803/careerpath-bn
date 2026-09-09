<?php

namespace App\Services\Business;

use App\Models\FeatureDefinition;
use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;

class EntitlementService
{
    /**
     * Resolve both the plan and where that access
     * came from.
     *
     * Priority:
     * 1. Direct user grant
     * 2. Sponsored access
     * 3. Default plan
     *
     * @return array{
     *     plan: ?Plan,
     *     source: string,
     *     grant: UserPlanGrant|SponsoredAccessGrant|null
     * }
     */
    public function accessFor(
        User $user
    ): array {
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
            return [
                'plan' =>
                    $directGrant->plan,

                'source' =>
                    'direct',

                'grant' =>
                    $directGrant,
            ];
        }

        $sponsoredGrant =
            $this->sponsoredGrantFor(
                $user
            );

        if ($sponsoredGrant) {
            return [
                'plan' =>
                    $sponsoredGrant->plan,

                'source' =>
                    'sponsored',

                'grant' =>
                    $sponsoredGrant,
            ];
        }

        $defaultPlan = Plan::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->with('features')
            ->first();

        return [
            'plan' =>
                $defaultPlan,

            'source' =>
                $defaultPlan
                    ? 'default'
                    : 'none',

            'grant' =>
                null,
        ];
    }

    /**
     * Resolve the plan currently applying to a user.
     */
    public function planFor(
        User $user
    ): ?Plan {
        return $this->accessFor(
            $user
        )['plan'];
    }

    /**
     * Resolve the active sponsored grant applying
     * through organisation/group membership.
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
                $query->whereNull(
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
                'featureOverrides.featureDefinition',
            ])
            ->orderByDesc('priority')
            ->orderByRaw(
                'organisation_group_id IS NULL ASC'
            )
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Read the effective feature value.
     *
     * Resolution:
     *
     * Global maintenance switch
     *      ↓
     * Parent feature
     *      ↓
     * Sponsored override, when sponsored access wins
     *      ↓
     * Current plan value
     */
    public function value(
        User $user,
        string $key,
        mixed $default = null
    ): mixed {
        $access = $this->accessFor(
            $user
        );

        return $this->resolveValue(
            $access,
            $key,
            $default,
            []
        );
    }

    /**
     * Determine whether a boolean feature is
     * effectively enabled.
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
     * Resolve whether a boolean feature is available
     * and explain why it may be unavailable.
     *
     * @return array{
     *     allowed: bool,
     *     reason: ?string,
     *     message: ?string,
     *     feature_key: string,
     *     feature_name: string,
     *     source: string,
     *     plan_code: ?string
     * }
     */
    public function featureAccess(
        User $user,
        string $key,
        bool $default = false
    ): array {
        $access = $this->accessFor(
            $user
        );

        return $this->resolveFeatureAccess(
            $access,
            $key,
            $default,
            []
        );
    }

    /**
     * Resolve a feature state while preserving the
     * reason that access is unavailable.
     */
    private function resolveFeatureAccess(
        array $access,
        string $key,
        bool $default,
        array $visited
    ): array {
        if (isset($visited[$key])) {
            return [
                'allowed' => false,
                'reason' => 'parent_restriction',
                'message' =>
                    'This feature is unavailable because its access hierarchy could not be resolved safely.',
                'feature_key' => $key,
                'feature_name' => $key,
                'source' => $access['source'],
                'plan_code' =>
                    $access['plan']?->code,
            ];
        }

        $visited[$key] = true;

        $definition = FeatureDefinition::query()
            ->where('key', $key)
            ->first();

        $featureName =
            $definition?->name
            ?? $key;

        /*
        * Global OFF always means maintenance /
        * operational unavailability, regardless
        * of the user's plan or sponsorship.
        */
        if (
            $definition
            && ! $definition->global_enabled
        ) {
            return [
                'allowed' => false,
                'reason' => 'maintenance',
                'message' =>
                    'Temporarily unavailable due to maintenance.',
                'feature_key' => $key,
                'feature_name' => $featureName,
                'source' => $access['source'],
                'plan_code' =>
                    $access['plan']?->code,
            ];
        }

        /*
        * A child cannot be available when its parent
        * capability is unavailable.
        */
        if (
            $definition
            && $definition->parent_key
        ) {
            $parentAccess =
                $this->resolveFeatureAccess(
                    $access,
                    $definition->parent_key,
                    false,
                    $visited
                );

            if (! $parentAccess['allowed']) {
                $maintenance =
                    $parentAccess['reason']
                    === 'maintenance';

                return [
                    'allowed' => false,

                    'reason' =>
                        $maintenance
                            ? 'maintenance'
                            : 'parent_restriction',

                    'message' =>
                        $maintenance
                            ? 'Temporarily unavailable because '
                                . $parentAccess['feature_name']
                                . ' is under maintenance.'
                            : 'Unavailable because '
                                . $parentAccess['feature_name']
                                . ' is not available with your current access.',

                    'feature_key' => $key,
                    'feature_name' => $featureName,
                    'source' => $access['source'],
                    'plan_code' =>
                        $access['plan']?->code,
                ];
            }
        }

        $allowed = (bool)
            $this->rawValueForAccess(
                $access,
                $key,
                $default
            );

        if ($allowed) {
            return [
                'allowed' => true,
                'reason' => null,
                'message' => null,
                'feature_key' => $key,
                'feature_name' => $featureName,
                'source' => $access['source'],
                'plan_code' =>
                    $access['plan']?->code,
            ];
        }

        if (
            $access['source']
            === 'sponsored'
        ) {
            return [
                'allowed' => false,
                'reason' =>
                    'sponsored_restriction',

                'message' =>
                    'Not included in your current sponsored access.',

                'feature_key' => $key,
                'feature_name' => $featureName,
                'source' => $access['source'],
                'plan_code' =>
                    $access['plan']?->code,
            ];
        }

        return [
            'allowed' => false,
            'reason' =>
                'plan_restriction',

            'message' =>
                'Not included in your current plan.',

            'feature_key' => $key,
            'feature_name' => $featureName,
            'source' => $access['source'],
            'plan_code' =>
                $access['plan']?->code,
        ];
    }

    /**
     * Read the plan/sponsorship value without applying
     * global or parent feature switches.
     */
    private function rawValueForAccess(
        array $access,
        string $key,
        mixed $default = null
    ): mixed {
        $plan = $access['plan'];

        if (! $plan) {
            return $default;
        }

        if (
            $access['source']
                === 'sponsored'
            && $access['grant']
                instanceof SponsoredAccessGrant
        ) {
            $override = $access['grant']
                ->featureOverrides
                ->first(
                    fn ($override) =>
                        $override
                            ->featureDefinition
                            ?->key
                        === $key
                );

            if ($override) {
                return $override->value;
            }
        }

        $feature = $plan
            ->features
            ->firstWhere(
                'key',
                $key
            );

        return $feature
            ? $feature->value
            : $default;
    }

    /**
     * Resolve feature values recursively so parent
     * switches can disable their child features.
     */
    private function resolveValue(
        array $access,
        string $key,
        mixed $default,
        array $visited
    ): mixed {
        if (isset($visited[$key])) {
            return $default;
        }

        $visited[$key] = true;

        $definition = FeatureDefinition::query()
            ->where(
                'key',
                $key
            )
            ->first();

        /*
         * A missing definition is treated as an older
         * uncatalogued plan feature for backwards
         * compatibility.
         */
        if (
            $definition
            && ! $definition->global_enabled
        ) {
            return $default;
        }

        if (
            $definition
            && $definition->parent_key
        ) {
            $parentEnabled = (bool)
                $this->resolveValue(
                    $access,
                    $definition->parent_key,
                    false,
                    $visited
                );

            if (! $parentEnabled) {
                return $default;
            }
        }

        return $this->rawValueForAccess(
            $access,
            $key,
            $default
        );
    }

    /**
     * Build the organisation and ancestor-group
     * scope represented by a user's memberships.
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
            $currentId =
                $membership->id;

            $visited = [];

            while ($currentId) {
                if (isset(
                    $visited[$currentId]
                )) {
                    break;
                }

                $visited[$currentId] =
                    true;

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
     * Advertisements are student-facing only and
     * still require the student's personal opt-in.
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