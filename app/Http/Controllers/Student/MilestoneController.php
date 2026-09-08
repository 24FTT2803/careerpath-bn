<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentMilestone;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240', // 10MB
            ],
        ]);

        $data = [
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
            'is_completed' => false,
            'completed_date' => null,
        ];

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

        return redirect()->route('student.milestones')
            ->with('success', 'Milestone added successfully!');
    }

    public function complete(Request $request, StudentMilestone $milestone)
    {
        // Proof of completion is now always required (not just when past
        // due) - students must attach evidence before a milestone can be
        // marked complete, unless proof was already attached earlier
        // (e.g. uploaded when the milestone was first created).
        $request->validate([
            'proof_file' => [
                $milestone->proof_file_path ? 'nullable' : 'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240', // 10MB
            ],
        ], [
            'proof_file.required' => 'Please upload proof of completion (an image, PDF, or document) before marking this milestone as done.',
        ]);

        if ($request->hasFile('proof_file')) {
            // Delete old proof if replacing
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

        // No new file uploaded, but proof already exists from earlier
        // (validation above guarantees this branch only runs when
        // $milestone->proof_file_path is already set).
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
            ->with('success', '🎉 Milestone completed with proof!');
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

    /**
     * View the proof file for a milestone (Student)
     */
    public function viewProof(StudentMilestone $milestone): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        
        // Check if the user owns this milestone OR is admin/lecturer
        if ($milestone->user_id !== $user->id && !in_array($user->role, ['admin', 'lecturer'])) {
            abort(403, 'Unauthorized access.');
        }

        if (!$milestone->proof_file_path || !Storage::disk('local')->exists($milestone->proof_file_path)) {
            abort(404, 'Proof file not found.');
        }

        $filePath = $milestone->proof_file_path;
        $fileName = basename($filePath);

        return Storage::disk('local')->download($filePath, $fileName);
    }

    /**
     * View proof file for admin/lecturer
     */
    public function viewProofAdmin(int $studentId, int $milestoneId): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'lecturer'])) {
            abort(403, 'Unauthorized access.');
        }

        $milestone = StudentMilestone::where('user_id', $studentId)
            ->findOrFail($milestoneId);

        if (!$milestone->proof_file_path || !Storage::disk('local')->exists($milestone->proof_file_path)) {
            abort(404, 'Proof file not found.');
        }

        $filePath = $milestone->proof_file_path;
        $fileName = basename($filePath);

        return Storage::disk('local')->download($filePath, $fileName);
    }
}