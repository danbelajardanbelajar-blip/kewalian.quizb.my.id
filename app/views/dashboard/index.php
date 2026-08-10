<?php
/**
 * Dashboard/index.php — Halaman Analitik Kehadiran Siswa
 */
$today = date('Y-m-d');
?>

<!-- Tambahkan Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-pie-chart me-2 text-primary"></i>
                Dashboard Wali Kelas
            </h1>
            <p class="page-subtitle">
                Pantau statistik partisipasi kegiatan siswa kelas <strong><?= htmlspecialchars($kelas) ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/laporan/rekap" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-table me-1"></i> Lihat Rekap Detail
            </a>
        </div>
    </div>
</div>

<!-- Pemilih Tanggal -->
<div class="card card-main shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-auto">
                <label for="tanggal" class="form-label fw-semibold mb-0">
                    <i class="bi bi-calendar3 me-1"></i> Pilih Tanggal:
                </label>
            </div>
            <div class="col-12 col-md-auto">
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" max="<?= $today ?>">
            </div>
        </div>
    </div>
</div>

<!-- Baris Info Cepat (Cards) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Total Siswa</div>
            <div class="stat-card-value text-primary"><?= $stats['total'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Sudah Mengisi Absen</div>
            <div class="stat-card-value text-success"><?= $stats['sudah_isi'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Belum Mengisi Absen</div>
            <div class="stat-card-value text-danger"><?= count($stats['belum_isi']) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Partisipasi Pengisian</div>
            <?php $partisipasi = $stats['total'] > 0 ? round(($stats['sudah_isi'] / $stats['total']) * 100) : 0; ?>
            <div class="stat-card-value text-info"><?= $partisipasi ?>%</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Grafik Utama -->
    <div class="col-12 col-lg-8">
        <div class="card card-main shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    Grafik Persentase Kehadiran/Partisipasi
                </h5>
            </div>
            <div class="card-body">
                <?php if ($stats['sudah_isi'] > 0): ?>
                    <canvas id="kehadiranChart" style="max-height: 400px;"></canvas>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bar-chart display-1 text-light"></i>
                        <h4 class="mt-3">Belum Ada Data</h4>
                        <p>Belum ada siswa yang mengisi absen pada tanggal ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Samping (Daftar Siswa Belum Isi) -->
    <div class="col-12 col-lg-4">
        <div class="card card-main shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-danger">
                    <i class="bi bi-person-x-fill me-2"></i>
                    Belum Mengisi
                </h5>
                <span class="badge bg-danger rounded-pill"><?= count($stats['belum_isi']) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($stats['belum_isi'])): ?>
                    <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($stats['belum_isi'] as $nama): ?>
                            <li class="list-group-item px-4 py-3 d-flex align-items-center">
                                <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 35px; height: 35px;">
                                    <?= strtoupper(substr($nama, 0, 1)) ?>
                                </div>
                                <span class="fw-medium text-dark"><?= htmlspecialchars(ucwords(strtolower($nama))) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-5 px-3">
                        <div class="mb-3">
                            <i class="bi bi-emoji-smile text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-success fw-bold">Luar Biasa!</h5>
                        <p class="text-muted small mb-0">Semua siswa telah mengisi presensi mandiri hari ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reload halaman ketika tanggal diubah
    document.getElementById('tanggal').addEventListener('change', function () {
        window.location.href = '<?= BASE_URL ?>?tanggal=' + this.value;
    });

    <?php if ($stats['sudah_isi'] > 0): ?>
    // Data untuk grafik
    const labels = <?= json_encode($grafikData['labels']) ?>;
    const dataPersentase = <?= json_encode($grafikData['persentase']) ?>;
    const bgColors = <?= json_encode($grafikData['warna']) ?>;

    const ctx = document.getElementById('kehadiranChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Persentase Hadir / Mengikuti (%)',
                data: dataPersentase,
                backgroundColor: bgColors,
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.y + '% siswa berpartisipasi';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>
