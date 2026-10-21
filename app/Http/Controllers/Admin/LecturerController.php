<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LecturerAssignment;
use App\Models\OrganisationGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LecturerController extends Controller
{
    public function index(Request $request): View
    {
        $assignedIds = LecturerAssignment::select('user_id');

        $query = User::where('role', 'lecturer');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->string('status')->toString() === 'unassigned') {
            $query->whereNotIn('id', $assignedIds);
        }

        $lecturers = $query->orderBy('name')->paginate(15)->withQueryString();

        $assignments = LecturerAssignment::with('organisationGroup')
            ->whereIn('user_id', $lecturers->pluck('id'))
            ->get()
            ->groupBy('user_id');

        $total = User::where('role', 'lecturer')->count();
        $unassigned = User::where('role', 'lecturer')
            ->whereNotIn('id', $assignedIds)
            ->count();

        return view('admin.lecturers.index', [
            'lecturers' => $lecturers,
            'assignments' => $assignments,
            'groups' => $this->assignableGroups(),
            'stats' => [
                'total' => $total,
                'unassigned' => $unassigned,
                'assigned' => $total - $unassigned,
            ],
        ]);
    }

    public function assign(Request $request, User $lecturer)
    {
        abort_unless($lecturer->role === 'lecturer', 404);

        $data = $request->validate([
            'organisation_group_id' => ['required', 'exists:organisation_groups,id'],
        ]);

        // The unique key makes a repeat click harmless.
        LecturerAssignment::firstOrCreate([
            'user_id' => $lecturer->id,
            'organisation_group_id' => $data['organisation_group_id'],
        ]);

        return back()->with('success', $lecturer->name.' assigned.');
    }

    public function unassign(User $lecturer, OrganisationGroup $group)
    {
        abort_unless($lecturer->role === 'lecturer', 404);

        LecturerAssignment::where('user_id', $lecturer->id)
            ->where('organisation_group_id', $group->id)
            ->delete();

        return back()->with('success', 'Assignment removed.');
    }

    /**
     * Active classes (groups with nothing beneath them), labelled
     * with their full path.
     */
    private function assignableGroups()
    {
        return OrganisationGroup::query()
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->get()
            ->map(fn (OrganisationGroup $g) => (object) [
                'id' => $g->id,
                'label' => $g->pathLabel(),
            ])
            ->sortBy('label')
            ->values();
    }
}