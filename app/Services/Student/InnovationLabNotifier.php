<?php

namespace App\Services\Student;

use App\Models\InnovationLabNote;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Tells every student, through their notification bell, when an
 * administrator publishes a new Innovation Lab post.
 */
class InnovationLabNotifier
{
    public const TYPE = 'innovation';

    /**
     * Announce a note the first time it goes live.
     *
     * Saving a draft, editing a live note, or unpublishing and
     * publishing it again never sends a second notification,
     * because published_at is only ever set once.
     *
     * @return int How many students were notified.
     */
    public function announce(InnovationLabNote $note): int
    {
        if (! $note->is_published || $note->published_at !== null) {
            return 0;
        }

        $note->forceFill(['published_at' => now()])->saveQuietly();

        $link = route('student.innovation-lab', absolute: false);
        $message = 'New post: '.Str::limit($note->title, 120);
        $sent = 0;

        User::query()
            ->where('role', 'student')
            ->select('id')
            ->chunkById(500, function ($students) use ($link, $message, &$sent) {
                $now = now();

                Notification::insert($students->map(fn (User $student) => [
                    'user_id' => $student->id,
                    'type' => self::TYPE,
                    'title' => 'New in the Innovation Lab',
                    'message' => $message,
                    'link' => $link,
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());

                $sent += $students->count();
            });

        return $sent;
    }
}
