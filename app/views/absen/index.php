<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Absen Mandiri Siswa — Kelas <?= htmlspecialchars($kelas) ?>">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
</head>
<body class="absen-page">

<!-- Hero Header -->
<div class="absen-hero">
    <div class="absen-hero-content">
        <div class="absen-logo">
            <i class="bi bi-pencil-square"></i>
        </div>
        <h1 class="absen-hero-title">Absen Mandiri</h1>
        <p class="absen-hero-sub">
            Kelas <?= htmlspecialchars($kelas) ?> &nbsp;·&nbsp;
            <?= date('l, d F Y', strtotime($tanggal)) ?>
        </p>
    </div>
</div>

<div class="absen-container">

    <!-- Flash messages -->
    <?= Flash::render() ?>

    <!-- Info box -->
    <div class="absen-info-box mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        Klik namamu, lalu jawab pertanyaan dengan jujur. Hanya perlu 1 menit!
    </div>

    <!-- Search -->
    <div class="mb-4">
        <div class="absen-search-wrap">
            <i class="bi bi-search absen-search-icon"></i>
            <input type="text" id="searchNama" class="absen-search"
                   placeholder="Ketik namamu di sini..." autocomplete="off" autofocus>
        </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="siswa-grid" id="gridSiswa">
        <?php foreach ($siswa as $i => $s): ?>
            <?php $done = $sudahIsi[$s['id']] ?? false; ?>
            <?php $url  = BASE_URL . '/absen/isi/' . $s['id'] . '?tanggal=' . $tanggal . '&wali=' . $idWali; ?>

            <a href="<?= $url ?>"
               class="siswa-card <?= $done ? 'siswa-card--done' : '' ?>"
               title="<?= htmlspecialchars($s['nama']) ?>"
               data-nama="<?= htmlspecialchars(strtolower($s['nama'])) ?>">
                
                <div class="siswa-card-no" title="ID Siswa"><?= $s['id'] ?></div>
                
                <div class="siswa-card-nama">
                    <?= htmlspecialchars($s['nama']) ?></div>
                <?php if ($done): ?>
                    <div class="siswa-card-status">
                        <i class="bi bi-check-circle-fill"></i> Sudah Isi
                    </div>
                <?php else: ?>
                    <div class="siswa-card-status siswa-card-status--pending">
                        <i class="bi bi-circle"></i> Belum Isi
                    </div>
                <?php endif; ?>
                <div class="siswa-card-arrow">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty state saat search tidak ada hasil -->
    <div id="emptySearch" class="absen-empty" style="display:none">
        <i class="bi bi-search display-5 d-block mb-2"></i>
        <p>Nama tidak ditemukan</p>
    </div>

    <!-- Footer -->
    <div class="absen-footer-note mt-4">
        <i class="bi bi-shield-lock me-1"></i>
        Data kamu bersifat rahasia dan hanya bisa dilihat oleh Wali Kelas
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchNama');
const cards       = document.querySelectorAll('.siswa-card');
const emptyEl     = document.getElementById('emptySearch');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(card => {
        const nama = card.dataset.nama;
        const show = nama.includes(q);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    emptyEl.style.display = visible === 0 ? 'block' : 'none';
});
</script>
</body>
</html>
