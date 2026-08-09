<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
</head>
<body class="absen-page">

<div class="selesai-wrapper">
    <!-- Animasi sukses -->
    <div class="selesai-icon-wrap">
        <div class="selesai-icon <?= $isEdit ? 'selesai-icon--edit' : '' ?>">
            <i class="bi <?= $isEdit ? 'bi-arrow-repeat' : 'bi-check-lg' ?>"></i>
        </div>
    </div>

    <h1 class="selesai-title">
        <?= $isEdit ? 'Data Diperbarui!' : 'Absen Tersimpan!' ?>
    </h1>
    <p class="selesai-nama"><?= htmlspecialchars($nama) ?></p>
    <p class="selesai-tanggal">
        <i class="bi bi-calendar-check me-1"></i>
        <?= date('l, d F Y', strtotime($tanggal)) ?>
    </p>

    <div class="selesai-msg">
        <?php if ($isEdit): ?>
            <i class="bi bi-info-circle me-2"></i>
            Data absenmu hari ini berhasil diperbarui. Terima kasih!
        <?php else: ?>
            <i class="bi bi-stars me-2"></i>
            Terima kasih sudah jujur mengisi absen hari ini! Semangat terus!
        <?php endif; ?>
    </div>

    <!-- Motivasi acak -->
    <?php
    $motivasi = [
        '"Orang yang belajar dari kesalahannya adalah orang yang bijak."',
        '"Ilmu adalah cahaya yang menerangi jalan hidupmu."',
        '"Setiap hari adalah kesempatan baru untuk menjadi lebih baik."',
        '"Kejujuran adalah pondasi dari segala kebaikan."',
        '"Disiplin hari ini adalah sukses di masa depan."',
        '"Teruslah belajar, karena ilmu tidak pernah habis."',
    ];
    echo '<div class="selesai-quote">' . $motivasi[array_rand($motivasi)] . '</div>';
    ?>

    <div class="selesai-actions">
        <a href="<?= BASE_URL ?>/absen?tanggal=<?= $tanggal ?>" class="btn-selesai-kembali">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar
        </a>
        <a href="<?= BASE_URL ?>/absen/isi/<?= rawurlencode($nama) ?>?tanggal=<?= $tanggal ?>"
           class="btn-selesai-edit">
            <i class="bi bi-pencil me-2"></i> Edit Jawabanku
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Animasi confetti sederhana
(function () {
    const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899'];
    for (let i = 0; i < 40; i++) {
        const el = document.createElement('div');
        el.style.cssText = `
            position:fixed; top:-10px;
            left:${Math.random()*100}vw;
            width:${6+Math.random()*8}px;
            height:${6+Math.random()*8}px;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            border-radius:${Math.random()>0.5?'50%':'2px'};
            animation: confettiFall ${2+Math.random()*3}s ${Math.random()*2}s ease-in forwards;
            opacity:0.85;
            pointer-events:none;
            z-index:9999;
        `;
        document.body.appendChild(el);
    }
})();
</script>
<style>
@keyframes confettiFall {
    0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(105vh) rotate(720deg); opacity: 0; }
}
</style>
</body>
</html>
