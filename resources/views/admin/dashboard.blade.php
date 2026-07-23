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
                <i class="fa-solid fa-users"></i> Kelola Pegawai
            </button>
            <button type="button" class="tab-btn" data-tab="jadwal">
                <i class="fa-solid fa-calendar-week"></i> Jadwal Mingguan
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
                        <input type="text" name="search" class="search-input" placeholder="Cari nama karyawan..." value="{{ $search }}">
                    </div>
                    <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="all" {{ ($status === '' || $status === 'all') ? 'selected' : '' }}>Semua status</option>
                        <option value="hadir" {{ $status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="wfh" {{ $status === 'wfh' ? 'selected' : '' }}>WFH</option>
                        <option value="sakit" {{ $status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ $status === 'izin' ? 'selected' : '' }}>Izin</option>
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
                                    <th>Karyawan</th>
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Status</th>
                                    <th>Lampiran</th>
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
                                                {{ $rec->created_at ? $rec->created_at->format('H:i') . ' WIB' : '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-{{ $rec->status }}">{{ strtoupper($rec->status) }}</span>
                                        </td>
                                        <td>
                                            @if ($rec->foto)
                                                <a href="{{ asset($rec->foto) }}" target="_blank" title="Lihat lampiran">
                                                    <img src="{{ asset($rec->foto) }}" alt="Lampiran" class="attachment-thumb">
                                                </a>
                                            @elseif ($rec->laporan)
                                                <span class="text-muted" style="font-size:0.82rem;" title="{{ $rec->laporan }}">
                                                    <i class="fa-regular fa-file-lines me-1"></i> Laporan
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size:0.82rem;">—</span>
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
    </div>

    {{-- TAB: Kelola Pegawai --}}
    <div class="tab-panel d-none" id="panel-pegawai">
        <div class="admin-card overflow-hidden">
            <div class="p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0" style="color:var(--dark);">
                    <i class="fa-solid fa-users me-1" style="color:var(--primary);"></i> Daftar Pegawai
                </h6>
                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa-solid fa-plus"></i> Tambah Pegawai
                </button>
            </div>

            @if ($users->isEmpty())
                <div class="empty-state">
                    <h6>Belum ada pegawai terdaftar</h6>
                    <p>Tambahkan pegawai baru untuk memulai pencatatan absensi.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:5%;">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal Ditambahkan</th>
                                <th style="width:12%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $i => $u)
                                <tr>
                                    <td class="text-muted fw-semibold">{{ $i + 1 }}</td>
                                    <td class="fw-semibold" style="color:var(--dark);">{{ $u->nama }}</td>
                                    <td>{{ $u->email ?? '—' }}</td>
                                    <td>{{ $u->created_at ? $u->created_at->translatedFormat('d F Y') : '—' }}</td>
                                    <td>
                                        <button type="button" class="btn-action me-1" onclick="editUser({{ $u->id }}, {{ json_encode($u->nama) }}, {{ json_encode($u->email) }})" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="#" class="btn-action danger" onclick="confirmDel(event, '{{ route('admin.user.destroy', $u->id) }}')" title="Hapus">
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
                            Atur jadwal Sen–Kam manual. Jumat otomatis WFH untuk semua pegawai.
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
                                Pegawai dibagi acak: setengah Sen/Rab WFH, setengah Sen/Rab WFO. Jumat tetap WFH.
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
                    <h6>Belum ada pegawai</h6>
                    <p>Tambahkan pegawai terlebih dahulu di tab Kelola Pegawai.</p>
                </div>
            @else
                <form action="{{ route('admin.jadwal.update') }}" method="POST" id="scheduleForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
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
                                                    <option value="wfo" {{ ($jadwal?->$day ?? 'wfo') === 'wfo' ? 'selected' : '' }}>WFO</option>
                                                    <option value="wfh" {{ ($jadwal?->$day ?? 'wfo') === 'wfh' ? 'selected' : '' }}>WFH</option>
                                                </select>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span class="jumat-fixed">WFH</span>
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
</div>

{{-- Modal: Tambah Pegawai --}}
<div class="modal fade modal-clean" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-plus me-2" style="color:var(--primary);"></i>Tambah Pegawai</h5>
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
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">Simpan</button>
                    <button type="button" class="btn-logout" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Pegawai --}}
<div class="modal fade modal-clean" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-pen me-2" style="color:var(--primary);"></i>Edit Pegawai</h5>
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
});

function confirmRandomSchedule(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Acak jadwal mingguan?',
        text: 'Semua pegawai akan dibagi acak antara pola Sen/Rab WFH dan Sen/Rab WFO. Jumat tetap WFH.',
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

function editUser(id, nama, email) {
    document.getElementById('editForm').action = "{{ url('admin/pegawai/update') }}/" + id;
    document.getElementById('e_nama').value = nama;
    document.getElementById('e_email').value = email || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function confirmDel(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus pegawai?',
        text: 'Seluruh data absensi pegawai ini akan dihapus permanen.',
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
