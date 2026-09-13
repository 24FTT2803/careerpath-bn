<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\OrganisationGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    /**
     * Uploaded clips are capped well below anything a student
     * would wait for on a phone connection.
     */
    private const MAX_IMAGE_KILOBYTES = 2048;

    private const MAX_VIDEO_KILOBYTES = 10240;

    public function index(): View
    {
        $advertisements = Advertisement::query()
            ->with('organisationGroup')
            ->orderBy('position')
            ->orderByDesc('id')
            ->get();

        return view(
            'admin.advertisements.index',
            compact('advertisements')
        );
    }

    public function create(): View
    {
        return view(
            'admin.advertisements.form',
            [
                'advertisement' => new Advertisement([
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'is_active' => true,
                ]),
                'groups' => $this->groups(),
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['asset_path'] = $this->storeAsset(
            $request
        );

        Advertisement::create(
            $this->withScheduleWindow($validated)
        );

        return redirect()
            ->route('admin.business.advertisements.index')
            ->with(
                'success',
                'Advertisement created.'
            );
    }

    public function edit(Advertisement $advertisement): View
    {
        return view(
            'admin.advertisements.form',
            [
                'advertisement' => $advertisement,
                'groups' => $this->groups(),
            ]
        );
    }

    public function update(
        Request $request,
        Advertisement $advertisement
    ) {
        $validated = $this->validated($request);

        $uploaded = $this->storeAsset($request);

        if ($uploaded !== null) {
            $this->deleteAsset($advertisement);

            $validated['asset_path'] = $uploaded;
        }

        $advertisement->update(
            $this->withScheduleWindow($validated)
        );

        return redirect()
            ->route('admin.business.advertisements.index')
            ->with(
                'success',
                'Advertisement updated.'
            );
    }

    public function destroy(Advertisement $advertisement)
    {
        $this->deleteAsset($advertisement);

        $advertisement->delete();

        return redirect()
            ->route('admin.business.advertisements.index')
            ->with(
                'success',
                'Advertisement deleted.'
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate(
            [
                'title' => ['required', 'string', 'max:120'],

                'type' => [
                    'required',
                    'in:image,video,link,network',
                ],

                'position' => [
                    'required',
                    'in:one,two',
                ],

                /*
                 * An advertisement needs something to show:
                 * either an upload or an address. Editing an
                 * existing one may supply neither, because it
                 * already has an asset on file.
                 */
                'asset' => [
                    'nullable',
                    'file',
                    'max:'.self::MAX_VIDEO_KILOBYTES,
                ],

                'external_url' => [
                    'nullable',
                    'url',
                    'max:2048',
                ],

                'click_url' => [
                    'nullable',
                    'url',
                    'max:2048',
                ],

                'alt_text' => [
                    'nullable',
                    'string',
                    'max:160',
                ],

                'is_active' => ['nullable', 'boolean'],

                'starts_at' => ['nullable', 'date'],

                'ends_at' => [
                    'nullable',
                    'date',
                    'after_or_equal:starts_at',
                ],

                'organisation_group_id' => [
                    'nullable',
                    'exists:organisation_groups,id',
                ],
            ],
            [
                'ends_at.after_or_equal' => 'The end date must not be before the start date.',

                'asset.max' => 'The uploaded file is too large.',
            ]
        ) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Turn the two dates into a usable window.
     *
     * A date field has no time, and taking midnight literally
     * breaks both ends: an advertisement starting today would
     * not be live until tomorrow morning in Brunei, and one
     * ending today would stop at the very start of that day.
     *
     * Dates are read in the institution's timezone, then the
     * window is widened to cover the whole of both days.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withScheduleWindow(array $validated): array
    {
        $zone = config('app.business_timezone');

        $storage = config('app.timezone');

        /*
         * Converted back to the storage timezone before saving.
         * Eloquent formats a date using whatever timezone the
         * instance carries and discards the offset, so a Brunei
         * midnight would otherwise be written as a UTC midnight
         * eight hours later than intended.
         */
        if (! empty($validated['starts_at'])) {
            $validated['starts_at'] = Carbon::parse(
                $validated['starts_at'],
                $zone
            )
                ->startOfDay()
                ->setTimezone($storage);
        }

        if (! empty($validated['ends_at'])) {
            $validated['ends_at'] = Carbon::parse(
                $validated['ends_at'],
                $zone
            )
                ->endOfDay()
                ->setTimezone($storage);
        }

        return $validated;
    }

    /**
     * Store an uploaded asset and return its path.
     *
     * Returns null when nothing was uploaded, which leaves an
     * existing advertisement's asset untouched.
     */
    private function storeAsset(Request $request): ?string
    {
        if (! $request->hasFile('asset')) {
            return null;
        }

        $file = $request->file('asset');

        $isVideo = str_starts_with(
            (string) $file->getMimeType(),
            'video/'
        );

        $limit = $isVideo
            ? self::MAX_VIDEO_KILOBYTES
            : self::MAX_IMAGE_KILOBYTES;

        abort_if(
            $file->getSize() > $limit * 1024,
            422,
            'The uploaded file is too large.'
        );

        return $file->store(
            'advertisements',
            'public'
        );
    }

    private function deleteAsset(
        Advertisement $advertisement
    ): void {
        if (! $advertisement->asset_path) {
            return;
        }

        Storage::disk('public')->delete(
            $advertisement->asset_path
        );
    }

    /**
     * Groups available for targeting, newest structure first.
     */
    private function groups()
    {
        return OrganisationGroup::query()
            ->orderBy('name')
            ->get();
    }
}
