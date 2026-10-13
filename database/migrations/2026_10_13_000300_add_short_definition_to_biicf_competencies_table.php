<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A competency in a sentence.
     *
     * The book's definition runs to a paragraph, which is the
     * right length to read once and the wrong length for a table
     * of a hundred and thirty-nine rows. Cutting it at fifty
     * characters gave half a sentence — "The ability to apply
     * knowledge of the princip…" — which tells a reader nothing
     * the name had not already told them.
     *
     * So the short form is written rather than cut. Short on
     * purpose: a column that cannot hold a paragraph cannot
     * quietly become one.
     *
     * Nothing fills it yet. An administrator can, on the
     * competency's own page, and the simplified descriptions a
     * new edition will generate land here too.
     */
    public function up(): void
    {
        Schema::table('biicf_competencies', function (Blueprint $table) {
            $table->string('short_definition', 255)
                ->nullable()
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('biicf_competencies', function (Blueprint $table) {
            $table->dropColumn('short_definition');
        });
    }
};
