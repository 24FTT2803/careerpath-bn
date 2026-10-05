<?php

namespace App\Services\AI;

use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Services\Business\EntitlementService;

/**
 * A short pause between recommendation generations.
 *
 * Generating takes several seconds, during which the button
 * stays clickable and the page looks idle. Students pressed it
 * again, which spent another quota use and queued a second
 * identical request. The pause is deliberately short: it exists
 * to absorb an accidental second press, not to ration access,
 * which is what the quota is for.
 */
class RegenerationCooldown
{
    /**
     * Seconds to wait when nothing is configured.
     */
    public const DEFAULT_SECONDS = 30;

    public const FEATURE_KEY =
        'career_recommendations.regeneration_cooldown';

    public function __construct(
        private EntitlementService $entitlements
    ) {}

    /**
     * How long this student must wait, in seconds.
     *
     * Zero means they may generate now, which is also what an
     * administrator gets by switching the feature off globally.
     */
    public function secondsRemaining(User $student): int
    {
        $window = $this->windowFor($student);

        if ($window < 1) {
            return 0;
        }

        $last = $this->lastGeneratedAt($student);

        if ($last === null) {
            return 0;
        }

        $elapsed = $last->diffInSeconds(now());

        return (int) max(0, $window - $elapsed);
    }

    public function blocks(User $student): bool
    {
        return $this->secondsRemaining($student) > 0;
    }

    /**
     * The configured pause for this student's plan.
     *
     * Switching the feature off globally returns zero, so an
     * administrator can remove the pause everywhere without
     * editing each plan.
     */
    public function windowFor(User $student): int
    {
        /*
         * featureAccess is what carries the global switch. Asking
         * for the value alone would hand back the default while
         * the feature is switched off, which is the opposite of
         * what an administrator just asked for.
         */
        $access = $this->entitlements->featureAccess(
            $student,
            self::FEATURE_KEY,
            true
        );

        if (! $access['allowed']) {
            return 0;
        }

        $configured = $this->entitlements->value(
            $student,
            self::FEATURE_KEY,
            self::DEFAULT_SECONDS
        );

        return (int) max(0, (int) $configured);
    }

    private function lastGeneratedAt(User $student)
    {
        return RecommendationGeneration::query()
            ->where('user_id', $student->id)
            ->latest('generated_at')
            ->value('generated_at');
    }

    /**
     * Wording a student can act on.
     */
    public function message(User $student): string
    {
        $seconds = $this->secondsRemaining($student);

        return 'You have just generated recommendations. '
            .'Please wait '
            .$seconds
            .' more '
            .($seconds === 1 ? 'second' : 'seconds')
            .' before generating again.';
    }
}
