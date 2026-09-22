<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturer_assignments', function (Blueprint $table) {
            $table->id();

            /*
             * The lecturer. Cascade on delete so removing a
             * lecturer account cleans up their assignments.
             */
            $table
                ->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * The class they teach. Cascade on delete so
             * removing a class removes the teaching
             * assignment with it.
             */
            $table
                ->foreignId('organisation_group_id')
                ->constrained('organisation_groups')
                ->cascadeOnDelete();

            $table->timestamps();

            /*
             * A lecturer can only be assigned to a class once.
             * The unique constraint makes double-assignment
             * impossible without a check in the controller.
             */
            $table->unique(
                ['user_id', 'organisation_group_id'],
                'lecturer_assignment_unique'
            );

            $table->index('organisation_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_assignments');
    }
};