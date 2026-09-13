<?php

namespace App\Services\Business;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Support\Collection;

class AdvertisementService
{
    public function __construct(
        private EntitlementService $entitlements
    ) {}

    /**
     * The positions a page can fill, in order.
     *
     * @var array<int, string>
     */
    public const POSITIONS = [
        Advertisement::POSITION_ONE,
        Advertisement::POSITION_TWO,
    ];

    /**
     * Resolve one advertisement per eligible position.
     *
     * Returns an empty collection when the student should see
     * no advertising at all, which lets the layout drop its
     * advertising columns entirely rather than reserving space
     * nobody fills.
     *
     * @return Collection<string, Advertisement>
     */
    public function forStudent(User $student): Collection
    {
        if (! $this->entitlements->shouldShowAds($student)) {
            return collect();
        }

        $groupIds = $student
            ->groupMemberships()
            ->pluck('organisation_group_id');

        $resolved = collect();

        foreach (self::POSITIONS as $position) {
            if (! $this->positionEnabled($student, $position)) {
                continue;
            }

            $advertisement = $this->pickFor(
                $position,
                $groupIds
            );

            if ($advertisement !== null) {
                $resolved->put($position, $advertisement);
            }
        }

        /*
         * Rails are all or nothing. A single advertisement in
         * one side column with the other empty reads as a
         * rendering fault rather than a design, so a lone
         * advertisement falls back to the inline layout.
         */
        return $resolved;
    }

    /**
     * Choose one advertisement for a position.
     *
     * Advertisements aimed at a group the student belongs to
     * are preferred over untargeted ones. Beyond that the pick
     * is random, so every booked advertisement gets exposure
     * instead of the oldest row winning every time.
     *
     * @param  Collection<int, int>  $groupIds
     */
    private function pickFor(
        string $position,
        Collection $groupIds
    ): ?Advertisement {
        $query = Advertisement::query()
            ->currentlyRunning()
            ->where('position', $position)
            ->where(function ($query) use ($groupIds) {
                $query
                    ->whereNull('organisation_group_id')
                    ->orWhereIn(
                        'organisation_group_id',
                        $groupIds
                    );
            });

        $targeted = (clone $query)
            ->whereNotNull('organisation_group_id')
            ->inRandomOrder()
            ->first();

        return $targeted
            ?? $query->inRandomOrder()->first();
    }

    /**
     * Whether a position is switched on for this student.
     */
    private function positionEnabled(
        User $student,
        string $position
    ): bool {
        return $this->entitlements->allows(
            $student,
            "ads.position.{$position}.enabled"
        );
    }
}
