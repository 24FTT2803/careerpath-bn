<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Special accounts, set up by an administrator: for showing
     * the platform, or for staff trying it out.
     *
     * special_rules holds what is special about the account:
     * unlimited AI use, Premium without paying, no advertising,
     * and which roles it may switch between. home_role is the
     * role it was given, kept while it is switched to another,
     * so it can always go back.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('special_rules')->nullable()->after('role');
            $table->string('home_role', 20)->nullable()->after('special_rules');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['special_rules', 'home_role']);
        });
    }
};
