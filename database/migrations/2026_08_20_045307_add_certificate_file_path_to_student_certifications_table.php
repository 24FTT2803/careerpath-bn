<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_certifications', function (Blueprint $table) {
            $table->string('issuing_organization')->nullable()->change();
            $table->date('issue_date')->nullable()->change();

            $table->string('certificate_file_path')
                ->nullable()
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('student_certifications', function (Blueprint $table) {
            $table->string('issuing_organization')->nullable(false)->change();
            $table->date('issue_date')->nullable(false)->change();

            $table->dropColumn('certificate_file_path');
        });
    }
};