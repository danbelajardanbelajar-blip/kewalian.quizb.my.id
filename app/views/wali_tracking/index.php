<?php
/**
 * wali_tracking/index.php — Halaman Wali Murid Tracking
 */
$sudahHp      = 0;
$belumHp      = 0;
$sudahKunjung = 0;
$belumKunjung = 0;
foreach ($data as $row) {
    if (!empty($row['no_hp'])) $sudahHp++; else $belumHp++;
    if ((int)$row['total_kunjungan'] > 0) $sudahKunjung++; else $belumKunjung++;
}
$total = count($data);
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>
                Wali Murid Tracking
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                Pantau kepedulian wali murid terhadap anaknya
            </p>
        </div>
    </div>
</div>

<?= Flash::render() ?>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Total Siswa</div>
            <div class="stat-card-value"><?= $total ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Sudah Isi No HP</div>
            <div class="stat-card-value text-success"><?= $sudahHp ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Belum Isi No HP</div>
            <div class="stat-card-value text-danger"><?= $belumHp ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Pernah Lihat Laporan</div>
            <div class="stat-card-value text-info"><?= $sudahKunjung ?></div>
        </div>
    </div>
</div>

<?php if ($belumHp > 0): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div>
        <strong>Wali murid belum mengisi nomor HP (<?= $belumHp ?> siswa):</strong><br>
        <small><?= implode(', ', array_map(fn($r) => htmlspecialchars($r['nama']), array_filter($data, fn($r) => empty($r['no_hp'])))) ?></small>
    </div>
</div>
<?php endif; ?>

<?php if ($belumKunjung > 0): ?>
<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-eye-slash-fill mt-1"></i>
    <div>
        <strong>Wali murid belum pernah membuka halaman laporan (<?= $belumKunjung ?> siswa):</strong><br>
        <small><?= implode(', ', array_map(fn($r) => htmlspecialchars($r['nama']), array_filter($data, fn($r) => (int)$r['total_kunjungan'] === 0))) ?></small>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Detail -->
<div class="card card-main shadow-sm">
    <div class="card-header-custom">
        <i class="bi bi-table me-2"></i> Detail Per Siswa
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Siswa</th>
                        <th class="text-center">No HP Wali</th>
                        <th class="text-center">Kunjungan Laporan</th>
                        <th class="text-center">Terakhir Kunjung</th>
                        <th class="text-center">Status Kepedulian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                    <?php
                        $punyaHp      = !empty($row['no_hp']);
                        $sudahBuka    = (int)$row['total_kunjungan'] > 0;
                        $kepedulian   = $punyaHp && $sudahBuka
                            ? ['label' => 'Peduli', 'cls' => 'success', 'icon' => 'bi-heart-fill']
                            : ($punyaHp || $sudahBuka
                                ? ['label' => 'Sebagian', 'cls' => 'warning', 'icon' => 'bi-heart-half']
                                : ['label' => 'Perlu Perhatian', 'cls' => 'danger', 'icon' => 'bi-heart']
                            );
                    ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                        <td class="text-center">
                            <?php if ($punyaHp): ?>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($row['no_hp']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger">
                                    <i class="bi bi-x-circle me-1"></i> Belum diisi
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($sudahBuka): ?>
                                <span class="badge bg-info-subtle text-info border border-info">
                                    <i class="bi bi-eye me-1"></i> <?= $row['total_kunjungan'] ?>x
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                    <i class="bi bi-eye-slash me-1"></i> Belum pernah
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row['kunjungan_terakhir']): ?>
                                <small class="text-muted"><?= date('d M Y H:i', strtotime($row['kunjungan_terakhir'])) ?></small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $kepedulian['cls'] ?>">
                                <i class="bi <?= $kepedulian['icon'] ?> me-1"></i><?= $kepedulian['label'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
