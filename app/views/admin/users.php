<?php
$adminTitle = 'Kelola User';
$adminActivePage = 'users';
$adminSubtitle = 'Manajemen pengguna aplikasi';
$unreadFeedback = $unreadFeedback ?? 0;
require_once APP_PATH . '/views/admin/layout_admin.php';
?>
<div class="admin-content">
    <div class="admin-card card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Kelola User <span class="badge bg-primary ms-2"><?= count($users ?? []) ?></span></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Login via</th>
                            <th>Terdaftar</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($users)): ?>
                            <?php $i = 1; foreach($users as $user): ?>
                                <tr>
                                    <td class="ps-4"><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold">@<?= htmlspecialchars($user['username']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($user['kelas']) ?></td>
                                    <td>
                                        <?php if($user['is_admin'] == 1): ?>
                                            <span class="badge bg-danger rounded-pill badge-admin"><i class="bi bi-shield-lock"></i> Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($user['google_id'])): ?>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-google text-danger"></i> Google</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small"><?= date('d M Y', strtotime($user['created_at'])) ?></div>
                                        <div class="small text-muted"><?= date('H:i', strtotime($user['created_at'])) ?></div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= BASEURL ?>/admin/editUser/<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary btn-admin-edit" title="Edit User">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="<?= BASEURL ?>/admin/toggleAdmin" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm <?= $user['is_admin'] == 1 ? 'btn-warning' : 'btn-outline-warning' ?>" title="Toggle Admin Status" onclick="return confirm('Yakin ingin mengubah status admin user ini?');">
                                                    <i class="bi bi-shield-fill-exclamation"></i>
                                                </button>
                                            </form>
                                            <form action="<?= BASEURL ?>/admin/deleteUser" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-admin-danger" title="Hapus User" onclick="return confirm('Yakin ingin menghapus user ini secara permanen?');">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    Belum ada data user.
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
