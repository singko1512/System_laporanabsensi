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
        border-radius: 24px;
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
    .schedule-section {
        max-width: 920px;
        margin: 3rem auto 2rem;
    }

    .schedule-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .schedule-header {
        padding: 1.5rem 1.75rem 1rem;
        border-bottom: 1px solid var(--border);
    }

    .schedule-header h3 {
        font-size: 1.1rem;
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

    .schedule-table {
        width: 100%;
        border-collapse: collapse;
    }

    .schedule-table thead th {
        background: #f1f5f9;
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
        background: rgba(108, 92, 231, 0.1);
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
        gap: 1.25rem;
        padding: 0.85rem 1.5rem;
        border-top: 1px solid var(--border);
        font-size: 0.78rem;
        color: var(--text-muted);
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
        <a href="{{ route('absensi.index') }}" class="feature-card card-employee">
            <div class="feature-icon">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
            <div class="card-label">Untuk Karyawan</div>
            <div class="card-title">Menu Absensi & Laporan</div>
            <div class="card-desc">
                Isi absensi harian, unggah foto lokasi, dan kirim laporan pekerjaan Anda.
            </div>
            <span class="card-cta">Mulai sekarang <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <!-- Admin Card -->
        @if(session('admin_authenticated'))
            <a href="{{ route('admin.dashboard') }}" class="feature-card card-admin">
                <div class="feature-icon">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
                <div class="card-label">Untuk Admin</div>
                <div class="card-title">Akses Admin</div>
                <div class="card-desc">
                    Kelola data karyawan, pantau rekap, dan ekspor laporan.
                    Diperlukan PIN 6-digit.
                </div>
                <span class="card-cta">Buka Dashboard <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        @else
            <a href="javascript:void(0)" onclick="triggerAdminLogin()" class="feature-card card-admin">
                <div class="feature-icon">
                    <i class="fa-regular fa-circle-check"></i>
                </div>
                <div class="card-label">Untuk Admin</div>
                <div class="card-title">Akses Admin</div>
                <div class="card-desc">
                    Kelola data karyawan, pantau rekap, dan ekspor laporan.
                    Diperlukan PIN 6-digit.
                </div>
                <span class="card-cta">Masuk dengan PIN <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        @endif
    </div>

    {{-- Jadwal Mingguan --}}
    <div class="schedule-section">
        <div class="schedule-card">
            <div class="schedule-header">
                <h3><i class="fa-solid fa-calendar-week me-2 text-primary"></i>Jadwal Minggu Ini</h3>
                <p>{{ $weekStart->translatedFormat('d F') }} – {{ $weekEnd->translatedFormat('d F Y') }}</p>
            </div>

            @if ($users->isEmpty())
                <div class="schedule-empty">
                    <h6>Belum ada jadwal</h6>
                    <p>Jadwal WFO/WFH akan tampil setelah admin menambahkan pegawai dan mengatur jadwal.</p>
                </div>
            @else
                @php
                    $dayLabels = [
                        'senin' => 'Senin',
                        'selasa' => 'Selasa',
                        'rabu' => 'Rabu',
                        'kamis' => 'Kamis',
                        'jumat' => 'Jumat',
                    ];
                @endphp
                <div class="table-responsive">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                @foreach ($dayLabels as $key => $label)
                                    <th class="{{ $todayKey === $key ? 'is-today' : '' }}">
                                        {{ $label }}
                                        <span class="day-date">{{ $dayMap[$key]->format('d/m') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php $jadwal = $user->jadwalMingguan; @endphp
                                <tr>
                                    <td>{{ $user->nama }}</td>
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
                <div class="schedule-legend">
                    <span><span class="loc-badge loc-wfo">WFO</span> Work From Office (Masuk Kantor)</span>
                    <span><span class="loc-badge loc-wfh">WFH</span> Work From Home</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
