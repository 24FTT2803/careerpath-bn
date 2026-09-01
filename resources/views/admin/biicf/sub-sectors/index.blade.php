@extends('admin.layouts.admin')

@section('title', 'Manage ICT Sub-Sectors')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-layer-group"></i> ICT Sub-Sectors</h1>
            <p class="text-muted">Manage ICT sub-sectors in the BIICF framework</p>
        </div>
        <a href="{{ route('admin.biicf.sub-sectors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Sub-Sector
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
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Lead Organisation</th>
                            <th>Job Roles</th>
                            <th>Sort Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subSectors as $subSector)
                            <tr>
                                <td>{{ $subSector->id }}</td>
                                <td><strong>{{ $subSector->name }}</strong></td>
                                <td><code>{{ $subSector->slug }}</code></td>
                                <td>{{ $subSector->lead_organisation ?? '-' }}</td>
                                <td><span class="badge badge-primary">{{ $subSector->job_roles_count }}</span></td>
                                <td>{{ $subSector->sort_order }}</td>
                                <td>
                                    <a href="{{ route('admin.biicf.sub-sectors.edit', $subSector) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.biicf.sub-sectors.destroy', $subSector) }}" method="POST" class="d-inline" data-confirm-delete data-item-name="{{ $subSector->name }}">
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
                                <td colspan="7" class="text-center">No sub-sectors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $subSectors->links() }}
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
    .badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 500; }
    .badge-primary { background: #e8f0fe; color: #2a5a8c; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .text-center { text-align: center; }
    code { background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
    .d-inline { display: inline; }
</style>
@endsection