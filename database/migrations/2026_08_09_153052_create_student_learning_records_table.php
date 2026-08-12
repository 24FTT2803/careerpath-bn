<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_learning_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_title');
            $table->string('activity_type'); // course, workshop, seminar, self-study
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->integer('hours_spent')->default(0);
            $table->json('skills_learned')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_learning_records');
    }
};