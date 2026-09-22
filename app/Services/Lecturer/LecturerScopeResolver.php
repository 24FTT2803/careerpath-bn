<?php

namespace App\Services\Lecturer;

use App\Models\User;
use Illuminate\Support\Collection;

class LecturerScopeResolver
{
    /**
     * The organisation group IDs this lecturer is assigned to
     * teach.
     *
     * Every lecturer-facing query must begin with this method,
     * so that adding a new page cannot accidentally leak
     * students from outside the lecturer's classes.
     *
     * @return array<int, int>
     */
    public function classIdsFor(User $lecturer): array
    {
        return $lecturer
            ->lecturerAssignments()
            ->pluck('organisation_group_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Whether the given lecturer can see the given student.
     *
     * A student is visible to a lecturer when the student has a
     * membership in at least one of the lecturer's classes.
     */
    public function canSeeStudent(
        User $lecturer,
        User $student
    ): bool {
        $classIds = $this->classIdsFor($lecturer);

        if ($classIds === []) {
            return false;
        }

        return $student
            ->groupMemberships()
            ->whereIn('organisation_group_id', $classIds)
            ->exists();
    }

    /**
     * Students visible to this lecturer, as a query.
     *
     * Returned as a query rather than a collection so callers
     * can paginate, filter, or eager load before running it.
     */
    public function studentsQueryFor(User $lecturer)
    {
        $classIds = $this->classIdsFor($lecturer);

        return User::query()
            ->where('role', 'student')
            ->whereHas(
                'groupMemberships',
                fn ($query) => $query
                    ->whereIn('organisation_group_id', $classIds)
            );
    }
}