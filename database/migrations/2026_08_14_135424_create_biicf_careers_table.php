<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('biicf_careers', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->string('subsector');
            $table->json('technical_skills')->nullable();
            $table->json('soft_skills')->nullable();
            $table->json('entry_requirements')->nullable();
            $table->json('recommended_training')->nullable();
            $table->json('certifications')->nullable();
            $table->text('job_description')->nullable();
            $table->string('demand_level')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('biicf_careers');
    }
};