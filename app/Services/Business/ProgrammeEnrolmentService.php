<?php

namespace App\Services\Business;

use App\Models\OrganisationGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgrammeEnrolmentService
{
    /**
     * The group type students enrol into.
     */
    public const PROGRAMME_TYPE = 'Programme';

    /**
     * Programmes a student can be enrolled in.
     *
     * Read from the structure rather than a list in code, so
     * adding a programme to the tree is all it takes for it to
     * appear everywhere it is offered.
     *
     * @return Collection<int, OrganisationGroup>
     */
    public function options(): Collection
    {
        return OrganisationGroup::query()
            ->where('is_active', true)
            ->whereHas(
                'type',
                fn ($query) => $query->where(
                    'name',
                    self::PROGRAMME_TYPE
                )
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * Put a student in the group matching their programme.
     *
     * users.programme stays as text because snapshots, reports
     * and the AI payload all read it, and an archived snapshot
     * has to keep the exact wording it was generated with. The
     * membership is what makes sponsorship and advertising able
     * to find the student.
     */
    public function syncFor(
        User $student,
        ?string $programmeName
    ): void {
        $programmeIds = $this->options()->pluck('id');

        $target = $programmeName === null
            ? null
            : $this->options()
                ->firstWhere('name', $programmeName);

        /*
         * Drop any programme-level membership that no longer
         * applies. Class memberships are left alone: those are
         * assigned deliberately and carry the student's place in
         * an intake as well as a programme.
         */
        $student->groupMemberships()
            ->whereIn('organisation_group_id', $programmeIds)
            ->when(
                $target !== null,
                fn ($query) => $query->where(
                    'organisation_group_id',
                    '!=',
                    $target->id
                )
            )
            ->delete();

        if ($target === null) {
            return;
        }

        /*
         * A student already in a class beneath the programme is
         * covered through the structure. Adding a second
         * membership would duplicate that, and would linger if
         * they later moved class.
         */
        if ($this->belongsBeneath($student, $target)) {
            return;
        }

        $student->groupMemberships()->firstOrCreate([
            'organisation_group_id' => $target->id,
        ]);
    }

    /**
     * Whether the student already belongs to something inside
     * this programme.
     */
    private function belongsBeneath(
        User $student,
        OrganisationGroup $programme
    ): bool {
        $descendants = $this->descendantIds($programme->id);

        if ($descendants === []) {
            return false;
        }

        return $student->groupMemberships()
            ->whereIn('organisation_group_id', $descendants)
            ->exists();
    }

    /**
     * Everything beneath a group, through any branch.
     *
     * @return array<int, int>
     */
    private function descendantIds(int $groupId): array
    {
        $found = [];
        $pending = [$groupId];

        while ($pending !== []) {
            $currentId = array_shift($pending);

            $childIds = DB::table('organisation_group_parents')
                ->where('parent_id', $currentId)
                ->pluck('group_id');

            foreach ($childIds as $childId) {
                if (isset($found[$childId])) {
                    continue;
                }

                $found[$childId] = true;
                $pending[] = $childId;
            }
        }

        return array_keys($found);
    }
}
