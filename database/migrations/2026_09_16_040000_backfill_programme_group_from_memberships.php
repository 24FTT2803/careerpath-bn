<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Recover the programme key from existing memberships.
     *
     * The first backfill matched on the stored name, which had
     * already drifted: the programmes were renamed, so students
     * holding the old wording matched nothing. Their membership
     * never drifted, because it points at the group itself, so
     * it is the better source.
     *
     * The stored name is refreshed at the same time, otherwise
     * reports would keep showing wording that no longer exists.
     */
    public function up(): void
    {
        $programmes = DB::table('organisation_groups')
            ->join(
                'organisation_group_types',
                'organisation_group_types.id',
                '=',
                'organisation_groups.group_type_id'
            )
            ->where('organisation_group_types.name', 'Programme')
            ->pluck(
                'organisation_groups.name',
                'organisation_groups.id'
            );

        if ($programmes->isEmpty()) {
            return;
        }

        $memberships = DB::table('group_memberships')
            ->whereIn(
                'organisation_group_id',
                $programmes->keys()
            )
            ->get(['user_id', 'organisation_group_id']);

        foreach ($memberships as $membership) {
            DB::table('users')
                ->where('id', $membership->user_id)
                ->whereNull('programme_group_id')
                ->update([
                    'programme_group_id' => $membership
                        ->organisation_group_id,

                    'programme' => $programmes->get(
                        $membership->organisation_group_id
                    ),
                ]);
        }

        $this->refreshStaleLabels($programmes);
    }

    /**
     * Bring stored names back in step with their group.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $programmes
     */
    private function refreshStaleLabels($programmes): void
    {
        foreach ($programmes as $groupId => $name) {
            DB::table('users')
                ->where('programme_group_id', $groupId)
                ->where('programme', '!=', $name)
                ->update(['programme' => $name]);
        }
    }

    /**
     * Left in place deliberately.
     *
     * Clearing the key would be guesswork, since by the time
     * this is reversed some of it may have been set by hand.
     */
    public function down(): void {}
};
