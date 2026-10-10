<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Move what is already stored into the new tables.
     *
     * critical_work_function holds one duty per line, so each
     * line becomes a generic function in the order it was
     * written. None of it is attributed to a job family or
     * nested, because the text records neither — that structure
     * arrives with the real framework data.
     *
     * job_description is prose rather than a list, so it becomes
     * a single point. Splitting a paragraph on sentence
     * boundaries would invent a structure the data does not
     * have.
     *
     * Nothing is removed. The columns stay until the pages
     * reading them have moved across.
     */
    public function up(): void
    {
        $now = now();

        $roles = DB::table('biicf_job_roles')
            ->select('id', 'job_description', 'critical_work_function')
            ->get();

        $points = [];
        $functions = [];

        foreach ($roles as $role) {
            $description = trim((string) $role->job_description);

            if ($description !== '') {
                $points[] = [
                    'job_role_id' => $role->id,
                    'text' => $description,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($this->lines($role->critical_work_function) as $index => $line) {
                $functions[] = [
                    'job_role_id' => $role->id,
                    'kind' => 'generic',
                    'job_family_id' => null,
                    'parent_id' => null,
                    'text' => $line,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        /*
         * Chunked because a role can carry a dozen functions and
         * there are forty roles, which is more placeholders than
         * some drivers accept in one statement.
         */
        foreach (array_chunk($points, 100) as $chunk) {
            DB::table('biicf_job_description_points')->insert($chunk);
        }

        foreach (array_chunk($functions, 100) as $chunk) {
            DB::table('biicf_critical_work_functions')->insert($chunk);
        }
    }

    public function down(): void
    {
        /*
         * The text columns were never emptied, so there is
         * nothing to restore — only the copies to clear out.
         */
        DB::table('biicf_critical_work_functions')->delete();
        DB::table('biicf_job_description_points')->delete();
    }

    /**
     * The non-empty lines of a newline-separated value.
     *
     * @return array<int, string>
     */
    private function lines(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $value) ?: []),
            fn (string $line) => $line !== ''
        ));
    }
};
