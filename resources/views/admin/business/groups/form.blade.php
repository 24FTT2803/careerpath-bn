@extends('admin.layouts.admin')

@section('title', $group->exists ? 'Edit Group' : 'New Group')

@section('content')
@php
    $isEdit = $group->exists;
    $currentParentId = $group->exists
        ? $group->primaryParent()?->id
        : null;
@endphp

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $isEdit ? '✏️ Edit Group' : '🏫 New Group' }}
            </h1>
            <p class="text-gray-600">
                Where this group sits in the structure
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.index') }}"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition"
        >
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
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
                ? route('admin.business.groups.update', $group)
                : route('admin.business.groups.store')
        }}"
        class="bg-white rounded-lg shadow p-6"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $group->name) }}"
                    maxlength="120"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Code
                </label>

                <input
                    type="text"
                    name="code"
                    value="{{ old('code', $group->code) }}"
                    maxlength="40"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >

                <p class="text-xs text-gray-500 mt-1">
                    Optional short reference, such as DADT04.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Type
                </label>

                <select
                    name="group_type_id"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >
                    @foreach($types as $type)
                        <option
                            value="{{ $type->id }}"
                            @selected((int) old('group_type_id', $group->group_type_id) === $type->id)
                        >{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sits inside
                </label>

                <select
                    name="parent_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                >
                    <option value="">Top level</option>

                    @foreach($parents as $parent)
                        <option
                            value="{{ $parent->id }}"
                            @selected((int) old('parent_id', $currentParentId) === $parent->id)
                        >
                            {{ $parent->name }} ({{ $parent->type?->name }})
                        </option>
                    @endforeach
                </select>

                <p class="text-xs text-gray-500 mt-1">
                    A class sits inside a programme, a programme
                    inside a school, and so on.
                </p>
            </div>
        </div>

        @if($isEdit)
            <div class="mb-6">
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $group->is_active))
                    >

                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>
        @endif

        <button
            type="submit"
            class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition"
        >
            {{ $isEdit ? 'Save changes' : 'Create group' }}
        </button>
    </form>
</div>
@endsection
