<?php

namespace App\Services\Biicf;

use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRole;
use RuntimeException;

/**
 * Writing the ways in to a role, one set per level.
 *
 * Its own class for the same reason the duties have one: the
 * seeder, an edition and a rebuild all need to do this and must
 * do it identically.
 *
 * Replaces only what the book supplies. An administrator can add
 * a way in the framework does not list and it takes the next
 * number along; clearing the level outright would delete it
 * every time the book was read again.
 */
class EntryRequirementWriter
{
    /**
     * @param  array<string, array<int, array<string, mixed>>>  $byTier  keyed by the level's label
     * @return int how many ways in were written
     */
    public function write(BiicfJobRole $role, array $byTier): int
    {
        if ($byTier === []) {
            return 0;
        }

        $tiers = $role->tiers()->get()->keyBy('label');

        $written = 0;

        foreach ($byTier as $label => $alternatives) {
            $tier = $tiers->get($label);

            if ($tier === null) {
                throw new RuntimeException(
                    "{$role->title}: an entry requirement names a level "
                    ."\"{$label}\" the role does not have."
                );
            }

            $tier->entryRequirements()
                ->where('alternative_number', '<=', count($alternatives))
                ->delete();

            foreach ($alternatives as $index => $alternative) {
                BiicfEntryRequirement::create([
                    'job_role_tier_id' => $tier->id,
                    'alternative_number' => $index + 1,
                    ...$alternative,
                ]);

                $written++;
            }
        }

        return $written;
    }
}
