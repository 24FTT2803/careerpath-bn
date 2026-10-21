<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per difference between an uploaded edition and the
 * framework as it currently stands.
 *
 * Nothing an upload proposes reaches the live tables until an
 * administrator has been through these: a change is accepted,
 * the current value is kept, or the proposal is edited before
 * being accepted. An edition can sit half-reviewed indefinitely
 * without students seeing any of it.
 *
 * Values are stored as JSON because what differs is not always a
 * string — a role's description is a list, a competency's
 * proficiency is a number against a tier — and a diff that
 * flattened them to text could not be applied back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biicf_edition_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('edition_id')
                ->constrained('biicf_editions')
                ->cascadeOnDelete();

            /*
             * What the change is about, as the book organises it:
             * a job role, a competency, a sub-sector, a training.
             * Kept as a string rather than a model class so that
             * a proposal for something that does not exist yet
             * still has a subject.
             */
            $table->string('subject_type', 40);

            /*
             * Identifies the subject within its type across
             * editions — a role's title, a competency's name and
             * type. Not an id: the point of a diff is to work
             * before anything has been created.
             */
            $table->string('subject_key');
            $table->string('subject_label');

            /*
             * Which part of the subject differs. A path rather
             * than a column, since one subject carries lists and
             * per-tier tables: "description",
             * "competencies.Cloud Computing.Entrant".
             */
            $table->string('field');

            // added, changed or removed.
            $table->string('kind', 10);

            $table->json('current_value')->nullable();
            $table->json('proposed_value')->nullable();

            /*
             * What the administrator decided, and what they
             * decided it to be. edited_value is null unless they
             * rewrote the proposal before accepting it.
             */
            $table->string('decision', 10)->default('pending');
            $table->json('edited_value')->nullable();

            $table->timestamp('decided_at')->nullable();

            $table->foreignId('decided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Set when the decision has been written to the live
             * tables. A change decided but not yet applied is
             * still only a proposal.
             */
            $table->timestamp('applied_at')->nullable();

            $table->timestamps();

            /*
             * One row per field of per subject per edition: a
             * re-extraction of the same upload should update the
             * proposal rather than stack another copy of it.
             */
            $table->unique(
                ['edition_id', 'subject_type', 'subject_key', 'field'],
                'edition_change_unique'
            );

            $table->index(['edition_id', 'decision'], 'edition_change_decision_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_edition_changes');
    }
};
