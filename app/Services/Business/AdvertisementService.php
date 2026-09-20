<?php

namespace App\Services\Business;

use App\Models\Advertisement;
use App\Models\AdvertisementSlot;
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
     * @return Collection<string, Collection<int, Advertisement>>
     */
    public function forStudent(User $student): Collection
    {
        if (! $this->entitlements->shouldShowAds($student)) {
            return collect();
        }

        /*
         * The student's own groups plus everything above them,
         * so an advertisement aimed at a school or an intake
         * reaches the classes inside it. Students are members
         * of a class, never of the school directly.
         */
        $groupIds = collect(
            $this->entitlements->scopeGroupIdsFor($student)
        );

        $resolved = collect();

        foreach (self::POSITIONS as $position) {
            if (! $this->positionEnabled($student, $position)) {
                continue;
            }

            $slot = $this->slotFor($position);

            if ($slot === null || ! $slot->is_active) {
                continue;
            }

            $advertisements = $this->pickFor(
                $position,
                $groupIds,
                $slot->resolveCount()
            );

            if ($advertisements->isNotEmpty()) {
                $resolved->put($position, $advertisements);
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
     * The slots, read once per request.
     *
     * @var Collection<string, AdvertisementSlot>|null
     */
    private ?Collection $slots = null;

    public function slotFor(string $position): ?AdvertisementSlot
    {
        $this->slots ??= AdvertisementSlot::all()
            ->keyBy('position');

        return $this->slots->get($position);
    }

    /**
     * Choose the advertisements for a position.
     *
     * Anything aimed at a group the student is not in is left
     * out; the rest appear in the order the placement was
     * arranged in.
     *
     * @param  Collection<int, int>  $groupIds
     * @return Collection<int, Advertisement>
     */
    private function pickFor(
        string $position,
        Collection $groupIds,
        int $wanted
    ): Collection {
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

        /*
         * The administrator decides the order, so a student
         * sees the placement as it was arranged rather than in
         * whatever order the query happened to produce.
         * Targeting still decides what is eligible.
         */
        return $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($wanted)
            ->get();
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
