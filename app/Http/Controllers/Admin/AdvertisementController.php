<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementSlot;
use App\Models\OrganisationGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    /**
     * Uploaded clips are capped well below anything a student
     * would wait for on a phone connection.
     */
    /**
     * Generous enough that an animated GIF is usable.
     */
    private const MAX_IMAGE_KILOBYTES = 5120;

    /**
     * Thirty seconds of video does not fit in ten megabytes at
     * any reasonable quality, so this is sized for the limit
     * rather than against it.
     */
    private const MAX_VIDEO_KILOBYTES = 25600;

    /**
     * How long a video advertisement may run.
     *
     * Checked in the browser before upload, since reading a
     * video's duration on the server would mean installing
     * ffmpeg for one rule.
     */
    public const MAX_VIDEO_SECONDS = 30;

    /**
     * Croppable still images.
     *
     * GIF is deliberately absent. It is an image and renders as
     * one, but cropping flattens it to a single frame, so it is
     * accepted and left alone.
     */
    private const CROPPABLE_IMAGE_TYPES = [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    private const IMAGE_TYPES = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
    ];

    private const VIDEO_TYPES = [
        'mp4',
        'webm',
        'ogg',
    ];

    public function index(Request $request): View
    {
        $all = Advertisement::query()
            ->with('organisationGroup')
            ->orderBy('position')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $filter = $request->string('status')->toString();

        $counts = [
            'all' => $all->count(),
            'live' => 0,
            'scheduled' => 0,
            'ended' => 0,
            'paused' => 0,
        ];

        foreach ($all as $advertisement) {
            $counts[$advertisement->status()]++;
        }

        $advertisements = in_array(
            $filter,
            ['live', 'scheduled', 'ended', 'paused'],
            true
        )
            ? $all->filter(
                fn (Advertisement $advertisement) => $advertisement->status() === $filter
            )
            : $all;

        return view(
            'admin.advertisements.index',
            [
                'advertisements' => $advertisements,
                'counts' => $counts,
                'filter' => $filter,
                'reach' => $this->reachFor($all),

                'slots' => AdvertisementSlot::orderBy('position')
                    ->get(),

                /*
                 * Grouped by placement, because a placement is
                 * the thing an administrator thinks about: what
                 * appears above the page, and what below.
                 */
                'byPosition' => $advertisements->groupBy('position'),
            ]
        );
    }

    /**
     * How many students each advertisement can reach.
     *
     * Targeting resolves through the structure, so aiming at a
     * school covers its classes. That is not obvious from a
     * group name alone.
     *
     * @param  Collection<int, Advertisement>  $advertisements
     * @return array<int, int>
     */
    private function reachFor($advertisements): array
    {
        $studentCount = User::where('role', 'student')->count();

        $reach = [];

        foreach ($advertisements as $advertisement) {
            if ($advertisement->organisation_group_id === null) {
                $reach[$advertisement->id] = $studentCount;

                continue;
            }

            $groupIds = $this->groupWithDescendants(
                $advertisement->organisation_group_id
            );

            $reach[$advertisement->id] = User::query()
                ->where('role', 'student')
                ->whereHas(
                    'groupMemberships',
                    fn ($query) => $query->whereIn(
                        'organisation_group_id',
                        $groupIds
                    )
                )
                ->count();
        }

        return $reach;
    }

    /**
     * A group and everything beneath it, through any branch.
     *
     * @return array<int, int>
     */
    private function groupWithDescendants(int $groupId): array
    {
        $found = [$groupId => true];
        $pending = [$groupId];

        while ($pending !== []) {
            $currentId = array_shift($pending);

            $childIds = DB::table('organisation_group_parents')
                ->where('parent_id', $currentId)
                ->pluck('group_id');

            foreach ($childIds as $childId) {
                if (isset($found[$childId])) {
                    continue;
                }

                $found[$childId] = true;
                $pending[] = $childId;
            }
        }

        return array_keys($found);
    }

    public function create(Request $request): View
    {
        return view(
            'admin.advertisements.form',
            [
                'advertisement' => new Advertisement([
                    'type' => Advertisement::TYPE_IMAGE,

                    'position' => $request->string('position')
                        ->toString()
                        ?: Advertisement::POSITION_ONE,

                    'is_active' => true,
                ]),

                'lockedPosition' => $request->string('position')
                    ->toString() ?: null,

                'groups' => $this->groups(),
            ]
        );
    }

    /**
     * Change how a placement behaves.
     */
    public function updateSlot(
        Request $request,
        AdvertisementSlot $slot
    ) {
        $validated = $request->validate([
            'rotation_enabled' => ['required', 'boolean'],

            'rotation_size' => [
                'required',
                'integer',
                'min:1',
                'max:'.AdvertisementSlot::MAX_ROTATION_SIZE,
            ],

            'dwell_seconds' => [
                'required',
                'integer',
                'min:2',
                'max:120',
            ],

            'is_active' => ['required', 'boolean'],
        ]);

        $slot->update($validated);

        return redirect()
            ->route('admin.business.advertisements.index')
            ->with('success', $slot->name.' updated.');
    }

    /**
     * Move an advertisement within its placement.
     */
    public function reorder(
        Request $request,
        Advertisement $advertisement
    ) {
        $direction = $request->string('direction')->toString();

        $neighbour = Advertisement::query()
            ->where('position', $advertisement->position)
            ->when(
                $direction === 'up',
                fn ($query) => $query
                    ->where('sort_order', '<', $advertisement->sort_order)
                    ->orderByDesc('sort_order'),
                fn ($query) => $query
                    ->where('sort_order', '>', $advertisement->sort_order)
                    ->orderBy('sort_order')
            )
            ->first();

        if ($neighbour !== null) {
            $theirs = $neighbour->sort_order;

            $neighbour->update([
                'sort_order' => $advertisement->sort_order,
            ]);

            $advertisement->update(['sort_order' => $theirs]);
        }

        return redirect()
            ->route('admin.business.advertisements.index')
            ->with('success', 'Order updated.');
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
                'lockedPosition' => $advertisement->position,
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
                /*
                 * The file has to match the kind of
                 * advertisement it was uploaded for. Without
                 * this a video could be stored as an image and
                 * the page would render a broken picture.
                 */
                'asset' => $this->assetRules(
                    $request->input('type')
                ),

                /*
                 * An image already cropped to the banner frame
                 * in the browser, sent as a data URL.
                 */
                'cropped_asset' => [
                    'nullable',
                    'string',
                    'starts_with:data:image/',
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
                'asset.uploaded' => 'That file is larger than this server accepts. The limit is '
                    .round(self::uploadLimitKilobytes() / 1024, 1)
                    .' MB.',

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
        $cropped = $this->storeCroppedImage($request);

        if ($cropped !== null) {
            return $cropped;
        }

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

    /**
     * Rules for the uploaded file, by advertisement type.
     *
     * @return array<int, mixed>
     */
    private function assetRules(?string $type): array
    {
        if ($type === Advertisement::TYPE_VIDEO) {
            return [
                'nullable',
                File::types(self::VIDEO_TYPES)
                    ->max($this->uploadLimitKilobytes(
                        self::MAX_VIDEO_KILOBYTES
                    )),
            ];
        }

        if ($type === Advertisement::TYPE_IMAGE) {
            return [
                'nullable',
                File::types(self::IMAGE_TYPES)
                    ->max($this->uploadLimitKilobytes(
                        self::MAX_IMAGE_KILOBYTES
                    )),
            ];
        }

        /*
         * Link and network advertisements carry an address
         * rather than a file, so nothing should be uploaded.
         */
        return ['nullable', 'prohibited'];
    }

    /**
     * The largest upload this server will actually accept.
     *
     * PHP discards anything over upload_max_filesize before
     * Laravel sees it, so a validation limit above that is a
     * promise the application cannot keep: the upload simply
     * fails with no useful explanation.
     */
    public static function uploadLimitKilobytes(
        int $preferred = PHP_INT_MAX
    ): int {
        $limits = [
            $preferred,
            self::iniKilobytes('upload_max_filesize'),

            /*
             * The whole request has to fit too, so leave a
             * little room for the other fields.
             */
            max(self::iniKilobytes('post_max_size') - 256, 1),
        ];

        return (int) min(array_filter($limits));
    }

    private static function iniKilobytes(string $key): int
    {
        $value = trim((string) ini_get($key));

        if ($value === '') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024,
            'm' => $number * 1024,
            'k' => $number,
            default => (int) ($number / 1024),
        };
    }

    /**
     * Store an image cropped in the browser.
     *
     * Cropping happens client side, so the server only decodes
     * and writes bytes. No image library is needed, and the
     * administrator sees the exact frame students will see.
     */
    private function storeCroppedImage(Request $request): ?string
    {
        $payload = $request->input('cropped_asset');

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        [$header, $encoded] = array_pad(
            explode(',', $payload, 2),
            2,
            null
        );

        $allowed = [
            'data:image/jpeg;base64' => 'jpg',
            'data:image/png;base64' => 'png',
            'data:image/webp;base64' => 'webp',
        ];

        $extension = $allowed[$header] ?? null;

        abort_if(
            $extension === null || $encoded === null,
            422,
            'That image could not be read.'
        );

        $binary = base64_decode($encoded, true);

        abort_if(
            $binary === false,
            422,
            'That image could not be read.'
        );

        abort_if(
            strlen($binary) > self::MAX_IMAGE_KILOBYTES * 1024,
            422,
            'The cropped image is too large.'
        );

        $path = 'advertisements/'
            .Str::uuid()
            .'.'
            .$extension;

        Storage::disk('public')->put($path, $binary);

        return $path;
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
    /**
     * Groups labelled with their full path.
     *
     * "January" means nothing once every intake has one.
     */
    private function groups()
    {
        $all = OrganisationGroup::query()
            ->with('parents')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return $all
            ->map(fn (OrganisationGroup $group) => (object) [
                'id' => $group->id,
                'path' => $this->pathFor($group, $all),
            ])
            ->sortBy('path')
            ->values();
    }

    /**
     * @param  Collection<int, OrganisationGroup>  $all
     */
    private function pathFor(
        OrganisationGroup $group,
        $all
    ): string {
        $names = [$group->name];
        $seen = [$group->id => true];
        $current = $group;

        while (true) {
            $parent = $current->parents
                ->firstWhere('pivot.is_primary', true)
                ?? $current->parents->first();

            if ($parent === null || isset($seen[$parent->id])) {
                break;
            }

            $seen[$parent->id] = true;
            array_unshift($names, $parent->name);

            $current = $all->get($parent->id) ?? $parent;
        }

        return implode(' › ', $names);
    }
}
