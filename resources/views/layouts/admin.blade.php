<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --green: #10b981;
            --red: #ef4444;
            --dark: #1e293b;
            --text: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --bg: #f0f4f8;
            --white: #ffffff;
            --border: #e2e8f0;
            --table-head: #f1f5f9;
            --font: 'Plus Jakarta Sans', -apple-system, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            font-family: var(--font);
            background: linear-gradient(180deg, #eef2ff 0%, var(--bg) 40%, #f8fafc 100%);
            color: var(--text);
            min-height: 100vh;
            margin: 0;
        }

        .admin-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 1.25rem 3rem;
        }

        .admin-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #a78bfa 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .admin-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: var(--dark);
        }

        .admin-tabs {
            display: inline-flex;
            gap: 0.25rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 4px;
        }

        .admin-tabs .tab-btn {
            border: none;
            background: transparent;
            border-radius: 11px;
            padding: 0.6rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
        }

        .admin-tabs .tab-btn.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .admin-tabs .tab-btn:not(.active):hover {
            color: var(--dark);
        }

        .filter-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
        }

        .filter-select {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.55rem 2rem 0.55rem 0.85rem;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--dark);
            background: var(--white);
            min-width: 130px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .btn-export-excel {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1.1rem;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-export-excel:hover {
            background: #059669;
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-export-pdf {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1.1rem;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-export-pdf:hover {
            background: #dc2626;
            color: #fff;
            transform: translateY(-1px);
        }

        .search-input {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            font-size: 0.88rem;
            width: 100%;
            background: var(--white);
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .search-wrap {
            position: relative;
        }

        .search-wrap i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .status-select {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.65rem 2rem 0.65rem 0.85rem;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--dark);
            background: var(--white);
            min-width: 150px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            background: var(--table-head);
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 0.85rem 1.25rem;
            border: none;
            text-align: left;
        }

        .data-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.88rem;
            vertical-align: middle;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: #fafbff;
        }

        .badge-status {
            display: inline-block;
            padding: 0.3em 0.75em;
            font-size: 0.72rem;
            font-weight: 700;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-hadir { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .badge-wfh { background: rgba(99, 102, 241, 0.12); color: var(--primary); }
        .badge-sakit { background: rgba(239, 68, 68, 0.12); color: var(--red); }
        .badge-izin { background: rgba(245, 158, 11, 0.15); color: #d97706; }

        .empty-state {
            text-align: center;
            padding: 3.5rem 1.5rem;
        }

        .empty-state h6 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.35rem;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }

        .attachment-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
        }

        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .attachment-link:hover { color: var(--primary-dark); }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-add:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .btn-action {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-action:hover { background: #f8fafc; color: var(--dark); }
        .btn-action.danger:hover { background: #fef2f2; color: var(--red); border-color: #fecaca; }

        .modal-clean .modal-content {
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .form-control-admin {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.88rem;
        }

        .form-control-admin:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
            outline: none;
        }

        .form-label-admin {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.4rem;
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @if (session('success_swal'))
            Swal.fire({ icon:'success', title:'Berhasil', text:"{{ session('success_swal') }}", confirmButtonColor:'#6366f1', timer:3000, customClass:{popup:'rounded-4 border-0 shadow-lg', confirmButton:'rounded-3 px-4'} });
        @endif
        @if (session('error_swal'))
            Swal.fire({ icon:'error', title:'Gagal', text:"{{ session('error_swal') }}", confirmButtonColor:'#6366f1', customClass:{popup:'rounded-4 border-0 shadow-lg', confirmButton:'rounded-3 px-4'} });
        @endif
    </script>
    @yield('scripts')
</body>
</html>
