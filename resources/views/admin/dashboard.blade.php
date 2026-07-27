@extends('layouts.admin')

@section('title', 'Admin Dashboard - Absensi & Laporan Harian')

@section('styles')
<style>
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
        cursor: grab;
        user-select: none;
    }

    .employee-chip:active { cursor: grabbing; }

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
                <h1 class="fw-bold mb-0" style="font-size:1.35rem; letter-spacing:-0.3px; color:var(--dark);">Admin Dashboard</h1>
                <p class="mb-0 text-muted" style="font-size:0.85rem;">Absensi &amp; Laporan Harian</p>
            </div>
        </div>
        <a href="{{ route('admin.logout') }}" class="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout Admin
        </a>
    </div>

    {{-- Filter & Export Card --}}
    <div class="admin-card p-4 mb-4">
        <form action="{{ route('admin.dashboard') }}" method="GET" id="filterForm">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="tab" value="{{ request('tab', 'rekap') }}">
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

    {{-- Tabs --}}
    <div class="mb-4">
        <div class="admin-tabs" id="adminTab">
            <button type="button" class="tab-btn active" data-tab="rekap">
                <i class="fa-solid fa-table-list"></i> Rekap Absensi
            </button>
            <button type="button" class="tab-btn" data-tab="pegawai">
                <i class="fa-solid fa-users"></i> Kelola Magang
            </button>
            <button type="button" class="tab-btn" data-tab="jadwal">
                <i class="fa-solid fa-calendar-week"></i> Jadwal Mingguan
            </button>
            <button type="button" class="tab-btn" data-tab="timeline">
                <i class="fa-solid fa-chart-gantt"></i> Timeline Project
            </button>
            <button type="button" class="tab-btn" data-tab="bidang">
                <i class="fa-solid fa-layer-group"></i> Kelola Bidang
            </button>
        </div>
    </div>

    {{-- TAB: Rekap Absensi --}}
    <div class="tab-panel" id="panel-rekap">
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
                                            @if ($rec->status === 'wfh' && $rec->lokasi_latitude && $rec->lokasi_longitude)
                                                @php
                                                    $mapsUrl = 'https://www.google.com/maps?q=' . $rec->lokasi_latitude . ',' . $rec->lokasi_longitude;
                                                @endphp
                                                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="attachment-link" title="Buka lokasi WFH">
                                                    <i class="fa-solid fa-map-location-dot"></i> Maps
                                                </a>
                                                <div class="text-muted mt-1" style="font-size:0.72rem;">
                                                    Akurasi {{ $rec->lokasi_akurasi ? round((float) $rec->lokasi_akurasi) . ' m' : '-' }}
                                                </div>
                                                <div class="text-muted" style="font-size:0.72rem;">
                                                    {{ $rec->lokasi_diambil_pada ? $rec->lokasi_diambil_pada->timezone(config('app.timezone'))->format('H:i') . ' WIB' : 'Saat absen' }}
                                                </div>
                                            @elseif ($rec->status === 'wfh')
                                                <span class="text-muted" style="font-size:0.82rem;">Belum ada lokasi</span>
                                            @else
                                                <span class="text-muted" style="font-size:0.82rem;">-</span>
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

    {{-- TAB: Kelola Magang --}}
    <div class="tab-panel d-none" id="panel-pegawai">
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
                            @foreach ($pembimbingOptions as $namaPembimbing)
                                <option value="{{ $namaPembimbing }}" {{ $pembimbingMagang === $namaPembimbing ? 'selected' : '' }}>
                                    {{ $namaPembimbing }}
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
                                    <td colspan="8" style="background:#f8fafc;color:var(--dark);font-weight:800;">
                                        <i class="fa-solid fa-user-tie me-1" style="color:var(--primary);"></i>
                                        {{ $groupName }} <span class="text-muted fw-semibold">({{ $groupUsers->count() }} peserta)</span>
                                    </td>
                                </tr>
                                @foreach ($groupUsers as $u)
                                    <tr>
                                        <td class="text-muted fw-semibold">{{ $rowNumber++ }}</td>
                                        <td class="fw-semibold" style="color:var(--dark);">{{ $u->nama }}</td>
                                        <td>{{ $u->email ?? '—' }}</td>
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
                                            <button type="button" class="btn-action me-1" onclick="editUser({{ $u->id }}, {{ json_encode($u->nama) }}, {{ json_encode($u->email) }}, {{ json_encode($u->pembimbing_magang) }}, {{ json_encode($u->bidang_magang) }}, {{ json_encode(optional($u->tanggal_mulai_magang)->format('Y-m-d')) }}, {{ json_encode(optional($u->tanggal_selesai_magang)->format('Y-m-d')) }})" title="Edit">
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

    {{-- TAB: Jadwal Mingguan --}}
    <div class="tab-panel d-none" id="panel-jadwal">
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
    <div class="tab-panel d-none" id="panel-timeline">
        <div class="admin-card p-4 mb-4">
            <form action="{{ route('admin.project.store') }}" method="POST" id="createProjectForm" onsubmit="return requireCheckedProjectUsers(event, 'create-project-user')">
                @csrf
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--dark);">
                            <i class="fa-solid fa-chart-gantt me-1" style="color:var(--primary);"></i> Buat Timeline Project
                        </h6>
                        <p class="text-muted mb-0" style="font-size:0.82rem;">Project akan tampil sebagai kotak harian dari tanggal mulai sampai deadline.</p>
                    </div>
                    <button type="submit" class="btn-add">
                        <i class="fa-solid fa-plus"></i> Simpan Project
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Peserta Magang Project <span class="text-danger">*</span></label>
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

        <div class="timeline-board">
            @forelse ($projects as $project)
                @php
                    $days = [];
                    $cursor = $project->tanggal_mulai->copy();
                    while ($cursor->lte($project->tanggal_selesai)) {
                        $days[] = $cursor->copy();
                        $cursor->addDay();
                    }
                    $notesByDate = $project->notes->groupBy(fn ($note) => $note->tanggal->toDateString());
                    $assignmentsByDate = $project->dayAssignments->groupBy(fn ($assignment) => $assignment->tanggal->toDateString());
                    $projectMembers = $project->members->isNotEmpty() ? $project->members : collect([$project->user])->filter();
                    $projectMemberIds = $projectMembers->pluck('id')->values();
                    $doneCount = $project->notes->whereNotNull('selesai_pada')->count();
                    $totalNotes = $project->notes->count();
                @endphp
                <div class="project-row">
                    <div class="project-row-header">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h6 class="fw-bold mb-0" style="color:var(--dark);">{{ $project->nama }}</h6>
                                <span class="monitor-pill">{{ strtoupper($project->status_label ?? $project->status) }}</span>
                                <span class="monitor-pill">{{ $doneCount }}/{{ $totalNotes }} note selesai</span>
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
                                    <span class="employee-chip" draggable="true" data-project-id="{{ $project->id }}" data-user-id="{{ $member->id }}" data-user-name="{{ $member->nama }}" title="Drag ke kotak hari">
                                        <i class="fa-solid fa-grip-vertical"></i> {{ $member->nama }}
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
                    <div class="timeline-scroll">
                        <div class="timeline-days">
                            @foreach ($days as $day)
                                @php
                                    $key = $day->toDateString();
                                    $dayNotes = $notesByDate->get($key, collect());
                                    $dayAssignments = $assignmentsByDate->get($key, collect());
                                    $activeDayNotes = $dayNotes->whereNull('selesai_pada');
                                    $assignedUsersWithActiveNotes = $activeDayNotes->pluck('user_id')->filter()->unique();
                                    $visibleDayAssignments = $dayAssignments->reject(fn ($assignment) => $assignedUsersWithActiveNotes->contains($assignment->user_id));
                                @endphp
                                <div class="timeline-day" role="button" tabindex="0" data-project-id="{{ $project->id }}" data-date="{{ $key }}" onclick="handleTimelineDayClick(event, {{ $project->id }}, {{ json_encode($project->nama) }}, {{ json_encode($key) }}, {{ json_encode($day->translatedFormat('d F Y')) }})">
                                    <div class="timeline-day-date">{{ $day->format('d M') }}</div>
                                    <div class="timeline-day-name">{{ $day->translatedFormat('D') }}</div>
                                    <div class="day-assignments" data-project-id="{{ $project->id }}" data-date="{{ $key }}">
                                        @foreach ($visibleDayAssignments as $assignment)
                                            <span class="assignment-chip" data-assignment-id="{{ $assignment->id }}" data-user-id="{{ $assignment->user_id }}">
                                                <i class="fa-solid fa-user-check"></i> {{ $assignment->user->nama ?? '-' }}
                                                <button type="button" class="assignment-remove" title="Hapus peserta magang dari hari ini" onclick="removeAssignment(event, {{ $assignment->id }})">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                    @forelse ($activeDayNotes as $note)
                                        <span class="note-chip note-{{ $note->kategori }} {{ $note->user_selesai_pada ? 'note-done' : '' }}">
                                            @if ($note->user)
                                                <span class="d-block" style="font-size:0.64rem;font-weight:800;opacity:0.8;">
                                                    <i class="fa-solid fa-user me-1"></i>{{ \Illuminate\Support\Str::limit($note->user->nama, 18) }}
                                                </span>
                                            @endif
                                            {{ \Illuminate\Support\Str::limit($note->judul, 26) }}
                                        </span>
                                    @empty
                                        @if ($dayNotes->isNotEmpty())
                                            <span class="note-chip note-rendah note-done">Selesai</span>
                                        @else
                                            <span class="text-muted" style="font-size:0.72rem;">Klik tambah note</span>
                                        @endif
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($project->notes->isNotEmpty())
                        <div class="p-3 border-top" style="border-color:var(--border)!important;">
                            @foreach ($project->notes->sortBy('tanggal') as $note)
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                                    <div>
                                        <span class="note-chip note-{{ $note->kategori }} {{ $note->selesai_pada ? 'note-done' : '' }}" style="display:inline-block;width:auto;">
                                            {{ strtoupper($note->kategori_label ?? $note->kategori) }}
                                        </span>
                                        <span class="fw-semibold" style="font-size:0.86rem;color:var(--dark);">
                                            {{ $note->tanggal->translatedFormat('d M') }}
                                            @if ($note->user)
                                                - {{ $note->user->nama }}
                                            @endif
                                            - {{ $note->judul }}
                                        </span>
                                        @if ($note->catatan)
                                            <div class="text-muted" style="font-size:0.76rem;">{{ $note->catatan }}</div>
                                        @endif
                                    </div>
                                    @if ($note->selesai_pada)
                                        <span class="monitor-pill">Selesai {{ $note->selesai_pada->timezone(config('app.timezone'))->format('d M H:i') }}</span>
                                    @else
                                        @if ($note->user_id && !$note->user_selesai_pada)
                                            <span class="text-muted small"><i class="fa-solid fa-hourglass-start me-1"></i>Belum diselesaikan user</span>
                                        @else
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($note->user_selesai_pada)
                                                    <span class="badge bg-warning text-dark small me-1">Perlu Konfirmasi</span>
                                                @endif
                                                <form action="{{ route('timeline.note.complete', $note) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn-action" title="Tandai selesai">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="admin-card empty-state">
                    <h6>Belum ada timeline project</h6>
                    <p>Buat project pertama dari form di atas.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- TAB: Kelola Bidang --}}
    <div class="tab-panel d-none" id="panel-bidang">
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
                    <label class="form-label-admin">Email</label>
                    <input type="email" name="email" class="form-control form-control-admin w-100" placeholder="nama@email.com">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Pembimbing Magang</label>
                        <input type="text" name="pembimbing_magang" class="form-control form-control-admin w-100" placeholder="Nama pembimbing">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                        <select name="bidang_magang" class="form-select form-control-admin w-100" required>
                            <option value="" disabled selected>Pilih bidang...</option>
                            @foreach ($bidangs as $b)
                                <option value="{{ $b->nama }}">{{ $b->nama }}</option>
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
                    <label class="form-label-admin">Email</label>
                    <input type="email" name="email" id="e_email" class="form-control form-control-admin w-100" placeholder="nama@email.com">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label-admin">Pembimbing Magang</label>
                        <input type="text" name="pembimbing_magang" id="e_pembimbing_magang" class="form-control form-control-admin w-100" placeholder="Nama pembimbing">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-admin">Bidang Magang <span class="text-danger">*</span></label>
                        <select name="bidang_magang" id="e_bidang_magang" class="form-select form-control-admin w-100" required>
                            <option value="" disabled>Pilih bidang...</option>
                            @foreach ($bidangs as $b)
                                <option value="{{ $b->nama }}">{{ $b->nama }}</option>
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
            <form action="" method="POST" id="editProjectForm" onsubmit="return requireCheckedProjectUsers(event, 'edit-project-user')">
                @csrf
                <div class="mb-3">
                    <label class="form-label-admin">Peserta Magang Project <span class="text-danger">*</span></label>
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('#adminTab .tab-btn');
    const panels = {
        rekap: document.getElementById('panel-rekap'),
        pegawai: document.getElementById('panel-pegawai'),
        jadwal: document.getElementById('panel-jadwal'),
        timeline: document.getElementById('panel-timeline'),
        bidang: document.getElementById('panel-bidang'),
    };

    function switchTab(name) {
        tabs.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === name));
        Object.keys(panels).forEach(key => {
            panels[key].classList.toggle('d-none', key !== name);
        });
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    const activeTab = new URLSearchParams(window.location.search).get('tab');
    if (activeTab && panels[activeTab]) {
        switchTab(activeTab);
    }

    initProjectAssignmentDragDrop();
    bindProjectMemberPickers();
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

function editUser(id, nama, email, pembimbingMagang, bidangMagang, tanggalMulaiMagang, tanggalSelesaiMagang) {
    document.getElementById('editForm').action = "{{ url('admin/pegawai/update') }}/" + id;
    document.getElementById('e_nama').value = nama;
    document.getElementById('e_email').value = email || '';
    document.getElementById('e_pembimbing_magang').value = pembimbingMagang || '';
    document.getElementById('e_bidang_magang').value = bidangMagang || '';
    document.getElementById('e_tanggal_mulai_magang').value = tanggalMulaiMagang || '';
    document.getElementById('e_tanggal_selesai_magang').value = tanggalSelesaiMagang || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
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

function confirmProjectDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus project?',
        text: 'Timeline dan semua note project ini akan dihapus permanen.',
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
</script>
@endsection
