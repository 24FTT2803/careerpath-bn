@extends('admin.layouts.admin')

@section('title', 'Section Guides')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1><i class="fas fa-circle-question"></i> Section Guides</h1>
            <p class="subtitle">
                The help students see when they click the ? beside a section on Edit Profile and Career Analysis
            </p>
        </div>
    </div>

    @foreach($guidesByPage as $page => $guides)
        <div class="card" style="margin-bottom:20px;">
            <h3 style="margin:0 0 14px;font-size:15px;color:var(--text);">{{ $page }}</h3>

            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Video</th>
                            <th>Last updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guides as $guide)
                            <tr>
                                <td>
                                    <span class="cell-title">{{ $guide->title }}</span>
                                    <span
                                        class="cell-sub"
                                        style="display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;max-width:460px;"
                                    >{{ $guide->body }}</span>
                                </td>
                                <td>
                                    @if($guide->video_url)
                                        <span class="status-pill status-pill-green">Added</span>
                                    @else
                                        <span class="status-pill status-pill-muted">None</span>
                                    @endif
                                </td>
                                <td>{{ $guide->updated_at?->timezone(config('app.business_timezone'))->format('d M Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a
                                            href="{{ route('admin.business.guides.edit', $guide) }}"
                                            class="link"
                                        >Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
