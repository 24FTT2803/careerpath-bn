<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A required proficiency belongs to a tier, not to a role.
     *
     * The book prints a role's competencies as a table with one
     * column per tier:
     *
     *     Technical Proficiency Level   Level 1  Level 2  Level 3
     *     Application Development          1        2        2
     *     Information Security Mgmt        2        3        3
     *
     * The pivot held one proficiency per competency per role, so
     * that table could not be stored — only one of its columns
     * could. The unique key moves with it: a competency appears
     * once per tier rather than once per role.
     *
     * job_role_id stays on the pivot so the role's own relation
     * keeps working. job_role_tier_id is a plain indexed column
     * with no foreign key, because SQLite can neither drop a
     * column carrying a key nor drop a constraint by name, and
     * this migration has to be able to reverse itself. Deleting
     * a tier clears its rows through a model hook instead.
     */
    public function up(): void
    {
        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->unsignedBigInteger('job_role_tier_id')
                ->nullable()
                ->after('job_role_id')
                ->index();
        });

        $this->attachExistingRowsToTiers();

        /*
         * MySQL insists on an index over a foreign key's column
         * and refuses to drop the one serving that purpose. The
         * unique key being replaced here, on job_role_id and
         * competency_id, is what indexes job_role_id for its
         * key — so job_role_id needs an index of its own before
         * that one can go.
         */
        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->index('job_role_id', 'job_role_competency_role_index');
        });

        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->dropUnique('jr_comp_unique');

            $table->unique(
                ['job_role_tier_id', 'competency_id'],
                'tier_competency_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->dropUnique('tier_competency_unique');
        });

        /*
         * Restoring one proficiency per competency per role means
         * only one tier's column can survive. The lowest tier is
         * the one kept, being the requirement for entering the
         * role at all.
         */
        $lowestTiers = DB::table('biicf_job_role_tiers')
            ->orderBy('sort_order')
            ->get(['id', 'job_role_id'])
            ->groupBy('job_role_id')
            ->map(fn ($tiers) => $tiers->first()->id)
            ->values()
            ->all();

        DB::table('biicf_job_role_competency')
            ->whereNotNull('job_role_tier_id')
            ->whereNotIn('job_role_tier_id', $lowestTiers ?: [0])
            ->delete();

        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->dropIndex(['job_role_tier_id']);
            $table->dropColumn('job_role_tier_id');
        });

        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->unique(['job_role_id', 'competency_id'], 'jr_comp_unique');
        });

        /*
         * The composite key is back to indexing job_role_id for
         * its foreign key, so the standalone index added above is
         * no longer carrying anything.
         */
        Schema::table('biicf_job_role_competency', function (Blueprint $table) {
            $table->dropIndex('job_role_competency_role_index');
        });
    }

    /**
     * Point each existing pivot row at its role's tier.
     *
     * Row by row rather than an UPDATE with a JOIN, which SQLite
     * does not support.
     */
    private function attachExistingRowsToTiers(): void
    {
        $tierByRole = DB::table('biicf_job_role_tiers')
            ->orderBy('sort_order')
            ->pluck('id', 'job_role_id');

        foreach ($tierByRole as $roleId => $tierId) {
            DB::table('biicf_job_role_competency')
                ->where('job_role_id', $roleId)
                ->update(['job_role_tier_id' => $tierId]);
        }

        $orphans = DB::table('biicf_job_role_competency')
            ->whereNull('job_role_tier_id')
            ->count();

        if ($orphans > 0) {
            throw new RuntimeException(
                "{$orphans} job role competency row(s) have no tier to attach to. "
                .'The tier backfill migration should have run first.'
            );
        }
    }
};
