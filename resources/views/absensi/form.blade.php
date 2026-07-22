@extends('layouts.app')

@section('title', 'Form Absensi - Absensi Harian')

@section('styles')
<style>
    .status-option {
        border: 1.5px solid var(--border);
        border-radius: 16px;
        background: var(--white);
        padding: 0.85rem 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    .status-option:hover {
        border-color: #d1d5db;
        background: #fafbff;
    }
    .btn-check:checked + .status-option {
        border-color: var(--primary);
        background: rgba(108, 92, 231, 0.05);
        box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
    }
    .status-option .icon { font-size: 1.35rem; margin-bottom: 0.35rem; display: block; }

    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 16px;
        padding: 2rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        background: #fafbff;
    }
    .upload-zone:hover { border-color: var(--primary); background: rgba(108,92,231,0.02); }
    .upload-zone i.cloud { font-size: 2rem; color: var(--text-light); margin-bottom: 0.5rem; transition: color 0.2s; }
    .upload-zone:hover i.cloud { color: var(--primary); }
    .upload-zone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .file-name { display: none; margin-top: 0.75rem; font-weight: 600; font-size: 0.82rem; color: var(--primary); }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="text-center mb-4">
                <span class="header-badge"><i class="fa-regular fa-pen-to-square me-1"></i> Form Absensi</span>
                <h2 class="fw-bold" style="letter-spacing: -0.5px;">Presensi Harian</h2>
                <p class="text-muted small">Pilih nama, tentukan status, dan kirim laporan Anda.</p>
            </div>

            <div class="glass-card p-4">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 mb-3 small">
                        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('absensi.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="user_id" class="form-label form-label-premium">Nama Pegawai <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select form-select-premium" required>
                            <option value="" disabled selected>-- Pilih Nama --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->nama }} @if($user->nip_atau_id)({{ $user->nip_atau_id }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-premium">Status <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @foreach([['hadir','fa-check-double','Hadir','text-success'],['wfh','fa-house-laptop','WFH','text-primary'],['sakit','fa-notes-medical','Sakit','text-danger'],['izin','fa-calendar-day','Izin','text-warning']] as $s)
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="status" id="status_{{ $s[0] }}" value="{{ $s[0] }}" {{ old('status', 'hadir') == $s[0] ? 'checked' : '' }} autocomplete="off">
                                <label class="status-option w-100" for="status_{{ $s[0] }}">
                                    <i class="fa-solid fa-{{ $s[1] }} icon {{ $s[3] }}"></i>
                                    <span class="small fw-bold d-block">{{ $s[2] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3" id="photo_section">
                        <label id="foto_label" class="form-label form-label-premium">Foto Bukti <span class="text-danger" id="foto_star">*</span></label>
                        <div class="upload-zone">
                            <i class="fa-solid fa-cloud-arrow-up cloud d-block"></i>
                            <div class="small fw-semibold text-dark" id="upload_text">Klik untuk unggah gambar</div>
                            <div class="text-muted" style="font-size:0.72rem;">PNG, JPG, JPEG · Maks 2 MB</div>
                            <input type="file" name="foto" id="foto" accept="image/*">
                            <div class="file-name" id="file_name"><i class="fa-solid fa-circle-check me-1"></i><span id="fname"></span></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="laporan" id="laporan_label" class="form-label form-label-premium">Laporan Pekerjaan <span class="text-danger">*</span></label>
                        <textarea name="laporan" id="laporan" rows="3" class="form-control form-control-premium" placeholder="Tuliskan laporan aktivitas hari ini..." required>{{ old('laporan') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-premium-primary w-100 py-3">
                        <i class="fa-solid fa-paper-plane me-2"></i> Kirim Absensi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="status"]');
    const fotoLabel = document.getElementById('foto_label');
    const fotoInput = document.getElementById('foto');
    const laporanLabel = document.getElementById('laporan_label');
    const laporanInput = document.getElementById('laporan');
    const fileNameDiv = document.getElementById('file_name');
    const fnameSpan = document.getElementById('fname');
    const uploadText = document.getElementById('upload_text');

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

    function update() {
        const v = document.querySelector('input[name="status"]:checked').value;
        const map = {
            hadir: ['Foto Bukti Kehadiran', true, 'Laporan Pekerjaan Harian', 'Deskripsi pekerjaan yang diselesaikan hari ini...'],
            wfh: ['Foto Bukti WFH', true, 'Laporan Progres WFH', 'Progres pekerjaan dari rumah...'],
            sakit: ['Surat Keterangan Sakit', true, 'Keterangan Sakit', 'Rincian kondisi kesehatan...'],
            izin: ['Foto Pendukung (Opsional)', false, 'Alasan Izin', 'Alasan pengajuan izin...']
        };
        const m = map[v];
        fotoLabel.innerHTML = m[0] + (m[1] ? ' <span class="text-danger">*</span>' : '');
        fotoInput.required = m[1];
        laporanLabel.innerHTML = m[2] + ' <span class="text-danger">*</span>';
        laporanInput.placeholder = m[3];
    }

    radios.forEach(r => r.addEventListener('change', update));
    update();
});
</script>
@endsection
