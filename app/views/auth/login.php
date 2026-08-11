<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login Dashboard Wali Kelas — Sistem Manajemen Presensi Santri">
    <title><?= htmlspecialchars($title ?? 'Login') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">

    <style>
        /* ── Google Button ── */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 13px 20px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #3c4043;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .btn-google:hover {
            background: #f7f8ff;
            border-color: #4285F4;
            box-shadow: 0 4px 16px rgba(66,133,244,0.18);
            transform: translateY(-2px);
            color: #1a1a2e;
        }
        .btn-google:active {
            transform: translateY(0);
        }
        .btn-google svg {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
        }
        .divider-or {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: #adb5bd;
            font-size: 13px;
            font-weight: 500;
        }
        .divider-or::before,
        .divider-or::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e9ecef;
        }
    </style>
</head>
<body class="login-page">

<div class="login-bg">
    <div class="login-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
</div>

<!-- Flash Messages -->
<div class="position-fixed top-0 start-50 translate-middle-x pt-3" style="z-index:9999; width: min(90vw, 420px)">
    <?= Flash::render() ?>
</div>

<div class="login-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-card">
        <!-- Logo / Brand -->
        <div class="login-header text-center mb-4">
            <div class="login-icon mb-3">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h1 class="login-title">Dashboard Wali Kelas</h1>
            <p class="login-subtitle">Sistem Manajemen Presensi Harian Santri</p>
        </div>

        <!-- ── Login dengan Google ── -->
        <a href="<?= BASE_URL ?>/auth/google" class="btn-google" id="btnGoogle">
            <!-- Logo Google SVG resmi -->
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
            Lanjutkan dengan Google
        </a>

        <!-- Pemisah -->
        <div class="divider-or">atau masuk dengan username</div>

        <!-- Form Login Username -->
        <form action="<?= BASE_URL ?>/auth/proses" method="POST" id="loginForm" autocomplete="off" novalidate>

            <div class="mb-4">
                <label for="username" class="form-label fw-semibold">
                    <i class="bi bi-person me-1"></i> Username
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent">
                        <i class="bi bi-person-fill text-muted"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0"
                           id="username"
                           name="username"
                           placeholder="Masukkan username"
                           required
                           autofocus
                           autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">
                    <i class="bi bi-lock me-1"></i> Password
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent">
                        <i class="bi bi-lock-fill text-muted"></i>
                    </span>
                    <input type="password"
                           class="form-control border-start-0 border-end-0"
                           id="password"
                           name="password"
                           placeholder="Masukkan password"
                           required
                           autocomplete="current-password">
                    <button class="input-group-text bg-transparent border-start-0"
                            type="button"
                            id="togglePassword"
                            title="Tampilkan/Sembunyikan Password">
                        <i class="bi bi-eye-fill text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-login btn-lg" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Masuk
                </button>
            </div>
        </form>

        <div class="login-footer text-center mt-4 border-top pt-3">
            <p class="mb-2 text-muted">
                Belum punya akun? 
                <a href="<?= BASE_URL ?>/auth/daftar" class="text-decoration-none fw-semibold">Daftar di sini</a>
            </p>
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Akses terbatas untuk Wali Kelas
            </small>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/js/app.js"></script>
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const pwd  = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        const isHidden = pwd.type === 'password';
        pwd.type  = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'bi bi-eye-slash-fill text-muted' : 'bi bi-eye-fill text-muted';
    });

    // Login button loading state
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memverifikasi...';
        btn.disabled = true;
    });

    // Google button loading state
    document.getElementById('btnGoogle').addEventListener('click', function () {
        this.innerHTML = `
            <span class="spinner-border spinner-border-sm" style="width:20px;height:20px"></span>
            Menghubungkan ke Google...
        `;
        this.style.pointerEvents = 'none';
        this.style.opacity = '0.75';
    });
</script>
</body>
</html>
