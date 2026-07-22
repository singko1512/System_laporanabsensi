@extends('layouts.app')

@section('title', 'Admin Dashboard - Absensi Harian')

@section('styles')
<style>
    .tab-pill { display: inline-flex; gap: 0.25rem; background: var(--bg); border: 1px solid var(--border); border-radius: 14px; padding: 4px; }
    .tab-pill .nav-link { border: none; border-radius: 12px; padding: 0.6rem 1.25rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); transition: all 0.2s; }
    .tab-pill .nav-link.active { background: var(--dark); color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
    .tab-pill .nav-link:not(.active):hover { color: var(--dark); }

    .clean-table { border-collapse: separate; border-spacing: 0 6px; }
    .clean-table thead th {
        background: var(--dark); color: #fff; font-size: 0.78rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px; padding: 0.9rem 1rem; border: none;
    }
    .clean-table thead th:first-child { border-radius: 12px 0 0 12px; }
    .clean-table thead th:last-child { border-radius: 0 12px 12px 0; }
    .clean-table tbody tr { background: var(--white); transition: all 0.2s; }
    .clean-table tbody tr:hover { background: #fafbff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .clean-table td { padding: 0.9rem 1rem; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); font-size: 0.9rem; }
    .clean-table td:first-child { border-left: 1px solid var(--border); border-radius: 12px 0 0 12px; }
    .clean-table td:last-child { border-right: 1px solid var(--border); border-radius: 0 12px 12px 0; }

    .modal-clean .modal-content { border-radius: 24px !important; border: 1px solid var(--border) !important; box-shadow: 0 20px 60px rgba(0,0,0,0.1) !important; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="glass-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="header-badge"><i class="fa-solid fa-shield-halved me-1"></i> Administrator</span>
                <h2 class="fw-bold mb-1" style="letter-spacing:-0.5px;">Panel Kontrol Admin</h2>
                <p class="text-muted small mb-0">Kelola kepegawaian, pantau rekap, dan ekspor laporan.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('home') }}" class="btn btn-premium-secondary py-2"><i class="fa-solid fa-arrow-left me-1"></i> Beranda</a>
                <a href="{{ route('admin.logout') }}" class="btn btn-dark py-2 rounded-3 px-4"><i class="fa-solid fa-power-off me-1"></i> Keluar</a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="glass-card p-4">
        <ul class="nav tab-pill mb-4" id="adminTab" role="tablist">
            <li><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#rekap-panel"><i class="fa-solid fa-table-list me-1"></i> Rekap Bulanan</button></li>
            <li><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pegawai-panel"><i class="fa-solid fa-users me-1"></i> Kelola Pegawai</button></li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: Rekap -->
            <div class="tab-pane fade show active" id="rekap-panel">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 p-3 rounded-3" style="background:var(--bg); border:1px solid var(--border);">
                    <span class="text-muted small"><i class="fa-solid fa-business-time me-1"></i> Hari kerja aktif: <strong>{{ $totalWorkdays }} hari</strong></span>
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex gap-2 align-items-center">
                        <select name="month" class="form-select form-select-premium py-1 px-2" style="width:auto; font-size:0.85rem;">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select name="year" class="form-select form-select-premium py-1 px-2" style="width:auto; font-size:0.85rem;">
                            @for ($y = date('Y')-3; $y <= date('Y')+1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="btn btn-premium-primary py-1 px-3"><i class="fa-solid fa-rotate"></i></button>
                    </form>
                </div>

                <div class="d-flex gap-2 mb-3 justify-content-end">
                    <a href="{{ route('admin.rekap.excel', ['month'=>$month,'year'=>$year]) }}" class="btn btn-success btn-sm rounded-3 px-3"><i class="fa-solid fa-file-excel me-1"></i> Excel</a>
                    <a href="{{ route('admin.rekap.pdf', ['month'=>$month,'year'=>$year]) }}" class="btn btn-danger btn-sm rounded-3 px-3"><i class="fa-solid fa-file-pdf me-1"></i> PDF</a>
                </div>

                <div class="table-responsive">
                    <table class="table clean-table align-middle mb-0">
                        <thead><tr>
                            <th class="text-center" style="width:4%">No</th>
                            <th>Nama</th><th>NIP/ID</th>
                            <th class="text-center">Hadir</th><th class="text-center">WFH</th>
                            <th class="text-center">Sakit</th><th class="text-center">Izin</th>
                            <th class="text-center" style="width:16%">%</th>
                            <th class="text-center" style="width:9%">Aksi</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($rekapData as $i => $d)
                            <tr>
                                <td class="text-center fw-bold">{{ $i+1 }}</td>
                                <td class="fw-bold">{{ $d->user->nama }}</td>
                                <td>{{ $d->user->nip_atau_id ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">{{ $d->hadir }}</span></td>
                                <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">{{ $d->wfh }}</span></td>
                                <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">{{ $d->sakit }}</span></td>
                                <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2">{{ $d->izin }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 justify-content-center">
                                        <div class="progress flex-grow-1" style="height:5px;min-width:40px;">
                                            <div class="progress-bar" style="width:{{ $d->persentase }}%;background:var(--primary);"></div>
                                        </div>
                                        <span class="fw-bold small">{{ $d->persentase }}%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick='showLogs({{ json_encode($d->user->nama) }}, {{ json_encode($d->absensi) }})'>Detail</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Pegawai -->
            <div class="tab-pane fade" id="pegawai-panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-users me-1 text-primary"></i> Daftar Pegawai</h6>
                    <button class="btn btn-premium-primary btn-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
                </div>
                <div class="table-responsive">
                    <table class="table clean-table align-middle mb-0">
                        <thead><tr>
                            <th class="text-center" style="width:4%">No</th>
                            <th>Nama</th><th>NIP/ID</th><th>Tgl Ditambahkan</th>
                            <th class="text-center" style="width:14%">Aksi</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($users as $i => $u)
                            <tr>
                                <td class="text-center fw-bold">{{ $i+1 }}</td>
                                <td class="fw-bold">{{ $u->nama }}</td>
                                <td>{{ $u->nip_atau_id ?? '-' }}</td>
                                <td>{{ $u->created_at ? $u->created_at->translatedFormat('d F Y') : '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" onclick="editUser({{ $u->id }},'{{ $u->nama }}','{{ $u->nip_atau_id }}')"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmDel(event,'{{ route('admin.user.destroy', $u->id) }}')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada pegawai.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Tambah -->
<div class="modal fade modal-clean" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i>Tambah Pegawai</h5>
            <form action="{{ route('admin.user.store') }}" method="POST">
                @csrf
                <div class="mb-3"><label class="form-label form-label-premium">Nama <span class="text-danger">*</span></label><input type="text" name="nama" class="form-control form-control-premium" required></div>
                <div class="mb-4"><label class="form-label form-label-premium">NIP / ID</label><input type="text" name="nip_atau_id" class="form-control form-control-premium"></div>
                <div class="d-flex gap-2"><button type="submit" class="btn btn-premium-primary flex-grow-1">Simpan</button><button type="button" class="btn btn-premium-secondary" data-bs-dismiss="modal">Batal</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit -->
<div class="modal fade modal-clean" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Pegawai</h5>
            <form action="" method="POST" id="editForm">
                @csrf
                <div class="mb-3"><label class="form-label form-label-premium">Nama <span class="text-danger">*</span></label><input type="text" name="nama" id="e_nama" class="form-control form-control-premium" required></div>
                <div class="mb-4"><label class="form-label form-label-premium">NIP / ID</label><input type="text" name="nip_atau_id" id="e_nip" class="form-control form-control-premium"></div>
                <div class="d-flex gap-2"><button type="submit" class="btn btn-premium-primary flex-grow-1">Simpan</button><button type="button" class="btn btn-premium-secondary" data-bs-dismiss="modal">Batal</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Detail Logs -->
<div class="modal fade modal-clean" id="logsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-regular fa-folder-open text-primary me-2"></i>Detail: <span id="logName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="logContent" style="max-height:400px; overflow-y:auto;"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editUser(id, nama, nip) {
    document.getElementById('editForm').action = "{{ url('admin/pegawai/update') }}/" + id;
    document.getElementById('e_nama').value = nama;
    document.getElementById('e_nip').value = nip || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function confirmDel(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus pegawai?',
        text: 'Seluruh data absensi pegawai ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e17055',
        cancelButtonColor: '#b2bec3',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 border-0 shadow-lg', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
    }).then(r => { if (r.isConfirmed) window.location.href = url; });
}

function showLogs(name, logs) {
    document.getElementById('logName').innerText = name;
    const c = document.getElementById('logContent');
    if (!logs || !logs.length) {
        c.innerHTML = '<div class="text-center py-5 text-muted"><i class="fa-regular fa-folder-open fs-2 d-block mb-2 opacity-25"></i><p class="fw-semibold small">Belum ada data.</p></div>';
    } else {
        let h = '';
        logs.forEach(l => {
            const d = new Date(l.tanggal).toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
            const badge = `<span class="badge badge-status badge-${l.status}">${l.status}</span>`;
            const foto = l.foto ? `<a href="{{ asset('') }}${l.foto}" target="_blank" class="d-block rounded-3 overflow-hidden border mt-2" style="width:60px;height:60px;"><img src="{{ asset('') }}${l.foto}" class="w-100 h-100 object-fit-cover"></a>` : '';
            h += `<div style="padding:1rem 0; border-bottom:1px solid var(--border);">
                <div class="d-flex justify-content-between align-items-center mb-1"><span class="fw-bold small">${d}</span>${badge}</div>
                <p class="text-muted small mb-0" style="line-height:1.6;">${l.laporan||'-'}</p>${foto}</div>`;
        });
        c.innerHTML = h;
    }
    new bootstrap.Modal(document.getElementById('logsModal')).show();
}
</script>
@endsection
