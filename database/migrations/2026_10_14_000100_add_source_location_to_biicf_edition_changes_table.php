<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where in the uploaded book a proposed value was read from.
 *
 * An administrator deciding a difference is being asked which of
 * two values is right, and until now the only way to check was
 * to search three hundred pages for the sentence. The reader
 * knew the answer all along — it reads the book by position —
 * and threw it away on the way out.
 *
 * Kept on the change rather than the edition because it is per
 * field: one edition holds thousands of differences and each one
 * is printed somewhere different. Kept as JSON because a page
 * and a handful of positions is not a column, and because the
 * review screen is the only thing that reads it.
 *
 * Null for a difference raised by an upload that carries no
 * positions at all — a JSON edition has no pages — and for every
 * row staged before this column existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biicf_edition_changes', function (Blueprint $table) {
            $table->json('source_location')->nullable()->after('proposed_value');
        });
    }

    public function down(): void
    {
        Schema::table('biicf_edition_changes', function (Blueprint $table) {
            $table->dropColumn('source_location');
        });
    }
};
