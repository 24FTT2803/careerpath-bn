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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
            ->with([
                'career',
                'jobRole.subSector',
            ])
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
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'error',
                    'message' =>
                        'The Career Adviser could not reach the AI service. '
                        . 'Please try again shortly.',
                ],
                503
            );
        } catch (RequestException $exception) {
            report($exception);

            if (
                $exception->response->status() === 429
            ) {
                return response()->json(
                    [
                        'schema_version' => '1.0',
                        'status' => 'error',
                        'message' =>
                            'The AI service is currently busy due to usage limits. '
                            . 'Please wait a moment and try again.',
                    ],
                    429
                );
            }

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
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'error',
                    'message' =>
                        'The Career Adviser could not process the response. '
                        . 'Please try again.',
                ],
                503
            );
        }
    }
}