<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether the CPBN Bot may change the page by itself.
     *
     * On by default: asked to do something that happens on
     * another page, the bot goes there. A student who would
     * rather be given a link turns it off, by telling the bot or
     * in Settings, and it stays off on their account.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cpbn_bot_auto_navigate')
                ->default(true)
                ->after('show_ads');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cpbn_bot_auto_navigate');
        });
    }
};
