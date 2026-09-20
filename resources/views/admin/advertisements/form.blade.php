@extends('admin.layouts.admin')

@section('title', $advertisement->exists ? 'Edit Advertisement' : 'New Advertisement')

@section('content')
@php
    $isEdit = $advertisement->exists;
@endphp

<div>
    <div class="page-header">
        <div>
            <h1>
                {{ $isEdit ? '✏️ Edit Advertisement' : '📣 New Advertisement' }}
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

            <div>
                <label class="field-label">
                    Placement
                </label>

                @if($lockedPosition !== null)
                    <input
                        type="hidden"
                        name="position"
                        value="{{ $lockedPosition }}"
                    >

                    <p class="field-input" style="background:#f9fafb;">
                        {{ $lockedPosition === 'one'
                            ? 'Above the page'
                            : 'Below the page' }}
                    </p>

                    <p class="field-hint">
                        Set by the placement this was opened from.
                    </p>
                @else
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
                @endif
            </div>
        </div>

        <div class="mb-4">
            <label class="field-label">
                Image
            </label>

            @if($advertisement->asset_path)
                <div class="mb-3">
                    <p class="text-xs text-gray-500 mb-1">Currently showing</p>

                    <img
                        src="{{ $advertisement->mediaUrl() }}"
                        alt=""
                        style="width:100%;max-width:728px;aspect-ratio:6/1;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb"
                    >
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
                $uploadLimitMb = round(
                    App\Http\Controllers\Admin\AdvertisementController::uploadLimitKilobytes() / 1024,
                    1
                );
            @endphp

            <p class="field-hint">
                Banners are shown at 6:1, so 1456&times;243 is a
                good size. This server accepts files up to
                {{ $uploadLimitMb }} MB, and video up to
                {{ App\Http\Controllers\Admin\AdvertisementController::MAX_VIDEO_SECONDS }}
                seconds. An animated GIF is kept as it is, since
                cropping would flatten it. Leave empty to keep
                the current file.
            </p>

            <p id="assetError" class="field-hint" style="display:none;color:#c0392b;"></p>

            <input type="hidden" name="cropped_asset" id="croppedAsset">

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

        <div class="mb-4">
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
            <div>
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

        var MAX_UPLOAD_BYTES = {{ App\Http\Controllers\Admin\AdvertisementController::uploadLimitKilobytes() * 1024 }};

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

            if (file.size > MAX_UPLOAD_BYTES) {
                complain(
                    'That file is '
                    + (file.size / 1048576).toFixed(1)
                    + ' MB. This server accepts up to '
                    + (MAX_UPLOAD_BYTES / 1048576).toFixed(1)
                    + ' MB.'
                );

                input.value = '';

                return;
            }

            if (file.type.indexOf('video/') === 0) {
                checkVideo(file);

                return;
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