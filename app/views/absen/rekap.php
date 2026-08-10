<?php
/**
 * absen/rekap.php — Rekap Absen Mandiri (untuk Wali Kelas)
 */
$labelStatus = [
    'hadir'       => '<span class="badge bg-success">Hadir</span>',
    'absen'       => '<span class="badge bg-danger">Absen</span>',
    'sakit'       => '<span class="badge bg-warning text-dark">Sakit</span>',
    'izin'        => '<span class="badge bg-info text-dark">Izin</span>',
    'ikut'        => '<span class="badge bg-success">Ikut</span>',
    'udzur_haid'  => '<span class="badge bg-warning text-dark">Udzur</span>',
    'tidak_ikut'  => '<span class="badge bg-danger">Tidak</span>',
    'iya'         => '<span class="badge bg-success">Belajar</span>',
    'tidak'       => '<span class="badge bg-danger">Tidak</span>',
];

$siswaData = $dataTanggal['siswa'] ?? [];
$labelKat  = [
    'sekolah'  => '🏫 Sekolah',
    'almiftah' => '📖 Al-Miftah',
    'diniyah'  => '🌙 Diniyah',
    'subuh'    => '🌅 Ngaji Pagi',
    'quran'    => '📿 Al-Qur\'an',
    'dluha'    => '🕌 Dluha',
    'belajar'  => '📚 Belajar',
];
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-person-check me-2 text-primary"></i>
                Rekap Absen Mandiri
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                Data yang diisi sendiri oleh siswa
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/absen" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Form Siswa
            </a>
            <?php if (!empty($siswaData)): ?>
                <form action="<?= BASE_URL ?>/absen/hapus" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus seluruh data absen mandiri pada tanggal <?= $tanggal ?>? Tindakan ini tidak dapat dibatalkan.');">
                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Hapus Rekap
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pilih Tanggal -->
<div class="card card-main shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-auto">
                <label class="fw-semibold me-2"><i class="bi bi-calendar3 me-1"></i> Pilih Tanggal:</label>
                <input type="date" id="pilihTanggal" class="form-control form-control-sm d-inline-block"
                       style="max-width:200px" value="<?= $tanggal ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-12 col-md">
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ($allDates as $d): ?>
                        <a href="?tanggal=<?= $d['tanggal'] ?>"
                           class="badge <?= $d['tanggal'] === $tanggal ? 'bg-primary' : 'bg-secondary-subtle text-secondary' ?> text-decoration-none p-2">
                            <?= date('d/m', strtotime($d['tanggal'])) ?>
                            <span class="ms-1 opacity-75">(<?= $d['jumlah_isi'] ?>)</span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($allDates)): ?>
                        <span class="text-muted small">Belum ada data absen mandiri</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Total Siswa</div>
            <div class="stat-card-value"><?= $statistik['total'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Sudah Isi</div>
            <div class="stat-card-value text-success"><?= $statistik['sudah_isi'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Belum Isi</div>
            <div class="stat-card-value text-danger"><?= count($statistik['belum_isi']) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">% Partisipasi</div>
            <div class="stat-card-value">
                <?= $statistik['total'] > 0 ? round(($statistik['sudah_isi']/$statistik['total'])*100) : 0 ?>%
            </div>
        </div>
    </div>
</div>

<?php if (!empty($statistik['belum_isi'])): ?>
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <strong>Belum mengisi (<?= count($statistik['belum_isi']) ?> siswa):</strong><br>
            <small><?= implode(', ', array_map('htmlspecialchars', $statistik['belum_isi'])) ?></small>
        </div>
    </div>
<?php endif; ?>

<!-- Tabel detail -->
<?php if (!empty($siswaData)): ?>
    <div class="card card-main shadow-sm">
        <div class="card-header-custom">
            <i class="bi bi-table me-2"></i>
            Detail Isian Siswa — <?= date('d F Y', strtotime($tanggal)) ?>
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
                        <th class="text-center">Waktu Isi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswa as $sObj): ?>
                        <?php 
                        $namaSiswa = $sObj['nama'];
                        if (!isset($siswaData[$sObj['id']])) continue; 
                        $s = $siswaData[$sObj['id']]; 
                        ?>
                        <tr>
                            <td class="text-center text-muted" title="ID Siswa"><?= $sObj['id'] ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($namaSiswa) ?></td>

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

                            <td class="text-center"><?= $labelStatus[$s['dluha']['status'] ?? 'tidak_ikut'] ?></td>
                            <td class="text-center"><?= $labelStatus[$s['belajar']['status'] ?? 'tidak'] ?></td>
                            <td class="text-center"><?= $labelStatus[$s['memaafkan']['status'] ?? 'tidak'] ?? '-' ?></td>
                            <td class="text-center"><?= $labelStatus[$s['mendoakan_muslimin']['status'] ?? 'tidak'] ?? '-' ?></td>
                            <td class="text-center"><?= $labelStatus[$s['mendoakan_ortu']['status'] ?? 'tidak'] ?? '-' ?></td>
                            <td class="text-center"><?= $labelStatus[$s['shadaqah']['status'] ?? 'tidak'] ?? '-' ?></td>
                            <td class="text-center text-muted" style="font-size:.75rem">
                                <?= !empty($s['waktu_isi']) ? date('H:i', strtotime($s['waktu_isi'])) : '-' ?>
                            </td>
                            <td class="text-center">
                                <form action="<?= BASE_URL ?>/absen/hapus_siswa" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data absen <?= htmlspecialchars($namaSiswa) ?> pada tanggal ini?');">
                                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                                    <input type="hidden" name="id_siswa" value="<?= $sObj['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm p-1" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php elseif (empty($allDates)): ?>
    <div class="card card-main shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-person-x display-4 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Belum ada siswa yang mengisi absen mandiri</h5>
            <p class="text-muted small">Bagikan link berikut ke siswa:</p>
            <div class="input-group mx-auto" style="max-width:400px">
                <input type="text" class="form-control form-control-sm" id="linkAbsen"
                       value="<?= BASE_URL ?>/absen" readonly>
                <button class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('linkAbsen').value); this.textContent='Tersalin!'">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card card-main shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Tidak ada data untuk tanggal ini</h5>
        </div>
    </div>
<?php endif; ?>

<script>
document.getElementById('pilihTanggal').addEventListener('change', function () {
    window.location.href = '<?= BASE_URL ?>/absen/rekap?tanggal=' + this.value;
});
</script>
