<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        .absen-hero {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2.5rem 1.5rem 4rem;
            text-align: center;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            margin-bottom: -2rem;
        }
        .absen-hero-title {
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .absen-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 1.5rem 2rem;
            position: relative;
            z-index: 10;
        }
        .siswa-card {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            text-decoration: none;
            color: var(--bs-body-color);
            transition: all 0.2s ease;
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .siswa-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            color: var(--bs-primary);
        }
        .siswa-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
            margin-right: 1rem;
            text-transform: uppercase;
        }
        .siswa-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .siswa-info {
            flex-grow: 1;
        }
        .siswa-nama {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0;
            line-height: 1.2;
        }
        .siswa-arrow {
            color: #adb5bd;
        }
    </style>
</head>
<body class="bg-light">

<div class="absen-hero">
    <div class="absen-hero-content">
        <a href="<?= BASE_URL ?>/walimurid" class="text-white text-decoration-none d-block text-start mb-2 opacity-75">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <h1 class="absen-hero-title">Laporan Kelas <?= htmlspecialchars($kelas) ?></h1>
        <p class="mb-0 opacity-75">Pilih nama putra/putri Anda</p>
    </div>
</div>

<div class="absen-container">
    <?= Flash::render() ?>

    <div class="mt-4">
        <?php if (empty($siswa)): ?>
            <div class="alert alert-warning text-center shadow-sm">
                Belum ada data siswa di kelas ini.
            </div>
        <?php else: ?>
            <div class="mb-3 position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="searchNama" class="form-control form-control-lg ps-5 border-0 shadow-sm" placeholder="Cari nama siswa..." style="border-radius: 12px; font-size:1rem;">
            </div>
            
            <div id="siswaList">
                <?php foreach ($siswa as $s): ?>
                    <a href="<?= BASE_URL ?>/walimurid?id=<?= $s['id'] ?>" class="siswa-card" data-nama="<?= strtolower($s['nama']) ?>">
                        <div class="siswa-avatar">
                            <?php if (!empty($s['foto'])): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/foto_siswa/<?= htmlspecialchars($s['foto']) ?>" alt="Foto">
                            <?php else: ?>
                                <?= mb_substr($s['nama'], 0, 1) ?>
                            <?php endif; ?>
                        </div>
                        <div class="siswa-info">
                            <div class="siswa-nama"><?= htmlspecialchars(ucwords(strtolower($s['nama']))) ?></div>
                        </div>
                        <div class="siswa-arrow">
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Empty state saat search tidak ada hasil -->
            <div id="emptySearch" class="text-center text-muted mt-4" style="display:none">
                <i class="bi bi-search display-5 d-block mb-2"></i>
                <p>Nama tidak ditemukan</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchNama');
if(searchInput){
    const cards = document.querySelectorAll('.siswa-card');
    const emptyEl = document.getElementById('emptySearch');

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        cards.forEach(card => {
            const nama = card.dataset.nama;
            const show = nama.includes(q);
            card.style.display = show ? 'flex' : 'none';
            if (show) visible++;
        });
        if(emptyEl) emptyEl.style.display = visible === 0 ? 'block' : 'none';
    });
}
</script>
</body>
</html>
