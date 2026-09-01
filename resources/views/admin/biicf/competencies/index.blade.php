@extends('admin.layouts.admin')

@section('title', 'Manage Competencies')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-tools"></i> Competencies</h1>
            <p class="text-muted">Manage technical and soft skill competencies</p>
        </div>
        <a href="{{ route('admin.biicf.competencies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Competency
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="filter-bar mb-3">
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <div class="filter-field">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search competencies..." class="form-control">
                    </div>
                    <div class="filter-field">
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="technical" {{ request('type') == 'technical' ? 'selected' : '' }}>Technical</option>
                            <option value="soft_skill" {{ request('type') == 'soft_skill' ? 'selected' : '' }}>Soft Skill</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.biicf.competencies') }}" class="btn btn-outline btn-sm">Reset</a>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($competencies as $competency)
                            <tr>
                                <td>{{ $competency->id }}</td>
                                <td><strong>{{ $competency->name }}</strong></td>
                                <td>
                                    <span class="badge {{ $competency->type == 'technical' ? 'badge-blue' : 'badge-green' }}">
                                        {{ $competency->type == 'technical' ? 'Technical' : 'Soft Skill' }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($competency->description, 50) ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.biicf.competencies.edit', $competency) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.biicf.competencies.destroy', $competency) }}" method="POST" class="d-inline" data-confirm-delete data-item-name="{{ $competency->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No competencies found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $competencies->links() }}
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
    .badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 500; }
    .badge-blue { background: #e8f0fe; color: #2a5a8c; }
    .badge-green { background: #e9f3ee; color: #2d8f5c; }
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