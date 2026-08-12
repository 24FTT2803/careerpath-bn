<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_aspirations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('career_goals')->nullable();
            $table->json('preferred_industries')->nullable();
            $table->json('preferred_work_activities')->nullable();
            $table->text('vision_statement')->nullable();
            $table->text('mission_statement')->nullable();
            $table->text('long_term_goals')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_aspirations');
    }
};