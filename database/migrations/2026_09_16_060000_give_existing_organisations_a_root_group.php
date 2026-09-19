<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give organisations created before roots existed one now.
     *
     * An organisation added through the screen gets a group
     * named after it, so its tree reads as the institution and
     * then everything within. Politeknik Brunei predates that
     * and had nothing at the top, which made the two
     * organisations behave differently for no reason a user
     * could see.
     *
     * Existing top-level groups are moved underneath it, since
     * that is what they already meant.
     */
    public function up(): void
    {
        $organisations = DB::table('organisations')
            ->whereNull('root_group_id')
            ->get(['id', 'name', 'code']);

        foreach ($organisations as $organisation) {
            $this->createRoot($organisation);
        }
    }

    private function createRoot(object $organisation): void
    {
        $typeId = $this->institutionTypeId($organisation->id);

        /*
         * Read the existing roots before adding the new one, so
         * it does not end up parented to itself.
         */
        $existingRootIds = DB::table('organisation_groups')
            ->where('organisation_id', $organisation->id)
            ->whereNotIn(
                'id',
                fn ($query) => $query
                    ->select('group_id')
                    ->from('organisation_group_parents')
            )
            ->pluck('id');

        $rootId = DB::table('organisation_groups')->insertGetId([
            'organisation_id' => $organisation->id,
            'group_type_id' => $typeId,
            'name' => $organisation->name,
            'code' => $organisation->code,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($existingRootIds as $groupId) {
            DB::table('organisation_group_parents')->insert([
                'group_id' => $groupId,
                'parent_id' => $rootId,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('organisations')
            ->where('id', $organisation->id)
            ->update(['root_group_id' => $rootId]);
    }

    private function institutionTypeId(int $organisationId): int
    {
        $existing = DB::table('organisation_group_types')
            ->where('organisation_id', $organisationId)
            ->where('name', 'Institution')
            ->value('id');

        if ($existing !== null) {
            return $existing;
        }

        return DB::table('organisation_group_types')->insertGetId([
            'organisation_id' => $organisationId,
            'name' => 'Institution',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Left in place deliberately.
     *
     * Reversing would mean deciding which groups were roots
     * before, and that is not recorded anywhere.
     */
    public function down(): void {}
};
