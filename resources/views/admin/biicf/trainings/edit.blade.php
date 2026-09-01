@extends('admin.layouts.admin')

@section('title', 'Edit Training')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-edit"></i> Edit Training</h1>
            <p class="text-muted">Update "{{ $training->name }}"</p>
        </div>
        <a href="{{ route('admin.biicf.trainings') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.biicf.trainings.update', $training) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Training Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $training->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Provider</label>
                    <input type="text" name="provider" class="form-control @error('provider') is-invalid @enderror" 
                           value="{{ old('provider', $training->provider) }}">
                    @error('provider')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Certification Body</label>
                    <input type="text" name="certification_body" class="form-control @error('certification_body') is-invalid @enderror" 
                           value="{{ old('certification_body', $training->certification_body) }}">
                    @error('certification_body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" 
                           value="{{ old('url', $training->url) }}" placeholder="https://...">
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="3">{{ old('description', $training->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Training
                    </button>
                </div>
            </form>
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
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 10px 20px; border-radius: 8px; font-weight: 500; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
    .text-danger { color: #c0392b; }
    .form-control { width: 100%; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
    .form-control:focus { border-color: #c9a84c; outline: none; box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.15); }
    .is-invalid { border-color: #c0392b; }
    .invalid-feedback { color: #c0392b; font-size: 12px; margin-top: 4px; }
    .form-actions { margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; }
</style>
@endsection