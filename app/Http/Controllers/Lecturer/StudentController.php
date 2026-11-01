<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\OrganisationGroup;
use App\Models\User;
use App\Services\Business\StudentClassService;
use App\Services\Lecturer\LecturerScopeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        private LecturerScopeResolver $scope
    ) {}

    /**
     * Students the logged-in lecturer can see.
     */
    public function index(): View
    {
        /** @var User $lecturer */
        $lecturer = Auth::user();

        $classIds = $this->scope->classIdsFor($lecturer);

        $students = $this->scope
            ->studentsQueryFor($lecturer)
            ->with([
                'profile',
                'competencies',
                'milestones',
                'programmeGroup',
                /* Which of this lecturer's classes each one is in. */
                'organisationGroups' => fn ($query) => $query
                    ->whereIn('organisation_groups.id', $classIds)
                    ->with('type'),
            ])
            ->orderBy('name')
            ->paginate(15);

        /*
         * The classes themselves, so the page can show
         * "You teach: DADT04, DADT05".
         */
        $assignedClasses = $lecturer
            ->assignedGroups()
            ->orderBy('name')
            ->get();

        return view(
            'lecturer.students.index',
            compact(
                'students',
                'assignedClasses',
                'classIds'
            )
        );
    }

    /**
     * Take a student out of one of this lecturer's classes, when
     * they chose it wrongly. Only a class the lecturer teaches.
     */
    public function removeFromClass(User $student, OrganisationGroup $class, StudentClassService $classes): RedirectResponse
    {
        /** @var User $lecturer */
        $lecturer = Auth::user();

        abort_unless(
            $lecturer->role === 'lecturer'
                && $student->role === 'student'
                && $classes->removeByLecturer($lecturer, $student, $class->id),
            404
        );

        return redirect()
            ->route('lecturer.students.index')
            ->with('success', $student->name.' is no longer in '.$class->name.'.');
    }

    /**
     * A single student, but only if they are in one of the
     * lecturer's classes.
     */
    public function show(User $student): View
    {
        /** @var User $lecturer */
        $lecturer = Auth::user();

        /*
         * Refuse outright if the student is not one of this
         * lecturer's students. Deliberately 404 rather than 403,
         * so a lecturer cannot tell whether the student exists
         * at all outside their scope.
         */
        abort_unless(
            $lecturer->role === 'lecturer'
                && $student->role === 'student'
                && $this->scope->canSeeStudent($lecturer, $student),
            404
        );

        $student->load([
            'profile',
            'academicRecords',
            'competencies',
            'interests',
            'projects',
            'certifications',
            'aspirations',
            'milestones',
            'programmeGroup',
        ]);

        $topRecommendations = $student
            ->currentRecommendations()
            ->with([
                'career',
                'jobRole.subSector',
            ])
            ->orderBy('rank')
            ->orderByDesc('match_score')
            ->limit(3)
            ->get();

        return view(
            'lecturer.students.show',
            compact(
                'student',
                'topRecommendations'
            )
        );
    }
}
