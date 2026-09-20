<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make each placement a thing that can be configured.
     *
     * The two positions were constants in code, so changing how
     * a placement behaves meant changing the application. A slot
     * is a row instead: whether it rotates, how many
     * advertisements take part, and how long each is shown.
     */
    public function up(): void
    {
        Schema::create('advertisement_slots', function (Blueprint $table) {
            $table->id();

            /*
             * Matches advertisements.position, which is how an
             * advertisement says which placement it belongs to.
             */
            $table->string('position')->unique();

            $table->string('name');

            $table->boolean('rotation_enabled')->default(false);

            /*
             * How many advertisements take part when rotating.
             * One means the slot shows a single advertisement,
             * which is how both placements behaved before.
             */
            $table->unsignedTinyInteger('rotation_size')
                ->default(1);

            /*
             * How long each advertisement is shown, in seconds.
             * A video plays to its end first, so this is a floor
             * rather than a cut-off.
             */
            $table->unsignedSmallInteger('dwell_seconds')
                ->default(8);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        $this->seedExistingPlacements();
    }

    /**
     * Carry the two placements across as they behave today.
     */
    private function seedExistingPlacements(): void
    {
        foreach ([
            'one' => 'Above the page',
            'two' => 'Below the page',
        ] as $position => $name) {
            DB::table('advertisement_slots')->insert([
                'position' => $position,
                'name' => $name,
                'rotation_enabled' => false,
                'rotation_size' => 1,
                'dwell_seconds' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_slots');
    }
};
