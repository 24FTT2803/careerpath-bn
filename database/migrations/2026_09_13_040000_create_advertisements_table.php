<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            /*
             * One table serves every kind of advertisement.
             *
             * image   - uploaded still
             * video   - uploaded short clip
             * link    - text or image pointing at an external URL
             * network - embed from an ad network
             *
             * A separate table per kind would multiply the admin
             * screens and the rendering paths for no gain.
             */
            $table->string(
                'type',
                16
            )->default('image');

            /*
             * Either an uploaded asset or an external address.
             * Admins choose per advertisement, so both are
             * nullable and validation enforces one of them.
             */
            $table
                ->string('asset_path')
                ->nullable();

            $table
                ->string('external_url')
                ->nullable();

            /*
             * Where the advertisement sends the student. Kept
             * apart from external_url because a hosted image can
             * still be clickable.
             */
            $table
                ->string('click_url')
                ->nullable();

            /*
             * Free text shown when an image cannot load, and
             * read aloud by screen readers.
             */
            $table
                ->string('alt_text')
                ->nullable();

            /*
             * one or two. Positions relocate by viewport rather
             * than appearing and disappearing, so the number of
             * advertisements a page shows stays constant.
             */
            $table->string(
                'position',
                16
            );

            $table
                ->boolean('is_active')
                ->default(true);

            $table
                ->timestamp('starts_at')
                ->nullable();

            $table
                ->timestamp('ends_at')
                ->nullable();

            /*
             * Optional targeting. Null means every eligible
             * student sees it, which is the common case.
             */
            $table
                ->foreignId('organisation_group_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'position',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
