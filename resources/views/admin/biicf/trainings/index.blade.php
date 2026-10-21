@extends('admin.layouts.admin')

@section('title', 'Manage Trainings')

@section('content')
@php
    $rows = $trainings->map(fn ($training) => [
        'id' => $training->id,
        'name' => $training->name,
        'roles' => $training->job_roles_count,
        'edit_url' => route('admin.biicf.trainings.edit', $training),
        'destroy_url' => route('admin.biicf.trainings.destroy', $training),
    ])->values();
@endphp

@include('admin.biicf.partials.table')

<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-graduation-cap"></i> Trainings &amp; Certifications</h1>
            <p class="text-muted">Manage trainings and certifications in the BIICF framework</p>
        </div>
        <a href="{{ route('admin.biicf.trainings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Training
        </a>
    </div>

    <div class="card" x-data="adminTable(@js($rows), { sort: 'name', searchable: ['name'] })">
        <div class="card-body">
            <div class="filter-bar mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <div class="filter-field">
                        <input type="text" x-model="query" @input="page = 1"
                               placeholder="Search trainings..." class="form-control">
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" @click="reset()">Reset</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="sortable" :class="{ 'is-sorted': sort.key === 'id' }" @click="sortBy('id')">
                                ID <i :class="caret('id')"></i>
                            </th>
                            <th class="sortable" :class="{ 'is-sorted': sort.key === 'name' }" @click="sortBy('name')">
                                Name <i :class="caret('name')"></i>
                            </th>
                            {{--
                                Worth sorting on: a course no role
                                recommends is one nothing in the
                                framework points at any more.
                            --}}
                            <th class="sortable" :class="{ 'is-sorted': sort.key === 'roles' }" @click="sortBy('roles')">
                                Recommended for <i :class="caret('roles')"></i>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in shown" :key="row.id">
                            <tr>
                                <td x-text="row.id"></td>
                                <td><strong x-text="row.name"></strong></td>
                                <td>
                                    <span class="badge badge-primary">
                                        <span x-text="row.roles"></span>
                                        <span x-text="row.roles === 1 ? 'role' : 'roles'"></span>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a :href="row.edit_url" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form :action="row.destroy_url" method="POST" class="d-inline"
                                          @submit.prevent="confirmDelete($event.target, row.name)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="!filtered.length">
                            <td colspan="4" class="text-center">No trainings found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @include('admin.biicf.partials.pager')
        </div>
    </div>
</div>

<style>
    .biicf-page { padding: 0 4px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a3a5c; margin: 0; }
    .page-header h1 i { color: #c9a84c; }
    .text-muted { color: #6b7280; font-size: 14px; margin-top: 2px; }
    .btn-primary { background: #c9a84c; color: #0d1f33; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
    .btn-primary:hover { background: #e8d4a0; transform: translateY(-2px); }
    .btn-sm { padding: 4px 10px; font-size: 12px; border-radius: 6px; }
    .btn-warning { background: #fbf1de; color: #8a6420; border: none; }
    .btn-warning:hover { background: #e8d4a0; }
    .btn-danger { background: #fbeceb; color: #c65b4e; border: none; }
    .btn-danger:hover { background: #c65b4e; color: white; }
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 8px 16px; border-radius: 8px; font-weight: 500; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 20px; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { text-align: left; padding: 12px 16px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
    .table td { padding: 12px 16px; border-bottom: 1px solid #e5e7eb; }
    .table-responsive { overflow-x: auto; }
    .actions { white-space: nowrap; }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 500; }
    .badge-primary { background: #e8f0fe; color: #2a5a8c; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .form-control { padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; min-width: 150px; }
    .filter-bar .filter-field { min-width: 180px; }
    .d-flex { display: flex; }
    .flex-wrap { flex-wrap: wrap; }
    .gap-2 { gap: 8px; }
    .mb-3 { margin-bottom: 16px; }
    .text-center { text-align: center; }
    .d-inline { display: inline; }
</style>
@endsection
