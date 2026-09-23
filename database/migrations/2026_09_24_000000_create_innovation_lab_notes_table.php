<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('innovation_lab_notes', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->text('body');

            /*
             * Kept even if the admin account is later removed, so
             * a note does not vanish along with its author.
             */
            $table
                ->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Lets an admin draft a note before students see it,
             * without needing a separate drafts table.
             */
            $table
                ->boolean('is_published')
                ->default(true);

            $table->timestamps();

            $table->index(['is_published', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovation_lab_notes');
    }
};
