<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The five schools Politeknik Brunei runs, by their own
     * abbreviations.
     */
    private const SCHOOLS = [
        'SBS' => 'School of Business',
        'SICT' => 'School of Information and Communication Technology',
        'SSE' => 'School of Science and Engineering',
        'SHS' => 'School of Health Sciences',
        'SPC' => 'School of Petrochemical',
    ];

    /**
     * Level 5 Diploma programmes, school code => [code => name].
     *
     * A null name marks a programme that already exists here
     * under that code. It is only found and placed, never
     * renamed, because students point at it by key and its name
     * is copied into their profiles and archived reports.
     */
    private const PROGRAMMES = [
        'SBS' => [
            'DBAF' => 'Diploma in Business Accounting and Finance',
            'DEMS' => 'Diploma in Entrepreneurship and Marketing Strategies',
            'DHCM' => 'Diploma in Human Capital Management',
            'DBTM' => 'Diploma in Business and Tourism Management',
        ],
        'SICT' => [
            'DADT' => null,
            'DCNG' => null,
            'DCSY' => null,
            'DDAT' => null,
            'DDAM' => null,
            'DDSL' => 'Diploma in Digital Solutions',
        ],
        'SSE' => [
            'DARC' => 'Diploma in Architecture',
            'DINT' => 'Diploma in Interior Design',
            'DCVL' => 'Diploma in Civil Engineering',
            'DELE' => 'Diploma in Electrical Engineering',
            'DECE' => 'Diploma in Electronic and Communication Engineering',
            'DMEC' => 'Diploma in Mechanical Engineering',
            'DPET' => 'Diploma in Petroleum Engineering',
        ],
        'SHS' => [
            'DNUR' => 'Diploma in Health Science (Nursing)',
            'DMID' => 'Diploma in Health Science (Midwifery)',
            'DPAR' => 'Diploma in Health Science (Paramedic)',
            'DCVT' => 'Diploma in Health Science (Cardiovascular Technology)',
            'DPUH' => 'Diploma in Health Science (Public Health)',
        ],
        'SPC' => [
            'DAST' => 'Diploma in Applied Science Technology',
            'DPLE' => 'Diploma in Plant Engineering',
        ],
    ];

    /**
     * Bring the Politeknik Brunei structure in line with the
     * schools and programmes it currently runs.
     *
     * Only adds what is missing. Nothing is renamed, moved or
     * deleted, no membership is touched, and running it again
     * changes nothing. The one exception is a programme that
     * Politeknik Brunei no longer lists and nobody uses, which
     * is archived rather than deleted so it can be restored.
     */
    public function up(): void
    {
        $organisation = DB::table('organisations')
            ->where('code', 'PB')
            ->first();

        /*
         * A database without Politeknik Brunei is a fresh one,
         * which the seeder builds. Nothing to align yet.
         */
        if ($organisation === null) {
            return;
        }

        DB::transaction(function () use ($organisation) {
            $schoolType = $this->typeId($organisation->id, 'School');
            $programmeType = $this->typeId($organisation->id, 'Programme');

            $rootId = $organisation->root_group_id ?? null;

            $schoolIds = [];

            foreach (self::SCHOOLS as $code => $name) {
                $id = $this->ensureGroup(
                    $organisation->id,
                    $code,
                    $name,
                    $schoolType
                );

                if ($id === null) {
                    continue;
                }

                $schoolIds[$code] = $id;

                $this->placeIfUnplaced($id, $rootId);
            }

            foreach (self::PROGRAMMES as $schoolCode => $programmes) {
                $schoolId = $schoolIds[$schoolCode] ?? null;

                if ($schoolId === null) {
                    continue;
                }

                foreach ($programmes as $code => $name) {
                    $id = $this->ensureGroup(
                        $organisation->id,
                        $code,
                        $name,
                        $programmeType
                    );

                    if ($id !== null) {
                        $this->placeIfUnplaced($id, $schoolId);
                    }
                }
            }

            $this->archiveIfUnused($organisation->id, 'DBIS');
        });
    }

    /**
     * Left in place deliberately.
     *
     * By the time anyone reverses this, students and classes
     * may sit under the groups it added, and removing those
     * would take them with it.
     */
    public function down(): void {}

    private function typeId(int $organisationId, string $name): int
    {
        $existing = DB::table('organisation_group_types')
            ->where('organisation_id', $organisationId)
            ->where('name', $name)
            ->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return (int) DB::table('organisation_group_types')->insertGetId([
            'organisation_id' => $organisationId,
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Find the group, or create it. Returns null when the code is
     * already taken by a group that is clearly something else, so
     * that one is never adopted by mistake.
     */
    private function ensureGroup(
        int $organisationId,
        string $code,
        ?string $name,
        int $typeId
    ): ?int {
        $byCode = DB::table('organisation_groups')
            ->where('organisation_id', $organisationId)
            ->where('code', $code)
            ->first();

        if ($byCode !== null) {
            if ($name !== null && ! $this->sameName($byCode->name, $name)) {
                Log::warning('Group alignment skipped: code is used by another group.', [
                    'code' => $code,
                    'existing_name' => $byCode->name,
                    'wanted_name' => $name,
                ]);

                return null;
            }

            return (int) $byCode->id;
        }

        if ($name === null) {
            return null;
        }

        $byName = DB::table('organisation_groups')
            ->where('organisation_id', $organisationId)
            ->where('group_type_id', $typeId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($byName !== null) {
            return (int) $byName->id;
        }

        return (int) DB::table('organisation_groups')->insertGetId([
            'organisation_id' => $organisationId,
            'group_type_id' => $typeId,
            'name' => $name,
            'code' => $code,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sameName(string $a, string $b): bool
    {
        return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
    }

    /**
     * Put a group under a parent only when it is under nothing.
     * One that already has a place keeps it.
     */
    private function placeIfUnplaced(int $groupId, ?int $parentId): void
    {
        if ($parentId === null || $parentId === $groupId) {
            return;
        }

        $placed = DB::table('organisation_group_parents')
            ->where('group_id', $groupId)
            ->exists();

        if ($placed) {
            return;
        }

        DB::table('organisation_group_parents')->insert([
            'group_id' => $groupId,
            'parent_id' => $parentId,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Archive a programme nobody uses. Anything that points at it
     * at all, however lightly, leaves it exactly as it is.
     */
    private function archiveIfUnused(int $organisationId, string $code): void
    {
        $group = DB::table('organisation_groups')
            ->where('organisation_id', $organisationId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($group === null) {
            return;
        }

        $pointedAt = DB::table('group_memberships')
            ->where('organisation_group_id', $group->id)
            ->exists()
            || DB::table('organisation_group_parents')
                ->where('parent_id', $group->id)
                ->exists();

        foreach ([
            'lecturer_assignments',
            'sponsored_access_grants',
            'advertisements',
        ] as $table) {
            if (
                ! $pointedAt
                && Schema::hasTable($table)
                && Schema::hasColumn($table, 'organisation_group_id')
            ) {
                $pointedAt = DB::table($table)
                    ->where('organisation_group_id', $group->id)
                    ->exists();
            }
        }

        if (
            ! $pointedAt
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'programme_group_id')
        ) {
            $pointedAt = DB::table('users')
                ->where('programme_group_id', $group->id)
                ->orWhere('programme', $group->name)
                ->exists();
        }

        if ($pointedAt) {
            return;
        }

        DB::table('organisation_groups')
            ->where('id', $group->id)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
};
