<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CpbnBotConversation;
use App\Models\CpbnBotMessage;
use App\Models\User;
use App\Services\Business\EntitlementService;
use App\Services\Business\FeatureUsageService;
use App\Services\CpbnBot\CpbnBotLinks;
use App\Services\CpbnBot\CpbnBotService;
use App\Support\AdviserText;
use App\Support\CpbnBotKnowledge;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * The CPBN Bot: the guide to the website, in a panel opened from
 * the button on every student page.
 *
 * It is separate from the Career Adviser. Its allowance is in AI
 * tokens, per plan; a plan with CPBN Bot History can reopen past
 * conversations.
 */
class CpbnBotController extends Controller
{
    public const FEATURE = 'cpbn_bot.enabled';

    public const TOKEN_QUOTA = 'cpbn_bot.token_quota';

    public const HISTORY = 'cpbn_bot.history.enabled';

    public function __construct(
        private EntitlementService $entitlements,
        private FeatureUsageService $featureUsage,
        private CpbnBotService $bot,
        private CpbnBotLinks $linkResolver,
    ) {}

    /**
     * Everything the panel needs when it opens.
     */
    public function panel(): JsonResponse
    {
        $student = $this->student();

        $access = $this->entitlements->featureAccess($student, self::FEATURE);
        $historyEnabled = $this->historyEnabled($student);

        $conversation = $historyEnabled
            ? $this->bot->currentConversation($student)
            : null;

        return response()->json([
            'available' => $access['allowed'],
            'unavailable_message' => $access['allowed'] ? null : $access['message'],
            'history_enabled' => $historyEnabled,
            'tokens' => $this->tokenPayload($this->featureUsage->status($student, self::TOKEN_QUOTA)),
            'conversation' => $conversation?->exists
                ? $this->conversationPayload($conversation, true)
                : null,
        ]);
    }

    public function ask(Request $request): JsonResponse
    {
        $student = $this->student();

        $access = $this->entitlements->featureAccess($student, self::FEATURE);

        if (! $access['allowed']) {
            return $this->failure($access['message'], $access['reason'] === 'maintenance' ? 503 : 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'conversation_id' => ['nullable', 'integer'],
            'new_conversation' => ['nullable', 'boolean'],
            'current_page' => ['nullable', 'string', Rule::in(CpbnBotKnowledge::pageKeys())],
        ], [
            'message.required' => 'Please type a question for the CPBN Bot.',
            'message.max' => 'Your question may not be longer than 500 characters.',
        ]);

        $historyEnabled = $this->historyEnabled($student);

        if (! empty($validated['new_conversation'])) {
            $conversation = $this->bot->newConversation($student);
        } elseif ($historyEnabled && ! empty($validated['conversation_id'])) {
            /*
             * Reopening a past conversation is part of history.
             * Without it the question goes into the current one.
             */
            $conversation = $student->cpbnBotConversations()->find($validated['conversation_id']);

            if ($conversation === null) {
                return $this->failure('That conversation could not be found.', 404);
            }
        } else {
            $conversation = $this->bot->currentConversation($student);
        }

        /*
         * A question is allowed while any of the allowance is
         * left; what it costs is known only once it is answered.
         */
        $tokens = $this->featureUsage->status($student, self::TOKEN_QUOTA);

        if (! $tokens['allowed']) {
            return response()->json([
                'status' => 'unavailable',
                'message' => $tokens['reason'] === 'quota_exceeded'
                    ? $this->limitMessage($tokens)
                    : $tokens['message'],
                'reason' => $tokens['reason'],
                'tokens' => $this->tokenPayload($tokens),
            ], $tokens['reason'] === 'quota_exceeded' ? 429 : 503);
        }

        try {
            $answer = $this->bot->ask(
                $student,
                $validated['message'],
                $conversation,
                $validated['current_page'] ?? null
            );
        } catch (ConnectionException $exception) {
            report($exception);

            return $this->failure('The CPBN Bot could not reach the AI service. Please try again shortly.', 503);
        } catch (RequestException $exception) {
            report($exception);

            return $exception->response->status() === 429
                ? $this->failure('The AI service is busy at the moment. Please wait a little and try again.', 429)
                : $this->failure('The CPBN Bot is temporarily unavailable. Please try again.', 503);
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure('The CPBN Bot could not answer just now. Please try again.', 503);
        }

        $this->featureUsage->recordUsage($student, self::TOKEN_QUOTA, $answer['tokens']);

        return response()->json([
            'status' => 'completed',
            'message' => $answer['message'],
            'message_html' => AdviserText::toHtml($answer['message']),
            'links' => $this->links($answer['links']),

            /* Where the panel should take the student, straight away. */
            'navigate' => $answer['navigate'] ? $this->linkResolver->present($answer['navigate']) : null,
            'conversation_id' => $answer['conversation']->id,
            'tokens_used' => $answer['tokens'],
            'tokens' => $this->tokenPayload($this->featureUsage->status($student, self::TOKEN_QUOTA)),
        ]);
    }

    /**
     * Past conversations, newest first.
     */
    public function conversations(): JsonResponse
    {
        $student = $this->student();

        abort_unless($this->historyEnabled($student), 403);

        return response()->json([
            'conversations' => $student->cpbnBotConversations()
                ->where('message_count', '>', 0)
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->limit(50)
                ->get()
                ->map(fn (CpbnBotConversation $conversation) => $this->conversationPayload($conversation, false))
                ->values(),
        ]);
    }

    /**
     * One past conversation, to reopen it.
     */
    public function conversation(int $conversation): JsonResponse
    {
        $student = $this->student();

        abort_unless($this->historyEnabled($student), 403);

        return response()->json([
            'conversation' => $this->conversationPayload(
                $student->cpbnBotConversations()->findOrFail($conversation),
                true
            ),
        ]);
    }

    /**
     * Delete one of the student's conversations for good.
     *
     * Its tokens stay spent: deleting a conversation removes what
     * was said, not what it cost.
     */
    public function destroy(int $conversation): JsonResponse
    {
        $student = $this->student();

        $student->cpbnBotConversations()->findOrFail($conversation)->delete();

        return response()->json(['status' => 'deleted']);
    }

    private function student(): User
    {
        /** @var User|null $student */
        $student = Auth::user();

        abort_unless($student && $student->isStudent(), 403);

        return $student;
    }

    private function historyEnabled(User $student): bool
    {
        return $this->entitlements->allows($student, self::HISTORY);
    }

    private function failure(string $message, int $status): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }

    /**
     * Stored links as buttons the panel can show.
     *
     * @param  array<int, string|array<string, mixed>>|null  $links
     * @return array<int, array{key: string, label: string, url: string}>
     */
    private function links(?array $links): array
    {
        return collect($links ?? [])
            ->filter(fn ($link) => is_string($link) || is_array($link))
            ->map(fn ($link) => $this->linkResolver->present($link))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationPayload(CpbnBotConversation $conversation, bool $withMessages): array
    {
        $payload = [
            'id' => $conversation->id,
            'title' => $conversation->title ?: 'Conversation',
            'message_count' => $conversation->message_count,
            'last_message_label' => $conversation->last_message_at
                ?->timezone(config('app.business_timezone'))
                ->format('j M Y, g:i A'),
        ];

        if ($withMessages) {
            $payload['messages'] = $conversation->messages()
                ->get()
                ->map(fn (CpbnBotMessage $message) => [
                    'role' => $message->role,
                    'html' => $message->isFromStudent()
                        ? nl2br(e($message->content))
                        : AdviserText::toHtml($message->content),
                    'links' => $message->isFromStudent() ? [] : $this->links($message->links),
                ])
                ->values();
        }

        return $payload;
    }

    /**
     * The usage ring's figures.
     *
     * @param  array<string, mixed>  $status
     * @return array<string, mixed>
     */
    private function tokenPayload(array $status): array
    {
        $limited = in_array($status['mode'], ['total', 'recurring'], true);

        return [
            'mode' => $status['mode'],
            'allowed' => $status['allowed'],
            'used' => (int) $status['used'],
            'limit' => $limited ? $status['amount'] : null,
            'remaining' => $limited ? $status['remaining'] : null,
            'percent_used' => $limited && $status['amount'] > 0
                ? min(100, (int) round($status['used'] / $status['amount'] * 100))
                : 0,
            'period' => $status['mode'] === 'recurring'
                ? $this->periodLabel($status['period_value'], $status['period_unit'])
                : null,
            'next_available_label' => $status['next_available_at']
                ?->timezone(config('app.business_timezone'))
                ->format('j M, g:i A'),
        ];
    }

    private function periodLabel(?int $value, ?string $unit): ?string
    {
        if ($value === null || $unit === null) {
            return null;
        }

        return $value === 1
            ? 'the last '.$unit
            : 'the last '.$value.' '.Str::plural($unit, $value);
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function limitMessage(array $status): string
    {
        if ($status['mode'] === 'total') {
            return 'You have used all of your CPBN Bot allowance.';
        }

        return $status['next_available_at']
            ? 'You have used your CPBN Bot allowance for now. You can ask again from '
                .$status['next_available_at']->timezone(config('app.business_timezone'))->format('j M, g:i A').'.'
            : 'You have used your CPBN Bot allowance for now. Please try again later.';
    }
}
