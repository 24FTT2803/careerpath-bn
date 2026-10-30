<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The field the CPBN Bot last asked about or filled in.
 *
 * So "change it" or "make that more formal" is understood as being
 * about the thing just shown, rather than taken as the answer to
 * the next question.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpbn_bot_conversations', function (Blueprint $table) {
            $table->string('fill_field', 64)->nullable()->after('fill_section');
        });
    }

    public function down(): void
    {
        Schema::table('cpbn_bot_conversations', function (Blueprint $table) {
            $table->dropColumn('fill_field');
        });
    }
};
