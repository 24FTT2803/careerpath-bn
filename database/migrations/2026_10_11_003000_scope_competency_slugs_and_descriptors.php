<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two things the real framework needs that the schema could
     * not hold.
     *
     * Service Orientation is both a technical competency and a
     * soft skill, with a different definition in each of the
     * book's two glossaries. A unique slug allowed only one of
     * them, so the key becomes the slug and the type together.
     *
     * A competency's level descriptors are not always shared.
     * The book gives Infrastructure Management three different
     * sets — one for IT Technician, one for Systems Engineer,
     * one for Network Engineer and Network Team Lead — and does
     * the same for Security Architecture, Software Configuration
     * and Software Design. So a descriptor carries the scope it
     * was printed under, and the roles that scope names.
     */
    public function up(): void
    {
        Schema::table('biicf_competencies', function (Blueprint $table) {
            $table->dropUnique(['slug']);

            $table->unique(['slug', 'type'], 'competency_slug_type_unique');
        });

        Schema::table('biicf_competency_level_descriptors', function (Blueprint $table) {
            /*
             * The role scope exactly as the book prints it, for
             * instance "Solutions Architect, Associate Security
             * Analyst, Cyber Risk Analyst, Security Engineer".
             *
             * Empty rather than null: a unique key treats nulls
             * as distinct, which would let the same competency
             * and level be described twice with no scope at all.
             * Empty means the descriptor applies wherever the
             * competency is required.
             */
            $table->string('scope_label', 255)->default('');
        });

        /*
         * MySQL adopts an existing index to satisfy a foreign
         * key and then refuses to drop it. The unique key being
         * replaced here was created before the keys on
         * competency_id and proficiency_level_id, so it is the
         * index serving both, and each needs one of its own
         * before it can go.
         */
        Schema::table('biicf_competency_level_descriptors', function (Blueprint $table) {
            $table->index('competency_id', 'descriptor_competency_index');
            $table->index('proficiency_level_id', 'descriptor_level_index');
        });

        Schema::table('biicf_competency_level_descriptors', function (Blueprint $table) {
            $table->dropUnique('competency_level_descriptor_unique');

            $table->unique(
                ['competency_id', 'proficiency_level_id', 'scope_label'],
                'competency_level_scope_unique'
            );
        });

        /*
         * Which roles a scoped descriptor covers. Resolved from
         * the scope text once job roles are loaded; the text
         * stands on its own until then, so an empty pivot is a
         * normal state rather than missing data.
         */
        Schema::create('biicf_competency_descriptor_job_role', function (Blueprint $table) {
            $table->id();

            $table->foreignId('descriptor_id')
                ->constrained('biicf_competency_level_descriptors')
                ->cascadeOnDelete();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['descriptor_id', 'job_role_id'],
                'descriptor_job_role_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_competency_descriptor_job_role');

        Schema::table('biicf_competency_level_descriptors', function (Blueprint $table) {
            $table->dropUnique('competency_level_scope_unique');
        });

        /*
         * Restoring one descriptor set per competency and level
         * means the scoped variants cannot all survive. The
         * first of each is kept.
         */
        $seen = [];

        foreach (
            DB::table('biicf_competency_level_descriptors')
                ->orderBy('id')
                ->get(['id', 'competency_id', 'proficiency_level_id']) as $descriptor
        ) {
            $key = $descriptor->competency_id.':'.$descriptor->proficiency_level_id;

            if (isset($seen[$key])) {
                DB::table('biicf_competency_level_descriptors')
                    ->where('id', $descriptor->id)
                    ->delete();

                continue;
            }

            $seen[$key] = true;
        }

        Schema::table('biicf_competency_level_descriptors', function (Blueprint $table) {
            $table->dropColumn('scope_label');

            $table->unique(
                ['competency_id', 'proficiency_level_id'],
                'competency_level_descriptor_unique'
            );
        });

        /*
         * The composite key is back to indexing both foreign key
         * columns, so the standalone indexes added on the way up
         * are no longer carrying anything.
         */
        Schema::table('biicf_competency_level_descriptors', function (Blueprint $table) {
            $table->dropIndex('descriptor_competency_index');
            $table->dropIndex('descriptor_level_index');
        });

        /*
         * Likewise a unique slug can hold only one of a pair
         * sharing it, so the later one is renamed rather than
         * dropped.
         */
        $slugs = [];

        foreach (
            DB::table('biicf_competencies')
                ->orderBy('id')
                ->get(['id', 'slug', 'type']) as $competency
        ) {
            if (! isset($slugs[$competency->slug])) {
                $slugs[$competency->slug] = true;

                continue;
            }

            DB::table('biicf_competencies')
                ->where('id', $competency->id)
                ->update(['slug' => $competency->slug.'-'.$competency->type]);
        }

        Schema::table('biicf_competencies', function (Blueprint $table) {
            $table->dropUnique('competency_slug_type_unique');

            $table->unique('slug');
        });
    }
};
