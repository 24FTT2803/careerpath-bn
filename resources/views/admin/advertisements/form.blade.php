@extends('admin.layouts.admin')

@section('title', $advertisement->exists ? 'Edit Advertisement' : 'New Advertisement')

@section('content')
@php
    $isEdit = $advertisement->exists;
@endphp

<style>
    /*
     * The frame showing what is already uploaded. Shared by the
     * image and the video so both sit at the banner's shape
     * rather than each carrying its own inline sizing.
     */
    .current-asset-preview {
        display: block;
        width: 100%;
        max-width: 728px;
        aspect-ratio: 6 / 1;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
    }
</style>

<div>
    <div class="page-header">
        <div>
            <h1>
                <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                {{ $isEdit ? 'Edit Advertisement' : 'New Advertisement' }}
            </h1>
            <p class="subtitle">
                What appears in the placement, and who sees it
            </p>
        </div>

        <a
            href="{{ route('admin.business.advertisements.index') }}"
            class="btn btn-outline"
        >
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form
        method="POST"
        action="{{
            $isEdit
                ? route('admin.business.advertisements.update', $advertisement)
                : route('admin.business.advertisements.store')
        }}"
        enctype="multipart/form-data"
        class="card"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="mb-4">
            <label class="field-label">
                Title
            </label>

            <input
                type="text"
                name="title"
                value="{{ old('title', $advertisement->title) }}"
                maxlength="120"
                required
                class="field-input"
            >

            <p class="field-hint">
                For your own reference. Students do not see this.
            </p>
        </div>

        <div class="field-grid field-grid-2">
            <div>
                <label class="field-label">
                    Type
                </label>

                <select
                    name="type"
                    id="typeSelect"
                    class="field-input"
                >
                    @foreach([
                        'image' => 'Image',
                        'video' => 'Video',
                        'link' => 'External link',
                        'network' => 'Ad network embed',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(old('type', $advertisement->type) === $value)
                        >{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{--
                Only shown when there is a choice to make. Opened
                from a placement, or editing one that already has
                a placement, it cannot be changed here — so it was
                a label and a greyed-out box saying what you
                already knew. The value still travels with the
                form, just without the furniture.
            --}}
            @if($lockedPosition !== null)
                <input
                    type="hidden"
                    name="position"
                    value="{{ $lockedPosition }}"
                >
            @else
                <div>
                    <label class="field-label">
                        Placement
                    </label>

                    <select
                        name="position"
                        class="field-input"
                    >
                        <option
                            value="one"
                            @selected(old('position', $advertisement->position) === 'one')
                        >Above the page</option>

                        <option
                            value="two"
                            @selected(old('position', $advertisement->position) === 'two')
                        >Below the page</option>
                    </select>
                </div>
            @endif
        </div>

        {{--
            Which kinds of advertisement each field belongs to.
            A hosted image has nothing to fetch from elsewhere,
            and an embed has nothing to upload, so showing every
            field for every type left most of the form to be
            ignored.
        --}}
        <div class="mb-4" data-ad-field="image video">
            <label class="field-label" id="assetLabel">
                {{ old('type', $advertisement->type) === 'video' ? 'Video' : 'Image' }}
            </label>

            @if($advertisement->asset_path)
                <div class="mb-3">
                    <p class="text-xs text-gray-500 mb-1">Currently showing</p>

                    {{--
                        A clip has to be a video element. Pointed
                        at an img it simply fails to load, so
                        editing a video advertisement showed an
                        empty frame and no sign of what was
                        already there.
                    --}}
                    @if($advertisement->isVideo())
                        <div id="currentVideo" data-src="{{ $advertisement->mediaUrl() }}">
                            <x-advertisement-video
                                :advertisement="$advertisement"
                                class="current-asset-preview"
                                muted
                                loop
                                autoplay
                                playsinline
                            />
                        </div>

                        <p class="field-hint" style="margin-top:6px;">
                            {{ basename($advertisement->asset_path) }} &middot;
                            {{ $advertisement->videoFrame() ? 'showing the part you chose' : 'showing the whole video' }}
                        </p>

                        <button
                            type="button"
                            id="videoFrameExisting"
                            class="btn btn-subtle btn-sm"
                            style="margin-top:6px;"
                        >
                            <i class="fas fa-crop-simple"></i> Choose the part students see
                        </button>
                    @else
                        <img
                            src="{{ $advertisement->mediaUrl() }}"
                            alt=""
                            class="current-asset-preview"
                        >
                    @endif
                </div>
            @endif

            <input
                type="file"
                id="assetInput"
                name="asset"
                accept="image/*,video/*"
                class="field-input"
            >

            @php
                $imageLimitMb = round(
                    App\Http\Controllers\Admin\AdvertisementController::assetLimitKilobytes(
                        App\Models\Advertisement::TYPE_IMAGE
                    ) / 1024,
                    1
                );

                $videoLimitMb = round(
                    App\Http\Controllers\Admin\AdvertisementController::assetLimitKilobytes(
                        App\Models\Advertisement::TYPE_VIDEO
                    ) / 1024,
                    1
                );
            @endphp

            <p class="field-hint">
                Banners are shown at 6:1, so 1456&times;243 is a
                good size. Images may be up to {{ $imageLimitMb }} MB
                and video up to {{ $videoLimitMb }} MB and
                {{ App\Http\Controllers\Admin\AdvertisementController::MAX_VIDEO_SECONDS }}
                seconds. An animated GIF is kept as it is, since
                cropping would flatten it. Leave empty to keep
                the current file.
            </p>

            <p id="assetError" class="field-hint" style="display:none;color:#c0392b;"></p>

            <input type="hidden" name="cropped_asset" id="croppedAsset">

            {{-- The chosen part of a video, as fractions of the picture. --}}
            <input
                type="hidden"
                name="video_crop"
                id="videoCrop"
                value="{{ old('video_crop', $advertisement->video_crop ? json_encode($advertisement->video_crop) : '') }}"
            >

            {{--
                A video is framed on a still taken from it. The clip
                itself is kept as uploaded and shown through the
                chosen frame.
            --}}
            <div id="videoFramePanel" style="display:none;margin-top:12px">
                <p class="field-hint" style="margin-bottom:8px;">
                    This is a still from the video. Drag the box over
                    the part students should see, then confirm it.
                    The video plays inside that box.
                </p>

                <div style="max-width:728px">
                    <img id="videoFrameImage" alt="" style="max-width:100%">
                </div>

                <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                    <button type="button" id="videoFrameConfirm" class="btn btn-subtle btn-sm">
                        Use this part
                    </button>

                    <button type="button" id="videoFrameWhole" class="btn btn-subtle btn-sm">
                        Use the whole video
                    </button>

                    <button type="button" id="videoFrameCancel" class="btn btn-subtle btn-sm">
                        Cancel
                    </button>
                </div>
            </div>

            <div id="videoPreview" style="display:none;margin-top:12px">
                <p class="field-hint" style="margin-bottom:6px;" id="videoPreviewNote">
                    This is what will be saved.
                </p>

                <div
                    id="videoPreviewFrame"
                    class="current-asset-preview"
                    style="position:relative;overflow:hidden;"
                >
                    <video
                        id="videoPreviewPlayer"
                        muted
                        loop
                        autoplay
                        playsinline
                        style="position:absolute;display:block;max-width:none;width:100%;height:100%;left:0;top:0;object-fit:cover;"
                    ></video>
                </div>

                <button
                    type="button"
                    id="videoFrameReopen"
                    class="btn btn-subtle btn-sm"
                    style="margin-top:8px;"
                >
                    <i class="fas fa-crop-simple"></i> Change the part students see
                </button>
            </div>

            <div id="cropperPanel" style="display:none;margin-top:12px">
                <p class="field-hint" style="margin-bottom:8px;">
                    Drag to choose the part students will see,
                    then confirm it.
                </p>

                <div style="max-width:728px">
                    <img id="cropperImage" alt="" style="max-width:100%">
                </div>

                <div style="display:flex;gap:8px;margin-top:8px;">
                    <button
                        type="button"
                        id="cropperConfirm"
                        class="btn btn-subtle btn-sm"
                    >
                        Use this crop
                    </button>

                    <button
                        type="button"
                        id="cropperClear"
                        class="btn btn-subtle btn-sm"
                    >
                        Use the whole image
                    </button>
                </div>
            </div>

            <div id="cropPreview" style="display:none;margin-top:12px">
                <p class="field-hint" style="margin-bottom:6px;">
                    This is what will be saved.
                </p>

                <img
                    id="cropPreviewImage"
                    alt=""
                    style="width:100%;max-width:728px;aspect-ratio:6/1;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb"
                >

                <button
                    type="button"
                    id="cropReopen"
                    class="btn btn-subtle btn-sm"
                    style="margin-top:8px;"
                >
                    Change the crop
                </button>
            </div>

        </div>

        <div class="mb-4" data-ad-field="link network">
            <label class="field-label">
                External address
            </label>

            <input
                type="url"
                name="external_url"
                value="{{ old('external_url', $advertisement->external_url) }}"
                class="field-input"
                placeholder="https://"
            >

            <p class="field-hint">
                Used when the media is hosted elsewhere, or for
                an ad network embed.
            </p>
        </div>

        <div class="field-grid field-grid-2">
            {{--
                Also shown for an external link, despite the name
                of the field above it: "External address" is
                where the media is hosted, not where a click
                goes. Without this, such an advertisement has
                nothing to click through to.

                An embed is excluded because the network handles
                its own clicks inside the frame.
            --}}
            <div data-ad-field="image video link">
                <label class="field-label">
                    Click destination
                </label>

                <input
                    type="url"
                    name="click_url"
                    value="{{ old('click_url', $advertisement->click_url) }}"
                    class="field-input"
                    placeholder="https://"
                >

                <p class="field-hint">
                    Where a student goes if they click. Optional.
                </p>
            </div>

            <div>
                <label class="field-label">
                    Alternative text
                </label>

                <input
                    type="text"
                    name="alt_text"
                    value="{{ old('alt_text', $advertisement->alt_text) }}"
                    maxlength="160"
                    class="field-input"
                >

                <p class="field-hint">
                    Read aloud by screen readers and shown if the
                    image cannot load.
                </p>
            </div>
        </div>

        <div class="field-grid field-grid-3">
            <div>
                <label class="field-label">
                    Starts
                </label>

                <input
                    type="date"
                    name="starts_at"
                    value="{{ old('starts_at', $advertisement->starts_at?->timezone(config('app.business_timezone'))->format('Y-m-d')) }}"
                    class="field-input"
                >
            </div>

            <div>
                <label class="field-label">
                    Ends
                </label>

                <input
                    type="date"
                    name="ends_at"
                    value="{{ old('ends_at', $advertisement->ends_at?->timezone(config('app.business_timezone'))->format('Y-m-d')) }}"
                    class="field-input"
                >
            </div>

            <div>
                <label class="field-label">
                    Audience
                </label>

                <select
                    name="organisation_group_id"
                    class="field-input"
                >
                    <option value="">All students</option>

                    @foreach($groups as $group)
                        <option
                            value="{{ $group->id }}"
                            @selected((int) old('organisation_group_id', $advertisement->organisation_group_id) === $group->id)
                        >{{ $group->path }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="inline-flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $advertisement->is_active))
                >

                <span>Active</span>
            </label>
        </div>

        <button
            type="submit"
            class="btn btn-primary"
        >
            {{ $isEdit ? 'Save changes' : 'Create advertisement' }}
        </button>
    </form>
</div>
@endsection

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"
>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('assetInput');
        var panel = document.getElementById('cropperPanel');
        var image = document.getElementById('cropperImage');
        var hidden = document.getElementById('croppedAsset');
        var preview = document.getElementById('cropPreview');
        var previewImage = document.getElementById('cropPreviewImage');
        var confirmButton = document.getElementById('cropperConfirm');
        var clearButton = document.getElementById('cropperClear');
        var reopenButton = document.getElementById('cropReopen');
        var error = document.getElementById('assetError');
        var cropper = null;

        var MAX_VIDEO_SECONDS = {{ App\Http\Controllers\Admin\AdvertisementController::MAX_VIDEO_SECONDS }};

        /*
         * The per-kind limits, not just the server ceiling.
         * Checking only the ceiling let a 10 MB image upload in
         * full and then fail validation at 5 MB, which is the
         * slowest possible way to learn a file is too big.
         */
        var MAX_IMAGE_BYTES = {{ App\Http\Controllers\Admin\AdvertisementController::assetLimitKilobytes(App\Models\Advertisement::TYPE_IMAGE) * 1024 }};
        var MAX_VIDEO_BYTES = {{ App\Http\Controllers\Admin\AdvertisementController::assetLimitKilobytes(App\Models\Advertisement::TYPE_VIDEO) * 1024 }};

        if (!input) {
            return;
        }

        function complain(message) {
            error.textContent = message;
            error.style.display = message ? '' : 'none';
        }

        function closeCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            panel.style.display = 'none';
        }

        function reset() {
            closeCropper();
            hidden.value = '';
            preview.style.display = 'none';
            complain('');
        }

        function openCropper(source) {
            image.src = source;
            panel.style.display = '';
            preview.style.display = 'none';

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(image, {
                aspectRatio: 6,
                viewMode: 1,
                autoCropArea: 1
            });
        }

        /*
         * A video's length can only be read once the browser has
         * loaded its metadata, so this is checked here rather
         * than on the server, which would need ffmpeg for one
         * rule.
         */
        function checkVideo(file) {
            var probe = document.createElement('video');

            probe.preload = 'metadata';

            probe.onloadedmetadata = function () {
                window.URL.revokeObjectURL(probe.src);

                if (probe.duration > MAX_VIDEO_SECONDS) {
                    complain(
                        'That video is '
                        + Math.round(probe.duration)
                        + ' seconds. The limit is '
                        + MAX_VIDEO_SECONDS
                        + '.'
                    );

                    input.value = '';

                    if (window.adVideoFraming) {
                        window.adVideoFraming.forget();
                    }
                }
            };

            probe.src = window.URL.createObjectURL(file);
        }

        input.addEventListener('change', function () {
            reset();

            var file = input.files && input.files[0];

            if (!file) {
                return;
            }

            var isVideo = file.type.indexOf('video/') === 0;

            var limit = isVideo
                ? MAX_VIDEO_BYTES
                : MAX_IMAGE_BYTES;

            if (file.size > limit) {
                complain(
                    'That '
                    + (isVideo ? 'video' : 'image')
                    + ' is '
                    + (file.size / 1048576).toFixed(1)
                    + ' MB. The limit is '
                    + (limit / 1048576).toFixed(1)
                    + ' MB.'
                );

                input.value = '';

                return;
            }

            if (isVideo) {
                checkVideo(file);

                /* Seen straight away, then framed, as an image is cropped. */
                if (window.adVideoFraming) {
                    window.adVideoFraming.choose(window.URL.createObjectURL(file), file.name);
                }

                return;
            }

            if (window.adVideoFraming) {
                window.adVideoFraming.forget();
            }

            /*
             * An animated GIF is an image and renders as one,
             * but cropping flattens it to a single frame, so it
             * is left exactly as uploaded.
             */
            if (file.type === 'image/gif') {
                complain('');

                return;
            }

            if (file.type.indexOf('image/') !== 0) {
                return;
            }

            if (typeof Cropper === 'undefined') {
                return;
            }

            var reader = new FileReader();

            reader.onload = function (event) {
                openCropper(event.target.result);
            };

            reader.readAsDataURL(file);
        });

        if (confirmButton) {
            confirmButton.addEventListener('click', function () {
                if (!cropper) {
                    return;
                }

                var canvas = cropper.getCroppedCanvas({
                    width: 1456,
                    height: 243
                });

                if (!canvas) {
                    return;
                }

                var data = canvas.toDataURL('image/jpeg', 0.9);

                hidden.value = data;
                previewImage.src = data;

                closeCropper();
                preview.style.display = '';
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', reset);
        }

        if (reopenButton) {
            reopenButton.addEventListener('click', function () {
                var file = input.files && input.files[0];

                if (!file) {
                    return;
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    openCropper(event.target.result);
                };

                reader.readAsDataURL(file);
            });
        }
    });
</script>

<script>
    /*
     * Framing a video. A still is taken from the clip and cropped
     * like an image; what is kept is where the box sits, as
     * fractions of the picture. The preview then plays the clip
     * through that box, exactly as students will see it.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('assetInput') && document.getElementById('assetInput').form;
        var hidden = document.getElementById('videoCrop');
        var panel = document.getElementById('videoFramePanel');
        var still = document.getElementById('videoFrameImage');
        var preview = document.getElementById('videoPreview');
        var previewNote = document.getElementById('videoPreviewNote');
        var previewPlayer = document.getElementById('videoPreviewPlayer');
        var current = document.getElementById('currentVideo');
        var error = document.getElementById('assetError');

        if (! form || ! hidden || ! panel) {
            return;
        }

        var cropper = null;
        var source = null;
        var sourceName = '';

        function readCrop() {
            try {
                var crop = JSON.parse(hidden.value || 'null');

                return crop && crop.w > 0 && crop.h > 0 ? crop : null;
            } catch (e) {
                return null;
            }
        }

        /* The clip placed behind the 6:1 window so only the chosen part shows. */
        function placeVideo(video, crop) {
            if (! crop) {
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.left = '0';
                video.style.top = '0';
                video.style.objectFit = 'cover';

                return;
            }

            video.style.objectFit = 'fill';
            video.style.width = (100 / crop.w) + '%';
            video.style.height = (100 / crop.h) + '%';
            video.style.left = (-100 * crop.x / crop.w) + '%';
            video.style.top = (-100 * crop.y / crop.h) + '%';
        }

        function showPreview() {
            if (! source) {
                return;
            }

            if (previewPlayer.getAttribute('src') !== source) {
                previewPlayer.src = source;
            }

            placeVideo(previewPlayer, readCrop());
            previewNote.textContent = (sourceName ? sourceName + ': ' : '')
                + (readCrop() ? 'this part of the video will be shown.' : 'the whole video will be shown, filling the banner.');
            preview.style.display = '';

            if (current) {
                current.style.display = 'none';
            }
        }

        function closePanel() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            panel.style.display = 'none';
        }

        /* A still from about half a second in, or the middle of a shorter clip. */
        function takeStill(url, done) {
            var probe = document.createElement('video');

            probe.muted = true;
            probe.playsInline = true;
            probe.preload = 'auto';

            probe.addEventListener('loadeddata', function () {
                probe.currentTime = Math.min(0.5, (probe.duration || 1) / 2);
            }, { once: true });

            probe.addEventListener('seeked', function () {
                var canvas = document.createElement('canvas');

                canvas.width = probe.videoWidth;
                canvas.height = probe.videoHeight;
                canvas.getContext('2d').drawImage(probe, 0, 0);

                try {
                    done(canvas.toDataURL('image/jpeg', 0.85), probe.videoWidth, probe.videoHeight);
                } catch (e) {
                    done(null);
                }
            }, { once: true });

            probe.addEventListener('error', function () {
                done(null);
            }, { once: true });

            probe.src = url;
        }

        function openPanel() {
            if (! source || typeof Cropper === 'undefined') {
                showPreview();

                return;
            }

            takeStill(source, function (dataUrl, width, height) {
                if (! dataUrl) {
                    if (error) {
                        error.textContent = 'A still could not be taken from this video, so the whole video will be shown.';
                        error.style.display = '';
                    }

                    showPreview();

                    return;
                }

                preview.style.display = 'none';
                panel.style.display = '';
                still.src = dataUrl;

                if (cropper) {
                    cropper.destroy();
                }

                var crop = readCrop();

                cropper = new Cropper(still, {
                    aspectRatio: 6,
                    viewMode: 1,
                    autoCropArea: 1,
                    ready: function () {
                        if (crop) {
                            cropper.setData({
                                x: crop.x * width,
                                y: crop.y * height,
                                width: crop.w * width,
                                height: crop.h * height,
                            });
                        }
                    },
                });
            });
        }

        function changed() {
            if (typeof window.markUnsaved === 'function') {
                window.markUnsaved(form);
            }
        }

        document.getElementById('videoFrameConfirm').addEventListener('click', function () {
            if (! cropper) {
                return;
            }

            var data = cropper.getData(true);
            var image = cropper.getImageData();
            var width = image.naturalWidth;
            var height = image.naturalHeight;

            var round = function (value) {
                return Math.round(Math.max(0, Math.min(1, value)) * 100000) / 100000;
            };

            var crop = {
                x: round(data.x / width),
                y: round(data.y / height),
                w: round(data.width / width),
                h: round(data.height / height),
            };

            hidden.value = crop.w >= 0.999 && crop.h >= 0.999 ? '' : JSON.stringify(crop);
            changed();
            closePanel();
            showPreview();
        });

        document.getElementById('videoFrameWhole').addEventListener('click', function () {
            hidden.value = '';
            changed();
            closePanel();
            showPreview();
        });

        document.getElementById('videoFrameCancel').addEventListener('click', function () {
            closePanel();
            showPreview();
        });

        document.getElementById('videoFrameReopen').addEventListener('click', openPanel);

        var existing = document.getElementById('videoFrameExisting');

        if (existing && current) {
            existing.addEventListener('click', function () {
                source = current.getAttribute('data-src');
                sourceName = '';
                openPanel();
            });
        }

        window.adVideoFraming = {
            /* A new clip: shown at once, and framed from scratch. */
            choose: function (url, name) {
                source = url;
                sourceName = name || '';
                hidden.value = '';
                openPanel();
            },

            /* Not a video any more: no frame to keep. */
            forget: function () {
                closePanel();
                preview.style.display = 'none';

                if (current) {
                    current.style.display = '';
                }
            },
        };
    });
</script>

<script>
    /*
     * Show only the fields the chosen kind of advertisement
     * actually uses. Hidden fields keep their values, so
     * switching type to look and switching back does not quietly
     * discard what was already filled in.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var typeSelect = document.getElementById('typeSelect');

        if (!typeSelect) {
            return;
        }

        var blocks = document.querySelectorAll('[data-ad-field]');

        var assetLabel = document.getElementById('assetLabel');

        function applyType() {
            var current = typeSelect.value;

            if (assetLabel) {
                assetLabel.textContent = current === 'video' ? 'Video' : 'Image';
            }

            blocks.forEach(function (block) {
                var types = block
                    .getAttribute('data-ad-field')
                    .split(' ');

                block.style.display =
                    types.indexOf(current) === -1 ? 'none' : '';
            });
        }

        typeSelect.addEventListener('change', applyType);

        applyType();
    });
</script>