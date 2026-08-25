<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ICT Sub-sectors
        Schema::create('biicf_sub_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('lead_organisation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Job Roles (belongs to a sub-sector)
        Schema::create('biicf_job_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_sector_id')->constrained('biicf_sub_sectors')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('functional_group')->nullable();
            $table->text('job_description')->nullable();
            $table->text('critical_work_function')->nullable(); // key duties, one per line
            $table->json('alternative_titles')->nullable(); // e.g. ["IT Generalist","Network Administrator"]
            $table->unsignedTinyInteger('career_path_level')->default(0); // 0 = entry level, rising = seniority
            $table->string('box_colour', 20)->default('primary'); // matches BIICF's visual tiering
            $table->timestamps();
        });

        // 3. Career Paths — progression edges between job roles (a role can lead to 1+ next roles)
        Schema::create('biicf_career_path_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_job_role_id')->constrained('biicf_job_roles')->cascadeOnDelete();
            $table->foreignId('to_job_role_id')->constrained('biicf_job_roles')->cascadeOnDelete();
            $table->text('notes')->nullable(); // e.g. typical years of experience before progressing
            $table->timestamps();
            $table->unique(['from_job_role_id', 'to_job_role_id']);
        });

        // 4. Proficiency Levels (shared reference scale, e.g. Entrant / Specialist / Expert-Management)
        Schema::create('biicf_proficiency_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level_number'); // 1..6
            $table->string('name'); // e.g. "Entrant", "Specialist", "Expert / Management", "Senior"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. Competencies (technical or soft-skill)
        Schema::create('biicf_competencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['technical', 'soft_skill']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot: which competencies a job role needs, and at what proficiency level
        Schema::create('biicf_job_role_competency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_role_id')->constrained('biicf_job_roles')->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('biicf_competencies')->cascadeOnDelete();
            $table->foreignId('proficiency_level_id')->constrained('biicf_proficiency_levels');
            $table->boolean('is_core')->default(true); // core vs supporting competency
            $table->timestamps();
            $table->unique(['job_role_id', 'competency_id'], 'jr_comp_unique');
        });

        // 6. Entry Requirements (one-to-one-ish with job role, but modelled as its own table for flexibility)
        Schema::create('biicf_entry_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_role_id')->constrained('biicf_job_roles')->cascadeOnDelete();
            $table->string('bdqf_level')->nullable(); // e.g. "BDQF Level 6"
            $table->string('field_of_study')->nullable(); // e.g. "IT, Computer Science or related field"
            $table->text('alternative_pathway')->nullable(); // e.g. equivalent work experience route
            $table->text('years_experience')->nullable();
            $table->timestamps();
        });

        // 7. Training & Certifications
        Schema::create('biicf_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('certification_body')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot: which trainings are recommended for which job role
        Schema::create('biicf_job_role_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_role_id')->constrained('biicf_job_roles')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained('biicf_trainings')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['job_role_id', 'training_id'], 'jr_training_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_job_role_training');
        Schema::dropIfExists('biicf_trainings');
        Schema::dropIfExists('biicf_entry_requirements');
        Schema::dropIfExists('biicf_job_role_competency');
        Schema::dropIfExists('biicf_competencies');
        Schema::dropIfExists('biicf_proficiency_levels');
        Schema::dropIfExists('biicf_career_path_edges');
        Schema::dropIfExists('biicf_job_roles');
        Schema::dropIfExists('biicf_sub_sectors');
    }
};