<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_generations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Per-user sequence starting at 1 so History can
             * label "Generation #3" without counting rows or
             * relying on the primary key.
             */
            $table->unsignedInteger('generation_number');

            /*
             * current  - newest generation, still matches profile
             * previous - superseded, still matches profile
             * outdated - profile changed after generation
             *
             * Outdated detection requires profile snapshots and
             * arrives in a later migration. This table only
             * produces current and previous for now.
             */
            $table->string(
                'status',
                16
            )->default('current');

            /*
             * Which Career AI client produced this generation.
             * Preserved because mock and Groq output differ, and
             * archived generations must stay interpretable.
             */
            $table->string(
                'driver',
                32
            );

            $table->string(
                'schema_version',
                16
            )->nullable();

            /*
             * Denormalised so History listings do not count
             * recommendation rows. The number of recommendations
             * per generation is plan-configurable, so this cannot
             * be assumed to be three.
             */
            $table
                ->unsignedTinyInteger('recommendation_count')
                ->default(0);

            $table->timestamp('generated_at');

            $table->timestamps();

            $table->unique([
                'user_id',
                'generation_number',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_generations');
    }
};
