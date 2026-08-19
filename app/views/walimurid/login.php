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
                
                <?php if (!$isSet): ?>
                    <input type="hidden" name="action" value="set">
                    <div class="alert alert-info small rounded-4 shadow-sm border-0 mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Ini adalah kunjungan pertama Anda. Silakan buat **Kode Akses** untuk masuk.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Buat Kode Akses (Kata Sandi)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="text" name="kode_akses" class="form-control" placeholder="Contoh: ibubudi" required autocomplete="off" pattern="[a-zA-Z\s]+" title="Hanya boleh berisi huruf dan spasi, tanpa angka">
                        </div>
                        <div class="form-text text-danger small fw-medium mt-2">
                            <i class="bi bi-exclamation-triangle-fill"></i> PENTING: Kode akses <strong>TIDAK BOLEH mengandung angka</strong>. Hanya huruf saja. <strong>INGAT KODE INI UNTUK KUNJUNGAN BERIKUTNYA!</strong>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-2 fw-bold">
                        Simpan & Buka Laporan
                    </button>
                    
                <?php else: ?>
                    <input type="hidden" name="action" value="verify">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Masukkan Kode Akses</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="text" name="kode_akses" class="form-control" placeholder="Masukkan kode akses Anda" required autocomplete="off">
                        </div>
                        <div class="form-text text-muted small mt-2">Masukkan kode akses yang telah Anda buat sebelumnya.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3 fw-bold">
                        Buka Laporan
                    </button>
                <?php endif; ?>
                
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
