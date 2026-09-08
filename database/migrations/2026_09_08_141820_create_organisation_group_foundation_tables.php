<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('organisation_group_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->timestamps();

            $table->unique([
                'organisation_id',
                'name',
            ]);
        });

        Schema::create('organisation_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organisation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('group_type_id')
                ->constrained('organisation_group_types')
                ->restrictOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('organisation_groups')
                ->nullOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique([
                'organisation_id',
                'code',
            ]);
        });

        Schema::create('group_memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('organisation_group_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'user_id',
                'organisation_group_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_memberships');
        Schema::dropIfExists('organisation_groups');
        Schema::dropIfExists('organisation_group_types');
        Schema::dropIfExists('organisations');
    }
};