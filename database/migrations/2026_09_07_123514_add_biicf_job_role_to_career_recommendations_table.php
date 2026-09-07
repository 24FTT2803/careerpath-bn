<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_recommendations', function (Blueprint $table) {
            /*
             * Legacy recommendations use biicf_career_id.
             * Current BIICF recommendations use biicf_job_role_id.
             */
            $table->foreignId('biicf_career_id')
                ->nullable()
                ->change();

            $table->foreignId('biicf_job_role_id')
                ->nullable()
                ->after('biicf_career_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        /*
         * Rows using only the current BIICF job-role relationship
         * cannot exist after reverting to the legacy schema.
         */
        DB::table('career_recommendations')
            ->whereNull('biicf_career_id')
            ->delete();

        Schema::table('career_recommendations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('biicf_job_role_id');

            $table->foreignId('biicf_career_id')
                ->nullable(false)
                ->change();
        });
    }
};