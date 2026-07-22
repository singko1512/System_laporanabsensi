@extends('layouts.app')

@section('title', 'Rekap Saya - Absensi Harian')

@section('styles')
<style>
    .ring-container { position: relative; width: 110px; height: 110px; }
    .ring-bg { fill: none; stroke: var(--border); stroke-width: 7; }
    .ring-fill { fill: none; stroke: var(--primary); stroke-width: 7; stroke-linecap: round; transform: rotate(-90deg); transform-origin: 50% 50%; transition: stroke-dashoffset 0.8s ease; }
    .ring-label { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.35rem; font-weight: 800; color: var(--dark); }

    .stat-box {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--white);
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        transition: transform 0.2s;
    }
    .stat-box:hover { transform: translateY(-2px); }

    .tl-wrap { border-left: 2px solid var(--border); padding-left: 1.5rem; margin-left: 0.5rem; }
    .tl-item {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
        transition: all 0.25s ease;
    }
    .tl-item:hover { transform: translateX(3px); border-color: rgba(108,92,231,0.2); }
    .tl-dot {
        position: absolute;
        left: -33px; top: 22px;
        width: 12px; height: 12px;
        border-radius: 50%;
        border: 3px solid var(--bg);
    }
    .tl-dot-hadir { background: var(--green); box-shadow: 0 0 0 3px rgba(0,184,148,0.15); }
    .tl-dot-wfh { background: var(--primary); box-shadow: 0 0 0 3px rgba(108,92,231,0.15); }
    .tl-dot-sakit { background: var(--rose); box-shadow: 0 0 0 3px rgba(225,112,85,0.15); }
    .tl-dot-izin { background: var(--amber); box-shadow: 0 0 0 3px rgba(253,203,110,0.3); }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="glass-card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <span class="header-badge"><i class="fa-regular fa-chart-bar me-1"></i> Rekap Absensi</span>
                        <h2 class="fw-bold mb-1" style="letter-spacing:-0.5px;">Statistik Kehadiran</h2>
                        <p class="text-muted small mb-0">Pilih nama untuk melihat persentase kehadiran dan riwayat laporan.</p>
                    </div>
                    <div class="col-md-5 mt-3 mt-md-0">
                        <form action="{{ route('absensi.rekap') }}" method="GET" id="userForm">
                            <select name="user_id" class="form-select form-select-premium" onchange="document.getElementById('userForm').submit()">
                                <option value="" disabled selected>-- Pilih Nama --</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }} @if($u->nip_atau_id)({{ $u->nip_atau_id }})@endif</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            @if ($selectedUser)
            <div class="row g-4 mb-4">
                <!-- Filter -->
                <div class="col-md-4 col-lg-3">
                    <div class="glass-card p-4 h-100">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-sliders me-1 text-primary"></i> Filter</h6>
                        <form action="{{ route('absensi.rekap') }}" method="GET">
                            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                            <div class="mb-3">
                                <select name="filter_type" id="filter_type" class="form-select form-select-premium py-2">
                                    <option value="month" {{ $filterType == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                                    <option value="week" {{ $filterType == 'week' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="date" {{ $filterType == 'date' ? 'selected' : '' }}>Tanggal Spesifik</option>
                                </select>
                            </div>
                            <div class="mb-3 @if($filterType !== 'week') d-none @endif" id="wg">
                                <input type="week" name="week" class="form-control form-control-premium" value="{{ request('week', date('Y-\WW')) }}">
                            </div>
                            <div class="mb-3 @if($filterType !== 'date') d-none @endif" id="dg">
                                <input type="date" name="date" class="form-control form-control-premium" value="{{ request('date', date('Y-m-d')) }}">
                            </div>
                            <button type="submit" class="btn btn-premium-primary w-100 py-2">Terapkan</button>
                        </form>
                    </div>
                </div>

                <!-- Stats -->
                <div class="col-md-8 col-lg-9">
                    <div class="glass-card p-4 h-100">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie me-1 text-primary"></i> {{ $selectedUser->nama }}</h6>
                        <div class="row align-items-center g-4">
                            <div class="col-sm-5 d-flex flex-column align-items-center text-center border-end">
                                <div class="ring-container">
                                    <svg class="w-100 h-100" viewBox="0 0 100 100">
                                        <circle class="ring-bg" cx="50" cy="50" r="40"/>
                                        @php $c = 2 * pi() * 40; $o = $c - ($stats['persentase']/100) * $c; @endphp
                                        <circle class="ring-fill" cx="50" cy="50" r="40" style="stroke-dasharray:{{ $c }}; stroke-dashoffset:{{ $o }};"/>
                                    </svg>
                                    <span class="ring-label">{{ $stats['persentase'] }}%</span>
                                </div>
                                <div class="fw-bold mt-2">Kehadiran</div>
                                <span class="text-muted small">{{ $stats['total_hari_kerja'] }} hari kerja</span>
                            </div>
                            <div class="col-sm-7">
                                <div class="row g-2">
                                    @foreach([['Hadir',$stats['hadir'],'fa-user-check','text-success','bg-success'],['WFH',$stats['wfh'],'fa-house-laptop','text-primary','bg-primary'],['Sakit',$stats['sakit'],'fa-notes-medical','text-danger','bg-danger'],['Izin',$stats['izin'],'fa-calendar-day','text-warning','bg-warning']] as $s)
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="rounded-3 p-2 {{ $s[4] }} bg-opacity-10 {{ $s[3] }}"><i class="fa-solid fa-{{ $s[2] }}"></i></div>
                                            <div><div class="fw-bold fs-5">{{ $s[1] }}</div><span class="text-muted small">{{ $s[0] }}</span></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="glass-card p-4">
                <h6 class="fw-bold mb-4"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> Riwayat Laporan</h6>
                @if ($absensi->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-regular fa-folder-open fs-1 d-block mb-3 opacity-25"></i>
                        <p class="fw-semibold mb-1">Tidak ada data</p>
                        <span class="small">Tidak ditemukan riwayat absensi dalam rentang filter ini.</span>
                    </div>
                @else
                    <div class="tl-wrap">
                        @foreach ($absensi as $rec)
                        <div class="tl-item">
                            <div class="tl-dot tl-dot-{{ $rec->status }}"></div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small">{{ \Carbon\Carbon::parse($rec->tanggal)->translatedFormat('l, d F Y') }}</span>
                                <span class="badge badge-status badge-{{ $rec->status }}">{{ $rec->status }}</span>
                            </div>
                            <div class="row align-items-start g-2">
                                <div class="col">
                                    <p class="text-muted small mb-1" style="line-height:1.6;">{{ $rec->laporan }}</p>
                                    <span class="text-muted" style="font-size:0.72rem;"><i class="fa-regular fa-clock me-1"></i>{{ $rec->created_at->format('H:i') }} WIB</span>
                                </div>
                                @if ($rec->foto)
                                <div class="col-auto">
                                    <a href="{{ asset($rec->foto) }}" target="_blank" class="d-block rounded-3 overflow-hidden border" style="width:64px;height:64px;">
                                        <img src="{{ asset($rec->foto) }}" class="w-100 h-100 object-fit-cover" alt="Bukti">
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @else
                <div class="glass-card p-5 text-center">
                    <i class="fa-regular fa-hand fs-1 d-block mb-3 text-primary opacity-50"></i>
                    <h5 class="fw-bold">Selamat datang</h5>
                    <p class="text-muted small col-md-7 mx-auto">Pilih nama pegawai di atas untuk melihat statistik kehadiran dan riwayat laporan kerja harian.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ft = document.getElementById('filter_type');
    const wg = document.getElementById('wg');
    const dg = document.getElementById('dg');
    if (ft) ft.addEventListener('change', function() {
        wg.classList.toggle('d-none', this.value !== 'week');
        dg.classList.toggle('d-none', this.value !== 'date');
    });
});
</script>
@endsection
