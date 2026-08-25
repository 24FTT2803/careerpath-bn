<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            'title' => 'required|string|max:255', // Title is REQUIRED
            'category' => 'required|string|in:academic,career,personal,skill',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
        ]);

        $targetDate = $request->target_date ? Carbon::parse($request->target_date) : null;
        $isPast = $targetDate && $targetDate->endOfDay()->lt(now());

        $milestone = Auth::user()->milestones()->create([
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
            // Auto-complete if the target date is in the past
            'is_completed' => $isPast,
            'completed_date' => $isPast ? now() : null,
        ]);

        $message = $isPast 
            ? '✅ Past milestone added and marked as completed!' 
            : 'Milestone added successfully!';

        return redirect()->route('student.milestones')
            ->with('success', $message);
    }

    public function complete(StudentMilestone $milestone)
    {
        $milestone->update([
            'is_completed' => true,
            'completed_date' => now(),
        ]);

        return redirect()->route('student.milestones')
            ->with('success', '🎉 Milestone completed!');
    }

    public function destroy(StudentMilestone $milestone)
    {
        $milestone->delete();
        return redirect()->route('student.milestones')
            ->with('success', 'Milestone deleted.');
    }
}