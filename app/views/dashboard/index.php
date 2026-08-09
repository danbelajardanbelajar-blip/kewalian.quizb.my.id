<?php
/**
 * Dashboard/index.php — Form Input Presensi Harian
 */
$today = date('Y-m-d');
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-clipboard2-check me-2 text-primary"></i>
                Input Presensi Harian
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                <?php if ($isEdit): ?>
                    <span class="badge badge-edit"><i class="bi bi-pencil me-1"></i>Mode Edit</span>
                <?php else: ?>
                    <span class="badge badge-new"><i class="bi bi-plus-circle me-1"></i>Input Baru</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/laporan" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-journal-text me-1"></i> Riwayat Laporan
            </a>
        </div>
    </div>
</div>

<!-- Card Form -->
<div class="card card-main shadow-sm">
    <div class="card-body p-0">
        <form action="<?= BASE_URL ?>/laporan/simpan" method="POST" id="formPresensi">

            <!-- Tanggal & Aksi -->
            <div class="form-topbar px-4 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-auto">
                        <label for="tanggal" class="form-label fw-semibold mb-1">
                            <i class="bi bi-calendar3 me-1"></i> Tanggal Input
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                            <input type="date"
                                   class="form-control"
                                   id="tanggal"
                                   name="tanggal"
                                   value="<?= htmlspecialchars($tanggal) ?>"
                                   max="<?= $today ?>"
                                   required>
                        </div>
                    </div>

                    <div class="col-12 col-md">
                        <!-- Info Statistik Cepat -->
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="stat-badge">
                                <i class="bi bi-people-fill"></i>
                                <span><?= count($siswa) ?> Siswa</span>
                            </div>
                            <div class="stat-badge">
                                <i class="bi bi-list-check"></i>
                                <span><?= count($kategori) ?> Kategori</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-auto text-md-end">
                        <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                            <!-- Centang Semua / Hapus Semua -->
                            <button type="button" class="btn btn-outline-success btn-sm" id="btnCentangSemua">
                                <i class="bi bi-check-all me-1"></i> Centang Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnHapusSemua">
                                <i class="bi bi-x-square me-1"></i> Hapus Semua
                            </button>
                            <button type="submit" class="btn btn-primary-custom btn-sm" id="btnSimpan">
                                <i class="bi bi-save2 me-1"></i>
                                <?= $isEdit ? 'Perbarui Laporan' : 'Simpan Laporan' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Presensi -->
            <div class="table-responsive px-4 pb-4">
                <table class="table table-presensi table-hover align-middle mb-0" id="tablePresensi">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-no text-center">No</th>
                            <th rowspan="2" class="col-nama">Nama Siswa</th>
                            <th colspan="4" class="text-center border-start">
                                <i class="bi bi-door-open me-1"></i> Kehadiran
                                <small class="d-block fw-normal opacity-75">(✓ = Hadir)</small>
                            </th>
                            <th colspan="3" class="text-center border-start">
                                <i class="bi bi-star me-1"></i> Amaliyah & Kegiatan
                                <small class="d-block fw-normal opacity-75">(✓ = Selesai)</small>
                            </th>
                        </tr>
                        <tr>
                            <?php foreach ($kategori as $key => $label): ?>
                                <th class="text-center col-kategori" data-key="<?= htmlspecialchars($key) ?>">
                                    <div class="kategori-header">
                                        <?= htmlspecialchars($label) ?>
                                        <div class="mt-1">
                                            <button type="button" class="btn-col-toggle btn-col-all text-success"
                                                    data-key="<?= htmlspecialchars($key) ?>"
                                                    title="Centang semua kolom ini">
                                                <i class="bi bi-check-all"></i>
                                            </button>
                                            <button type="button" class="btn-col-toggle btn-col-none text-danger"
                                                    data-key="<?= htmlspecialchars($key) ?>"
                                                    title="Hapus centang kolom ini">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($siswa)): ?>
                            <?php foreach ($siswa as $index => $s): ?>
                                <?php
                                $inputName      = "data[{$index}]";
                                $existingData   = $existingSiswaData[$s['nama']] ?? [];
                                ?>
                                <tr class="siswa-row">
                                    <td class="text-center fw-bold text-muted" title="ID Siswa"><?= $s['id'] ?></td>
                                    <td class="fw-medium">
                                        <?= htmlspecialchars($s['nama']) ?>
                                        <input type="hidden" name="<?= $inputName ?>[nama]" value="<?= htmlspecialchars($s['nama']) ?>">
                                    </td>

                                    <?php foreach ($kategori as $key => $label): ?>
                                        <?php
                                        // Default checked jika input baru, atau ambil dari data existing
                                        if ($isEdit) {
                                            $checked = !empty($existingData[$key]);
                                        } else {
                                            $checked = true; // default semua hadir
                                        }
                                        ?>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center mb-0">
                                                <input class="form-check-input check-presensi shadow-none"
                                                       type="checkbox"
                                                       name="<?= $inputName ?>[<?= htmlspecialchars($key) ?>]"
                                                       value="1"
                                                       data-key="<?= htmlspecialchars($key) ?>"
                                                       <?= $checked ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 2 + count($kategori) ?>" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2"></i>
                                    Data siswa belum tersedia. Silakan tambah siswa di menu
                                    <a href="<?= BASE_URL ?>/siswa">Data Siswa</a>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </form>
    </div>
</div>

<!-- Script Interaksi Tabel -->
<script>
(function () {
    'use strict';

    // Centang/Hapus semua
    document.getElementById('btnCentangSemua').addEventListener('click', () => {
        document.querySelectorAll('.check-presensi').forEach(cb => cb.checked = true);
    });
    document.getElementById('btnHapusSemua').addEventListener('click', () => {
        document.querySelectorAll('.check-presensi').forEach(cb => cb.checked = false);
    });

    // Toggle per kolom
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

    // Ganti tanggal → redirect
    document.getElementById('tanggal').addEventListener('change', function () {
        window.location.href = '<?= BASE_URL ?>?tanggal=' + this.value;
    });

    // Submit loading
    document.getElementById('formPresensi').addEventListener('submit', function () {
        const btn = document.getElementById('btnSimpan');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        btn.disabled = true;
    });
})();
</script>
