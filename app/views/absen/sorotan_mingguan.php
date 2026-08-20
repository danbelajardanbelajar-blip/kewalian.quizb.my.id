<?php
/**
 * absen/sorotan_mingguan.php — Sorotan Mingguan
 */
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-calendar-week text-warning me-2"></i>
                Sorotan Mingguan
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                Pemantauan anomali dan kebohongan selama seminggu
            </p>
        </div>
    </div>
</div>

<!-- Pilih Minggu -->
<div class="card card-main shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-auto">
                <label class="fw-semibold me-2"><i class="bi bi-calendar3 me-1"></i> Pilih Minggu:</label>
                <input type="week" id="pilihMinggu" class="form-control form-control-sm d-inline-block"
                       style="max-width:200px" value="<?= $week ?>">
            </div>
            <div class="col-12 col-md">
                <span class="text-muted small">
                    Rentang: <?= date('d M Y', strtotime($dates[0])) ?> - <?= date('d M Y', strtotime($dates[6])) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php
// Cari ID pertanyaan yang berhubungan dengan Ngaji Pagi dan Shalat
$ngajiQIds = [];
$shalatQIds = [];

foreach ($pertanyaan as $p) {
    $label = strtolower($p['judul'] . ' ' . ($p['label_singkat'] ?? ''));
    if (strpos($label, 'ngaji pagi') !== false || strpos($label, 'ngaji subuh') !== false || strpos($label, 'ngaji') !== false) {
        $ngajiQIds[] = $p['id'];
    }
    if (stripos($p['opsi'], 'udzur') !== false) {
        $shalatQIds[] = $p['id'];
    }
}

$anomalies = [];

foreach ($siswa as $sObj) {
    $sId = $sObj['id'];
    $nama = $sObj['nama'];
    
    $udzurPerDay = []; // Array of true/false per date
    $missingDays = []; // Track missed days
    
    foreach ($dates as $d) {
        $dayData = $weeklyData[$d]['siswa'][$sId] ?? null;
        if (!$dayData) {
            $udzurPerDay[$d] = false;
            // Ignore if it's Friday (holiday) or in the future
            if (date('N', strtotime($d)) != 5 && strtotime($d) <= strtotime('today')) {
                $missingDays[] = date('d F Y', strtotime($d));
            }
            continue;
        }
        
        // Cek anomali 1: Ngaji Pagi bohong
        // weeklyData[$d] = form yang diisi PADA tgl $d, tentang kegiatan KEMARIN ($d-1)
        // Bukti kesiangan pada $d-1 = apakah ada form yang dikirim pada $d-1 lewat jam 07:00?
        $yesterday     = date('Y-m-d', strtotime($d . ' -1 day'));
        $jamLate       = $lateMap[$sId][$yesterday] ?? null; // HH:MM atau null

        if ($jamLate !== null) {
            foreach ($ngajiQIds as $qid) {
                if (isset($dayData['jawaban'][$qid])) {
                    $ans = strtolower(trim($dayData['jawaban'][$qid]['jawaban']));
                    if ($ans !== 'tidak' && $ans !== 'alpha' && $ans !== 'sakit' && $ans !== 'izin' && $ans !== 'udzur' && $ans !== 'tidak hadir') {
                        $tglKlaim   = isset($dayData['waktu_isi'])
                            ? date('d M Y', strtotime($dayData['waktu_isi']))
                            : date('d M Y', strtotime($d));
                        $tglKegiatan = date('d M Y', strtotime($yesterday));
                        $anomalies[] = [
                            'type'  => 'danger',
                            'title' => 'Indikasi Berbohong Ngaji Pagi - ' . htmlspecialchars($nama),
                            'desc'  => 'Pada tanggal <strong>' . $tglKlaim . '</strong> ananda mengklaim ikut ngaji pagi untuk tanggal <strong>' . $tglKegiatan . '</strong>. Namun record menunjukkan bahwa pada tanggal tersebut ananda baru mengisi form pukul <strong>' . $jamLate . '</strong> (setelah jam 07:00 pagi).'
                        ];
                        break;
                    }
                }
            }
        }
        
        // Kumpulkan data udzur per hari (Cek anomali 2)
        $hasUdzurToday = false;
        foreach ($shalatQIds as $qid) {
            if (isset($dayData['jawaban'][$qid])) {
                $ans = strtolower($dayData['jawaban'][$qid]['jawaban']);
                if (strpos($ans, 'udzur') !== false) {
                    $hasUdzurToday = true;
                    break;
                }
            }
        }
        $udzurPerDay[$d] = $hasUdzurToday;
    }
    
    // Cek pola udzur berseling-seling antar hari (misal: U -> N -> U)
    // Ubah ke array boolean
    $uArr = array_values($udzurPerDay);
    $blocks = 0;
    $inBlock = false;
    foreach ($uArr as $isU) {
        if ($isU && !$inBlock) {
            $blocks++;
            $inBlock = true;
        } elseif (!$isU && $inBlock) {
            $inBlock = false;
        }
    }
    
    if ($blocks > 1) {
        // Find which days were udzur for display
        $hariUdzur = [];
        foreach ($udzurPerDay as $d => $isU) {
            if ($isU) $hariUdzur[] = date('d M', strtotime($d));
        }
        
        $anomalies[] = [
            'type' => 'danger',
            'title' => 'Udzur Lintas Hari Berseling - ' . htmlspecialchars($nama),
            'desc' => 'Tercatat udzur pada hari yang tidak berurutan/terputus dalam minggu ini: <strong>' . implode(', ', $hariUdzur) . '</strong>.'
        ];
    }
    
    if (!empty($missingDays)) {
        $anomalies[] = [
            'type'  => 'warning',
            'title' => 'Tidak Mengisi Laporan - ' . htmlspecialchars($nama),
            'desc'  => 'Ananda Tidak mengisi laporan pada tanggal: <strong>' . implode(', ', $missingDays) . '</strong>'
        ];
    }
}
?>

<?php if (!empty($anomalies)): ?>
<div class="card shadow-sm border-warning mb-4">
    <div class="card-header bg-warning text-dark fw-bold">
        <i class="bi bi-exclamation-octagon me-2"></i> Detail Sorotan Mingguan (<?= count($anomalies) ?> Ditemukan)
    </div>
    <div class="card-body py-3">
        <div class="row g-3">
            <?php foreach ($anomalies as $anom): ?>
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-start border rounded p-3 bg-light h-100 border-<?= $anom['type'] ?>">
                    <div class="me-3 mt-1 text-<?= $anom['type'] ?>">
                        <i class="bi bi-exclamation-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark fs-5 mb-1"><?= $anom['title'] ?></strong>
                        <div class="text-muted"><?= $anom['desc'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
    <div>
        <strong>Tidak Ada Kejanggalan!</strong><br>
        Selama minggu ini tidak ada indikasi kebohongan ngaji pagi atau udzur yang tidak wajar.
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('pilihMinggu').addEventListener('change', function() {
    window.location.href = '?week=' + this.value;
});
</script>
