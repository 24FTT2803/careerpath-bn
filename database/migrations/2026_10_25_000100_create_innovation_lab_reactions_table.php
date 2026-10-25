<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How students react to a post in the Innovation Lab.
 *
 * One row per student per emoji, so a student may add several
 * different ones to the same post and the unique index stops
 * them adding the same one twice. That is the Slack shape rather
 * than the Facebook one: reactions accumulate beside each other
 * instead of replacing one another.
 *
 * The emoji itself is stored rather than a name for it. It is
 * what gets drawn, the column is utf8mb4 like the rest of the
 * schema, and the set a student may choose from is fixed in the
 * model — so a row can only ever hold one of a handful of
 * characters, and adding one to the palette needs no migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('innovation_lab_reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('note_id')
                ->constrained('innovation_lab_notes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('emoji', 16);

            $table->timestamps();

            $table->unique(['note_id', 'user_id', 'emoji'], 'lab_reaction_unique');

            /*
             * The feed counts a post's reactions on every page
             * load, grouped by emoji.
             */
            $table->index(['note_id', 'emoji'], 'lab_reaction_tally_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovation_lab_reactions');
    }
};
