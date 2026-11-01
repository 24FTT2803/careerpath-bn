<?php

namespace App\Services\Business;

use App\Models\OrganisationGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A student's class (DADT04, say), chosen by the student.
 *
 * Lecturers are assigned classes, and see the students in them.
 * Left to an administrator, every student had to be placed by
 * hand. So a student picks their own class on their profile,
 * from the classes under their own programme, and it counts at
 * once. A lecturer who teaches the class can take a student out
 * of it if they chose wrongly.
 */
class StudentClassService
{
    /**
     * The group type classes are.
     */
    public const CLASS_TYPE = 'Class / Group';

    /**
     * Every active class, with the programme each sits under, so a
     * form can show the right ones as the programme changes.
     *
     * @return Collection<int, array{id: int, name: string, programme_id: int}>
     */
    public function allByProgramme(): Collection
    {
        $programmes = app(ProgrammeEnrolmentService::class)->options();

        return $programmes
            ->flatMap(fn (OrganisationGroup $programme) => $this->classesUnder($programme->id)
                ->map(fn (OrganisationGroup $class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'programme_id' => $programme->id,
                    'programme' => $programme->name,
                ]))
            ->values();
    }

    /**
     * The classes a student may choose: those under their programme.
     *
     * @return Collection<int, OrganisationGroup>
     */
    public function options(User $student): Collection
    {
        if ($student->programme_group_id === null) {
            return collect();
        }

        return $this->classesUnder($student->programme_group_id);
    }

    /**
     * The class the student is in, if any.
     */
    public function current(User $student): ?OrganisationGroup
    {
        return OrganisationGroup::query()
            ->whereIn('id', $student->groupMemberships()->pluck('organisation_group_id'))
            ->whereHas('type', fn ($query) => $query->where('name', self::CLASS_TYPE))
            ->orderBy('name')
            ->first();
    }

    /**
     * Whether this class may be chosen by this student.
     */
    public function canChoose(User $student, int $classId): bool
    {
        return $this->options($student)->contains('id', $classId);
    }

    /**
     * Put the student in this class, or in none. Any other class
     * they were in is left, so a student is in one class at a
     * time. Their programme membership is covered through the
     * class while they have one, and comes back when they do not.
     */
    public function choose(User $student, ?int $classId): void
    {
        if ($classId !== null && ! $this->canChoose($student, $classId)) {
            return;
        }

        DB::transaction(function () use ($student, $classId) {
            $student->groupMemberships()
                ->whereIn('organisation_group_id', $this->allClassIds())
                ->when($classId !== null, fn ($query) => $query->where('organisation_group_id', '!=', $classId))
                ->delete();

            if ($classId !== null) {
                $student->groupMemberships()->firstOrCreate([
                    'organisation_group_id' => $classId,
                ]);

                /* The class already places them in their programme. */
                if ($student->programme_group_id !== null) {
                    $student->groupMemberships()
                        ->where('organisation_group_id', $student->programme_group_id)
                        ->delete();
                }
            }
        });

        app(ProgrammeEnrolmentService::class)->syncFor($student->fresh(), $student->fresh()->programme);
    }

    /**
     * A class from another programme no longer applies once the
     * student changes programme.
     */
    public function dropOutsideProgramme(User $student): void
    {
        $current = $this->current($student);

        if ($current !== null && ! $this->canChoose($student, $current->id)) {
            $this->choose($student, null);
        }
    }

    /**
     * Take a student out of a class this lecturer teaches. False
     * when the lecturer does not teach it, or the student is not
     * in it.
     */
    public function removeByLecturer(User $lecturer, User $student, int $classId): bool
    {
        $teaches = in_array($classId, $this->allClassIds(), true)
            && $lecturer->lecturerAssignments()
                ->where('organisation_group_id', $classId)
                ->exists();

        if (! $teaches) {
            return false;
        }

        $removed = $student->groupMemberships()
            ->where('organisation_group_id', $classId)
            ->delete() > 0;

        if ($removed) {
            app(ProgrammeEnrolmentService::class)->syncFor($student->fresh(), $student->programme);
        }

        return $removed;
    }

    /**
     * Active classes anywhere beneath a group, through any branch
     * (a class may sit under an intake inside the programme).
     *
     * @return Collection<int, OrganisationGroup>
     */
    public function classesUnder(int $groupId): Collection
    {
        $found = [];
        $pending = [$groupId];

        while ($pending !== []) {
            $current = array_shift($pending);

            foreach (DB::table('organisation_group_parents')->where('parent_id', $current)->pluck('group_id') as $child) {
                if (! isset($found[$child])) {
                    $found[$child] = true;
                    $pending[] = $child;
                }
            }
        }

        if ($found === []) {
            return collect();
        }

        return OrganisationGroup::query()
            ->whereIn('id', array_keys($found))
            ->where('is_active', true)
            ->whereHas('type', fn ($query) => $query->where('name', self::CLASS_TYPE))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, int>
     */
    private function allClassIds(): array
    {
        return OrganisationGroup::query()
            ->whereHas('type', fn ($query) => $query->where('name', self::CLASS_TYPE))
            ->pluck('id')
            ->all();
    }
}
