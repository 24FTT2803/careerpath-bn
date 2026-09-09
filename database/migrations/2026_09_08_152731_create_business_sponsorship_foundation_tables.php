<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sponsored_access_grants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_sponsor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            /*
             * Every grant belongs to an organisation.
             *
             * If organisation_group_id is null, the grant
             * applies organisation-wide.
             *
             * If it is present, the grant applies to that
             * group and users within its descendant groups.
             */
            $table->foreignId('organisation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('organisation_group_id')
                ->nullable()
                ->constrained('organisation_groups')
                ->cascadeOnDelete();

            $table->timestamp('starts_at')
                ->nullable();

            $table->timestamp('ends_at')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            /*
             * Allows overlapping sponsorships to be
             * resolved deliberately later through Admin
             * configuration.
             */
            $table->integer('priority')
                ->default(0);

            $table->timestamps();

            $table->index([
                'organisation_id',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'sponsored_access_grants'
        );

        Schema::dropIfExists(
            'business_sponsors'
        );
    }
};