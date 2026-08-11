<?php
$adminTitle = 'Dashboard Admin';
$adminActivePage = 'dashboard';
$adminSubtitle = 'Ringkasan data aplikasi Wali Kelas';
$unreadFeedback = $stats['unread_feedback'] ?? ($unreadFeedback ?? 0);
require_once APP_PATH . '/views/admin/layout_admin.php';
?>
<div class="admin-content">
    <div class="admin-stats-grid mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="admin-stat-card border-bottom border-4 border-primary shadow-sm rounded p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total User</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_users'] ?? 0) ?></h3>
                </div>
                <div class="text-primary bg-primary bg-opacity-10 p-3 rounded">
                    <i class="bi bi-people fs-4"></i>
                </div>
            </div>
        </div>
        <div class="admin-stat-card border-bottom border-4 border-success shadow-sm rounded p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Kunjungan Hari Ini</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_kunjungan_today'] ?? 0) ?></h3>
                </div>
                <div class="text-success bg-success bg-opacity-10 p-3 rounded">
                    <i class="bi bi-graph-up fs-4"></i>
                </div>
            </div>
        </div>
        <div class="admin-stat-card border-bottom border-4 border-info shadow-sm rounded p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Kunjungan</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_kunjungan_all'] ?? 0) ?></h3>
                </div>
                <div class="text-info bg-info bg-opacity-10 p-3 rounded">
                    <i class="bi bi-eye fs-4"></i>
                </div>
            </div>
        </div>
        <div class="admin-stat-card border-bottom border-4 border-warning shadow-sm rounded p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Feedback Belum Dibaca</h6>
                    <h3 class="mb-0"><?= number_format($stats['unread_feedback'] ?? 0) ?></h3>
                </div>
                <div class="text-warning bg-warning bg-opacity-10 p-3 rounded">
                    <i class="bi bi-chat-heart fs-4"></i>
                </div>
            </div>
        </div>
        <div class="admin-stat-card border-bottom border-4 border-danger shadow-sm rounded p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">User Baru Minggu Ini</h6>
                    <h3 class="mb-0"><?= number_format($stats['new_users_week'] ?? 0) ?></h3>
                </div>
                <div class="text-danger bg-danger bg-opacity-10 p-3 rounded">
                    <i class="bi bi-person-plus fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="admin-card card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">Kunjungan 7 Hari Terakhir</h5>
                </div>
                <div class="card-body">
                    <canvas id="kunjunganChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="admin-card card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Terbaru</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table admin-table mb-0 table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">User</th>
                                    <th>Kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentUsers)): ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($user['nama_lengkap']) ?>"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                                            <div class="small text-muted">@<?= htmlspecialchars($user['username']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($user['kelas']) ?></span>
                                            <div class="small text-muted mt-1"><?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'Lama' ?></div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Belum ada user</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?= json_encode($chartData ?? []) ?>;
    
    if(chartData && chartData.length > 0) {
        const labels = chartData.map(item => item.tanggal);
        const data = chartData.map(item => item.jumlah);
        
        const ctx = document.getElementById('kunjunganChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(33, 37, 41, 0.5)'); // dark gradient
        gradient.addColorStop(1, 'rgba(33, 37, 41, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: data,
                    backgroundColor: gradient,
                    borderColor: '#212529', // dark border
                    borderWidth: 2,
                    pointBackgroundColor: '#212529',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#212529',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once APP_PATH . '/views/admin/layout_admin_footer.php'; ?>
