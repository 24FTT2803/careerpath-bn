<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per published edition of the BIICF reference book.
     *
     * AITI updates the framework by publishing a new edition, so
     * an edition — not a timestamp — is the unit in which our
     * framework data changes. Keeping them as rows lets us say
     * which edition any piece of data came from, keep the
     * superseded version readable instead of deleting it, and
     * pin a student's old report to the framework it was
     * generated against.
     *
     * The live biicf_* tables always hold the current edition.
     * When a new one is applied, the outgoing dataset is
     * serialised into snapshot_data on the edition it belonged
     * to — the same approach profile_snapshots takes for student
     * profiles, so History can explain what was in play at the
     * time rather than only that something changed.
     */
    public function up(): void
    {
        Schema::create('biicf_editions', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            /*
             * Sorts editions without relying on the title, since
             * "Second Edition" does not sort after "First".
             */
            $table->unsignedSmallInteger('edition_number');

            $table->date('published_on')->nullable();

            /*
             * The uploaded reference book itself, kept so an
             * administrator reviewing an extracted value can open
             * the source and check it.
             */
            $table->string('source_file_path')->nullable();
            $table->string('source_original_name')->nullable();

            /*
             * Null until the extracted data has been reviewed and
             * applied. An edition can therefore exist, and be
             * part-reviewed, without affecting what students see.
             */
            $table->timestamp('applied_at')->nullable();

            /*
             * Exactly one edition is current. Enforced in the
             * application rather than the schema, since MySQL has
             * no partial unique index.
             */
            $table->boolean('is_current')->default(false);

            /*
             * The framework as it stood when this edition was
             * superseded. Null for the current edition, which is
             * live in the biicf_* tables instead.
             */
            $table->json('snapshot_data')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('edition_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_editions');
    }
};
