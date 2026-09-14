<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_group_parents', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('group_id')
                ->constrained('organisation_groups')
                ->cascadeOnDelete();

            $table
                ->foreignId('parent_id')
                ->constrained('organisation_groups')
                ->cascadeOnDelete();

            /*
             * A group can sit in several branches at once: a
             * class belongs to its programme and to its intake
             * session. One edge is marked primary so anything
             * needing a single path, such as a breadcrumb, has
             * an answer. Targeting uses every edge.
             */
            $table
                ->boolean('is_primary')
                ->default(false);

            $table->timestamps();

            $table->unique([
                'group_id',
                'parent_id',
            ]);

            $table->index('parent_id');
        });

        $this->migrateExistingParents();

        Schema::table('organisation_groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }

    /**
     * Carry the single parent across as a primary edge.
     */
    private function migrateExistingParents(): void
    {
        $groups = DB::table('organisation_groups')
            ->whereNotNull('parent_id')
            ->get(['id', 'parent_id']);

        foreach ($groups as $group) {
            DB::table('organisation_group_parents')->insert([
                'group_id' => $group->id,
                'parent_id' => $group->parent_id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('organisation_groups', function (Blueprint $table) {
            $table
                ->foreignId('parent_id')
                ->nullable()
                ->constrained('organisation_groups')
                ->nullOnDelete();
        });

        /*
         * Only the primary edge survives going back, because a
         * single column cannot hold more than one parent.
         */
        $edges = DB::table('organisation_group_parents')
            ->where('is_primary', true)
            ->get(['group_id', 'parent_id']);

        foreach ($edges as $edge) {
            DB::table('organisation_groups')
                ->where('id', $edge->group_id)
                ->update(['parent_id' => $edge->parent_id]);
        }

        Schema::dropIfExists('organisation_group_parents');
    }
};
