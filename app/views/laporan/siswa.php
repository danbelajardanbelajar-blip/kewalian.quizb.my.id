<?php
/**
 * laporan/siswa.php — Performa Per Siswa (untuk Wali Kelas)
 * Menggunakan data & layout yang sama dengan walimurid/laporan.php
 * namun diakses melalui layout wali kelas (dengan navbar).
 */
function simplifyQuestionWK($text) {
    $text = strtolower(trim($text));
    $removeWords = [
        'apakah ', 'kemarin ', 'pagi ', 'sore ', 'malam ', 'siang ', 'hari ini', 'tadi ',
        'ananda ', '{{nama}}', 'berapa ', 'kapan ', 'sudahkah ', 'telahkah ', 'tolong '
    ];
    foreach ($removeWords as $word) {
        $text = str_replace($word, '', $text);
    }
    $text = str_replace(['?', ':', '!', '.', ','], '', $text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return ucwords($text);
}
?>

<!-- Breadcrumb & Header -->
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/laporan">Riwayat</a></li>
            <li class="breadcrumb-item active">Performa <?= htmlspecialchars($siswa['nama']) ?></li>
        </ol>
    </nav>
    <h1 class="page-title">
        <i class="bi bi-person-badge text-primary me-2"></i>
        <?= htmlspecialchars($siswa['nama']) ?>
    </h1>
    <p class="page-subtitle">Performa &amp; riwayat harian — sama seperti yang dilihat wali murid</p>
</div>

<!-- Rating Periodik -->
<div class="row mb-4">
    <?php
    $periods = [
        ['title' => 'Performa Mingguan', 'key' => 'mingguan', 'subtitle' => '7 Hari Terakhir'],
        ['title' => 'Performa Bulanan',  'key' => 'bulanan',  'subtitle' => '30 Hari Terakhir'],
        ['title' => 'Performa Tahunan',  'key' => 'tahunan',  'subtitle' => '1 Tahun Terakhir'],
    ];
    foreach ($periods as $p):
        $r   = $myRank[$p['key']]['rating'];
        $pt  = $myRank[$p['key']]['total_poin'];
        $avg = $myRank[$p['key']]['avg_kelas'];
    ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100 text-center p-3 shadow-sm">
            <div class="mb-2 text-warning fs-3">
                <?php for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= $r) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star text-secondary"></i>';
                } ?>
            </div>
            <div class="fw-bold"><?= $p['title'] ?></div>
            <small class="text-muted d-block mb-2"><?= $p['subtitle'] ?></small>
            <div class="d-flex justify-content-center gap-3 text-muted small">
                <span><i class="bi bi-award text-primary me-1"></i><?= number_format($pt) ?> Poin</span>
                <span><i class="bi bi-people me-1"></i>Rata-rata: <?= number_format($avg, 1) ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Grafik -->
<div class="card card-main shadow-sm mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4"><i class="bi bi-graph-up me-2 text-primary"></i> Grafik Progres Harian</h5>
        <canvas id="progressChart" height="80"></canvas>
    </div>
</div>

<!-- Riwayat Detail Accordion -->
<div class="card card-main shadow-sm">
    <div class="card-header-custom">
        <i class="bi bi-list-check me-2"></i> Rincian Absensi &amp; Jawaban
    </div>
    <div class="card-body p-4">
        <?php if (empty($riwayatDetail)): ?>
            <div class="text-center py-4 text-muted">Belum ada data absensi untuk siswa ini.</div>
        <?php else: ?>
        <div class="accordion" id="accordionRiwayat">
            <?php $i = 0; foreach ($riwayatDetail as $tanggal => $dayData): $isFirst = ($i === 0); ?>
            <div class="accordion-item border mb-2 rounded shadow-sm">
                <h2 class="accordion-header" id="heading<?= $i ?>">
                    <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?> rounded" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>"
                            aria-expanded="<?= $isFirst ? 'true' : 'false' ?>">
                        <div class="d-flex flex-column w-100 pe-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">
                                    <i class="bi bi-calendar-check me-2 text-primary"></i>
                                    <?= date('d F Y', strtotime($tanggal)) ?>
                                </span>
                                <span class="badge bg-primary rounded-pill px-3 py-2"><?= number_format($dayData['total_poin']) ?> Poin</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span>
                                    <?php $dr = $dayData['rating'] ?? 0;
                                    for ($r = 1; $r <= 5; $r++) {
                                        echo ($r <= $dr) ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-secondary"></i>';
                                    } ?>
                                </span>
                                <span>Rata-rata kelas: <?= number_format($dayData['avg_kelas'] ?? 0, 1) ?></span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
                     data-bs-parent="#accordionRiwayat">
                    <div class="accordion-body bg-light">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($dayData['detail'] as $det):
                                $ansLower = strtolower(trim($det['jawaban']));
                                $poin     = (int)$det['poin'];

                                if (preg_match('/\b(sakit|izin|udzur|haid)\b/i', $ansLower)) {
                                    $icon = 'bi-exclamation-circle-fill'; $tc = 'text-warning'; $bc = 'bg-warning text-dark';
                                } elseif ($poin > 0) {
                                    $icon = 'bi-check-circle-fill'; $tc = 'text-success'; $bc = 'bg-success';
                                } else {
                                    $icon = 'bi-x-circle-fill'; $tc = 'text-danger'; $bc = 'bg-danger';
                                }
                            ?>
                            <li class="list-group-item bg-transparent px-0 py-3 border-bottom-dashed">
                                <div class="text-muted small fw-medium mb-1 text-uppercase">
                                    <?= !empty($det['label_singkat'])
                                        ? htmlspecialchars(trim($det['label_singkat']))
                                        : htmlspecialchars(simplifyQuestionWK($det['pertanyaan'])) ?>
                                </div>
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="fw-bold <?= $tc ?> d-flex align-items-center gap-2" style="font-size:1.1rem">
                                        <i class="bi <?= $icon ?> fs-5"></i>
                                        <?= htmlspecialchars($det['jawaban']) ?>
                                    </div>
                                    <span class="badge <?= $bc ?> rounded-pill px-3 py-2">+<?= $det['poin'] ?></span>
                                </div>
                                <?php if (!empty($det['keterangan'])): ?>
                                <div class="text-secondary small fst-italic mt-2 p-2 bg-white rounded border-start border-3 <?= str_replace('text-', 'border-', $tc) ?>">
                                    <i class="bi bi-chat-text me-1"></i> <?= htmlspecialchars($det['keterangan']) ?>
                                </div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php $i++; endforeach; ?>
        </div>
        <style>
            .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: inherit; box-shadow: none; }
            .accordion-item { border-radius: .5rem !important; overflow: hidden; }
            .accordion-button:focus { box-shadow: none; }
            .border-bottom-dashed { border-bottom: 1px dashed #dee2e6 !important; }
            .border-bottom-dashed:last-child { border-bottom: none !important; }
        </style>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const progressData = <?= json_encode(array_values($progress)) ?>;
    const labels   = progressData.map(d => {
        const dt = new Date(d.tanggal);
        return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    });
    const dataPoin = progressData.map(d => parseInt(d.total_poin));
    const ctx = document.getElementById('progressChart');
    if (ctx && progressData.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Poin Harian',
                    data: dataPoin,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
