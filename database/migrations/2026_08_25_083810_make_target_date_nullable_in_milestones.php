<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            $table->date('target_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            $table->date('target_date')->nullable(false)->change();
        });
    }
};