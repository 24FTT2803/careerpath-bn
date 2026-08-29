<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            $table->string('proof_file_path')->nullable()->after('completed_date');
            $table->timestamp('proof_submitted_at')->nullable()->after('proof_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            $table->dropColumn(['proof_file_path', 'proof_submitted_at']);
        });
    }
};