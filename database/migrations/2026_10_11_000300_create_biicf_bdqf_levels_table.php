<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Brunei Darussalam Qualifications Framework ladder, from
     * Annex 1 of the BIICF.
     *
     * Every entry requirement in the framework is phrased as a
     * BDQF level — "BDQF Level 4 in IT or related field" — so
     * without the ladder itself that text is an opaque string. We
     * store it to answer two questions: what a level means in
     * plain qualifications, and which level a student's own
     * qualification sits at, which is what makes an entry
     * requirement comparable rather than merely displayable.
     *
     * The three sector columns mirror the Annex's own layout so
     * the page can be checked against the book side by side.
     */
    public function up(): void
    {
        Schema::create('biicf_bdqf_levels', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('level')->unique();

            /*
             * Qualifications awarded at this level, kept per
             * sector as the Annex presents them. A sector with no
             * award at a given level is null rather than an empty
             * list, so "the Annex leaves this blank" and "we have
             * not filled it in" stay distinguishable.
             */
            $table->json('schools_sector')->nullable();
            $table->json('tvet_sector')->nullable();
            $table->json('higher_education_sector')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_bdqf_levels');
    }
};
