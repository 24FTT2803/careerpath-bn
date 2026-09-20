<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let an administrator decide the order within a placement.
     *
     * The order used to fall out of how the query happened to
     * be built, which meant nobody could say which
     * advertisement a student would see first.
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('position');

            $table->index(['position', 'sort_order']);
        });

        $this->seedExistingOrder();
    }

    /**
     * Keep what is there in the order it was created.
     */
    private function seedExistingOrder(): void
    {
        foreach (['one', 'two'] as $position) {
            $advertisements = DB::table('advertisements')
                ->where('position', $position)
                ->orderBy('id')
                ->pluck('id');

            foreach ($advertisements as $index => $id) {
                DB::table('advertisements')
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropIndex(['position', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
