<?php
$adminTitle = 'Kunjungan';
$adminActivePage = 'kunjungan';
$adminSubtitle = 'Log dan statistik kunjungan aplikasi';
$unreadFeedback = $unreadFeedback ?? 0;
require_once APP_PATH . '/views/admin/layout_admin.php';
?>
<div class="admin-content">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="bi bi-eye-fill fs-3 text-primary"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Total Kunjungan All Time</h6>
                    <h3 class="mb-0 fw-bold"><?= number_format($totalKunjungan ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="btn-group shadow-sm" role="group">
                <button type="button" class="btn btn-primary" onclick="updateChart('7')">7 Hari</button>
                <button type="button" class="btn btn-outline-primary" onclick="updateChart('30')">30 Hari</button>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="admin-card card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0" id="chartTitle">Tren Kunjungan (7 Hari)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trenKunjunganChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="admin-card card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">Kunjungan per Halaman</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table admin-table table-borderless table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Halaman</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="pe-4" style="width: 40%;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($perHalaman)): 
                                    $max = 0;
                                    foreach($perHalaman as $p) {
                                        if($p['jumlah'] > $max) $max = $p['jumlah'];
                                    }
                                ?>
                                    <?php foreach($perHalaman as $hal): 
                                        $pct = $max > 0 ? ($hal['jumlah'] / $max) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($hal['halaman'] ?? '') ?>">
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($hal['halaman'] ?? '') ?></span>
                                        </td>
                                        <td class="text-end fw-bold"><?= number_format($hal['jumlah']) ?></td>
                                        <td class="pe-4">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7 mb-4">
            <div class="admin-card card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Log Kunjungan Terbaru</h5>
                    <small class="text-muted">
                        Halaman <?= $page ?> dari <?= $totalPages ?>
                        (<?= number_format($totalKunjungan) ?> total)
                    </small>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table admin-table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-4">Waktu</th>
                                    <th>IP</th>
                                    <th>Halaman</th>
                                    <th class="pe-4">User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($kunjunganList)): ?>
                                    <?php foreach($kunjunganList as $log): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="small fw-bold"><?= date('d M', strtotime($log['created_at'])) ?></div>
                                            <div class="small text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                        </td>
                                        <td><span class="font-monospace small"><?= htmlspecialchars($log['ip'] ?? '') ?></span></td>
                                        <td><span class="badge bg-light text-primary border"><?= htmlspecialchars($log['halaman'] ?? '') ?></span></td>
                                        <td class="pe-4 text-truncate small" style="max-width: 150px;" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                            <?= htmlspecialchars($log['user_agent'] ?? '') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($totalPages > 1): ?>
                    <div class="px-4 py-3 border-top">
                        <nav>
                            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap gap-1">
                                <!-- Prev -->
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/admin/kunjungan?page=<?= $page - 1 ?>">&laquo;</a>
                                </li>

                                <?php
                                $start = max(1, $page - 2);
                                $end   = min($totalPages, $page + 2);
                                if($start > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/admin/kunjungan?page=1">1</a></li>
                                    <?php if($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                <?php endif; ?>

                                <?php for($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/admin/kunjungan?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if($end < $totalPages): ?>
                                    <?php if($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/admin/kunjungan?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                                <?php endif; ?>

                                <!-- Next -->
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/admin/kunjungan?page=<?= $page + 1 ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let myChart = null;
const data7 = <?= json_encode($chartData7 ?? []) ?>;
const data30 = <?= json_encode($chartData30 ?? []) ?>;

function renderChart(dataArr) {
    if(!dataArr || dataArr.length === 0) return;
    
    const labels = dataArr.map(item => item.tanggal);
    const data = dataArr.map(item => item.jumlah);
    
    const ctx = document.getElementById('trenKunjunganChart').getContext('2d');
    
    if(myChart != null) {
        myChart.destroy();
    }
    
    myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Kunjungan',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(54, 162, 235, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { borderDash: [2, 4], color: 'rgba(0, 0, 0, 0.05)' } }
            }
        }
    });
}

function updateChart(days) {
    const btns = document.querySelectorAll('.btn-group .btn');
    btns.forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    
    if(days === '7') {
        document.querySelector('.btn-group .btn:nth-child(1)').classList.add('btn-primary');
        document.querySelector('.btn-group .btn:nth-child(1)').classList.remove('btn-outline-primary');
        document.getElementById('chartTitle').innerText = 'Tren Kunjungan (7 Hari)';
        renderChart(data7);
    } else {
        document.querySelector('.btn-group .btn:nth-child(2)').classList.add('btn-primary');
        document.querySelector('.btn-group .btn:nth-child(2)').classList.remove('btn-outline-primary');
        document.getElementById('chartTitle').innerText = 'Tren Kunjungan (30 Hari)';
        renderChart(data30);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderChart(data7);
});
</script>
<?php require_once APP_PATH . '/views/admin/layout_admin_footer.php'; ?>
