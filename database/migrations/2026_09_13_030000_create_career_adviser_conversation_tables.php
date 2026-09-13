<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_adviser_conversations', function (Blueprint $table) {
            $table->id();

            /*
             * Unique because a student keeps one running thread
             * rather than separate conversations.
             */
            $table
                ->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Denormalised so History and staff listings can be
             * built without loading or counting messages. Staff
             * are shown activity only, never message content.
             */
            $table
                ->unsignedInteger('message_count')
                ->default(0);

            $table
                ->timestamp('last_message_at')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('career_adviser_messages', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('career_adviser_conversation_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Which recommendation generation was active when
             * this message was sent.
             *
             * Recorded per message rather than per conversation
             * because one thread spans the student's whole time
             * on the platform and therefore many generations.
             */
            $table
                ->foreignId('recommendation_generation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * user or assistant. System prompts are owned by the
             * AI client and are not persisted here.
             */
            $table->string(
                'role',
                16
            );

            $table->text('content');

            $table->timestamps();

            $table->index([
                'career_adviser_conversation_id',
                'id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_adviser_messages');
        Schema::dropIfExists('career_adviser_conversations');
    }
};
