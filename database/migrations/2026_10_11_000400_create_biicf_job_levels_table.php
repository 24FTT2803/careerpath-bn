<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The framework's named job levels, and which roles sit at
     * them.
     *
     * The BIICF gives every role a Job Level — Entrant,
     * Specialist, Expert/Management, Senior Management — and
     * several role pages name two of them ("Entrant or
     * Expert/Management"), which is why this is a pivot rather
     * than a column. One role in the book, Chief Information
     * Officer, is marked C-Suite, a level the framework's own
     * list of four does not mention; it is included here because
     * the role pages use it.
     *
     * biicf_job_roles.career_path_level stays as it is. That
     * integer orders the career path diagram; this is the level's
     * identity, and the two are not the same thing.
     */
    public function up(): void
    {
        Schema::create('biicf_job_levels', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            /*
             * Seniority order, used for sorting. Not a proficiency
             * scale and not comparable to one.
             */
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('biicf_job_role_job_level', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            $table->foreignId('job_level_id')
                ->constrained('biicf_job_levels')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['job_role_id', 'job_level_id'],
                'job_role_job_level_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_job_role_job_level');
        Schema::dropIfExists('biicf_job_levels');
    }
};
