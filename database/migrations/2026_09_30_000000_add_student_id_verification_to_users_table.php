<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track who confirmed each student's Politeknik Brunei ID,
     * and tidy existing IDs into the stored form (uppercase,
     * no spaces) so "24ftt2803" and "24FTT2803" can never be
     * two different students.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('student_id_verified_at')->nullable()->after('student_id');
            $table->foreignId('student_id_verified_by')
                ->nullable()
                ->after('student_id_verified_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('users')
            ->whereNotNull('student_id')
            ->orderBy('id')
            ->get(['id', 'student_id'])
            ->each(function (object $user): void {
                $normalised = strtoupper(preg_replace('/\s+/', '', $user->student_id));

                if ($normalised === $user->student_id) {
                    return;
                }

                /*
                 * Leave the row alone if tidying it would clash
                 * with another account; an admin sorts that out.
                 */
                $taken = DB::table('users')
                    ->where('id', '!=', $user->id)
                    ->where('student_id', $normalised)
                    ->exists();

                if (! $taken) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['student_id' => $normalised === '' ? null : $normalised]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id_verified_by');
            $table->dropColumn('student_id_verified_at');
        });
    }
};