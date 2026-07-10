@extends('layouts.app')

@section('title', $mode === 'create' ? 'Tambah Sumber Data' : 'Edit Sumber Data')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Admin Data</p>
        <h1>{{ $mode === 'create' ? 'Tambah Sumber Data' : 'Edit Sumber Data' }}</h1>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body">
        <form method="post" action="{{ $mode === 'create' ? route('data-sources.store') : route('data-sources.update', $source) }}">
            @csrf
            @if ($mode === 'edit')
                @method('put')
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama sumber</label>
                    <input id="name" name="name" class="form-control" value="{{ old('name', $source->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="type" class="form-label">Tipe sumber</label>
                    <select id="type" name="type" class="form-select" required>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $source->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label for="source_url" class="form-label">URL / path sumber</label>
                    <input id="source_url" name="source_url" class="form-control" value="{{ old('source_url', $source->source_url) }}" placeholder="https://... atau mysql://host:3306/database?table=employees">
                </div>
                <div class="col-md-4">
                    <label for="auth_type" class="form-label">Tipe Auth</label>
                    <select id="auth_type" name="auth_type" class="form-select">
                        @foreach ($authTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('auth_type', $source->auth_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="api_key" class="form-label">API Key / Credential</label>
                    <input id="api_key" name="api_key" class="form-control" value="{{ old('api_key') }}" placeholder="{{ $mode === 'edit' ? 'Kosongkan jika tidak diubah' : 'Opsional' }}">
                </div>
                <div class="col-md-4">
                    <label for="sync_interval_minutes" class="form-label">Interval Sync (menit)</label>
                    <input id="sync_interval_minutes" type="number" min="15" max="1440" name="sync_interval_minutes" class="form-control" value="{{ old('sync_interval_minutes', $source->sync_interval_minutes) }}" required>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $source->is_active))>
                        <label class="form-check-label" for="is_active">Aktifkan sumber data</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                    <a class="btn btn-outline-secondary" href="{{ route('data-sources.index') }}"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
