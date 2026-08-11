<?php
/**
 * laporan/show.php — Detail Laporan Wali Kelas
 */
$tglFormatted  = date('l, d F Y', strtotime($tanggal));
$siswaData     = $laporan['siswa'] ?? [];
$kelas         = $laporan['kelas'] ?? '';
$updatedAt     = $laporan['updated_at'] ?? '';
$createdBy     = $laporan['created_by'] ?? '-';

// Hitung persentase pengisi
$totalSiswa = count($siswaData);
$totalPoin = 0;
foreach ($siswaData as $s) {
    $totalPoin += ($s['total_poin'] ?? 0);
}
$avgPoin = $totalSiswa > 0 ? round($totalPoin / $totalSiswa) : 0;
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

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card text-center">
            <div class="stat-card-label">Rata-rata Poin</div>
            <div class="stat-card-value text-primary"><?= $avgPoin ?></div>
            <div class="stat-card-pct">Poin per Siswa</div>
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
                    <?php foreach ($pertanyaan as $p): ?>
                        <th class="text-center" style="min-width:100px"><?= htmlspecialchars($p['judul']) ?></th>
                    <?php endforeach; ?>
                    <th class="text-center">Total Poin</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($siswaData as $id => $s): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $no++ ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($s['nama']) ?></td>
                        <?php foreach ($pertanyaan as $p): 
                            $pId = $p['id'];
                            $ans = $s['jawaban'][$pId] ?? null;
                            $dispLabel = '-';
                            $badgeClass = 'bg-secondary';
                            
                            if ($ans) {
                                $val = $ans['jawaban'];
                                $ket = $ans['keterangan'];
                                $poin = $ans['poin'];
                                
                                if ($p['tipe'] === 'pilihan_ganda') {
                                    $opsi = json_decode($p['opsi'], true);
                                    foreach ($opsi as $op) {
                                        if ($op['value'] === $val) {
                                            $dispLabel = $op['label'];
                                            $badgeClass = ($op['poin'] > 0) ? 'bg-success' : 'bg-danger';
                                            if (!empty($ket)) {
                                                $dispLabel .= " ($ket)";
                                                if ($op['poin'] == 0) $badgeClass = 'bg-warning text-dark';
                                            }
                                            break;
                                        }
                                    }
                                } else {
                                    $opsi = json_decode($p['opsi'], true);
                                    $sat = $opsi['satuan'] ?? '';
                                    $dispLabel = $val . ' ' . $sat;
                                    if (!empty($ket)) {
                                        $dispLabel .= " ($ket)";
                                    }
                                    $badgeClass = ($val > 0) ? 'bg-primary' : 'bg-danger';
                                }
                            }
                        ?>
                            <td class="text-center">
                                <span class="badge <?= $badgeClass ?> text-wrap" style="line-height:1.2">
                                    <?= htmlspecialchars($dispLabel) ?>
                                </span>
                                <?php if ($ans && $ans['poin'] > 0): ?>
                                    <div class="text-success mt-1" style="font-size: 0.7rem;">+<?= $ans['poin'] ?></div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        
                        <td class="text-center fw-bold text-primary">
                            <?= $s['total_poin'] ?? 0 ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
