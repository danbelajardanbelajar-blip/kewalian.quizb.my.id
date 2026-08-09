<?php
/**
 * laporan/show.php — Detail Laporan
 */
$tglFormatted  = date('l, d F Y', strtotime($tanggal));
$kategori      = $laporan['kategori'] ?? [];
$siswaData     = $laporan['siswa'] ?? [];
$kelas         = $laporan['kelas'] ?? '';
$updatedAt     = $laporan['updated_at'] ?? '';
$createdBy     = $laporan['created_by'] ?? '-';

// Hitung statistik
$stats = [];
foreach ($kategori as $key => $label) {
    $hadir = array_filter($siswaData, fn($s) => !empty($s[$key]));
    $stats[$key] = ['label' => $label, 'hadir' => count($hadir), 'total' => count($siswaData)];
}
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/laporan">Riwayat</a></li>
                    <li class="breadcrumb-item active"><?= $tglFormatted ?></li>
                </ol>
            </nav>
            <h1 class="page-title">
                <i class="bi bi-file-earmark-check me-2 text-primary"></i>
                Detail Laporan Presensi
            </h1>
            <p class="page-subtitle">
                <?= $tglFormatted ?> — Kelas <strong><?= htmlspecialchars($kelas) ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/laporan/export/<?= $tanggal ?>"
               class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
            </a>
            <a href="<?= BASE_URL ?>/laporan/edit/<?= $tanggal ?>"
               class="btn btn-outline-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit Laporan
            </a>
            <a href="<?= BASE_URL ?>/laporan" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <?php foreach ($stats as $key => $stat): ?>
        <?php $pct = $stat['total'] > 0 ? round(($stat['hadir'] / $stat['total']) * 100) : 0; ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="stat-card">
                <div class="stat-card-label"><?= htmlspecialchars($stat['label']) ?></div>
                <div class="stat-card-value"><?= $stat['hadir'] ?><span>/ <?= $stat['total'] ?></span></div>
                <div class="progress stat-progress" title="<?= $pct ?>% hadir">
                    <div class="progress-bar <?= $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger') ?>"
                         style="width: <?= $pct ?>%"></div>
                </div>
                <div class="stat-card-pct"><?= $pct ?>% hadir</div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Tabel Detail -->
<div class="card card-main shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>
            <i class="bi bi-people-fill me-2"></i>
            <strong><?= count($siswaData) ?></strong> Siswa
        </span>
        <div class="text-muted small">
            <i class="bi bi-clock me-1"></i>
            Disimpan: <?= !empty($updatedAt) ? date('d/m/Y H:i', strtotime($updatedAt)) : '-' ?>
            &nbsp;|&nbsp;
            <i class="bi bi-person me-1"></i>
            <?= htmlspecialchars($createdBy) ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th>Nama Siswa</th>
                    <?php foreach ($kategori as $label): ?>
                        <th class="text-center"><?= htmlspecialchars($label) ?></th>
                    <?php endforeach; ?>
                    <th class="text-center">Total Hadir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaData as $i => $siswa): ?>
                    <?php
                    $totalHadir = 0;
                    foreach ($kategori as $key => $label) {
                        if (!empty($siswa[$key])) $totalHadir++;
                    }
                    $totalKat = count($kategori);
                    ?>
                    <tr>
                        <td class="text-center text-muted"><?= $i + 1 ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($siswa['nama']) ?></td>
                        <?php foreach ($kategori as $key => $label): ?>
                            <td class="text-center">
                                <?php if (!empty($siswa[$key])): ?>
                                    <span class="badge-hadir"><i class="bi bi-check-lg"></i></span>
                                <?php else: ?>
                                    <span class="badge-absen"><i class="bi bi-x-lg"></i></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-center">
                            <span class="fw-bold <?= $totalHadir == $totalKat ? 'text-success' : ($totalHadir >= $totalKat / 2 ? 'text-warning' : 'text-danger') ?>">
                                <?= $totalHadir ?>/<?= $totalKat ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
