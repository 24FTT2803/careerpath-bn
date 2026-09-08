<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('value_type');
            $table->string('parent_key')->nullable();
            $table->boolean('global_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('parent_key');
        });

        Schema::create(
            'sponsored_access_feature_overrides',
            function (Blueprint $table) {
                $table->id();

                /*
                * Explicit short constraint names are used
                * because MySQL limits identifiers to
                * 64 characters.
                */
                $table->foreignId(
                    'sponsored_access_grant_id'
                );

                $table->foreign(
                    'sponsored_access_grant_id',
                    'saf_override_grant_fk'
                )
                    ->references('id')
                    ->on('sponsored_access_grants')
                    ->cascadeOnDelete();

                $table->foreignId(
                    'feature_definition_id'
                );

                $table->foreign(
                    'feature_definition_id',
                    'saf_override_feature_fk'
                )
                    ->references('id')
                    ->on('feature_definitions')
                    ->cascadeOnDelete();

                $table->json('value')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'sponsored_access_grant_id',
                        'feature_definition_id',
                    ],
                    'saf_override_grant_feature_unique'
                );
            }
        );

        Schema::table(
            'sponsored_access_grants',
            function (Blueprint $table) {
                /*
                 * Commercial/funding classification is
                 * deliberately separate from entitlement.
                 *
                 * A sponsorship can therefore be paid,
                 * complimentary, a partnership, internal,
                 * or another arrangement without requiring
                 * any payment gateway.
                 */
                $table->string('funding_type')
                    ->default('complimentary');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'sponsored_access_feature_overrides'
        );

        Schema::dropIfExists(
            'feature_definitions'
        );

        Schema::table(
            'sponsored_access_grants',
            function (Blueprint $table) {
                $table->dropColumn(
                    'funding_type'
                );
            }
        );
    }
};