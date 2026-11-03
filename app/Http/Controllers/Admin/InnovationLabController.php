<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InnovationLabNote;
use App\Services\Student\InnovationLabNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class InnovationLabController extends Controller
{
    private const MAX_IMAGE_KILOBYTES = 5120;

    public function __construct(
        private InnovationLabNotifier $notifier
    ) {}

    public function index(Request $request): View
    {
        $all = InnovationLabNote::query()
            ->with('author')
            ->latest()
            ->get();

        $counts = [
            'all' => $all->count(),
            'live' => $all->filter(fn ($n) => $n->status() === 'live')->count(),
            'expired' => $all->filter(fn ($n) => $n->status() === 'expired')->count(),
            'draft' => $all->filter(fn ($n) => $n->status() === 'draft')->count(),
        ];

        $filter = $request->string('status')->toString();

        $notes = in_array($filter, ['live', 'expired', 'draft'], true)
            ? $all->filter(fn ($n) => $n->status() === $filter)
            : $all;

        return view('admin.innovation-lab.index', [
            'notes' => $notes->values(),
            'counts' => $counts,
            'filter' => $filter,
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
        $note = InnovationLabNote::create(
            $this->validated($request) + [
                'image_path' => $this->storeImage($request),
                'created_by' => $request->user()->id,
            ]
        );

        $notified = $this->announceIfLive($note);

        return $this->withExpiryWarning(
            redirect()
                ->route('admin.business.innovation-lab.index')
                ->with('success', $note->is_published
                    ? 'Note published.'.$this->notifiedText($notified)
                    : 'Draft saved. Students will be notified when you publish it.'),
            $note
        );
    }

    public function edit(InnovationLabNote $innovationLab): View
    {
        return view('admin.innovation-lab.form', [
            'note' => $innovationLab,
        ]);
    }

    public function update(Request $request, InnovationLabNote $innovationLab)
    {
        $data = $this->validated($request);

        $uploaded = $this->storeImage($request);

        if ($uploaded !== null) {
            $this->deleteImage($innovationLab);
            $data['image_path'] = $uploaded;
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($innovationLab);
            $data['image_path'] = null;
        }

        $innovationLab->update($data);

        $notified = $this->announceIfLive($innovationLab);

        return $this->withExpiryWarning(
            redirect()
                ->route('admin.business.innovation-lab.index')
                ->with('success', $notified > 0
                    ? 'Note published.'.$this->notifiedText($notified)
                    : 'Note updated.'),
            $innovationLab
        );
    }

    public function destroy(InnovationLabNote $innovationLab)
    {
        $this->deleteImage($innovationLab);

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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string'],
            'image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp', 'gif'])
                    ->max(self::MAX_IMAGE_KILOBYTES),
            ],
            'is_published' => ['nullable', 'boolean'],
            'accepts_interest' => ['nullable', 'boolean'],
            // Blank means the post never expires.
            'show_until' => ['nullable', 'date'],
        ]);

        unset($validated['image']);

        return $validated + [
            'is_published' => $request->boolean('is_published'),
            'accepts_interest' => $request->boolean('accepts_interest'),
        ];
    }

    /**
     * A post past its last day is hidden from students, so students
     * are not told about it either.
     */
    private function announceIfLive(InnovationLabNote $note): int
    {
        return $note->isExpired() ? 0 : $this->notifier->announce($note);
    }

    /**
     * Saving a published post with a date that has already passed
     * is allowed, but the admin should know it will not show.
     */
    private function withExpiryWarning(RedirectResponse $redirect, InnovationLabNote $note): RedirectResponse
    {
        if ($note->is_published && $note->isExpired()) {
            $redirect->with(
                'warning',
                'The "show until" date has already passed, so students will not see this post.'
            );
        }

        return $redirect;
    }

    private function notifiedText(int $notified): string
    {
        return $notified > 0
            ? ' '.$notified.' '.($notified === 1 ? 'student was' : 'students were').' notified.'
            : '';
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('innovation-lab', 'public');
    }

    private function deleteImage(InnovationLabNote $note): void
    {
        if ($note->image_path) {
            Storage::disk('public')->delete($note->image_path);
        }
    }
}
