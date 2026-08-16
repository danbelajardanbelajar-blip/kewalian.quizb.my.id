<?php
function simplifyQuestion($text) {
    $text = strtolower(trim($text));
    $removeWords = [
        'apakah ', 'kemarin ', 'pagi ', 'sore ', 'malam ', 'siang ', 'hari ini', 'tadi ', 'ananda ', '{{nama}}', 'berapa ', 'kapan ', 'sudahkah ', 'telahkah ', 'tolong '
    ];
    foreach ($removeWords as $word) {
        $text = str_replace($word, '', $text);
    }
    $text = str_replace(['?', ':', '!', '.', ','], '', $text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return ucwords($text);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f8f9fa; }
        .card-main { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.05); }
        .stat-card { background: #fff; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .stat-card h3 { margin: 0; font-size: 2rem; color: #0d6efd; font-weight: bold; }
        .stat-card p { margin: 0; color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i> Laporan Siswa
            </a>
            <div class="d-flex">
                <a href="<?= BASE_URL ?>/walimurid/logout?id=<?= $siswa["id"] ?>" class="btn btn-sm btn-light text-primary fw-bold">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <h4 class="mb-0">Halo, Wali dari <?= htmlspecialchars($siswa["nama"]) ?></h4>
                <p class="text-muted mb-0">Ini adalah ringkasan progres harian ananda.</p>
            </div>
        </div>

        <div class="row mb-4">
            <?php
            $periods = [
                ['title' => 'Performa Mingguan', 'key' => 'mingguan', 'subtitle' => '7 Hari Terakhir'],
                ['title' => 'Performa Bulanan', 'key' => 'bulanan', 'subtitle' => '30 Hari Terakhir'],
                ['title' => 'Performa Tahunan', 'key' => 'tahunan', 'subtitle' => '1 Tahun Terakhir']
            ];
            foreach ($periods as $p):
                $r = $myRank[$p['key']]['rating'];
                $pt = $myRank[$p['key']]['total_poin'];
                $avg = $myRank[$p['key']]['avg_kelas'];
            ?>
            <div class="col-md-4 mb-3">
                <div class="stat-card py-3">
                    <h3 class="mb-1 text-warning">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $r) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star text-light"></i>';
                        }
                        ?>
                    </h3>
                    <p class="mb-0 fw-bold"><?= $p['title'] ?></p>
                    <small class="text-muted d-block mb-2" style="font-size:0.75rem;"><?= $p['subtitle'] ?></small>
                    <div class="d-flex justify-content-center gap-3 text-muted" style="font-size:0.8rem;">
                        <span><i class="bi bi-award text-primary me-1"></i> <?= number_format($pt) ?> Poin</span>
                        <span><i class="bi bi-people me-1"></i> Rata-rata: <?= number_format($avg, 1) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card card-main mb-4">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4"><i class="bi bi-graph-up me-2 text-primary"></i> Grafik Progres Harian</h5>
                <canvas id="progressChart" height="100"></canvas>
            </div>
        </div>

        <div class="card card-main">
            <div class="card-body p-0">
                <div class="p-4 border-bottom bg-light rounded-top">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i> Rincian Absensi & Jawaban Terakhir</h5>
                </div>
                <div class="p-4">
                    <?php if (empty($riwayatDetail)): ?>
                        <div class="text-center py-4 text-muted">Belum ada data absensi.</div>
                    <?php else: ?>
                        <div class="accordion" id="accordionRiwayat">
                            <?php 
                            $i = 0;
                            foreach ($riwayatDetail as $tanggal => $data): 
                                $isFirst = ($i === 0);
                            ?>
                                <div class="accordion-item border mb-2 rounded shadow-sm">
                                    <h2 class="accordion-header" id="heading<?= $i ?>">
                                        <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?> rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>" aria-controls="collapse<?= $i ?>">
                                            <div class="d-flex flex-column w-100 pe-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold"><i class="bi bi-calendar-check me-2 text-primary"></i><?= date("d F Y", strtotime($tanggal)) ?></span>
                                                    <span class="badge bg-primary rounded-pill px-3 py-2"><?= number_format($data['total_poin']) ?> Poin</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.8rem;">
                                                    <span>
                                                        <?php 
                                                        $dailyRating = $data['rating'] ?? 0;
                                                        for ($r = 1; $r <= 5; $r++) {
                                                            if ($r <= $dailyRating) {
                                                                echo '<i class="bi bi-star-fill text-warning"></i>';
                                                            } else {
                                                                echo '<i class="bi bi-star text-light"></i>';
                                                            }
                                                        }
                                                        ?>
                                                    </span>
                                                    <span>Rata-rata kelas: <?= number_format($data['avg_kelas'] ?? 0, 1) ?></span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>" aria-labelledby="heading<?= $i ?>" data-bs-parent="#accordionRiwayat">
                                        <div class="accordion-body bg-light">
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($data['detail'] as $det): 
                                                    $isPositive = ((int)$det['poin'] > 0);
                                                    $badgeClass = $isPositive ? 'bg-success' : 'bg-danger';
                                                    $textClass = $isPositive ? 'text-success' : 'text-danger';
                                                    $icon = $isPositive ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                                                    
                                                    // Deteksi tipe jawaban dari teks
                                                    $ansLower = strtolower($det['jawaban']);
                                                    if (strpos($ansLower, 'sudah') !== false || strpos($ansLower, 'hadir') !== false || strpos($ansLower, 'ya') !== false) {
                                                        $icon = 'bi-check-circle-fill';
                                                        $badgeClass = 'bg-success';
                                                        $textClass = 'text-success';
                                                    } elseif (strpos($ansLower, 'belum') !== false || strpos($ansLower, 'tidak') !== false || strpos($ansLower, 'alpa') !== false) {
                                                        $icon = 'bi-x-circle-fill';
                                                        $badgeClass = 'bg-danger';
                                                        $textClass = 'text-danger';
                                                    } elseif (strpos($ansLower, 'sakit') !== false || strpos($ansLower, 'izin') !== false) {
                                                        $icon = 'bi-exclamation-circle-fill';
                                                        $badgeClass = 'bg-warning text-dark';
                                                        $textClass = 'text-warning';
                                                    }
                                                ?>
                                                    <li class="list-group-item bg-transparent px-0 py-3 border-bottom-dashed">
                                                        <div class="text-muted small fw-medium mb-1 text-uppercase tracking-wider">
                                                            <?= !empty($det['label_singkat']) ? htmlspecialchars(trim($det['label_singkat'])) : htmlspecialchars(simplifyQuestion($det['pertanyaan'])) ?>
                                                        </div>
                                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                                            <div class="fw-bold <?= $textClass ?> d-flex align-items-center gap-2" style="font-size: 1.15rem;">
                                                                <i class="bi <?= $icon ?> fs-4"></i>
                                                                <?= htmlspecialchars($det['jawaban']) ?>
                                                            </div>
                                                            <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2 fs-6">+<?= $det['poin'] ?></span>
                                                        </div>
                                                        <?php if (!empty($det['keterangan'])): ?>
                                                            <div class="text-secondary small fst-italic mt-2 p-2 bg-light rounded border-start border-3 <?= str_replace('text-', 'border-', $textClass) ?>">
                                                                <i class="bi bi-chat-text me-1"></i> <?= htmlspecialchars($det['keterangan']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                $i++;
                                // Tampilkan max 10 data terakhir
                                if ($i >= 10) break;
                            endforeach; 
                            ?>
                        </div>
                        <style>
                            .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: inherit; box-shadow: none; }
                            .accordion-item { border-radius: 0.5rem !important; overflow: hidden; }
                            .accordion-button:focus { box-shadow: none; }
                            .border-bottom-dashed { border-bottom: 1px dashed #dee2e6 !important; }
                            .border-bottom-dashed:last-child { border-bottom: none !important; }
                        </style>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const progressData = <?= json_encode($progress) ?>;
            const labels = progressData.map(d => {
                const date = new Date(d.tanggal);
                return date.toLocaleDateString("id-ID", { day: "numeric", month: "short" });
            });
            const dataPoin = progressData.map(d => parseInt(d.total_poin));

            const ctx = document.getElementById("progressChart");
            if (ctx && progressData.length > 0) {
                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Poin Harian",
                            data: dataPoin,
                            borderColor: "#0d6efd",
                            backgroundColor: "rgba(13, 110, 253, 0.1)",
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: "#0d6efd"
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
