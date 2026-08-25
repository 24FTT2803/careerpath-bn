@extends('layouts.app')

@section('title', 'Milestones')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-dash{
        --ink:#0d1a2b; --ink-dim:#5b6675; --paper:#faf8f2; --card:#ffffff; --line:#e7e2d4;
        --gold:#cf9a3d; --gold-bright:#e9b95a; --gold-wash:#fbf1de;
        --rose:#c65b4e; --rose-wash:#fbeceb; --green:#4c8a68; --green-wash:#e9f3ee;
        --purple:#7a5ea8; --purple-wash:#f1ecf7;
        --font-display:'Fraunces', Georgia, serif; --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        background:var(--paper); color:var(--ink); font-family:var(--font-body);
        margin:-24px -16px 0; padding:32px 20px 56px;
    }
    .cpbn-dash *{box-sizing:border-box}
    .cpbn-dash a{text-decoration:none;color:inherit}
    .cpbn-wrap{max-width:1000px;margin-inline:auto}

    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:22px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:24px;letter-spacing:-.01em}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}

    .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:5px;font-size:14px;font-weight:500;border:none;cursor:pointer;transition:background .15s}
    .cpbn-btn svg{width:14px;height:14px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}
    .cpbn-btn-muted{background:#eee9db;color:var(--ink)}
    .cpbn-btn-muted:hover{background:#e4dfcd}
    .cpbn-btn-sm{padding:7px 13px;font-size:12.5px}
    .cpbn-btn-green{background:var(--green);color:#fff}
    .cpbn-btn-green:hover{background:#3f7657}

    /* Add form */
    .cpbn-addform{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:22px;margin-bottom:22px;display:none}
    .cpbn-addform.open{display:block}
    .cpbn-fgrid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .cpbn-field{margin-bottom:0}
    .cpbn-field.full{grid-column:1 / -1}
    .cpbn-field label{display:block;font-size:13px;font-weight:500;margin-bottom:6px}
    .cpbn-field input,.cpbn-field select,.cpbn-field textarea{
        width:100%;padding:10px 13px;border-radius:4px;border:1px solid var(--line);background:#fff;
        font-family:var(--font-body);font-size:14px;color:var(--ink);
    }
    .cpbn-field input:focus,.cpbn-field select:focus,.cpbn-field textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(207,154,61,0.15)}
    .cpbn-form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px}

    /* Stats */
    .cpbn-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
    .cpbn-stat{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:18px;text-align:center}
    .cpbn-stat .n{font-family:var(--font-mono);font-size:24px;font-weight:500;color:#8a6420}
    .cpbn-stat .l{font-size:12px;color:var(--ink-dim);margin-top:4px}

    /* List */
    .cpbn-list{background:var(--card);border:1px solid var(--line);border-radius:6px;overflow:hidden}
    .cpbn-list-head{padding:14px 20px;border-bottom:1px solid var(--line);font-weight:600;font-size:14.5px}
    .cpbn-mrow{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:14px;transition:background .15s}
    .cpbn-mrow:last-child{border-bottom:none}
    .cpbn-mrow:hover{background:#faf8f0}
    .cpbn-mrow-left{display:flex;align-items:center;gap:14px}
    .cpbn-micon{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .cpbn-micon svg{width:18px;height:18px}
    .mi-done{background:var(--green-wash);color:var(--green)}
    .mi-pending{background:var(--gold-wash);color:#8a6420}
    .cpbn-mtitle{font-weight:500;font-size:14.5px}
    .cpbn-mtitle.done{text-decoration:line-through;color:var(--ink-dim)}
    .cpbn-mmeta{display:flex;align-items:center;gap:10px;margin-top:5px;flex-wrap:wrap}
    .cpbn-cat{font-family:var(--font-mono);font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;background:#eef1f5;color:var(--ink-dim);padding:3px 9px;border-radius:100px}
    .cpbn-mmeta span.date{font-size:11.5px;color:var(--ink-dim)}
    .cpbn-mmeta span.done-date{font-size:11.5px;color:var(--green);display:flex;align-items:center;gap:4px}
    .cpbn-mmeta span.done-date svg{width:11px;height:11px}
    .cpbn-mrow-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
    .cpbn-chip-done{font-family:var(--font-mono);font-size:11.5px;background:var(--green-wash);color:var(--green);padding:6px 12px;border-radius:100px;display:flex;align-items:center;gap:5px}
    .cpbn-chip-done svg{width:12px;height:12px}
    .cpbn-del{background:none;border:none;color:var(--rose);cursor:pointer;padding:6px}
    .cpbn-del svg{width:16px;height:16px}
    .cpbn-del:hover{color:#8f3a30}

    .cpbn-empty{padding:56px 20px;text-align:center}
    .cpbn-empty svg{width:34px;height:34px;color:var(--gold);margin-inline:auto;margin-bottom:14px}
    .cpbn-empty p.t{font-weight:500;font-size:14.5px}
    .cpbn-empty p.s{font-size:13px;color:var(--ink-dim);margin-top:4px}

    @media (max-width:760px){
        .cpbn-fgrid{grid-template-columns:1fr}
        .cpbn-field.full{grid-column:auto}
        .cpbn-stats{grid-template-columns:repeat(2,1fr)}
        .cpbn-mrow{flex-direction:column;align-items:flex-start}
        .cpbn-mrow-right{align-self:flex-end}
    }
</style>

<div class="cpbn-dash">
    <div class="cpbn-wrap">

        <div class="cpbn-head">
            <div>
                <h1>My Milestones</h1>
                <p class="sub">Track your learning and career progress</p>
            </div>
            <button onclick="document.getElementById('addMilestoneForm').classList.toggle('open')" class="cpbn-btn cpbn-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Add Milestone
            </button>
        </div>

        <!-- Add Milestone Form -->
        <div id="addMilestoneForm" class="cpbn-addform">
            <!-- REMOVED data-confirm-save from form -->
            <form action="{{ route('student.milestones.store') }}" method="POST">
                @csrf
                <div class="cpbn-fgrid">
                    <div class="cpbn-field">
                        <label>Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="cpbn-field">
                        <label>Category</label>
                        <select name="category">
                            <option value="academic">Academic</option>
                            <option value="career">Career</option>
                            <option value="personal">Personal</option>
                            <option value="skill">Skill</option>
                        </select>
                    </div>
                    <div class="cpbn-field full">
                        <label>Description</label>
                        <textarea name="description" rows="2"></textarea>
                    </div>
                    <div class="cpbn-field">
                        <label>Target Date</label>
                        <input type="date" name="target_date">
                    </div>
                </div>
                <div class="cpbn-form-actions">
                    <button type="button" onclick="document.getElementById('addMilestoneForm').classList.toggle('open')" class="cpbn-btn cpbn-btn-muted">
                        Cancel
                    </button>
                    <!-- KEEP data-confirm-save ONLY on submit button -->
                    <button type="submit" class="cpbn-btn cpbn-btn-primary" data-confirm-save>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Add Milestone
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="cpbn-stats">
            <div class="cpbn-stat">
                <p class="n">{{ $milestones->count() }}</p>
                <p class="l">Total Milestones</p>
            </div>
            <div class="cpbn-stat">
                <p class="n">{{ $milestones->where('is_completed', true)->count() }}</p>
                <p class="l">Completed</p>
            </div>
            <div class="cpbn-stat">
                <p class="n">{{ $milestones->where('is_completed', false)->count() }}</p>
                <p class="l">In Progress</p>
            </div>
            <div class="cpbn-stat">
                <p class="n">
                    {{ $milestones->count() > 0 ? round(($milestones->where('is_completed', true)->count() / $milestones->count()) * 100) : 0 }}%
                </p>
                <p class="l">Completion Rate</p>
            </div>
        </div>

        <!-- Milestones List -->
        <div class="cpbn-list">
            <div class="cpbn-list-head">All Milestones</div>

            @forelse($milestones as $milestone)
                <div class="cpbn-mrow">
                    <div class="cpbn-mrow-left">
                        <div class="cpbn-micon {{ $milestone->is_completed ? 'mi-done' : 'mi-pending' }}">
                            @if($milestone->is_completed)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            @endif
                        </div>
                        <div>
                            <p class="cpbn-mtitle {{ $milestone->is_completed ? 'done' : '' }}">{{ $milestone->title }}</p>
                            <div class="cpbn-mmeta">
                                <span class="cpbn-cat">{{ $milestone->category }}</span>
                                @if($milestone->target_date)
                                    <span class="date">Target: {{ $milestone->target_date->format('d M Y') }}</span>
                                @endif
                                @if($milestone->is_completed && $milestone->completed_date)
                                    <span class="done-date">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                        Completed: {{ $milestone->completed_date->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="cpbn-mrow-right">
                        @if(!$milestone->is_completed)
                            <form action="{{ route('student.milestones.complete', $milestone) }}" method="POST" data-confirm-update data-item-name="{{ $milestone->title }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="cpbn-btn cpbn-btn-green cpbn-btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                    Complete
                                </button>
                            </form>
                        @else
                            <span class="cpbn-chip-done">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                Done
                            </span>
                        @endif
                        <form action="{{ route('student.milestones.destroy', $milestone) }}" method="POST" data-confirm-delete data-item-name="{{ $milestone->title }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cpbn-del" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="cpbn-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    <p class="t">No milestones yet</p>
                    <p class="s">Click "Add Milestone" to start tracking your progress</p>
                </div>
            @endforelse
        </div>

    </div>
</div>

@endsection