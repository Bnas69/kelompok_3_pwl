@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
@php
    $roleLabel = ($account['role'] ?? 'user') === 'admin' ? 'Administrator' : 'User';
@endphp

<div class="page-header">
    <div>
        <p class="page-eyebrow">Pengaturan</p>
        <h1>Pengaturan Akun</h1>
        <p class="page-subtitle mb-0">Kelola profil dan keamanan akun administrator.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Profile --}}
    <div class="col-lg-6">
        <div class="card-dashboard h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <span class="page-eyebrow">Profil</span>
                        <h2 class="card-title mb-0">Profil Akun</h2>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-auto">{{ $roleLabel }}</span>
                </div>

                <form method="post" action="{{ route('settings.profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Nama Lengkap</label>
                        <input id="display_name" name="display_name" class="form-control" value="{{ old('display_name', $account['display_name'] ?? '') }}" maxlength="100" required>
                        @error('display_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $account['email'] ?? '') }}" maxlength="120" placeholder="contoh@email.com">
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input class="form-control" value="{{ $account['username'] }}" disabled>
                        <div class="text-muted small mt-1">Username tidak dapat diubah.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Password --}}
    <div class="col-lg-6">
        <div class="card-dashboard h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width:48px;height:48px;border-radius:12px;background:#fef2f2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <span class="page-eyebrow">Keamanan</span>
                        <h2 class="card-title mb-0">Ubah Password</h2>
                    </div>
                </div>

                <form method="post" action="{{ route('settings.password.update') }}">
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
                        @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" minlength="8" required>
                        <div class="text-muted small mt-1">Minimal 8 karakter.</div>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-lock me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- App Info --}}
    <div class="col-lg-6">
        <div class="card-dashboard h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width:48px;height:48px;border-radius:12px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <span class="page-eyebrow">Informasi Aplikasi</span>
                        <h2 class="card-title mb-0">Tentang Aplikasi</h2>
                    </div>
                </div>
                <table class="table table-borderless mb-0 small">
                    <tbody>
                        <tr><td class="text-muted ps-0" style="width:40%;">Nama Aplikasi</td><td class="fw-semibold ps-0">{{ $appInfo['name'] }}</td></tr>
                        <tr><td class="text-muted ps-0" style="width:40%;">Versi</td><td class="fw-semibold ps-0">{{ $appInfo['version'] }}</td></tr>
                        <tr><td class="text-muted ps-0" style="width:40%;">Kelompok</td><td class="fw-semibold ps-0">{{ $appInfo['group'] }}</td></tr>
                        <tr><td class="text-muted ps-0" style="width:40%;">Program Studi</td><td class="fw-semibold ps-0">{{ $appInfo['study_program'] }}</td></tr>
                        <tr><td class="text-muted ps-0" style="width:40%;">Tahun Akademik</td><td class="fw-semibold ps-0">{{ $appInfo['academic_year'] }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Security --}}
    <div class="col-lg-6">
        <div class="card-dashboard h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width:48px;height:48px;border-radius:12px;background:#f5f3ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                        <i class="bi bi-key"></i>
                    </div>
                    <div>
                        <span class="page-eyebrow">Sesi & Keamanan</span>
                        <h2 class="card-title mb-0">Keamanan Akun</h2>
                    </div>
                </div>
                <table class="table table-borderless mb-3 small">
                    <tbody>
                        <tr><td class="text-muted ps-0" style="width:40%;">Terakhir Login</td><td class="fw-semibold ps-0">{{ $account['last_login_at'] ?? 'Belum ada catatan login' }}</td></tr>
                        <tr><td class="text-muted ps-0" style="width:40%;">Role</td><td class="fw-semibold ps-0">{{ $roleLabel }}</td></tr>
                    </tbody>
                </table>
                <div class="d-flex gap-2">
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
