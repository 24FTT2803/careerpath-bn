<?php

namespace App\Services\Business;

use App\Models\FeatureUsage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class FeatureUsageService
{
    public function __construct(
        private EntitlementService $entitlements
    ) {
    }

    /**
     * Resolve the user's current quota state.
     *
     * @return array{
     *     allowed: bool,
     *     reason: ?string,
     *     message: ?string,
     *     mode: string,
     *     amount: ?int,
     *     used: int,
     *     remaining: ?int,
     *     next_available_at: ?Carbon,
     *     period_value: ?int,
     *     period_unit: ?string
     * }
     */
    public function status(
        User $user,
        string $featureKey
    ): array {
        /*
        * Preserve global/parent availability reasons for the
        * quota feature itself before attempting to interpret
        * its configured value.
        */
        $quotaAccess =
            $this->entitlements
                ->featureAccess(
                    $user,
                    $featureKey,
                    true
                );

        if (! $quotaAccess['allowed']) {
            return [
                'allowed' => false,
                'reason' =>
                    $quotaAccess['reason'],
                'message' =>
                    $quotaAccess['message'],
                'mode' => 'unavailable',
                'amount' => null,
                'used' => 0,
                'remaining' => null,
                'next_available_at' => null,
                'period_value' => null,
                'period_unit' => null,
            ];
        }

        $quota = $this->entitlements
            ->value(
                $user,
                $featureKey
            );

        if (! is_array($quota)) {
            return $this->invalidQuotaStatus();
        }

        $mode = $quota['mode'] ?? null;

        if ($mode === 'unlimited') {
            return [
                'allowed' => true,
                'reason' => null,
                'message' => null,
                'mode' => 'unlimited',
                'amount' => null,
                'used' =>
                    $this->usageQuery(
                        $user,
                        $featureKey
                    )->count(),
                'remaining' => null,
                'next_available_at' => null,
                'period_value' => null,
                'period_unit' => null,
            ];
        }

        if ($mode === 'total') {
            return $this->totalStatus(
                $user,
                $featureKey,
                $quota
            );
        }

        if ($mode === 'recurring') {
            return $this->recurringStatus(
                $user,
                $featureKey,
                $quota
            );
        }

        return $this->invalidQuotaStatus();
    }

    /**
     * Record one successful use of a quota-controlled
     * feature.
     */
    public function recordUsage(
        User $user,
        string $featureKey
    ): FeatureUsage {
        $access = $this->entitlements
            ->accessFor($user);

        return FeatureUsage::create([
            'user_id' =>
                $user->id,

            'feature_key' =>
                $featureKey,

            'access_source' =>
                $access['source'],

            'plan_id' =>
                $access['plan']?->id,

            'grant_id' =>
                $access['grant']?->id,

            'used_at' =>
                now(),
        ]);
    }

    private function totalStatus(
        User $user,
        string $featureKey,
        array $quota
    ): array {
        $amount = $this->positiveInteger(
            $quota['amount'] ?? null
        );

        if ($amount === null) {
            return $this->invalidQuotaStatus();
        }

        $used = $this->usageQuery(
            $user,
            $featureKey
        )->count();

        $remaining = max(
            0,
            $amount - $used
        );

        $allowed = $used < $amount;

        return [
            'allowed' => $allowed,
            'reason' =>
                $allowed
                    ? null
                    : 'quota_exceeded',

            'message' =>
                $allowed
                    ? null
                    : 'You have reached the total usage limit for this feature.',

            'mode' => 'total',
            'amount' => $amount,
            'used' => $used,
            'remaining' => $remaining,
            'next_available_at' => null,
            'period_value' => null,
            'period_unit' => null,
        ];
    }

    private function recurringStatus(
        User $user,
        string $featureKey,
        array $quota
    ): array {
        $amount = $this->positiveInteger(
            $quota['amount'] ?? null
        );

        $periodValue = $this->positiveInteger(
            $quota['period_value'] ?? null
        );

        $periodUnit =
            $quota['period_unit']
            ?? null;

        if (
            $amount === null
            || $periodValue === null
            || ! in_array(
                $periodUnit,
                [
                    'minute',
                    'hour',
                    'day',
                    'week',
                    'month',
                    'year',
                ],
                true
            )
        ) {
            return $this->invalidQuotaStatus();
        }

        $windowStart =
            $this->subtractPeriod(
                now(),
                $periodValue,
                $periodUnit
            );

        $query = $this->usageQuery(
            $user,
            $featureKey
        )->where(
            'used_at',
            '>',
            $windowStart
        );

        $used = (clone $query)->count();

        $remaining = max(
            0,
            $amount - $used
        );

        $allowed = $used < $amount;

        $nextAvailableAt = null;

        if (! $allowed) {
            /*
             * If the quota was reduced after additional
             * uses had already occurred, more than one
             * old usage may need to expire before another
             * use becomes available.
             */
            $offset =
                max(
                    0,
                    $used - $amount
                );

            $blockingUsage =
                (clone $query)
                    ->orderBy('used_at')
                    ->skip($offset)
                    ->first();

            if ($blockingUsage) {
                $nextAvailableAt =
                    $this->addPeriod(
                        $blockingUsage->used_at,
                        $periodValue,
                        $periodUnit
                    );
            }
        }

        return [
            'allowed' => $allowed,
            'reason' =>
                $allowed
                    ? null
                    : 'quota_exceeded',

            'message' =>
                $allowed
                    ? null
                    : 'You have reached the usage limit for this feature. Please try again after your usage window allows another request.',

            'mode' => 'recurring',
            'amount' => $amount,
            'used' => $used,
            'remaining' => $remaining,
            'next_available_at' =>
                $nextAvailableAt,

            'period_value' =>
                $periodValue,

            'period_unit' =>
                $periodUnit,
        ];
    }

    /**
     * Limit usage calculations to the entitlement context
     * currently applying to the user.
     */
    private function usageQuery(
        User $user,
        string $featureKey
    ): Builder {
        $access = $this->entitlements
            ->accessFor($user);

        $query = FeatureUsage::query()
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'feature_key',
                $featureKey
            )
            ->where(
                'access_source',
                $access['source']
            );

        if ($access['plan']) {
            $query->where(
                'plan_id',
                $access['plan']->id
            );
        } else {
            $query->whereNull(
                'plan_id'
            );
        }

        if ($access['grant']) {
            $query->where(
                'grant_id',
                $access['grant']->id
            );
        } else {
            $query->whereNull(
                'grant_id'
            );
        }

        return $query;
    }

    private function positiveInteger(
        mixed $value
    ): ?int {
        if (
            ! is_numeric($value)
            || (int) $value < 1
        ) {
            return null;
        }

        return (int) $value;
    }

    private function subtractPeriod(
        Carbon $time,
        int $value,
        string $unit
    ): Carbon {
        return match ($unit) {
            'minute' =>
                $time->copy()
                    ->subMinutes($value),

            'hour' =>
                $time->copy()
                    ->subHours($value),

            'day' =>
                $time->copy()
                    ->subDays($value),

            'week' =>
                $time->copy()
                    ->subWeeks($value),

            'month' =>
                $time->copy()
                    ->subMonthsNoOverflow($value),

            'year' =>
                $time->copy()
                    ->subYearsNoOverflow($value),

            default =>
                throw new InvalidArgumentException(
                    'Unsupported quota period unit.'
                ),
        };
    }

    private function addPeriod(
        Carbon $time,
        int $value,
        string $unit
    ): Carbon {
        return match ($unit) {
            'minute' =>
                $time->copy()
                    ->addMinutes($value),

            'hour' =>
                $time->copy()
                    ->addHours($value),

            'day' =>
                $time->copy()
                    ->addDays($value),

            'week' =>
                $time->copy()
                    ->addWeeks($value),

            'month' =>
                $time->copy()
                    ->addMonthsNoOverflow($value),

            'year' =>
                $time->copy()
                    ->addYearsNoOverflow($value),

            default =>
                throw new InvalidArgumentException(
                    'Unsupported quota period unit.'
                ),
        };
    }

    private function invalidQuotaStatus(): array
    {
        return [
            'allowed' => false,
            'reason' =>
                'invalid_quota',

            'message' =>
                'This feature is temporarily unavailable because its usage limit is not configured correctly.',

            'mode' => 'invalid',
            'amount' => null,
            'used' => 0,
            'remaining' => null,
            'next_available_at' => null,
            'period_value' => null,
            'period_unit' => null,
        ];
    }
}