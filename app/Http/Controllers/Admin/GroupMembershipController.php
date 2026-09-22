<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupMembership;
use App\Models\LecturerAssignment;
use App\Models\OrganisationGroup;
use App\Models\User;
use App\Services\Business\ProgrammeEnrolmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupMembershipController extends Controller
{
    public function __construct(
        private ProgrammeEnrolmentService $programmes
    ) {}

    public function index(
        Request $request,
        OrganisationGroup $group
    ): View {
        $members = User::query()
            ->whereHas(
                'groupMemberships',
                fn ($query) => $query->where(
                    'organisation_group_id',
                    $group->id
                )
            )
            ->orderBy('name')
            ->get();

        $search = trim(
            $request->string('q')->toString()
        );

        /*
         * Searching is deliberate rather than listing everyone:
         * an institution has far more students than fit on a
         * page, and adding the wrong one is easy from a long
         * list of similar names.
         */
        $candidates = $search === ''
            ? collect()
            : User::query()
                ->where('role', 'student')
                ->whereNotIn('id', $members->pluck('id'))
                ->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(25)
                ->get();

                $programmeNames = $this->programmeAncestorNames($group);

        /*
         * Lecturers assigned to teach this class, plus the
         * search candidates for assigning more.
         */
        $lecturers = $group
            ->assignedLecturers()
            ->orderBy('name')
            ->get();

        $lecturerSearch = trim(
            $request->string('lq')->toString()
        );

        $lecturerCandidates = $lecturerSearch === ''
            ? collect()
            : User::query()
                ->where('role', 'lecturer')
                ->whereNotIn('id', $lecturers->pluck('id'))
                ->where(function ($query) use ($lecturerSearch) {
                    $query
                        ->where('name', 'like', "%{$lecturerSearch}%")
                        ->orWhere('email', 'like', "%{$lecturerSearch}%");
                })
                ->orderBy('name')
                ->limit(25)
                ->get();

        return view(
            'admin.business.groups.members',
            [
                'lecturers' => $lecturers,
                'lecturerSearch' => $lecturerSearch,
                'lecturerCandidates' => $lecturerCandidates,
                'group' => $group,
                'members' => $members,
                'candidates' => $candidates,
                'search' => $search,
                'programmeNames' => $programmeNames,

                /*
                 * A programme membership is written from the
                 * student's profile, so removing one here only
                 * lasts until they next save.
                 */
                'isProgramme' => $group->type?->name
                    === ProgrammeEnrolmentService::PROGRAMME_TYPE,

                'mismatched' => $this->mismatchedIds(
                    $members,
                    $programmeNames
                ),
            ]
        );
    }

    public function store(
        Request $request,
        OrganisationGroup $group
    ) {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            GroupMembership::firstOrCreate([
                'user_id' => $userId,
                'organisation_group_id' => $group->id,
            ]);
        }

        return redirect()
            ->route('admin.business.groups.members.index', $group)
            ->with('success', 'Added to the group.');
    }

    public function destroy(
        OrganisationGroup $group,
        User $user
    ) {
        $user->groupMemberships()
            ->where('organisation_group_id', $group->id)
            ->delete();

        return redirect()
            ->route('admin.business.groups.members.index', $group)
            ->with('success', 'Removed from the group.');
    }

    /**
     * The programmes this group sits under, or itself when it
     * is a programme.
     *
     * @return array<int, string>
     */
    private function programmeAncestorNames(
        OrganisationGroup $group
    ): array {
        $programmeIds = $this->programmes
            ->options()
            ->pluck('name', 'id');

        $found = [];
        $pending = [$group->id];
        $seen = [];

        while ($pending !== []) {
            $currentId = array_shift($pending);

            if (isset($seen[$currentId])) {
                continue;
            }

            $seen[$currentId] = true;

            if ($programmeIds->has($currentId)) {
                $found[] = $programmeIds->get($currentId);
            }

            $parentIds = DB::table('organisation_group_parents')
                ->where('group_id', $currentId)
                ->pluck('parent_id');

            foreach ($parentIds as $parentId) {
                $pending[] = $parentId;
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * Members whose chosen programme does not match where this
     * group sits.
     *
     * Surfaced rather than prevented. A student correcting their
     * own profile should not be blocked by a class an
     * administrator assigned, but nobody should have to notice
     * the disagreement by accident either.
     *
     * @param  Collection<int, User>  $members
     * @param  array<int, string>  $programmeNames
     * @return array<int, int>
     */
    private function mismatchedIds(
        Collection $members,
        array $programmeNames
    ): array {
        if ($programmeNames === []) {
            return [];
        }

                return $members
            ->filter(
                fn (User $member) => $member->programme !== null
                    && ! in_array(
                        $member->programme,
                        $programmeNames,
                        true
                    )
            )
            ->pluck('id')
            ->all();
    }

    /**
     * Assign a lecturer to teach this class.
     */
    public function assignLecturer(
        Request $request,
        OrganisationGroup $group
    ) {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => [
                Rule::exists('users', 'id')
                    ->where('role', 'lecturer'),
            ],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            LecturerAssignment::firstOrCreate([
                'user_id' => $userId,
                'organisation_group_id' => $group->id,
            ]);
        }

        return redirect()
            ->route(
                'admin.business.groups.members.index',
                $group
            )
            ->with('success', 'Lecturer assigned to the class.');
    }

    /**
     * Remove a lecturer from this class.
     */
    public function unassignLecturer(
        OrganisationGroup $group,
        User $user
    ) {
        LecturerAssignment::where(
            'user_id',
            $user->id
        )
            ->where('organisation_group_id', $group->id)
            ->delete();

        return redirect()
            ->route(
                'admin.business.groups.members.index',
                $group
            )
            ->with('success', 'Lecturer removed from the class.');
    }
}
