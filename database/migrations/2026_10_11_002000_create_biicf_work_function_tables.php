<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A role's duties as the book structures them, rather than as
     * one block of text.
     *
     * Both were single columns with newlines in them, which could
     * hold a flat list and nothing else. The book is not flat:
     *
     *   Job Description
     *     ▷ Provide support functions ranging from setting up
     *       technology equipment to maintaining internal networks
     *     ▷ Work with IT colleagues, staff at all levels,
     *       external clients and vendors
     *
     *   Critical Work Functions
     *     Generic Functions
     *       ▷ Install, maintain and upgrade desktop hardware
     *     Specific Functions
     *       Desktop and Application Support
     *         ▷ Install and test new ICT equipment
     *       Applications Configuration and Installation
     *         ▷ Configuration and Installation
     *           ○ Follow an acceptance test procedure on new
     *             ICT equipment and report results
     *
     * So a critical work function needs three things a list
     * cannot give it: whether it is generic to the role or
     * specific to one of the role's job families, which family
     * that is, and a parent for the second level of bullets.
     *
     * The old columns stay for now. Everything still reads them,
     * and they are dropped once the admin pages and the explorer
     * have moved across.
     */
    public function up(): void
    {
        Schema::create('biicf_job_description_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            $table->text('text');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['job_role_id', 'sort_order']);
        });

        Schema::create('biicf_critical_work_functions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            /*
             * Generic functions belong to the role as a whole.
             * Specific ones belong to one of its job families,
             * which is why the family is only set for those.
             */
            $table->string('kind', 20)->default('generic');

            $table->foreignId('job_family_id')
                ->nullable()
                ->constrained('biicf_job_families')
                ->restrictOnDelete();

            /*
             * The book nests two levels deep under some families:
             * a heading bullet with its own sub-bullets. A parent
             * is how a sub-bullet knows which heading it sits
             * under.
             */
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('biicf_critical_work_functions')
                ->cascadeOnDelete();

            $table->text('text');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['job_role_id', 'kind', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_critical_work_functions');
        Schema::dropIfExists('biicf_job_description_points');
    }
};
