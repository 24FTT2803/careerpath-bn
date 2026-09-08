<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique([
                'plan_id',
                'key',
            ]);
        });

        Schema::create('user_plan_grants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Kept as a flexible string so future sources
             * such as sponsorships or business grants do not
             * require changing this table structure.
             */
            $table->string('source')
                ->default('admin');

            $table->timestamp('starts_at')
                ->nullable();

            $table->timestamp('ends_at')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_active',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            /*
             * Student preference only.
             *
             * Whether ads actually render also depends on
             * the user's role, plan and future ad placement
             * configuration.
             */
            $table->boolean('show_ads')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_ads');
        });

        Schema::dropIfExists('user_plan_grants');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }
};