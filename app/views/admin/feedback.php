<?php
$adminTitle = 'Feedback';
$adminActivePage = 'feedback';
$adminSubtitle = 'Saran dan masukan pengguna';
$unreadFeedback = $unreadCount ?? ($unreadFeedback ?? 0);
require_once APP_PATH . '/views/admin/layout_admin.php';

$filter = $_GET['filter'] ?? 'all';
?>
<div class="admin-content">
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="admin-stat-card card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Rata-rata Rating</h6>
                            <h3 class="mb-0 d-flex align-items-center">
                                <?= number_format($avgRating ?? 0, 1) ?> 
                                <span class="ms-2 fs-5 text-warning">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="admin-stat-card card shadow-sm border-0 border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Belum Dibaca</h6>
                            <h3 class="mb-0 text-danger fw-bold"><?= number_format($unreadCount ?? 0) ?></h3>
                        </div>
                        <i class="bi bi-envelope-exclamation fs-1 text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Feedback</h6>
                            <h3 class="mb-0 fw-bold"><?= count($feedbacks ?? []) ?></h3>
                        </div>
                        <i class="bi bi-chat-square-text fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="mb-0">Daftar Feedback</h5>
            
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <div class="btn-group shadow-sm" role="group">
                    <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">Semua</a>
                    <a href="?filter=unread" class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' ?>">Belum Dibaca</a>
                    <a href="?filter=read" class="btn btn-sm <?= $filter === 'read' ? 'btn-primary' : 'btn-outline-primary' ?>">Sudah Dibaca</a>
                </div>
                
                <?php if($unreadCount > 0): ?>
                <form action="<?= BASEURL ?>/admin/markAllFeedbackRead" method="POST" class="d-inline ms-2">
                    <button type="submit" class="btn btn-sm btn-outline-success shadow-sm">
                        <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Pengirim</th>
                            <th>Email</th>
                            <th style="width: 35%;">Pesan</th>
                            <th>Rating</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($feedbacks)): ?>
                            <?php foreach($feedbacks as $fb): ?>
                            <tr class="<?= $fb['is_read'] == 0 ? 'table-warning' : '' ?>">
                                <td class="ps-4 fw-bold">
                                    <?= htmlspecialchars($fb['nama']) ?>
                                </td>
                                <td><?= htmlspecialchars($fb['email']) ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($fb['pesan']) ?>">
                                        <?= mb_strlen($fb['pesan']) > 100 ? mb_substr(htmlspecialchars($fb['pesan']), 0, 100) . '...' : htmlspecialchars($fb['pesan']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        <?php 
                                        $rating = (int)($fb['rating'] ?? 0);
                                        for($i=1; $i<=5; $i++) {
                                            echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><?= date('d M Y H:i', strtotime($fb['created_at'])) ?></div>
                                </td>
                                <td>
                                    <?php if($fb['is_read'] == 1): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Dibaca</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-envelope"></i> Baru</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <?php if($fb['is_read'] == 0): ?>
                                        <form action="<?= BASEURL ?>/admin/markFeedbackRead" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Tandai Dibaca">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form action="<?= BASEURL ?>/admin/deleteFeedback" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus feedback ini?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-envelope-paper fs-1 d-block mb-3 opacity-50"></i>
                                    Belum ada feedback.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once APP_PATH . '/views/admin/layout_admin_footer.php'; ?>
