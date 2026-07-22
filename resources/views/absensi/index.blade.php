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
    .filter-bar {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
        max-width: 680px;
        margin: 0 auto 1.5rem;
    }
    .filter-bar label { font-size: 0.82rem; font-weight: 700; color: var(--dark); margin-bottom: 0.4rem; display: block; }

    /* Stats row */
    .stats-row {
        display: grid;
        grid-template-columns: auto 1fr 1fr 1fr 1fr;
        gap: 0.75rem;
        max-width: 680px;
        margin: 0 auto 1.5rem;
    }
    @media (max-width: 640px) { .stats-row { grid-template-columns: 1fr 1fr; } }

    .stat-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }

    .pct-ring { position: relative; width: 60px; height: 60px; }
    .pct-ring svg { width: 100%; height: 100%; }
    .pct-ring-bg { fill: none; stroke: var(--border); stroke-width: 6; }
    .pct-ring-fill { fill: none; stroke: var(--primary); stroke-width: 6; stroke-linecap: round; transform: rotate(-90deg); transform-origin: 50% 50%; }
    .pct-label { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; color: var(--dark); }

    /* History section */
    .history-section {
        max-width: 680px;
        margin: 0 auto;
    }
    .history-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }
    .history-item { padding: 1rem 0; border-bottom: 1px solid var(--border); }
    .history-item:last-child { border-bottom: none; }
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
                            {{ $user->nama }}@if($user->nip_atau_id) ({{ $user->nip_atau_id }})@endif
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

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form action="{{ route('absensi.index') }}" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="tab" value="rekap">

            <div class="col-md-4">
                <label>Karyawan</label>
                <select name="user_id" class="form-select form-select-premium py-2" onchange="this.form.submit()">
                    <option value="">Semua karyawan</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Periode</label>
                <select name="filter_type" class="form-select form-select-premium py-2">
                    <option value="all" {{ $filterType == 'all' ? 'selected' : '' }}>Semua waktu</option>
                    <option value="month" {{ $filterType == 'month' ? 'selected' : '' }}>Per bulan</option>
                    <option value="date" {{ $filterType == 'date' ? 'selected' : '' }}>Tanggal spesifik</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Tanggal spesifik</label>
                <input type="date" name="date" class="form-control form-control-premium py-2" value="{{ request('date') }}">
            </div>

            <div class="col-12 text-end d-md-none">
                <button type="submit" class="btn btn-premium-primary w-100 py-2">Terapkan</button>
            </div>

            <!-- Hidden submit trigger on desktop filter change -->
            <noscript><div class="col-12"><button type="submit" class="btn btn-premium-primary">Filter</button></div></noscript>
        </form>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <!-- Percentage Ring -->
        <div class="stat-card d-flex align-items-center gap-3">
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
                <div class="fw-bold" style="font-size:0.9rem;">Kehadiran</div>
                <div class="text-muted small">Hadir + WFH</div>
            </div>
        </div>

        @foreach([
            ['Hadir', $stats['hadir'], 'fa-building', 's-icon-hadir', '#00b894'],
            ['WFH', $stats['wfh'], 'fa-house', 's-icon-wfh', '#6c5ce7'],
            ['Sakit', $stats['sakit'], 'fa-face-tired', 's-icon-sakit', '#e17055'],
            ['Izin', $stats['izin'], 'fa-file-lines', 's-icon-izin', '#fdcb6e'],
        ] as $s)
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="s-icon {{ $s[3] }}" style="width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:0.9rem;">
                    <i class="fa-solid fa-{{ $s[2] }}"></i>
                </div>
                <span class="badge rounded-pill" style="font-size:0.68rem; font-weight:700; background:{{ $s[4] }}22; color:{{ $s[4] }};">
                    <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:{{ $s[4] }}; margin-right:4px; vertical-align:middle;"></span>{{ $s[0] }}
                </span>
            </div>
            <div class="fw-extrabold fs-4" style="font-weight:800; color:var(--dark);">{{ $s[1] }}</div>
            <div class="text-muted small">Total {{ $s[0] }}</div>
        </div>
        @endforeach
    </div>

    <!-- History Section -->
    <div class="history-section">
        <h6 class="fw-bold mb-3" style="font-size:0.95rem; color:var(--dark);">Riwayat Laporan</h6>
        <div class="history-card">
            @if ($absensi->isEmpty())
                <div class="text-center py-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px; height:56px; background:rgba(108,92,231,0.06);">
                        <i class="fa-solid fa-file-lines text-primary" style="font-size:1.3rem;"></i>
                    </div>
                    <h6 class="fw-bold">Belum ada laporan</h6>
                    <p class="text-muted small mb-0">Kirim absensi pertama Anda dari tab pertama.</p>
                </div>
            @else
                @foreach ($absensi as $rec)
                <div class="history-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-dark">{{ \Carbon\Carbon::parse($rec->tanggal)->translatedFormat('l, d F Y') }}</span>
                        <span class="badge badge-status badge-{{ $rec->status }}">{{ $rec->status }}</span>
                    </div>
                    <div class="row align-items-start g-2">
                        <div class="col">
                            <p class="text-muted small mb-1" style="line-height:1.65;">{{ $rec->laporan }}</p>
                            <span class="text-muted" style="font-size:0.72rem;"><i class="fa-regular fa-clock me-1"></i>{{ $rec->created_at->format('H:i') }} WIB</span>
                        </div>
                        @if ($rec->foto)
                        <div class="col-auto">
                            <a href="{{ asset($rec->foto) }}" target="_blank" class="d-block rounded-3 overflow-hidden border" style="width:56px; height:56px;">
                                <img src="{{ asset($rec->foto) }}" class="w-100 h-100 object-fit-cover" alt="Bukti">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
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

    // Rekap tab: auto-submit on filter change (desktop)
    const filterSelects = document.querySelectorAll('.filter-bar select');
    filterSelects.forEach(sel => {
        sel.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endsection
