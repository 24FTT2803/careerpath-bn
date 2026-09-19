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
                    Position
                </label>

                <select
                    name="position"
                    class="field-input"
                >
                    <option
                        value="one"
                        @selected(old('position', $advertisement->position) === 'one')
                    >Position one</option>

                    <option
                        value="two"
                        @selected(old('position', $advertisement->position) === 'two')
                    >Position two</option>
                </select>

                <p class="field-hint">
                    Position one sits above the page content and
                    position two below it, at every screen size.
                </p>
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

            <p class="field-hint">
                Banners are shown at 6:1, so 1456&times;243 is a
                good size. Images up to 2 MB, video up to 10 MB.
                Leave empty to keep the current file.
            </p>

            <input type="hidden" name="cropped_asset" id="croppedAsset">

            <div id="cropperPanel" style="display:none;margin-top:12px">
                <p class="text-xs text-gray-500 mb-2">
                    Drag to choose the part students will see.
                </p>

                <div style="max-width:728px">
                    <img id="cropperImage" alt="" style="max-width:100%">
                </div>

                <button
                    type="button"
                    id="cropperClear"
                    class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded-lg text-xs transition"
                >
                    Use the whole image instead
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
        var clear = document.getElementById('cropperClear');
        var form = input ? input.closest('form') : null;
        var cropper = null;

        if (!input || !form || typeof Cropper === 'undefined') {
            return;
        }

        function stop() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            panel.style.display = 'none';
            hidden.value = '';
        }

        input.addEventListener('change', function () {
            stop();

            var file = input.files && input.files[0];

            if (!file || file.type.indexOf('image/') !== 0) {
                return;
            }

            var reader = new FileReader();

            reader.onload = function (event) {
                image.src = event.target.result;
                panel.style.display = '';

                cropper = new Cropper(image, {
                    aspectRatio: 6,
                    viewMode: 1,
                    autoCropArea: 1
                });
            };

            reader.readAsDataURL(file);
        });

        clear.addEventListener('click', stop);

        form.addEventListener('submit', function () {
            if (!cropper) {
                return;
            }

            var canvas = cropper.getCroppedCanvas({
                width: 1456,
                height: 243
            });

            if (canvas) {
                hidden.value = canvas.toDataURL('image/jpeg', 0.9);
            }
        });
    });
</script>
