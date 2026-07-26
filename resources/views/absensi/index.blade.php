@extends('layouts.app')

@section('title', 'Menu Absensi & Laporan')

@section('styles')
<style>
    /* ── Page Header ── */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        margin-bottom: 1rem;
    }
    .page-header-back {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: color 0.2s;
    }
    .page-header-back:hover { color: var(--primary); }
    .page-header-title { text-align: right; }
    .page-header-title h5 { font-weight: 800; margin: 0; font-size: 1rem; color: var(--dark); }
    .page-header-title span { font-size: 0.78rem; color: var(--text-muted); }

    /* ── Tab Toggle ── */
    .tab-toggle {
        display: flex;
        max-width: 480px;
        margin: 0 auto 2rem;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 100px;
        padding: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .tab-toggle a {
        flex: 1;
        text-align: center;
        padding: 0.7rem 1rem;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        border-radius: 100px;
        transition: all 0.25s ease;
    }
    .tab-toggle a.active {
        background: linear-gradient(135deg, var(--primary) 0%, #7c6cf0 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(108, 92, 231, 0.3);
    }
    .tab-toggle a:not(.active):hover { color: var(--dark); }

    /* ── Form Card ── */
    .form-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
        max-width: 680px;
        margin: 0 auto;
    }

    /* ── Status Selector ── */
    .status-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }
    @media (max-width: 480px) { .status-grid { grid-template-columns: 1fr 1fr; } }

    .status-grid > div { display: flex; flex-direction: column; }
    .status-grid .btn-check { position: absolute; clip: rect(0,0,0,0); pointer-events: none; }

    .status-card {
        border: 1.5px solid var(--border);
        border-radius: 18px;
        background: var(--white);
        padding: 1.25rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .status-card:hover { border-color: #d1d5db; background: #fafbff; }

    .status-card .s-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; margin-bottom: 0.75rem;
    }
    .status-card .s-name { font-weight: 700; font-size: 0.92rem; color: var(--dark); }
    .status-card .s-desc { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }

    /* icon colors */
    .s-icon-hadir { background: rgba(0,184,148,0.1); color: #00b894; }
    .s-icon-wfh { background: rgba(108,92,231,0.1); color: #6c5ce7; }
    .s-icon-sakit { background: rgba(225,112,85,0.1); color: #e17055; }
    .s-icon-izin { background: rgba(253,203,110,0.15); color: #e17055; }

    /* checked state */
    .btn-check:checked + .status-card { border-color: var(--primary); background: rgba(108,92,231,0.04); box-shadow: 0 0 0 3px rgba(108,92,231,0.1); }

    /* ── Upload Zone ── */
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 16px;
        padding: 1.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        background: #fafbff;
    }
    .upload-zone:hover { border-color: var(--primary); background: rgba(108,92,231,0.02); }
    .upload-zone i.cloud { font-size: 2rem; color: var(--text-light); margin-bottom: 0.5rem; }
    .upload-zone:hover i.cloud { color: var(--primary); }
    .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .file-name { display: none; margin-top: 0.6rem; font-weight: 600; font-size: 0.82rem; color: var(--primary); }
    .upload-preview {
        display: none;
        width: 100%;
        max-height: 220px;
        margin-top: 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--border);
        object-fit: contain;
        background: #fff;
    }
    .camera-panel {
        display: none;
        margin-top: 0.85rem;
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        background: #0f172a;
    }
    .camera-panel video {
        display: block;
        width: 100%;
        max-height: 280px;
        object-fit: cover;
        background: #0f172a;
    }
    .camera-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #fff;
        border-top: 1px solid var(--border);
    }
    .camera-message {
        color: var(--text-muted);
        font-size: 0.75rem;
    }
    .camera-preview {
        display: none;
        width: 100%;
        max-height: 220px;
        border-top: 1px solid var(--border);
        object-fit: contain;
        background: #fff;
    }
    .camera-start-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
    }

    .location-panel {
        display: none;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
        padding: 1rem;
    }
    .location-panel.show { display: block; }
    .location-status {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: var(--text-muted);
        font-size: 0.82rem;
        line-height: 1.45;
    }
    .location-status i {
        color: var(--primary);
        margin-top: 0.15rem;
    }
    .location-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }
    .location-pill {
        border-radius: 999px;
        background: rgba(108,92,231,0.08);
        color: var(--primary);
        font-size: 0.74rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
    }
    /* ── Submit Button ── */
    .btn-submit {
        background: linear-gradient(135deg, var(--primary) 0%, #a29bfe 100%);
        border: none; color: #fff; font-weight: 700; border-radius: 100px;
        padding: 1rem; width: 100%; font-size: 1rem;
        box-shadow: 0 6px 20px rgba(108,92,231,0.25);
        transition: all 0.25s ease;
    }
    .btn-submit:hover { box-shadow: 0 8px 28px rgba(108,92,231,0.35); transform: translateY(-1px); color: #fff; }

    /* ── Rekap Section ── */
    .rekap-wrap {
        max-width: 960px;
        margin: 0 auto;
    }

    .filter-bar {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
        margin-bottom: 1.25rem;
    }
    .filter-bar label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 0.4rem;
        display: block;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1.2fr repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
    }

    .stat-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 1.1rem 1.15rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }

    .stat-card.highlight {
        display: flex;
        align-items: center;
        gap: 1rem;
        grid-row: span 1;
    }

    .pct-ring { position: relative; width: 64px; height: 64px; flex-shrink: 0; }
    .pct-ring svg { width: 100%; height: 100%; }
    .pct-ring-bg { fill: none; stroke: var(--border); stroke-width: 6; }
    .pct-ring-fill { fill: none; stroke: var(--primary); stroke-width: 6; stroke-linecap: round; transform: rotate(-90deg); transform-origin: 50% 50%; }
    .pct-label { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; color: var(--dark); }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.25rem 0.55rem;
        border-radius: 100px;
        margin-bottom: 0.5rem;
    }

    .history-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }

    .history-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: #fafbff;
    }

    .rekap-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .rekap-table thead th {
        background: #f1f5f9;
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.85rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }

    .rekap-table tbody td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .rekap-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rekap-table tbody tr:hover {
        background: #fafbff;
    }

    .filter-extra {
        display: none;
    }

    .filter-extra.show {
        display: block;
    }

    .timeline-wrap {
        max-width: 1080px;
        margin: 0 auto;
    }

    .timeline-project {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        margin-bottom: 1rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
    }

    .timeline-project-head {
        padding: 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
    }

    .timeline-scroll {
        overflow-x: auto;
        padding: 1rem;
    }

    .timeline-days {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 118px;
        gap: 0.55rem;
        min-height: 138px;
    }

    .timeline-day {
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        padding: 0.7rem;
        min-height: 128px;
    }

    .timeline-day.today {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(108,92,231,0.08);
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
        padding: 0.4rem 0.45rem;
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

    .assignment-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        max-width: 100%;
        border-radius: 999px;
        background: rgba(0,184,148,0.1);
        color: #047857;
        font-size: 0.68rem;
        font-weight: 800;
        padding: 0.28rem 0.5rem;
        margin: 0.25rem 0.2rem 0 0;
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
        justify-content: flex-start;
        margin: 0;
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

    .note-list {
        padding: 1rem;
        border-top: 1px solid var(--border);
    }
</style>
@endsection

@section('content')
<div class="container py-3">
    <!-- Page Header -->
    <div class="page-header">
        <a href="{{ route('home') }}" class="page-header-back">
            <i class="fa-solid fa-arrow-left"></i> Beranda
        </a>
        <div class="page-header-title">
            <h5>Menu Absensi & Laporan</h5>
            <span>Isi form atau lihat rekap Anda</span>
        </div>
    </div>

    <!-- Tab Toggle -->
    <div class="tab-toggle">
        <a href="{{ route('absensi.index', ['tab' => 'form']) }}" class="{{ $activeTab === 'form' ? 'active' : '' }}">
            1. Form Absensi Baru
        </a>
        <a href="{{ route('absensi.index', ['tab' => 'rekap']) }}" class="{{ $activeTab === 'rekap' ? 'active' : '' }}">
            2. Cek Rekap & Status
        </a>
        <a href="{{ route('absensi.index', ['tab' => 'timeline']) }}" class="{{ $activeTab === 'timeline' ? 'active' : '' }}">
            3. Timeline Project
        </a>
    </div>

    @if ($activeTab === 'form')
    {{-- ═══════════════ TAB 1: FORM ABSENSI ═══════════════ --}}
    <div class="form-card">
        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-3 mb-3 small">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('absensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Nama Peserta Magang -->
            <div class="mb-4">
                <label class="form-label form-label-premium">Nama Peserta Magang</label>
                <select name="user_id" id="user_id" class="form-select form-select-premium" required>
                    <option value="" disabled selected>Pilih nama Anda...</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nama }}@if($user->email) ({{ $user->email }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Absensi -->
            <div class="mb-4">
                <label class="form-label form-label-premium">Status Absensi Hari Ini</label>
                <div class="status-grid">
                    @php
                        $statusMeta = [
                            'hadir' => ['icon' => 'fa-building', 'desc' => 'Di kantor'],
                            'wfh' => ['icon' => 'fa-house', 'desc' => 'Work from home'],
                            'sakit' => ['icon' => 'fa-face-tired', 'desc' => 'Butuh istirahat'],
                            'izin' => ['icon' => 'fa-file-lines', 'desc' => 'Keperluan lain'],
                        ];
                    @endphp
                    @foreach ($absensiStatuses as $statusOption)
                        @php $meta = $statusMeta[$statusOption->kode] ?? ['icon' => 'fa-circle-check', 'desc' => 'Status absensi']; @endphp
                        <div>
                            <input type="radio" class="btn-check" name="status" id="status_{{ $statusOption->kode }}" value="{{ $statusOption->kode }}" {{ old('status', 'hadir') == $statusOption->kode ? 'checked' : '' }} autocomplete="off">
                            <label class="status-card" for="status_{{ $statusOption->kode }}">
                                <div class="s-icon s-icon-{{ $statusOption->kode }}"><i class="fa-solid {{ $meta['icon'] }}"></i></div>
                                <div class="s-name">{{ $statusOption->nama }}</div>
                                <div class="s-desc">{{ $meta['desc'] }}</div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Lokasi WFH -->
            <div class="mb-4 location-panel" id="location_section">
                <label class="form-label form-label-premium">Lokasi WFH <span class="text-danger">*</span></label>
                <input type="hidden" name="lokasi_latitude" id="lokasi_latitude" value="{{ old('lokasi_latitude') }}">
                <input type="hidden" name="lokasi_longitude" id="lokasi_longitude" value="{{ old('lokasi_longitude') }}">
                <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="{{ old('lokasi_akurasi') }}">
                <div class="location-status">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <div id="location_status">Klik Kunci Lokasi untuk menyimpan posisi WFH saat ini.</div>
                        <div class="location-meta">
                            <span class="location-pill" id="location_accuracy">Akurasi: -</span>
                            <span class="location-pill" id="location_coordinates">Koordinat: -</span>
                        </div>
                        <button type="button" class="btn btn-premium-secondary py-2 px-3 mt-3" id="lock_location">
                            <i class="fa-solid fa-location-crosshairs me-1"></i> Kunci Lokasi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Lampiran -->
            <div class="mb-4" id="photo_section">
                <label id="foto_label" class="form-label form-label-premium">Lampiran</label>
                <div class="upload-zone">
                    <i class="fa-solid fa-cloud-arrow-up cloud d-block"></i>
                    <div class="small fw-semibold text-dark" id="upload_text">Klik untuk unggah gambar</div>
                    <div class="text-muted" style="font-size:0.72rem;">PNG, JPG, JPEG, WEBP - Maks 5 MB</div>
                    <input type="file" name="foto" id="foto" accept="image/*">
                    <div class="file-name" id="file_name"><i class="fa-solid fa-circle-check me-1"></i><span id="fname"></span></div>
                    <img src="" class="upload-preview" id="foto_preview" alt="Preview lampiran">
                </div>
            </div>

            <!-- Foto Kamera -->
            <div class="mb-4" id="camera_section">
                <label class="form-label form-label-premium" id="camera_label">Foto Kamera <span class="text-danger">*</span></label>
                <input type="file" name="foto_kamera" id="foto_kamera" accept="image/*" class="d-none">
                <div class="camera-start-actions" id="camera_start_actions">
                    <span class="camera-message" id="camera_start_message">Nyalakan kamera lalu ambil foto untuk Hadir/WFH.</span>
                    <button type="button" class="btn btn-premium-secondary py-2 px-3" id="start_camera">
                        <i class="fa-solid fa-video me-1"></i> Nyalakan Kamera
                    </button>
                </div>
                <div class="camera-panel" id="camera_panel">
                    <video id="camera_video" autoplay playsinline muted></video>
                    <img src="" class="camera-preview" id="camera_preview" alt="Preview foto kamera">
                    <canvas id="camera_canvas" class="d-none"></canvas>
                    <div class="camera-actions">
                        <span class="camera-message" id="camera_message">Kamera aktif untuk Hadir/WFH.</span>
                        <button type="button" class="btn btn-premium-primary py-2 px-3" id="capture_photo">
                            <i class="fa-solid fa-camera me-1"></i> Ambil Foto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Laporan -->
            <div class="mb-4">
                <label for="laporan" id="laporan_label" class="form-label form-label-premium">Laporan Pekerjaan <span class="text-danger">*</span></label>
                <textarea name="laporan" id="laporan" rows="3" class="form-control form-control-premium" placeholder="Tuliskan laporan aktivitas hari ini..." required>{{ old('laporan') }}</textarea>
            </div>

            <button type="submit" class="btn btn-submit">
                <i class="fa-solid fa-paper-plane me-2"></i> Kirim Absensi
            </button>
        </form>
    </div>

    @elseif ($activeTab === 'rekap')
    {{-- ═══════════════ TAB 2: CEK REKAP & STATUS ═══════════════ --}}
    <div class="rekap-wrap">

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form action="{{ route('absensi.index') }}" method="GET" id="rekapFilterForm">
                <input type="hidden" name="tab" value="rekap">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label>Peserta Magang</label>
                        <select name="user_id" class="form-select form-select-premium py-2">
                            <option value="">Semua peserta magang</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Periode</label>
                        <select name="filter_type" id="filter_type" class="form-select form-select-premium py-2">
                            <option value="all" {{ $filterType == 'all' ? 'selected' : '' }}>Semua waktu</option>
                            <option value="month" {{ $filterType == 'month' ? 'selected' : '' }}>Per bulan</option>
                            <option value="date" {{ $filterType == 'date' ? 'selected' : '' }}>Tanggal spesifik</option>
                        </select>
                    </div>

                    <div class="col-md-4 filter-extra {{ $filterType === 'date' ? 'show' : '' }}" id="dateFilter">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control form-control-premium py-2" value="{{ request('date') }}">
                    </div>

                    <div class="col-md-4 filter-extra {{ $filterType === 'month' ? 'show' : '' }}" id="monthFilter">
                        <label>Bulan</label>
                        <input type="month" name="month_filter" class="form-control form-control-premium py-2" value="{{ request('month_filter', date('Y-m')) }}">
                    </div>

                    <div class="col-md-4 ms-auto">
                        <button type="submit" class="btn btn-premium-primary w-100 py-2">
                            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card highlight">
                <div class="pct-ring">
                    <svg viewBox="0 0 100 100">
                        <circle class="pct-ring-bg" cx="50" cy="50" r="40"/>
                        @php $c = 2 * pi() * 40; $o = $c - ($stats['persentase']/100) * $c; @endphp
                        <circle class="pct-ring-fill" cx="50" cy="50" r="40" style="stroke-dasharray:{{ $c }}; stroke-dashoffset:{{ $o }};"/>
                    </svg>
                    <span class="pct-label">{{ $stats['persentase'] }}%</span>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Persentase</div>
                    <div class="fw-bold">Kehadiran</div>
                    <div class="text-muted small">Hadir + WFH · {{ $stats['total_hari_kerja'] }} hari kerja</div>
                </div>
            </div>

            @foreach([
                ['Hadir', $stats['hadir'], '#00b894', 'rgba(0,184,148,0.12)'],
                ['WFH', $stats['wfh'], '#6c5ce7', 'rgba(108,92,231,0.12)'],
                ['Sakit', $stats['sakit'], '#e17055', 'rgba(225,112,85,0.12)'],
                ['Izin', $stats['izin'], '#d97706', 'rgba(245,158,11,0.15)'],
            ] as $s)
            <div class="stat-card">
                <span class="stat-pill" style="background:{{ $s[3] }}; color:{{ $s[2] }};">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $s[2] }};"></span>
                    {{ $s[0] }}
                </span>
                <div class="stat-value">{{ $s[1] }}</div>
                <div class="stat-label">Total {{ $s[0] }}</div>
            </div>
            @endforeach
        </div>

        <!-- History Table -->
        <div class="history-card">
            <div class="history-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> Riwayat Laporan
                </h6>
                <span class="text-muted small">{{ $absensi->count() }} entri</span>
            </div>

            @if ($absensi->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px; height:56px; background:rgba(108,92,231,0.06);">
                        <i class="fa-solid fa-file-lines text-primary" style="font-size:1.3rem;"></i>
                    </div>
                    <h6 class="fw-bold">Belum ada laporan</h6>
                    <p class="text-muted small mb-0">Ubah filter atau kirim absensi pertama dari tab Form Absensi Baru.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="rekap-table">
                        <thead>
                            <tr>
                                @if (!request('user_id'))
                                    <th>Peserta Magang</th>
                                @endif
                                <th>Tanggal &amp; Waktu</th>
                                <th>Status</th>
                                <th>Laporan</th>
                                <th>Foto Kamera</th>
                                <th>Lampiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($absensi as $rec)
                            <tr>
                                @if (!request('user_id'))
                                    <td class="fw-semibold">{{ $rec->user->nama ?? '-' }}</td>
                                @endif
                                <td>
                                    <div class="fw-medium">{{ \Carbon\Carbon::parse($rec->tanggal)->translatedFormat('d M Y') }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $rec->created_at->timezone(config('app.timezone'))->format('H:i') }} WIB</div>
                                </td>
                                <td><span class="badge badge-status badge-{{ $rec->status }}">{{ strtoupper($rec->status_label ?? $rec->status) }}</span></td>
                                <td style="max-width:280px;">
                                    <span class="text-muted" style="font-size:0.82rem; line-height:1.5;">{{ Str::limit($rec->laporan, 80) }}</span>
                                </td>
                                <td>
                                    @if ($rec->foto_kamera)
                                        @php $kameraUrl = route('absensi.kamera', $rec); @endphp
                                        <a href="{{ $kameraUrl }}" target="_blank" class="attachment-link">
                                            <img src="{{ $kameraUrl }}" alt="Foto Kamera" style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid var(--border);" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                            <span class="d-none"><i class="fa-solid fa-camera me-1"></i>Lihat</span>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($rec->foto)
                                        @php $lampiranUrl = route('absensi.lampiran', $rec); @endphp
                                        <a href="{{ $lampiranUrl }}" target="_blank" class="attachment-link">
                                            <img src="{{ $lampiranUrl }}" alt="Lampiran" style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid var(--border);" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                            <span class="d-none"><i class="fa-regular fa-image me-1"></i>Lihat</span>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @else
    {{-- TAB 3: TIMELINE PROJECT --}}
    <div class="timeline-wrap">
        <div class="filter-bar">
            <form action="{{ route('absensi.index') }}" method="GET">
                <input type="hidden" name="tab" value="timeline">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label>Peserta Magang</label>
                        <select name="user_id" class="form-select form-select-premium py-2" required>
                            <option value="" disabled {{ request('user_id') ? '' : 'selected' }}>Pilih nama untuk melihat timeline...</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama }}{{ $u->email ? ' - ' . $u->email : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-premium-primary w-100 py-2">
                            <i class="fa-solid fa-chart-gantt me-1"></i> Lihat Timeline
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if (! $timelineUser)
            <div class="history-card text-center py-5 px-3">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;background:rgba(108,92,231,0.06);">
                    <i class="fa-solid fa-chart-gantt text-primary" style="font-size:1.3rem;"></i>
                </div>
                <h6 class="fw-bold">Pilih nama peserta magang</h6>
                <p class="text-muted small mb-0">Timeline project akan muncul berdasarkan project yang sudah dibuat admin.</p>
            </div>
        @elseif ($timelineProjects->isEmpty())
            <div class="history-card text-center py-5 px-3">
                <h6 class="fw-bold">Belum ada project</h6>
                <p class="text-muted small mb-0">Admin belum membuat timeline project untuk {{ $timelineUser->nama }}.</p>
            </div>
        @else
            @foreach ($timelineProjects as $project)
                @php
                    $days = [];
                    $cursor = $project->tanggal_mulai->copy();
                    while ($cursor->lte($project->tanggal_selesai)) {
                        $days[] = $cursor->copy();
                        $cursor->addDay();
                    }
                    $notesByDate = $project->notes->groupBy(fn ($note) => $note->tanggal->toDateString());
                    $assignmentsByDate = $project->dayAssignments->groupBy(fn ($assignment) => $assignment->tanggal->toDateString());
                    $doneCount = $project->notes->whereNotNull('selesai_pada')->count();
                    $totalNotes = $project->notes->count();
                    $today = now(config('app.timezone'))->toDateString();
                @endphp
                <div class="timeline-project">
                    <div class="timeline-project-head">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h6 class="fw-bold mb-1" style="color:var(--dark);">{{ $project->nama }}</h6>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    {{ $project->tanggal_mulai->translatedFormat('d M Y') }} - {{ $project->tanggal_selesai->translatedFormat('d M Y') }}
                                </div>
                            </div>
                            <span class="stat-pill" style="background:rgba(108,92,231,0.1);color:var(--primary);">
                                {{ $doneCount }}/{{ $totalNotes }} selesai
                            </span>
                        </div>
                        @if ($project->kebutuhan)
                            <p class="text-muted mb-0 mt-2" style="font-size:0.82rem;">{{ $project->kebutuhan }}</p>
                        @endif
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
                                <div class="timeline-day {{ $key === $today ? 'today' : '' }}">
                                    <div class="timeline-day-date">{{ $day->format('d M') }}</div>
                                    <div class="timeline-day-name">{{ $day->translatedFormat('D') }}</div>
                                    <div class="day-assignments">
                                        @foreach ($visibleDayAssignments as $assignment)
                                            <span class="assignment-chip">
                                                <i class="fa-solid fa-user-check"></i> {{ $assignment->user->nama ?? '-' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @forelse ($activeDayNotes as $note)
                                        <span class="note-chip note-{{ $note->kategori }}">
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
                                            <span class="text-muted" style="font-size:0.72rem;">Tidak ada note</span>
                                        @endif
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($project->notes->isNotEmpty())
                        <div class="note-list">
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
                                        <span class="stat-pill" style="background:rgba(16,185,129,0.1);color:#047857;">
                                            Selesai {{ $note->selesai_pada->timezone(config('app.timezone'))->format('d M H:i') }}
                                        </span>
                                    @elseif ($note->user_id && (int) $note->user_id !== (int) $timelineUser->id)
                                        <span class="text-muted" style="font-size:0.76rem;">Note peserta lain</span>
                                    @else
                                        <form action="{{ route('timeline.note.complete', $note) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $timelineUser->id }}">
                                            <input type="hidden" name="redirect_tab" value="timeline">
                                            <button type="submit" class="btn btn-premium-primary py-2 px-3">
                                                <i class="fa-solid fa-check me-1"></i> Tandai Selesai
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form tab: dynamic labels
    const radios = document.querySelectorAll('input[name="status"]');
    const attendanceForm = document.querySelector('form[action="{{ route('absensi.store') }}"]');
    const fotoLabel = document.getElementById('foto_label');
    const fotoInput = document.getElementById('foto');
    const laporanLabel = document.getElementById('laporan_label');
    const laporanInput = document.getElementById('laporan');
    const fileNameDiv = document.getElementById('file_name');
    const fnameSpan = document.getElementById('fname');
    const uploadText = document.getElementById('upload_text');
    const fotoPreview = document.getElementById('foto_preview');
    const fotoKameraInput = document.getElementById('foto_kamera');
    const cameraSection = document.getElementById('camera_section');
    const cameraStartActions = document.getElementById('camera_start_actions');
    const startCameraButton = document.getElementById('start_camera');
    const cameraPanel = document.getElementById('camera_panel');
    const cameraVideo = document.getElementById('camera_video');
    const cameraCanvas = document.getElementById('camera_canvas');
    const cameraLabel = document.getElementById('camera_label');
    const cameraStartMessage = document.getElementById('camera_start_message');
    const cameraMessage = document.getElementById('camera_message');
    const cameraPreview = document.getElementById('camera_preview');
    const capturePhoto = document.getElementById('capture_photo');
    const locationSection = document.getElementById('location_section');
    const locationStatus = document.getElementById('location_status');
    const locationAccuracy = document.getElementById('location_accuracy');
    const locationCoordinates = document.getElementById('location_coordinates');
    const latitudeInput = document.getElementById('lokasi_latitude');
    const longitudeInput = document.getElementById('lokasi_longitude');
    const accuracyInput = document.getElementById('lokasi_akurasi');
    const lockLocationButton = document.getElementById('lock_location');
    let previewUrl = null;
    let cameraPreviewUrl = null;
    let cameraStream = null;

    function setLocation(position, options = {}) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        if (latitudeInput) latitudeInput.value = lat.toFixed(7);
        if (longitudeInput) longitudeInput.value = lng.toFixed(7);
        if (accuracyInput) accuracyInput.value = accuracy ? accuracy.toFixed(2) : '';
        if (locationStatus) locationStatus.innerText = options.message || 'Lokasi WFH terkunci. Klik Ambil Ulang Lokasi jika posisinya belum tepat.';
        if (locationAccuracy) locationAccuracy.innerText = `Akurasi: ${accuracy ? Math.round(accuracy) : '-'} m`;
        if (locationCoordinates) locationCoordinates.innerText = `Koordinat: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        if (lockLocationButton) lockLocationButton.innerHTML = '<i class="fa-solid fa-location-crosshairs me-1"></i> Ambil Ulang Lokasi';
    }

    function setLocationError(message) {
        if (locationStatus) locationStatus.innerText = message;
        if (latitudeInput) latitudeInput.value = '';
        if (longitudeInput) longitudeInput.value = '';
        if (accuracyInput) accuracyInput.value = '';
        if (locationAccuracy) locationAccuracy.innerText = 'Akurasi: -';
        if (locationCoordinates) locationCoordinates.innerText = 'Koordinat: -';
        if (lockLocationButton) lockLocationButton.innerHTML = '<i class="fa-solid fa-location-crosshairs me-1"></i> Kunci Lokasi';
    }

    function showLocationSection() {
        if (!locationSection) return;
        locationSection.classList.add('show');
        if (!latitudeInput?.value || !longitudeInput?.value) {
            if (locationStatus) locationStatus.innerText = 'Klik Kunci Lokasi untuk menyimpan posisi WFH saat ini.';
        }
    }

    function hideLocationSection() {
        if (locationSection) locationSection.classList.remove('show');
        setLocationError('Klik Kunci Lokasi untuk menyimpan posisi WFH saat ini.');
    }

    function captureLocationOnce() {
        if (!navigator.geolocation) {
            return Promise.reject(new Error('Browser ini tidak mendukung geolocation. Gunakan browser lain untuk WFH.'));
        }

        if (locationStatus) locationStatus.innerText = 'Mengambil lokasi saat ini...';

        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                position => {
                    setLocation(position);
                    resolve(position);
                },
                () => {
                    setLocationError('Lokasi belum bisa diambil. Izinkan akses lokasi browser untuk absensi WFH.');
                    reject(new Error('Lokasi belum bisa diambil.'));
                },
                { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 }
            );
        });
    }

    async function startCamera() {
        if (!cameraPanel || !cameraVideo || cameraStream) return;

        cameraPanel.style.display = 'block';
        if (cameraStartActions) cameraStartActions.style.display = 'none';
        cameraMessage.innerText = 'Membuka kamera...';

        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Kamera tidak didukung browser ini.');
            }

            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false
            });
            cameraVideo.srcObject = cameraStream;
            cameraMessage.innerText = 'Kamera aktif. Klik Ambil Foto sebelum kirim absensi.';
        } catch (error) {
            if (cameraStartActions) cameraStartActions.style.display = 'flex';
            cameraMessage.innerText = 'Kamera tidak bisa dibuka. Gunakan upload gambar manual.';
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }

        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }

        if (cameraPanel) {
            cameraPanel.style.display = 'none';
        }

        if (cameraStartActions) {
            cameraStartActions.style.display = 'flex';
        }
    }

    function setPreviewFromFile(file) {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        fnameSpan.innerText = file.name;
        fileNameDiv.style.display = 'block';
        uploadText.innerText = 'Ganti berkas';

        if (fotoPreview && file.type.startsWith('image/')) {
            previewUrl = URL.createObjectURL(file);
            fotoPreview.src = previewUrl;
            fotoPreview.style.display = 'block';
        }
    }

    function setCameraPreviewFromFile(file) {
        if (cameraPreviewUrl) {
            URL.revokeObjectURL(cameraPreviewUrl);
            cameraPreviewUrl = null;
        }

        if (cameraPreview && file.type.startsWith('image/')) {
            cameraPreviewUrl = URL.createObjectURL(file);
            cameraPreview.src = cameraPreviewUrl;
            cameraPreview.style.display = 'block';
        }
    }

    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                setPreviewFromFile(this.files[0]);
            } else {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }
                fileNameDiv.style.display = 'none';
                uploadText.innerText = 'Klik untuk unggah gambar';
                if (fotoPreview) {
                    fotoPreview.removeAttribute('src');
                    fotoPreview.style.display = 'none';
                }
            }
        });
    }

    if (capturePhoto) {
        capturePhoto.addEventListener('click', function() {
            if (!fotoKameraInput || !cameraVideo || !cameraCanvas || !cameraStream) return;

            const width = cameraVideo.videoWidth || 1280;
            const height = cameraVideo.videoHeight || 720;
            cameraCanvas.width = width;
            cameraCanvas.height = height;
            cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0, width, height);

            cameraCanvas.toBlob(function(blob) {
                if (!blob) return;

                const file = new File([blob], `kamera_absensi_${Date.now()}.jpg`, { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                fotoKameraInput.files = transfer.files;
                setCameraPreviewFromFile(file);
                cameraMessage.innerText = 'Foto berhasil diambil dan siap dikirim.';
            }, 'image/jpeg', 0.9);
        });
    }

    if (startCameraButton) {
        startCameraButton.addEventListener('click', startCamera);
    }

    if (lockLocationButton) {
        lockLocationButton.addEventListener('click', function() {
            captureLocationOnce().catch(error => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lokasi belum bisa dikunci',
                        text: error.message || 'Izinkan akses lokasi browser untuk mengunci lokasi WFH.',
                        confirmButtonColor: '#6c5ce7'
                    });
                } else {
                    alert(error.message || 'Izinkan akses lokasi browser untuk mengunci lokasi WFH.');
                }
            });
        });
    }

    if (attendanceForm) {
        attendanceForm.addEventListener('submit', function(e) {
            const checked = document.querySelector('input[name="status"]:checked');
            const needsCamera = checked && ['hadir', 'wfh'].includes(checked.value);
            const needsLocation = checked && checked.value === 'wfh';

            if (needsCamera && (!fotoKameraInput || fotoKameraInput.files.length === 0)) {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto kamera belum diambil',
                        text: 'Klik Nyalakan Kamera, lalu Ambil Foto terlebih dahulu untuk status Hadir atau WFH.',
                        confirmButtonColor: '#6c5ce7'
                    });
                } else {
                    alert('Klik Nyalakan Kamera, lalu Ambil Foto terlebih dahulu untuk status Hadir atau WFH.');
                }
                return;
            }

            if (needsLocation) {
                if (!latitudeInput?.value || !longitudeInput?.value) {
                    e.preventDefault();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Lokasi WFH belum dikunci',
                            text: 'Klik Kunci Lokasi terlebih dahulu sebelum mengirim absensi WFH.',
                            confirmButtonColor: '#6c5ce7'
                        });
                    } else {
                        alert('Klik Kunci Lokasi terlebih dahulu sebelum mengirim absensi WFH.');
                    }
                }
            }
        });
    }

    function updateLabels() {
        const checked = document.querySelector('input[name="status"]:checked');
        if (!checked || !fotoLabel) return;
        const v = checked.value;
        const map = {
            hadir: ['Lampiran Tambahan (Opsional)', false, 'Laporan Pekerjaan Harian', 'Deskripsi pekerjaan hari ini...'],
            wfh: ['Lampiran Tambahan (Opsional)', false, 'Rencana & Progres WFH', 'Tuliskan project/task yang dikerjakan dari rumah hari ini...'],
            sakit: ['Surat Keterangan Sakit', true, 'Keterangan Sakit', 'Rincian kondisi kesehatan...'],
            izin: ['Lampiran Izin (Opsional)', false, 'Alasan Izin', 'Alasan pengajuan izin...']
        };
        const m = map[v];
        fotoLabel.innerHTML = m[0] + (m[1] ? ' <span class="text-danger">*</span>' : '');
        if (fotoInput) fotoInput.required = m[1];
        if (laporanLabel) laporanLabel.innerHTML = m[2] + ' <span class="text-danger">*</span>';
        if (laporanInput) laporanInput.placeholder = m[3];
        if (['hadir', 'wfh'].includes(v)) {
            if (cameraSection) cameraSection.style.display = 'block';
            if (!cameraStream && cameraPanel) cameraPanel.style.display = 'none';
            if (!cameraStream && cameraStartActions) cameraStartActions.style.display = 'flex';
            if (cameraLabel) {
                cameraLabel.innerHTML = (v === 'wfh' ? 'Foto Aktivitas WFH' : 'Foto Kamera') + ' <span class="text-danger">*</span>';
            }
            if (cameraStartMessage) {
                cameraStartMessage.innerText = v === 'wfh'
                    ? 'Ambil foto laptop, layar kerja, meja kerja, atau catatan project sebagai bukti aktivitas.'
                    : 'Nyalakan kamera lalu ambil foto untuk absensi hadir.';
            }
            if (cameraMessage) {
                cameraMessage.innerText = v === 'wfh'
                    ? 'Kamera aktif. Arahkan ke bukti aktivitas kerja, lalu klik Ambil Foto.'
                    : 'Kamera aktif. Klik Ambil Foto sebelum kirim absensi.';
            }
        } else {
            if (cameraSection) cameraSection.style.display = 'none';
            if (fotoKameraInput) {
                fotoKameraInput.value = '';
            }
            if (cameraPreviewUrl) {
                URL.revokeObjectURL(cameraPreviewUrl);
                cameraPreviewUrl = null;
            }
            if (cameraPreview) {
                cameraPreview.removeAttribute('src');
                cameraPreview.style.display = 'none';
            }
            stopCamera();
        }

        if (v === 'wfh') {
            showLocationSection();
        } else {
            hideLocationSection();
        }
    }

    radios.forEach(r => r.addEventListener('change', updateLabels));
    updateLabels();

    // Rekap tab: toggle filter fields
    const filterType = document.getElementById('filter_type');
    const dateFilter = document.getElementById('dateFilter');
    const monthFilter = document.getElementById('monthFilter');

    function toggleFilterFields() {
        if (!filterType) return;
        const value = filterType.value;
        dateFilter?.classList.toggle('show', value === 'date');
        monthFilter?.classList.toggle('show', value === 'month');
    }

    filterType?.addEventListener('change', toggleFilterFields);
    toggleFilterFields();

});
</script>
@endsection
