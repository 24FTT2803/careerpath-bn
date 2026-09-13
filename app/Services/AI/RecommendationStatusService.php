<?php

namespace App\Services\AI;

use App\Models\RecommendationGeneration;
use App\Models\User;

class RecommendationStatusService
{
    public function __construct(
        private ProfileSnapshotService $profileSnapshots
    ) {}

    /**
     * Re-evaluate a student's generations against their current
     * AI-relevant profile.
     *
     * Status is derived, never accumulated. Every call compares
     * each stored snapshot against the profile as it stands now,
     * so a generation returns to current if the student undoes
     * whatever change made it outdated. The point of the feature
     * is that the student can trust the label at any moment, not
     * that they were warned once.
     *
     * Generations without a snapshot are skipped. Those were
     * backfilled from rows written before snapshots existed, so
     * there is nothing to compare and no honest verdict to give.
     */
    public function refreshFor(User $student): void
    {
        $generations = $student
            ->recommendationGenerations()
            ->whereNotNull('profile_snapshot_id')
            ->with('profileSnapshot')
            ->get();

        if ($generations->isEmpty()) {
            return;
        }

        $currentHash = $this->profileSnapshots
            ->currentHashFor($student);

        $newestNumber = (int) $student
            ->recommendationGenerations()
            ->max('generation_number');

        foreach ($generations as $generation) {
            $status = $this->resolveStatus(
                $generation,
                $currentHash,
                $newestNumber
            );

            if ($generation->status === $status) {
                continue;
            }

            $generation->update([
                'status' => $status,
            ]);
        }
    }

    /**
     * Decide what a single generation's status should be.
     */
    private function resolveStatus(
        RecommendationGeneration $generation,
        string $currentHash,
        int $newestNumber
    ): string {
        $matchesProfile = $generation
            ->profileSnapshot
            ?->snapshot_hash === $currentHash;

        if (! $matchesProfile) {
            return RecommendationGeneration::STATUS_OUTDATED;
        }

        return $generation->generation_number === $newestNumber
            ? RecommendationGeneration::STATUS_CURRENT
            : RecommendationGeneration::STATUS_PREVIOUS;
    }
}
