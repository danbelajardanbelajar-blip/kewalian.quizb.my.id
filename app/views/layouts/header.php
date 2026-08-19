<?php
// Pastikan variabel $title ada
$pageTitle = $title ?? 'Dashboard Wali Kelas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi Dashboard Wali Kelas — Manajemen Kehadiran dan Kegiatan Harian Santri/Siswa">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon"        href="<?= BASE_URL ?>/public/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/public/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/public/img/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180"    href="<?= BASE_URL ?>/public/img/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>/public/site.webmanifest">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNavbar">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
            <div class="brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="brand-title">Wali Kelas</div>
                <div class="brand-sub">
                    <?php
                    require_once APP_PATH . '/models/KonfigurasiModel.php';
                    $__konfig = new KonfigurasiModel();
                    echo 'Kelas ' . htmlspecialchars($__konfig->getKelas());
                    ?>
                </div>
            </div>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if (Session::get('logged_in')): ?>
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false || $_SERVER['REQUEST_URI'] == BASE_PATH . '/') ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>">
                        <i class="bi bi-house-door me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/absen/rekap') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/absen/rekap">
                        <i class="bi bi-person-check me-1"></i> Absen Mandiri
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/absen/sorotan') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/absen/sorotan">
                        <i class="bi bi-stars me-1"></i> Sorotan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/laporan') !== false && strpos($_SERVER['REQUEST_URI'], '/rekap') === false) ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/laporan">
                        <i class="bi bi-journal-text me-1"></i> Riwayat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/laporan/rekap') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/laporan/rekap">
                        <i class="bi bi-bar-chart-line me-1"></i> Rekap
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/siswa') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/siswa">
                        <i class="bi bi-people me-1"></i> Data Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/pertanyaan') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/pertanyaan">
                        <i class="bi bi-ui-checks me-1"></i> Pertanyaan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/watemplate') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/watemplate">
                        <i class="bi bi-whatsapp me-1"></i> Template WA
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/feedback') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/feedback">
                        <i class="bi bi-chat-heart me-1"></i> Kirim Feedback
                    </a>
                </li>
                <?php if (Session::get('is_admin')): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold" href="<?= BASE_URL ?>/admin">
                        <i class="bi bi-shield-lock-fill me-1"></i> Admin Panel
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- User Info + Logout -->
            <div class="d-flex align-items-center gap-3">
                <?php if (Session::get('impersonator_id')): ?>
                    <form action="<?= BASE_URL ?>/auth/kembaliAdmin" method="POST" class="m-0">
                        <button type="submit" class="btn btn-sm btn-warning fw-bold d-flex align-items-center gap-1 shadow-sm" style="animation: pulse 2s infinite;">
                            <i class="bi bi-shield-fill-exclamation"></i> Kembali ke Admin
                        </button>
                    </form>
                    <style>
                        @keyframes pulse {
                            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
                            70% { box-shadow: 0 0 0 6px rgba(255, 193, 7, 0); }
                            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
                        }
                    </style>
                <?php endif; ?>
                <div class="d-flex align-items-center gap-2 text-white-50">
                    <div class="user-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="small"><?= htmlspecialchars(Session::get('username', 'Admin')) ?></span>
                </div>
                <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-sm btn-logout">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<div class="container-fluid px-4 mt-3">
    <?= Flash::render() ?>
</div>

<!-- Main Content -->
<main class="container-fluid px-4 py-3">
