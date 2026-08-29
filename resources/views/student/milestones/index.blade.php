@extends('layouts.app')

@section('title', 'Milestones')

@section('content')

<style>
    .milestone-page {
        padding: 24px 0 40px;
    }

    .milestone-page .wrap {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .milestone-page .head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .milestone-page .head h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1a3a5c;
        margin: 0;
    }

    .milestone-page .head h1 span {
        color: #c9a84c;
    }

    .milestone-page .head .sub {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .btn-primary {
        background: #c9a84c;
        color: #0d1f33;
    }

    .btn-primary:hover {
        background: #e8d4a0;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(201, 168, 76, 0.25);
    }

    .btn-muted {
        background: #e5e7eb;
        color: #1a1a2e;
    }

    .btn-muted:hover {
        background: #d1d5db;
        transform: translateY(-2px);
    }

    .btn-success {
        background: #2d8f5c;
        color: #fff;
    }

    .btn-success:hover {
        background: #1e6b44;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }

    /* Add Form */
    .add-form {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        display: none;
    }

    .add-form.open {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-grid .full {
        grid-column: 1 / -1;
    }

    .field {
        margin-bottom: 0;
    }

    .field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 4px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .field label .req {
        color: #c0392b;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Inter', -apple-system, sans-serif;
        transition: all 0.3s ease;
        background: #fff;
        color: #1a1a2e;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.15);
    }

    .field textarea {
        resize: vertical;
        min-height: 60px;
    }

    .field .hint {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .field .error-text {
        color: #c0392b;
        font-size: 12px;
        margin-top: 4px;
        display: none;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .field .error-input {
        border-color: #c0392b !important;
        box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.15) !important;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }

    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 40px rgba(26, 58, 92, 0.15);
    }

    .stat-card .number {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #a88830;
    }

    .stat-card .label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    /* Milestone List */
    .milestone-list {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .milestone-list .list-header {
        padding: 14px 20px;
        background: #f4f6f9;
        font-weight: 600;
        font-size: 14px;
        color: #1a3a5c;
        border-bottom: 1px solid #e5e7eb;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .milestone-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        gap: 14px;
    }

    .milestone-item:last-child {
        border-bottom: none;
    }

    .milestone-item:hover {
        background: #f8f6f0;
    }

    .milestone-item .left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }

    .milestone-item .icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    .milestone-item .icon.completed {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .milestone-item .icon.pending {
        background: #fbf1de;
        color: #a88830;
    }

    .milestone-item .icon.overdue {
        background: #fbeceb;
        color: #c65b4e;
    }

    .milestone-item .icon.past {
        background: #f1ecf7;
        color: #7a5ea8;
    }

    .milestone-item .info .title {
        font-weight: 600;
        font-size: 14px;
        color: #1a3a5c;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .milestone-item .info .title.done {
        text-decoration: line-through;
        color: #6b7280;
    }

    .milestone-item .info .meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .milestone-item .info .meta .category {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 10px;
        border-radius: 100px;
        background: #f4f6f9;
        color: #6b7280;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .milestone-item .info .meta .date {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .milestone-item .info .meta .status-badge {
        font-size: 11px;
        font-weight: 500;
        padding: 2px 10px;
        border-radius: 100px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .milestone-item .info .meta .status-badge.overdue {
        background: #fbeceb;
        color: #c65b4e;
    }

    .milestone-item .info .meta .status-badge.past {
        background: #f1ecf7;
        color: #7a5ea8;
    }

    .milestone-item .info .meta .status-badge.completed {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .milestone-item .right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .milestone-item .right .done-badge {
        font-size: 11px;
        font-weight: 500;
        color: #2d8f5c;
        background: #e9f3ee;
        padding: 4px 12px;
        border-radius: 100px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .milestone-item .right .delete-btn {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .milestone-item .right .delete-btn:hover {
        color: #c0392b;
        background: #fbeceb;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 48px;
        color: #e5e7eb;
        margin-bottom: 16px;
    }

    .empty-state h4 {
        font-size: 18px;
        color: #1a3a5c;
        margin-bottom: 4px;
        font-family: 'Playfair Display', serif;
    }

    .empty-state p {
        font-size: 14px;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .form-grid {
            grid-template-columns: 1fr;
        }
        .form-grid .full {
            grid-column: 1;
        }
        .milestone-item {
            flex-direction: column;
            align-items: stretch;
        }
        .milestone-item .right {
            justify-content: flex-end;
        }
        .milestone-page .head h1 {
            font-size: 24px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .milestone-item .left {
            flex-direction: column;
            align-items: flex-start;
        }
        .milestone-item .info .meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
    }
</style>

<div class="milestone-page">
    <div class="wrap">

        <div class="head">
            <div>
                <h1>My <span>Milestones</span></h1>
                <p class="sub">Track your learning and career progress</p>
            </div>
            <button onclick="document.getElementById('addForm').classList.toggle('open')" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Milestone
            </button>
        </div>

        <!-- Add Form -->
        <div id="addForm" class="add-form">
            <form action="{{ route('student.milestones.store') }}" method="POST" id="milestone-form">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label>Title <span class="req">*</span></label>
                        <input type="text" name="title" id="milestone-title" placeholder="Enter milestone title" required>
                        <div class="error-text" id="title-error">Please enter a title</div>
                        @error('title')
                            <div class="error-text" style="display:block;color:#c0392b;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="field">
                        <label>Category <span class="req">*</span></label>
                        <select name="category" required>
                            <option value="academic">Academic</option>
                            <option value="career">Career</option>
                            <option value="personal">Personal</option>
                            <option value="skill">Skill</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label>Description</label>
                        <textarea name="description" rows="2" placeholder="Describe this milestone..."></textarea>
                    </div>
                    <div class="field">
                        <label>Target Date</label>
                        <input type="date" name="target_date">
                        <div class="hint"><i class="fas fa-info-circle"></i> Past dates will be auto-marked as completed</div>
                    </div>
                    <div class="field">
                    <label>Proof of Completion</label>
                    <input type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div class="hint">PDF, JPG, JPEG, PNG, DOC, DOCX · Max 10 MB</div>
                </div>

                </div>
                <div class="form-actions">
                    <button type="button" onclick="document.getElementById('addForm').classList.toggle('open')" class="btn btn-muted">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit-milestone" data-confirm-save>
                        <i class="fas fa-plus"></i> Add Milestone
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number">{{ $milestones->count() }}</div>
                <div class="label">Total Milestones</div>
            </div>
            <div class="stat-card">
                <div class="number">{{ $milestones->where('is_completed', true)->count() }}</div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="number">{{ $milestones->where('is_completed', false)->count() }}</div>
                <div class="label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="number">
                    {{ $milestones->count() > 0 ? round(($milestones->where('is_completed', true)->count() / $milestones->count()) * 100) : 0 }}%
                </div>
                <div class="label">Completion Rate</div>
            </div>
        </div>

        <!-- Milestone List -->
        <div class="milestone-list">
            <div class="list-header"><i class="fas fa-list"></i> All Milestones</div>

            @forelse($milestones as $milestone)
                @php
                    $isOverdue = !$milestone->is_completed && $milestone->target_date && now()->startOfDay() > \Carbon\Carbon::parse($milestone->target_date)->endOfDay();
                    $isPast = !$milestone->is_completed && $milestone->target_date && now()->startOfDay() > \Carbon\Carbon::parse($milestone->target_date)->endOfDay();
                @endphp

                <div class="milestone-item">
                    <div class="left">
                        <div class="icon {{ $milestone->is_completed ? 'completed' : ($isOverdue ? 'overdue' : ($isPast ? 'past' : 'pending')) }}">
                            @if($milestone->is_completed)
                                <i class="fas fa-check"></i>
                            @elseif($isOverdue)
                                <i class="fas fa-exclamation"></i>
                            @else
                                <i class="fas fa-clock"></i>
                            @endif
                        </div>
                        <div class="info">
                            <div class="title {{ $milestone->is_completed ? 'done' : '' }}">{{ $milestone->title }}</div>
                            <div class="meta">
                                <span class="category">{{ $milestone->category }}</span>
                                @if($milestone->target_date)
                                    <span class="date">
                                        <i class="fas fa-calendar-alt"></i>
                                        Target: {{ $milestone->target_date->format('d M Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="status-badge overdue">
                                            <i class="fas fa-exclamation-triangle"></i> Overdue
                                        </span>
                                    @elseif($isPast && !$milestone->is_completed)
                                        <span class="status-badge past">
                                            <i class="fas fa-clock"></i> Past Due
                                        </span>
                                    @endif
                                @endif
                                @if($milestone->is_completed && $milestone->completed_date)
                                    <span class="status-badge completed">
                                        <i class="fas fa-check-circle"></i>
                                        Completed: {{ $milestone->completed_date->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="right">
                        @if(!$milestone->is_completed)
                            <form action="{{ route('student.milestones.complete', $milestone) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm" data-confirm-update data-item-name="{{ $milestone->title }}">
                                    <i class="fas fa-check"></i> {{ $isPast ? 'Mark Done' : 'Complete' }}
                                </button>
                            </form>
                        @else
                            <span class="done-badge"><i class="fas fa-check-circle"></i> Done</span>
                        @endif
                        <form action="{{ route('student.milestones.destroy', $milestone) }}" method="POST" style="display:inline;" data-confirm-delete data-item-name="{{ $milestone->title }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-flag-checkered"></i>
                    <h4>No milestones yet</h4>
                    <p>Click "Add Milestone" to start tracking your progress.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('milestone-form');
        const titleInput = document.getElementById('milestone-title');
        const titleError = document.getElementById('title-error');
        const submitBtn = document.getElementById('submit-milestone');

        if (form && titleInput) {
            form.addEventListener('submit', function(e) {
                const title = titleInput.value.trim();
                if (title === '') {
                    e.preventDefault();
                    titleInput.classList.add('error-input');
                    titleError.style.display = 'block';
                    titleInput.focus();
                    return false;
                }
                titleInput.classList.remove('error-input');
                titleError.style.display = 'none';
                return true;
            });

            titleInput.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('error-input');
                    titleError.style.display = 'none';
                }
            });

            titleInput.addEventListener('focus', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('error-input');
                    titleError.style.display = 'none';
                }
            });
        }
    });
</script>

@endsection