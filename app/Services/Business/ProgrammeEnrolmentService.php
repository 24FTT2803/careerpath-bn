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

        $target = $this->resolve($student, $programmeName);

        /*
         * Record the key alongside the name. From here the key
         * is what the enrolment rests on, so renaming a
         * programme no longer detaches anybody.
         */
        if ($student->programme_group_id !== $target?->id) {
            $student->forceFill([
                'programme_group_id' => $target?->id,
            ])->save();
        }

        /*
         * Keep the stored label in step with the group it now
         * points at, so a renamed programme reads correctly on
         * the profile the next time it is saved.
         */
        if ($target !== null && $student->programme !== $target->name) {
            $student->forceFill([
                'programme' => $target->name,
            ])->save();
        }

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
     * Find the programme a student belongs to.
     *
     * The key is preferred where it is already recorded, so a
     * renamed programme still resolves. The name is only used
     * to interpret what was just submitted, or to match a
     * student who predates the key.
     */
    private function resolve(
        User $student,
        ?string $programmeName
    ): ?OrganisationGroup {
        $options = $this->options();

        /*
         * No programme means the student cleared it, which is a
         * deliberate choice and must not be undone by falling
         * back to what they had before.
         */
        if ($programmeName === null || $programmeName === '') {
            return null;
        }

        $byName = $options->firstWhere('name', $programmeName);

        if ($byName !== null) {
            return $byName;
        }

        /*
         * A name that matches nothing means the programme was
         * renamed since it was recorded, so the key is the only
         * thing left that still identifies it.
         */
        if ($student->programme_group_id !== null) {
            return $options->firstWhere(
                'id',
                $student->programme_group_id
            );
        }

        return null;
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
