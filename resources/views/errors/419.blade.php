<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Halaman Kedaluwarsa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --dark: #0f172a;
            --text: #334155;
            --text-muted: #64748b;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --font: 'Plus Jakarta Sans', -apple-system, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            font-family: var(--font);
            background: linear-gradient(135deg, #eef2ff 0%, var(--bg) 50%, #f1f5f9 100%);
            color: var(--text);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .error-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.12), 0 0 1px 1px rgba(0, 0, 0, 0.02);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .error-icon-box {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.25));
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .error-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.3px;
            margin-bottom: 0.75rem;
        }

        .error-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-reload {
            background: var(--primary);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
            text-decoration: none;
            cursor: pointer;
        }

        .btn-reload:hover {
            background: var(--primary-dark);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-back {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-top: 0.75rem;
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: var(--dark);
        }

        .countdown-text {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-badge">
            <i class="fa-solid fa-shield-halved"></i> Keamanan Sesi
        </div>
        <div>
            <div class="error-icon-box">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
        <h1 class="error-title">Sesi Halaman Kedaluwarsa</h1>
        <p class="error-desc">
            Halaman ini telah terbuka tanpa aktivitas dalam waktu yang cukup lama atau token keamanan sesi telah diperbarui. Silakan muat ulang halaman untuk melanjutkan.
        </p>

        <button type="button" class="btn-reload" onclick="handleReload()">
            <i class="fa-solid fa-rotate-right" id="reloadIcon"></i>
            <span>Muat Ulang Halaman</span>
        </button>

        <a href="{{ url('/') }}" class="btn-back">
            <i class="fa-solid fa-house"></i>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="countdown-text" id="autoCountdown">
            Otomatis memuat ulang dalam <strong id="countdownVal">5</strong> detik...
        </div>
    </div>

    <script>
        let seconds = 5;
        const countdownVal = document.getElementById('countdownVal');
        const autoCountdown = document.getElementById('autoCountdown');

        const timer = setInterval(() => {
            seconds--;
            if (countdownVal) countdownVal.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                handleReload();
            }
        }, 1000);

        function handleReload() {
            clearInterval(timer);
            const icon = document.getElementById('reloadIcon');
            if (icon) icon.classList.add('fa-spin');
            window.location.reload();
        }
    </script>
</body>
</html>
