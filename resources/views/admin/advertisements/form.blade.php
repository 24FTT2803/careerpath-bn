@extends('admin.layouts.admin')

@section('title', $advertisement->exists ? 'Edit Advertisement' : 'New Advertisement')

@section('content')
@php
    $isEdit = $advertisement->exists;
@endphp

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $isEdit ? '✏️ Edit Advertisement' : '📣 New Advertisement' }}
            </h1>
            <p class="text-gray-600">
                What appears in the placement, and who sees it
            </p>
        </div>

        <a
            href="{{ route('admin.business.advertisements.index') }}"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition"
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
        class="bg-white rounded-lg shadow p-6"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Title
            </label>

            <input
                type="text"
                name="title"
                value="{{ old('title', $advertisement->title) }}"
                maxlength="120"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
            >

            <p class="text-xs text-gray-500 mt-1">
                For your own reference. Students do not see this.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Type
                </label>

                <select
                    name="type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
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
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Position
                </label>

                <select
                    name="position"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
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

                <p class="text-xs text-gray-500 mt-1">
                    Each position moves to suit the screen size.
                    On a wide screen it sits in a side column;
                    on a narrow one it moves into the page.
                </p>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Upload
            </label>

            <input
                type="file"
                name="asset"
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
            >

            <p class="text-xs text-gray-500 mt-1">
                Images up to 2 MB, video up to 10 MB. Leave empty
                to use an external address instead, or to keep
                the current file.
            </p>

            @if($advertisement->asset_path)
                <p class="text-xs text-gray-600 mt-2">
                    Current file:
                    <a
                        href="{{ $advertisement->mediaUrl() }}"
                        target="_blank"
                        class="underline"
                    >{{ basename($advertisement->asset_path) }}</a>
                </p>
            @endif
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                External address
            </label>

            <input
                type="url"
                name="external_url"
                value="{{ old('external_url', $advertisement->external_url) }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
                placeholder="https://"
            >

            <p class="text-xs text-gray-500 mt-1">
                Used when the media is hosted elsewhere, or for
                an ad network embed.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Click destination
                </label>

                <input
                    type="url"
                    name="click_url"
                    value="{{ old('click_url', $advertisement->click_url) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                    placeholder="https://"
                >

                <p class="text-xs text-gray-500 mt-1">
                    Where a student goes if they click. Optional.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alternative text
                </label>

                <input
                    type="text"
                    name="alt_text"
                    value="{{ old('alt_text', $advertisement->alt_text) }}"
                    maxlength="160"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >

                <p class="text-xs text-gray-500 mt-1">
                    Read aloud by screen readers and shown if the
                    image cannot load.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Starts
                </label>

                <input
                    type="date"
                    name="starts_at"
                    value="{{ old('starts_at', $advertisement->starts_at?->timezone(config('app.business_timezone'))->format('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ends
                </label>

                <input
                    type="date"
                    name="ends_at"
                    value="{{ old('ends_at', $advertisement->ends_at?->timezone(config('app.business_timezone'))->format('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Audience
                </label>

                <select
                    name="organisation_group_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >
                    <option value="">Everyone</option>

                    @foreach($groups as $group)
                        <option
                            value="{{ $group->id }}"
                            @selected((int) old('organisation_group_id', $advertisement->organisation_group_id) === $group->id)
                        >{{ $group->name }}</option>
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

                <span class="text-sm text-gray-700">Active</span>
            </label>
        </div>

        <button
            type="submit"
            class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition"
        >
            {{ $isEdit ? 'Save changes' : 'Create advertisement' }}
        </button>
    </form>
</div>
@endsection
