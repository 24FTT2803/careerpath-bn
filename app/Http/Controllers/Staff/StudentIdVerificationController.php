<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Lecturer\LecturerScopeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentIdVerificationController extends Controller
{
    public function __construct(
        private LecturerScopeResolver $scope
    ) {}

    /**
     * Confirm the student's ID belongs to them.
     */
    public function verify(Request $request, User $student): RedirectResponse
    {
        $this->authorizeStaffFor($request->user(), $student);

        if (blank($student->student_id)) {
            return back()->with('error', "{$student->name} has not entered a Student ID yet.");
        }

        $student->forceFill([
            'student_id_verified_at' => now(),
            'student_id_verified_by' => $request->user()->id,
        ])->save();

        return back()->with('success', "Student ID {$student->student_id} verified for {$student->name}.");
    }

    /**
     * Withdraw verification so the student can correct the ID.
     */
    public function revoke(Request $request, User $student): RedirectResponse
    {
        $this->authorizeStaffFor($request->user(), $student);

        $student->forceFill([
            'student_id_verified_at' => null,
            'student_id_verified_by' => null,
        ])->saveQuietly();

        return back()->with('success', "Verification removed. {$student->name} can now correct their Student ID.");
    }

    /**
     * Admins may act on any student; lecturers only on students
     * in the classes they teach. 404 rather than 403, matching
     * the lecturer student pages.
     */
    private function authorizeStaffFor(User $staff, User $student): void
    {
        abort_unless($student->role === 'student', 404);

        if ($staff->role === 'admin') {
            return;
        }

        abort_unless(
            $staff->role === 'lecturer' && $this->scope->canSeeStudent($staff, $student),
            404
        );
    }
}