<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_usages', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string(
                'feature_key',
                191
            );

            /*
             * Preserve the access context under which
             * this usage occurred.
             *
             * Sources correspond to EntitlementService:
             * default, direct, sponsored.
             */
            $table->string(
                'access_source',
                32
            );

            $table
                ->foreignId('plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            /*
             * A grant may be either UserPlanGrant or
             * SponsoredAccessGrant, so it is intentionally
             * not a foreign key to one specific table.
             */
            $table
                ->unsignedBigInteger('grant_id')
                ->nullable();

            $table->timestamp('used_at');

            $table->timestamps();

            $table->index(
                [
                    'user_id',
                    'feature_key',
                    'used_at',
                ],
                'feature_usages_user_feature_time_idx'
            );

            $table->index(
                [
                    'user_id',
                    'feature_key',
                    'access_source',
                    'plan_id',
                    'grant_id',
                ],
                'feature_usages_access_context_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'feature_usages'
        );
    }
};