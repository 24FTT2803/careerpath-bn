<?php

namespace App\Services\Business;

use App\Models\SponsoredAccessGrant;
use App\Models\UserPlanGrant;
use Illuminate\Support\Facades\Cache;

/**
 * Tells students when Premium runs out on its own: a trial or
 * grant reaching its end date, or a sponsorship ending.
 *
 * Each grant is announced once (expiry_notified_at). Grants an
 * administrator revoked are skipped, because the revoke already
 * sent its own message.
 */
class PremiumExpiry
{
    private const THROTTLE_KEY = 'premium-expiry:last-check';

    public function __construct(
        private PremiumNotifier $notifier
    ) {}

    /**
     * Run at most once a minute, however many pages are loaded.
     */
    public function runIfDue(): int
    {
        if (! Cache::add(self::THROTTLE_KEY, true, 60)) {
            return 0;
        }

        return $this->run();
    }

    /**
     * Announce every grant that has ended and not been announced.
     */
    public function run(): int
    {
        return $this->expireDirectGrants() + $this->expireSponsorships();
    }

    private function expireDirectGrants(): int
    {
        $count = 0;

        UserPlanGrant::query()
            ->where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->whereNull('expiry_notified_at')
            ->orderBy('id')
            ->each(function (UserPlanGrant $grant) use (&$count) {
                /* Mark first, so a slow request can never send it twice. */
                $claimed = UserPlanGrant::query()
                    ->whereKey($grant->id)
                    ->whereNull('expiry_notified_at')
                    ->update(['expiry_notified_at' => now()]);

                if ($claimed === 1) {
                    $this->notifier->notifyDirectExpired($grant);
                    $count++;
                }
            });

        return $count;
    }

    private function expireSponsorships(): int
    {
        $count = 0;

        SponsoredAccessGrant::query()
            ->with('sponsor')
            ->where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->whereNull('expiry_notified_at')
            ->orderBy('id')
            ->each(function (SponsoredAccessGrant $grant) use (&$count) {
                $claimed = SponsoredAccessGrant::query()
                    ->whereKey($grant->id)
                    ->whereNull('expiry_notified_at')
                    ->update(['expiry_notified_at' => now()]);

                /* A suspended sponsor already told its students. */
                if ($claimed !== 1 || ! $grant->sponsor?->is_active) {
                    return;
                }

                $coveredIds = $this->notifier->studentsCoveredBy(
                    (int) $grant->organisation_id,
                    $grant->organisation_group_id ? (int) $grant->organisation_group_id : null
                );

                /* Only students left without Premium are told. */
                $lostIds = array_values(array_diff(
                    $coveredIds,
                    $this->notifier->alreadyPremium($coveredIds)
                ));

                $count += $this->notifier->notifySponsorshipExpired(
                    $lostIds,
                    $grant->sponsor,
                    $grant->ends_at
                );
            });

        return $count;
    }
}
