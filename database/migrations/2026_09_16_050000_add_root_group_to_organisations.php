<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Record which group an organisation's structure hangs from.
     *
     * The root was created alongside the organisation but the
     * link was only implied, so nothing stopped the root being
     * deleted from the tree. That left an organisation with no
     * structure and no way to tell.
     */
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table
                ->foreignId('root_group_id')
                ->nullable()
                ->after('code')
                ->constrained('organisation_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('root_group_id');
        });
    }
};
