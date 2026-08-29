<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CareerAdviserController extends Controller
{
    /**
     * Display the Career Adviser interface.
     */
    public function index(): View
    {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student && $student->isStudent(),
            403
        );

        return view(
            'student.career-adviser.index',
            compact('student')
        );
    }
}