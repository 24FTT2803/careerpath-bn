<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_certifications', function (Blueprint $table) {
            $table->string('certificate_original_name')
                ->nullable()
                ->after('certificate_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_certifications', function (Blueprint $table) {
            $table->dropColumn('certificate_original_name');
        });
    }
};