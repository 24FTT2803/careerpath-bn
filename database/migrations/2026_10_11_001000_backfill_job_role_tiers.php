<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give every existing job role a tier.
     *
     * Entry requirements and competency proficiencies are about
     * to hang off tiers rather than roles, and that only works
     * if the invariant holds everywhere: every role has at least
     * one tier, so nothing downstream has to branch on whether a
     * particular role happens to have any.
     *
     * The label is a placeholder. The roles in the database are a
     * seeder's approximation of the book and carry no tier
     * information, so there is nothing truthful to call these
     * yet; the first real edition import replaces them with the
     * book's own columns.
     */
    private const PLACEHOLDER_LABEL = 'All levels';

    public function up(): void
    {
        $now = now();

        $existing = DB::table('biicf_job_role_tiers')
            ->pluck('job_role_id')
            ->all();

        $rows = DB::table('biicf_job_roles')
            ->whereNotIn('id', $existing ?: [0])
            ->pluck('id')
            ->map(fn ($id) => [
                'job_role_id' => $id,
                'label' => self::PLACEHOLDER_LABEL,
                'job_level_id' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows !== []) {
            DB::table('biicf_job_role_tiers')->insert($rows);
        }
    }

    public function down(): void
    {
        /*
         * Only the placeholders go. A tier someone has since
         * added by hand, or one carrying the book's own label,
         * is not this migration's to remove.
         */
        DB::table('biicf_job_role_tiers')
            ->where('label', self::PLACEHOLDER_LABEL)
            ->whereNull('job_level_id')
            ->delete();
    }
};
