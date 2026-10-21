<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give a student's qualifications a level the framework can read.
 *
 * Entry requirements are stated as BDQF levels, so matching one
 * needs the student's qualifications on the same ladder. The
 * table already holds a free-text level — "Diploma", "Degree" —
 * which reads well and compares with nothing.
 *
 * A student can hold several qualifications, which is what this
 * table was always for; until now the profile recorded only the
 * one programme on the user. That programme is copied in here so
 * a match works for students who have already filled in their
 * profile.
 */
return new class extends Migration
{
    /**
     * What Politeknik Brunei's diplomas are treated as.
     *
     * Annex 1 puts a plain Diploma at level 4 and Advanced
     * Diploma or HND at 5. PB's diplomas are taken as level 5
     * on the institution's own reading, which is a default
     * rather than a reading of the Annex: a student whose award
     * sits elsewhere on the ladder can say so on their record.
     */
    private const POLITEKNIK_DIPLOMA_LEVEL = 5;

    public function up(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            /*
             * Null where nobody has said which rung the award
             * sits on. A match reports that as undetermined
             * rather than assuming the bottom of the ladder.
             */
            $table->unsignedTinyInteger('bdqf_level')
                ->nullable()
                ->after('level');

            $table->string('field_of_study')
                ->nullable()
                ->after('bdqf_level');
        });

        $this->copyProgrammesIn();
    }

    /**
     * Give every student who named a programme a record for it.
     *
     * Skipped for anyone who already has one: the profile pages
     * have never written to this table, but a seeder or an
     * import may have.
     */
    private function copyProgrammesIn(): void
    {
        $existing = DB::table('academic_records')->pluck('user_id')->all();

        $students = DB::table('users')
            ->where('role', 'student')
            ->whereNotNull('programme')
            ->where('programme', '!=', '')
            ->whereNotIn('id', $existing ?: [0])
            ->get(['id', 'programme']);

        $now = now();

        foreach ($students as $student) {
            DB::table('academic_records')->insert([
                'user_id' => $student->id,
                'institution_name' => 'Politeknik Brunei',
                'programme_name' => $student->programme,
                'level' => 'Diploma',
                'bdqf_level' => self::POLITEKNIK_DIPLOMA_LEVEL,
                'field_of_study' => $student->programme,
                /*
                 * Studying rather than held: the students on the
                 * system are on their programme now, and a
                 * qualification not yet awarded should not count
                 * as one a role can be entered with.
                 */
                'is_current' => true,
                'start_date' => $now->copy()->startOfYear(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        /*
         * Only the records this migration wrote. A qualification
         * a student has since added by hand is not this
         * migration's to remove.
         */
        DB::table('academic_records')
            ->where('institution_name', 'Politeknik Brunei')
            ->where('level', 'Diploma')
            ->whereColumn('created_at', 'updated_at')
            ->delete();

        Schema::table('academic_records', function (Blueprint $table) {
            $table->dropColumn(['bdqf_level', 'field_of_study']);
        });
    }
};
