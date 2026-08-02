@extends('layouts.admin')

@section('title', 'Dashboard Admin - Absensi & Laporan Harian')

@section('styles')
<style>
    /* Kanban Board Styles */
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
        margin-top: 1rem;
    }
    @media (max-width: 768px) {
        .kanban-board { grid-template-columns: 1fr; }
    }
    .kanban-col {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.9rem;
        min-height: 320px;
    }
    .kanban-col-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 0.75rem;
        border-bottom: 1.5px solid var(--border);
        padding-bottom: 0.5rem;
    }
    .kanban-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.6rem;
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
        border-radius: 8px;
        padding: 0.9rem;
        overflow-x: auto;
        margin-top: 0.9rem;
    }
    .gantt-row {
        display: flex;
        align-items: center;
        min-width: 620px;
        padding: 0.55rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .gantt-row:last-child {
        border-bottom: none;
    }
    .gantt-label {
        width: 155px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--dark);
        padding-right: 0.75rem;
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
        gap: 0.45rem;
        margin-top: 0.75rem;
        background: #fafbff;
        border: 1px solid var(--border);
        padding: 0.75rem;
        border-radius: 8px;
    }
    .comparison-bar-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .comparison-bar-label {
        width: 92px;
        flex: 0 0 92px;
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
        gap: 0.75rem;
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
        border-radius: 8px;
        background: #fff;
        padding: 0.65rem 0.75rem;
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
        gap: 0.5rem;
        font-size: 0.86rem;
        font-weight: 800;
    }

    .member-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        min-height: 24px;
        margin-top: 0.45rem;
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
        gap: 0.32rem;
        margin-top: 0.5rem;
    }

    .employee-chip,
    .assignment-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        max-width: 100%;
        border: 1px solid rgba(99,102,241,0.16);
        border-radius: 7px;
        background: rgba(99,102,241,0.08);
        color: var(--primary-dark);
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.28rem 0.5rem;
        line-height: 1.25;
        white-space: normal;
        overflow-wrap: anywhere;
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
        border-radius: 8px;
        background: #fff;
        overflow: visible;
    }

    .project-row-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 0.95rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .timeline-group {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .timeline-group-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.55rem;
        padding: 0.75rem 0.85rem;
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
        padding: 0.75rem;
    }

    .timeline-days {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(104px, 112px);
        gap: 0.45rem;
        min-height: 126px;
    }

    .timeline-day {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
        padding: 0.6rem;
        min-height: 118px;
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

    #panel-timeline > .admin-card.p-4 {
        padding: 1rem !important;
        border-radius: 8px;
    }

    #panel-timeline > .admin-card.mb-4 {
        margin-bottom: 0.85rem !important;
    }

    #panel-timeline .project-row > .p-3,
    #panel-timeline .monitor-item.p-3,
    #panel-timeline .border.rounded-3.p-3 {
        padding: 0.85rem !important;
    }

    #panel-timeline .project-row-header > div:first-child {
        min-width: 0;
        flex: 1 1 420px;
    }

    #panel-timeline .project-row-header > .d-flex {
        flex: 0 0 auto;
    }

    #panel-timeline .monitor-meta,
    #panel-timeline .kanban-card-title,
    #panel-timeline .monitor-name {
        overflow-wrap: anywhere;
    }

    #panel-timeline .monitor-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.7rem;
    }

    #panel-timeline .kanban-list {
        max-height: 340px !important;
        padding-right: 0.15rem;
    }

    #panel-timeline .btn-module-collapse {
        border-radius: 8px !important;
        min-height: 46px;
    }

    #panel-pegawai .table-responsive,
    #panel-sertifikat .table-responsive {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    #panel-pegawai .data-table,
    #panel-sertifikat .data-table {
        min-width: 1240px;
    }

    #panel-pegawai .data-table th,
    #panel-pegawai .data-table td,
    #panel-sertifikat .data-table th,
    #panel-sertifikat .data-table td {
        white-space: nowrap;
    }

    #panel-pegawai .data-table th:nth-child(2),
    #panel-pegawai .data-table td:nth-child(2),
    #panel-pegawai .data-table th:nth-child(5),
    #panel-pegawai .data-table td:nth-child(5),
    #panel-pegawai .data-table th:nth-child(6),
    #panel-pegawai .data-table td:nth-child(6),
    #panel-sertifikat .data-table th:nth-child(2),
    #panel-sertifikat .data-table td:nth-child(2),
    #panel-sertifikat .data-table th:nth-child(4),
    #panel-sertifikat .data-table td:nth-child(4) {
        min-width: 160px;
    }

    #panel-pegawai .data-table th:nth-child(3),
    #panel-pegawai .data-table td:nth-child(3),
    #panel-sertifikat .data-table th:nth-child(3),
    #panel-sertifikat .data-table td:nth-child(3) {
        min-width: 230px;
    }

    #panel-pegawai .data-table th:nth-child(7),
    #panel-pegawai .data-table td:nth-child(7),
    #panel-sertifikat .data-table th:nth-child(5),
    #panel-sertifikat .data-table td:nth-child(5) {
        min-width: 190px;
    }

    #panel-sertifikat .data-table th:nth-child(7),
    #panel-sertifikat .data-table td:nth-child(7) {
        min-width: 210px;
    }

    #panel-sertifikat .data-table th:nth-child(8),
    #panel-sertifikat .data-table td:nth-child(8) {
        min-width: 300px;
    }

    #panel-sertifikat td form.d-flex {
        flex-wrap: nowrap !important;
    }

    #panel-sertifikat input[type="file"].form-control-admin {
        min-width: 230px;
        max-width: none !important;
    }

    #panel-sertifikat .btn-action,
    #panel-pegawai .btn-action {
        flex-shrink: 0;
    }

    #panel-sertifikat .certificate-action-btn {
        width: auto;
        min-width: 86px;
        padding: 0 0.7rem;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .admin-wrap {
            width: calc(100% - 1rem);
            padding: 1.4rem 0 2rem;
        }

        #panel-timeline > .admin-card.p-4 {
            padding: 0.85rem !important;
        }

        #panel-timeline .project-row-header {
            padding: 0.75rem;
        }

        #panel-timeline .project-row-header > div:first-child,
        #panel-timeline .project-row-header > .d-flex {
            flex-basis: 100%;
            width: 100%;
        }

        #panel-timeline .project-row-header > .d-flex {
            justify-content: flex-start;
        }

        #panel-timeline .comparison-bar-row {
            display: grid;
            grid-template-columns: 88px minmax(0, 1fr) auto;
            gap: 0.45rem;
        }

        #panel-timeline .comparison-bar-label {
            width: auto;
            flex-basis: auto;
        }

        .gantt-row {
            min-width: 540px;
        }

        .gantt-label {
            width: 130px;
        }

        .timeline-days {
            grid-auto-columns: minmax(98px, 108px);
        }
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

    /* Collapsible Envelope Form for Modules */
    .btn-module-collapse {
        background: #f8fafc;
        border: 1px solid var(--border);
        color: var(--dark);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        outline: none;
    }
    .btn-module-collapse:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: var(--primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .btn-module-collapse[aria-expanded="true"] {
        background: #eef2ff;
        border-color: rgba(99, 102, 241, 0.35);
        color: var(--primary);
        box-shadow: 0 2px 10px rgba(99,102,241,0.08);
    }
    .btn-module-collapse[aria-expanded="true"] .transition-chevron {
        transform: rotate(180deg);
        color: var(--primary) !important;
    }
    .btn-module-collapse[aria-expanded="true"] .icon-closed {
        display: none !important;
    }
    .btn-module-collapse[aria-expanded="true"] .icon-opened {
        display: inline-block !important;
    }
    .btn-module-collapse .icon-opened {
        display: none !important;
    }
    .btn-module-collapse[aria-expanded="true"] .collapse-hint-text {
        color: var(--primary) !important;
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
                    {{ $isSuperAdmin ? 'Dashboard Super Admin' : 'Dashboard Admin' }}
                </h1>
                <p class="mb-0 text-muted" style="font-size:0.85rem;">
                    {{ $isSuperAdmin ? 'Akses penuh sistem absensi magang' : 'Kelola peserta magang dan sertifikat' }}
                </p>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5" style="font-size:0.75rem;">
                        <i class="fa-solid fa-layer-group me-1"></i>
                        {{ $adminBidangScope ? $adminBidangScope->nama : 'Semua Bidang' }}
                    </span>
                    @if (!$isSuperAdmin && !$adminBidangScope)
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1.5" style="font-size:0.75rem;">
                            Admin belum punya bidang khusus
                        </span>
                    @endif
                </div>
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
                <div class="admin-stat-lbl">Perlu Ditinjau ({{ $taskTerlambatCount }} Terlambat)</div>
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
                            <div class="filter-label">Tampilan Bidang</div>
                            <select name="bidang_id" class="filter-select" onchange="document.getElementById('filterForm').submit()" style="min-width:260px;">
                                <option value="">Semua Bidang</option>
                                @foreach ($adminBidangOptions as $bidangOption)
                                    <option value="{{ $bidangOption->id }}" {{ (string) $activeBidangId === (string) $bidangOption->id ? 'selected' : '' }}>
                                        {{ $bidangOption->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                        <a href="{{ route('admin.rekap.excel', ['month' => $month, 'year' => $year] + ($activeBidangId ? ['bidang_id' => $activeBidangId] : [])) }}" class="btn-export-excel">
                            <i class="fa-solid fa-file-excel"></i> Unduh Excel
                        </a>
                        <a href="{{ route('admin.rekap.pdf', ['month' => $month, 'year' => $year] + ($activeBidangId ? ['bidang_id' => $activeBidangId] : [])) }}" class="btn-export-pdf">
                            <i class="fa-solid fa-file-pdf"></i> Unduh PDF
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
            @endif
            <button type="button" class="tab-btn {{ $activeAdminTab === 'timeline' ? 'active' : '' }}" data-tab="timeline">
                <i class="fa-solid fa-chart-gantt"></i> Timeline Proyek
            </button>
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
                    @if ($activeBidangId)
                        <input type="hidden" name="bidang_id" value="{{ $activeBidangId }}">
                    @endif
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
                                    <th>Laporan / Proyek</th>
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
                                                @if ($activeBidangId)
                                                    <input type="hidden" name="bidang_id" value="{{ $activeBidangId }}">
                                                @endif
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
            </div>

            <div class="px-4 pb-4">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="pegawai">
                    @if ($activeBidangId)
                        <input type="hidden" name="bidang_id" value="{{ $activeBidangId }}">
                    @endif
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
                            <a href="{{ route('admin.dashboard', ['tab' => 'pegawai'] + ($activeBidangId ? ['bidang_id' => $activeBidangId] : [])) }}" class="btn-logout" title="Reset filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if ($users->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada peserta magang terdaftar</h6>
                    <p>Peserta magang yang mendaftar akan tampil di sini untuk pencatatan absensi.</p>
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
                                        <td class="fw-semibold" style="color:var(--dark);">
                                            {{ $u->nama }}
                                        </td>
                                        <td>{{ $u->email ?? '—' }}</td>
                                        <td>
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
                                            <button type="button" class="btn-action me-1" onclick="editUser({{ $u->id }}, {{ json_encode($u->nama) }}, {{ json_encode($u->email) }}, {{ json_encode($u->pembimbing_magang_id) }}, {{ json_encode($u->bidang_id) }}, {{ json_encode(optional($u->tanggal_mulai_magang)->format('Y-m-d')) }}, {{ json_encode(optional($u->tanggal_selesai_magang)->format('Y-m-d')) }}, {{ json_encode($u->status_akun ?? 'aktif') }}, {{ json_encode($u->grup ?? 'A') }})" title="Edit">
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
                    <p class="text-muted mb-0" style="font-size:0.82rem;">Sertifikat dibuat otomatis dari data peserta magang dan template resmi sistem.</p>
                </div>
            </div>

            @if ($isSuperAdmin)
                <div class="px-4 pb-4">
                    <div class="p-3 border rounded-3" style="border-color:var(--border)!important;background:#f8fafc;">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-bold" style="font-size:0.9rem;color:var(--dark);">
                                    <i class="fa-solid fa-file-code me-1" style="color:var(--primary);"></i> Template Sertifikat Aktif
                                </div>
                                <div class="text-muted mt-1" style="font-size:0.78rem;">
                                    {{ $certificateTemplate['name'] ?? 'Template Default Sistem' }}
                                </div>
                                <div class="text-muted mt-1" style="font-size:0.74rem;">
                                    Upload file HTML yang memakai placeholder seperti <code>@{{ nama }}</code>, <code>@{{ bidang }}</code>, <code>@{{ tanggal_mulai }}</code>, dan <code>@{{ tanggal_selesai }}</code>.
                                </div>
                            </div>
                            <form action="{{ route('admin.sertifikat.template.upload') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
                                @csrf
                                <input type="file" name="certificate_template" class="form-control form-control-admin" accept=".html,.htm" required style="max-width:280px;">
                                <button type="submit" class="btn-add">
                                    <i class="fa-solid fa-upload"></i> Upload Template
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

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
                                <th>Template Sertifikat</th>
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
                                        @if ($eligibleCertificate)
                                            <span class="badge-status badge-hadir">SIAP CETAK</span>
                                        @elseif ($u->tanggal_selesai_magang)
                                            <span class="monitor-pill">Menunggu selesai magang</span>
                                        @else
                                            <span class="monitor-pill">Tanggal selesai belum diisi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:0.82rem;color:var(--dark);">Generate otomatis</div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ $certificateTemplate['name'] ?? 'Template Default Sistem' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                        @if ($eligibleCertificate)
                                            <a href="{{ route('sertifikat.show', \Illuminate\Support\Str::slug($u->nama)) }}" target="_blank" class="btn-action certificate-action-btn" title="Preview sertifikat">
                                                <i class="fa-solid fa-eye"></i>
                                                <span>Preview</span>
                                            </a>
                                            <a href="{{ route('admin.sertifikat.generate', $u) }}" target="_blank" class="btn-action certificate-action-btn" title="Download PDF sertifikat">
                                                <i class="fa-solid fa-file-pdf"></i>
                                                <span>PDF</span>
                                            </a>
                                        @else
                                            <button type="button" class="btn-action certificate-action-btn" disabled title="Sertifikat belum aktif">
                                                <i class="fa-solid fa-lock"></i>
                                                <span>Belum aktif</span>
                                            </button>
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
        @php
            $usersSortedByTeam = $users->sort(function ($a, $b) {
                $grupA = (string) ($a->grup ?? 'A');
                $grupB = (string) ($b->grup ?? 'A');
                $cmp = strnatcasecmp($grupA, $grupB);
                return $cmp !== 0 ? $cmp : strcasecmp((string) $a->nama, (string) $b->nama);
            })->values();

            $getTeamStyle = function($team) {
                $t = strtoupper(trim((string) $team));
                $colors = [
                    'A' => ['bg' => '#eff6ff', 'text' => '#2563eb', 'border' => '#bfdbfe', 'icon' => '🔵'],
                    'B' => ['bg' => '#f0fdf4', 'text' => '#16a34a', 'border' => '#bbf7d0', 'icon' => '🟢'],
                    'C' => ['bg' => '#faf5ff', 'text' => '#9333ea', 'border' => '#e9d5ff', 'icon' => '🟣'],
                    'D' => ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fde68a', 'icon' => '🟠'],
                    'E' => ['bg' => '#fff1f2', 'text' => '#e11d48', 'border' => '#fecdd3', 'icon' => '🔴'],
                    'F' => ['bg' => '#ecfeff', 'text' => '#0891b2', 'border' => '#a5f3fc', 'icon' => '🔷'],
                ];
                return $colors[$t] ?? ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1', 'icon' => '⚪'];
            };
        @endphp

        <div class="admin-card overflow-hidden">
            {{-- View Mode Switcher Header + Pengaturan Tampilan Halaman Utama (Persistent) --}}
            <div class="p-4 border-bottom bg-white" style="border-color:var(--border)!important;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--dark);">
                            <i class="fa-solid fa-calendar-week me-1" style="color:var(--primary);"></i> Pengaturan Jadwal Mingguan
                        </h6>
                        <p class="text-muted mb-0" style="font-size:0.82rem;">
                            Pilih mode tampilan untuk mengatur jadwal kerja WFO / WFH atau pembagian tim peserta magang.
                        </p>
                    </div>

                    {{-- Segmented View Mode Toggle Buttons --}}
                    <div class="btn-group p-1 rounded-2" style="background:#f1f5f9; border:1px solid var(--border);" role="group" id="jadwalViewToggle">
                        <button type="button" 
                            id="btnViewNormal" 
                            class="btn btn-sm px-3 fw-semibold transition-all {{ request('view', 'normal') !== 'team' ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary border-0 bg-transparent' }}"
                            style="border-radius:6px; font-size:0.82rem;"
                            onclick="switchJadwalView('normal')">
                            <i class="fa-solid fa-user me-1.5"></i> Tampilan Perorangan
                        </button>
                        <button type="button" 
                            id="btnViewTeam" 
                            class="btn btn-sm px-3 fw-semibold transition-all {{ request('view') === 'team' ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary border-0 bg-transparent' }}"
                            style="border-radius:6px; font-size:0.82rem;"
                            onclick="switchJadwalView('team')">
                            <i class="fa-solid fa-people-group me-1.5"></i> Tampilan Tim
                        </button>
                    </div>
                </div>

                {{-- Pengaturan Tampilan Jadwal di Halaman Utama (User Biasa) - Selalu Tampil di Luar Tampilan Tim & Perorangan --}}
                <div class="mt-3 p-3 bg-white rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm" style="border-color:var(--border)!important;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:36px; height:36px; font-size:0.95rem;">
                            <i class="fa-solid fa-display"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size:0.85rem;">
                                Pengaturan Tampilan Jadwal di Halaman Utama Peserta
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                Pilih apakah jadwal di halaman awal sistem ditampilkan terkelompok berbasis tim atau per orangan biasa (tanpa tim).
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('admin.jadwal.landing_view') }}" method="POST" id="landingViewForm" class="d-flex align-items-center gap-2 m-0">
                            @csrf
                            <div class="btn-group p-1 rounded-2" style="background:#f1f5f9; border:1px solid var(--border);" role="group">
                                <button type="button" 
                                    class="btn btn-sm px-3 fw-semibold transition-all js-btn-landing-mode {{ ($jadwalLandingView ?? 'individual') === 'team' ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary border-0 bg-transparent' }}"
                                    style="border-radius:6px; font-size:0.78rem;"
                                    onclick="updateLandingViewSetting('team')">
                                    <i class="fa-solid fa-people-group me-1"></i> Tampilan Tim
                                </button>
                                <button type="button" 
                                    class="btn btn-sm px-3 fw-semibold transition-all js-btn-landing-mode {{ ($jadwalLandingView ?? 'individual') !== 'team' ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary border-0 bg-transparent' }}"
                                    style="border-radius:6px; font-size:0.78rem;"
                                    onclick="updateLandingViewSetting('individual')">
                                    <i class="fa-solid fa-user me-1"></i> Perorangan (Tanpa Tim)
                                </button>
                            </div>
                            <input type="hidden" name="jadwal_landing_view" id="inputLandingView" value="{{ $jadwalLandingView ?? 'individual' }}">
                        </form>
                        <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold" style="font-size:0.78rem; border-radius:6px;" title="Lihat Tampilan di Halaman Utama">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Pratinjau
                        </a>
                    </div>
                </div>
            </div>

            {{-- VIEW 1: TAMPILAN PERORANGAN (Jadwal Kerja Peserta Individu / Tanpa Tim) --}}
            <div id="jadwal-view-normal" class="{{ request('view', 'normal') !== 'team' ? '' : 'd-none' }}">
                <div class="p-4 border-bottom bg-light bg-opacity-50" style="border-color:var(--border)!important;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h6 class="fw-bold mb-1" style="color:var(--dark); font-size:0.92rem;">
                                <i class="fa-solid fa-user-check me-1 text-primary"></i> Jadwal Kerja Perorangan (WFO / WFH)
                            </h6>
                            <p class="text-muted mb-0" style="font-size:0.8rem;">
                                Atur jadwal kerja peserta secara manual perorangan, atau gunakan tombol acak normal. Jumat otomatis WFH.
                            </p>
                        </div>
                        
                        {{-- Tombol Acak Normal & Simpan Jadwal --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Acak Jadwal Normal (Perorangan) --}}
                            <form action="{{ route('admin.jadwal.random') }}" method="POST" onsubmit="return confirmRandomSchedule(event)" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary fw-semibold" style="padding:0.42rem 0.85rem; font-size:0.82rem; border-radius:6px;" title="Acak Jadwal Perorangan">
                                    <i class="fa-solid fa-shuffle me-1.5"></i> Acak Normal
                                </button>
                            </form>

                            {{-- Simpan Jadwal --}}
                            <button type="submit" form="scheduleForm" class="btn-add" style="padding:0.42rem 0.9rem; font-size:0.82rem;">
                                <i class="fa-solid fa-floppy-disk me-1.5"></i> Simpan Jadwal
                            </button>
                        </div>
                    </div>

                    {{-- Toolbar Pencarian di Tampilan Perorangan --}}
                    <div class="row g-2 mt-2 pt-2">
                        <div class="col-md-6 col-lg-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" id="filterNormalSearch" class="form-control border-start-0" placeholder="Cari nama peserta atau bidang magang..." style="font-size:0.8rem;">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7 d-flex align-items-center justify-content-md-end gap-2 text-muted" style="font-size:0.75rem;">
                            <span><i class="fa-solid fa-circle-check text-success me-1"></i> Total {{ $users->count() }} Peserta Magang</span>
                        </div>
                    </div>
                </div>

                @if ($users->isEmpty())
                    <div class="empty-state p-5 text-center">
                        <h6>Belum ada peserta magang</h6>
                        <p>Tambahkan peserta magang terlebih dahulu di tab Kelola Magang.</p>
                    </div>
                @else
                    <form action="{{ route('admin.jadwal.update') }}" method="POST" id="scheduleForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="data-table mb-0" id="normalScheduleTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="min-width: 280px; padding: 0.85rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Peserta Magang</th>
                                        <th class="text-center" style="min-width: 120px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Senin</th>
                                        <th class="text-center" style="min-width: 120px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Selasa</th>
                                        <th class="text-center" style="min-width: 120px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Rabu</th>
                                        <th class="text-center" style="min-width: 120px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Kamis</th>
                                        <th class="text-center" style="min-width: 110px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Jumat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $weekdays = ['senin', 'selasa', 'rabu', 'kamis']; 
                                    @endphp
                                    @foreach ($users as $u)
                                        @php 
                                            $jadwal = $u->jadwalMingguan; 
                                            $bidangNama = $u->bidang->nama ?? $u->bidang_magang ?? 'Peserta Magang';
                                        @endphp
                                        <tr id="normal-user-row-{{ $u->id }}" class="normal-user-schedule-row" data-user-id="{{ $u->id }}" data-user-name="{{ strtolower($u->nama) }}" data-user-bidang="{{ strtolower($bidangNama) }}">
                                            <td style="padding: 0.85rem 1.25rem;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size:0.85rem; background:#ede9fe; color:#6366f1; flex-shrink:0;">
                                                        {{ strtoupper(substr($u->nama, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-dark fw-semibold" style="font-size:0.9rem;">{{ $u->nama }}</div>
                                                        <div class="text-muted" style="font-size:0.78rem;">{{ $bidangNama }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach ($weekdays as $day)
                                                <td class="text-center" style="vertical-align:middle;">
                                                    <select name="schedules[{{ $u->id }}][{{ $day }}]" class="schedule-select text-center" style="min-width:78px; font-weight:600; padding:0.32rem 0.55rem; border-radius:6px; border:1px solid var(--border);">
                                                        @foreach ($jadwalStatuses as $jadwalStatus)
                                                            <option value="{{ $jadwalStatus->kode }}" {{ ($jadwal?->$day ?? 'wfo') === $jadwalStatus->kode ? 'selected' : '' }}>
                                                                {{ strtoupper($jadwalStatus->kode) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            @endforeach
                                            <td class="text-center" style="vertical-align:middle;">
                                                <span class="jumat-fixed" style="font-size:0.78rem; padding:0.32rem 0.75rem; border-radius:6px; font-weight:700; background:#ede9fe; color:#6d28d9; display:inline-block;">WFH</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                @endif
            </div>

            {{-- VIEW 2: TAMPILAN TIM (Kelola Tim & Pembagian Tim Magang) --}}
            <div id="jadwal-view-team" class="{{ request('view') === 'team' ? '' : 'd-none' }}">
                @if ($users->isEmpty())
                    <div class="empty-state p-5 text-center">
                        <h6>Belum ada peserta magang</h6>
                        <p>Tambahkan peserta magang terlebih dahulu di tab Kelola Magang.</p>
                    </div>
                @else
                    {{-- Ringkasan Tim Peserta --}}
                    <div class="p-4 border-bottom bg-light bg-opacity-50" style="border-color:var(--border)!important;">
                        <div class="p-3 bg-white rounded-3 border" style="border-color:var(--border)!important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-bold" style="font-size:0.85rem; color:var(--dark);">
                                    <i class="fa-solid fa-circle-info text-primary me-1"></i> Ringkasan Tim Peserta
                                </div>
                                <span class="text-muted small" style="font-size:0.75rem;">Total {{ $users->count() }} Peserta Magang Aktif</span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach ($availableTeams as $teamKey)
                                    @php
                                        $tStyle = $getTeamStyle($teamKey);
                                        $count = $users->where('grup', $teamKey)->count();
                                    @endphp
                                    <div class="p-2 rounded-2 border text-center flex-fill" style="min-width:90px; background:{{ $tStyle['bg'] }}; border-color:{{ $tStyle['border'] }}!important;">
                                        <div class="fw-bold" style="font-size:1.1rem; color:{{ $tStyle['text'] }};" id="team-count-{{ $teamKey }}">{{ $count }}</div>
                                        <div class="small fw-semibold" style="font-size:0.72rem; color:{{ $tStyle['text'] }};">
                                            {{ $tStyle['icon'] }} Tim {{ $teamKey }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Form Terpisah untuk Acak Anggota Tim & Acak Jadwal Pola Tim (menghindari nested form) --}}
                    <form id="randomTeamMembersForm" action="{{ route('admin.jadwal.team.random_members') }}" method="POST" onsubmit="return confirmRandomTeamMembers(event)" class="d-none">
                        @csrf
                    </form>
                    <form id="randomTeamScheduleForm" action="{{ route('admin.jadwal.team.random') }}" method="POST" onsubmit="return confirmRandomTeamSchedule(event)" class="d-none">
                        @csrf
                    </form>

                    {{-- Card 2: Form Kelola & Pembagian Tim Peserta (Master dari Kelola Magang) --}}
                    <div class="p-4">
                        <form action="{{ route('admin.jadwal.team.members') }}" method="POST" id="teamMembersForm">
                            @csrf
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:var(--dark);">
                                        <i class="fa-solid fa-people-arrows me-1" style="color:var(--primary);"></i> Pembagian Tim Peserta Magang
                                    </h6>
                                    <p class="text-muted mb-0" style="font-size:0.8rem;">
                                        Daftar peserta otomatis diurutkan berdasarkan Tim (Tim A, Tim B, dst). Saat Anda memilih tim, perubahan akan tersimpan langsung secara otomatis.
                                    </p>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" style="font-size:0.82rem; border-radius:6px; padding:0.42rem 0.85rem;" data-bs-toggle="modal" data-bs-target="#modalTambahTim">
                                        <i class="fa-solid fa-plus me-1 text-primary"></i> Tambah Tim Baru
                                    </button>
                                    <button type="submit" form="randomTeamMembersForm" class="btn-dice" style="padding:0.42rem 0.85rem; font-size:0.82rem;">
                                        <i class="fa-solid fa-shuffle me-1.5"></i> Acak Anggota Tim
                                    </button>
                                    <button type="submit" form="randomTeamScheduleForm" class="btn-dice" style="padding:0.42rem 0.85rem; font-size:0.82rem;" title="Acak Jadwal Tim secara serentak">
                                        <i class="fa-solid fa-dice me-1.5"></i> Acak Pola Tim
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive border rounded-3 overflow-hidden">
                                <table class="data-table mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th style="min-width: 250px;">Nama Peserta Magang (Master)</th>
                                            <th style="min-width: 180px;">Email / Akun</th>
                                            <th style="min-width: 160px;">Bidang Magang</th>
                                            <th style="min-width: 180px;">Penugasan Tim</th>
                                        </tr>
                                    </thead>
                                    <tbody id="teamMembersTableBody">
                                        @php $currentGroup = null; @endphp
                                        @foreach ($usersSortedByTeam as $index => $u)
                                            @php
                                                $userGroup = $u->grup ?? 'A';
                                                $tStyle = $getTeamStyle($userGroup);
                                            @endphp
                                            @if ($currentGroup !== $userGroup)
                                                @php $currentGroup = $userGroup; @endphp
                                                <tr style="background:#f8fafc;" class="team-group-header-row" data-team="{{ $userGroup }}">
                                                    <td colspan="5" class="py-2 px-3 fw-bold" style="font-size:0.82rem; color:{{ $tStyle['text'] }}; border-top:2px solid {{ $tStyle['border'] }};">
                                                        <span class="badge me-1" style="background:{{ $tStyle['bg'] }}; color:{{ $tStyle['text'] }}; border:1px solid {{ $tStyle['border'] }}; font-size:0.78rem;">
                                                            {{ $tStyle['icon'] }} KELOMPOK TIM {{ $userGroup }}
                                                        </span>
                                                        <span class="text-muted fw-normal small team-group-member-count" data-team="{{ $userGroup }}">({{ $users->where('grup', $userGroup)->count() }} Peserta)</span>
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr id="user-row-{{ $u->id }}" class="user-team-row" data-user-id="{{ $u->id }}" data-team="{{ $userGroup }}" data-user-name="{{ strtolower($u->nama) }}">
                                                <td class="text-center text-muted fw-semibold team-row-number" style="font-size:0.82rem;">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-circle-sm rounded-circle d-flex align-items-center justify-content-center fw-bold user-avatar-badge" id="user-avatar-{{ $u->id }}" style="width:28px; height:28px; font-size:0.75rem; background:{{ $tStyle['bg'] }}; color:{{ $tStyle['text'] }}; border:1px solid {{ $tStyle['border'] }};">
                                                            {{ strtoupper(substr($u->nama, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-dark user-name-display" style="font-size:0.88rem;">
                                                                {{ $u->nama }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-muted" style="font-size:0.8rem;">
                                                        <i class="fa-solid fa-envelope me-1 text-secondary" style="font-size:0.75rem;"></i>{{ $u->email }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light border text-dark" style="font-size:0.75rem; font-weight:500;">
                                                        {{ $u->bidang->nama ?? $u->bidang_magang ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <select name="members[{{ $u->id }}][grup]" class="form-select form-select-sm fw-semibold js-team-assignment-select" data-user-id="{{ $u->id }}" style="border-radius:6px; font-size:0.82rem;">
                                                        @foreach ($availableTeams as $tOption)
                                                            @php $optStyle = $getTeamStyle($tOption); @endphp
                                                            <option value="{{ $tOption }}" {{ ($u->grup ?? 'A') === $tOption ? 'selected' : '' }}>
                                                                {{ $optStyle['icon'] }} Tim {{ $tOption }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- TAB: Timeline Project --}}
    <div class="tab-panel {{ $activeAdminTab === 'timeline' ? '' : 'd-none' }}" id="panel-timeline">
        <div class="admin-card p-4 mb-4">
            <form action="{{ route('admin.project.store') }}" method="POST" id="createProjectForm">
                @csrf
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--dark);">
                            <i class="fa-solid fa-folder-tree me-1" style="color:var(--primary);"></i> Buat Proyek
                        </h6>
                        <p class="text-muted mb-0" style="font-size:0.82rem;">Setelah proyek dibuat, tambahkan jadwal dan modul pekerjaan di bawahnya.</p>
                    </div>
                    <button type="submit" class="btn-add">
                        <i class="fa-solid fa-plus"></i> Simpan Proyek
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Peserta Magang Proyek</label>
                        <button type="button" class="member-selector" data-bs-toggle="modal" data-bs-target="#createProjectMembersModal">
                            <span class="member-selector-top">
                                <span><i class="fa-solid fa-users me-1" style="color:var(--primary);"></i> Pilih anggota proyek</span>
                                <span class="member-count" id="create_member_count">0 dipilih</span>
                            </span>
                            <span class="member-preview" id="create_member_preview">
                                <span class="member-placeholder">Klik untuk membuka daftar anggota</span>
                            </span>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Nama Proyek <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-admin w-100" placeholder="Contoh: Website Absensi" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-admin">Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" id="cp_tanggal_mulai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-admin">Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" id="cp_tanggal_selesai" class="form-control form-control-admin w-100" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label-admin">Kebutuhan Proyek</label>
                        <textarea name="kebutuhan" rows="2" class="form-control form-control-admin w-100" placeholder="Tuliskan kebutuhan, target, atau batas pekerjaan proyek..."></textarea>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-card p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-inbox me-1" style="color:var(--primary);"></i> Tinjau Laporan Tugas
                </h6>
                <span class="monitor-pill">{{ $pendingTasks->count() }} Menunggu Ditinjau</span>
            </div>
            @if ($pendingTasks->isEmpty())
                <div class="empty-state py-4 text-center text-muted">
                    <i class="fa-solid fa-circle-check fs-2 text-success mb-2"></i>
                    <h6 class="fw-bold">Semua tugas bersih!</h6>
                    <p class="small mb-0">Tidak ada tugas yang menunggu ditinjau saat ini.</p>
                </div>
            @else
                <div class="monitor-grid">
                    @foreach ($pendingTasks as $task)
                        <div class="monitor-item" style="border-left: 5px solid #f59e0b !important;">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <div class="monitor-name">{{ $task->user->nama ?? 'Peserta belum tercatat' }}</div>
                                    <div class="monitor-meta">{{ $task->project->nama ?? '-' }} &middot; {{ $task->module?->nama ?? '-' }}</div>
                                </div>
                                <span class="monitor-pill" style="background:rgba(245,158,11,0.15);color:#b45309;">DITINJAU</span>
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
                                <span class="monitor-pill">{{ number_format($project->progress_percentage, 1) }}% selesai</span>
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
                                    <span class="employee-chip" title="Anggota proyek">
                                        <i class="fa-solid fa-user"></i> {{ $member->nama }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn-action" title="Edit proyek" onclick="editProject({{ $project->id }}, {{ $projectMemberIds->toJson() }}, {{ json_encode($project->nama) }}, {{ json_encode($project->kebutuhan) }}, {{ json_encode($project->tanggal_mulai->format('Y-m-d')) }}, {{ json_encode($project->tanggal_selesai->format('Y-m-d')) }}, {{ json_encode($project->status) }})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.project.destroy', $project) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn-action danger" onclick="confirmProjectDelete(event, this.form)" title="Hapus proyek">
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
                                <strong>Perhatian:</strong> Total bobot modul untuk proyek ini adalah <strong>{{ $currentWeightSum }}%</strong>. Sesuaikan bobot modul agar totalnya tepat 100% untuk pelacakan progres yang akurat.
                            </div>
                        @endif

                        <!-- Planning vs Actual Comparison Visualisation -->
                        <div class="progress-comparison mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small"><i class="fa-solid fa-chart-line me-1 text-primary"></i> Perbandingan Target Jadwal dan Progres Nyata</span>
                            </div>
                            <div class="comparison-bar-row">
                                <span class="comparison-bar-label">Target Jadwal</span>
                                <div class="comparison-bar-bg">
                                    <div class="comparison-bar-fill" style="width: {{ $project->planned_progress_percentage }}%; background: #64748b;"></div>
                                </div>
                                <span class="fw-bold text-muted small">{{ $project->planned_progress_percentage }}%</span>
                            </div>
                            <div class="comparison-bar-row">
                                <span class="comparison-bar-label">Progres Nyata</span>
                                <div class="comparison-bar-bg">
                                    <div class="comparison-bar-fill" style="width: {{ $project->progress_percentage }}%; background: #6c5ce7;"></div>
                                </div>
                                <span class="fw-bold text-primary small">{{ $project->progress_percentage }}%</span>
                            </div>
                            @if ($project->progress_percentage < $project->planned_progress_percentage)
                                <div class="text-danger mt-1 small" style="font-size:0.75rem;"><i class="fa-solid fa-triangle-exclamation"></i> Progres nyata tertinggal {{ round($project->planned_progress_percentage - $project->progress_percentage, 1) }}% dari target jadwal.</div>
                            @else
                                <div class="text-success mt-1 small" style="font-size:0.75rem;"><i class="fa-solid fa-circle-check"></i> Pekerjaan sesuai jadwal rencana atau berjalan lebih cepat!</div>
                            @endif
                        </div>

                        <!-- Toggle View Mode Switcher -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="d-flex gap-2 bg-light p-1.5 rounded-3 d-inline-flex">
                                <button type="button" class="btn btn-sm btn-primary px-3 rounded-2" id="btn-timeline-{{ $project->id }}" onclick="switchProjectView({{ $project->id }}, 'timeline')">
                                    <i class="fa-solid fa-chart-gantt me-1"></i> Tampilan Timeline Modul
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-2" id="btn-board-{{ $project->id }}" onclick="switchProjectView({{ $project->id }}, 'board')">
                                    <i class="fa-solid fa-chalkboard-user me-1"></i> Papan Tugas
                                </button>
                            </div>
                        </div>

                        <!-- COLLAPSIBLE ENVELOPE: Tambah Modul Baru -->
                        <div class="mb-4">
                            <button type="button" class="btn-module-collapse d-flex justify-content-between align-items-center w-100 p-2.5 px-3 rounded-3" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseAddModule-{{ $project->id }}" 
                                    aria-expanded="false" 
                                    aria-controls="collapseAddModule-{{ $project->id }}">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 30px; height: 30px; background: rgba(99, 102, 241, 0.12); color: var(--primary);">
                                        <i class="fa-solid fa-envelope icon-closed" style="font-size: 0.88rem;"></i>
                                        <i class="fa-solid fa-envelope-open-text icon-opened" style="font-size: 0.88rem;"></i>
                                    </span>
                                    <strong style="font-size:0.86rem; letter-spacing: -0.2px;">Tambah Modul Baru</strong>
                                    <span class="badge bg-white text-muted border fw-normal py-0.5 px-2 d-none d-sm-inline-block" style="font-size: 0.72rem;">Buka / Tutup Formulir</span>
                                </span>
                                <span class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
                                    <span class="collapse-hint-text">Klik untuk mengisi</span>
                                    <i class="fa-solid fa-chevron-down transition-chevron" style="transition: transform 0.25s ease;"></i>
                                </span>
                            </button>

                            <div class="collapse mt-2" id="collapseAddModule-{{ $project->id }}">
                                <div class="p-3 border rounded-3 shadow-sm" style="border-color: var(--border) !important; background: #fbfcfe !important;">
                                    <form action="{{ route('admin.project.module.store') }}" method="POST" class="js-module-create-form" 
                                          data-project-start="{{ $project->tanggal_mulai->format('Y-m-d') }}" 
                                          data-project-end="{{ $project->tanggal_selesai->format('Y-m-d') }}" 
                                          data-project-name="{{ e($project->nama) }}">
                                        @csrf
                                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-calendar-check me-1 text-primary"></i>
                                                Rentang tanggal proyek: <strong class="text-dark">{{ $project->tanggal_mulai->format('d/m/Y') }}</strong> s/d <strong class="text-dark">{{ $project->tanggal_selesai->format('d/m/Y') }}</strong>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <label class="form-label-admin">Nama Modul <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" class="form-control form-control-admin w-100 bg-white" placeholder="Contoh: Login dan Hak Akses" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label-admin">Bobot (%) <span class="text-danger">*</span></label>
                                                <input type="number" name="bobot" value="10" min="0" max="100" step="0.01" class="form-control form-control-admin w-100 bg-white" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label-admin">Mulai <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_mulai" 
                                                       min="{{ $project->tanggal_mulai->format('Y-m-d') }}" 
                                                       max="{{ $project->tanggal_selesai->format('Y-m-d') }}" 
                                                       class="form-control form-control-admin w-100 bg-white js-module-start-input" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label-admin">Selesai <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_selesai" 
                                                       min="{{ $project->tanggal_mulai->format('Y-m-d') }}" 
                                                       max="{{ $project->tanggal_selesai->format('Y-m-d') }}" 
                                                       class="form-control form-control-admin w-100 bg-white js-module-end-input" required>
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
                                </div>
                            </div>
                        </div>

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
                                            <div class="text-muted" style="font-size:0.7rem;">Bobot: {{ $module->bobot }}% &middot; Progres: {{ $module->progress }}%</div>
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
                                    <div class="monitor-item bg-white p-3 rounded-3 shadow-sm border" style="border-color: var(--border) !important;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong class="text-dark fs-6">{{ $module->nama }}</strong>
                                                <div class="text-muted" style="font-size:0.75rem;">
                                                    Bobot: <strong class="text-primary">{{ $module->bobot }}%</strong> &middot; Status: <span class="badge bg-light text-dark border">{{ $module->status_label }}</span>
                                                </div>
                                                <div class="text-muted mt-0.5" style="font-size:0.72rem;">
                                                    <i class="fa-regular fa-calendar me-1"></i>
                                                    {{ $module->tanggal_mulai ? $module->tanggal_mulai->format('d M Y') : '-' }} s/d {{ $module->tanggal_selesai ? $module->tanggal_selesai->format('d M Y') : '-' }}
                                                </div>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill" style="font-size:0.75rem;">
                                                <i class="fa-solid fa-chart-pie me-1"></i>{{ $module->progress }}% Selesai
                                            </span>
                                        </div>
                                        @if ($module->deskripsi)
                                            <p class="text-muted small mt-2 mb-2" style="line-height:1.45;">{{ $module->deskripsi }}</p>
                                        @endif

                                        <!-- Sub-tasks / Task Breakdown under this module -->
                                        <div class="mt-3 pt-2.5 border-top" style="border-color: var(--border) !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-dark" style="font-size:0.75rem;">
                                                    <i class="fa-solid fa-list-check me-1 text-primary"></i> Daftar Tugas / Sub-tugas ({{ $module->tasks->count() }})
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-pill" style="font-size:0.7rem;"
                                                    onclick="openAddTaskModalWithModule({{ $project->id }}, {{ $projectMembers->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama])->values()->toJson() }}, {{ $projectModules->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama, 'tanggal_mulai' => $m->tanggal_mulai?->format('Y-m-d'), 'tanggal_selesai' => $m->tanggal_selesai?->format('Y-m-d')])->values()->toJson() }}, {{ $module->id }})">
                                                    <i class="fa-solid fa-plus-circle me-1"></i> + Tugas
                                                </button>
                                            </div>

                                            @if ($module->tasks->isEmpty())
                                                <div class="p-2.5 rounded-2 bg-light text-center text-muted" style="font-size:0.72rem; border: 1px dashed var(--border);">
                                                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> Belum ada tugas pada modul ini. Klik <strong>+ Tugas</strong> untuk membagi modul menjadi tugas tim.
                                                </div>
                                            @else
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach ($module->tasks as $task)
                                                        <div class="p-2 rounded-2 border bg-light" style="font-size:0.73rem;">
                                                            <div class="d-flex justify-content-between align-items-start gap-1">
                                                                <div class="fw-semibold text-dark">{{ $task->judul }}</div>
                                                                <div class="d-flex align-items-center gap-1">
                                                                    @if ($task->status === 'selesai')
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-1.5 rounded" style="font-size:0.65rem;">Selesai</span>
                                                                    @elseif ($task->status === 'review')
                                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-0.5 px-1.5 rounded" style="font-size:0.65rem;">Ditinjau</span>
                                                                    @elseif ($task->status === 'revision' || $task->catatan_revisi)
                                                                        <span class="badge bg-danger text-white py-0.5 px-1.5 rounded" style="font-size:0.65rem;">Revisi</span>
                                                                    @elseif ($task->status === 'sedang_dikerjakan')
                                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0.5 px-1.5 rounded" style="font-size:0.65rem;">Dikerjakan</span>
                                                                    @else
                                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-0.5 px-1.5 rounded" style="font-size:0.65rem;">Tersedia</span>
                                                                    @endif
                                                                    <form action="{{ route('admin.project.task.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1" style="font-size:0.75rem; text-decoration:none;" title="Hapus tugas">
                                                                            <i class="fa-solid fa-trash-can"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            @if ($task->deskripsi)
                                                                <div class="text-muted mt-0.5" style="font-size:0.7rem; line-height:1.35;">{{ Str::limit($task->deskripsi, 90) }}</div>
                                                            @endif
                                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mt-1.5 pt-1 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                                                                <div class="text-muted" style="font-size:0.7rem;">
                                                                    <i class="fa-regular fa-user me-1"></i>
                                                                    @if ($task->user)
                                                                        Penanggung jawab: <strong class="text-dark">{{ $task->user->nama }}</strong>
                                                                    @else
                                                                        <span class="text-secondary fst-italic">Belum ada penanggung jawab. Tugas bisa diambil peserta.</span>
                                                                    @endif
                                                                </div>
                                                                @if ($task->tanggal_selesai)
                                                                    <div class="text-muted" style="font-size:0.7rem;">
                                                                        <i class="fa-regular fa-clock me-1"></i>Batas: {{ $task->tanggal_selesai->format('d M Y') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="border-color:var(--border)!important;">
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 px-2 py-1" style="font-size:0.75rem;" 
                                                data-id="{{ $module->id }}"
                                                data-nama="{{ e($module->nama) }}"
                                                data-bobot="{{ $module->bobot }}"
                                                data-tanggal-mulai="{{ $module->tanggal_mulai ? $module->tanggal_mulai->format('Y-m-d') : '' }}"
                                                data-tanggal-selesai="{{ $module->tanggal_selesai ? $module->tanggal_selesai->format('Y-m-d') : '' }}"
                                                data-project-start="{{ $project->tanggal_mulai->format('Y-m-d') }}"
                                                data-project-end="{{ $project->tanggal_selesai->format('Y-m-d') }}"
                                                data-project-nama="{{ e($project->nama) }}"
                                                data-deskripsi="{{ e($module->deskripsi ?? '') }}"
                                                onclick="editProjectModule(this)">
                                                <i class="fa-solid fa-pen"></i> Edit Modul
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
                                <div class="fw-bold mb-2 text-dark" style="font-size:0.86rem;"><i class="fa-solid fa-folder-open text-muted me-1"></i> Tugas Tersedia (Belum Diambil)</div>
                                <div class="row g-2">
                                    @forelse ($projectTasks->where('status', 'belum_dikerjakan') as $task)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="p-2.5 border rounded bg-light position-relative">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="badge bg-secondary text-white" style="font-size:0.65rem; font-weight:700;">Modul: {{ $task->module?->nama ?? '-' }}</span>
                                                    <form action="{{ route('admin.project.task.destroy', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.75rem; background:none; line-height:1;" title="Hapus tugas" onclick="return confirm('Hapus tugas ini?')">
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
                                                        <option value="" disabled selected>Pilih penanggung jawab...</option>
                                                        @foreach ($projectMembers as $member)
                                                            <option value="{{ $member->id }}">{{ $member->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-primary py-0.5 px-2 text-white fw-bold" style="font-size:0.72rem;">Tetapkan</button>
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
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.75rem; background:none; line-height:1;" title="Hapus tugas" onclick="return confirm('Hapus tugas ini?')">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="kanban-card-title">{{ $task->judul }}</div>
                                                <div class="text-muted mt-1 d-flex align-items-center gap-1" style="font-size:0.74rem;">
                                                    Penanggung jawab: <strong class="text-dark">{{ $task->user->nama ?? '-' }}</strong>
                                                    <form action="{{ route('admin.project.task.unassign_pic', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.72rem; background:none; line-height:1;" title="Lepas penanggung jawab">
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
                                                        <span class="badge bg-success-subtle text-success py-0.5 px-1.5 rounded" style="font-size:0.6rem; font-weight:800;">DISETUJUI</span>
                                                        <form action="{{ route('admin.project.task.destroy', $task) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-0 text-danger" style="font-size:0.75rem; background:none; line-height:1;" title="Hapus tugas" onclick="return confirm('Hapus tugas ini?')">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="kanban-card-title text-decoration-line-through text-muted">{{ $task->judul }}</div>
                                                <div class="text-muted mt-1" style="font-size:0.74rem;">Penanggung jawab: <strong class="text-dark">{{ $task->user->nama ?? '-' }}</strong></div>
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
                    <h6>Belum ada timeline proyek</h6>
                    <p class="text-muted small">Buat proyek pertama dari formulir di atas.</p>
                </div>
            @endforelse
        </div>

        <!-- SECTION: Log Aktivitas Magang (Activity Log) -->
        <div class="admin-card p-4 mt-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-clock-rotate-left me-1" style="color:var(--primary);"></i> Riwayat Aktivitas Magang
                </h6>
                <span class="monitor-pill bg-light text-primary border">{{ $activityLogs->count() }} Aktivitas</span>
            </div>
            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
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
    </div>

    @if ($isSuperAdmin)
    {{-- TAB: Kelola Bidang --}}
    <div class="tab-panel {{ $activeAdminTab === 'bidang' ? '' : 'd-none' }}" id="panel-bidang">
        {{-- Card 1: Daftar Bidang Magang --}}
        <div class="admin-card overflow-hidden">
            <div class="p-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="fw-bold mb-1" style="color:var(--dark);">
                        <i class="fa-solid fa-layer-group me-1" style="color:var(--primary);"></i> Daftar Bidang Magang
                    </h6>
                    <p class="text-muted mb-0" style="font-size:0.8rem;">
                        Kelola bidang atau divisi magang untuk pengelompokan tugas dan pembimbing.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light border text-secondary fw-semibold px-2.5 py-1.5" style="font-size:0.75rem;" id="bidangCountBadge">
                        {{ $bidangs->count() }} Bidang
                    </span>
                    <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addBidangModal">
                        <i class="fa-solid fa-plus"></i> Tambah Bidang
                    </button>
                </div>
            </div>

            @if ($bidangs->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada bidang magang terdaftar</h6>
                    <p>Tambahkan bidang magang baru untuk dapat memilihnya saat mendaftarkan anak magang.</p>
                </div>
            @else
                {{-- Search Bar & Filter / Dropdown Controls --}}
                <div class="p-4 pt-3 pb-0">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-{{ $bidangs->count() > 5 ? '6' : '12' }}">
                            <div class="search-wrap w-100">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="searchBidangInput" class="search-input" placeholder="Cari nama bidang magang...">
                            </div>
                        </div>
                        @if ($bidangs->count() > 5)
                            <div class="col-md-3">
                                <select id="dropdownBidangSelect" class="form-select form-select-sm filter-select w-100" style="font-size:0.82rem; height:42px; border-radius:10px;">
                                    <option value="">-- Dropdown / Pilih Bidang ({{ $bidangs->count() }}) --</option>
                                    @foreach ($bidangs as $b)
                                        <option value="{{ strtolower($b->nama) }}">{{ $b->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="limitBidangSelect" class="form-select form-select-sm filter-select w-100" style="font-size:0.82rem; height:42px; border-radius:10px;">
                                    <option value="5" selected>Tampilkan 5 per halaman</option>
                                    <option value="10">Tampilkan 10 per halaman</option>
                                    <option value="all">Tampilkan Semua ({{ $bidangs->count() }})</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="data-table mb-0" id="tableBidang">
                        <thead>
                            <tr>
                                <th style="width:10%;" class="text-center">No</th>
                                <th>Nama Bidang</th>
                                <th style="width:20%;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBidang">
                            @php $bidangRowNumber = 1; @endphp
                            @foreach ($bidangs as $b)
                                <tr class="bidang-row" data-name="{{ strtolower($b->nama) }}">
                                    <td class="text-muted fw-semibold text-center row-number">{{ $bidangRowNumber++ }}</td>
                                    <td class="fw-semibold" style="color:var(--dark);">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-primary border p-2 rounded-2" style="font-size:0.75rem;">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </span>
                                            <span class="bidang-name-text">{{ $b->nama }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
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
                    <div id="noBidangFound" class="text-center py-4 text-muted d-none">
                        <i class="fa-solid fa-magnifying-glass mb-2" style="font-size:1.5rem; opacity:0.5;"></i>
                        <p class="mb-0" style="font-size:0.85rem;">Bidang tidak ditemukan dengan kata kunci pencarian tersebut.</p>
                    </div>
                </div>

                @if ($bidangs->count() > 5)
                    <div class="p-3 border-top bg-light bg-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-2" id="bidangPaginationWrapper">
                        <div class="text-muted small" id="bidangPaginationInfo">
                            Menampilkan <span class="fw-bold text-dark" id="bidangShowingCount">5</span> dari <span class="fw-bold text-dark" id="bidangTotalCount">{{ $bidangs->count() }}</span> bidang
                        </div>
                        <div class="btn-group btn-group-sm" id="bidangPaginationControls">
                            <button type="button" class="btn btn-outline-secondary" id="btnPrevBidang" disabled>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <span class="btn btn-light text-dark disabled fw-semibold" id="pageLabelBidang">Halaman 1</span>
                            <button type="button" class="btn btn-outline-secondary" id="btnNextBidang">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Card 2: Daftar Pembimbing Magang --}}
        <div class="admin-card overflow-hidden mt-4">
            <div class="p-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="fw-bold mb-1" style="color:var(--dark);">
                        <i class="fa-solid fa-user-tie me-1" style="color:var(--primary);"></i> Daftar Pembimbing Magang
                    </h6>
                    <p class="text-muted mb-0" style="font-size:0.8rem;">
                        Kelola data pembimbing magang yang mendampingi peserta pada tiap bidang.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light border text-secondary fw-semibold px-2.5 py-1.5" style="font-size:0.75rem;" id="pembimbingCountBadge">
                        {{ $pembimbingMagangs->count() }} Pembimbing
                    </span>
                    <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addPembimbingModal">
                        <i class="fa-solid fa-plus"></i> Tambah Pembimbing
                    </button>
                </div>
            </div>

            @if ($pembimbingMagangs->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada pembimbing magang terdaftar</h6>
                    <p>Tambahkan pembimbing agar peserta dapat memilihnya saat daftar akun.</p>
                </div>
            @else
                {{-- Search Bar & Filter / Dropdown Controls --}}
                <div class="p-4 pt-3 pb-0">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-{{ $pembimbingMagangs->count() > 5 ? '5' : '7' }}">
                            <div class="search-wrap w-100">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="searchPembimbingInput" class="search-input" placeholder="Cari nama pembimbing atau bidang...">
                            </div>
                        </div>
                        <div class="col-md-{{ $pembimbingMagangs->count() > 5 ? '4' : '5' }}">
                            <select id="dropdownPembimbingBidang" class="form-select form-select-sm filter-select w-100" style="font-size:0.82rem; height:42px; border-radius:10px;">
                                <option value="">-- Semua Bidang Pembimbing --</option>
                                @foreach ($bidangs as $b)
                                    <option value="{{ strtolower($b->nama) }}">{{ $b->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($pembimbingMagangs->count() > 5)
                            <div class="col-md-3">
                                <select id="limitPembimbingSelect" class="form-select form-select-sm filter-select w-100" style="font-size:0.82rem; height:42px; border-radius:10px;">
                                    <option value="5" selected>Tampilkan 5 per halaman</option>
                                    <option value="10">Tampilkan 10 per halaman</option>
                                    <option value="all">Tampilkan Semua ({{ $pembimbingMagangs->count() }})</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="data-table mb-0" id="tablePembimbing">
                        <thead>
                            <tr>
                                <th style="width:10%;" class="text-center">No</th>
                                <th>Nama Pembimbing</th>
                                <th>Bidang Magang</th>
                                <th style="width:20%;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPembimbing">
                            @foreach ($pembimbingMagangs as $index => $pembimbing)
                                <tr class="pembimbing-row" data-name="{{ strtolower($pembimbing->nama) }}" data-bidang="{{ strtolower($pembimbing->bidang->nama ?? '') }}">
                                    <td class="text-muted fw-semibold text-center row-number">{{ $index + 1 }}</td>
                                    <td class="fw-semibold" style="color:var(--dark);">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle-sm rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:28px; height:28px; font-size:0.75rem; background:rgba(99,102,241,0.1); color:var(--primary); border:1px solid rgba(99,102,241,0.2);">
                                                {{ strtoupper(substr($pembimbing->nama, 0, 1)) }}
                                            </div>
                                            <span>{{ $pembimbing->nama }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light border text-dark" style="font-size:0.75rem; font-weight:500;">
                                            {{ $pembimbing->bidang->nama ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
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
                    <div id="noPembimbingFound" class="text-center py-4 text-muted d-none">
                        <i class="fa-solid fa-magnifying-glass mb-2" style="font-size:1.5rem; opacity:0.5;"></i>
                        <p class="mb-0" style="font-size:0.85rem;">Pembimbing tidak ditemukan dengan kata kunci pencarian tersebut.</p>
                    </div>
                </div>

                @if ($pembimbingMagangs->count() > 5)
                    <div class="p-3 border-top bg-light bg-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-2" id="pembimbingPaginationWrapper">
                        <div class="text-muted small" id="pembimbingPaginationInfo">
                            Menampilkan <span class="fw-bold text-dark" id="pembimbingShowingCount">5</span> dari <span class="fw-bold text-dark" id="pembimbingTotalCount">{{ $pembimbingMagangs->count() }}</span> pembimbing
                        </div>
                        <div class="btn-group btn-group-sm" id="pembimbingPaginationControls">
                            <button type="button" class="btn btn-outline-secondary" id="btnPrevPembimbing" disabled>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <span class="btn btn-light text-dark disabled fw-semibold" id="pageLabelPembimbing">Halaman 1</span>
                            <button type="button" class="btn btn-outline-secondary" id="btnNextPembimbing">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal: Tambah Tim Baru --}}
<div class="modal fade modal-clean" id="modalTambahTim" tabindex="-1" aria-labelledby="modalTambahTimLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" id="modalTambahTimLabel" style="color:var(--dark);">
                    <i class="fa-solid fa-people-group text-primary me-2"></i>Tambah Tim Baru
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <p class="text-muted mb-3" style="font-size:0.8rem;">
                Masukkan kode atau nama tim baru yang ingin ditambahkan (misal: <strong>C</strong>, <strong>D</strong>, atau nama tim lainnya).
            </p>
            <form action="{{ route('admin.jadwal.team.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Kode / Nama Tim <span class="text-danger">*</span></label>
                    <input type="text" name="nama_tim" class="form-control form-control-admin w-100" placeholder="Contoh: C atau D" maxlength="20" required autofocus>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Tim</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
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
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Status Akun</label>
                        <select name="status_akun" class="form-select form-control-admin w-100">
                            <option value="aktif" selected>Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Grup / Tim <span class="text-danger">*</span></label>
                        <select name="grup" class="form-select form-control-admin w-100" required>
                            @foreach ($availableTeams as $tOption)
                                <option value="{{ $tOption }}" {{ $loop->first ? 'selected' : '' }}>Tim {{ $tOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                        <select name="bidang_id" id="add_bidang_id" class="form-select form-control-admin w-100 js-bidang-select" data-pembimbing-target="add_pembimbing_magang_id" required>
                            <option value="" disabled selected>Pilih bidang...</option>
                            @foreach ($manageableBidangs as $b)
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
                            @foreach ($manageableBidangs as $b)
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

{{-- Modal: Pilih Anggota Proyek Baru --}}
<div class="modal fade modal-clean" id="createProjectMembersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-users me-2" style="color:var(--primary);"></i>Pilih Anggota Proyek</h5>
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

{{-- Modal: Pilih Anggota Edit Proyek --}}
<div class="modal fade modal-clean" id="editProjectMembersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-users me-2" style="color:var(--primary);"></i>Edit Anggota Proyek</h5>
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

{{-- Modal: Edit Proyek --}}
<div class="modal fade modal-clean" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-gantt me-2" style="color:var(--primary);"></i>Edit Proyek</h5>
            <form action="" method="POST" id="editProjectForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Peserta Magang Proyek</label>
                    <button type="button" class="member-selector" data-bs-toggle="modal" data-bs-target="#editProjectMembersModal">
                        <span class="member-selector-top">
                            <span><i class="fa-solid fa-users me-1" style="color:var(--primary);"></i> Pilih anggota proyek</span>
                            <span class="member-count" id="edit_member_count">0 dipilih</span>
                        </span>
                        <span class="member-preview" id="edit_member_preview">
                            <span class="member-placeholder">Klik untuk membuka daftar anggota</span>
                        </span>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label-admin">Nama Proyek <span class="text-danger">*</span></label>
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
                    <label class="form-label-admin">Kebutuhan Proyek</label>
                    <textarea name="kebutuhan" id="ep_kebutuhan" rows="3" class="form-control form-control-admin w-100"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Proyek</button>
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
            <h5 class="fw-bold mb-2"><i class="fa-solid fa-layer-group me-2" style="color:var(--primary);"></i>Edit Modul</h5>
            <div class="mb-3 p-2.5 rounded-3 border d-flex align-items-center gap-2" style="background:#f8fafc; font-size:0.82rem; border-color:var(--border)!important;">
                <i class="fa-solid fa-calendar-check text-primary"></i>
                <div>Rentang timeline proyek <strong id="em_project_name" class="text-dark"></strong>: <strong id="em_project_range" class="text-primary"></strong></div>
            </div>
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
                    <input type="text" name="nama" class="form-control form-control-admin w-100" placeholder="Contoh: Bidang Aplikasi Informatika" required>
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
                    <input type="text" name="nama" id="eb_nama" class="form-control form-control-admin w-100" placeholder="Contoh: Bidang Aplikasi Informatika" required>
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

{{-- Modal: Tambah Tugas Baru --}}
<div class="modal fade modal-clean" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus me-2" style="color:var(--primary);"></i>Tambah Tugas Baru</h5>
            <form action="{{ route('admin.project.task.store') }}" method="POST" id="addTaskForm">
                @csrf
                <input type="hidden" name="project_id" id="at_project_id">
                <input type="hidden" name="module_id" id="at_module_id" required>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Modul Terpilih</label>
                        <div class="p-2 px-3 rounded-3 border bg-light d-flex align-items-center justify-content-between" style="min-height: 42px; border-color: var(--border) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-cubes text-primary"></i>
                                <span id="at_module_name_display" class="fw-bold text-dark">-</span>
                            </div>
                            <span id="at_module_dates_badge" class="badge bg-white text-muted border" style="font-size:0.7rem;">-</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control form-control-admin w-100" placeholder="Contoh: Membuat rancangan ERD" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Penanggung Jawab Tugas (Opsional - Bisa Diambil Mandiri oleh Peserta)</label>
                        <select name="user_id" id="at_user_id" class="form-select form-control-admin w-100">
                            <!-- populated by js -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-admin">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-admin w-100">
                        <div id="at_hint_start" class="text-muted small mt-1" style="font-size:0.68rem;"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-admin">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-admin w-100">
                        <div id="at_hint_end" class="text-muted small mt-1" style="font-size:0.68rem;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label-admin">Deskripsi Tugas</label>
                        <textarea name="deskripsi" rows="3" class="form-control form-control-admin w-100" placeholder="Deskripsi tugas atau hasil yang diharapkan..."></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan Tugas</button>
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
        timeline: document.getElementById('panel-timeline'),
        sertifikat: document.getElementById('panel-sertifikat'),
        @if ($isSuperAdmin)
        rekap: document.getElementById('panel-rekap'),
        jadwal: document.getElementById('panel-jadwal'),
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

        try {
            sessionStorage.setItem('admin_active_tab', name);
            localStorage.setItem('admin_active_tab', name);
        } catch (e) {}

        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', name);
            if (name !== 'jadwal') {
                url.searchParams.delete('view');
            }
            window.history.replaceState({}, '', url);
        } catch (e) {}
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    const urlParams = new URLSearchParams(window.location.search);
    const urlTab = urlParams.get('tab');
    const storedTab = sessionStorage.getItem('admin_active_tab') || localStorage.getItem('admin_active_tab');
    let activeTab = (urlTab && panels[urlTab]) ? urlTab : ((storedTab && panels[storedTab]) ? storedTab : @json($activeAdminTab));

    if (activeTab && panels[activeTab]) {
        switchTab(activeTab);
    }

    const activeJadwalView = new URLSearchParams(window.location.search).get('view');
    if (activeJadwalView === 'team') {
        switchJadwalView('team');
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
    initKelolaBidangFilters();
    initTimelineDateRestrictions();

});

function confirmRandomSchedule(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Acak jadwal mingguan normal?',
        text: 'Pola selang-seling pasangan (Senin-Rabu dan Selasa-Kamis) akan diacak untuk setiap peserta magang secara perorangan. Hari Jumat tetap WFH.',
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

function confirmRandomTeamSchedule(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Acak jadwal tim serentak?',
        text: 'Jadwal kerja WFO & WFH bergantian (Senin & Rabu vs Selasa & Kamis) akan diterapkan serentak untuk seluruh anggota Tim A dan Tim B. Hari Jumat tetap WFH.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, acak serentak!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) e.target.submit(); });
    return false;
}

function confirmRandomTeamMembers(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Acak pembagian anggota tim?',
        text: 'Seluruh peserta magang akan diacak dan dibagi seimbang (50:50) ke dalam Tim A dan Tim B.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, acak anggota!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) e.target.submit(); });
    return false;
}

function switchJadwalView(view) {
    const normalView = document.getElementById('jadwal-view-normal');
    const teamView = document.getElementById('jadwal-view-team');
    const btnNormal = document.getElementById('btnViewNormal');
    const btnTeam = document.getElementById('btnViewTeam');

    if (view === 'team') {
        if (normalView) normalView.classList.add('d-none');
        if (teamView) teamView.classList.remove('d-none');
        if (btnNormal) {
            btnNormal.className = 'btn btn-sm px-3 fw-semibold transition-all btn-light text-secondary border-0 bg-transparent';
        }
        if (btnTeam) {
            btnTeam.className = 'btn btn-sm px-3 fw-semibold transition-all btn-primary text-white shadow-sm';
        }
        const url = new URL(window.location);
        url.searchParams.set('tab', 'jadwal');
        url.searchParams.set('view', 'team');
        window.history.replaceState({}, '', url);
    } else {
        if (teamView) teamView.classList.add('d-none');
        if (normalView) normalView.classList.remove('d-none');
        if (btnNormal) {
            btnNormal.className = 'btn btn-sm px-3 fw-semibold transition-all btn-primary text-white shadow-sm';
        }
        if (btnTeam) {
            btnTeam.className = 'btn btn-sm px-3 fw-semibold transition-all btn-light text-secondary border-0 bg-transparent';
        }
        const url = new URL(window.location);
        url.searchParams.set('tab', 'jadwal');
        url.searchParams.set('view', 'normal');
        window.history.replaceState({}, '', url);
    }
}

function editUser(id, nama, email, pembimbingMagangId, bidangId, tanggalMulaiMagang, tanggalSelesaiMagang, statusAkun, grup) {
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
    if (document.getElementById('e_grup')) {
        document.getElementById('e_grup').value = grup || 'A';
    }
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
        Swal.fire('Pilih peserta magang', 'Pilih minimal satu peserta magang untuk proyek ini.', 'warning');
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
                Swal.fire('Peserta dari proyek berbeda', 'Pindahkan peserta dari baris proyek yang sama.', 'warning');
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
            throw new Error(payload.message || 'Penjadwalan peserta gagal disimpan.');
        }

        renderAssignmentChip(day, payload);
        openNoteModal(
            payload.project_id,
            day.closest('.project-row')?.querySelector('.project-row-header h6')?.innerText || 'Proyek',
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
            throw new Error(payload.message || 'Penjadwalan peserta gagal dihapus.');
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

function formatDateId(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return dateStr;
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
    
    const epStart = document.getElementById('ep_tanggal_mulai');
    const epEnd = document.getElementById('ep_tanggal_selesai');
    epStart.value = tanggalMulai;
    epEnd.value = tanggalSelesai;
    epEnd.min = tanggalMulai;

    document.getElementById('ep_status').value = status || 'aktif';
    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}

function editProjectTimeline(button) {
    const form = document.getElementById('editTimelineForm');
    form.action = "{{ url('admin/project/timeline/update') }}/" + button.dataset.id;
    document.getElementById('et_nama').value = button.dataset.nama || '';
    
    const etStart = document.getElementById('et_tanggal_mulai');
    const etEnd = document.getElementById('et_tanggal_selesai');
    etStart.value = button.dataset.tanggalMulai || '';
    etEnd.value = button.dataset.tanggalSelesai || '';
    if (button.dataset.tanggalMulai) {
        etEnd.min = button.dataset.tanggalMulai;
    }

    document.getElementById('et_status').value = button.dataset.status || 'belum_dimulai';
    new bootstrap.Modal(document.getElementById('editTimelineModal')).show();
}

function editProjectModule(button) {
    const form = document.getElementById('editModuleForm');
    form.action = "{{ url('admin/project/module/update') }}/" + button.dataset.id;
    document.getElementById('em_nama').value = button.dataset.nama || '';
    document.getElementById('em_bobot').value = button.dataset.bobot || 0;

    const projectStart = button.dataset.projectStart || '';
    const projectEnd = button.dataset.projectEnd || '';
    const projectNama = button.dataset.projectNama || 'Proyek';
    const moduleStart = button.dataset.tanggalMulai || '';
    const moduleEnd = button.dataset.tanggalSelesai || '';

    const startInput = document.getElementById('em_tanggal_mulai');
    const endInput = document.getElementById('em_tanggal_selesai');

    if (projectStart) startInput.min = projectStart;
    if (projectEnd) startInput.max = projectEnd;
    startInput.value = moduleStart;

    endInput.min = moduleStart || projectStart;
    if (projectEnd) endInput.max = projectEnd;
    endInput.value = moduleEnd;

    document.getElementById('em_project_name').textContent = projectNama;
    document.getElementById('em_project_range').textContent = (projectStart ? formatDateId(projectStart) : '-') + ' s/d ' + (projectEnd ? formatDateId(projectEnd) : '-');

    form.dataset.projectStart = projectStart;
    form.dataset.projectEnd = projectEnd;
    form.dataset.projectName = projectNama;

    document.getElementById('em_deskripsi').value = button.dataset.deskripsi || '';

    new bootstrap.Modal(document.getElementById('editModuleModal')).show();
}

function initTimelineDateRestrictions() {
    // 1. Create Project Form Date Sync
    const cpStart = document.getElementById('cp_tanggal_mulai');
    const cpEnd = document.getElementById('cp_tanggal_selesai');
    const cpForm = document.getElementById('createProjectForm');

    if (cpStart && cpEnd) {
        cpStart.addEventListener('change', function() {
            if (this.value) {
                cpEnd.min = this.value;
                if (cpEnd.value && cpEnd.value < this.value) {
                    cpEnd.value = this.value;
                }
            }
        });
    }

    if (cpForm) {
        cpForm.addEventListener('submit', function(e) {
            if (cpStart && cpEnd && cpStart.value && cpEnd.value) {
                if (cpEnd.value < cpStart.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai proyek tidak boleh lebih awal dari tanggal mulai proyek.',
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
            }
        });
    }

    // 2. Edit Project Form Date Sync
    const epStart = document.getElementById('ep_tanggal_mulai');
    const epEnd = document.getElementById('ep_tanggal_selesai');
    const epForm = document.getElementById('editProjectForm');

    if (epStart && epEnd) {
        epStart.addEventListener('change', function() {
            if (this.value) {
                epEnd.min = this.value;
                if (epEnd.value && epEnd.value < this.value) {
                    epEnd.value = this.value;
                }
            }
        });
    }

    if (epForm) {
        epForm.addEventListener('submit', function(e) {
            if (epStart && epEnd && epStart.value && epEnd.value) {
                if (epEnd.value < epStart.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai proyek tidak boleh lebih awal dari tanggal mulai proyek.',
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
            }
        });
    }

    // 3. Edit Timeline Form Date Sync
    const etStart = document.getElementById('et_tanggal_mulai');
    const etEnd = document.getElementById('et_tanggal_selesai');
    const etForm = document.getElementById('editTimelineForm');

    if (etStart && etEnd) {
        etStart.addEventListener('change', function() {
            if (this.value) {
                etEnd.min = this.value;
                if (etEnd.value && etEnd.value < this.value) {
                    etEnd.value = this.value;
                }
            }
        });
    }

    if (etForm) {
        etForm.addEventListener('submit', function(e) {
            if (etStart && etEnd && etStart.value && etEnd.value) {
                if (etEnd.value < etStart.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai timeline tidak boleh lebih awal dari tanggal mulai.',
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
            }
        });
    }

    // 4. Create Module Form Date Sync & Boundaries
    document.querySelectorAll('.js-module-create-form').forEach(form => {
        const pStart = form.dataset.projectStart;
        const pEnd = form.dataset.projectEnd;
        const pName = form.dataset.projectName || 'Proyek';
        const startInput = form.querySelector('.js-module-start-input');
        const endInput = form.querySelector('.js-module-end-input');

        if (startInput && endInput) {
            if (pStart) {
                startInput.min = pStart;
                endInput.min = pStart;
            }
            if (pEnd) {
                startInput.max = pEnd;
                endInput.max = pEnd;
            }

            startInput.addEventListener('change', function() {
                if (pStart && this.value && this.value < pStart) {
                    this.value = pStart;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal mulai modul tidak boleh sebelum tanggal mulai proyek (${formatDateId(pStart)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                }
                if (pEnd && this.value && this.value > pEnd) {
                    this.value = pEnd;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal mulai modul tidak boleh melebihi tanggal selesai proyek (${formatDateId(pEnd)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                }
                if (this.value) {
                    endInput.min = this.value;
                    if (endInput.value && endInput.value < this.value) {
                        endInput.value = this.value;
                    }
                }
            });

            endInput.addEventListener('change', function() {
                if (startInput.value && this.value && this.value < startInput.value) {
                    this.value = startInput.value;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai modul tidak boleh lebih awal dari tanggal mulai modul.',
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                }
                if (pEnd && this.value && this.value > pEnd) {
                    this.value = pEnd;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal selesai modul tidak boleh melebihi tanggal selesai proyek (${formatDateId(pEnd)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                }
            });
        }

        form.addEventListener('submit', function(e) {
            if (startInput && endInput && startInput.value && endInput.value) {
                if (pStart && startInput.value < pStart) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal mulai modul tidak boleh lebih awal dari tanggal mulai proyek (${formatDateId(pStart)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
                if (pEnd && endInput.value > pEnd) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal selesai modul tidak boleh melebihi tanggal selesai proyek (${formatDateId(pEnd)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
                if (endInput.value < startInput.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai modul tidak boleh lebih awal dari tanggal mulai modul.',
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
            }
        });
    });

    // 5. Edit Module Modal Form Date Boundaries
    const emStart = document.getElementById('em_tanggal_mulai');
    const emEnd = document.getElementById('em_tanggal_selesai');
    const emForm = document.getElementById('editModuleForm');

    if (emStart && emEnd) {
        emStart.addEventListener('change', function() {
            const pStart = emForm.dataset.projectStart;
            const pEnd = emForm.dataset.projectEnd;

            if (pStart && this.value && this.value < pStart) {
                this.value = pStart;
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal di Luar Rentang Proyek',
                    text: `Tanggal mulai modul tidak boleh sebelum tanggal mulai proyek (${formatDateId(pStart)}).`,
                    customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                });
            }
            if (pEnd && this.value && this.value > pEnd) {
                this.value = pEnd;
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal di Luar Rentang Proyek',
                    text: `Tanggal mulai modul tidak boleh melebihi tanggal selesai proyek (${formatDateId(pEnd)}).`,
                    customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                });
            }
            if (this.value) {
                emEnd.min = this.value;
                if (emEnd.value && emEnd.value < this.value) {
                    emEnd.value = this.value;
                }
            }
        });

        emEnd.addEventListener('change', function() {
            const pEnd = emForm.dataset.projectEnd;

            if (emStart.value && this.value && this.value < emStart.value) {
                this.value = emStart.value;
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: 'Tanggal selesai modul tidak boleh lebih awal dari tanggal mulai modul.',
                    customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                });
            }
            if (pEnd && this.value && this.value > pEnd) {
                this.value = pEnd;
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal di Luar Rentang Proyek',
                    text: `Tanggal selesai modul tidak boleh melebihi tanggal selesai proyek (${formatDateId(pEnd)}).`,
                    customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                });
            }
        });
    }

    if (emForm) {
        emForm.addEventListener('submit', function(e) {
            const pStart = emForm.dataset.projectStart;
            const pEnd = emForm.dataset.projectEnd;

            if (emStart && emEnd && emStart.value && emEnd.value) {
                if (pStart && emStart.value < pStart) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal mulai modul tidak boleh lebih awal dari tanggal mulai proyek (${formatDateId(pStart)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
                if (pEnd && emEnd.value > pEnd) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal di Luar Rentang Proyek',
                        text: `Tanggal selesai modul tidak boleh melebihi tanggal selesai proyek (${formatDateId(pEnd)}).`,
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
                if (emEnd.value < emStart.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai modul tidak boleh lebih awal dari tanggal mulai modul.',
                        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3' }
                    });
                    return false;
                }
            }
        });
    }
}

function confirmProjectDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus proyek?',
        text: 'Semua timeline, modul, dan catatan proyek ini akan dihapus permanen.',
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
        text: 'Penanggung jawab modul akan dilepas dan modul ini akan dihapus permanen.',
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

function initKelolaBidangFilters() {
    // 1. Bidang Table Filtering & Pagination
    const searchBidangInput = document.getElementById('searchBidangInput');
    const dropdownBidangSelect = document.getElementById('dropdownBidangSelect');
    const limitBidangSelect = document.getElementById('limitBidangSelect');
    const tbodyBidang = document.getElementById('tbodyBidang');
    const noBidangFound = document.getElementById('noBidangFound');
    const bidangPaginationWrapper = document.getElementById('bidangPaginationWrapper');
    const bidangShowingCount = document.getElementById('bidangShowingCount');
    const bidangTotalCount = document.getElementById('bidangTotalCount');
    const btnPrevBidang = document.getElementById('btnPrevBidang');
    const btnNextBidang = document.getElementById('btnNextBidang');
    const pageLabelBidang = document.getElementById('pageLabelBidang');

    let currentBidangPage = 1;

    function renderBidangTable() {
        if (!tbodyBidang) return;
        const rows = Array.from(tbodyBidang.querySelectorAll('.bidang-row'));
        const searchQuery = (searchBidangInput ? searchBidangInput.value : '').toLowerCase().trim();
        const dropdownQuery = (dropdownBidangSelect ? dropdownBidangSelect.value : '').toLowerCase().trim();
        const pageSize = limitBidangSelect ? (limitBidangSelect.value === 'all' ? rows.length : parseInt(limitBidangSelect.value, 10)) : rows.length;

        const matchingRows = rows.filter(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const matchSearch = searchQuery === '' || name.includes(searchQuery);
            const matchDropdown = dropdownQuery === '' || name === dropdownQuery || name.includes(dropdownQuery);
            return matchSearch && matchDropdown;
        });

        rows.forEach(r => r.classList.add('d-none'));

        if (matchingRows.length === 0) {
            if (noBidangFound) noBidangFound.classList.remove('d-none');
            if (bidangPaginationWrapper) bidangPaginationWrapper.classList.add('d-none');
            return;
        } else {
            if (noBidangFound) noBidangFound.classList.add('d-none');
        }

        const totalPages = Math.ceil(matchingRows.length / pageSize) || 1;
        if (currentBidangPage > totalPages) currentBidangPage = totalPages;
        if (currentBidangPage < 1) currentBidangPage = 1;

        const startIndex = (currentBidangPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, matchingRows.length);

        for (let i = startIndex; i < endIndex; i++) {
            const row = matchingRows[i];
            row.classList.remove('d-none');
            const numEl = row.querySelector('.row-number');
            if (numEl) numEl.textContent = i + 1;
        }

        if (bidangPaginationWrapper) {
            bidangPaginationWrapper.classList.toggle('d-none', matchingRows.length <= 5 && (!limitBidangSelect || limitBidangSelect.value === 'all'));
            if (bidangShowingCount) bidangShowingCount.textContent = (endIndex - startIndex);
            if (bidangTotalCount) bidangTotalCount.textContent = matchingRows.length;
            if (pageLabelBidang) pageLabelBidang.textContent = `Halaman ${currentBidangPage} / ${totalPages}`;
            if (btnPrevBidang) btnPrevBidang.disabled = (currentBidangPage <= 1);
            if (btnNextBidang) btnNextBidang.disabled = (currentBidangPage >= totalPages);
        }
    }

    if (searchBidangInput) {
        searchBidangInput.addEventListener('input', () => {
            currentBidangPage = 1;
            renderBidangTable();
        });
    }
    if (dropdownBidangSelect) {
        dropdownBidangSelect.addEventListener('change', () => {
            currentBidangPage = 1;
            renderBidangTable();
        });
    }
    if (limitBidangSelect) {
        limitBidangSelect.addEventListener('change', () => {
            currentBidangPage = 1;
            renderBidangTable();
        });
    }
    if (btnPrevBidang) {
        btnPrevBidang.addEventListener('click', () => {
            if (currentBidangPage > 1) {
                currentBidangPage--;
                renderBidangTable();
            }
        });
    }
    if (btnNextBidang) {
        btnNextBidang.addEventListener('click', () => {
            currentBidangPage++;
            renderBidangTable();
        });
    }

    // 2. Pembimbing Table Filtering & Pagination
    const searchPembimbingInput = document.getElementById('searchPembimbingInput');
    const dropdownPembimbingBidang = document.getElementById('dropdownPembimbingBidang');
    const limitPembimbingSelect = document.getElementById('limitPembimbingSelect');
    const tbodyPembimbing = document.getElementById('tbodyPembimbing');
    const noPembimbingFound = document.getElementById('noPembimbingFound');
    const pembimbingPaginationWrapper = document.getElementById('pembimbingPaginationWrapper');
    const pembimbingShowingCount = document.getElementById('pembimbingShowingCount');
    const pembimbingTotalCount = document.getElementById('pembimbingTotalCount');
    const btnPrevPembimbing = document.getElementById('btnPrevPembimbing');
    const btnNextPembimbing = document.getElementById('btnNextPembimbing');
    const pageLabelPembimbing = document.getElementById('pageLabelPembimbing');

    let currentPembimbingPage = 1;

    function renderPembimbingTable() {
        if (!tbodyPembimbing) return;
        const rows = Array.from(tbodyPembimbing.querySelectorAll('.pembimbing-row'));
        const searchQuery = (searchPembimbingInput ? searchPembimbingInput.value : '').toLowerCase().trim();
        const bidangQuery = (dropdownPembimbingBidang ? dropdownPembimbingBidang.value : '').toLowerCase().trim();
        const pageSize = limitPembimbingSelect ? (limitPembimbingSelect.value === 'all' ? rows.length : parseInt(limitPembimbingSelect.value, 10)) : rows.length;

        const matchingRows = rows.filter(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const bidang = (row.dataset.bidang || '').toLowerCase();
            const matchSearch = searchQuery === '' || name.includes(searchQuery) || bidang.includes(searchQuery);
            const matchBidang = bidangQuery === '' || bidang === bidangQuery;
            return matchSearch && matchBidang;
        });

        rows.forEach(r => r.classList.add('d-none'));

        if (matchingRows.length === 0) {
            if (noPembimbingFound) noPembimbingFound.classList.remove('d-none');
            if (pembimbingPaginationWrapper) pembimbingPaginationWrapper.classList.add('d-none');
            return;
        } else {
            if (noPembimbingFound) noPembimbingFound.classList.add('d-none');
        }

        const totalPages = Math.ceil(matchingRows.length / pageSize) || 1;
        if (currentPembimbingPage > totalPages) currentPembimbingPage = totalPages;
        if (currentPembimbingPage < 1) currentPembimbingPage = 1;

        const startIndex = (currentPembimbingPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, matchingRows.length);

        for (let i = startIndex; i < endIndex; i++) {
            const row = matchingRows[i];
            row.classList.remove('d-none');
            const numEl = row.querySelector('.row-number');
            if (numEl) numEl.textContent = i + 1;
        }

        if (pembimbingPaginationWrapper) {
            pembimbingPaginationWrapper.classList.toggle('d-none', matchingRows.length <= 5 && (!limitPembimbingSelect || limitPembimbingSelect.value === 'all'));
            if (pembimbingShowingCount) pembimbingShowingCount.textContent = (endIndex - startIndex);
            if (pembimbingTotalCount) pembimbingTotalCount.textContent = matchingRows.length;
            if (pageLabelPembimbing) pageLabelPembimbing.textContent = `Halaman ${currentPembimbingPage} / ${totalPages}`;
            if (btnPrevPembimbing) btnPrevPembimbing.disabled = (currentPembimbingPage <= 1);
            if (btnNextPembimbing) btnNextPembimbing.disabled = (currentPembimbingPage >= totalPages);
        }
    }

    if (searchPembimbingInput) {
        searchPembimbingInput.addEventListener('input', () => {
            currentPembimbingPage = 1;
            renderPembimbingTable();
        });
    }
    if (dropdownPembimbingBidang) {
        dropdownPembimbingBidang.addEventListener('change', () => {
            currentPembimbingPage = 1;
            renderPembimbingTable();
        });
    }
    if (limitPembimbingSelect) {
        limitPembimbingSelect.addEventListener('change', () => {
            currentPembimbingPage = 1;
            renderPembimbingTable();
        });
    }
    if (btnPrevPembimbing) {
        btnPrevPembimbing.addEventListener('click', () => {
            if (currentPembimbingPage > 1) {
                currentPembimbingPage--;
                renderPembimbingTable();
            }
        });
    }
    if (btnNextPembimbing) {
        btnNextPembimbing.addEventListener('click', () => {
            currentPembimbingPage++;
            renderPembimbingTable();
        });
    }

    renderBidangTable();
    renderPembimbingTable();
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

let currentProjectModules = [];

function openAddTaskModalWithModule(projectId, membersJson, modulesJson, moduleId) {
    document.getElementById('at_project_id').value = projectId;
    currentProjectModules = modulesJson || [];
    
    // Find the chosen module
    const selectedMod = currentProjectModules.find(m => String(m.id) === String(moduleId));
    const moduleIdInput = document.getElementById('at_module_id');
    const moduleNameDisplay = document.getElementById('at_module_name_display');
    const moduleDatesBadge = document.getElementById('at_module_dates_badge');
    
    if (moduleIdInput) moduleIdInput.value = moduleId;
    if (moduleNameDisplay && selectedMod) moduleNameDisplay.textContent = selectedMod.nama;
    if (moduleDatesBadge && selectedMod) {
        if (selectedMod.tanggal_mulai && selectedMod.tanggal_selesai) {
            moduleDatesBadge.textContent = selectedMod.tanggal_mulai + ' s/d ' + selectedMod.tanggal_selesai;
        } else {
            moduleDatesBadge.textContent = 'Modul Proyek';
        }
    }

    // Populate PIC user options
    const userSelect = document.getElementById('at_user_id');
    if (userSelect) {
        userSelect.innerHTML = '<option value="">-- Biarkan kosong agar bisa diambil peserta --</option>';
        (membersJson || []).forEach(u => {
            userSelect.insertAdjacentHTML('beforeend', `<option value="${u.id}">${escapeHtml(u.nama)}</option>`);
        });
    }

    // Reset task form inputs
    const form = document.getElementById('addTaskForm');
    if (form) {
        const judulInput = form.querySelector('input[name="judul"]');
        const deskripsiInput = form.querySelector('textarea[name="deskripsi"]');
        if (judulInput) judulInput.value = '';
        if (deskripsiInput) deskripsiInput.value = '';
    }

    // Date bounds
    const startInput = document.querySelector('#addTaskModal input[name="tanggal_mulai"]');
    const endInput = document.querySelector('#addTaskModal input[name="tanggal_selesai"]');
    const hintStart = document.getElementById('at_hint_start');
    const hintEnd = document.getElementById('at_hint_end');

    if (selectedMod && startInput && endInput) {
        if (selectedMod.tanggal_mulai) {
            startInput.min = selectedMod.tanggal_mulai;
            startInput.max = selectedMod.tanggal_selesai || '';
            startInput.value = selectedMod.tanggal_mulai;
            if (hintStart) hintStart.textContent = 'Min: ' + selectedMod.tanggal_mulai;
        } else {
            startInput.removeAttribute('min');
            startInput.removeAttribute('max');
            startInput.value = '';
            if (hintStart) hintStart.textContent = '';
        }

        if (selectedMod.tanggal_selesai) {
            endInput.min = selectedMod.tanggal_mulai || '';
            endInput.max = selectedMod.tanggal_selesai;
            endInput.value = selectedMod.tanggal_selesai;
            if (hintEnd) hintEnd.textContent = 'Maks: ' + selectedMod.tanggal_selesai;
        } else {
            endInput.removeAttribute('min');
            endInput.removeAttribute('max');
            endInput.value = '';
            if (hintEnd) hintEnd.textContent = '';
        }
    }

    new bootstrap.Modal(document.getElementById('addTaskModal')).show();
}

function openAddTaskModal(projectId, membersJson, modulesJson) {
    if (modulesJson && modulesJson.length > 0) {
        openAddTaskModalWithModule(projectId, membersJson, modulesJson, modulesJson[0].id);
    }
}

// Switch Jadwal View (Normal vs Team)
function switchJadwalView(mode) {
    const normalView = document.getElementById('jadwal-view-normal');
    const teamView = document.getElementById('jadwal-view-team');
    const btnNormal = document.getElementById('btnViewNormal');
    const btnTeam = document.getElementById('btnViewTeam');

    if (mode === 'team') {
        if (normalView) normalView.classList.add('d-none');
        if (teamView) teamView.classList.remove('d-none');
        if (btnNormal) {
            btnNormal.className = 'btn btn-sm px-3 fw-semibold transition-all btn-light text-secondary border-0 bg-transparent';
        }
        if (btnTeam) {
            btnTeam.className = 'btn btn-sm px-3 fw-semibold transition-all btn-primary text-white shadow-sm';
        }
        const url = new URL(window.location);
        url.searchParams.set('tab', 'jadwal');
        url.searchParams.set('view', 'team');
        window.history.replaceState({}, '', url);
    } else {
        if (normalView) normalView.classList.remove('d-none');
        if (teamView) teamView.classList.add('d-none');
        if (btnNormal) {
            btnNormal.className = 'btn btn-sm px-3 fw-semibold transition-all btn-primary text-white shadow-sm';
        }
        if (btnTeam) {
            btnTeam.className = 'btn btn-sm px-3 fw-semibold transition-all btn-light text-secondary border-0 bg-transparent';
        }
        const url = new URL(window.location);
        url.searchParams.set('tab', 'jadwal');
        url.searchParams.set('view', 'normal');
        window.history.replaceState({}, '', url);
    }
}

// Preserve scroll position on refresh and actions
window.addEventListener('beforeunload', function() {
    sessionStorage.setItem('admin_scroll_pos', window.scrollY);
});

document.addEventListener('DOMContentLoaded', function() {
    const savedPos = sessionStorage.getItem('admin_scroll_pos');
    if (savedPos !== null) {
        window.scrollTo({
            top: parseInt(savedPos, 10),
            behavior: 'instant'
        });
        sessionStorage.removeItem('admin_scroll_pos');
    }

    // Live auto-save & dynamic DOM reordering on team assignment select change
    const teamStylesMap = {
        'A': { bg: '#eff6ff', text: '#2563eb', border: '#bfdbfe', icon: '🔵' },
        'B': { bg: '#f0fdf4', text: '#16a34a', border: '#bbf7d0', icon: '🟢' },
        'C': { bg: '#faf5ff', text: '#9333ea', border: '#e9d5ff', icon: '🟣' },
        'D': { bg: '#fffbeb', text: '#d97706', border: '#fde68a', icon: '🟠' },
        'E': { bg: '#fff1f2', text: '#e11d48', border: '#fecdd3', icon: '🔴' },
        'F': { bg: '#ecfeff', text: '#0891b2', border: '#a5f3fc', icon: '🔷' },
    };

    function getTeamStyleConfig(team) {
        const t = String(team || 'A').toUpperCase().trim();
        return teamStylesMap[t] || { bg: '#f8fafc', text: '#475569', border: '#cbd5e1', icon: '⚪' };
    }

    function reorganizeTeamTable(highlightUserId = null) {
        const tbody = document.getElementById('teamMembersTableBody');
        if (!tbody) return;

        const userRows = Array.from(tbody.querySelectorAll('.user-team-row'));
        if (userRows.length === 0) return;

        // Remove old group header rows
        tbody.querySelectorAll('.team-group-header-row').forEach(el => el.remove());

        // Sort rows by team (natural comparison), then by user name
        userRows.sort((a, b) => {
            const teamA = (a.dataset.team || 'A').toUpperCase();
            const teamB = (b.dataset.team || 'A').toUpperCase();
            const cmp = teamA.localeCompare(teamB, undefined, { numeric: true, sensitivity: 'base' });
            if (cmp !== 0) return cmp;
            const nameA = (a.dataset.userName || '').toLowerCase();
            const nameB = (b.dataset.userName || '').toLowerCase();
            return nameA.localeCompare(nameB);
        });

        // Count members per team
        const teamCounts = {};
        userRows.forEach(row => {
            const team = (row.dataset.team || 'A').toUpperCase();
            teamCounts[team] = (teamCounts[team] || 0) + 1;
        });

        // Re-append header rows & user rows in grouped order
        let currentGroup = null;
        userRows.forEach((row, index) => {
            const team = (row.dataset.team || 'A').toUpperCase();
            if (currentGroup !== team) {
                currentGroup = team;
                const style = getTeamStyleConfig(currentGroup);
                const count = teamCounts[currentGroup] || 0;

                const headerRow = document.createElement('tr');
                headerRow.style.background = '#f8fafc';
                headerRow.className = 'team-group-header-row';
                headerRow.dataset.team = currentGroup;
                headerRow.innerHTML = `
                    <td colspan="5" class="py-2 px-3 fw-bold" style="font-size:0.82rem; color:${style.text}; border-top:2px solid ${style.border};">
                        <span class="badge me-1" style="background:${style.bg}; color:${style.text}; border:1px solid ${style.border}; font-size:0.78rem;">
                            ${style.icon} KELOMPOK TIM ${currentGroup}
                        </span>
                        <span class="text-muted fw-normal small team-group-member-count" data-team="${currentGroup}">(${count} Peserta)</span>
                    </td>
                `;
                tbody.appendChild(headerRow);
            }

            // Update row number
            const numEl = row.querySelector('.team-row-number');
            if (numEl) numEl.textContent = index + 1;

            // Update avatar badge styling
            const userId = row.dataset.userId;
            const avatarEl = document.getElementById('user-avatar-' + userId);
            if (avatarEl) {
                const style = getTeamStyleConfig(team);
                avatarEl.style.background = style.bg;
                avatarEl.style.color = style.text;
                avatarEl.style.borderColor = style.border;
            }

            tbody.appendChild(row);
        });

        // Smooth highlight for the moved participant row
        if (highlightUserId) {
            const movedRow = document.getElementById('user-row-' + highlightUserId);
            if (movedRow) {
                movedRow.style.transition = 'background-color 0.4s ease, box-shadow 0.4s ease';
                movedRow.style.backgroundColor = '#ecfdf5';
                movedRow.style.boxShadow = 'inset 0 0 0 2px #10b981';
                setTimeout(() => {
                    movedRow.style.backgroundColor = '';
                    movedRow.style.boxShadow = '';
                }, 1800);
            }
        }
    }

    const teamSelects = document.querySelectorAll('.js-team-assignment-select');
    teamSelects.forEach(select => {
        select.addEventListener('change', async function() {
            const userId = this.dataset.userId;
            const newGroup = this.value;
            const selectEl = this;

            // Visual feedback during request
            selectEl.disabled = true;
            selectEl.style.opacity = '0.6';

            try {
                const response = await fetch("{{ route('admin.jadwal.team.members') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        members: {
                            [userId]: {
                                grup: newGroup
                            }
                        }
                    })
                });

                if (response.ok) {
                    const data = await response.json();

                    // Update row's data-team attribute
                    const userRow = document.getElementById('user-row-' + userId);
                    if (userRow) {
                        userRow.dataset.team = newGroup;
                    }

                    // Dynamically move and reorganize the table without page reload
                    reorganizeTeamTable(userId);

                    // Update summary count badges dynamically
                    if (data.teamCounts) {
                        Object.entries(data.teamCounts).forEach(([team, count]) => {
                            const countEl = document.getElementById('team-count-' + team);
                            if (countEl) countEl.textContent = count;
                        });
                    }

                    // Update normal schedule table row if present
                    const normalRow = document.getElementById('normal-user-row-' + userId);
                    if (normalRow) {
                        normalRow.dataset.team = newGroup;
                        const style = getTeamStyleConfig(newGroup);
                        const normalAvatar = document.getElementById('normal-user-avatar-' + userId);
                        if (normalAvatar) {
                            normalAvatar.style.background = style.bg;
                            normalAvatar.style.color = style.text;
                            normalAvatar.style.borderColor = style.border;
                        }
                        const normalBadge = document.getElementById('normal-user-team-badge-' + userId);
                        if (normalBadge) {
                            normalBadge.style.background = style.bg;
                            normalBadge.style.color = style.text;
                            normalBadge.style.borderColor = style.border;
                            normalBadge.innerHTML = `${style.icon} Tim ${newGroup}`;
                        }
                    }

                    // Toast notification
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-3 shadow'
                        }
                    });
                    Toast.fire({
                        icon: 'success',
                        title: `Peserta berhasil dipindahkan ke Tim ${newGroup}`
                    });
                } else {
                    throw new Error('Gagal menyimpan penugasan tim.');
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: 'Terjadi gangguan saat menyimpan tim. Silakan coba lagi.'
                });
            } finally {
                selectEl.disabled = false;
                selectEl.style.opacity = '1';
            }
        });
    });

    // Filter and search logic for Tampilan Perorangan Schedule Table
    const filterNormalSearch = document.getElementById('filterNormalSearch');
    const normalScheduleTable = document.getElementById('normalScheduleTable');

    function applyNormalScheduleFilter() {
        if (!normalScheduleTable) return;
        const query = (filterNormalSearch?.value || '').toLowerCase().trim();
        const rows = normalScheduleTable.querySelectorAll('.normal-user-schedule-row');

        rows.forEach(row => {
            const userName = (row.dataset.userName || '').toLowerCase();
            const userBidang = (row.dataset.userBidang || '').toLowerCase();
            const matchesQuery = query === '' || userName.includes(query) || userBidang.includes(query);

            if (matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (filterNormalSearch) {
        filterNormalSearch.addEventListener('input', applyNormalScheduleFilter);
    }

    // Function to update landing page schedule view setting asynchronously
    window.updateLandingViewSetting = async function(mode) {
        const btnTeam = document.querySelector('.js-btn-landing-mode[onclick*="team"]');
        const btnIndiv = document.querySelector('.js-btn-landing-mode[onclick*="individual"]');
        const inputVal = document.getElementById('inputLandingView');
        if (inputVal) inputVal.value = mode;

        if (mode === 'team') {
            btnTeam?.classList.add('btn-primary', 'text-white', 'shadow-sm');
            btnTeam?.classList.remove('btn-light', 'text-secondary', 'border-0', 'bg-transparent');
            btnIndiv?.classList.remove('btn-primary', 'text-white', 'shadow-sm');
            btnIndiv?.classList.add('btn-light', 'text-secondary', 'border-0', 'bg-transparent');
        } else {
            btnIndiv?.classList.add('btn-primary', 'text-white', 'shadow-sm');
            btnIndiv?.classList.remove('btn-light', 'text-secondary', 'border-0', 'bg-transparent');
            btnTeam?.classList.remove('btn-primary', 'text-white', 'shadow-sm');
            btnTeam?.classList.add('btn-light', 'text-secondary', 'border-0', 'bg-transparent');
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                          document.querySelector('#landingViewForm input[name="_token"]')?.value;
            const res = await fetch("{{ route('admin.jadwal.landing_view') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ jadwal_landing_view: mode })
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pengaturan Disimpan',
                        text: 'Tampilan jadwal di halaman depan diubah ke: ' + (mode === 'team' ? 'Tampilan Tim' : 'Perorangan (Tanpa Tim)'),
                        timer: 2200,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            }
        } catch (err) {
            console.error(err);
        }
    };
});

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
