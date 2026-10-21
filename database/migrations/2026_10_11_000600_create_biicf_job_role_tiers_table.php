<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The columns of a role's competency table.
     *
     * A BIICF role page does not state one proficiency per
     * competency. It states a column per tier of the role, and
     * the book uses that column in two ways:
     *
     *  - IT Technician, whose Job Level is Entrant, has columns
     *    Level 1, Level 2 and Level 3 — a progression inside the
     *    one job level, each with its own entry requirement
     *    (BDQF 2, 3 and 4 respectively).
     *  - IT Administrator/Manager, whose Job Level reads "Entrant
     *    or Expert/Management", has columns Entrant and
     *    Expert/Management — the job levels it spans.
     *
     * A tier covers both: a labelled step within a role that
     * carries its own entry requirement and its own column of
     * required proficiencies. job_level_id is set in the second
     * case and null in the first.
     *
     * Every role gets at least one tier, including the many with
     * a single column, so that entry requirements and competency
     * proficiencies always hang off a tier rather than sometimes
     * a tier and sometimes a role.
     */
    public function up(): void
    {
        Schema::create('biicf_job_role_tiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            /*
             * The column heading as the book prints it: "Level 1",
             * "Entrant", "Expert/Management".
             */
            $table->string('label');

            /*
             * Set when the tier is one of the framework's named
             * job levels, null when it is a numbered step inside
             * a single level. Restricted rather than cascading:
             * removing a job level that roles are tiered by should
             * fail loudly, not quietly delete their tiers.
             */
            $table->foreignId('job_level_id')
                ->nullable()
                ->constrained('biicf_job_levels')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['job_role_id', 'label'],
                'job_role_tier_label_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_job_role_tiers');
    }
};
