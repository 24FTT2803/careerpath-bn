<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Lecturer\LecturerScopeResolver;
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