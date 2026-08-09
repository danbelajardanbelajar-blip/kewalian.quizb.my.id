<?php
/**
 * laporan/edit.php — Form Edit Laporan
 * Sama seperti dashboard/index.php tapi untuk edit laporan yang sudah ada
 */
?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/laporan">Riwayat</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/laporan/lihat/<?= $tanggal ?>"><?= date('d F Y', strtotime($tanggal)) ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h1 class="page-title">
                <i class="bi bi-pencil-square me-2 text-warning"></i>
                Edit Laporan Presensi
            </h1>
            <p class="page-subtitle">
                <?= date('l, d F Y', strtotime($tanggal)) ?> — Kelas <strong><?= htmlspecialchars($kelas) ?></strong>
            </p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/laporan/lihat/<?= $tanggal ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> Batal Edit
            </a>
        </div>
    </div>
</div>

<div class="card card-main shadow-sm">
    <div class="card-body p-0">
        <form action="<?= BASE_URL ?>/laporan/simpan" method="POST" id="formEdit">
            <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">

            <div class="form-topbar px-4 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="stat-badge bg-warning-subtle text-warning">
                                <i class="bi bi-pencil"></i>
                                <span>Mode Edit Aktif</span>
                            </div>
                            <div class="stat-badge">
                                <i class="bi bi-people-fill"></i>
                                <span><?= count($siswa) ?> Siswa</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto text-md-end">
                        <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                            <button type="button" class="btn btn-outline-success btn-sm" id="btnCentangSemua">
                                <i class="bi bi-check-all me-1"></i> Centang Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnHapusSemua">
                                <i class="bi bi-x-square me-1"></i> Hapus Semua
                            </button>
                            <button type="submit" class="btn btn-warning btn-sm fw-semibold" id="btnSimpan">
                                <i class="bi bi-save2 me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Edit -->
            <div class="table-responsive px-4 pb-4">
                <table class="table table-presensi table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-no text-center">No</th>
                            <th rowspan="2" class="col-nama">Nama Siswa</th>
                            <th colspan="4" class="text-center border-start">
                                <i class="bi bi-door-open me-1"></i> Kehadiran
                            </th>
                            <th colspan="3" class="text-center border-start">
                                <i class="bi bi-star me-1"></i> Amaliyah & Kegiatan
                            </th>
                        </tr>
                        <tr>
                            <?php foreach ($kategori as $key => $label): ?>
                                <th class="text-center col-kategori">
                                    <div class="kategori-header">
                                        <?= htmlspecialchars($label) ?>
                                        <div class="mt-1">
                                            <button type="button" class="btn-col-toggle btn-col-all text-success"
                                                    data-key="<?= htmlspecialchars($key) ?>">
                                                <i class="bi bi-check-all"></i>
                                            </button>
                                            <button type="button" class="btn-col-toggle btn-col-none text-danger"
                                                    data-key="<?= htmlspecialchars($key) ?>">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($siswa as $index => $nama): ?>
                            <?php $existing = $existingSiswaData[$nama] ?? []; ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                <td class="fw-medium">
                                    <?= htmlspecialchars($nama) ?>
                                    <input type="hidden" name="data[<?= $index ?>][nama]" value="<?= htmlspecialchars($nama) ?>">
                                </td>
                                <?php foreach ($kategori as $key => $label): ?>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center mb-0">
                                            <input class="form-check-input check-presensi shadow-none"
                                                   type="checkbox"
                                                   name="data[<?= $index ?>][<?= htmlspecialchars($key) ?>]"
                                                   value="1"
                                                   data-key="<?= htmlspecialchars($key) ?>"
                                                   <?= !empty($existing[$key]) ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    document.getElementById('btnCentangSemua').addEventListener('click', () => {
        document.querySelectorAll('.check-presensi').forEach(cb => cb.checked = true);
    });
    document.getElementById('btnHapusSemua').addEventListener('click', () => {
        document.querySelectorAll('.check-presensi').forEach(cb => cb.checked = false);
    });
    document.querySelectorAll('.btn-col-all').forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.key;
            document.querySelectorAll(`.check-presensi[data-key="${key}"]`).forEach(cb => cb.checked = true);
        });
    });
    document.querySelectorAll('.btn-col-none').forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.key;
            document.querySelectorAll(`.check-presensi[data-key="${key}"]`).forEach(cb => cb.checked = false);
        });
    });
    document.getElementById('formEdit').addEventListener('submit', function () {
        const btn = document.getElementById('btnSimpan');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        btn.disabled = true;
    });
})();
</script>
