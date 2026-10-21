<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where in an edition's book every value it states is printed.
 *
 * Not only the values that differed from the framework. A
 * difference is staged when the book and the framework disagree,
 * so an edition that has been applied — or one the book agrees
 * with throughout — leaves nothing to point at, and checking the
 * framework against the book was possible only where the two had
 * already fallen out.
 *
 * That is backwards. Checking is what you do when you do not yet
 * know whether something is right, so the place of every value
 * is recorded, agreed or not, and kept for as long as the
 * edition is on record.
 *
 * The reader produces exactly this and used to hand it only to
 * the comparison.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biicf_edition_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('edition_id')
                ->constrained('biicf_editions')
                ->cascadeOnDelete();

            /*
             * Keyed the way a staged difference is keyed, so the
             * review screen and the checking screen ask the same
             * question of the same key: a job role by title, a
             * field by the path the comparison names it with.
             */
            $table->string('subject_type', 40);
            $table->string('subject_key');
            $table->string('field');

            $table->unsignedSmallInteger('page');

            /*
             * The printed positions, with the text at each. The
             * reader that recorded these is not the one drawing
             * them, and two readers of one PDF need not agree
             * about which end of a page they count from, so the
             * words are what make a match possible.
             */
            $table->json('spans');

            $table->timestamps();

            $table->unique(
                ['edition_id', 'subject_type', 'subject_key', 'field'],
                'edition_location_unique'
            );

            $table->index(['edition_id', 'subject_type', 'subject_key'], 'edition_location_subject_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_edition_locations');
    }
};
