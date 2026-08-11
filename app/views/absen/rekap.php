<?php
/**
 * absen/rekap.php — Rekap Absen Mandiri Dinamis (untuk Wali Kelas)
 */
$siswaData = $dataTanggal['siswa'] ?? [];
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
            <a href="<?= BASE_URL ?>/absen?wali=<?= urlencode($usernameWali) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
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

<?= Flash::render() ?>

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
                        <?php foreach ($pertanyaan as $p): ?>
                            <th class="text-center" style="min-width:100px"><?= htmlspecialchars($p['judul']) ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">Total Poin</th>
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
                            <td class="text-center text-muted"><?= $sObj['id'] ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($namaSiswa) ?></td>

                            <?php 
                            $waMessage = "Salam Ayah dan Ibu.\nIni kulo, ananda *" . ucwords(strtolower(trim($namaSiswa))) . "*.\n";
                            $waMessage .= "Berikut adalah rekap harian kulo untuk hari ini:\n\n";

                            foreach ($pertanyaan as $p): 
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
                                    
                                    $waMessage .= "• " . $p['judul'] . ": " . $dispLabel . " (+" . $poin . " Poin)\n";
                                }
                            ?>
                                <td class="text-center">
                                    <span class="badge <?= $badgeClass ?> text-wrap" style="line-height:1.2">
                                        <?= htmlspecialchars($dispLabel) ?>
                                    </span>
                                </td>
                            <?php endforeach; ?>

                            <td class="text-center fw-bold text-primary">
                                <?= $s['total_poin'] ?? 0 ?>
                            </td>

                            <td class="text-center text-muted" style="font-size:.75rem">
                                <?= !empty($s['waktu_isi']) ? date('H:i', strtotime($s['waktu_isi'])) : '-' ?>
                            </td>
                            <td class="text-center text-nowrap">
                                <?php
                                $noHp = $sObj['no_hp'] ?? '';
                                if (!empty($noHp)) {
                                    $waMessage .= "\nTotal Poin Harian: " . ($s['total_poin'] ?? 0) . " Poin.\nMohon doanya Ayah dan Ibu. Matur nuwun.";
                                    $linkWa = "https://wa.me/" . urlencode($noHp) . "?text=" . urlencode($waMessage);
                                    echo '<a href="' . $linkWa . '" target="_blank" class="btn btn-outline-success btn-sm p-1 me-1" title="Kirim WA ke Wali"><i class="bi bi-whatsapp"></i></a>';
                                }
                                ?>
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
                       value="<?= BASE_URL ?>/absen?wali=<?= urlencode($usernameWali) ?>" readonly>
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
