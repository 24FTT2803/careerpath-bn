@extends('admin.layouts.admin')

@section('title', 'Manage Proficiency Levels')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-level-up-alt"></i> Proficiency Levels</h1>
            <p class="text-muted">Manage proficiency levels used in the BIICF framework</p>
        </div>
        <a href="{{ route('admin.biicf.proficiency-levels.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Proficiency Level
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                            <tr>
                                <td>
                                    <span class="level-badge level-{{ $level->level_number }}">
                                        Level {{ $level->level_number }}
                                    </span>
                                </td>
                                <td><strong>{{ $level->name }}</strong></td>
                                <td>{{ Str::limit($level->description, 60) ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.biicf.proficiency-levels.edit', $level) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.biicf.proficiency-levels.destroy', $level) }}" method="POST" class="d-inline" data-confirm-delete data-item-name="{{ $level->name }}">
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
                                <td colspan="4" class="text-center">No proficiency levels found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $levels->links() }}
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
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 20px; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { text-align: left; padding: 12px 16px; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
    .table td { padding: 12px 16px; border-bottom: 1px solid #e5e7eb; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .text-center { text-align: center; }
    .d-inline { display: inline; }
    .level-badge { display: inline-block; padding: 4px 14px; border-radius: 100px; font-size: 12px; font-weight: 600; }
    .level-1 { background: #e8f0fe; color: #2a5a8c; }
    .level-2 { background: #e9f3ee; color: #2d8f5c; }
    .level-3 { background: #fbf1de; color: #8a6420; }
    .level-4 { background: #f1ecf7; color: #7a5ea8; }
    .level-5 { background: #fbeceb; color: #c65b4e; }
</style>
@endsection