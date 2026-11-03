<?php

namespace App\Services\Business;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Wipes an account's student side back to a fresh start.
 *
 * For demonstration accounts, such as the one used at an
 * exhibition, so a single account can be shown from the start
 * again and again. Only an admin's or a special account's: a real
 * student's work is never wiped this way.
 *
 * Everything a student builds up goes: profile answers,
 * competencies, interests, projects, certificates, aspirations,
 * milestones, recommendations and reports, Career Adviser and
 * CPBN Bot chats, AI usage counts, notifications, and the files
 * uploaded with them. The login, role, special rules, plan
 * grants and lecturer classes stay.
 */
class StudentSideReset
{
    /**
     * Tables holding a student's own records, by user_id, in an
     * order that removes children before what they point at.
     *
     * @var array<int, string>
     */
    private const TABLES = [
        'career_recommendations',
        'recommendation_generations',
        'profile_snapshots',
        'student_competencies',
        'student_interests',
        'student_projects',
        'student_certifications',
        'student_aspirations',
        'student_learning_records',
        'student_milestones',
        'academic_records',
        'notifications',
        'feature_usages',
        'user_section_views',
        'innovation_lab_reactions',
        'innovation_lab_interests',
    ];

    public function canReset(User $user): bool
    {
        return $user->homeRole() === 'admin' || $user->isSpecial();
    }

    public function reset(User $user): void
    {
        $files = $this->uploadedFiles($user);

        DB::transaction(function () use ($user) {
            /* Chats: their messages first, then the conversations. */
            $this->deleteConversations($user, 'career_adviser_conversations', 'career_adviser_messages', 'career_adviser_conversation_id');
            $this->deleteConversations($user, 'cpbn_bot_conversations', 'cpbn_bot_messages', 'cpbn_bot_conversation_id');

            foreach (self::TABLES as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                    DB::table($table)->where('user_id', $user->id)->delete();
                }
            }

            /* The profile itself stays, empty, as a new account's does. */
            if (Schema::hasTable('student_profiles')) {
                $phone = DB::table('student_profiles')->where('user_id', $user->id)->value('phone');

                DB::table('student_profiles')->where('user_id', $user->id)->delete();
                DB::table('student_profiles')->insert([
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /* Their place as a student: programme and class. */
            $user->groupMemberships()->delete();

            $user->forceFill([
                'programme' => null,
                'programme_group_id' => null,
                'cgpa' => null,
                'student_id' => null,
                'student_id_verified_at' => null,
                'student_id_verified_by' => null,
            ])->save();
        });

        foreach ($files as $path) {
            Storage::disk('public')->delete($path);
        }

        /*
         * Welcomed like a new account on its next visit to the
         * student dashboard: the first-visit greeting, and the
         * tips that explain where to start shown again.
         */
        Cache::forever(self::firstVisitKey($user), true);
    }

    /**
     * Where a reset account's pending welcome is kept until its
     * student dashboard has shown it.
     */
    public static function firstVisitKey(User $user): string
    {
        return 'student-first-visit:'.$user->id;
    }

    /**
     * Whether this visit to the student dashboard is the first
     * since the account began or was reset. Read once: the next
     * visit is an ordinary one.
     */
    public static function takeFirstVisit(User $user): bool
    {
        return (bool) Cache::pull(self::firstVisitKey($user), false);
    }

    private function deleteConversations(User $user, string $conversations, string $messages, string $key): void
    {
        if (! Schema::hasTable($conversations)) {
            return;
        }

        $ids = DB::table($conversations)->where('user_id', $user->id)->pluck('id');

        if (Schema::hasTable($messages) && $ids->isNotEmpty()) {
            DB::table($messages)->whereIn($key, $ids)->delete();
        }

        DB::table($conversations)->where('user_id', $user->id)->delete();
    }

    /**
     * Files the student uploaded, gone with their records.
     *
     * @return array<int, string>
     */
    private function uploadedFiles(User $user): array
    {
        $files = [];

        foreach ([
            ['student_profiles', 'profile_picture'],
            ['student_certifications', 'certificate_file_path'],
            ['student_milestones', 'proof_file_path'],
        ] as [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                $files = array_merge($files, DB::table($table)
                    ->where('user_id', $user->id)
                    ->whereNotNull($column)
                    ->pluck($column)
                    ->all());
            }
        }

        return array_values(array_filter($files));
    }
}
