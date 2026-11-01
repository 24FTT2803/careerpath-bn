<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The part of a video advertisement students see.
     *
     * A video cannot be cut in the browser the way an image is,
     * and the server has no video tools. So the clip is kept as
     * it was uploaded, and the frame the administrator chose is
     * stored as fractions of the picture (x, y, width, height)
     * and shown through the banner's 6:1 window.
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->json('video_crop')
                ->nullable()
                ->after('asset_path');
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn('video_crop');
        });
    }
};
