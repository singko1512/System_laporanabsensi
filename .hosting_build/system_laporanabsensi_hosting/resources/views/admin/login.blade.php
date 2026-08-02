@extends('layouts.admin')

@section('title', $activeAuthMode === 'register' ? 'Register Peserta Magang' : ($activeAuthMode === 'forgot' ? 'Lupa Password Peserta' : $title))

@section('styles')
<style>
    .login-shell {
        min-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .login-card {
        width: 100%;
        max-width: var(--card-max-width, 460px);
        padding: 2.75rem 2.5rem;
        border: 1px solid rgba(99, 102, 241, 0.08);
        border-radius: 12px;
        box-shadow: 0 12px 40px rgba(99, 102, 241, 0.06), 0 2px 4px rgba(0, 0, 0, 0.01);
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease, box-shadow 0.3s ease;
    }

    .login-card:hover {
        box-shadow: 0 16px 48px rgba(99, 102, 241, 0.1), 0 4px 8px rgba(0, 0, 0, 0.02);
    }

    .login-role {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.08);
        color: var(--primary);
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .auth-switch {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.25rem;
        padding: 0.3rem;
        margin-bottom: 1.6rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #f8fafc;
    }

    .auth-switch a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 42px;
        border-radius: 12px;
        color: var(--text-muted);
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .auth-switch a:hover:not(.active) {
        color: var(--dark);
        background: rgba(15, 23, 42, 0.03);
    }

    .auth-switch a.active {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }

    .form-control-admin {
        height: 46px;
        border-radius: 12px;
        padding: 0.65rem 1rem;
        border: 1px solid var(--border);
        background: #f8fafc;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--dark);
        transition: all 0.2s ease;
    }

    .form-control-admin:focus {
        background: #fff;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }

    textarea.form-control-admin {
        height: auto;
    }

    .form-label-admin {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.45rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .login-actions,
    .register-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .register-actions {
        justify-content: flex-end;
    }

    .dev-credentials-info {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid var(--border);
        border-left: 4px solid var(--primary-light);
        border-radius: 10px;
        padding: 1.25rem;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 1.5rem;
    }

    .dev-credentials-info strong {
        color: var(--dark);
    }

    .dev-credentials-title {
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.84rem;
    }

    .dev-credentials-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .dev-credentials-item {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        line-height: 1.4;
    }

    .dev-credentials-item i {
        color: var(--primary);
        font-size: 0.7rem;
        margin-top: 0.25rem;
    }

    @media (max-width: 576px) {
        .login-card { 
            padding: 1.75rem 1.5rem; 
        }
        .login-actions,
        .register-actions { 
            flex-direction: column; 
            align-items: stretch; 
        }
    }
</style>
@endsection

@section('content')
<div class="login-shell">
    <div class="admin-card login-card" style="--card-max-width: {{ $activeAuthMode === 'register' ? '780px' : ($activeAuthMode === 'forgot' ? '520px' : '460px') }}">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="admin-logo">
                <i class="fa-solid {{ $activeAuthMode === 'register' ? 'fa-user-plus' : ($activeAuthMode === 'forgot' ? 'fa-key' : ($loginRole === 'superadmin' ? 'fa-shield-halved' : 'fa-user-tie')) }}"></i>
            </div>
            <div>
                <span class="login-role">
                    {{ in_array($activeAuthMode, ['register', 'forgot'], true) ? 'Peserta Magang' : ($loginRole === 'superadmin' ? 'Super Admin' : ($loginRole === 'admin' ? 'Admin' : 'Akun Sistem')) }}
                </span>
                <h1 class="fw-bold mb-1 mt-2" style="font-size:1.3rem;color:var(--dark);letter-spacing:-0.2px;">
                    {{ $activeAuthMode === 'register' ? 'Register Akun' : ($activeAuthMode === 'forgot' ? 'Lupa Password' : $title) }}
                </h1>
                @if($activeAuthMode !== 'login')
                    <p class="text-muted mb-0" style="font-size:0.82rem;line-height:1.4;">
                        {{ $activeAuthMode === 'register' ? 'Buat akun peserta untuk akses presensi harian dan tugas magang.' : 'Masukkan nama lengkap dan email yang dipakai saat registrasi.' }}
                    </p>
                @endif
            </div>
        </div>

        @if(! in_array($loginRole, ['admin', 'superadmin'], true) && $activeAuthMode !== 'forgot')
            <div class="auth-switch">
                <a href="{{ route('login.form') }}" class="{{ $activeAuthMode === 'login' ? 'active' : '' }}">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </a>
                <a href="{{ route('login.form', ['mode' => 'register']) }}" class="{{ $activeAuthMode === 'register' ? 'active' : '' }}">
                    <i class="fa-solid fa-user-plus"></i> Daftar
                </a>
            </div>
        @endif

        @if($activeAuthMode === 'register')
            <form action="{{ route('register.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-admin">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control form-control-admin w-100" value="{{ old('nama') }}" required autofocus>
                        @error('nama')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Email</label>
                        <input type="email" name="email" class="form-control form-control-admin w-100" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Bidang Magang</label>
                        <select name="bidang_id" id="register_bidang_id" class="form-select form-control-admin w-100" required>
                            <option value="" disabled {{ old('bidang_id') ? '' : 'selected' }}>Pilih bidang...</option>
                            @foreach ($bidangOptions as $bidang)
                                <option value="{{ $bidang->id }}" {{ (string) old('bidang_id') === (string) $bidang->id ? 'selected' : '' }}>
                                    {{ $bidang->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('bidang_id')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Pembimbing Magang</label>
                        <select name="pembimbing_magang_id" id="register_pembimbing_magang_id" class="form-select form-control-admin w-100" required disabled>
                            <option value="" disabled selected>Pilih bidang terlebih dahulu...</option>
                            @foreach ($pembimbingOptions as $pembimbing)
                                <option value="{{ $pembimbing->id }}" data-bidang-id="{{ $pembimbing->bidang_id }}" {{ (string) old('pembimbing_magang_id') === (string) $pembimbing->id ? 'selected' : '' }}>
                                    {{ $pembimbing->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('pembimbing_magang_id')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Mulai Magang</label>
                        <input type="date" name="tanggal_mulai_magang" class="form-control form-control-admin w-100" value="{{ old('tanggal_mulai_magang') }}">
                        @error('tanggal_mulai_magang')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Selesai Magang</label>
                        <input type="date" name="tanggal_selesai_magang" class="form-control form-control-admin w-100" value="{{ old('tanggal_selesai_magang') }}">
                        @error('tanggal_selesai_magang')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Password</label>
                        <input type="password" name="password" class="form-control form-control-admin w-100" autocomplete="new-password" required>
                        @error('password')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-admin">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-admin w-100" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="register-actions mt-4 pt-3 border-top" style="border-color:var(--border)!important;">
                    <a href="{{ route('login.form') }}" class="btn-logout justify-content-center">
                        <i class="fa-solid fa-right-to-bracket"></i> Sudah Punya Akun
                    </a>
                    <button type="submit" class="btn-add justify-content-center">
                        <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
                    </button>
                </div>
            </form>
        @elseif($activeAuthMode === 'forgot')
            @if(!empty($verifiedResetUser))
                <div class="alert alert-success py-2 px-3 rounded-3 small mb-3">
                    <i class="fa-solid fa-circle-check me-1"></i>
                    Data ditemukan untuk <strong>{{ $verifiedResetUser->nama }}</strong>. Buat password baru untuk akun ini.
                </div>
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-admin">Email Terdaftar</label>
                        <input type="email" class="form-control form-control-admin w-100" value="{{ $verifiedResetUser->email }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-admin">Password Baru</label>
                        <input type="password" name="password" class="form-control form-control-admin w-100" autocomplete="new-password" placeholder="Minimal 6 karakter" required autofocus>
                        @error('password')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label-admin">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-admin w-100" autocomplete="new-password" placeholder="Ulangi password baru" required>
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="btn-add flex-grow-1 justify-content-center">
                            <i class="fa-solid fa-key"></i> Simpan Password Baru
                        </button>
                        <a href="{{ route('password.request', ['reset' => 1]) }}" class="btn-logout justify-content-center">
                            <i class="fa-solid fa-rotate-left"></i> Ganti Data
                        </a>
                    </div>
                </form>
            @else
                <form action="{{ route('password.verify') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-admin">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control form-control-admin w-100" value="{{ old('nama') }}" autocomplete="name" placeholder="Nama sesuai registrasi" required autofocus>
                        @error('nama')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label-admin">Email Terdaftar</label>
                        <input type="email" name="email" class="form-control form-control-admin w-100" value="{{ old('email') }}" autocomplete="email" placeholder="email@contoh.com" required>
                        @error('email')
                            <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="btn-add flex-grow-1 justify-content-center">
                            <i class="fa-solid fa-magnifying-glass"></i> Cek Data
                        </button>
                        <a href="{{ route('login.form') }}" class="btn-logout justify-content-center">
                            <i class="fa-solid fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            @endif
        @else
            <form action="{{ $action }}" method="POST">
                @csrf
                @if(in_array($loginRole, ['admin', 'superadmin'], true))
                    <input type="hidden" name="expected_role" value="{{ $loginRole }}">
                @endif
                <div class="mb-3">
                    @php
                        $isAdminLoginRole = in_array($loginRole, ['admin', 'superadmin'], true);
                        $loginLabel = $isAdminLoginRole
                            ? ($loginRole === 'superadmin' ? 'ID Super Admin / Email' : 'ID Admin / Email')
                            : 'Email';
                        $loginPlaceholder = $isAdminLoginRole
                            ? ($loginRole === 'superadmin' ? 'superadmin' : 'admin.aplikasi')
                            : 'Masukkan email';
                    @endphp
                    <label class="form-label-admin">{{ $loginLabel }}</label>
                    <input
                        type="text"
                        name="login"
                        class="form-control form-control-admin w-100"
                        value="{{ old('login', old('username')) }}"
                        autocomplete="{{ $isAdminLoginRole ? 'username' : 'email' }}"
                        inputmode="{{ $isAdminLoginRole ? 'text' : 'email' }}"
                        placeholder="{{ $loginPlaceholder }}"
                        required
                        autofocus
                    >
                    @error('login')
                        <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label-admin">Password</label>
                    <input type="password" name="password" class="form-control form-control-admin w-100" autocomplete="current-password" placeholder="Masukkan password" required>
                    @error('password')
                        <div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>
                    @enderror
                    @if(! in_array($loginRole, ['admin', 'superadmin'], true))
                        <div class="text-end mt-2">
                            <a href="{{ route('password.request') }}" class="text-decoration-none fw-bold" style="font-size:0.78rem;color:var(--primary);">
                                Lupa password?
                            </a>
                        </div>
                    @endif
                </div>

                <div class="login-actions">
                    <button type="submit" class="btn-add flex-grow-1 justify-content-center">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem
                    </button>
                    <a href="{{ route('home') }}" class="btn-logout justify-content-center">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>

        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bidangSelect = document.getElementById('register_bidang_id');
    const pembimbingSelect = document.getElementById('register_pembimbing_magang_id');

    if (!bidangSelect || !pembimbingSelect) return;

    const placeholder = pembimbingSelect.querySelector('option[value=""]');
    const options = Array.from(pembimbingSelect.querySelectorAll('option[data-bidang-id]'));
    const oldValue = @json(old('pembimbing_magang_id'));

    function syncPembimbing() {
        const bidangId = bidangSelect.value;
        let hasSelected = false;

        pembimbingSelect.disabled = !bidangId;
        if (placeholder) {
            placeholder.textContent = bidangId ? 'Pilih pembimbing...' : 'Pilih bidang terlebih dahulu...';
        }

        options.forEach(option => {
            const visible = option.dataset.bidangId === bidangId;
            option.hidden = !visible;
            option.disabled = !visible;

            if (!visible && option.selected) {
                option.selected = false;
            }

            if (visible && oldValue && option.value === String(oldValue)) {
                option.selected = true;
                hasSelected = true;
            }
        });

        if (!hasSelected && !options.some(option => option.selected && !option.disabled)) {
            pembimbingSelect.value = '';
        }
    }

    bidangSelect.addEventListener('change', syncPembimbing);
    syncPembimbing();
});
</script>
@endsection
