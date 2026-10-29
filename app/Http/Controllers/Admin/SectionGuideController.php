<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionGuide;
use App\Support\SectionGuideDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The help behind the '?' icons students see.
 *
 * Each guide belongs to a section of a page, so guides are edited
 * here but never added or removed: a new one would have no icon
 * to open it, and removing one would leave an icon that opens
 * nothing.
 */
class SectionGuideController extends Controller
{
    public function index(): View
    {
        SectionGuideDefaults::insertMissing();

        $order = array_keys(SectionGuideDefaults::all());

        return view('admin.section-guides.index', [
            'guidesByPage' => SectionGuide::query()
                ->get()
                ->sortBy(fn (SectionGuide $guide) => array_search($guide->key, $order, true))
                ->groupBy(fn (SectionGuide $guide) => $guide->pageLabel()),
        ]);
    }

    public function edit(SectionGuide $guide): View
    {
        return view('admin.section-guides.form', [
            'guide' => $guide,
            'hasDefault' => array_key_exists($guide->key, SectionGuideDefaults::all()),
        ]);
    }

    public function update(Request $request, SectionGuide $guide): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url:https', 'max:500'],
        ], [
            'video_url.url' => 'The video link must be a full https:// address, such as a YouTube link.',
        ]);

        $guide->update($validated);

        return redirect()
            ->route('admin.business.guides.index')
            ->with('success', "The {$guide->title} guide has been updated.");
    }

    /**
     * Put the text back as it was first written. A video link is
     * kept, since there is no default video to return to.
     */
    public function restore(SectionGuide $guide): RedirectResponse
    {
        $default = SectionGuideDefaults::all()[$guide->key] ?? null;

        if ($default === null) {
            return back()->withErrors(['guide' => 'This guide has no default text to restore.']);
        }

        $guide->update([
            'title' => $default['title'],
            'body' => $default['body'],
        ]);

        return redirect()
            ->route('admin.business.guides.edit', $guide)
            ->with('success', 'The default text has been restored.');
    }
}
