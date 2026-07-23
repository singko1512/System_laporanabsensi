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

            <!-- Nama Karyawan -->
            <div class="mb-4">
                <label class="form-label form-label-premium">Nama Karyawan</label>
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
                    <div>
                        <input type="radio" class="btn-check" name="status" id="status_hadir" value="hadir" {{ old('status', 'hadir') == 'hadir' ? 'checked' : '' }} autocomplete="off">
                        <label class="status-card" for="status_hadir">
                            <div class="s-icon s-icon-hadir"><i class="fa-solid fa-building"></i></div>
                            <div class="s-name">Hadir</div>
                            <div class="s-desc">Di kantor</div>
                        </label>
                    </div>
                    <div>
                        <input type="radio" class="btn-check" name="status" id="status_wfh" value="wfh" {{ old('status') == 'wfh' ? 'checked' : '' }} autocomplete="off">
                        <label class="status-card" for="status_wfh">
                            <div class="s-icon s-icon-wfh"><i class="fa-solid fa-house"></i></div>
                            <div class="s-name">WFH</div>
                            <div class="s-desc">Work from home</div>
                        </label>
                    </div>
                    <div>
                        <input type="radio" class="btn-check" name="status" id="status_sakit" value="sakit" {{ old('status') == 'sakit' ? 'checked' : '' }} autocomplete="off">
                        <label class="status-card" for="status_sakit">
                            <div class="s-icon s-icon-sakit"><i class="fa-solid fa-face-tired"></i></div>
                            <div class="s-name">Sakit</div>
                            <div class="s-desc">Butuh istirahat</div>
                        </label>
                    </div>
                    <div>
                        <input type="radio" class="btn-check" name="status" id="status_izin" value="izin" {{ old('status') == 'izin' ? 'checked' : '' }} autocomplete="off">
                        <label class="status-card" for="status_izin">
                            <div class="s-icon s-icon-izin"><i class="fa-solid fa-file-lines"></i></div>
                            <div class="s-name">Izin</div>
                            <div class="s-desc">Keperluan lain</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Upload Foto -->
            <div class="mb-4" id="photo_section">
                <label id="foto_label" class="form-label form-label-premium">Foto Bukti <span class="text-danger" id="foto_star">*</span></label>
                <div class="upload-zone">
                    <i class="fa-solid fa-cloud-arrow-up cloud d-block"></i>
                    <div class="small fw-semibold text-dark" id="upload_text">Klik untuk unggah gambar</div>
                    <div class="text-muted" style="font-size:0.72rem;">PNG, JPG, JPEG — Maks 2 MB</div>
                    <input type="file" name="foto" id="foto" accept="image/*">
                    <div class="file-name" id="file_name"><i class="fa-solid fa-circle-check me-1"></i><span id="fname"></span></div>
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

    @else
    {{-- ═══════════════ TAB 2: CEK REKAP & STATUS ═══════════════ --}}
    <div class="rekap-wrap">

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form action="{{ route('absensi.index') }}" method="GET" id="rekapFilterForm">
                <input type="hidden" name="tab" value="rekap">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label>Karyawan</label>
                        <select name="user_id" class="form-select form-select-premium py-2">
                            <option value="">Semua karyawan</option>
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
                                    <th>Karyawan</th>
                                @endif
                                <th>Tanggal &amp; Waktu</th>
                                <th>Status</th>
                                <th>Laporan</th>
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
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $rec->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td><span class="badge badge-status badge-{{ $rec->status }}">{{ strtoupper($rec->status) }}</span></td>
                                <td style="max-width:280px;">
                                    <span class="text-muted" style="font-size:0.82rem; line-height:1.5;">{{ Str::limit($rec->laporan, 80) }}</span>
                                </td>
                                <td>
                                    @if ($rec->foto)
                                        <a href="{{ asset($rec->foto) }}" target="_blank">
                                            <img src="{{ asset($rec->foto) }}" alt="Lampiran" style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
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
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form tab: dynamic labels
    const radios = document.querySelectorAll('input[name="status"]');
    const fotoLabel = document.getElementById('foto_label');
    const fotoInput = document.getElementById('foto');
    const laporanLabel = document.getElementById('laporan_label');
    const laporanInput = document.getElementById('laporan');
    const fileNameDiv = document.getElementById('file_name');
    const fnameSpan = document.getElementById('fname');
    const uploadText = document.getElementById('upload_text');

    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fnameSpan.innerText = this.files[0].name;
                fileNameDiv.style.display = 'block';
                uploadText.innerText = 'Ganti berkas';
            } else {
                fileNameDiv.style.display = 'none';
                uploadText.innerText = 'Klik untuk unggah gambar';
            }
        });
    }

    function updateLabels() {
        const checked = document.querySelector('input[name="status"]:checked');
        if (!checked || !fotoLabel) return;
        const v = checked.value;
        const map = {
            hadir: ['Foto Bukti Kehadiran', true, 'Laporan Pekerjaan Harian', 'Deskripsi pekerjaan hari ini...'],
            wfh: ['Foto Bukti WFH', true, 'Laporan Progres WFH', 'Progres pekerjaan dari rumah...'],
            sakit: ['Surat Keterangan Sakit', true, 'Keterangan Sakit', 'Rincian kondisi kesehatan...'],
            izin: ['Foto Pendukung (Opsional)', false, 'Alasan Izin', 'Alasan pengajuan izin...']
        };
        const m = map[v];
        fotoLabel.innerHTML = m[0] + (m[1] ? ' <span class="text-danger">*</span>' : '');
        if (fotoInput) fotoInput.required = m[1];
        if (laporanLabel) laporanLabel.innerHTML = m[2] + ' <span class="text-danger">*</span>';
        if (laporanInput) laporanInput.placeholder = m[3];
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
