<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('institution_name');
            $table->string('programme_name');
            $table->string('level'); // Diploma, Degree, etc.
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('cgpa', 3, 2)->nullable();
            $table->json('subjects')->nullable();
            $table->json('grades')->nullable();
            $table->text('achievements')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_records');
    }
};