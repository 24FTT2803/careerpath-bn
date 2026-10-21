<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saying you have none.
     *
     * A profile counts projects and certifications towards being
     * complete, so a student with neither could never finish one
     * — and plenty have neither. A first-year has not built a
     * portfolio yet and has sat no certification, which is not an
     * incomplete profile, it is a complete one with nothing in
     * those sections.
     *
     * An empty section cannot say this on its own: it means
     * either "none" or "not filled in yet", and the difference is
     * exactly what is being asked. So the student says which.
     *
     * Only true while the section is empty. Adding a project
     * answers the question better than the checkbox did, so the
     * declaration gives way to it.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->boolean('declared_no_projects')
                ->default(false)
                ->after('profile_picture');

            $table->boolean('declared_no_certifications')
                ->default(false)
                ->after('declared_no_projects');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['declared_no_projects', 'declared_no_certifications']);
        });
    }
};
