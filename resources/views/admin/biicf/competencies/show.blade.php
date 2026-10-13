@extends('admin.layouts.admin')

@section('title', $competency->name)

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-tools"></i> {{ $competency->name }}</h1>
            <p class="text-muted">
                <span class="badge {{ $competency->type === 'technical' ? 'badge-blue' : 'badge-green' }}">
                    {{ $competency->type === 'technical' ? 'Technical' : 'Soft Skill' }}
                </span>
                rated over {{ $scale->count() }} levels
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.biicf.competencies.edit', $competency) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.biicf.competencies') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="card-header">Definition</div>
            <div class="card-body">
                @if($competency->short_definition)
                    <p class="in-essence">{{ $competency->short_definition }}</p>
                @endif

                @if($competency->description)
                    <p class="definition">{{ $competency->description }}</p>
                @else
                    <p class="nothing">The framework's definition has not been recorded for this competency.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Required by</div>
            <div class="card-body">
                @if($requiredBy === [])
                    <p class="nothing">No job role asks for this competency.</p>
                @else
                    <table class="mini-table">
                        <tbody>
                            @foreach($requiredBy as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.biicf.job-roles.show', $row['role']) }}">
                                            {{ $row['role']->title }}
                                        </a>
                                        <span class="tier">{{ $row['tier'] }}</span>
                                    </td>
                                    <td class="level-cell">
                                        @if($row['level'])
                                            {{ $row['level']->level_number }}. {{ $row['level']->name }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{--
        What the book says at each rung. A rung it leaves out is
        shown as left out: the competency does not reach that
        level, which is not the same as reaching it with nothing
        to say.
    --}}
    @foreach($sets as $set)
        <div class="card">
            <div class="card-header">
                {{ $set['scope'] !== '' ? $set['scope'] : 'What it means at each level' }}
                @if($set['job_roles']->isNotEmpty())
                    <span class="scope-note">{{ $set['job_roles']->pluck('title')->join(', ') }}</span>
                @endif
            </div>
            <div class="card-body">
                @foreach($set['levels'] as $row)
                    <div class="level {{ $row['descriptor'] === null ? 'is-absent' : '' }}">
                        <div class="level-head">
                            <span class="level-name">
                                {{ $row['level']->level_number }}. {{ $row['level']->name }}
                            </span>
                            @if($row['descriptor'] === null)
                                <span class="badge-na">N/A</span>
                            @endif
                        </div>

                        @if($row['descriptor'] === null)
                            <p class="nothing">The framework does not describe this competency at this level.</p>
                        @else
                            <ul class="statements">
                                @foreach($row['descriptor']->statements ?? [] as $statement)
                                    <li>{{ $statement }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<style>
    .biicf-page { padding: 0 4px; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a3a5c; margin: 0; }
    .page-header h1 i { color: #c9a84c; }
    .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .text-muted { color: #6b7280; font-size: 14px; margin-top: 6px; display: flex; align-items: center; gap: 8px; }
    .btn-primary { background: #c9a84c; color: #0d1f33; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
    .btn-primary:hover { background: #e8d4a0; transform: translateY(-2px); }
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 10px 20px; border-radius: 8px; font-weight: 500; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 20px; }
    .card-header { padding: 16px 20px; background: #faf8f2; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 16px; color: #1a3a5c; display: flex; flex-wrap: wrap; align-items: baseline; gap: 10px; }
    .scope-note { font-size: 12px; font-weight: 400; color: #6b7280; }
    .card-body { padding: 20px; }
    .badge { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    .badge-blue { background: #eaf0f7; color: #1a3a5c; }
    .badge-green { background: #e9f3ee; color: #2d8f5c; }
    .in-essence { margin: 0 0 12px; font-size: 15px; font-weight: 600; color: #1a3a5c; line-height: 1.6; }
    .definition { margin: 0; font-size: 14px; line-height: 1.7; color: #1a1a2e; }
    .nothing { margin: 0; font-size: 13px; color: #6b7280; font-style: italic; }
    .mini-table { width: 100%; border-collapse: collapse; }
    .mini-table td { padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; vertical-align: top; }
    .mini-table tr:last-child td { border-bottom: none; }
    .mini-table a { color: #1a3a5c; font-weight: 500; }
    .tier { display: block; font-size: 12px; color: #6b7280; }
    .level-cell { text-align: right; white-space: nowrap; color: #1a3a5c; font-weight: 500; }
    .level { padding: 14px 0; border-bottom: 1px solid #f3f4f6; }
    .level:last-child { border-bottom: none; }
    .level.is-absent { opacity: .65; }
    .level-head { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
    .level-name { font-weight: 600; color: #1a3a5c; }
    .badge-na { padding: 2px 8px; border-radius: 999px; background: #f3f4f6; color: #6b7280; font-size: 11px; font-weight: 600; letter-spacing: .04em; }
    .statements { margin: 0; padding-left: 20px; }
    .statements li { font-size: 14px; line-height: 1.7; color: #1a1a2e; margin-bottom: 4px; }
</style>
@endsection
