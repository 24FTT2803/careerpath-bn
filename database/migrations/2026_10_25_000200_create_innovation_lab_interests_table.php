<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students who said they would like to join what a post is
 * about, and the posts that are open to being joined.
 *
 * Not every post is something you can join. A note about a bug
 * fix is not, and "Interested in joining" under one reads as
 * noise and makes the count mean nothing — so the button is
 * there only where whoever wrote the post asked for it.
 *
 * One row per student per post. Pressed again it is removed, so
 * the count is always the number of students who currently say
 * yes rather than the number who ever pressed it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('innovation_lab_notes', function (Blueprint $table) {
            $table->boolean('accepts_interest')
                ->default(false)
                ->after('is_published');
        });

        Schema::create('innovation_lab_interests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('note_id')
                ->constrained('innovation_lab_notes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['note_id', 'user_id'], 'lab_interest_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovation_lab_interests');

        Schema::table('innovation_lab_notes', function (Blueprint $table) {
            $table->dropColumn('accepts_interest');
        });
    }
};
