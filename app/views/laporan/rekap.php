<?php
/**
 * laporan/rekap.php — Rekap Kehadiran Per Siswa
 */
$totalLaporan = count(array_unique(array_column(array_values($rekap), 'total_hari')));
?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-bar-chart-line me-2 text-info"></i>
                Rekap Kehadiran Siswa
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                Akumulasi dari semua laporan yang tersimpan
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/laporan" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-journal-text me-1"></i> Riwayat Laporan
            </a>
            <a href="<?= BASE_URL ?>" class="btn btn-primary-custom btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Input Presensi
            </a>
        </div>
    </div>
</div>

<?php if (empty($rekap)): ?>
    <div class="card card-main shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-bar-chart display-4 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Belum ada data rekap</h5>
            <p class="text-muted small">Rekap akan muncul setelah minimal 1 laporan disimpan.</p>
            <a href="<?= BASE_URL ?>" class="btn btn-primary-custom mt-2">Input Presensi Pertama</a>
        </div>
    </div>
<?php else: ?>
    <div class="card card-main shadow-sm">
        <div class="card-header-custom">
            <i class="bi bi-table me-2"></i>
            Rekap <strong><?= count($rekap) ?></strong> Siswa
            <span class="text-muted small ms-2">— data diambil dari semua laporan tersimpan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>Nama Siswa</th>
                        <?php foreach ($kategori as $label): ?>
                            <th class="text-center"><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">Total Hari</th>
                        <th class="text-center">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($rekap as $nama => $data): ?>
                        <?php
                        $totalKat   = count($kategori);
                        $totalHadir = 0;
                        $totalMax   = $data['total_hari'] * $totalKat;
                        foreach ($kategori as $key => $label) {
                            $totalHadir += $data['kategori'][$key]['hadir'] ?? 0;
                        }
                        $pctTotal = $totalMax > 0 ? round(($totalHadir / $totalMax) * 100) : 0;
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($nama) ?></td>
                            <?php foreach ($kategori as $key => $label): ?>
                                <?php
                                $hadir = $data['kategori'][$key]['hadir'] ?? 0;
                                $total = $data['total_hari'];
                                $pct   = $total > 0 ? round(($hadir / $total) * 100) : 0;
                                $cls   = $pct >= 80 ? 'text-success' : ($pct >= 60 ? 'text-warning' : 'text-danger');
                                ?>
                                <td class="text-center <?= $cls ?> fw-semibold">
                                    <?= $hadir ?>/<?= $total ?>
                                    <div class="small fw-normal"><?= $pct ?>%</div>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center fw-semibold"><?= $data['total_hari'] ?> hari</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <div class="progress flex-grow-1" style="height:8px; max-width:80px">
                                        <div class="progress-bar <?= $pctTotal >= 80 ? 'bg-success' : ($pctTotal >= 60 ? 'bg-warning' : 'bg-danger') ?>"
                                             style="width: <?= $pctTotal ?>%"></div>
                                    </div>
                                    <span class="fw-semibold <?= $pctTotal >= 80 ? 'text-success' : ($pctTotal >= 60 ? 'text-warning' : 'text-danger') ?>">
                                        <?= $pctTotal ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
