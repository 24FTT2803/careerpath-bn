<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\InnovationLabNote;
use Illuminate\View\View;

class InnovationLabController extends Controller
{
    public function index(): View
    {
        return view('student.innovation-lab.index', [
            'notes' => InnovationLabNote::query()
                ->with('author')
                ->where('is_published', true)
                ->latest()
                ->get(),
        ]);
    }
}
