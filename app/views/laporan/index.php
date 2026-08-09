<?php
/**
 * laporan/index.php — Daftar Riwayat Laporan
 */
?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-journal-text me-2 text-primary"></i>
                Riwayat Laporan Presensi
            </h1>
            <p class="page-subtitle">Semua laporan presensi harian yang tersimpan</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/laporan/rekap" class="btn btn-outline-info btn-sm">
                <i class="bi bi-bar-chart-line me-1"></i> Lihat Rekap
            </a>
            <a href="<?= BASE_URL ?>" class="btn btn-primary-custom btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Input Presensi Baru
            </a>
        </div>
    </div>
</div>

<?php if (empty($laporan)): ?>
    <!-- Empty State -->
    <div class="card card-main shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-x display-4 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Belum ada laporan tersimpan</h5>
            <p class="text-muted small">Mulai dengan input presensi harian pertama Anda.</p>
            <a href="<?= BASE_URL ?>" class="btn btn-primary-custom mt-2">
                <i class="bi bi-plus-circle me-1"></i> Input Presensi Sekarang
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Tabel Laporan -->
    <div class="card card-main shadow-sm">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-list-ul me-2"></i>
                Total: <strong><?= count($laporan) ?></strong> laporan
            </span>
            <div class="input-group input-group-sm" style="max-width:250px">
                <span class="input-group-text bg-transparent">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0" id="searchLaporan"
                       placeholder="Cari tanggal...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tableLaporan">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Kelas</th>
                        <th class="text-center">Jml Siswa</th>
                        <th>Terakhir Diperbarui</th>
                        <th>Disimpan Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan as $i => $row): ?>
                        <tr class="laporan-row">
                            <td class="text-center text-muted fw-bold"><?= $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="date-badge">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold tanggal-text">
                                            <?= date('d F Y', strtotime($row['tanggal'])) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('l', strtotime($row['tanggal'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($row['kelas']) ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success fw-semibold">
                                    <?= $row['jumlah_siswa'] ?> siswa
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-' ?>
                            </td>
                            <td>
                                <i class="bi bi-person-fill text-muted me-1"></i>
                                <?= htmlspecialchars($row['created_by'] ?? '-') ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="<?= BASE_URL ?>/laporan/lihat/<?= $row['tanggal'] ?>"
                                       class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/laporan/edit/<?= $row['tanggal'] ?>"
                                       class="btn btn-sm btn-outline-warning" title="Edit Laporan">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/laporan/export/<?= $row['tanggal'] ?>"
                                       class="btn btn-sm btn-outline-success" title="Export CSV">
                                        <i class="bi bi-file-earmark-excel"></i>
                                    </a>
                                    <form action="<?= BASE_URL ?>/laporan/hapus/<?= $row['tanggal'] ?>"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus laporan tanggal <?= date('d/m/Y', strtotime($row['tanggal'])) ?>? Tindakan ini tidak dapat dibatalkan.')">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // Search filter
    document.getElementById('searchLaporan').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#tableLaporan tbody .laporan-row').forEach(row => {
            const txt = row.querySelector('.tanggal-text').textContent.toLowerCase();
            row.style.display = txt.includes(q) ? '' : 'none';
        });
    });
    </script>
<?php endif; ?>
