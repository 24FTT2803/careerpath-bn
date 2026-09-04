<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\AI\CareerAdviserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Throwable;

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

    /**
     * Process a Career Adviser question.
     */
    public function ask(
        Request $request,
        CareerAdviserService $adviser
    ): JsonResponse {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student && $student->isStudent(),
            403
        );

        $validator = Validator::make(
            $request->all(),
            [
                'message' => [
                    'required',
                    'string',
                    'max:500',
                ],
            ],
            [
                'message.required' =>
                    'Please enter a question for the Career Adviser.',

                'message.string' =>
                    'The Career Adviser question must be valid text.',

                'message.max' =>
                    'Your question may not exceed 500 characters.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' =>
                        $validator->errors()->first('message'),

                    'errors' =>
                        $validator->errors(),
                ],
                422
            );
        }

        $validated = $validator->validated();

        try {
            $response = $adviser->ask(
                $student,
                $validated['message']
            );

            return response()->json(
                $response
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'error',
                    'message' =>
                        'The Career Adviser is temporarily unavailable. '
                        . 'Please try again.',
                ],
                503
            );
        }
    }
}