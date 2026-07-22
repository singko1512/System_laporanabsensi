<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Absensi Harian - Employee Attendance System')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #6c5ce7;
            --primary-light: #a29bfe;
            --primary-dark: #5a4bd1;
            --dark: #1a1a2e;
            --text: #2d3436;
            --text-muted: #636e72;
            --text-light: #b2bec3;
            --bg: #f7f8fc;
            --white: #ffffff;
            --border: #eef0f6;
            --green: #00b894;
            --rose: #e17055;
            --amber: #fdcb6e;
            --font: 'Plus Jakarta Sans', -apple-system, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* ── Navbar ── */
        .site-nav {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: var(--dark);
        }

        .nav-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
        }

        .nav-brand-text strong {
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--dark);
        }

        .nav-brand-text span {
            display: block;
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
            background: rgba(108, 92, 231, 0.06);
        }

        /* ── Glass Cards ── */
        .glass-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .glass-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }

        /* ── Buttons ── */
        .btn-premium-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #7c6cf0 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 14px;
            padding: 0.8rem 1.8rem;
            box-shadow: 0 4px 14px rgba(108, 92, 231, 0.25);
            transition: all 0.25s ease;
        }

        .btn-premium-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            box-shadow: 0 6px 20px rgba(108, 92, 231, 0.35);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-premium-secondary {
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--text);
            font-weight: 600;
            border-radius: 14px;
            padding: 0.8rem 1.8rem;
            transition: all 0.25s ease;
        }

        .btn-premium-secondary:hover {
            border-color: #d1d5db;
            background: #fafbff;
            transform: translateY(-1px);
        }

        /* ── Form Inputs ── */
        .form-control-premium,
        .form-select-premium {
            border: 1px solid var(--border);
            background: var(--white);
            border-radius: 14px;
            padding: 0.85rem 1.1rem;
            font-size: 0.92rem;
            color: var(--text);
            transition: all 0.2s ease;
        }

        .form-control-premium:focus,
        .form-select-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
            outline: none;
        }

        .form-label-premium {
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        /* ── Badges ── */
        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            padding: 0.4rem 1rem;
            border-radius: 100px;
            border: 1px solid var(--border);
            background: var(--white);
            margin-bottom: 1.25rem;
        }

        .badge-status {
            padding: 0.35em 0.75em;
            font-size: 0.72em;
            font-weight: 700;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .badge-hadir { background: rgba(0,184,148,0.1); color: var(--green); }
        .badge-wfh { background: rgba(108,92,231,0.1); color: var(--primary); }
        .badge-sakit { background: rgba(225,112,85,0.1); color: var(--rose); }
        .badge-izin { background: rgba(253,203,110,0.15); color: #e17055; }

        /* ── Footer ── */
        footer {
            margin-top: auto;
            padding: 1.5rem 0;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-light);
            border-top: 1px solid var(--border);
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Navbar -->
    <nav class="site-nav">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="nav-brand">
                <div class="nav-brand-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="nav-brand-text">
                    <strong>Absensi Harian</strong>
                    <span>Employee Attendance System</span>
                </div>
            </a>

            <ul class="nav-links d-none d-md-flex">
                <li>
                    <a href="{{ route('absensi.index') }}" class="@if(Route::is('absensi.index')) active @endif">
                        <i class="fa-regular fa-pen-to-square"></i> Absensi
                    </a>
                </li>
                @if(session('admin_authenticated'))
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="@if(Route::is('admin.dashboard')) active @endif">
                            <i class="fa-solid fa-gauge-high"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.logout') }}" style="color: var(--rose);">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0)" onclick="triggerAdminLogin()">
                            <i class="fa-solid fa-lock"></i> Admin
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Mobile toggle -->
            <button class="btn d-md-none border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                <i class="fa-solid fa-bars fs-5 text-dark"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Off-canvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <h6 class="offcanvas-title fw-bold">Menu</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-2">
            <a href="{{ route('absensi.index') }}" class="btn btn-outline-dark rounded-3 text-start"><i class="fa-regular fa-pen-to-square me-2"></i> Menu Absensi</a>
            @if(session('admin_authenticated'))
                <a href="{{ route('admin.dashboard') }}" class="btn btn-dark rounded-3 text-start"><i class="fa-solid fa-gauge-high me-2"></i> Dashboard Admin</a>
                <a href="{{ route('admin.logout') }}" class="btn btn-outline-danger rounded-3 text-start"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Keluar</a>
            @else
                <button onclick="triggerAdminLogin()" class="btn btn-outline-dark rounded-3 text-start"><i class="fa-solid fa-lock me-2"></i> Admin Panel</button>
            @endif
        </div>
    </div>

    <!-- Main -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            &copy; {{ date('Y') }} AbsensiKita &middot; Sistem Laporan Absensi Pegawai
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function triggerAdminLogin() {
            Swal.fire({
                title: 'Verifikasi Admin',
                text: 'Masukkan PIN 6-digit untuk mengakses panel administrator',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off',
                    placeholder: '••••••',
                    maxlength: '20',
                    style: 'text-align:center; letter-spacing:0.6rem; font-size:1.5rem; font-weight:700; border-radius:14px; border:1px solid #eef0f6; padding:1rem;'
                },
                showCancelButton: true,
                confirmButtonText: 'Masuk',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#6c5ce7',
                cancelButtonColor: '#b2bec3',
                customClass: {
                    popup: 'rounded-4 border-0 shadow-lg',
                    title: 'fw-bold',
                    confirmButton: 'rounded-3 px-4 py-2',
                    cancelButton: 'rounded-3 px-4 py-2'
                },
                preConfirm: (pin) => {
                    if (!pin) Swal.showValidationMessage('PIN tidak boleh kosong');
                    return pin;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('admin.login') }}";

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = "{{ csrf_token() }}";
                    form.appendChild(csrf);

                    const pin = document.createElement('input');
                    pin.type = 'hidden'; pin.name = 'pin'; pin.value = result.value;
                    form.appendChild(pin);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        @if (session('success_swal'))
            Swal.fire({ icon:'success', title:'Berhasil', text:"{{ session('success_swal') }}", confirmButtonColor:'#6c5ce7', timer:3000, customClass:{popup:'rounded-4 border-0 shadow-lg', confirmButton:'rounded-3 px-4'} });
        @endif
        @if (session('error_swal'))
            Swal.fire({ icon:'error', title:'Gagal', text:"{{ session('error_swal') }}", confirmButtonColor:'#6c5ce7', customClass:{popup:'rounded-4 border-0 shadow-lg', confirmButton:'rounded-3 px-4'} });
        @endif
        @if (session('error'))
            Swal.fire({ icon:'warning', title:'Akses Ditolak', text:"{{ session('error') }}", confirmButtonColor:'#6c5ce7', customClass:{popup:'rounded-4 border-0 shadow-lg', confirmButton:'rounded-3 px-4'} });
        @endif
    </script>
    @yield('scripts')
</body>
</html>
