<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BIICFCareer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerController extends Controller
{
    /**
     * Display list of careers (Read-Only), with search/filter support.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $query = BIICFCareer::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                  ->orWhere('subsector', 'like', "%{$search}%");
            });
        }

        if ($subsector = $request->get('subsector')) {
            // Case-insensitive: avoids silent mismatches if subsector data
            // has inconsistent casing (e.g. "Cloud Computing" vs "cloud computing").
            $query->whereRaw('LOWER(subsector) = ?', [strtolower($subsector)]);
        }

        if ($demand = $request->get('demand')) {
            // Case-insensitive for the same reason - demand_level display
            // logic elsewhere in this view already normalises with strtolower().
            $query->whereRaw('LOWER(demand_level) = ?', [strtolower($demand)]);
        }

        // Stats reflect the current filtered result set, not just the
        // 15 rows on the visible page - cloned before paginate() consumes it.
        $totalSubsectors = (clone $query)
            ->whereNotNull('subsector')
            ->distinct()
            ->count('subsector');

        $totalHighDemand = (clone $query)
            ->where(function ($q) {
                $q->where('demand_level', 'like', '%high%')
                  ->orWhere('demand_level', 'like', '%very%');
            })
            ->count();

        $careers = $query->orderBy('job_title')
            ->paginate(15)
            ->withQueryString();

        // Full list of sub-sectors for the dropdown - independent of the
        // current filter/page, so every option is always selectable.
        $allSubsectors = BIICFCareer::whereNotNull('subsector')
            ->distinct()
            ->orderBy('subsector')
            ->pluck('subsector');

        return view('admin.careers.index', compact(
            'careers',
            'isAdmin',
            'totalSubsectors',
            'totalHighDemand',
            'allSubsectors'
        ));
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