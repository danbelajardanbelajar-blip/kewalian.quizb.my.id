<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Lengkapi Profil') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">

    <style>
        .setup-avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4285F4;
            box-shadow: 0 4px 16px rgba(66,133,244,0.25);
        }
        .avatar-placeholder {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4285F4, #34A853);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: #fff;
            box-shadow: 0 4px 16px rgba(66,133,244,0.25);
        }
        .google-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px;
            background: #e8f0fe; color: #1a73e8;
            font-size: 12px; font-weight: 600;
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
    <div class="login-card" style="max-width: 440px">

        <!-- Header -->
        <div class="login-header text-center mb-4">
            <!-- Avatar dari Google -->
            <div class="d-flex justify-content-center mb-3">
                <?php if (!empty($pending['avatar'])): ?>
                    <img src="<?= htmlspecialchars($pending['avatar']) ?>" alt="Foto Profil Google" class="setup-avatar">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="google-badge mb-2">
                <svg width="14" height="14" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Terverifikasi Google
            </div>

            <h1 class="login-title" style="font-size:1.5rem">Halo, <?= htmlspecialchars($pending['name']) ?>!</h1>
            <p class="login-subtitle">Lengkapi data kelas untuk menyelesaikan pendaftaran</p>
        </div>

        <!-- Info email Google (read-only) -->
        <div class="mb-3 p-3 rounded-3" style="background:#f8f9fa; border: 1px solid #e9ecef">
            <small class="text-muted d-block mb-1"><i class="bi bi-envelope me-1"></i> Email Google</small>
            <strong><?= htmlspecialchars($pending['email']) ?></strong>
        </div>

        <!-- Form Setup -->
        <form action="<?= BASE_URL ?>/auth/google/simpan" method="POST" id="setupForm">

            <div class="mb-4">
                <label for="username" class="form-label fw-semibold">
                    <i class="bi bi-person me-1"></i> Username Login
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent">
                        <i class="bi bi-at text-muted"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0"
                           id="username"
                           name="username"
                           placeholder="Buat username unik"
                           value="<?= htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $pending['name'])[0]))) ?>"
                           required
                           autocomplete="username">
                </div>
                <small class="text-muted">Hanya huruf, angka, dan underscore. Akan digunakan sebagai ID di link absen.</small>
            </div>

            <div class="mb-4">
                <label for="kelas" class="form-label fw-semibold">
                    <i class="bi bi-door-open me-1"></i> Nama Kelas
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent">
                        <i class="bi bi-building text-muted"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0"
                           id="kelas"
                           name="kelas"
                           placeholder="Contoh: Kelas 7A, Tahfidz B"
                           required>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-login btn-lg" id="setupBtn">
                    <i class="bi bi-check-circle me-2"></i>
                    Selesaikan Pendaftaran
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/auth/login" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('setupForm').addEventListener('submit', function () {
        const btn = document.getElementById('setupBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        btn.disabled = true;
    });
</script>
</body>
</html>
