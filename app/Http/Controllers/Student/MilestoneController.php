<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $milestones = $user->milestones()
            ->orderBy('is_completed')
            ->orderBy('target_date')
            ->get();

        return view('student.milestones.index', compact('milestones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:academic,career,personal,skill',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date|after:today',
        ]);

        Auth::user()->milestones()->create([
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
        ]);

        return redirect()->route('student.milestones')
            ->with('success', 'Milestone added successfully!');
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