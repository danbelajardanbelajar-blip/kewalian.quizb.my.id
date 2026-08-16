<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        body { background: #f8f9fa; }
        .login-card { max-width: 400px; margin: 2rem auto; border-radius: 15px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container mt-5 pt-5">
        <?= Flash::render() ?>
        <div class="card login-card p-4">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                <h4 class="mt-2">Akses Laporan Siswa</h4>
                <p class="text-muted small">Melihat laporan ananda <strong><?= htmlspecialchars($siswa["nama"]) ?></strong></p>
            </div>
            <form action="<?= BASE_URL ?>/walimurid/verify" method="POST">
                <input type="hidden" name="id" value="<?= $siswa["id"] ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor WhatsApp Terdaftar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 62812345678" required autocomplete="off">
                    </div>
                    <div class="form-text text-muted small">Masukkan nomor yang didaftarkan oleh wali kelas.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3 fw-bold">
                    Buka Laporan
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
