<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mark the record that is the student's Politeknik programme.
 *
 * The profile asked for a current programme in a section of its
 * own and for qualifications in another, which put every
 * Politeknik student's own programme in two places at once and
 * asked a post-graduate — who has no current studies — to fill in
 * a section built around having some. The programme now appears
 * as a qualification like any other, pre-filled and not
 * removable.
 *
 * Which record that is has to be a fact rather than a guess.
 * Matching on the institution's name and a current flag works
 * until a student holds two Politeknik awards, or finishes the
 * one they are on, and then the form would silently adopt a
 * different row as the student's programme.
 *
 * users.programme stays where it is and stays authoritative:
 * forty-odd readers depend on it, from the AI payload to the
 * lecturer's class lists. This flag says which qualification
 * mirrors it.
 */
return new class extends Migration
{
    private const INSTITUTION = 'Politeknik Brunei';

    public function up(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            $table->boolean('is_programme_record')
                ->default(false)
                ->after('is_current');
        });

        $this->markExistingProgrammes();
    }

    /**
     * Adopt the record each student's programme already has.
     *
     * One per student, and only where there is no doubt which:
     * the Politeknik record naming the programme on the account,
     * or failing that the earliest Politeknik record. A student
     * with neither gets no flag and the form offers them an empty
     * programme row, which is the right answer for somebody who
     * has not said what they study.
     */
    private function markExistingProgrammes(): void
    {
        $students = DB::table('users')
            ->where('role', 'student')
            ->get(['id', 'programme']);

        foreach ($students as $student) {
            $records = DB::table('academic_records')
                ->where('user_id', $student->id)
                ->where('institution_name', self::INSTITUTION)
                ->orderBy('id')
                ->get(['id', 'programme_name']);

            if ($records->isEmpty()) {
                continue;
            }

            $named = $records->firstWhere(
                'programme_name',
                $student->programme
            );

            DB::table('academic_records')
                ->where('id', ($named ?? $records->first())->id)
                ->update(['is_programme_record' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            $table->dropColumn('is_programme_record');
        });
    }
};
