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
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h3><?= htmlspecialchars($myRank["rank"] ?? "-") ?></h3>
                    <p>Peringkat Kelas</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h3><?= number_format($myRank["total_poin"] ?? 0) ?></h3>
                    <p>Total Poin</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h3><?= count($progress) ?></h3>
                    <p>Hari Kehadiran</p>
                </div>
            </div>
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
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i> Riwayat Absensi Terakhir</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="py-3 text-center">Poin Didapat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($progress)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">Belum ada data absensi.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $reversedProgress = array_reverse($progress);
                                foreach (array_slice($reversedProgress, 0, 10) as $row): 
                                ?>
                                    <tr>
                                        <td class="px-4"><?= date("d F Y", strtotime($row["tanggal"])) ?></td>
                                        <td class="text-center fw-bold text-primary"><?= number_format($row["total_poin"]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
