<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BIICFCareer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerController extends Controller
{
    /**
     * Display list of careers (Read-Only)
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $careers = BIICFCareer::orderBy('job_title')->paginate(15);

        return view('admin.careers.index', compact('careers', 'isAdmin'));
    }

    /**
     * Show single career details (Read-Only)
     */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $career = BIICFCareer::findOrFail($id);

        return view('admin.careers.show', compact('career', 'isAdmin'));
    }

    /**
     * Export careers data (Optional)
     */
    public function export()
    {
        // Export careers as CSV
    }
}