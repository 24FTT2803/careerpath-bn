<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link a student to their programme by key rather than name.
     *
     * users.programme holds the programme's name as text, and
     * the name was also what matched a student to their group.
     * Renaming a programme therefore silently detached every
     * student holding the old wording. The column stays, because
     * reports and AI payloads must keep the exact label they
     * were generated with, but the key is now what the link
     * rests on.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->foreignId('programme_group_id')
                ->nullable()
                ->after('programme')
                ->constrained('organisation_groups')
                ->nullOnDelete();
        });

        $this->backfill();
    }

    /**
     * Match existing students to a programme group by name.
     */
    private function backfill(): void
    {
        $programmeTypeIds = DB::table('organisation_group_types')
            ->where('name', 'Programme')
            ->pluck('id');

        if ($programmeTypeIds->isEmpty()) {
            return;
        }

        $groups = DB::table('organisation_groups')
            ->whereIn('group_type_id', $programmeTypeIds)
            ->pluck('id', 'name');

        foreach ($groups as $name => $groupId) {
            DB::table('users')
                ->where('programme', $name)
                ->update(['programme_group_id' => $groupId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('programme_group_id');
        });
    }
};
