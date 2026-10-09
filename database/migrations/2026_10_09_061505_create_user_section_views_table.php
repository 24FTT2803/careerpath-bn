<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When each user last opened a section (e.g. History), so
     * anything newer can be flagged with a "new" badge.
     */
    public function up(): void
    {
        Schema::create('user_section_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('section', 40);
            $table->timestamp('seen_at');
            $table->timestamps();

            $table->unique(['user_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_section_views');
    }
};
