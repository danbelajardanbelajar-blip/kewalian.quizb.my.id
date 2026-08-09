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
    <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.svg" type="image/svg+xml">

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
            </ul>

            <!-- User Info + Logout -->
            <div class="d-flex align-items-center gap-3">
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
