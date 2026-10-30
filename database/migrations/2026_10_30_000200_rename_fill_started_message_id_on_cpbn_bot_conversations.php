<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where the CPBN Bot's memory of a conversation begins.
 *
 * It began only with a filling session. Now every part of a
 * conversation is bounded the same way: memory starts when a
 * section starts being filled, and starts afresh once it is
 * finished. The student sees a marker in the chat at each point.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpbn_bot_conversations', function (Blueprint $table) {
            $table->renameColumn('fill_started_message_id', 'memory_from_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('cpbn_bot_conversations', function (Blueprint $table) {
            $table->renameColumn('memory_from_message_id', 'fill_started_message_id');
        });
    }
};
