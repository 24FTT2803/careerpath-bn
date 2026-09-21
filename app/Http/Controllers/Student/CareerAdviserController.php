<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\AI\CareerAdviserService;
use App\Services\Business\EntitlementService;
use App\Services\Business\FeatureUsageService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class CareerAdviserController extends Controller
{
    public function __construct(
        private EntitlementService $entitlements,
        private FeatureUsageService $featureUsage
    ) {}

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

        $careerAdviserAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_adviser.enabled'
                );

        $careerAdviserQuota =
            $this->featureUsage
                ->status(
                    $student,
                    'career_adviser.usage_quota'
                );

        $profileCompletion = (int) $student->profile_completion;

        /*
         * Every current match, not just the first. The panel
         * pages through them so a student can see the gaps for
         * each rather than only their top role.
         */
        $recommendations = $student
            ->currentRecommendations()
            ->with([
                'career',
                'jobRole.subSector',
            ])
            ->orderBy('rank')
            ->orderByDesc('match_score')
            ->get();

        $topRecommendation = $recommendations->first();

        $matchPanels = $recommendations->map(
            fn ($recommendation) => [
                'title' => $recommendation->jobRole?->title
                    ?? $recommendation->career?->job_title
                    ?? 'Role',

                'match' => (float) $recommendation->match_score,

                'readiness' => (float) (
                    $recommendation->career_readiness_score ?? 0
                ),

                /*
                 * The size of each gap as well as its name. How
                 * far short a student is matters more than the
                 * fact that they are short.
                 */
                'gaps' => collect(
                    $recommendation->skill_gaps ?? []
                )
                    ->map(fn ($gap) => is_array($gap)
                        ? [
                            'name' => $gap['skill_name']
                                ?? $gap['name']
                                ?? null,

                            'size' => (int) ($gap['gap'] ?? 0),

                            'current' => $gap['current_level']
                                ?? null,

                            'required' => $gap['required_label']
                                ?? $gap['recommended_level']
                                ?? null,
                        ]
                        : ['name' => $gap, 'size' => 0])
                    ->filter(fn ($gap) => filled($gap['name']))
                    ->values()
                    ->all(),

                'matched' => collect(
                    $recommendation->matched_skills ?? []
                )
                    ->filter()
                    ->values()
                    ->all(),
            ]
        )->values();

        /*
         * The gaps themselves, not just how many. Shown on the
         * page so a student can see what the adviser is talking
         * about without asking for it.
         */
        $skillGaps = collect(
            $topRecommendation?->skill_gaps ?? []
        )
            ->filter(fn ($gap) => filled($gap))
            ->map(fn ($gap) => is_array($gap)
                ? ($gap['skill_name'] ?? $gap['name'] ?? null)
                : $gap)
            ->filter()
            ->values();

        $skillGapCount = $skillGaps->count();

        $matchedSkills = collect(
            $topRecommendation?->matched_skills ?? []
        )
            ->filter(fn ($skill) => filled($skill))
            ->values();

        $readinessScore = (float) (
            $topRecommendation?->career_readiness_score ?? 0
        );

        /*
         * Messages are always stored. This entitlement decides
         * whether the student can see and continue the thread,
         * so switching it on later reveals their history rather
         * than starting them from nothing.
         */
        $adviserHistoryEnabled = $this->entitlements
            ->allows(
                $student,
                'career_adviser.history.enabled'
            );

        $conversationMessages = $adviserHistoryEnabled
            ? $student
                ->careerAdviserConversation
                ?->messages()
                ->get()
            : null;

        $conversationMessages ??= collect();

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
                'conversationMessages',
                'adviserHistoryEnabled',
                'skillGapCount',
                'skillGaps',
                'matchPanels',
                'matchedSkills',
                'readinessScore',
                'biicfRoleCount',
                'biicfSubSectorCount',
                'biicfAvailable',
                'careerAdviserAccess',
                'careerAdviserQuota'
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

        $careerAdviserAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_adviser.enabled'
                );

        if (! $careerAdviserAccess['allowed']) {
            $status =
                $careerAdviserAccess['reason']
                === 'maintenance'
                    ? 503
                    : 403;

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'unavailable',
                    'message' => $careerAdviserAccess[
                            'message'
                        ],
                    'reason' => $careerAdviserAccess[
                            'reason'
                        ],
                ],
                $status
            );
        }

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
                'message.required' => 'Please enter a question for the Career Adviser.',

                'message.string' => 'The Career Adviser question must be valid text.',

                'message.max' => 'Your question may not exceed 500 characters.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors()->first('message'),

                    'errors' => $validator->errors(),
                ],
                422
            );
        }

        $validated = $validator->validated();

        $quotaStatus =
            $this->featureUsage
                ->status(
                    $student,
                    'career_adviser.usage_quota'
                );

        if (! $quotaStatus['allowed']) {
            $status =
                $quotaStatus['reason']
                    === 'quota_exceeded'
                    ? 429
                    : 503;

            return response()->json(
                [
                    'schema_version' => '1.0',

                    'status' => 'unavailable',

                    'message' => $quotaStatus['message'],

                    'reason' => $quotaStatus['reason'],

                    'quota' => [
                        'allowed' => $quotaStatus[
                                'allowed'
                            ],

                        'reason' => $quotaStatus[
                                'reason'
                            ],

                        'mode' => $quotaStatus[
                                'mode'
                            ],

                        'amount' => $quotaStatus[
                                'amount'
                            ],

                        'used' => $quotaStatus[
                                'used'
                            ],

                        'remaining' => $quotaStatus[
                                'remaining'
                            ],

                        'next_available_at' => $quotaStatus[
                                'next_available_at'
                            ]?->toIso8601String(),

                        'period_value' => $quotaStatus[
                                'period_value'
                            ],

                        'period_unit' => $quotaStatus[
                                'period_unit'
                            ],
                    ],
                ],
                $status
            );
        }

        try {
            $response = $adviser->ask(
                $student,
                $validated['message'],
                $this->entitlements->allows(
                    $student,
                    'career_adviser.history.enabled'
                )
            );

            /*
            * Consume quota only after the adviser has returned
            * a successful response.
            */
            $this->featureUsage
                ->recordUsage(
                    $student,
                    'career_adviser.usage_quota'
                );

            $updatedQuota =
                $this->featureUsage
                    ->status(
                        $student,
                        'career_adviser.usage_quota'
                    );

            $response['quota'] = [
                'allowed' => $updatedQuota['allowed'],

                'reason' => $updatedQuota['reason'],

                'mode' => $updatedQuota['mode'],

                'amount' => $updatedQuota['amount'],

                'used' => $updatedQuota['used'],

                'remaining' => $updatedQuota['remaining'],

                'next_available_at' => $updatedQuota['next_available_at']
                    ?->toIso8601String(),

                'period_value' => $updatedQuota['period_value'],

                'period_unit' => $updatedQuota['period_unit'],
            ];

            return response()->json(
                $response
            );
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'error',
                    'message' => 'The Career Adviser could not reach the AI service. '
                        .'Please try again shortly.',
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
                        'message' => 'The AI service is currently busy due to usage limits. '
                            .'Please wait a moment and try again.',
                    ],
                    429
                );
            }

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'error',
                    'message' => 'The Career Adviser is temporarily unavailable. '
                        .'Please try again.',
                ],
                503
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(
                [
                    'schema_version' => '1.0',
                    'status' => 'error',
                    'message' => 'The Career Adviser could not process the response. '
                        .'Please try again.',
                ],
                503
            );
        }
    }
}
