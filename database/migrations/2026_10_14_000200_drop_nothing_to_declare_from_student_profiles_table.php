<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two columns a checkbox used to fill, and nothing does now.
     *
     * Projects and certifications counted towards a complete
     * profile, so a student with neither could not finish one.
     * The first answer to that was a box to tick saying you had
     * none — which made the student do ceremony to say the
     * ordinary thing, and left a column recording it.
     *
     * The simpler reading is that the two sections are optional.
     * Having none is the ordinary case and needs no declaring;
     * adding one is the statement, and the row is where it is
     * recorded. So the completion no longer counts them, the
     * checkbox is gone, and so are these.
     *
     * down() puts them back, empty, for a database that still
     * wants to ask the question.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['declared_no_projects', 'declared_no_certifications']);
        });
    }

    public function down(): void
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
};
