<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Human Resource Analytics Dashboard">
    <title>@yield('title', 'Human Resource Analytics') | Kelompok 3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $role = session('hr_role', 'karyawan');
    $isAdmin = $role === 'admin';
    $isHrd = $role === 'hrd';
    $isOwner = $role === 'owner';
    $isKaryawan = $role === 'karyawan';
    $displayName = session('hr_display_name', 'User');
    $email = session('hr_email', '-');
    $avatar = strtoupper(substr($displayName, 0, 1));
    $roleLabels = ['admin' => 'Administrator', 'hrd' => 'HR Departemen', 'owner' => 'Pemilik', 'karyawan' => 'Karyawan'];
    $roleLabel = $roleLabels[$role] ?? 'User';
@endphp
<body>
<div class="sidebar-overlay"></div>
<div class="app-shell">

    {{-- ── Sidebar ─────────────────────────────────────── --}}
    <aside class="sidebar" aria-label="Sidebar navigasi">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="bi bi-people-fill fs-5 text-white"></i>
                </div>
                <div>
                    <div class="sidebar-logo-text">HR Analytics</div>
                    <div class="sidebar-logo-sub">Kelompok 3</div>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav" aria-label="Navigasi utama">
            <div class="sidebar-section">Menu Utama</div>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            @if ($isAdmin || $isHrd)
                <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Data Karyawan
                </a>
                <a href="{{ route('risks.index') }}" class="sidebar-link {{ request()->routeIs('risks.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i> Risiko Turnover
                </a>
                <a href="{{ route('factors.index') }}" class="sidebar-link {{ request()->routeIs('factors.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Analisis Faktor
                </a>
            @endif

            @if ($isAdmin || $isHrd || $isOwner)
                <a href="{{ route('exports.index') }}" class="sidebar-link {{ request()->routeIs('exports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Report
                </a>
            @endif

            @if ($isAdmin)
                <a href="{{ route('import.index') }}" class="sidebar-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
                    <i class="bi bi-upload"></i> Import Data
                </a>
                <a href="{{ route('data-sources.index') }}" class="sidebar-link {{ request()->routeIs('data-sources.*') ? 'active' : '' }}">
                    <i class="bi bi-database"></i> Sumber Data
                </a>
                <a href="{{ route('sync-logs.index') }}" class="sidebar-link {{ request()->routeIs('sync-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat"></i> Sync Logs
                </a>
            @endif

            <div class="sidebar-section">Lainnya</div>

            @if ($isAdmin)
                <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Pengaturan
                </a>
            @endif
            <a href="{{ route('about') }}" class="sidebar-link {{ request()->routeIs('about') ? 'active' : '' }}">
                <i class="bi bi-info-circle"></i> Tentang Project
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ $avatar }}</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="sidebar-user-name">{{ $displayName }}</div>
                    <div class="sidebar-user-email">{{ $email }}</div>
                </div>
            </div>
            <div style="padding:0 16px 12px;">
                <span class="badge bg-opacity-10 text-muted" style="font-size:10px;background:var(--card-border);">{{ $roleLabel }}</span>
            </div>
        </div>
    </aside>

    {{-- ── Main Content ─────────────────────────────────── --}}
    <main class="main-content">
        {{-- Topbar --}}
        <div class="topbar">
            <button id="sidebarToggle" class="sidebar-toggle" type="button" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-date">
                <i class="bi bi-calendar3"></i>
                <span id="topbarCurrentDate">-</span>
            </div>
            <div class="topbar-right">
                <div class="dropdown">
                    <button class="btn btn-sm" style="border:1px solid var(--card-border);border-radius:8px;" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu profil">
                        <i class="bi bi-person-circle text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:10px;border:1px solid var(--card-border);min-width:200px;">
                        <li><span class="dropdown-item-text small text-muted">{{ $displayName }}</span></li>
                        <li><span class="dropdown-item-text small text-muted" style="font-size:10px;">{{ $roleLabel }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Profil Kelompok</a></li>
                        @if ($isAdmin)
                            <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-body">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert" style="border-radius:10px;border:none;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert" style="border-radius:10px;border:none;">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:10px;border:none;">
                    @foreach ($errors->all() as $error)
                        <div class="d-flex align-items-center gap-2"><i class="bi bi-x-circle-fill"></i> {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
