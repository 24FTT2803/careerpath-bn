<?php

namespace App\Services\Business;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlanGrant;

class EntitlementService
{
    /**
     * Resolve the plan currently applying to a user.
     *
     * An active direct grant takes precedence over the
     * system default plan.
     */
    public function planFor(
        User $user
    ): ?Plan {
        $grant = UserPlanGrant::query()
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

        if ($grant) {
            return $grant->plan;
        }

        return Plan::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->with('features')
            ->first();
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