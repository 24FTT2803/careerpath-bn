<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Three columns the framework has nothing to put in.
     *
     * A training's provider and certification body, and a
     * sub-sector's lead organisation, were guesses at what the
     * book might say. It says none of them: a recommended course
     * is a name and nothing else, and a sub-sector is a name and
     * the roles under it. Nothing has ever written them, so every
     * row is null, and the admin pages showed three columns of
     * dashes and three fields nobody could fill.
     *
     * Dropped rather than hidden. A column that is always null is
     * a question every reader of the schema has to ask, and the
     * answer was only ever "that was never in the book".
     */
    public function up(): void
    {
        Schema::table('biicf_trainings', function (Blueprint $table) {
            $table->dropColumn(['provider', 'certification_body']);
        });

        Schema::table('biicf_sub_sectors', function (Blueprint $table) {
            $table->dropColumn('lead_organisation');
        });
    }

    public function down(): void
    {
        Schema::table('biicf_trainings', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('name');
            $table->string('certification_body')->nullable()->after('provider');
        });

        Schema::table('biicf_sub_sectors', function (Blueprint $table) {
            $table->string('lead_organisation')->nullable()->after('description');
        });
    }
};
