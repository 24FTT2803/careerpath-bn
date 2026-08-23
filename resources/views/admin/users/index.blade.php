@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                Manage Users
            </h1>
            <p class="sub">Create, edit, and manage user accounts</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="cpbn-btn cpbn-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
            Add User
        </a>
    </div>

    <!-- Search and Filter -->
    <form method="GET" class="cpbn-filterbar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email...">
        <select name="role">
            <option value="">All Roles</option>
            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
            <option value="lecturer" {{ request('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <button type="submit" class="cpbn-btn cpbn-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.users.index') }}" class="cpbn-btn cpbn-btn-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 1 3 6.7"/><path d="M3 4v6h6"/></svg>
            Reset
        </a>
    </form>

    <!-- Users Table -->
    <div class="cpbn-table-wrap">
        <table class="cpbn-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Programme</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td style="font-weight:500">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="cpbn-pill {{ $user->role == 'admin' ? 'pill-rose' : ($user->role == 'lecturer' ? 'pill-gold' : 'pill-green') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->programme ?? '-' }}</td>
                        <td class="center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="link" style="margin-right:12px">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;display:inline;vertical-align:-1px"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                Edit
                            </a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline" data-confirm-delete data-item-name="{{ $user->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:var(--rose);cursor:pointer;font-size:13px;font-family:inherit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;display:inline;vertical-align:-1px"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="cpbn-empty-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="cpbn-pagination">
        {{ $users->withQueryString()->links() }}
    </div>
</div>

<!-- Confirmation Styles -->
<style>
    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:22px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:24px;letter-spacing:-.01em;display:flex;align-items:center;gap:8px}
    .cpbn-head h1 svg{width:22px;height:22px;color:var(--gold)}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}
    .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:5px;font-size:14px;font-weight:500;border:none;cursor:pointer;transition:background .15s;text-decoration:none;font-family:inherit}
    .cpbn-btn svg{width:14px;height:14px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}
    .cpbn-btn-muted{background:#eee9db;color:var(--ink)}
    .cpbn-btn-muted:hover{background:#e4dfcd}
    .cpbn-filterbar{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;align-items:center}
    .cpbn-filterbar input,.cpbn-filterbar select{padding:10px 13px;border:1px solid var(--line);border-radius:4px;background:#fff;font-size:14px;font-family:var(--font-body);min-width:180px}
    .cpbn-filterbar input:focus,.cpbn-filterbar select:focus{outline:none;border-color:var(--gold)}
    .cpbn-table-wrap{background:var(--card);border:1px solid var(--line);border-radius:6px;overflow:hidden}
    .cpbn-table{width:100%;border-collapse:collapse;font-size:14px}
    .cpbn-table th{text-align:left;padding:12px 16px;background:#f4f1e7;font-weight:600;color:var(--ink-dim)}
    .cpbn-table td{padding:12px 16px;border-top:1px solid var(--line)}
    .cpbn-table .center{text-align:center}
    .cpbn-pill{display:inline-block;padding:3px 12px;border-radius:100px;font-size:11.5px;font-weight:500}
    .pill-green{background:var(--green-wash);color:var(--green)}
    .pill-gold{background:var(--gold-wash);color:#8a6420}
    .pill-rose{background:var(--rose-wash);color:var(--rose)}
    .cpbn-empty-row{text-align:center;padding:48px 20px;color:var(--ink-dim)}
    .cpbn-empty-row svg{width:32px;height:32px;margin-inline:auto;margin-bottom:10px;display:block}
    .cpbn-pagination{margin-top:16px;font-size:13.5px;color:var(--ink-dim)}
    .link{color:#8a6420;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
    .link:hover{color:var(--gold)}
</style>
@endsection