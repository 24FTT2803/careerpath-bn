<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What each competency means at each of its levels.
     *
     * Over half the reference book is this: for every competency,
     * a separate list of statements per level. Application
     * Development at Level 2 (Assist) is "write or adapt software
     * modules for testing and integration", at Level 4 (Ensure)
     * it is "debug very complex or urgent problems" — different
     * content, not a stronger adverb. Without it, telling a
     * student they need a competency at level 2 says nothing they
     * can act on.
     *
     * Shared reference data keyed by competency and level, not
     * per role, so every role requiring Application Development
     * at Level 2 shows the same description.
     *
     * Rows are left absent where the book prints N/A, which it
     * does for levels a competency genuinely does not reach.
     * Absent therefore means "not applicable at this level", and
     * is not the same as an empty list.
     *
     * Populated by the edition import rather than by hand; this
     * migration only makes the table.
     */
    public function up(): void
    {
        Schema::create('biicf_competency_level_descriptors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competency_id')
                ->constrained('biicf_competencies')
                ->cascadeOnDelete();

            /*
             * Technical competencies descriptor against the
             * five-level technical scale, soft skills against the
             * four-level one. The scale lives on the proficiency
             * level, so nothing here needs to repeat it.
             */
            $table->foreignId('proficiency_level_id')
                ->constrained('biicf_proficiency_levels')
                ->restrictOnDelete();

            /*
             * The book's bullet list for this level, in order.
             * Stored as a list because the statements are only
             * ever displayed together and never queried
             * individually.
             */
            $table->json('statements');

            /*
             * Which edition this wording came from, and the page
             * it was taken from, so an administrator reviewing an
             * extracted descriptor can open the book and check it.
             */
            $table->foreignId('source_edition_id')
                ->nullable()
                ->constrained('biicf_editions')
                ->nullOnDelete();

            $table->unsignedSmallInteger('source_page')->nullable();

            $table->timestamps();

            $table->unique(
                ['competency_id', 'proficiency_level_id'],
                'competency_level_descriptor_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_competency_level_descriptors');
    }
};
