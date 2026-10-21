@extends('admin.layouts.admin')

@section('title', 'Lecturers')

@section('content')
<div class="lec-page">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-chalkboard-teacher"></i> Lecturers</h1>
            <p class="subtitle">Assign lecturers to the classes they teach</p>
        </div>
        <div class="lec-stats">
            <div class="lec-stat"><span class="n">{{ $stats['total'] }}</span><span class="l">Total</span></div>
            <div class="lec-stat"><span class="n">{{ $stats['assigned'] }}</span><span class="l">Assigned</span></div>
            <div class="lec-stat" title="Lecturers not yet assigned to any group">
                <span class="n {{ $stats['unassigned'] > 0 ? 'warn' : '' }}">{{ $stats['unassigned'] }}</span>
                <span class="l">Need assignment</span>
            </div>
        </div>
    </div>

    <form method="GET" class="lec-filter">
        <div class="lec-field">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email...">
        </div>
        <select name="status" class="lec-select">
            <option value="">All lecturers</option>
            <option value="unassigned" {{ request('status') === 'unassigned' ? 'selected' : '' }}>Need assignment</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('admin.lecturers.index') }}" class="btn btn-outline"><i class="fas fa-undo"></i> Reset</a>
    </form>

    <div class="lec-grid">
        @forelse($lecturers as $lecturer)
            @php
                $mine = $assignments->get($lecturer->id, collect());
                $available = $groups->whereNotIn('id', $mine->pluck('organisation_group_id'));
                $labels = $groups->keyBy('id');
            @endphp
            <div class="lec-card">
                <div class="lec-head">
                    <div class="lec-avatar">{{ strtoupper(substr($lecturer->name, 0, 1)) }}</div>
                    <div class="lec-info">
                        <h4>{{ $lecturer->name }}</h4>
                        <span>{{ $lecturer->email }}</span>
                    </div>
                    @if($mine->isEmpty())
                        <span class="lec-flag"><i class="fas fa-exclamation-circle"></i> Unassigned</span>
                    @endif
                </div>

                <div class="lec-chips">
                    @forelse($mine as $assignment)
                        @php $group = $assignment->organisationGroup; @endphp
                        @if($group)
                            <form method="POST"
                                  action="{{ route('admin.lecturers.unassign', [$lecturer, $group]) }}"
                                  onsubmit="return confirm('Remove {{ $lecturer->name }} from {{ $group->name }}?')">
                                @csrf
                                @method('DELETE')
                                <span class="lec-chip" title="{{ $labels[$group->id]->label ?? $group->name }}">
                                    {{ $group->name }}
                                    <button type="submit" aria-label="Remove">&times;</button>
                                </span>
                            </form>
                        @endif
                    @empty
                        <span class="lec-none">Not assigned to any group yet</span>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.lecturers.assign', $lecturer) }}" class="lec-assign">
                    @csrf
                    <select name="organisation_group_id" class="lec-select" required>
                        <option value="">Assign to a group...</option>
                        @foreach($available as $g)
                            <option value="{{ $g->id }}">{{ $g->label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Assign</button>
                </form>
            </div>
        @empty
            <div class="lec-empty">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>No lecturers found</h3>
                <p>Try changing your search or filter.</p>
            </div>
        @endforelse
    </div>

    <div class="lec-pagination">{{ $lecturers->links() }}</div>
</div>

<style>
    .lec-stats { display:flex; gap:24px; background:#fff; padding:12px 24px; border-radius:12px; border:1px solid var(--border); }
    .lec-stat { text-align:center; }
    .lec-stat .n { display:block; font-family:'Playfair Display',serif; font-size:24px; font-weight:700; color:var(--primary); }
    .lec-stat .n.warn { color:var(--warning); }
    .lec-stat .l { font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em; }

    .lec-filter { display:flex; gap:12px; flex-wrap:wrap; align-items:center; background:#fff; padding:16px 20px; border-radius:12px; border:1px solid var(--border); margin-bottom:24px; }
    .lec-field { position:relative; flex:1; min-width:200px; }
    .lec-field i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:14px; }
    .lec-field input { width:100%; padding:10px 12px 10px 36px; border:2px solid var(--border); border-radius:8px; font-size:14px; font-family:inherit; }
    .lec-field input:focus, .lec-select:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 4px rgba(201,168,76,.15); }
    .lec-select { padding:10px 12px; border:2px solid var(--border); border-radius:8px; font-size:13px; font-family:inherit; background:#fff; }

    .lec-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr)); gap:20px; margin-bottom:24px; }
    .lec-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:18px 20px; display:flex; flex-direction:column; gap:14px; transition:all .3s ease; }
    .lec-card:hover { border-color:var(--accent); box-shadow:0 12px 48px rgba(0,0,0,.08); transform:translateY(-3px); }
    .lec-head { display:flex; align-items:center; gap:12px; }
    .lec-avatar { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,#1a3a5c,#2a5a8c); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; flex-shrink:0; }
    .lec-info { flex:1; min-width:0; }
    .lec-info h4 { margin:0; font-size:15px; font-weight:600; color:var(--primary); }
    .lec-info span { font-size:12px; color:var(--text-muted); display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .lec-flag { font-size:11px; font-weight:600; color:#8a6420; background:var(--gold-wash); padding:4px 10px; border-radius:100px; white-space:nowrap; }

    .lec-chips { display:flex; flex-wrap:wrap; gap:8px; min-height:30px; align-items:center; }
    .lec-chips form { display:inline; }
    .lec-chip { display:inline-flex; align-items:center; gap:6px; background:#e8f0fe; color:var(--primary-light); font-size:12px; font-weight:600; padding:4px 6px 4px 12px; border-radius:100px; }
    .lec-chip button { border:0; background:transparent; color:inherit; cursor:pointer; font-size:15px; line-height:1; width:20px; height:20px; border-radius:50%; }
    .lec-chip button:hover { background:rgba(192,57,43,.15); color:var(--danger); }
    .lec-none { font-size:12px; color:#9ca3af; font-style:italic; }

    .lec-assign { display:flex; gap:8px; margin-top:auto; }
    .lec-assign .lec-select { flex:1; min-width:0; }

    .lec-empty { grid-column:1/-1; text-align:center; padding:60px 20px; background:#fff; border:2px dashed var(--border); border-radius:12px; color:var(--text-muted); }
    .lec-empty i { font-size:56px; color:var(--border); margin-bottom:12px; }
    .lec-empty h3 { color:var(--primary); margin:0 0 4px; }
    .lec-pagination { display:flex; justify-content:center; }

    @media (max-width:768px) {
        .lec-grid { grid-template-columns:1fr; }
        .lec-stats { justify-content:space-around; width:100%; }
    }
</style>
@endsection