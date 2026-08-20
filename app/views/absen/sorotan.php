<?php
/**
 * absen/sorotan.php — Sorotan & Kejanggalan
 */
$siswaData = $dataTanggal['siswa'] ?? [];
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-stars me-2 text-info"></i>
                Sorotan & Kejanggalan
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                Deteksi anomali pada jawaban siswa
            </p>
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
                           class="badge <?= $d['tanggal'] === $tanggal ? 'bg-info text-white' : 'bg-secondary-subtle text-secondary' ?> text-decoration-none p-2">
                            <?= date('d/m', strtotime($d['tanggal'])) ?>
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

<?php
// Anomali / Kejanggalan Calculation
$anomalies = [];
$totalSiswa = count($siswaData);

if ($totalSiswa > 0) {
    // 1. Total Poin Tertinggi dan Terendah
    $poinMax = ['val' => -9999, 'siswa' => []];
    $poinMin = ['val' => 99999, 'siswa' => []];
    
    foreach ($siswaData as $sId => $s) {
        $pt = (float)$s['total_poin'];
        if ($pt > $poinMax['val']) {
            $poinMax = ['val' => $pt, 'siswa' => [$s['nama']]];
        } elseif ($pt == $poinMax['val']) {
            $poinMax['siswa'][] = $s['nama'];
        }
        
        if ($pt < $poinMin['val']) {
            $poinMin = ['val' => $pt, 'siswa' => [$s['nama']]];
        } elseif ($pt == $poinMin['val']) {
            $poinMin['siswa'][] = $s['nama'];
        }
    }
    
    if ($poinMax['val'] != $poinMin['val']) {
        if ($poinMax['val'] !== -9999) {
            $anomalies[] = [
                'type' => 'success',
                'title' => 'Total Poin Tertinggi (' . $poinMax['val'] . ')',
                'desc' => implode(', ', $poinMax['siswa'])
            ];
        }
        if ($poinMin['val'] !== 99999) {
            $anomalies[] = [
                'type' => 'danger',
                'title' => 'Total Poin Terendah (' . $poinMin['val'] . ')',
                'desc' => implode(', ', $poinMin['siswa'])
            ];
        }
    }

    // 2. Anomali per pertanyaan
    foreach ($pertanyaan as $p) {
        $pId = $p['id'];
        $judul = trim($p['label_singkat'] ?? $p['judul']);
        
        if ($p['tipe'] === 'angka' || $p['tipe'] === 'ganda_dan_angka') {
            $max = ['val' => -9999, 'siswa' => []];
            $min = ['val' => 99999, 'siswa' => []];
            
            foreach ($siswaData as $sId => $s) {
                if (isset($s['jawaban'][$pId])) {
                    $ans = $s['jawaban'][$pId]['jawaban'];
                    $val = null;
                    if ($p['tipe'] === 'angka') {
                        $val = (float)$ans;
                    } elseif ($p['tipe'] === 'ganda_dan_angka') {
                        $parts = explode(':', $ans);
                        if (isset($parts[1])) $val = (float)$parts[1];
                    }
                    
                    if ($val !== null) {
                        if ($val > $max['val']) {
                            $max = ['val' => $val, 'siswa' => [$s['nama']]];
                        } elseif ($val == $max['val']) {
                            $max['siswa'][] = $s['nama'];
                        }
                        if ($val < $min['val']) {
                            $min = ['val' => $val, 'siswa' => [$s['nama']]];
                        } elseif ($val == $min['val']) {
                            $min['siswa'][] = $s['nama'];
                        }
                    }
                }
            }
            if ($max['val'] !== -9999 && $max['val'] != $min['val']) {
                $anomalies[] = [
                    'type' => 'primary',
                    'title' => $judul . ' - Nilai Tertinggi (' . $max['val'] . ')',
                    'desc' => implode(', ', $max['siswa'])
                ];
                $anomalies[] = [
                    'type' => 'warning',
                    'title' => $judul . ' - Nilai Terendah (' . $min['val'] . ')',
                    'desc' => implode(', ', $min['siswa'])
                ];
            }
        } elseif ($p['tipe'] === 'pilihan_ganda') {
            $freq = [];
            foreach ($siswaData as $sId => $s) {
                if (isset($s['jawaban'][$pId])) {
                    $val = $s['jawaban'][$pId]['jawaban'];
                    if (!isset($freq[$val])) $freq[$val] = [];
                    $freq[$val][] = $s['nama'];
                }
            }
            // Minoritas: <= 20% dari yang isi
            $jmlIsi = 0;
            foreach ($freq as $ans => $names) $jmlIsi += count($names);
            
            foreach ($freq as $ans => $names) {
                if (count($names) > 0 && count($names) <= ceil($jmlIsi * 0.20) && count($freq) > 1) {
                    $label = $ans;
                    $opsi = json_decode($p['opsi'], true);
                    if (is_array($opsi)) {
                        foreach ($opsi as $op) {
                            if (isset($op['value']) && $op['value'] == $ans) {
                                $label = $op['label'];
                                break;
                            }
                        }
                    }
                    $anomalies[] = [
                        'type' => 'secondary',
                        'title' => $judul . ' - Menjawab "' . $label . '" (Minoritas)',
                        'desc' => implode(', ', $names)
                    ];
                }
            }
        }
    }

    // 3. Anomali Udzur (Haid) pada shalat
    // Cari pertanyaan mana saja yang berhubungan dengan shalat (punya opsi 'udzur')
    $shalatQIds = [];
    foreach ($pertanyaan as $p) {
        if (stripos($p['opsi'], 'udzur') !== false) {
            $shalatQIds[] = $p['id'];
        }
    }
    
    if (count($shalatQIds) > 0) {
        foreach ($siswaData as $sId => $s) {
            $udzurAnswers = [];
            $totalShalatAnswered = 0;
            $udzurCount = 0;
            
            // Periksa jawaban shalat sesuai urutan pertanyaan
            foreach ($shalatQIds as $pId) {
                if (isset($s['jawaban'][$pId])) {
                    $ans = strtolower($s['jawaban'][$pId]['jawaban']);
                    $totalShalatAnswered++;
                    
                    $isUdzur = (strpos($ans, 'udzur') !== false);
                    $udzurAnswers[] = $isUdzur;
                    if ($isUdzur) $udzurCount++;
                }
            }
            
            // Jika ada yang dijawab udzur, tapi tidak semua, atau berseling
            if ($totalShalatAnswered > 0 && $udzurCount > 0 && $udzurCount < $totalShalatAnswered) {
                // Hitung blok / kelompok berurutan dari jawaban udzur
                $blocks = 0;
                $inBlock = false;
                foreach ($udzurAnswers as $isU) {
                    if ($isU && !$inBlock) {
                        $blocks++;
                        $inBlock = true;
                    } elseif (!$isU && $inBlock) {
                        $inBlock = false;
                    }
                }
                
                $isBerseling = ($blocks > 1);
                
                if ($isBerseling) {
                    $alasan = 'Udzur berseling-seling (tidak berurutan / terputus)';
                    $anomalies[] = [
                        'type' => 'danger',
                        'title' => 'Pola Udzur Tidak Wajar - ' . htmlspecialchars($s['nama']),
                        'desc' => $alasan . '. Menjawab udzur pada ' . $udzurCount . ' dari ' . $totalShalatAnswered . ' pertanyaan shalat.'
                    ];
                } else {
                    $alasan = 'Hanya udzur pada sebagian shalat (kemungkinan transisi haid)';
                    $anomalies[] = [
                        'type' => 'warning',
                        'title' => 'Pola Udzur Sebagian - ' . htmlspecialchars($s['nama']),
                        'desc' => $alasan . '. Menjawab udzur pada ' . $udzurCount . ' dari ' . $totalShalatAnswered . ' pertanyaan shalat.'
                    ];
                }
            }
        }
    }
    
    // 5. Cek siswa yang tidak mengisi (kecuali hari Jumat)
    if (date('N', strtotime($tanggal)) != 5) {
        foreach ($siswa as $sObj) {
            if (!isset($siswaData[$sObj['id']])) {
                $anomalies[] = [
                    'type' => 'warning',
                    'title' => 'Tidak Mengisi Laporan - ' . htmlspecialchars($sObj['nama']),
                    'desc' => 'Ananda Tidak mengisi laporan pada tanggal: <strong>' . date('d M Y', strtotime($tanggal)) . '</strong>'
                ];
            }
        }
    }
}
?>

<?php if (!empty($anomalies)): ?>
<div class="card shadow-sm border-info mb-4">
    <div class="card-header bg-info text-white fw-bold">
        <i class="bi bi-stars me-2"></i> Detail Sorotan Hari Ini (<?= count($anomalies) ?> Ditemukan)
    </div>
    <div class="card-body py-3">
        <div class="row g-3">
            <?php foreach ($anomalies as $anom): ?>
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-start border rounded p-3 bg-light h-100 border-<?= $anom['type'] ?>">
                    <div class="me-3 mt-1 text-<?= $anom['type'] ?>">
                        <i class="bi bi-info-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark fs-5 mb-1"><?= htmlspecialchars($anom['title']) ?></strong>
                        <div class="text-muted"><?= htmlspecialchars($anom['desc']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
    <?php if ($totalSiswa > 0): ?>
    <div class="alert alert-success d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
        <div>
            <strong>Tidak Ada Kejanggalan!</strong><br>
            Semua jawaban pada tanggal ini seragam atau belum cukup data untuk menentukan anomali.
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-secondary d-flex align-items-center mb-4">
        <i class="bi bi-info-circle fs-4 me-3"></i>
        <div>Belum ada data dari siswa pada tanggal ini.</div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<script>
document.getElementById('pilihTanggal').addEventListener('change', function() {
    window.location.href = '?tanggal=' + this.value;
});
</script>
