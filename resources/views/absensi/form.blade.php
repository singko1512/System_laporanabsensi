@extends('layouts.app')

@section('title', 'Form Absensi - SIMALAM')

@section('styles')
<style>
    .status-option {
        border: 1.5px solid var(--border);
        border-radius: 10px;
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
        border-radius: 10px;
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
        border-radius: 10px;
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
        border-radius: 10px;
        background: #fff;
    }
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
                        <label for="user_id" class="form-label form-label-premium">Nama Peserta Magang <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select form-select-premium" required>
                            <option value="" disabled selected>-- Pilih Nama --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->nama }} @if($user->email)({{ $user->email }})@endif
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
                        <label id="foto_label" class="form-label form-label-premium">Lampiran</label>
                        <div class="upload-zone">
                            <i class="fa-solid fa-cloud-arrow-up cloud d-block"></i>
                            <div class="small fw-semibold text-dark" id="upload_text">Klik untuk unggah gambar</div>
                            <div class="text-muted" style="font-size:0.72rem;">PNG, JPG, JPEG, WEBP - Maks 5 MB</div>
                            <input type="file" name="foto" id="foto" accept="image/*">
                            <div class="file-name" id="file_name"><i class="fa-solid fa-circle-check me-1"></i><span id="fname"></span></div>
                            <img src="" class="upload-preview" id="foto_preview" alt="Pratinjau lampiran">
                        </div>
                    </div>

                    <div class="mb-3" id="camera_section">
                        <label class="form-label form-label-premium">Foto Kamera <span class="text-danger">*</span></label>
                        <input type="file" name="foto_kamera" id="foto_kamera" accept="image/*" class="d-none">
                        <div class="camera-start-actions" id="camera_start_actions">
                            <span class="camera-message">Nyalakan kamera lalu ambil foto untuk Hadir/WFH.</span>
                            <button type="button" class="btn btn-premium-secondary py-2 px-3" id="start_camera">
                                <i class="fa-solid fa-video me-1"></i> Nyalakan Kamera
                            </button>
                        </div>
                        <div class="camera-panel" id="camera_panel">
                            <video id="camera_video" autoplay playsinline muted></video>
                            <img src="" class="camera-preview" id="camera_preview" alt="Pratinjau foto kamera">
                            <canvas id="camera_canvas" class="d-none"></canvas>
                            <div class="camera-actions">
                                <span class="camera-message" id="camera_message">Kamera aktif untuk Hadir/WFH.</span>
                                <button type="button" class="btn btn-premium-primary py-2 px-3" id="capture_photo">
                                    <i class="fa-solid fa-camera me-1"></i> Ambil Foto
                                </button>
                            </div>
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
    const cameraMessage = document.getElementById('camera_message');
    const cameraPreview = document.getElementById('camera_preview');
    const capturePhoto = document.getElementById('capture_photo');
    let previewUrl = null;
    let cameraPreviewUrl = null;
    let cameraStream = null;

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
            cameraMessage.innerText = 'Kamera tidak bisa dibuka. Unggah gambar secara manual.';
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

    if (attendanceForm) {
        attendanceForm.addEventListener('submit', function(e) {
            const checked = document.querySelector('input[name="status"]:checked');
            const needsCamera = checked && ['hadir', 'wfh'].includes(checked.value);

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
            }
        });
    }

    function update() {
        const v = document.querySelector('input[name="status"]:checked').value;
        const map = {
            hadir: ['Lampiran Tambahan (Opsional)', false, 'Laporan Pekerjaan Harian', 'Deskripsi pekerjaan yang diselesaikan hari ini...'],
            wfh: ['Lampiran Tambahan (Opsional)', false, 'Laporan Progres WFH', 'Progres pekerjaan dari rumah...'],
            sakit: ['Surat Keterangan Sakit', true, 'Keterangan Sakit', 'Rincian kondisi kesehatan...'],
            izin: ['Lampiran Izin (Opsional)', false, 'Alasan Izin', 'Alasan pengajuan izin...']
        };
        const m = map[v];
        fotoLabel.innerHTML = m[0] + (m[1] ? ' <span class="text-danger">*</span>' : '');
        fotoInput.required = m[1];
        laporanLabel.innerHTML = m[2] + ' <span class="text-danger">*</span>';
        laporanInput.placeholder = m[3];
        if (['hadir', 'wfh'].includes(v)) {
            if (cameraSection) cameraSection.style.display = 'block';
            if (!cameraStream && cameraPanel) cameraPanel.style.display = 'none';
            if (!cameraStream && cameraStartActions) cameraStartActions.style.display = 'flex';
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
    }

    radios.forEach(r => r.addEventListener('change', update));
    update();
});
</script>
@endsection
