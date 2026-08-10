<?php
/**
 * laporan/show.php — Detail Laporan Wali Kelas
 */
$tglFormatted  = date('l, d F Y', strtotime($tanggal));
$siswaData     = $laporan['siswa'] ?? [];
$kelas         = $laporan['kelas'] ?? '';
$updatedAt     = $laporan['updated_at'] ?? '';
$createdBy     = $laporan['created_by'] ?? '-';

$labelStatus = [
    'hadir'       => '<span class="badge bg-success">Hadir</span>',
    'absen'       => '<span class="badge bg-danger">Absen</span>',
    'sakit'       => '<span class="badge bg-warning text-dark">Sakit</span>',
    'izin'        => '<span class="badge bg-info text-dark">Izin</span>',
    'ikut'        => '<span class="badge bg-success">Ikut</span>',
    'udzur_haid'  => '<span class="badge bg-warning text-dark">Udzur</span>',
    'tidak_ikut'  => '<span class="badge bg-danger">Tidak</span>',
    'iya'         => '<span class="badge bg-success">Iya</span>',
    'tidak'       => '<span class="badge bg-danger">Belum</span>',
];

// Hitung statistik (sederhana)
$totalHadir = 0;
$totalSiswa = count($siswaData);
foreach ($siswaData as $s) {
    if (($s['sekolah']['status'] ?? '') === 'hadir') {
        $totalHadir++;
    }
}
$pct = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100) : 0;
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
            <a href="<?= BASE_URL ?>/?tanggal=<?= $tanggal ?>"
               class="btn btn-outline-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit Laporan
            </a>
            <a href="<?= BASE_URL ?>/laporan" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card text-center">
            <div class="stat-card-label">Kehadiran Sekolah</div>
            <div class="stat-card-value text-success"><?= $totalHadir ?> / <?= $totalSiswa ?></div>
            <div class="progress stat-progress mt-2" title="<?= $pct ?>% hadir">
                <div class="progress-bar <?= $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger') ?>"
                     style="width: <?= $pct ?>%"></div>
            </div>
            <div class="stat-card-pct"><?= $pct ?>% hadir</div>
        </div>
    </div>
</div>

<div class="card card-main shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>
            <i class="bi bi-table me-2"></i>
            Detail Seluruh Siswa
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
        <table class="table table-bordered table-hover align-middle mb-0 small">
            <thead>
                <tr>
                    <th class="text-center" width="4%">No</th>
                    <th style="min-width:180px">Nama Siswa</th>
                    <th class="text-center">🏫 Sekolah</th>
                    <th class="text-center">📖 Al-Miftah</th>
                    <th class="text-center">🌙 Diniyah</th>
                    <th class="text-center">🌅 Ngaji Pagi</th>
                    <th class="text-center" style="min-width:120px">📿 Al-Qur'an</th>
                    <th class="text-center">🕌 Dluha</th>
                    <th class="text-center">📚 Belajar</th>
                    <th class="text-center">💖 Memaafkan</th>
                    <th class="text-center">🤲 Doa Muslim</th>
                    <th class="text-center">👨‍👩‍👦 Doa Ortu</th>
                    <th class="text-center">🤝 Sedekah</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($siswaData as $id => $s): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $no++ ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($s['nama']) ?></td>

                        <?php foreach (['sekolah','almiftah','diniyah','subuh'] as $kat): ?>
                            <td class="text-center">
                                <?= $labelStatus[$s[$kat]['status'] ?? 'absen'] ?? '-' ?>
                                <?php if (!empty($s[$kat]['ket'])): ?>
                                    <div class="text-muted" style="font-size:.7rem;max-width:100px">
                                        <?= htmlspecialchars($s[$kat]['ket']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <td class="text-center">
                            <?php $q = $s['quran'] ?? []; ?>
                            <?php if (!empty($q)): ?>
                                <?php if ($q['type'] === 'setengah_juz'): ?>
                                    <span class="badge bg-info text-dark">½ Juz</span>
                                <?php elseif ($q['type'] === 'juz'): ?>
                                    <span class="badge bg-success"><?= $q['jumlah'] ?> Juz</span>
                                <?php elseif ($q['type'] === 'halaman'): ?>
                                    <span class="badge bg-primary"><?= $q['jumlah'] ?> Hal</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Belum</span>
                                <?php endif; ?>
                            <?php else: ?>-<?php endif; ?>
                        </td>

                        <td class="text-center"><?= $labelStatus[$s['dluha']['status'] ?? 'tidak_ikut'] ?? '-' ?></td>
                        <td class="text-center"><?= $labelStatus[$s['belajar']['status'] ?? 'tidak'] ?? '-' ?></td>
                        <td class="text-center"><?= $labelStatus[$s['memaafkan']['status'] ?? 'tidak'] ?? '-' ?></td>
                        <td class="text-center"><?= $labelStatus[$s['mendoakan_muslimin']['status'] ?? 'tidak'] ?? '-' ?></td>
                        <td class="text-center"><?= $labelStatus[$s['mendoakan_ortu']['status'] ?? 'tidak'] ?? '-' ?></td>
                        <td class="text-center"><?= $labelStatus[$s['shadaqah']['status'] ?? 'tidak'] ?? '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
