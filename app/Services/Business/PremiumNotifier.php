<?php

namespace App\Services\Business;

use App\Models\BusinessSponsor;
use App\Models\Notification;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\User;
use App\Models\UserPlanGrant;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Tells students, through their notification bell, when an
 * administrator turns Premium on for them.
 */
class PremiumNotifier
{
    public const TYPE = 'premium';

    public function __construct(
        private EntitlementService $entitlements
    ) {}

    /**
     * A student was given Premium directly (Access Grants page).
     */
    public function notifyDirectGrant(UserPlanGrant $grant): void
    {
        $scheduled = $this->isScheduled($grant->starts_at);

        $lead = match ($grant->source) {
            'trial' => 'You have been given a Premium trial.',
            'sponsorship' => $scheduled
                ? 'Premium has been arranged for your account through sponsorship.'
                : 'Premium has been activated on your account through sponsorship.',
            default => $scheduled
                ? 'An administrator has scheduled Premium for your account.'
                : 'An administrator has activated Premium on your account.',
        };

        $this->send(
            [$grant->user_id],
            $scheduled ? 'Premium coming soon' : 'Premium activated',
            $lead.' '.$this->timing($grant->starts_at, $grant->ends_at)
        );
    }

    /**
     * Students a sponsorship would reach: members of the chosen
     * group or any group beneath it, or everyone in the
     * organisation when no group is chosen.
     *
     * Mirrors how sponsorship is resolved, so only students who
     * would actually receive the access are counted.
     *
     * @return array<int, int>
     */
    public function studentsCoveredBy(int $organisationId, ?int $groupId): array
    {
        $organisation = Organisation::find($organisationId);

        if ($organisation === null || ! $organisation->is_active) {
            return [];
        }

        if ($groupId === null) {
            $groupIds = OrganisationGroup::query()
                ->where('organisation_id', $organisationId)
                ->pluck('id')
                ->all();
        } else {
            $target = OrganisationGroup::find($groupId);

            if ($target === null || ! $target->is_active) {
                return [];
            }

            $groupIds = $this->groupAndDescendants($target);
        }

        if ($groupIds === []) {
            return [];
        }

        return User::query()
            ->where('role', 'student')
            ->whereHas(
                'organisationGroups',
                fn ($query) => $query
                    ->whereIn('organisation_groups.id', $groupIds)
                    ->where('organisation_groups.is_active', true)
            )
            ->pluck('id')
            ->all();
    }

    /**
     * Of these students, the ones already on Premium.
     *
     * Read before a sponsorship is saved, so nobody is told
     * Premium was "activated" when they already had it.
     *
     * @param  array<int, int>  $userIds
     * @return array<int, int>
     */
    public function alreadyPremium(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->get()
            ->filter(fn (User $user) => $this->entitlements->planFor($user)?->code === 'premium')
            ->pluck('id')
            ->all();
    }

    /**
     * A sponsorship was added for a group (Sponsorship page).
     *
     * @param  array<int, int>  $userIds
     */
    public function notifySponsorship(
        array $userIds,
        BusinessSponsor $sponsor,
        ?CarbonInterface $startsAt,
        ?CarbonInterface $endsAt
    ): int {
        if ($userIds === []) {
            return 0;
        }

        $scheduled = $this->isScheduled($startsAt);

        $lead = $scheduled
            ? "{$sponsor->name} is sponsoring Premium for you."
            : "Premium has been activated on your account, sponsored by {$sponsor->name}.";

        return $this->send(
            $userIds,
            $scheduled ? 'Premium coming soon' : 'Premium activated',
            $lead.' '.$this->timing($startsAt, $endsAt)
        );
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function send(array $userIds, string $title, string $message): int
    {
        $link = route('student.settings', absolute: false);
        $now = now();

        $rows = array_map(fn (int $userId) => [
            'user_id' => $userId,
            'type' => self::TYPE,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_values(array_unique($userIds)));

        foreach (array_chunk($rows, 500) as $chunk) {
            Notification::insert($chunk);
        }

        return count($rows);
    }

    private function timing(?CarbonInterface $startsAt, ?CarbonInterface $endsAt): string
    {
        $start = $startsAt ? $this->day($startsAt) : null;
        $end = $endsAt ? $this->day($endsAt) : null;

        if ($this->isScheduled($startsAt)) {
            return $end
                ? "It runs from {$start} to {$end}."
                : "It starts on {$start}.";
        }

        return $end
            ? "It runs until {$end}."
            : 'Enjoy all Premium features.';
    }

    private function isScheduled(?CarbonInterface $startsAt): bool
    {
        return $startsAt !== null && $startsAt->isFuture();
    }

    private function day(CarbonInterface $date): string
    {
        return $date->copy()
            ->setTimezone(config('app.business_timezone'))
            ->format('j M Y');
    }

    /**
     * The chosen group plus every group nested under it.
     *
     * @return array<int, int>
     */
    private function groupAndDescendants(OrganisationGroup $target): array
    {
        $childrenByParent = [];

        foreach (DB::table('organisation_group_parents')->get(['group_id', 'parent_id']) as $edge) {
            $childrenByParent[$edge->parent_id][] = $edge->group_id;
        }

        $found = [];
        $pending = [$target->id];

        /* A group can sit under several parents; never revisit one. */
        while ($pending !== []) {
            $current = array_shift($pending);

            if (isset($found[$current])) {
                continue;
            }

            $found[$current] = true;

            foreach ($childrenByParent[$current] ?? [] as $childId) {
                if (! isset($found[$childId])) {
                    $pending[] = $childId;
                }
            }
        }

        return array_keys($found);
    }
}
