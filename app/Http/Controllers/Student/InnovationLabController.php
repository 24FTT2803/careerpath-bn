<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\InnovationLabNote;
use App\Models\InnovationLabReaction;
use App\Rules\IsEmoji;
use App\Services\Student\InnovationLabNotifier;
use App\Services\Student\StudentAttention;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InnovationLabController extends Controller
{
    public function index(Request $request, StudentAttention $attention): View
    {
        $user = $request->user();

        /*
         * Read when the page was last opened before recording this
         * visit, so the posts that are new to the student can be
         * marked "New" on the page itself.
         */
        $newSince = $attention->seenAt($user, StudentAttention::INNOVATION) ?? $user->created_at;

        $attention->markSeen($user, StudentAttention::INNOVATION);

        /* Opening the page also clears its bell notifications. */
        $user->notifications()
            ->where('type', InnovationLabNotifier::TYPE)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('student.innovation-lab.index', [
            'notes' => InnovationLabNote::query()
                /*
                 * The author and their picture, because each post
                 * is signed. One query rather than two per post.
                 */
                ->with(['author.profile', 'reactions', 'interests'])
                ->where('is_published', true)
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->latest('id')
                ->get(),
            'newSince' => $newSince,
        ]);
    }

    /**
     * Add or take back one emoji on a post.
     *
     * A student may have several different ones on the same
     * post, so this toggles the one named rather than replacing
     * whatever was there.
     */
    public function react(Request $request, InnovationLabNote $note)
    {
        abort_unless($note->is_published, 404);

        /*
         * Any emoji, not only the ones the page offers. The
         * palette is a convenience; what a reaction may be is
         * decided by what a character is.
         */
        $emoji = $request->validate([
            'emoji' => ['required', 'string', new IsEmoji],
        ])['emoji'];

        $existing = InnovationLabReaction::query()
            ->where('note_id', $note->id)
            ->where('user_id', $request->user()->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            /*
             * firstOrCreate rather than create, because the
             * unique index is the thing that actually decides and
             * two quick presses can both arrive before either has
             * written.
             */
            InnovationLabReaction::firstOrCreate([
                'note_id' => $note->id,
                'user_id' => $request->user()->id,
                'emoji' => $emoji,
            ]);
        }

        return $this->answer($request, $note);
    }

    /**
     * Say, or stop saying, that you would like to join.
     */
    public function interest(Request $request, InnovationLabNote $note)
    {
        abort_unless($note->is_published, 404);

        /*
         * Only where the post asked for it. Without this the
         * button could be sent to any post by anybody willing to
         * write the request by hand, and the count on a post
         * nobody can join would start moving.
         */
        abort_unless($note->accepts_interest, 404);

        $mine = $note->interests()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($mine) {
            $mine->delete();
        } else {
            $note->interests()->firstOrCreate([
                'user_id' => $request->user()->id,
            ]);
        }

        return $this->answer($request, $note);
    }

    /**
     * What a press gets back.
     *
     * The counts as JSON where the page asked by script, so a
     * reaction lands without the feed reloading and losing the
     * student's place in it. A plain form post gets sent back to
     * the feed, which is what happens with no JavaScript at all.
     */
    private function answer(Request $request, InnovationLabNote $note)
    {
        $note->load(['reactions', 'interests']);

        if (! $request->expectsJson()) {
            return back();
        }

        return response()->json([
            'reactions' => $note->reactionTally(),
            'mine' => $note->emojiFrom($request->user()),
            'interested' => $note->isInterested($request->user()),
            'interest_count' => $note->interestCount(),
        ]);
    }
}
