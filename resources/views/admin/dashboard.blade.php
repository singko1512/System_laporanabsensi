@extends('layouts.admin')

@section('title', 'Admin Dashboard - Absensi & Laporan Harian')

@section('styles')
<style>
    /* Kanban Board Styles */
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin-top: 1.5rem;
    }
    @media (max-width: 768px) {
        .kanban-board { grid-template-columns: 1fr; }
    }
    .kanban-col {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 1.25rem;
        min-height: 400px;
    }
    .kanban-col-header {
        display: flex;
        justify-content: justify-content;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1.5px solid var(--border);
        padding-bottom: 0.5rem;
    }
    .kanban-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.01);
    }
    .kanban-card-title {
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--dark);
    }

    /* Gantt Chart / Timeline Styles */
    .gantt-chart {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 1.25rem;
        overflow-x: auto;
        margin-top: 1.25rem;
    }
    .gantt-row {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .gantt-row:last-child {
        border-bottom: none;
    }
    .gantt-label {
        width: 180px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--dark);
        padding-right: 1rem;
    }
    .gantt-timeline-container {
        flex-grow: 1;
        position: relative;
        height: 28px;
        background: #f8fafc;
        border-radius: 6px;
        overflow: hidden;
    }
    .gantt-bar {
        position: absolute;
        height: 100%;
        background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
        border-radius: 6px;
        display: flex;
        align-items: center;
        padding-left: 0.5rem;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
    }

    /* Stats Dashboard Admin Styles */
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .admin-stat-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.015);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .admin-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .admin-stat-info {
        flex-grow: 1;
    }
    .admin-stat-val {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1.1;
    }
    .admin-stat-lbl {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 0.15rem;
    }

    /* Planning vs Actual bars */
    .progress-comparison {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
        background: #fafbff;
        border: 1px solid var(--border);
        padding: 1rem;
        border-radius: 12px;
    }
    .comparison-bar-row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .comparison-bar-label {
        width: 80px;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--text-muted);
    }
    .comparison-bar-bg {
        flex-grow: 1;
        height: 14px;
        background: var(--border);
        border-radius: 10px;
        overflow: hidden;
    }
    .comparison-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease;
    }

    .schedule-select {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.35rem 0.5rem;
        font-size: 0.78rem;
        font-weight: 600;
        background: #fff;
        min-width: 72px;
    }
    .schedule-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .btn-dice {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.15rem;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-dice:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        color: #fff;
    }

    .jumat-fixed {
        display: inline-block;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        background: rgba(99, 102, 241, 0.12);
        color: var(--primary);
    }

    .monitor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 0.85rem;
    }

    .monitor-item {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        padding: 1rem;
    }

    .monitor-name {
        color: var(--dark);
        font-weight: 700;
        font-size: 0.92rem;
    }

    .monitor-meta {
        color: var(--text-muted);
        font-size: 0.76rem;
        margin-top: 0.2rem;
    }

    .monitor-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.8rem;
    }

    .monitor-pill {
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.32rem 0.6rem;
    }

    .monitor-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.32rem 0.6rem;
        text-decoration: none;
    }

    .monitor-link:hover {
        color: var(--primary-dark);
        background: rgba(99, 102, 241, 0.15);
    }

    .timeline-board {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .employee-check-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.55rem;
        max-height: 320px;
        overflow: auto;
        padding: 0.45rem;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }

    .employee-check {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--dark);
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        background: #f8fafc;
    }

    .employee-check input { flex-shrink: 0; }

    .member-selector {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        padding: 0.8rem 0.9rem;
        text-align: left;
        color: var(--dark);
        cursor: pointer;
        transition: all 0.2s;
    }

    .member-selector:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.08);
    }

    .member-selector-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.86rem;
        font-weight: 800;
    }

    .member-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        min-height: 26px;
        margin-top: 0.55rem;
    }

    .member-placeholder {
        color: var(--text-muted);
        font-size: 0.78rem;
        font-weight: 600;
    }

    .member-count {
        border-radius: 999px;
        background: rgba(99,102,241,0.1);
        color: var(--primary);
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.28rem 0.55rem;
        white-space: nowrap;
    }

    .project-members {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.65rem;
    }

    .employee-chip,
    .assignment-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        max-width: 100%;
        border: 1px solid rgba(99,102,241,0.16);
        border-radius: 999px;
        background: rgba(99,102,241,0.08);
        color: var(--primary-dark);
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.3rem 0.55rem;
    }

    .employee-chip {
        cursor: default;
        user-select: none;
    }

    .employee-chip.status-approved {
        border-color: rgba(16,185,129,0.2) !important;
        background: rgba(16,185,129,0.08) !important;
        color: #047857 !important;
    }
    .employee-chip.status-submitted {
        border-color: rgba(245,158,11,0.2) !important;
        background: rgba(245,158,11,0.08) !important;
        color: #b45309 !important;
    }
    .employee-chip.status-revision {
        border-color: rgba(239,68,68,0.2) !important;
        background: rgba(239,68,68,0.08) !important;
        color: #b91c1c !important;
    }
    .employee-chip.status-joined {
        border-color: rgba(6,182,212,0.2) !important;
        background: rgba(6,182,212,0.08) !important;
        color: #0369a1 !important;
    }
    .employee-chip.status-belum-mulai {
        border-color: rgba(148,163,184,0.2) !important;
        background: rgba(148,163,184,0.06) !important;
        color: #475569 !important;
    }

    .assignment-chip {
        border-color: rgba(16,185,129,0.18);
        background: rgba(16,185,129,0.1);
        color: #047857;
        margin-top: 0.35rem;
    }

    .day-assignments {
        display: flex;
        flex-direction: column;
        gap: 0.28rem;
        max-height: 62px;
        overflow-y: auto;
        padding-right: 0.15rem;
        margin-bottom: 0.45rem;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .day-assignments .assignment-chip {
        width: 100%;
        justify-content: space-between;
        margin-top: 0;
    }

    .day-assignments:empty {
        display: none;
    }

    .day-assignments::-webkit-scrollbar {
        width: 5px;
    }

    .day-assignments::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .assignment-remove {
        border: 0;
        background: transparent;
        color: inherit;
        padding: 0;
        line-height: 1;
        cursor: pointer;
    }

    .project-row {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .project-row-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .timeline-group {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .timeline-group-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
    }

    .module-progress {
        height: 8px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .module-progress-bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #10b981);
    }

    select[multiple].form-control-admin {
        min-height: 118px;
    }

    .timeline-scroll {
        overflow-x: auto;
        padding: 1rem;
    }

    .timeline-days {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 116px;
        gap: 0.55rem;
        min-height: 142px;
    }

    .timeline-day {
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        padding: 0.7rem;
        min-height: 132px;
        text-align: left;
        cursor: pointer;
        transition: all 0.2s;
    }

    .timeline-day:hover,
    .timeline-day.drag-over {
        border-color: var(--primary);
        box-shadow: 0 4px 16px rgba(99,102,241,0.12);
        transform: translateY(-1px);
    }

    .timeline-day-date {
        font-size: 0.76rem;
        font-weight: 800;
        color: var(--dark);
    }

    .timeline-day-name {
        color: var(--text-muted);
        font-size: 0.7rem;
        margin-bottom: 0.5rem;
    }

    .note-chip {
        display: block;
        width: 100%;
        border: 0;
        border-radius: 8px;
        padding: 0.35rem 0.45rem;
        margin-top: 0.35rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-align: left;
        line-height: 1.25;
    }

    .note-rendah { background: rgba(16,185,129,0.12); color: #047857; }
    .note-sedang { background: rgba(245,158,11,0.15); color: #b45309; }
    .note-tinggi { background: rgba(239,68,68,0.12); color: #dc2626; }
    .note-done { opacity: 0.55; text-decoration: line-through; }

    .priority-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .priority-option {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
    }

    .priority-option input {
        margin-right: 0.35rem;
    }
</style>
@endsection

@section('content')
<div class="admin-wrap">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="admin-logo">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <h1 class="fw-bold mb-0" style="font-size:1.35rem; letter-spacing:-0.3px; color:var(--dark);">
                    {{ $isSuperAdmin ? 'Super Admin Dashboard' : 'Admin Dashboard' }}
                </h1>
                <p class="mb-0 text-muted" style="font-size:0.85rem;">
                    {{ $isSuperAdmin ? 'Akses penuh sistem absensi magang' : 'Kelola peserta magang dan sertifikat' }}
                </p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout Admin
            </button>
        </form>
    </div>

    <!-- Overhauled Statistics Grid -->
    <div class="admin-stats-grid mt-3">
        <!-- Stats Card 1: Projects Summary -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background:rgba(99,102,241,0.1);color:#6366f1;">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-val">{{ $projectCount }}</div>
                <div class="admin-stat-lbl">Total Proyek ({{ $projectAktifCount }} Aktif, {{ $projectSelesaiCount }} Selesai)</div>
            </div>
        </div>

        <!-- Stats Card 2: Modules & Tasks -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background:rgba(139,92,246,0.1);color:#8b5cf6;">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-val">{{ $taskCount }}</div>
                <div class="admin-stat-lbl">{{ $moduleCount }} Modul &middot; {{ $taskCount }} Tugas</div>
            </div>
        </div>

        <!-- Stats Card 3: Alert Tasks -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-val text-warning">{{ $taskReviewCount }}</div>
                <div class="admin-stat-lbl">Butuh Review ({{ $taskTerlambatCount }} Terlambat)</div>
            </div>
        </div>

        <!-- Stats Card 4: Attendance Today -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-val" style="font-size: 1.15rem; font-weight:800;">
                    Hadir: {{ $hadirCount }} | WFH: {{ $wfhCount }} | Belum: {{ $belumAbsenCount }}
                </div>
                <div class="admin-stat-lbl">Kehadiran ({{ $pesertaAktifCount }} Peserta Aktif)</div>
            </div>
        </div>
    </div>

    @if ($isSuperAdmin)
        {{-- Filter & Export Card --}}
        <div class="admin-card p-4 mb-4">
            <form action="{{ route('admin.dashboard') }}" method="GET" id="filterForm">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="tab" value="{{ $activeAdminTab }}">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                    <div class="d-flex flex-wrap gap-3">
                        <div>
                            <div class="filter-label">Bulan</div>
                            <select name="month" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <div class="filter-label">Tahun</div>
                            <select name="year" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                @for ($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.rekap.excel', ['month' => $month, 'year' => $year]) }}" class="btn-export-excel">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.rekap.pdf', ['month' => $month, 'year' => $year]) }}" class="btn-export-pdf">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-4">
        <div class="admin-tabs" id="adminTab">
            @if ($isSuperAdmin)
                <button type="button" class="tab-btn {{ $activeAdminTab === 'rekap' ? 'active' : '' }}" data-tab="rekap">
                    <i class="fa-solid fa-table-list"></i> Rekap Absensi
                </button>
            @endif
            <button type="button" class="tab-btn {{ $activeAdminTab === 'pegawai' ? 'active' : '' }}" data-tab="pegawai">
                <i class="fa-solid fa-users"></i> Kelola Magang
            </button>
            @if ($isSuperAdmin)
                <button type="button" class="tab-btn {{ $activeAdminTab === 'jadwal' ? 'active' : '' }}" data-tab="jadwal">
                    <i class="fa-solid fa-calendar-week"></i> Jadwal Mingguan
                </button>
                <button type="button" class="tab-btn {{ $activeAdminTab === 'timeline' ? 'active' : '' }}" data-tab="timeline">
                    <i class="fa-solid fa-chart-gantt"></i> Timeline Project
                </button>
            @endif
            <button type="button" class="tab-btn {{ $activeAdminTab === 'sertifikat' ? 'active' : '' }}" data-tab="sertifikat">
                <i class="fa-solid fa-certificate"></i> Sertifikat
            </button>
            @if ($isSuperAdmin)
                <button type="button" class="tab-btn {{ $activeAdminTab === 'bidang' ? 'active' : '' }}" data-tab="bidang">
                    <i class="fa-solid fa-layer-group"></i> Kelola Bidang
                </button>
            @endif
        </div>
    </div>

    {{-- TAB: Rekap Absensi --}}
    @if ($isSuperAdmin)
    <div class="tab-panel {{ $activeAdminTab === 'rekap' ? '' : 'd-none' }}" id="panel-rekap">
        <div class="admin-card overflow-hidden">
            {{-- Search & Status Filter --}}
            <div class="p-4 pb-0">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex flex-wrap gap-3 align-items-center">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="tab" value="rekap">
                    <div class="search-wrap flex-grow-1" style="min-width:200px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="search-input" placeholder="Cari nama peserta magang..." value="{{ $search }}">
                    </div>
                    <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="all" {{ ($status === '' || $status === 'all') ? 'selected' : '' }}>Semua status</option>
                        @foreach ($absensiStatuses as $statusOption)
                            <option value="{{ $statusOption->kode }}" {{ $status === $statusOption->kode ? 'selected' : '' }}>
                                {{ $statusOption->nama }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Table --}}
            <div class="mt-3">
                @if ($absensiRecords->isEmpty())
                    <div class="empty-state">
                        <h6>Belum ada data pada periode ini</h6>
                        <p>Ubah bulan/tahun atau filter untuk melihat data lainnya.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Peserta Magang</th>
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Status</th>
                                    <th>Laporan / Project</th>
                                    <th>Lokasi WFH</th>
                                    <th>Foto Kamera</th>
                                    <th>Lampiran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absensiRecords as $rec)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold" style="color:var(--dark);">{{ $rec->user->nama ?? '-' }}</div>
                                            @if ($rec->user && $rec->user->email)
                                                <div class="text-muted" style="font-size:0.78rem;">{{ $rec->user->email }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ \Carbon\Carbon::parse($rec->tanggal)->translatedFormat('d F Y') }}</div>
                                            <div class="text-muted" style="font-size:0.78rem;">
                                                {{ $rec->created_at ? $rec->created_at->timezone(config('app.timezone'))->format('H:i') . ' WIB' : '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-{{ $rec->status }}">{{ strtoupper($rec->status_label ?? $rec->status) }}</span>
                                        </td>
                                        <td style="max-width:260px;">
                                            @if ($rec->laporan)
                                                <span class="text-muted" style="font-size:0.82rem;line-height:1.45;" title="{{ $rec->laporan }}">
                                                    {{ \Illuminate\Support\Str::limit($rec->laporan, 90) }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size:0.82rem;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $gpsMasukLat = $rec->lokasi_masuk_latitude ?? $rec->lokasi_latitude;
                                                $gpsMasukLng = $rec->lokasi_masuk_longitude ?? $rec->lokasi_longitude;
                                                $gpsMasukAkurasi = $rec->lokasi_masuk_akurasi ?? $rec->lokasi_akurasi;
                                                $gpsMasukWaktu = $rec->lokasi_masuk_diambil_pada ?? $rec->lokasi_diambil_pada;
                                                $gpsPulangLat = $rec->lokasi_pulang_latitude;
                                                $gpsPulangLng = $rec->lokasi_pulang_longitude;
                                            @endphp
                                            @if ($gpsMasukLat && $gpsMasukLng)
                                                <a href="https://www.google.com/maps?q={{ $gpsMasukLat }},{{ $gpsMasukLng }}" target="_blank" rel="noopener" class="attachment-link" title="Buka GPS masuk">
                                                    <i class="fa-solid fa-map-location-dot"></i> Masuk
                                                </a>
                                                <div class="text-muted mt-1" style="font-size:0.72rem;">
                                                    {{ $gpsMasukWaktu ? $gpsMasukWaktu->timezone(config('app.timezone'))->format('H:i') . ' WIB' : 'Sebelum 12.00' }}
                                                    · {{ $gpsMasukAkurasi ? round((float) $gpsMasukAkurasi) . ' m' : 'akurasi -' }}
                                                </div>
                                            @else
                                                <div class="text-muted" style="font-size:0.82rem;">Masuk: belum ada GPS</div>
                                            @endif

                                            @if ($gpsPulangLat && $gpsPulangLng)
                                                <a href="https://www.google.com/maps?q={{ $gpsPulangLat }},{{ $gpsPulangLng }}" target="_blank" rel="noopener" class="attachment-link mt-2" title="Buka GPS pulang">
                                                    <i class="fa-solid fa-map-location-dot"></i> Pulang
                                                </a>
                                                <div class="text-muted mt-1" style="font-size:0.72rem;">
                                                    {{ $rec->lokasi_pulang_diambil_pada ? $rec->lokasi_pulang_diambil_pada->timezone(config('app.timezone'))->format('H:i') . ' WIB' : 'Setelah 12.00' }}
                                                    · {{ $rec->lokasi_pulang_akurasi ? round((float) $rec->lokasi_pulang_akurasi) . ' m' : 'akurasi -' }}
                                                </div>
                                            @else
                                                <div class="text-muted mt-2" style="font-size:0.82rem;">Pulang: belum ada GPS</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rec->foto_kamera)
                                                @php $kameraUrl = route('absensi.kamera', $rec); @endphp
                                                <a href="{{ $kameraUrl }}" target="_blank" title="Lihat foto kamera" class="attachment-link">
                                                    <img src="{{ $kameraUrl }}" alt="Foto Kamera" class="attachment-thumb" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                                    <span class="d-none"><i class="fa-solid fa-camera"></i> Lihat</span>
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size:0.82rem;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rec->foto)
                                                @php $lampiranUrl = route('absensi.lampiran', $rec); @endphp
                                                <a href="{{ $lampiranUrl }}" target="_blank" title="Lihat lampiran" class="attachment-link">
                                                    <img src="{{ $lampiranUrl }}" alt="Lampiran" class="attachment-thumb" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                                    <span class="d-none"><i class="fa-regular fa-image"></i> Lihat</span>
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size:0.82rem;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.absensi.destroy', $rec) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="month" value="{{ $month }}">
                                                <input type="hidden" name="year" value="{{ $year }}">
                                                <input type="hidden" name="search" value="{{ $search }}">
                                                <input type="hidden" name="status" value="{{ $status }}">
                                                <button type="button" class="btn-action danger" onclick="confirmAttendanceDelete(event, this.form)" title="Hapus absensi">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- TAB: Kelola Magang --}}
    <div class="tab-panel {{ $activeAdminTab === 'pegawai' ? '' : 'd-none' }}" id="panel-pegawai">
        <div class="admin-card overflow-hidden">
            <div class="p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-users me-1" style="color:var(--primary);"></i> Daftar Peserta Magang
                </h6>
                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa-solid fa-plus"></i> Tambah Peserta
                </button>
            </div>

            <div class="px-4 pb-4">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="pegawai">
                    <div class="col-md-6">
                        <label class="form-label-admin">Cari Peserta Magang</label>
                        <div class="search-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="magang_search" class="search-input" placeholder="Cari nama, email, bidang, atau pembimbing..." value="{{ $magangSearch }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-admin">Kelompok Pembimbing</label>
                        <select name="pembimbing_magang" class="filter-select w-100" onchange="this.form.submit()">
                            <option value="">Semua pembimbing</option>
                            @foreach ($pembimbingOptions as $pembimbingOption)
                                <option value="{{ $pembimbingOption->nama }}" {{ $pembimbingMagang === $pembimbingOption->nama ? 'selected' : '' }}>
                                    {{ $pembimbingOption->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn-add flex-grow-1 justify-content-center">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                        @if ($magangSearch !== '' || $pembimbingMagang !== '')
                            <a href="{{ route('admin.dashboard', ['tab' => 'pegawai']) }}" class="btn-logout" title="Reset filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if ($users->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada peserta magang terdaftar</h6>
                    <p>Tambahkan peserta magang baru untuk memulai pencatatan absensi.</p>
                </div>
            @elseif ($magangUsers->isEmpty())
                <div class="empty-state">
                    <h6>Peserta magang tidak ditemukan</h6>
                    <p>Ubah kata kunci pencarian atau pilih pembimbing lain.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:5%;">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status Akun</th>
                                <th>Pembimbing Magang</th>
                                <th>Bidang Magang</th>
                                <th>Periode Magang</th>
                                <th>Tanggal Ditambahkan</th>
                                <th style="width:12%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowNumber = 1; @endphp
                            @foreach ($magangGroups as $groupName => $groupUsers)
                                <tr>
                                    <td colspan="9" style="background:#f8fafc;color:var(--dark);font-weight:800;">
                                        <i class="fa-solid fa-user-tie me-1" style="color:var(--primary);"></i>
                                        {{ $groupName }} <span class="text-muted fw-semibold">({{ $groupUsers->count() }} peserta)</span>
                                    </td>
                                </tr>
                                @foreach ($groupUsers as $u)
                                    <tr>
                                        <td class="text-muted fw-semibold">{{ $rowNumber++ }}</td>
                                        <td class="fw-semibold" style="color:var(--dark);">{{ $u->nama }}</td>
                                        <td>{{ $u->email ?? '—' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ strtoupper($u->status_akun ?? 'aktif') }}</div>
                                            <span class="monitor-pill" style="{{ ($u->status_akun ?? 'aktif') === 'aktif' ? '' : 'background:rgba(239,68,68,0.12);color:#dc2626;' }}">
                                                {{ strtoupper($u->status_akun ?? 'aktif') }}
                                            </span>
                                        </td>
                                        <td>{{ $u->pembimbing_magang ?? '—' }}</td>
                                        <td>{{ $u->bidang_magang ?? '—' }}</td>
                                        <td>
                                            @if ($u->tanggal_mulai_magang || $u->tanggal_selesai_magang)
                                                <div class="fw-semibold" style="font-size:0.82rem;">
                                                    {{ $u->tanggal_mulai_magang ? $u->tanggal_mulai_magang->translatedFormat('d F Y') : '-' }}
                                                </div>
                                                <div class="text-muted" style="font-size:0.78rem;">
                                                    s/d {{ $u->tanggal_selesai_magang ? $u->tanggal_selesai_magang->translatedFormat('d F Y') : '-' }}
                                                </div>
                                                @if ($u->tanggal_selesai_magang && $u->tanggal_selesai_magang->lte(now(config('app.timezone'))))
                                                    <a href="{{ route('sertifikat.show', \Illuminate\Support\Str::slug($u->nama)) }}" target="_blank" class="attachment-link mt-1">
                                                        <i class="fa-solid fa-certificate"></i> Sertifikat
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $u->created_at ? $u->created_at->translatedFormat('d F Y') : '—' }}</td>
                                        <td>
                                            <button type="button" class="btn-action me-1" onclick="editUser({{ $u->id }}, {{ json_encode($u->nama) }}, {{ json_encode($u->email) }}, {{ json_encode($u->pembimbing_magang_id) }}, {{ json_encode($u->bidang_id) }}, {{ json_encode(optional($u->tanggal_mulai_magang)->format('Y-m-d')) }}, {{ json_encode(optional($u->tanggal_selesai_magang)->format('Y-m-d')) }}, {{ json_encode($u->status_akun ?? 'aktif') }})" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <a href="#" class="btn-action danger" onclick="confirmDel(event, '{{ route('admin.user.destroy', $u->id) }}')" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- TAB: Sertifikat --}}
    <div class="tab-panel {{ $activeAdminTab === 'sertifikat' ? '' : 'd-none' }}" id="panel-sertifikat">
        <div class="admin-card overflow-hidden">
            <div class="p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="fw-bold mb-1" style="color:var(--dark);">
                        <i class="fa-solid fa-certificate me-1" style="color:var(--primary);"></i> Sertifikat Magang
                    </h6>
                    <p class="text-muted mb-0" style="font-size:0.82rem;">Admin bisa upload file sertifikat PDF/gambar untuk tiap peserta magang.</p>
                </div>
            </div>

            @if ($sertifikatUsers->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada peserta magang</h6>
                    <p>Tambahkan peserta magang terlebih dahulu dari tab Kelola Magang.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:5%;">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Bidang</th>
                                <th>Periode</th>
                                <th>Status Sertifikat</th>
                                <th>File Upload</th>
                                <th style="width:22%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sertifikatUsers as $index => $u)
                                @php
                                    $eligibleCertificate = $u->tanggal_selesai_magang && $u->tanggal_selesai_magang->lte(now(config('app.timezone')));
                                @endphp
                                <tr>
                                    <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td class="fw-semibold" style="color:var(--dark);">{{ $u->nama }}</td>
                                    <td>{{ $u->email ?? '-' }}</td>
                                    <td>{{ $u->bidang_magang ?? '-' }}</td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:0.82rem;">
                                            {{ $u->tanggal_mulai_magang ? $u->tanggal_mulai_magang->translatedFormat('d F Y') : '-' }}
                                        </div>
                                        <div class="text-muted" style="font-size:0.78rem;">
                                            s/d {{ $u->tanggal_selesai_magang ? $u->tanggal_selesai_magang->translatedFormat('d F Y') : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($u->sertifikat_file_path)
                                            <span class="badge-status badge-hadir">SUDAH UPLOAD</span>
                                        @elseif ($eligibleCertificate)
                                            <span class="badge-status badge-hadir">SIAP CETAK</span>
                                        @elseif ($u->tanggal_selesai_magang)
                                            <span class="monitor-pill">Menunggu selesai magang</span>
                                        @else
                                            <span class="monitor-pill">Tanggal selesai belum diisi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($u->sertifikat_file_path)
                                            <a href="{{ route('admin.sertifikat.view', $u) }}" target="_blank" class="attachment-link" title="{{ $u->sertifikat_file_name }}">
                                                <i class="fa-solid fa-file-lines"></i>
                                                {{ \Illuminate\Support\Str::limit($u->sertifikat_file_name, 28) }}
                                            </a>
                                            <div class="text-muted mt-1" style="font-size:0.72rem;">
                                                {{ $u->sertifikat_diunggah_pada ? $u->sertifikat_diunggah_pada->timezone(config('app.timezone'))->format('d M Y H:i') . ' WIB' : '-' }}
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size:0.82rem;">Belum ada file</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.sertifikat.upload', $u) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
                                            @csrf
                                            <input type="file" name="sertifikat_file" class="form-control form-control-admin" accept=".pdf,image/jpeg,image/png,image/webp" required style="max-width:210px;">
                                            <button type="submit" class="btn-action" title="{{ $u->sertifikat_file_path ? 'Ganti file sertifikat' : 'Upload sertifikat' }}">
                                                <i class="fa-solid fa-upload"></i>
                                            </button>
                                        </form>
                                        <div class="d-flex gap-2 mt-2">
                                        @if ($eligibleCertificate)
                                            <a href="{{ route('sertifikat.show', \Illuminate\Support\Str::slug($u->nama)) }}" target="_blank" class="btn-action" title="Buka sertifikat">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn-action" disabled title="Sertifikat belum aktif">
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        @endif
                                        @if ($u->sertifikat_file_path)
                                            <form action="{{ route('admin.sertifikat.destroy', $u) }}" method="POST">
                                                @csrf
                                                <button type="button" class="btn-action danger" title="Hapus file upload" onclick="confirmCertificateDelete(event, this.form)">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($isSuperAdmin)
    {{-- TAB: Jadwal Mingguan --}}
    <div class="tab-panel {{ $activeAdminTab === 'jadwal' ? '' : 'd-none' }}" id="panel-jadwal">
        <div class="admin-card overflow-hidden">
            <div class="p-4 border-bottom" style="border-color:var(--border)!important;">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--dark);">
                            <i class="fa-solid fa-calendar-week me-1" style="color:var(--primary);"></i> Atur Jadwal WFO / WFH
                        </h6>
                        <p class="text-muted mb-0" style="font-size:0.82rem;">
                            Atur jadwal Sen–Kam manual. Jumat otomatis WFH untuk semua peserta magang.
                        </p>
                    </div>
                    <button type="submit" form="scheduleForm" class="btn-add">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal
                    </button>
                </div>

                @if ($users->isNotEmpty())
                    <div class="p-3 rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-3" style="background:#f8fafc; border:1px solid var(--border);">
                        <div>
                            <div class="fw-semibold" style="font-size:0.82rem; color:var(--dark);">Acak Jadwal Otomatis</div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                Peserta magang dibagi acak: setengah Sen/Rab WFH, setengah Sen/Rab WFO. Jumat tetap WFH.
                            </div>
                        </div>
                        <form action="{{ route('admin.jadwal.random') }}" method="POST" onsubmit="return confirmRandomSchedule(event)">
                            @csrf
                            <button type="submit" class="btn-dice">
                                <i class="fa-solid fa-dice"></i> Acak Jadwal
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if ($users->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada peserta magang</h6>
                    <p>Tambahkan peserta magang terlebih dahulu di tab Kelola Magang.</p>
                </div>
            @else
                <form action="{{ route('admin.jadwal.update') }}" method="POST" id="scheduleForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Peserta Magang</th>
                                    <th class="text-center">Senin</th>
                                    <th class="text-center">Selasa</th>
                                    <th class="text-center">Rabu</th>
                                    <th class="text-center">Kamis</th>
                                    <th class="text-center">Jumat <span class="text-muted fw-normal" style="font-size:0.65rem;">(WFH)</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $weekdays = ['senin', 'selasa', 'rabu', 'kamis']; @endphp
                                @foreach ($users as $u)
                                    @php $jadwal = $u->jadwalMingguan; @endphp
                                    <tr>
                                        <td class="fw-semibold" style="color:var(--dark);">{{ $u->nama }}</td>
                                        @foreach ($weekdays as $day)
                                            <td class="text-center">
                                                <select name="schedules[{{ $u->id }}][{{ $day }}]" class="schedule-select">
                                                    @foreach ($jadwalStatuses as $jadwalStatus)
                                                        <option value="{{ $jadwalStatus->kode }}" {{ ($jadwal?->$day ?? 'wfo') === $jadwalStatus->kode ? 'selected' : '' }}>
                                                            {{ $jadwalStatus->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span class="jumat-fixed">{{ optional($jadwalStatuses->firstWhere('kode', 'wfh'))->nama ?? 'WFH' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- TAB: Timeline Project --}}
    <div class="tab-panel {{ $activeAdminTab === 'timeline' ? '' : 'd-none' }}" id="panel-timeline">
        <div class="admin-card p-4 mb-4">
            <form action="{{ route('admin.project.store') }}" method="POST" id="createProjectForm">
                @csrf
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--dark);">
                            <i class="fa-solid fa-folder-tree me-1" style="color:var(--primary);"></i> Buat Project
                        </h6>
                        <p class="text-muted mb-0" style="font-size:0.82rem;">Setelah project dibuat, tambahkan timeline dan modul pekerjaan di bawahnya.</p>
                    </div>
                    <button type="submit" class="btn-add">
                        <i class="fa-solid fa-plus"></i> Simpan Project
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Peserta Magang Project</label>
                        <button type="button" class="member-selector" data-bs-toggle="modal" data-bs-target="#createProjectMembersModal">
                            <span class="member-selector-top">
                                <span><i class="fa-solid fa-users me-1" style="color:var(--primary);"></i> Pilih anggota project</span>
                                <span class="member-count" id="create_member_count">0 dipilih</span>
                            </span>
                            <span class="member-preview" id="create_member_preview">
                                <span class="member-placeholder">Klik untuk membuka daftar anggota</span>
                            </span>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Nama Project <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-admin w-100" placeholder="Contoh: Website Absensi" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-admin">Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-admin">Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label-admin">Kebutuhan Project</label>
                        <textarea name="kebutuhan" rows="2" class="form-control form-control-admin w-100" placeholder="Tuliskan kebutuhan, target, atau scope project..."></textarea>
                    </div>
                </div>
            </form>
        </div>

        @php
            $pendingTasks = \App\Models\ProjectTask::with(['user', 'project', 'module'])
                ->where('status', 'review')
                ->latest('updated_at')
                ->get();
        @endphp
        <div class="admin-card p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-inbox me-1" style="color:var(--primary);"></i> Review Laporan Tugas (Task)
                </h6>
                <span class="monitor-pill">{{ $pendingTasks->count() }} Menunggu Review</span>
            </div>
            @if ($pendingTasks->isEmpty())
                <div class="empty-state py-4 text-center text-muted">
                    <i class="fa-solid fa-circle-check fs-2 text-success mb-2"></i>
                    <h6 class="fw-bold">Semua tugas bersih!</h6>
                    <p class="small mb-0">Tidak ada tugas yang menunggu review saat ini.</p>
                </div>
            @else
                <div class="monitor-grid">
                    @foreach ($pendingTasks as $task)
                        <div class="monitor-item" style="border-left: 5px solid #f59e0b !important;">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <div class="monitor-name">{{ $task->user->nama ?? 'Tanpa User' }}</div>
                                    <div class="monitor-meta">{{ $task->project->nama ?? '-' }} &middot; {{ $task->module?->nama ?? '-' }}</div>
                                </div>
                                <span class="monitor-pill" style="background:rgba(245,158,11,0.15);color:#b45309;">REVIEW</span>
                            </div>
                            <div class="fw-bold text-dark mt-2" style="font-size:0.86rem;">{{ $task->judul }}</div>
                            @if ($task->laporan_kerja)
                                <div class="alert bg-light border p-2 mt-2 mb-2 small text-dark" style="font-size:0.8rem;">
                                    <strong>Laporan:</strong> {{ $task->laporan_kerja }}
                                </div>
                            @endif
                            @if ($task->file_lampiran)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $task->file_lampiran) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2.5" style="font-size:0.72rem;">
                                        <i class="fa-solid fa-paperclip me-1"></i> Buka Lampiran
                                    </a>
                                </div>
                            @endif
                            <div class="monitor-actions mt-3">
                                <form action="{{ route('admin.project.task.approve', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success py-1.5 px-3 fw-bold rounded text-white" title="Setujui Tugas">
                                        <i class="fa-solid fa-check me-1"></i> Setujui
                                    </button>
                                </form>
                                <form action="{{ route('admin.project.task.revision', $task) }}" method="POST" class="d-flex gap-2 flex-grow-1">
                                    @csrf
                                    <input type="text" name="review_note" class="form-control form-control-admin py-1" style="font-size:0.8rem;" placeholder="Catatan revisi..." required>
                                    <button type="submit" class="btn btn-sm btn-danger py-1 px-2.5 fw-bold rounded text-white" title="Minta Revisi">
                                        <i class="fa-solid fa-reply me-1"></i> Revisi
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="timeline-board">
            @forelse ($projects as $project)
                @php
                    $projectMembers = $project->members->isNotEmpty() ? $project->members : collect([$project->user])->filter();
                    $projectMemberIds = $projectMembers->pluck('id')->values();
                    $projectModules = $project->modules->sortBy('tanggal_mulai')->values();
                    $moduleCount = $projectModules->count();
                    $currentWeightSum = $projectModules->sum('bobot');
                    $projectDays = max(1, $project->tanggal_mulai->diffInDays($project->tanggal_selesai));
                @endphp
                <div class="project-row" id="project-row-{{ $project->id }}">
                    <div class="project-row-header">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h6 class="fw-bold mb-0" style="color:var(--dark);">{{ $project->nama }}</h6>
                                <span class="monitor-pill">{{ $moduleCount }} modul</span>
                                <span class="monitor-pill">{{ number_format($project->progress_percentage, 1) }}% progress</span>
                            </div>
                            <div class="monitor-meta">
                                {{ $projectMembers->pluck('nama')->join(', ') ?: '-' }} &middot;
                                {{ $project->tanggal_mulai->translatedFormat('d M Y') }} - {{ $project->tanggal_selesai->translatedFormat('d M Y') }}
                            </div>
                            @if ($project->kebutuhan)
                                <div class="monitor-meta">{{ \Illuminate\Support\Str::limit($project->kebutuhan, 140) }}</div>
                            @endif
                            <div class="project-members">
                                @foreach ($projectMembers as $member)
                                    <span class="employee-chip" title="Anggota project">
                                        <i class="fa-solid fa-user"></i> {{ $member->nama }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn-action" title="Edit project" onclick="editProject({{ $project->id }}, {{ $projectMemberIds->toJson() }}, {{ json_encode($project->nama) }}, {{ json_encode($project->kebutuhan) }}, {{ json_encode($project->tanggal_mulai->format('Y-m-d')) }}, {{ json_encode($project->tanggal_selesai->format('Y-m-d')) }}, {{ json_encode($project->status) }})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.project.destroy', $project) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn-action danger" onclick="confirmProjectDelete(event, this.form)" title="Hapus project">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-3 border-top" style="border-color:var(--border)!important;">
                        <!-- Alert: Weight validation warning -->
                        @if ($currentWeightSum != 100)
                            <div class="alert alert-warning py-2.5 px-3 rounded-3 small mb-3">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <strong>Perhatian:</strong> Total bobot modul untuk project ini adalah <strong>{{ $currentWeightSum }}%</strong>. Sesuaikan bobot modul agar totalnya tepat 100% untuk pelacakan progress yang akurat.
                            </div>
                        @endif

                        <!-- Planning vs Actual Comparison Visualisation -->
                        <div class="progress-comparison mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small"><i class="fa-solid fa-chart-line me-1 text-primary"></i> Perbandingan Progress Jadwal vs Aktual</span>
                            </div>
                            <div class="comparison-bar-row">
                                <span class="comparison-bar-label">Rencana (Plan)</span>
                                <div class="comparison-bar-bg">
                                    <div class="comparison-bar-fill" style="width: {{ $project->planned_progress_percentage }}%; background: #64748b;"></div>
                                </div>
                                <span class="fw-bold text-muted small">{{ $project->planned_progress_percentage }}%</span>
                            </div>
                            <div class="comparison-bar-row">
                                <span class="comparison-bar-label">Aktual (Real)</span>
                                <div class="comparison-bar-bg">
                                    <div class="comparison-bar-fill" style="width: {{ $project->progress_percentage }}%; background: #6c5ce7;"></div>
                                </div>
                                <span class="fw-bold text-primary small">{{ $project->progress_percentage }}%</span>
                            </div>
                            @if ($project->progress_percentage < $project->planned_progress_percentage)
                                <div class="text-danger mt-1 small" style="font-size:0.75rem;"><i class="fa-solid fa-triangle-exclamation"></i> Progress aktual terlambat {{ round($project->planned_progress_percentage - $project->progress_percentage, 1) }}% dari jadwal rencana.</div>
                            @else
                                <div class="text-success mt-1 small" style="font-size:0.75rem;"><i class="fa-solid fa-circle-check"></i> Pekerjaan sesuai jadwal rencana atau berjalan lebih cepat!</div>
                            @endif
                        </div>

                        <!-- Toggle View Mode Switcher -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="d-flex gap-2 bg-light p-1.5 rounded-3 d-inline-flex">
                                <button type="button" class="btn btn-sm btn-primary px-3 rounded-2" id="btn-timeline-{{ $project->id }}" onclick="switchProjectView({{ $project->id }}, 'timeline')">
                                    <i class="fa-solid fa-chart-gantt me-1"></i> Timeline View (Modul)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-2" id="btn-board-{{ $project->id }}" onclick="switchProjectView({{ $project->id }}, 'board')">
                                    <i class="fa-solid fa-chalkboard-user me-1"></i> Kanban Board (Tugas)
                                </button>
                            </div>
                        </div>

                        <!-- FORM: Tambah Modul -->
                        <form action="{{ route('admin.project.module.store') }}" method="POST" class="mb-4 p-3 border rounded-3 bg-light" style="border-color: var(--border) !important;">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <strong style="font-size:0.86rem;color:var(--dark);">
                                    <i class="fa-solid fa-layer-group me-1" style="color:var(--primary);"></i> Tambah Modul Baru
                                </strong>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label-admin">Nama Modul <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control form-control-admin w-100 bg-white" placeholder="Contoh: Authentication" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-admin">Bobot (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="bobot" value="10" min="0" max="100" step="0.01" class="form-control form-control-admin w-100 bg-white" required>
                                </div>
                                <div class="col-md-2.5">
                                    <label class="form-label-admin">Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mulai" class="form-control form-control-admin w-100 bg-white" required>
                                </div>
                                <div class="col-md-2.5">
                                    <label class="form-label-admin">Selesai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_selesai" class="form-control form-control-admin w-100 bg-white" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-admin">Deskripsi Pekerjaan</label>
                                    <textarea name="deskripsi" rows="2" class="form-control form-control-admin w-100 bg-white" placeholder="Deskripsi pekerjaan modul..."></textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-dark text-white py-1.8 px-3 rounded-2 fw-bold" style="font-size:0.82rem; background: #000; border-color: #000;">
                                        <i class="fa-solid fa-plus me-1"></i> Simpan Modul
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- VIEW 1: TIMELINE VIEW (GANTT CHART) -->
                        <div id="project-view-timeline-{{ $project->id }}">
                            <div class="gantt-chart">
                                @forelse ($projectModules as $module)
                                    @php
                                        $moduleStart = $module->tanggal_mulai ? $module->tanggal_mulai : $project->tanggal_mulai;
                                        $moduleEnd = $module->tanggal_selesai ? $module->tanggal_selesai : $project->tanggal_selesai;
                                        $leftDays = max(0, $project->tanggal_mulai->diffInDays($moduleStart));
                                        $moduleDays = max(1, $moduleStart->diffInDays($moduleEnd));
                                        $leftPercent = min(95, ($leftDays / $projectDays) * 100);
                                        $widthPercent = min(100 - $leftPercent, ($moduleDays / $projectDays) * 100);
                                    @endphp
                                    <div class="gantt-row">
                                        <div class="gantt-label">
                                            <div class="fw-bold text-dark text-truncate" style="max-width:160px;">{{ $module->nama }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">Bobot: {{ $module->bobot }}% &middot; Prog: {{ $module->progress }}%</div>
                                        </div>
                                        <div class="gantt-timeline-container flex-grow-1">
                                             <div class="gantt-bar" style="left: 0; width: 100%; background: #e2e8f0; border: 1px solid #cbd5e1; box-shadow: none; display: flex; align-items: center; justify-content: flex-start; padding-left: 0; position: absolute; overflow: hidden; height: 100%;">
                                                 <!-- Progress Fill -->
                                                 <div style="position: absolute; top:0; left:0; bottom:0; width: {{ $module->progress }}%; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); transition: width 0.3s ease;"></div>
                                                 <span class="px-2" style="position: relative; z-index: 2; font-size:0.68rem; color: {{ $module->progress > 30 ? '#fff' : '#1e293b' }}; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight: 700;">
                                                     {{ $module->nama }} ({{ $module->tanggal_mulai?->format('d M') }} - {{ $module->tanggal_selesai?->format('d M') }})
                                                 </span>
                                             </div>
                                         </div>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-muted small">Belum ada modul untuk dibuat visualisasi timeline.</div>
                                @endforelse
                            </div>

                            <!-- List Modul Detil -->
                            <div class="monitor-grid mt-3">
                                @foreach ($projectModules as $module)
                                    <div class="monitor-item bg-white">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong class="text-dark">{{ $module->nama }}</strong>
                                                <div class="text-muted" style="font-size:0.75rem;">
                                                    Bobot: {{ $module->bobot }}% &middot; Status: {{ $module->status_label }}
                                                </div>
                                                <div class="text-muted mt-0.5" style="font-size:0.72rem;">
                                                    <i class="fa-regular fa-calendar me-1"></i>
                                                    {{ $module->tanggal_mulai ? $module->tanggal_mulai->format('d M Y') : '' }} - {{ $module->tanggal_selesai ? $module->tanggal_selesai->format('d M Y') : '' }}
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-primary border px-2 py-1 rounded-pill" style="font-size:0.7rem;">{{ $module->progress }}%</span>
                                        </div>
                                        @if ($module->deskripsi)
                                            <p class="text-muted small mt-2 mb-2" style="line-height:1.45;">{{ $module->deskripsi }}</p>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="border-color:var(--border)!important;">
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 px-2 py-1" style="font-size:0.75rem;" 
                                                data-id="{{ $module->id }}"
                                                data-nama="{{ e($module->nama) }}"
                                                data-bobot="{{ $module->bobot }}"
                                                data-tanggal-mulai="{{ $module->tanggal_mulai ? $module->tanggal_mulai->format('Y-m-d') : '' }}"
                                                data-tanggal-selesai="{{ $module->tanggal_selesai ? $module->tanggal_selesai->format('Y-m-d') : '' }}"
                                                data-deskripsi="{{ e($module->deskripsi ?? '') }}"
                                                onclick="editProjectModule(this)">
                                                <i class="fa-solid fa-pen"></i> Edit Modul
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success border-0 px-2 py-1" style="font-size:0.75rem;"
                                                onclick="openAddTaskModalWithModule({{ $project->id }}, {{ $projectMembers->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama])->values()->toJson() }}, {{ $projectModules->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama])->values()->toJson() }}, {{ $module->id }})">
                                                <i class="fa-solid fa-plus-circle"></i> Tambah Task
                                            </button>
                                            <form action="{{ route('admin.project.module.destroy', $module) }}" method="POST">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 px-2 py-1" style="font-size:0.75rem;" onclick="confirmModuleDelete(event, this.form)">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- VIEW 2: BOARD VIEW (KANBAN BOARD) -->
                        <div id="project-view-board-{{ $project->id }}" class="d-none">
                            @php
                                $projectTasks = $project->tasks;
                            @endphp
                            
                            <!-- Available Tasks (Belum Dikerjakan) Sidebar/Box -->
                            <div class="p-3 border rounded-3 bg-white mb-3" style="border-left: 4px solid #64748b !important; border-color: var(--border) !important;">
                                <div class="fw-bold mb-2 text-dark" style="font-size:0.86rem;"><i class="fa-solid fa-folder-open text-muted me-1"></i> Tugas Tersedia (Belum Diambil / Unassigned)</div>
                                <div class="row g-2">
                                    @forelse ($projectTasks->where('status', 'belum_dikerjakan') as $task)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="p-2.5 border rounded bg-light position-relative">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="badge bg-secondary text-white" style="font-size:0.65rem; font-weight:700;">Modul: {{ $task->module?->nama ?? '-' }}</span>
                                                    <form action="{{ route('admin.project.task.destroy', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.75rem; background:none; line-height:1;" title="Hapus Task" onclick="return confirm('Hapus task ini?')">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="fw-bold text-dark" style="font-size:0.8rem;">{{ $task->judul }}</div>
                                                @if ($task->deskripsi)
                                                    <div class="text-muted mt-1" style="font-size:0.72rem; line-height:1.4;">{{ Str::limit($task->deskripsi, 80) }}</div>
                                                @endif
                                                <form action="{{ route('admin.project.task.assign_pic', $task) }}" method="POST" class="mt-2 d-flex gap-1 align-items-center">
                                                    @csrf
                                                    <select name="user_id" class="form-select form-control-admin py-0.5 px-1.5" style="font-size:0.72rem; height:auto;" required>
                                                        <option value="" disabled selected>Pilih PIC...</option>
                                                        @foreach ($projectMembers as $member)
                                                            <option value="{{ $member->id }}">{{ $member->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-primary py-0.5 px-2 text-white fw-bold" style="font-size:0.72rem;">Set</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted small px-2">Tidak ada tugas tersedia. Semua tugas aktif sedang dikerjakan.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="kanban-board mt-2">
                                <!-- Kolom: Sedang Dikerjakan -->
                                <div class="kanban-col">
                                    <div class="kanban-col-header">
                                        <span class="fw-bold text-dark"><i class="fa-solid fa-spinner fa-spin me-1 text-primary"></i> Sedang Dikerjakan</span>
                                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5" style="font-size:0.7rem;">{{ $projectTasks->whereIn('status', ['sedang_dikerjakan', 'revision'])->count() }}</span>
                                    </div>
                                    <div class="kanban-list" style="max-height: 400px; overflow-y: auto;">
                                        @forelse ($projectTasks->whereIn('status', ['sedang_dikerjakan', 'revision']) as $task)
                                            <div class="kanban-card position-relative" style="border-left: 4px solid {{ $task->status === 'revision' ? '#ef4444' : '#3b82f6' }} !important;">
                                                <div class="d-flex justify-content-between align-items-start mb-1.5">
                                                    <span class="badge bg-light text-primary border" style="font-size:0.65rem;">{{ $task->module?->nama ?? '-' }}</span>
                                                    <div class="d-flex gap-1.5 align-items-center">
                                                        @if ($task->status === 'revision')
                                                            <span class="badge bg-danger text-white py-0.5 px-1.5 rounded" style="font-size:0.6rem; font-weight:800;">REVISI</span>
                                                        @endif
                                                        <form action="{{ route('admin.project.task.destroy', $task) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.75rem; background:none; line-height:1;" title="Hapus Task" onclick="return confirm('Hapus task ini?')">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="kanban-card-title">{{ $task->judul }}</div>
                                                <div class="text-muted mt-1 d-flex align-items-center gap-1" style="font-size:0.74rem;">
                                                    PIC: <strong class="text-dark">{{ $task->user->nama ?? '-' }}</strong>
                                                    <form action="{{ route('admin.project.task.unassign_pic', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.72rem; background:none; line-height:1;" title="Lepas PIC / Jadikan Unassigned">
                                                            <i class="fa-solid fa-user-minus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                @if ($task->catatan_revisi)
                                                    <div class="alert alert-danger p-1.5 rounded-2 mt-2 mb-0" style="font-size:0.72rem; border:0;">
                                                        <strong>Catatan:</strong> {{ $task->catatan_revisi }}
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted small">Tidak ada tugas sedang dikerjakan.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Kolom: Selesai -->
                                <div class="kanban-col">
                                    <div class="kanban-col-header">
                                        <span class="fw-bold text-dark"><i class="fa-solid fa-circle-check me-1 text-success"></i> Selesai Disetujui</span>
                                        <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5" style="font-size:0.7rem;">{{ $projectTasks->where('status', 'selesai')->count() }}</span>
                                    </div>
                                    <div class="kanban-list" style="max-height: 400px; overflow-y: auto;">
                                        @forelse ($projectTasks->where('status', 'selesai') as $task)
                                            <div class="kanban-card position-relative" style="border-left: 4px solid #10b981 !important; background: #fafdfb;">
                                                <div class="d-flex justify-content-between align-items-start mb-1.5">
                                                    <span class="badge bg-light text-success border" style="font-size:0.65rem;">{{ $task->module?->nama ?? '-' }}</span>
                                                    <div class="d-flex gap-1.5 align-items-center">
                                                        <span class="badge bg-success-subtle text-success py-0.5 px-1.5 rounded" style="font-size:0.6rem; font-weight:800;">APPROVED</span>
                                                        <form action="{{ route('admin.project.task.destroy', $task) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.75rem; background:none; line-height:1;" title="Hapus Task" onclick="return confirm('Hapus task ini?')">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="kanban-card-title text-decoration-line-through text-muted">{{ $task->judul }}</div>
                                                <div class="text-muted mt-1" style="font-size:0.74rem;">PIC: <strong class="text-dark">{{ $task->user->nama ?? '-' }}</strong></div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted small">Belum ada tugas selesai.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="admin-card empty-state text-center py-5">
                    <h6>Belum ada timeline project</h6>
                    <p class="text-muted small">Buat project pertama dari form di atas.</p>
                </div>
            @endforelse
        </div>

        <!-- SECTION: Log Aktivitas Magang (Activity Log) -->
        <div class="admin-card p-4 mt-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-clock-rotate-left me-1" style="color:var(--primary);"></i> Log Aktivitas Magang (Activity Log)
                </h6>
                <span class="monitor-pill bg-light text-primary border">{{ $activityLogs->count() }} Aktivitas</span>
            </div>
            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activityLogs as $log)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->created_at->timezone(config('app.timezone'))->format('d M Y') }}</div>
                                    <div class="text-muted small">{{ $log->created_at->timezone(config('app.timezone'))->format('H:i') }} WIB</div>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $log->user->nama ?? 'Sistem' }}</strong>
                                    @if ($log->user && $log->user->email)
                                        <div class="text-muted small">{{ $log->user->email }}</div>
                                    @endif
                                </td>
                                <td>{{ $log->aktivitas }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">Belum ada log aktivitas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- TAB: Kelola Bidang --}}
    <div class="tab-panel {{ $activeAdminTab === 'bidang' ? '' : 'd-none' }}" id="panel-bidang">
        <div class="admin-card overflow-hidden">
            <div class="p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-layer-group me-1" style="color:var(--primary);"></i> Daftar Bidang Magang
                </h6>
                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addBidangModal">
                    <i class="fa-solid fa-plus"></i> Tambah Bidang
                </button>
            </div>

            @if ($bidangs->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada bidang magang terdaftar</h6>
                    <p>Tambahkan bidang magang baru untuk dapat memilihnya saat mendaftarkan anak magang.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:10%;">No</th>
                                <th>Nama Bidang</th>
                                <th style="width:20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $bidangRowNumber = 1; @endphp
                            @foreach ($bidangs as $b)
                                <tr>
                                    <td class="text-muted fw-semibold">{{ $bidangRowNumber++ }}</td>
                                    <td class="fw-semibold" style="color:var(--dark);">{{ $b->nama }}</td>
                                    <td>
                                        <button type="button" class="btn-action me-1" onclick="editBidang({{ $b->id }}, {{ json_encode($b->nama) }})" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="#" class="btn-action danger" onclick="confirmDelBidang(event, '{{ route('admin.bidang.destroy', $b->id) }}')" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="admin-card overflow-hidden mt-4">
            <div class="p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-user-tie me-1" style="color:var(--primary);"></i> Daftar Pembimbing Magang
                </h6>
                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addPembimbingModal">
                    <i class="fa-solid fa-plus"></i> Tambah Pembimbing
                </button>
            </div>

            @if ($pembimbingMagangs->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada pembimbing magang terdaftar</h6>
                    <p>Tambahkan pembimbing agar peserta dapat memilihnya saat daftar akun.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:10%;">No</th>
                                <th>Nama Pembimbing</th>
                                <th>Bidang Magang</th>
                                <th style="width:20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pembimbingMagangs as $index => $pembimbing)
                                <tr>
                                    <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td class="fw-semibold" style="color:var(--dark);">{{ $pembimbing->nama }}</td>
                                    <td>{{ $pembimbing->bidang->nama ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn-action me-1" onclick="editPembimbing({{ $pembimbing->id }}, {{ json_encode($pembimbing->nama) }}, {{ json_encode($pembimbing->bidang_id) }})" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="#" class="btn-action danger" onclick="confirmDelPembimbing(event, '{{ route('admin.pembimbing.destroy', $pembimbing->id) }}')" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal: Tambah Peserta Magang --}}
<div class="modal fade modal-clean" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-plus me-2" style="color:var(--primary);"></i>Tambah Peserta Magang</h5>
            <form action="{{ route('admin.user.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control form-control-admin w-100" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-admin w-100" placeholder="nama@email.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control form-control-admin w-100" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Status Akun</label>
                    <select name="status_akun" class="form-select form-control-admin w-100">
                        <option value="aktif" selected>Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                        <select name="bidang_id" id="add_bidang_id" class="form-select form-control-admin w-100 js-bidang-select" data-pembimbing-target="add_pembimbing_magang_id" required>
                            <option value="" disabled selected>Pilih bidang...</option>
                            @foreach ($bidangs as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Pembimbing Magang <span class="text-danger">*</span></label>
                        <select name="pembimbing_magang_id" id="add_pembimbing_magang_id" class="form-select form-control-admin w-100 js-pembimbing-select" required disabled>
                            <option value="" disabled selected>Pilih bidang terlebih dahulu...</option>
                            @foreach ($pembimbingMagangs as $pembimbing)
                                <option value="{{ $pembimbing->id }}" data-bidang-id="{{ $pembimbing->bidang_id }}">{{ $pembimbing->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Mulai Magang</label>
                        <input type="date" name="tanggal_mulai_magang" class="form-control form-control-admin w-100">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Selesai Magang</label>
                        <input type="date" name="tanggal_selesai_magang" class="form-control form-control-admin w-100">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Peserta Magang --}}
<div class="modal fade modal-clean" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-pen me-2" style="color:var(--primary);"></i>Edit Peserta Magang</h5>
            <form action="" method="POST" id="editForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="e_nama" class="form-control form-control-admin w-100" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="e_email" class="form-control form-control-admin w-100" placeholder="nama@email.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Password Baru</label>
                    <input type="password" name="password" id="e_password" class="form-control form-control-admin w-100" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Status Akun</label>
                    <select name="status_akun" id="e_status_akun" class="form-select form-control-admin w-100">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                        <select name="bidang_id" id="e_bidang_id" class="form-select form-control-admin w-100 js-bidang-select" data-pembimbing-target="e_pembimbing_magang_id" required>
                            <option value="" disabled>Pilih bidang...</option>
                            @foreach ($bidangs as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Pembimbing Magang <span class="text-danger">*</span></label>
                        <select name="pembimbing_magang_id" id="e_pembimbing_magang_id" class="form-select form-control-admin w-100 js-pembimbing-select" required disabled>
                            <option value="" disabled>Pilih bidang terlebih dahulu...</option>
                            @foreach ($pembimbingMagangs as $pembimbing)
                                <option value="{{ $pembimbing->id }}" data-bidang-id="{{ $pembimbing->bidang_id }}">{{ $pembimbing->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Mulai Magang</label>
                        <input type="date" name="tanggal_mulai_magang" id="e_tanggal_mulai_magang" class="form-control form-control-admin w-100">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Selesai Magang</label>
                        <input type="date" name="tanggal_selesai_magang" id="e_tanggal_selesai_magang" class="form-control form-control-admin w-100">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Tambah Note Timeline --}}
<div class="modal fade modal-clean" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-note-sticky me-2" style="color:var(--primary);"></i>Tambah Note Timeline</h5>
            <p class="text-muted mb-3" style="font-size:0.82rem;" id="noteModalMeta">Pilih tanggal pada timeline.</p>
            <form action="{{ route('admin.project.note.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" id="note_project_id">
                <input type="hidden" name="user_id" id="note_user_id">
                <input type="hidden" name="tanggal" id="note_tanggal">
                <div class="mb-3 d-none" id="note_user_box">
                    <span class="monitor-pill">
                        <i class="fa-solid fa-user me-1"></i>
                        <span id="note_user_name"></span>
                    </span>
                </div>

                <div class="mb-3">
                    <label class="form-label-admin">Kategori <span class="text-danger">*</span></label>
                    <div class="priority-options">
                        @foreach ($noteCategories as $category)
                            <label class="priority-option note-{{ $category->kode }}">
                                <input type="radio" name="kategori" value="{{ $category->kode }}" {{ $category->kode === 'sedang' ? 'checked' : '' }} required>
                                {{ $category->nama }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-admin">Judul Note <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control form-control-admin w-100" placeholder="Contoh: Buat migration absensi" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Catatan</label>
                    <textarea name="catatan" rows="3" class="form-control form-control-admin w-100" placeholder="Detail kebutuhan atau arahan kerja..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Note</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Pilih Anggota Project Baru --}}
<div class="modal fade modal-clean" id="createProjectMembersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-users me-2" style="color:var(--primary);"></i>Pilih Anggota Project</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-action" title="Pilih semua" onclick="toggleProjectUsers('create-project-user', true)">
                        <i class="fa-solid fa-check-double"></i>
                    </button>
                    <button type="button" class="btn-action danger" title="Kosongkan pilihan" onclick="toggleProjectUsers('create-project-user', false)">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>
            <div class="employee-check-grid mb-3">
                @foreach ($users as $u)
                    <label class="employee-check">
                        <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" class="create-project-user" data-name="{{ $u->nama }}" form="createProjectForm">
                        <span>{{ $u->nama }}{{ $u->email ? ' - ' . $u->email : '' }}</span>
                    </label>
                @endforeach
            </div>
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="text-muted" style="font-size:0.8rem;" id="create_member_modal_count">0 peserta dipilih</span>
                <button type="button" class="btn-add" data-bs-dismiss="modal">
                    <i class="fa-solid fa-check"></i> Selesai
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Pilih Anggota Edit Project --}}
<div class="modal fade modal-clean" id="editProjectMembersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-users me-2" style="color:var(--primary);"></i>Edit Anggota Project</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-action" title="Pilih semua" onclick="toggleProjectUsers('edit-project-user', true)">
                        <i class="fa-solid fa-check-double"></i>
                    </button>
                    <button type="button" class="btn-action danger" title="Kosongkan pilihan" onclick="toggleProjectUsers('edit-project-user', false)">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>
            <div class="employee-check-grid mb-3">
                @foreach ($users as $u)
                    <label class="employee-check">
                        <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" class="edit-project-user" data-name="{{ $u->nama }}" form="editProjectForm">
                        <span>{{ $u->nama }}{{ $u->email ? ' - ' . $u->email : '' }}</span>
                    </label>
                @endforeach
            </div>
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="text-muted" style="font-size:0.8rem;" id="edit_member_modal_count">0 peserta dipilih</span>
                <button type="button" class="btn-add" data-bs-dismiss="modal">
                    <i class="fa-solid fa-check"></i> Selesai
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Edit Project --}}
<div class="modal fade modal-clean" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-gantt me-2" style="color:var(--primary);"></i>Edit Project</h5>
            <form action="" method="POST" id="editProjectForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Peserta Magang Project</label>
                    <button type="button" class="member-selector" data-bs-toggle="modal" data-bs-target="#editProjectMembersModal">
                        <span class="member-selector-top">
                            <span><i class="fa-solid fa-users me-1" style="color:var(--primary);"></i> Pilih anggota project</span>
                            <span class="member-count" id="edit_member_count">0 dipilih</span>
                        </span>
                        <span class="member-preview" id="edit_member_preview">
                            <span class="member-placeholder">Klik untuk membuka daftar anggota</span>
                        </span>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label-admin">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="ep_nama" class="form-control form-control-admin w-100" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Mulai</label>
                        <input type="date" name="tanggal_mulai" id="ep_tanggal_mulai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Selesai</label>
                        <input type="date" name="tanggal_selesai" id="ep_tanggal_selesai" class="form-control form-control-admin w-100" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-admin">Status</label>
                    <select name="status" id="ep_status" class="form-control form-control-admin w-100">
                        @foreach ($projectStatuses as $projectStatus)
                            <option value="{{ $projectStatus->kode }}">{{ $projectStatus->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Kebutuhan Project</label>
                    <textarea name="kebutuhan" id="ep_kebutuhan" rows="3" class="form-control form-control-admin w-100"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Project</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Timeline --}}
<div class="modal fade modal-clean" id="editTimelineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-calendar-days me-2" style="color:var(--primary);"></i>Edit Timeline</h5>
            <form action="" method="POST" id="editTimelineForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Nama Timeline <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="et_nama" class="form-control form-control-admin w-100" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" id="et_tanggal_mulai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" id="et_tanggal_selesai" class="form-control form-control-admin w-100" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Status</label>
                    <select name="status" id="et_status" class="form-select form-control-admin w-100" required>
                        @foreach (\App\Models\ProjectTimeline::statusOptions() as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Timeline</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Modul --}}
<div class="modal fade modal-clean" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-layer-group me-2" style="color:var(--primary);"></i>Edit Modul</h5>
            <form action="" method="POST" id="editModuleForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Nama Modul <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="em_nama" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Bobot (%) <span class="text-danger">*</span></label>
                        <input type="number" name="bobot" id="em_bobot" min="0" max="100" step="0.01" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" id="em_tanggal_mulai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" id="em_tanggal_selesai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label-admin">Deskripsi Pekerjaan</label>
                        <textarea name="deskripsi" id="em_deskripsi" rows="3" class="form-control form-control-admin w-100"></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Modul</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Tambah Bidang --}}
<div class="modal fade modal-clean" id="addBidangModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-layer-group me-2" style="color:var(--primary);"></i>Tambah Bidang Magang</h5>
            <form action="{{ route('admin.bidang.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label-admin">Nama Bidang <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control form-control-admin w-100" placeholder="Contoh: Backend Developer" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Bidang --}}
<div class="modal fade modal-clean" id="editBidangModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-pen me-2" style="color:var(--primary);"></i>Edit Bidang Magang</h5>
            <form action="" method="POST" id="editBidangForm">
                @csrf
                <div class="mb-4">
                    <label class="form-label-admin">Nama Bidang <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="eb_nama" class="form-control form-control-admin w-100" placeholder="Contoh: Backend Developer" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Tambah Pembimbing --}}
<div class="modal fade modal-clean" id="addPembimbingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-tie me-2" style="color:var(--primary);"></i>Tambah Pembimbing Magang</h5>
            <form action="{{ route('admin.pembimbing.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label-admin">Nama Pembimbing <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control form-control-admin w-100" placeholder="Contoh: Rina Kartika" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                    <select name="bidang_id" class="form-select form-control-admin w-100" required>
                        <option value="" disabled selected>Pilih bidang...</option>
                        @foreach ($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Pembimbing --}}
<div class="modal fade modal-clean" id="editPembimbingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-pen me-2" style="color:var(--primary);"></i>Edit Pembimbing Magang</h5>
            <form action="" method="POST" id="editPembimbingForm">
                @csrf
                <div class="mb-4">
                    <label class="form-label-admin">Nama Pembimbing <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="epm_nama" class="form-control form-control-admin w-100" placeholder="Contoh: Rina Kartika" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                    <select name="bidang_id" id="epm_bidang_id" class="form-select form-control-admin w-100" required>
                        <option value="" disabled>Pilih bidang...</option>
                        @foreach ($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Tambah Task Baru --}}
<div class="modal fade modal-clean" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus me-2" style="color:var(--primary);"></i>Tambah Task Baru</h5>
            <form action="{{ route('admin.project.task.store') }}" method="POST" id="addTaskForm">
                @csrf
                <input type="hidden" name="project_id" id="at_project_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Pilih Modul <span class="text-danger">*</span></label>
                        <select name="module_id" id="at_module_id" class="form-select form-control-admin w-100" required>
                            <!-- populated by js -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Judul Task <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control form-control-admin w-100" placeholder="Contoh: Membuat rancangan ERD" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">PIC Tugas (Opsional - Bisa Diambil Mandiri oleh User)</label>
                        <select name="user_id" id="at_user_id" class="form-select form-control-admin w-100">
                            <!-- populated by js -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-admin">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-admin w-100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-admin">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-admin w-100">
                    </div>
                    <div class="col-12">
                        <label class="form-label-admin">Deskripsi Tugas</label>
                        <textarea name="deskripsi" rows="3" class="form-control form-control-admin w-100" placeholder="Deskripsi tugas atau hasil yang diharapkan..."></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Task</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('#adminTab .tab-btn');
    const panels = {
        pegawai: document.getElementById('panel-pegawai'),
        sertifikat: document.getElementById('panel-sertifikat'),
        @if ($isSuperAdmin)
        rekap: document.getElementById('panel-rekap'),
        jadwal: document.getElementById('panel-jadwal'),
        timeline: document.getElementById('panel-timeline'),
        bidang: document.getElementById('panel-bidang'),
        @endif
    };

    function switchTab(name) {
        if (! panels[name]) return;
        tabs.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === name));
        Object.keys(panels).forEach(key => {
            if (! panels[key]) return;
            panels[key].classList.toggle('d-none', key !== name);
        });
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    const activeTab = new URLSearchParams(window.location.search).get('tab') || @json($activeAdminTab);
    if (activeTab && panels[activeTab]) {
        switchTab(activeTab);
    }

    initProjectAssignmentDragDrop();
    bindProjectMemberPickers();

    // Fix overlap issues for nested project edit member modal
    const editProjectMembersModalEl = document.getElementById('editProjectMembersModal');
    const editProjectModalEl = document.getElementById('editProjectModal');
    if (editProjectMembersModalEl && editProjectModalEl) {
        editProjectMembersModalEl.addEventListener('show.bs.modal', function () {
            const inst = bootstrap.Modal.getInstance(editProjectModalEl);
            if (inst) {
                inst.hide();
            }
        });
        editProjectMembersModalEl.addEventListener('hidden.bs.modal', function () {
            const inst = bootstrap.Modal.getOrCreateInstance(editProjectModalEl);
            inst.show();
        });
    }

    bindDependentPembimbingSelects();
    updateMemberSummary('create-project-user', 'create_member_count', 'create_member_preview', 'create_member_modal_count');
    updateMemberSummary('edit-project-user', 'edit_member_count', 'edit_member_preview', 'edit_member_modal_count');

});

function confirmRandomSchedule(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Acak jadwal mingguan?',
        text: 'Semua peserta magang akan dibagi acak antara pola Sen/Rab WFH dan Sen/Rab WFO. Jumat tetap WFH.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, acak!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) e.target.submit(); });
    return false;
}

function editUser(id, nama, email, pembimbingMagangId, bidangId, tanggalMulaiMagang, tanggalSelesaiMagang, statusAkun) {
    document.getElementById('editForm').action = "{{ url('admin/pegawai/update') }}/" + id;
    document.getElementById('e_nama').value = nama;
    document.getElementById('e_email').value = email || '';
    document.getElementById('e_password').value = '';
    const bidangSelect = document.getElementById('e_bidang_id');
    bidangSelect.value = bidangId || '';
    syncDependentPembimbing(bidangSelect, pembimbingMagangId || '');
    document.getElementById('e_tanggal_mulai_magang').value = tanggalMulaiMagang || '';
    document.getElementById('e_tanggal_selesai_magang').value = tanggalSelesaiMagang || '';
    document.getElementById('e_status_akun').value = statusAkun || 'aktif';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function bindDependentPembimbingSelects() {
    document.querySelectorAll('.js-bidang-select').forEach(bidangSelect => {
        bidangSelect.addEventListener('change', () => syncDependentPembimbing(bidangSelect));
        syncDependentPembimbing(bidangSelect);
    });
}

function syncDependentPembimbing(bidangSelect, selectedValue = null) {
    const targetId = bidangSelect.dataset.pembimbingTarget;
    const pembimbingSelect = document.getElementById(targetId);
    if (!pembimbingSelect) return;

    const bidangId = bidangSelect.value;
    const placeholder = pembimbingSelect.querySelector('option[value=""]');
    const options = Array.from(pembimbingSelect.querySelectorAll('option[data-bidang-id]'));

    pembimbingSelect.disabled = !bidangId;
    if (placeholder) {
        placeholder.textContent = bidangId ? 'Pilih pembimbing...' : 'Pilih bidang terlebih dahulu...';
    }

    options.forEach(option => {
        const visible = option.dataset.bidangId === bidangId;
        option.hidden = !visible;
        option.disabled = !visible;
        if (!visible && option.selected) {
            option.selected = false;
        }
    });

    if (selectedValue) {
        const selectedOption = options.find(option => option.value === String(selectedValue) && !option.disabled);
        pembimbingSelect.value = selectedOption ? String(selectedValue) : '';
        return;
    }

    if (!options.some(option => option.selected && !option.disabled)) {
        pembimbingSelect.value = '';
    }
}

function openNoteModal(projectId, projectName, tanggal, tanggalLabel, userId = null, userName = null) {
    document.getElementById('note_project_id').value = projectId;
    document.getElementById('note_user_id').value = userId || '';
    document.getElementById('note_tanggal').value = tanggal;
    document.getElementById('noteModalMeta').innerText = userName
        ? `${projectName} - ${tanggalLabel} - ${userName}`
        : `${projectName} - ${tanggalLabel}`;

    const noteUserBox = document.getElementById('note_user_box');
    const noteUserName = document.getElementById('note_user_name');
    if (noteUserBox && noteUserName) {
        noteUserBox.classList.toggle('d-none', !userName);
        noteUserName.innerText = userName || '';
    }

    new bootstrap.Modal(document.getElementById('noteModal')).show();
}

function toggleProjectUsers(className, checked) {
    document.querySelectorAll(`.${className}`).forEach(input => {
        input.checked = checked;
    });
    refreshMemberSummaryByClass(className);
}

function requireCheckedProjectUsers(event, className) {
    const hasChecked = Array.from(document.querySelectorAll(`.${className}`)).some(input => input.checked);

    if (!hasChecked) {
        event.preventDefault();
        Swal.fire('Pilih peserta magang', 'Checklist minimal satu peserta magang untuk timeline project ini.', 'warning');
        return false;
    }

    return true;
}

function bindProjectMemberPickers() {
    document.querySelectorAll('.create-project-user, .edit-project-user').forEach(input => {
        input.addEventListener('change', () => refreshMemberSummaryByClass(input.classList.contains('create-project-user') ? 'create-project-user' : 'edit-project-user'));
    });
}

function refreshMemberSummaryByClass(className) {
    if (className === 'create-project-user') {
        updateMemberSummary('create-project-user', 'create_member_count', 'create_member_preview', 'create_member_modal_count');
        return;
    }

    updateMemberSummary('edit-project-user', 'edit_member_count', 'edit_member_preview', 'edit_member_modal_count');
}

function updateMemberSummary(className, countId, previewId, modalCountId) {
    const selected = Array.from(document.querySelectorAll(`.${className}:checked`));
    const countText = `${selected.length} dipilih`;
    const countEl = document.getElementById(countId);
    const previewEl = document.getElementById(previewId);
    const modalCountEl = document.getElementById(modalCountId);

    if (countEl) countEl.innerText = countText;
    if (modalCountEl) modalCountEl.innerText = `${selected.length} peserta dipilih`;

    if (!previewEl) return;

    if (selected.length === 0) {
        previewEl.innerHTML = '<span class="member-placeholder">Klik untuk membuka daftar anggota</span>';
        return;
    }

    previewEl.innerHTML = selected.slice(0, 4).map(input => (
        `<span class="employee-chip"><i class="fa-solid fa-user"></i> ${escapeHtml(input.dataset.name || input.value)}</span>`
    )).join('');

    if (selected.length > 4) {
        previewEl.insertAdjacentHTML('beforeend', `<span class="member-count">+${selected.length - 4} lagi</span>`);
    }
}

function handleTimelineDayClick(event, projectId, projectName, tanggal, tanggalLabel) {
    if (event.target.closest('.assignment-remove') || event.target.closest('.assignment-chip')) {
        return;
    }

    openNoteModal(projectId, projectName, tanggal, tanggalLabel);
}

function initProjectAssignmentDragDrop() {
    document.querySelectorAll('.employee-chip[draggable="true"]').forEach(chip => {
        chip.addEventListener('dragstart', event => {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('application/json', JSON.stringify({
                projectId: chip.dataset.projectId,
                userId: chip.dataset.userId,
                userName: chip.dataset.userName,
            }));
        });
    });

    document.querySelectorAll('.timeline-day[data-project-id][data-date]').forEach(day => {
        day.addEventListener('dragover', event => {
            event.preventDefault();
            day.classList.add('drag-over');
        });

        day.addEventListener('dragleave', () => day.classList.remove('drag-over'));

        day.addEventListener('drop', async event => {
            event.preventDefault();
            event.stopPropagation();
            day.classList.remove('drag-over');

            const raw = event.dataTransfer.getData('application/json');
            if (!raw) return;

            const employee = JSON.parse(raw);
            if (String(employee.projectId) !== String(day.dataset.projectId)) {
                Swal.fire('Peserta beda project', 'Drag peserta magang dari baris project yang sama.', 'warning');
                return;
            }

            await saveDayAssignment(day, employee);
        });
    });
}

async function saveDayAssignment(day, employee) {
    try {
        const response = await fetch("{{ route('admin.project.assignment.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                project_id: day.dataset.projectId,
                user_id: employee.userId,
                tanggal: day.dataset.date,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Assignment gagal disimpan.');
        }

        renderAssignmentChip(day, payload);
        openNoteModal(
            payload.project_id,
            day.closest('.project-row')?.querySelector('.project-row-header h6')?.innerText || 'Project',
            payload.tanggal,
            new Date(`${payload.tanggal}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }),
            payload.user_id,
            payload.user_name
        );
    } catch (error) {
        Swal.fire('Gagal', error.message, 'error');
    }
}

function renderAssignmentChip(day, assignment) {
    const holder = day.querySelector('.day-assignments');
    if (!holder || holder.querySelector(`[data-user-id="${assignment.user_id}"]`)) {
        return;
    }

    const chip = document.createElement('span');
    chip.className = 'assignment-chip';
    chip.dataset.assignmentId = assignment.id;
    chip.dataset.userId = assignment.user_id;
    chip.innerHTML = `
        <i class="fa-solid fa-user-check"></i> ${escapeHtml(assignment.user_name)}
        <button type="button" class="assignment-remove" title="Hapus peserta magang dari hari ini" onclick="removeAssignment(event, ${assignment.id})">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    holder.appendChild(chip);
}

async function removeAssignment(event, assignmentId) {
    event.preventDefault();
    event.stopPropagation();

    const chip = event.target.closest('.assignment-chip');

    try {
        const response = await fetch("{{ url('admin/project/assignment/hapus') }}/" + assignmentId, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Assignment gagal dihapus.');
        }

        chip?.remove();
    } catch (error) {
        Swal.fire('Gagal', error.message, 'error');
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function editProject(id, userIds, nama, kebutuhan, tanggalMulai, tanggalSelesai, status) {
    document.getElementById('editProjectForm').action = "{{ url('admin/project/update') }}/" + id;
    const selectedIds = (Array.isArray(userIds) ? userIds : [userIds]).map(String);
    document.querySelectorAll('.edit-project-user').forEach(input => {
        input.checked = selectedIds.includes(String(input.value));
    });
    updateMemberSummary('edit-project-user', 'edit_member_count', 'edit_member_preview', 'edit_member_modal_count');
    document.getElementById('ep_nama').value = nama;
    document.getElementById('ep_kebutuhan').value = kebutuhan || '';
    document.getElementById('ep_tanggal_mulai').value = tanggalMulai;
    document.getElementById('ep_tanggal_selesai').value = tanggalSelesai;
    document.getElementById('ep_status').value = status || 'aktif';
    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}

function editProjectTimeline(button) {
    const form = document.getElementById('editTimelineForm');
    form.action = "{{ url('admin/project/timeline/update') }}/" + button.dataset.id;
    document.getElementById('et_nama').value = button.dataset.nama || '';
    document.getElementById('et_tanggal_mulai').value = button.dataset.tanggalMulai || '';
    document.getElementById('et_tanggal_selesai').value = button.dataset.tanggalSelesai || '';
    document.getElementById('et_status').value = button.dataset.status || 'belum_dimulai';
    new bootstrap.Modal(document.getElementById('editTimelineModal')).show();
}

function editProjectModule(button) {
    const form = document.getElementById('editModuleForm');
    form.action = "{{ url('admin/project/module/update') }}/" + button.dataset.id;
    document.getElementById('em_nama').value = button.dataset.nama || '';
    document.getElementById('em_bobot').value = button.dataset.bobot || 0;
    document.getElementById('em_tanggal_mulai').value = button.dataset.tanggalMulai || '';
    document.getElementById('em_tanggal_selesai').value = button.dataset.tanggalSelesai || '';
    document.getElementById('em_deskripsi').value = button.dataset.deskripsi || '';

    new bootstrap.Modal(document.getElementById('editModuleModal')).show();
}

function confirmProjectDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus project?',
        text: 'Semua timeline, modul, dan catatan project ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}

function confirmTimelineDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus timeline?',
        text: 'Semua modul di dalam timeline ini akan ikut dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}

function confirmModuleDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus modul?',
        text: 'PIC modul akan dilepas dan modul ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}

function confirmDel(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus peserta magang?',
        text: 'Seluruh data absensi peserta magang ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) window.location.href = url; });
}

function confirmAttendanceDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus data absensi?',
        text: 'Data kehadiran dan lampiran foto pada baris ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}

function confirmCertificateDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus file sertifikat?',
        text: 'File upload sertifikat peserta ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}

function editBidang(id, nama) {
    const form = document.getElementById('editBidangForm');
    form.action = "{{ url('admin/bidang/update') }}/" + id;
    document.getElementById('eb_nama').value = nama;
    new bootstrap.Modal(document.getElementById('editBidangModal')).show();
}

function confirmDelBidang(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus bidang magang?',
        text: 'Bidang ini akan dihapus. Peserta magang di bidang ini akan diset tanpa bidang.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) window.location.href = url; });
}

function editPembimbing(id, nama, bidangId) {
    const form = document.getElementById('editPembimbingForm');
    form.action = "{{ url('admin/pembimbing/update') }}/" + id;
    document.getElementById('epm_nama').value = nama;
    document.getElementById('epm_bidang_id').value = bidangId || '';
    new bootstrap.Modal(document.getElementById('editPembimbingModal')).show();
}

function confirmDelPembimbing(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus pembimbing magang?',
        text: 'Pembimbing ini akan dihapus. Peserta magang dengan pembimbing ini akan diset tanpa pembimbing.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) window.location.href = url; });
}

function switchProjectView(projectId, viewMode) {
    const timelineEl = document.getElementById('project-view-timeline-' + projectId);
    const boardEl = document.getElementById('project-view-board-' + projectId);
    const btnTimeline = document.getElementById('btn-timeline-' + projectId);
    const btnBoard = document.getElementById('btn-board-' + projectId);

    if (viewMode === 'timeline') {
        if (timelineEl) timelineEl.classList.remove('d-none');
        if (boardEl) boardEl.classList.add('d-none');
        if (btnTimeline) {
            btnTimeline.classList.add('active', 'btn-primary');
            btnTimeline.classList.remove('btn-outline-primary');
        }
        if (btnBoard) {
            btnBoard.classList.remove('active', 'btn-primary');
            btnBoard.classList.add('btn-outline-primary');
        }
    } else {
        if (timelineEl) timelineEl.classList.add('d-none');
        if (boardEl) boardEl.classList.remove('d-none');
        if (btnTimeline) {
            btnTimeline.classList.remove('active', 'btn-primary');
            btnTimeline.classList.add('btn-outline-primary');
        }
        if (btnBoard) {
            btnBoard.classList.add('active', 'btn-primary');
            btnBoard.classList.remove('btn-outline-primary');
        }
    }
}

function openAddTaskModal(projectId, membersJson, modulesJson) {
    document.getElementById('at_project_id').value = projectId;
    
    const moduleSelect = document.getElementById('at_module_id');
    moduleSelect.innerHTML = '<option value="" disabled selected>Pilih modul...</option>';
    modulesJson.forEach(m => {
        moduleSelect.insertAdjacentHTML('beforeend', `<option value="${m.id}">${escapeHtml(m.nama)}</option>`);
    });

    const userSelect = document.getElementById('at_user_id');
    userSelect.innerHTML = '<option value="">-- Biarkan Kosong (Ambil Mandiri oleh User) --</option>';
    membersJson.forEach(u => {
        userSelect.insertAdjacentHTML('beforeend', `<option value="${u.id}">${escapeHtml(u.nama)}</option>`);
    });

    new bootstrap.Modal(document.getElementById('addTaskModal')).show();
}

function openAddTaskModalWithModule(projectId, membersJson, modulesJson, moduleId) {
    openAddTaskModal(projectId, membersJson, modulesJson);
    const moduleSelect = document.getElementById('at_module_id');
    if (moduleSelect) {
        moduleSelect.value = moduleId;
    }
}

@if(session('project_created_id'))
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const newProjectRow = document.getElementById('project-row-{{ session('project_created_id') }}');
        if (newProjectRow) {
            newProjectRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Highlight card dengan outline primary sementara
            newProjectRow.style.transition = 'outline 0.3s ease, outline-offset 0.3s ease';
            newProjectRow.style.outline = '3px solid var(--primary)';
            newProjectRow.style.outlineOffset = '4px';
            
            setTimeout(function() {
                newProjectRow.style.transition = 'outline 1.5s ease, outline-offset 1.5s ease';
                newProjectRow.style.outline = '3px solid transparent';
                newProjectRow.style.outlineOffset = '0px';
            }, 3000);
        }
    }, 500); // Tunggu sedikit agar loading tab selesai
});
@endif
</script>
@endsection
