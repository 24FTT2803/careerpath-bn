<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The profile section a CPBN Bot conversation is filling in, and
 * the message it started at.
 *
 * While a section is being filled, the bot is shown the whole of
 * that part of the conversation, however long, so nothing said
 * about it is forgotten. Once the student confirms it is finished
 * the section is cleared, and only a few recent messages are
 * shown again: what the form holds is read from the page instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpbn_bot_conversations', function (Blueprint $table) {
            $table->string('fill_section', 32)->nullable()->after('title');
            $table->unsignedBigInteger('fill_started_message_id')->nullable()->after('fill_section');
        });
    }

    public function down(): void
    {
        Schema::table('cpbn_bot_conversations', function (Blueprint $table) {
            $table->dropColumn(['fill_section', 'fill_started_message_id']);
        });
    }
};
