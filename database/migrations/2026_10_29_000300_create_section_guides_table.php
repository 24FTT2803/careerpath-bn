<?php

use App\Support\SectionGuideDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The help behind the '?' icons on Edit Profile and Career
 * Analysis.
 *
 * One row per section, keyed by where it appears. Administrators
 * edit the text and may add a video; the sections themselves are
 * fixed by the pages, so rows are never created or deleted from
 * the admin side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_guides', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('page');
            $table->string('title');
            $table->text('body');
            $table->string('video_url')->nullable();
            $table->timestamps();
        });

        SectionGuideDefaults::insertMissing();
    }

    public function down(): void
    {
        Schema::dropIfExists('section_guides');
    }
};
