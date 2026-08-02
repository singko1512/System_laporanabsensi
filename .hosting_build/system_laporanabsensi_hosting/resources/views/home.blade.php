@extends('layouts.app')

@section('title', 'Absensi Harian - Employee Attendance System')

@section('styles')
<style>
    .hero-section {
        padding: 5rem 0 3rem;
        text-align: center;
    }

    .status-dot {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 1.5rem;
    }

    .status-dot::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--green);
        box-shadow: 0 0 0 3px rgba(0, 184, 148, 0.2);
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 3px rgba(0, 184, 148, 0.2); }
        50% { box-shadow: 0 0 0 6px rgba(0, 184, 148, 0.08); }
    }

    .hero-heading {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -1.5px;
        color: var(--dark);
        max-width: 640px;
        margin: 0 auto 1.25rem;
    }

    .hero-heading em {
        font-style: italic;
        text-decoration: underline;
        text-decoration-color: var(--primary-light);
        text-underline-offset: 4px;
        text-decoration-thickness: 3px;
    }

    .hero-sub {
        font-size: 1rem;
        color: var(--text-muted);
        max-width: 520px;
        margin: 0 auto;
        line-height: 1.65;
    }

    /* ── Feature Cards ── */
    .feature-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        max-width: 820px;
        margin: 3.5rem auto 0;
    }

    @media (max-width: 640px) {
        .feature-grid { grid-template-columns: 1fr; }
    }

    .feature-card {
        border-radius: 12px;
        padding: 2rem 2rem 1.75rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.3s ease;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        min-height: 240px;
    }

    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.08);
    }

    /* Left card — gradient purple */
    .card-employee {
        background: linear-gradient(160deg, #c8c2f7 0%, #a29bfe 40%, #6c5ce7 100%);
        color: #fff;
    }

    /* Right card — light tint */
    .card-admin {
        background: linear-gradient(160deg, #f0efff 0%, #e8e6ff 40%, #ddd8ff 100%);
        color: var(--dark);
        border: 1px solid rgba(108, 92, 231, 0.1);
    }

    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1.25rem;
    }

    .card-employee .feature-icon {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }

    .card-admin .feature-icon {
        background: rgba(108, 92, 231, 0.12);
        color: var(--primary);
    }

    .card-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-bottom: 0.4rem;
    }

    .card-employee .card-label { color: rgba(255,255,255,0.7); }
    .card-admin .card-label { color: var(--primary); }

    .card-title {
        font-size: 1.3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        letter-spacing: -0.3px;
    }

    .card-desc {
        font-size: 0.85rem;
        line-height: 1.6;
        margin-bottom: auto;
    }

    .card-employee .card-desc { color: rgba(255,255,255,0.8); }
    .card-admin .card-desc { color: var(--text-muted); }
    .admin-login-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: auto;
    }
    .admin-login-actions a {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.8rem;
        border-radius: 12px;
        background: rgba(108, 92, 231, 0.1);
        color: var(--primary);
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
    }
    .admin-login-actions a:hover {
        background: rgba(108, 92, 231, 0.16);
        color: var(--primary);
    }

    .card-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        font-weight: 700;
        margin-top: 1.25rem;
        text-decoration: none;
        transition: gap 0.2s ease;
    }

    .feature-card:hover .card-cta {
        gap: 0.6rem;
    }

    .card-employee .card-cta { color: #fff; }
    .card-admin .card-cta { color: var(--primary); }

    /* ── Schedule Section ── */
    /* ── Schedule Section ── */
    .schedule-section {
        max-width: 1180px;
        margin: 3rem auto 2.5rem;
    }

    .schedule-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .schedule-header {
        padding: 1.5rem 1.75rem 1.25rem;
        border-bottom: 1px solid var(--border);
    }

    .schedule-header h3 {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.25rem;
        letter-spacing: -0.3px;
    }

    .schedule-header p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 0;
    }

    .schedule-bidang-tabs {
        display: flex;
        gap: 0.25rem;
        padding: 1rem 1.5rem;
        background: #fff;
        border-bottom: 1px solid var(--border);
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #d8dcef transparent;
    }

    .schedule-bidang-tabs-inner {
        display: inline-flex;
        gap: 0.25rem;
        padding: 4px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #f8fafc;
        min-width: max-content;
    }

    .schedule-bidang-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 0;
        border-radius: 11px;
        background: transparent;
        color: var(--text-muted);
        padding: 0.58rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 800;
        line-height: 1.25;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .schedule-bidang-tab:hover {
        color: var(--primary);
        background: #fff;
    }

    .schedule-bidang-tab.active {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 2px 8px rgba(108, 92, 231, 0.28);
    }

    .schedule-bidang-tab-count {
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.82);
        color: var(--primary);
        padding: 0.15rem 0.45rem;
        font-size: 0.68rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .schedule-bidang-tab:not(.active) .schedule-bidang-tab-count {
        background: #fff;
        color: var(--text-muted);
    }

    .schedule-bidang-panel.d-none {
        display: none !important;
    }

    .schedule-bidang-title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
    }

    .schedule-bidang-title h4 {
        margin: 0;
        color: var(--dark);
        font-size: 0.92rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .schedule-bidang-count {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.32rem 0.65rem;
        white-space: nowrap;
    }

    .schedule-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .schedule-table thead th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.85rem 0.75rem;
        text-align: center;
        border-bottom: 1px solid var(--border);
    }

    .schedule-table thead th:first-child {
        text-align: left;
        padding-left: 1.5rem;
    }

    .schedule-table thead th.is-today {
        background: rgba(108, 92, 231, 0.08);
        color: var(--primary);
    }

    .schedule-table tbody td {
        padding: 0.85rem 0.75rem;
        text-align: center;
        border-bottom: 1px solid var(--border);
        font-size: 0.85rem;
    }

    .schedule-table tbody td:first-child {
        text-align: left;
        padding-left: 1.5rem;
        font-weight: 600;
        color: var(--dark);
    }

    .schedule-table tbody tr:last-child td {
        border-bottom: none;
    }

    .schedule-table tbody tr:hover {
        background: #fafbff;
    }

    .schedule-table tbody td.is-today {
        background: rgba(108, 92, 231, 0.04);
    }

    .team-header-row {
        background: #f8fafc !important;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }

    .team-header-row td {
        padding: 0.65rem 1.5rem !important;
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        text-align: left !important;
        letter-spacing: 0.3px;
    }

    .day-date {
        display: block;
        font-size: 0.68rem;
        font-weight: 500;
        color: var(--text-light);
        margin-top: 2px;
    }

    .loc-badge {
        display: inline-block;
        padding: 0.28em 0.65em;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .loc-wfo {
        background: rgba(0, 184, 148, 0.12);
        color: #059669;
    }

    .loc-wfh {
        background: rgba(108, 92, 231, 0.12);
        color: var(--primary);
    }

    .schedule-empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--text-muted);
    }

    .schedule-empty h6 {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.35rem;
    }

    .schedule-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        padding: 0.85rem 1.5rem;
        border-top: 1px solid var(--border);
        font-size: 0.78rem;
        color: var(--text-muted);
        background: #fafbff;
    }
</style>
@endsection

@section('content')
<div class="container">
    <!-- Hero -->
    <div class="hero-section">
        <div class="status-dot">Sistem aktif — Hari ini</div>
        <h1 class="hero-heading">
            Absensi & <em>Laporan Harian</em> yang ringkas dan modern
        </h1>
        <p class="hero-sub">
            Catat kehadiran, kirim laporan pekerjaan, dan pantau performa tim dalam satu antarmuka yang bersih.
        </p>
    </div>

    <!-- Two Feature Cards -->
    <div class="feature-grid">
        <!-- Employee Card -->
        <a href="{{ auth()->check() && auth()->user()->role === 'user' ? route('absensi.index') : route('login.form') }}" class="feature-card card-employee">
            <div class="feature-icon">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
            <div class="card-label">Untuk Peserta Magang</div>
            <div class="card-title">Menu Absensi & Laporan</div>
            <div class="card-desc">
                Login akun, isi absensi masuk/pulang, pilih task harian, dan kirim laporan pekerjaan.
            </div>
            <span class="card-cta">Mulai sekarang <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <!-- Admin Card -->
        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin'], true))
            <a href="{{ route('admin.dashboard') }}" class="feature-card card-admin">
                <div class="feature-icon">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
                <div class="card-label">Untuk Admin</div>
                <div class="card-title">Akses {{ session('admin_role') === 'superadmin' ? 'Super Admin' : 'Admin' }}</div>
                <div class="card-desc">
                    {{ auth()->user()->role === 'superadmin' ? 'Akses penuh untuk rekap, timeline, bidang, magang, dan sertifikat.' : 'Kelola data peserta magang dan sertifikat.' }}
                </div>
                <span class="card-cta">Buka Dashboard <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        @else
            <div class="feature-card card-admin">
                <div class="feature-icon">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
                <div class="card-label">Untuk Admin</div>
                <div class="card-title">Login Akun Sistem</div>
                <div class="card-desc">
                    Peserta, Admin, dan Super Admin memakai satu halaman login berbasis akun.
                </div>
                <div class="admin-login-actions">
                    <a href="{{ route('login.form') }}"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
                    <a href="{{ route('login.form', ['mode' => 'register']) }}"><i class="fa-solid fa-user-plus"></i> Register Peserta</a>
                </div>
            </div>
        @endif
    </div>

    {{-- Jadwal Mingguan (Mendukung Tampilan Tim & Perorangan Terintegrasi) --}}
    @php
        $getTeamBadgeColor = function($team) {
            $t = strtoupper(trim((string)$team));
            $colors = [
                'A' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => '🔵', 'title_bg' => '#dbeafe', 'title_text' => '#1e40af'],
                'B' => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0', 'icon' => '🟢', 'title_bg' => '#dcfce7', 'title_text' => '#166534'],
                'C' => ['bg' => '#faf5ff', 'text' => '#7e22ce', 'border' => '#e9d5ff', 'icon' => '🟣', 'title_bg' => '#f3e8ff', 'title_text' => '#6b21a8'],
                'D' => ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a', 'icon' => '🟡', 'title_bg' => '#fef3c7', 'title_text' => '#92400e'],
                'E' => ['bg' => '#fff1f2', 'text' => '#be123c', 'border' => '#fecdd3', 'icon' => '🔴', 'title_bg' => '#ffe4e6', 'title_text' => '#9f1239'],
                'F' => ['bg' => '#ecfeff', 'text' => '#0891b2', 'border' => '#a5f3fc', 'icon' => '🔷', 'title_bg' => '#cffafe', 'title_text' => '#155e75'],
            ];
            return $colors[$t] ?? ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1', 'icon' => '⚪', 'title_bg' => '#f1f5f9', 'title_text' => '#334155'];
        };

        $dayLabels = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
        ];

        $currentLandingMode = $jadwalLandingView ?? 'individual';
        $activeScheduleBidangIndex = $scheduleBidangGroups->search(fn ($group) => $group['users']->isNotEmpty());
        $activeScheduleBidangIndex = $activeScheduleBidangIndex === false ? 0 : $activeScheduleBidangIndex;
    @endphp

    <div class="schedule-section">
        <div class="schedule-card">
            {{-- Header Jadwal --}}
            <div class="schedule-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h3 class="mb-1"><i class="fa-solid fa-calendar-week me-2 text-primary"></i>Jadwal Kerja Minggu Ini</h3>
                        <p class="mb-0">{{ $weekStart->translatedFormat('d F') }} – {{ $weekEnd->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <span class="badge rounded-pill fw-semibold px-3 py-1.5" style="background:#f8fafc; color:var(--text-muted); border:1px solid var(--border); font-size:0.75rem;">
                            <i class="fa-solid {{ $currentLandingMode === 'team' ? 'fa-people-group text-primary' : 'fa-user text-primary' }} me-1"></i>
                            {{ $currentLandingMode === 'team' ? 'Tampilan Tim' : 'Tampilan Perorangan' }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($users->isEmpty())
                <div class="schedule-empty">
                    <h6>Belum ada jadwal</h6>
                    <p>Jadwal WFO/WFH akan tampil setelah admin menambahkan peserta magang dan mengatur jadwal.</p>
                </div>
            @else
                <div class="schedule-bidang-tabs" role="tablist" aria-label="Pilih bidang jadwal magang">
                    <div class="schedule-bidang-tabs-inner">
                        @foreach ($scheduleBidangGroups as $bidangGroup)
                            <button type="button"
                                class="schedule-bidang-tab {{ $loop->index === $activeScheduleBidangIndex ? 'active' : '' }}"
                                data-schedule-bidang-target="schedule-bidang-{{ $currentLandingMode }}-{{ $loop->index }}"
                                role="tab"
                                aria-selected="{{ $loop->index === $activeScheduleBidangIndex ? 'true' : 'false' }}">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>{{ $bidangGroup['nama'] }}</span>
                                <span class="schedule-bidang-tab-count">{{ $bidangGroup['users']->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($currentLandingMode === 'team')
                    {{-- MODE 1: TAMPILAN BERBASIS TIM --}}
                    <div id="homeScheduleTeamView">
                        @foreach ($scheduleBidangGroups as $bidangGroup)
                            @php $bidangUsers = $bidangGroup['users']; @endphp
                            <div class="schedule-bidang-group schedule-bidang-panel {{ $loop->index === $activeScheduleBidangIndex ? '' : 'd-none' }}" id="schedule-bidang-{{ $currentLandingMode }}-{{ $loop->index }}" role="tabpanel">
                                <div class="schedule-bidang-title">
                                    <h4><i class="fa-solid fa-layer-group me-2 text-primary"></i>{{ $bidangGroup['nama'] }}</h4>
                                    <span class="schedule-bidang-count">
                                        <i class="fa-solid fa-users"></i> {{ $bidangUsers->count() }} Peserta
                                    </span>
                                </div>

                                @if ($bidangUsers->isEmpty())
                                    <div class="schedule-empty py-4">
                                        <h6>Belum ada peserta magang</h6>
                                        <p>Peserta bidang ini akan tampil setelah ditambahkan oleh admin.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="schedule-table">
                                            <thead>
                                                <tr>
                                                    <th style="min-width:240px;">Peserta & Kelompok Tim</th>
                                                    @foreach ($dayLabels as $key => $label)
                                                        <th class="{{ $todayKey === $key ? 'is-today' : '' }}">
                                                            {{ $label }}
                                                            <span class="day-date">{{ $dayMap[$key]->format('d/m') }}</span>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $groupedUsers = $bidangUsers->groupBy(function($u) {
                                                        return $u->grup ?: 'A';
                                                    });
                                                    $teamsList = $availableTeams ?? ['A', 'B'];
                                                    foreach ($groupedUsers->keys() as $k) {
                                                        if (!in_array($k, $teamsList, true)) {
                                                            $teamsList[] = $k;
                                                        }
                                                    }
                                                    natsort($teamsList);
                                                @endphp

                                                @foreach ($teamsList as $teamCode)
                                                    @php
                                                        $teamMembers = $groupedUsers->get($teamCode, collect());
                                                        if ($teamMembers->isEmpty()) continue;
                                                        $style = $getTeamBadgeColor($teamCode);
                                                    @endphp

                                                    <tr class="team-header-row" style="background:{{ $style['bg'] }}!important; border-color:{{ $style['border'] }}!important;">
                                                        <td colspan="6" style="color:{{ $style['title_text'] }}!important; border-color:{{ $style['border'] }}!important;">
                                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                                <span>
                                                                    <span class="me-1">{{ $style['icon'] }}</span>
                                                                    <strong>KELOMPOK TIM {{ $teamCode }}</strong>
                                                                    <span class="badge rounded-pill ms-2 fw-semibold" style="background:{{ $style['title_bg'] }}; color:{{ $style['title_text'] }}; font-size:0.72rem; border:1px solid {{ $style['border'] }};">
                                                                        {{ $teamMembers->count() }} Peserta
                                                                    </span>
                                                                </span>
                                                                <span style="font-size:0.72rem; font-weight:600; opacity:0.85;">
                                                                    Pola kerja serentak tim
                                                                </span>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    @foreach ($teamMembers as $user)
                                                        @php $jadwal = $user->jadwalMingguan; @endphp
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" 
                                                                        style="width:28px; height:28px; background:{{ $style['bg'] }}; color:{{ $style['text'] }}; border:1px solid {{ $style['border'] }}; font-size:0.75rem;">
                                                                        {{ strtoupper(substr($user->nama, 0, 1)) }}
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-bold text-dark" style="font-size:0.85rem;">{{ $user->nama }}</div>
                                                                        <div class="text-muted" style="font-size:0.72rem;">Tim {{ $user->grup ?: 'A' }}</div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            @foreach ($dayLabels as $key => $label)
                                                                @php $loc = $jadwal ? $jadwal->forDay($key) : ($key === 'jumat' ? 'wfh' : 'wfo'); @endphp
                                                                <td class="{{ $todayKey === $key ? 'is-today' : '' }}">
                                                                    <span class="loc-badge loc-{{ $loc }}">{{ strtoupper($loc) }}</span>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- MODE 2: TAMPILAN PERORANGAN (REGULER TANPA TIM) --}}
                    <div id="homeScheduleIndividualView">
                        @foreach ($scheduleBidangGroups as $bidangGroup)
                            @php $bidangUsers = $bidangGroup['users']; @endphp
                            <div class="schedule-bidang-group schedule-bidang-panel {{ $loop->index === $activeScheduleBidangIndex ? '' : 'd-none' }}" id="schedule-bidang-{{ $currentLandingMode }}-{{ $loop->index }}" role="tabpanel">
                                <div class="schedule-bidang-title">
                                    <h4><i class="fa-solid fa-layer-group me-2 text-primary"></i>{{ $bidangGroup['nama'] }}</h4>
                                    <span class="schedule-bidang-count">
                                        <i class="fa-solid fa-users"></i> {{ $bidangUsers->count() }} Peserta
                                    </span>
                                </div>

                                @if ($bidangUsers->isEmpty())
                                    <div class="schedule-empty py-4">
                                        <h6>Belum ada peserta magang</h6>
                                        <p>Peserta bidang ini akan tampil setelah ditambahkan oleh admin.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="schedule-table">
                                            <thead>
                                                <tr>
                                                    <th style="min-width:240px;">Peserta Magang</th>
                                                    @foreach ($dayLabels as $key => $label)
                                                        <th class="{{ $todayKey === $key ? 'is-today' : '' }}">
                                                            {{ $label }}
                                                            <span class="day-date">{{ $dayMap[$key]->format('d/m') }}</span>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($bidangUsers as $user)
                                                    @php $jadwal = $user->jadwalMingguan; @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" 
                                                                    style="width:28px; height:28px; background:rgba(108, 92, 231, 0.1); color:var(--primary); font-size:0.75rem;">
                                                                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold text-dark" style="font-size:0.85rem;">{{ $user->nama }}</div>
                                                                    <div class="text-muted" style="font-size:0.72rem;">Tim {{ $user->grup ?: 'A' }}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        @foreach ($dayLabels as $key => $label)
                                                            @php $loc = $jadwal ? $jadwal->forDay($key) : ($key === 'jumat' ? 'wfh' : 'wfo'); @endphp
                                                            <td class="{{ $todayKey === $key ? 'is-today' : '' }}">
                                                                <span class="loc-badge loc-{{ $loc }}">{{ strtoupper($loc) }}</span>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Legend --}}
                <div class="schedule-legend">
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <span class="loc-badge loc-wfo">WFO</span>
                        <span><strong>Work From Office</strong> (Masuk Kantor)</span>
                    </span>
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <span class="loc-badge loc-wfh">WFH</span>
                        <span><strong>Work From Home</strong> (Bekerja dari Rumah)</span>
                    </span>
                    <span class="ms-auto text-muted" style="font-size:0.75rem;">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Jadwal diatur dan diperbarui oleh Admin
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bidangTabs = document.querySelectorAll('.schedule-bidang-tab');
        const bidangPanels = document.querySelectorAll('.schedule-bidang-panel');

        bidangTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const targetId = tab.dataset.scheduleBidangTarget;

                bidangTabs.forEach(function (item) {
                    item.classList.toggle('active', item === tab);
                    item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
                });

                bidangPanels.forEach(function (panel) {
                    panel.classList.toggle('d-none', panel.id !== targetId);
                });
            });
        });
    });
</script>
@endsection
