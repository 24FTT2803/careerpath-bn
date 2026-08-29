<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentMilestone;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class MilestoneController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $milestones = $user->milestones()
            ->orderBy('is_completed')
            ->orderBy('target_date', 'desc')
            ->get();

        return view('student.milestones.index', compact('milestones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:academic,career,personal,skill',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'proof_file' => [
                'nullable',
                File::types(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])
                    ->max('10mb'),
            ],
        ]);

        $targetDate = $request->target_date ? Carbon::parse($request->target_date) : null;
        $isPast = $targetDate && $targetDate->endOfDay()->lt(now());

        $data = [
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
            'is_completed' => false, // Never auto-complete anymore
            'completed_date' => null,
        ];

        // Handle proof file upload
        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store(
                "milestone-proofs/{$request->user()->id}",
                'local'
            );
            $data['proof_file_path'] = $path;
            $data['proof_submitted_at'] = now();
        }

        $milestone = Auth::user()->milestones()->create($data);

        NotificationHelper::logMilestoneActivity(
            Auth::id(),
            $request->title,
            'added'
        );

        $message = 'Milestone added successfully!';

        return redirect()->route('student.milestones')
            ->with('success', $message);
    }

    public function complete(Request $request, StudentMilestone $milestone)
    {
        $request->validate([
            'proof_file' => [
                'nullable',
                File::types(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])
                    ->max('10mb'),
            ],
        ]);

        // If proof is uploaded, mark as completed
        if ($request->hasFile('proof_file')) {
            // Delete old proof if exists
            if ($milestone->proof_file_path) {
                Storage::disk('local')->delete($milestone->proof_file_path);
            }

            $path = $request->file('proof_file')->store(
                "milestone-proofs/{$request->user()->id}",
                'local'
            );

            $milestone->update([
                'is_completed' => true,
                'completed_date' => now(),
                'proof_file_path' => $path,
                'proof_submitted_at' => now(),
            ]);

            NotificationHelper::logMilestoneActivity(
                Auth::id(),
                $milestone->title,
                'completed'
            );

            return redirect()->route('student.milestones')
                ->with('success', '🎉 Milestone completed with proof!');
        }

        // If no proof but trying to complete, check if proof is required
        if ($milestone->target_date && Carbon::parse($milestone->target_date)->endOfDay()->lt(now())) {
            return redirect()->route('student.milestones')
                ->with('warning', 'Please upload proof of completion for this milestone.');
        }

        // For milestones without target date or future date, allow completion without proof
        $milestone->update([
            'is_completed' => true,
            'completed_date' => now(),
        ]);

        NotificationHelper::logMilestoneActivity(
            Auth::id(),
            $milestone->title,
            'completed'
        );

        return redirect()->route('student.milestones')
            ->with('success', '🎉 Milestone completed!');
    }

    public function destroy(StudentMilestone $milestone)
    {
        if ($milestone->proof_file_path) {
            Storage::disk('local')->delete($milestone->proof_file_path);
        }

        NotificationHelper::logMilestoneActivity(
            Auth::id(),
            $milestone->title,
            'deleted'
        );

        $milestone->delete();
        return redirect()->route('student.milestones')
            ->with('success', 'Milestone deleted.');
    }
}