<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The languages a role is expected to know, as their own
     * section.
     *
     * The book gives them one, set beneath the courses and
     * headed like every other section:
     *
     *   Recommended Technical Training Courses
     *     ▷ Node.js
     *   Programming Languages
     *     Programming Languages in Demand (JavaScript, Python,
     *     HTML, CSS, Java, SQL, NoSQL, C#, C++, Rust, Perl, Go,
     *     PHP, R, Ruby, Swift)
     *   Skills & Competencies
     *
     * A reader that did not know the heading carried straight on
     * from the courses, so the section arrived as three more
     * courses: the heading itself, the list up to where the line
     * wrapped, and the rest of the list as a course of its own.
     * They are not courses — nothing offers them and nothing
     * certifies them.
     *
     * One role in the second edition has the section, which is
     * why the languages are written out here. They are moved
     * rather than read again: the training row they were flattened
     * into lost the second half of the list, and the book's own
     * line is on the facing page of this comment.
     */
    public function up(): void
    {
        Schema::create('biicf_job_programming_languages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_role_id')
                ->constrained('biicf_job_roles')
                ->cascadeOnDelete();

            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['job_role_id', 'name']);
            $table->index(['job_role_id', 'sort_order']);
        });

        $this->moveThemOffTheCourses();
    }

    public function down(): void
    {
        Schema::dropIfExists('biicf_job_programming_languages');
    }

    /**
     * Give the section's languages to whichever roles were
     * holding them as courses, and drop the courses.
     *
     * Matched on the wording the flattening produced rather than
     * on the role, so a database that never had the mistake, or
     * had it under another role, comes out the same.
     */
    private function moveThemOffTheCourses(): void
    {
        $flattened = DB::table('biicf_trainings')
            ->where('name', 'like', 'Programming Languages%')
            ->orWhere('name', 'like', '%Ruby, Swift)')
            ->pluck('id');

        if ($flattened->isEmpty()) {
            return;
        }

        $roles = DB::table('biicf_job_role_training')
            ->whereIn('training_id', $flattened)
            ->pluck('job_role_id')
            ->unique();

        $now = now();

        foreach ($roles as $roleId) {
            $rows = [];

            foreach ($this->inDemand() as $index => $name) {
                $rows[] = [
                    'job_role_id' => $roleId,
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('biicf_job_programming_languages')->insert($rows);
        }

        DB::table('biicf_job_role_training')
            ->whereIn('training_id', $flattened)
            ->delete();

        DB::table('biicf_trainings')->whereIn('id', $flattened)->delete();
    }

    /**
     * The list as the second edition prints it, in its order.
     *
     * @return array<int, string>
     */
    private function inDemand(): array
    {
        return [
            'JavaScript', 'Python', 'HTML', 'CSS', 'Java', 'SQL', 'NoSQL',
            'C#', 'C++', 'Rust', 'Perl', 'Go', 'PHP', 'R', 'Ruby', 'Swift',
        ];
    }
};
