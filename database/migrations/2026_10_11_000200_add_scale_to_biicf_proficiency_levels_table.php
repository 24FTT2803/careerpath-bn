<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The BIICF rates technical and soft competencies on two
     * different scales, and we had been using one.
     *
     * Technical skills run over five levels — Follow, Assist,
     * Apply, Ensure, Strategise. Soft skills run over four —
     * Basic, Intermediate, Advanced, Expert. They are not the
     * same ladder with different labels: a soft skill has no
     * fifth rung at all, so mapping Basic onto technical level 2
     * (which is what we were doing) both overstates a student's
     * requirement and makes the two incomparable.
     *
     * Adding a scale lets both live in one table, so the pivot's
     * existing proficiency_level_id keeps working untouched.
     */
    public function up(): void
    {
        Schema::table('biicf_proficiency_levels', function (Blueprint $table) {
            /*
             * A plain string rather than an enum. Adding an enum
             * to an existing table behaves differently across
             * MySQL and the SQLite the tests run on, and the
             * allowed values are enforced by the model's own
             * constants either way.
             */
            $table->string('scale', 20)
                ->default('technical')
                ->after('id');
        });

        /*
         * Everything already in the table is the technical scale,
         * which the column default covers. Stated explicitly so a
         * re-run on a part-migrated database is still correct.
         */
        DB::table('biicf_proficiency_levels')->update(['scale' => 'technical']);

        Schema::table('biicf_proficiency_levels', function (Blueprint $table) {
            $table->unique(['scale', 'level_number'], 'proficiency_scale_level_unique');
        });
    }

    public function down(): void
    {
        /*
         * The soft rows have to go before the column does.
         * Without the scale to tell them apart they would collide
         * with the technical levels on level_number, and the old
         * seeder keys on that number alone.
         *
         * Anything pointing at a soft level must already have
         * been rolled back, which the later migrations handle.
         */
        DB::table('biicf_proficiency_levels')
            ->where('scale', 'soft')
            ->delete();

        Schema::table('biicf_proficiency_levels', function (Blueprint $table) {
            $table->dropUnique('proficiency_scale_level_unique');
            $table->dropColumn('scale');
        });
    }
};
