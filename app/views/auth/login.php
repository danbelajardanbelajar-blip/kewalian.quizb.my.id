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

        <!-- Form Login -->
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

        <div class="login-footer text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Akses terbatas untuk Wali Kelas yang berwenang
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
</script>
</body>
</html>
