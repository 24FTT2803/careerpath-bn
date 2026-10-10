<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job families — the framework's narrower grouping inside a
     * functional group.
     *
     * A role can belong to several: IT Technician lists three
     * (server and network support; desktop and peripherals
     * installation and maintenance; applications configuration
     * and installation, and IT security). They matter beyond
     * labelling, because the book groups a role's specific
     * critical work functions under the family they belong to,
     * so those functions need a family to point at.
     */
    public function up(): void
    {
        Schema::create('biicf_job_families', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('biicf_job_role_job_family', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            $table->foreignId('job_family_id')
                ->constrained('biicf_job_families')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['job_role_id', 'job_family_id'],
                'job_role_job_family_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_job_role_job_family');
        Schema::dropIfExists('biicf_job_families');
    }
};
