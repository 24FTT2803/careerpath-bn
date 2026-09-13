<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_recommendations', function (Blueprint $table) {
            /*
             * Nullable because existing rows predate generations.
             * They are attached to a backfilled generation below.
             */
            $table
                ->foreignId('recommendation_generation_id')
                ->nullable()
                ->after('user_id')
                ->constrained('recommendation_generations')
                ->cascadeOnDelete();

            $table->index([
                'recommendation_generation_id',
                'rank',
            ]);
        });

        $this->backfillExistingRecommendations();
    }

    /**
     * Attach pre-existing recommendations to a generation.
     *
     * Without this, rows written before Task 6 would belong to
     * no generation, and every screen reading the current
     * generation would show the student nothing.
     */
    private function backfillExistingRecommendations(): void
    {
        $userIds = DB::table('career_recommendations')
            ->whereNull('recommendation_generation_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $orphaned = DB::table('career_recommendations')
                ->where('user_id', $userId)
                ->whereNull('recommendation_generation_id');

            $generatedAt = (clone $orphaned)->min('created_at')
                ?? now();

            $generationId = DB::table('recommendation_generations')
                ->insertGetId([
                    'user_id' => $userId,
                    'generation_number' => 1,
                    'status' => 'current',

                    /*
                     * The client that produced these rows was not
                     * recorded at the time, so it is not invented
                     * here.
                     */
                    'driver' => 'unknown',
                    'schema_version' => null,

                    'recommendation_count' => (clone $orphaned)->count(),
                    'generated_at' => $generatedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            (clone $orphaned)->update([
                'recommendation_generation_id' => $generationId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('career_recommendations', function (Blueprint $table) {
            $table->dropIndex([
                'recommendation_generation_id',
                'rank',
            ]);

            $table->dropConstrainedForeignId(
                'recommendation_generation_id'
            );
        });
    }
};
