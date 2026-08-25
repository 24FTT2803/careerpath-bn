<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_projects', function (Blueprint $table) {
            $table->json('technologies_used')->nullable()->after('description');
            $table->json('team_members')->nullable()->after('technologies_used');
            $table->string('role')->nullable()->after('team_members');
            $table->string('project_url')->nullable()->after('role');
            $table->date('start_date')->nullable()->after('project_url');
            $table->date('end_date')->nullable()->after('start_date');
            $table->text('achievements')->nullable()->after('end_date');
        });
    }

    public function down()
    {
        Schema::table('student_projects', function (Blueprint $table) {
            $table->dropColumn([
                'technologies_used',
                'team_members',
                'role',
                'project_url',
                'start_date',
                'end_date',
                'achievements'
            ]);
        });
    }
};