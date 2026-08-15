<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_recommendations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('biicf_career_id')
                ->constrained('biicf_careers')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rank');

            $table->decimal('match_score', 5, 2);
            $table->decimal('career_readiness_score', 5, 2);

            $table->text('explanation')->nullable();

            $table->json('matched_skills')->nullable();
            $table->json('skill_gaps')->nullable();
            $table->json('development_plan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_recommendations');
    }
};