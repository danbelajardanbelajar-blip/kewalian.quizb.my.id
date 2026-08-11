<?php
/**
 * layout_admin.php — Admin Panel Layout Helper
 * Include this at the top of every admin view
 * Usage: include this file after setting $adminTitle and $adminActivePage
 *
 * Variables expected:
 *   $adminTitle      — Page title string
 *   $adminActivePage — One of: 'dashboard','users','soal','kunjungan','feedback'
 *   $unreadFeedback  — (optional) int count of unread feedback
 */

$_adminUser    = Session::get('username', 'Admin');
$_adminAvatar  = Session::get('google_avatar', '');
$_unreadCount  = $unreadFeedback ?? 0;
$_adminActive  = $adminActivePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle ?? 'Admin Panel') ?> — Wali Kelas Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/public/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/public/img/favicon-32x32.png">

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Admin CSS -->
    <link href="<?= BASE_URL ?>/public/css/admin.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

<div class="admin-wrapper">

    <!-- ── Sidebar ── -->
    <aside class="admin-sidebar" id="adminSidebar">

        <!-- Brand -->
        <div class="admin-sidebar-brand">
            <div class="brand-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <div class="brand-text">
                <div class="brand-name">Wali Kelas</div>
                <div class="brand-badge">Admin Panel</div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="admin-nav">
            <div class="admin-nav-section">
                <div class="admin-nav-label">Utama</div>
                <a href="<?= BASE_URL ?>/admin" class="admin-nav-item <?= $_adminActive === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>

            <div class="admin-nav-section mt-2">
                <div class="admin-nav-label">Manajemen</div>
                <a href="<?= BASE_URL ?>/admin/users" class="admin-nav-item <?= $_adminActive === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Kelola User
                </a>
                <a href="<?= BASE_URL ?>/admin/soal" class="admin-nav-item <?= $_adminActive === 'soal' ? 'active' : '' ?>">
                    <i class="bi bi-ui-checks-grid"></i> Soal Default
                </a>
            </div>

            <div class="admin-nav-section mt-2">
                <div class="admin-nav-label">Monitoring</div>
                <a href="<?= BASE_URL ?>/admin/kunjungan" class="admin-nav-item <?= $_adminActive === 'kunjungan' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i> Kunjungan & Tracking
                </a>
                <a href="<?= BASE_URL ?>/admin/feedback" class="admin-nav-item <?= $_adminActive === 'feedback' ? 'active' : '' ?>">
                    <i class="bi bi-chat-heart-fill"></i> Feedback
                    <?php if ($_unreadCount > 0): ?>
                        <span class="admin-nav-badge"><?= $_unreadCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="admin-nav-section mt-2">
                <div class="admin-nav-label">Lainnya</div>
                <a href="<?= BASE_URL ?>" class="admin-nav-item">
                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Wali Kelas
                </a>
            </div>
        </nav>

        <!-- Footer -->
        <div class="admin-sidebar-footer">
            <div class="admin-user-info">
                <div class="admin-user-avatar">
                    <?php if (!empty($_adminAvatar)): ?>
                        <img src="<?= htmlspecialchars($_adminAvatar) ?>" alt="avatar" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <i class="bi bi-person-fill"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="admin-user-name"><?= htmlspecialchars($_adminUser) ?></div>
                    <div class="admin-user-role">Super Admin</div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout" class="admin-logout-btn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </aside>

    <!-- ── Main ── -->
    <div class="admin-main">

        <!-- Topbar -->
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="admin-sidebar-toggle" onclick="toggleSidebar()" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <div class="admin-page-title"><?= htmlspecialchars($adminTitle ?? 'Dashboard') ?></div>
                    <div class="admin-page-subtitle"><?= htmlspecialchars($adminSubtitle ?? 'Panel Admin Wali Kelas') ?></div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger rounded-pill" style="font-size:11px">
                    <?php if ($_unreadCount > 0): ?>
                        <?= $_unreadCount ?> feedback baru
                    <?php endif; ?>
                </span>
                <a href="<?= BASE_URL ?>/admin/feedback" class="btn-admin-edit">
                    <i class="bi bi-bell"></i>
                </a>
            </div>
        </div>

        <!-- Flash -->
        <?php
        $flash = Flash::render();
        if ($flash): ?>
        <div class="px-4 pt-3">
            <?= $flash ?>
        </div>
        <?php endif; ?>

        <!-- Content slot — Views render here -->
        <div class="admin-content">
