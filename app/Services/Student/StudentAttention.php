<?php

namespace App\Services\Student;

use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Models\UserSectionView;
use App\Services\Business\EntitlementService;
use App\Services\Lecturer\LecturerScopeResolver;
use Illuminate\Support\Carbon;

/**
 * Decides where a "new" dot or badge belongs.
 *
 * Every signal points at something the user can act on, so a
 * badge always means "there is something here for you".
 */
class StudentAttention
{
    public const HISTORY = 'history';

    /** @var array<int, array<string, mixed>> */
    private array $cache = [];

    public function __construct(
        private EntitlementService $entitlements,
        private LecturerScopeResolver $lecturerScope
    ) {}

    /**
     * @return array{
     *     recommendations: ?string,
     *     history: int,
     *     profile: bool,
     *     any: bool
     * }
     */
    public function for(User $student): array
    {
        if (isset($this->cache[$student->id])) {
            return $this->cache[$student->id];
        }

        $empty = ['recommendations' => null, 'history' => 0, 'profile' => false, 'any' => false];

        if (! $student->isStudent()) {
            return $this->cache[$student->id] = $empty;
        }

        $profileComplete = $this->profileIsComplete($student);
        $recommendations = $this->recommendationState($student, $profileComplete);
        $history = $this->newHistoryCount($student);

        return $this->cache[$student->id] = [
            'recommendations' => $recommendations,
            'history' => $history,
            'profile' => ! $profileComplete,
            'any' => $recommendations !== null || $history > 0,
        ];
    }

    /**
     * Uses the same percentage the dashboard and profile show.
     */
    protected function profileIsComplete(User $student): bool
    {
        return (int) $student->profile_completion >= 100;
    }

    /**
     * Record that the user has just looked at a section.
     */
    public function markSeen(User $user, string $section): void
    {
        UserSectionView::updateOrCreate(
            ['user_id' => $user->id, 'section' => $section],
            ['seen_at' => now()]
        );

        unset($this->cache[$user->id]);
    }

    /**
     * Student IDs waiting for an admin or lecturer to confirm.
     * Lecturers only count students in the classes they teach.
     */
    public function pendingStudentIds(User $staff): int
    {
        $query = match ($staff->role) {
            'admin' => User::query()->where('role', 'student'),
            'lecturer' => $this->lecturerScope->studentsQueryFor($staff),
            default => null,
        };

        if ($query === null) {
            return 0;
        }

        return $query
            ->whereNotNull('student_id')
            ->where('student_id', '!=', '')
            ->whereNull('student_id_verified_at')
            ->count();
    }

    /**
     * "ready"    – profile finished, careers not generated yet.
     * "outdated" – the profile changed after the last results.
     */
    private function recommendationState(User $student, bool $profileComplete): ?string
    {
        if (! $this->entitlements->featureAccess($student, 'career_recommendations.enabled')['allowed']) {
            return null;
        }

        $latest = $student->currentRecommendationGeneration()->first();

        if ($latest === null) {
            return $profileComplete ? 'ready' : null;
        }

        return $latest->status === RecommendationGeneration::STATUS_OUTDATED
            ? 'outdated'
            : null;
    }

    /**
     * New results or adviser replies since History was last opened.
     */
    private function newHistoryCount(User $student): int
    {
        $seenAt = UserSectionView::query()
            ->where('user_id', $student->id)
            ->where('section', self::HISTORY)
            ->value('seen_at');

        $seenAt = $seenAt ? Carbon::parse($seenAt) : null;

        $count = 0;

        if ($this->entitlements->featureAccess($student, 'recommendation_history.enabled')['allowed']) {
            $count += $student->recommendationGenerations()
                ->when($seenAt, fn ($query) => $query->where('created_at', '>', $seenAt))
                ->count();
        }

        if ($this->entitlements->featureAccess($student, 'career_adviser.history.enabled')['allowed']) {
            $conversation = $student->careerAdviserConversation;

            if ($conversation?->last_message_at
                && ($seenAt === null || $conversation->last_message_at->gt($seenAt))
            ) {
                $count++;
            }
        }

        return $count;
    }
}
