<?php
/**
 * laporan/rekap.php — Rekap Kumulatif Poin Per Siswa
 */
?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-bar-chart-line me-2 text-info"></i>
                Rekap Akumulasi Poin Siswa
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
                <i class="bi bi-plus-circle me-1"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<?php if (empty($rekap)): ?>
    <div class="card card-main shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-bar-chart display-4 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Belum ada data rekap</h5>
            <p class="text-muted small">Rekap akan muncul setelah minimal 1 absen disimpan.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card card-main shadow-sm">
        <div class="card-header-custom">
            <i class="bi bi-table me-2"></i>
            Rekap <strong><?= count($rekap) ?></strong> Siswa
            <span class="text-muted small ms-2">— akumulasi dari awal hingga saat ini</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>Nama Siswa</th>
                        <th class="text-center">Total Hari Mengisi</th>
                        <th class="text-center">Total Akumulasi Poin</th>
                        <th class="text-center">Rata-rata Poin Harian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    // Sort descending by total poin
                    usort($rekap, function($a, $b) {
                        return $b['total_poin'] <=> $a['total_poin'];
                    });
                    ?>
                    <?php foreach ($rekap as $data): ?>
                        <?php
                        $nama = $data['nama'] ?? '';
                        $totalHari = $data['total_hari'];
                        $totalPoin = $data['total_poin'];
                        $rataRata = $totalHari > 0 ? round($totalPoin / $totalHari, 1) : 0;
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($nama) ?></td>
                            <td class="text-center fw-semibold"><?= $totalHari ?> hari</td>
                            <td class="text-center fw-bold text-success fs-5"><?= $totalPoin ?></td>
                            <td class="text-center fw-semibold text-primary"><?= $rataRata ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
