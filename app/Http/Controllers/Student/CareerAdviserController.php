<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
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

        $profileCompletion = (int) $student->profile_completion;

        $topRecommendation = $student
            ->careerRecommendations()
            ->with('career')
            ->orderBy('rank')
            ->orderByDesc('match_score')
            ->first();

        $skillGapCount = collect(
            $topRecommendation?->skill_gaps ?? []
        )
            ->filter(fn ($gap) => filled($gap))
            ->count();

        $biicfRoleCount = BiicfJobRole::count();
        $biicfSubSectorCount = BiicfSubSector::count();

        $biicfAvailable = (
            $biicfRoleCount > 0
            && $biicfSubSectorCount > 0
        );

        return view(
            'student.career-adviser.index',
            compact(
                'student',
                'profileCompletion',
                'topRecommendation',
                'skillGapCount',
                'biicfRoleCount',
                'biicfSubSectorCount',
                'biicfAvailable'
            )
        );
    }
}