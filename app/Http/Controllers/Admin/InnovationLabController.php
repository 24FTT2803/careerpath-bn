<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InnovationLabNote;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InnovationLabController extends Controller
{
    public function index(): View
    {
        return view('admin.innovation-lab.index', [
            'notes' => InnovationLabNote::query()
                ->with('author')
                ->latest()
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.innovation-lab.form', [
            'note' => new InnovationLabNote(['is_published' => true]),
        ]);
    }

    public function store(Request $request)
    {
        InnovationLabNote::create(
            $this->validated($request) + [
                'created_by' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('admin.business.innovation-lab.index')
            ->with('success', 'Note published.');
    }

    public function edit(InnovationLabNote $innovationLab): View
    {
        return view('admin.innovation-lab.form', [
            'note' => $innovationLab,
        ]);
    }

    public function update(Request $request, InnovationLabNote $innovationLab)
    {
        $innovationLab->update($this->validated($request));

        return redirect()
            ->route('admin.business.innovation-lab.index')
            ->with('success', 'Note updated.');
    }

    public function destroy(InnovationLabNote $innovationLab)
    {
        $innovationLab->delete();

        return redirect()
            ->route('admin.business.innovation-lab.index')
            ->with('success', 'Note deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]) + [
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
