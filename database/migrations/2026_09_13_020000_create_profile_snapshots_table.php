<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_snapshots', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * SHA-256 of the canonical snapshot payload.
             *
             * Comparing this against the student's current
             * profile hash is what makes a generation outdated,
             * rather than guessing which individual field
             * mattered.
             */
            $table->string(
                'snapshot_hash',
                64
            );

            /*
             * The AI-relevant profile exactly as it was when the
             * recommendations were produced. Stored so History
             * can later explain what the AI was given, not only
             * that something changed.
             */
            $table->json('snapshot_data');

            $table->timestamps();

            /*
             * An unchanged profile reuses its existing snapshot
             * instead of writing a duplicate row per generation.
             */
            $table->unique([
                'user_id',
                'snapshot_hash',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_snapshots');
    }
};
