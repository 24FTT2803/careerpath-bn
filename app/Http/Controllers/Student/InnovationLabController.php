<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\InnovationLabNote;
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
                ->where('is_published', true)
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->latest('id')
                ->get(),
            'newSince' => $newSince,
        ]);
    }
}
