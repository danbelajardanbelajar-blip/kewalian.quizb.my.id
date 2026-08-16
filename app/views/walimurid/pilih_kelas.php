<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
    <style>
        .wali-card {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: var(--bs-body-color);
            transition: all 0.2s ease;
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .wali-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            color: var(--bs-primary);
        }
        .wali-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .wali-info {
            flex-grow: 1;
        }
        .wali-kelas {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .wali-nama {
            font-size: 0.9rem;
            color: var(--bs-secondary);
        }
        .wali-arrow {
            color: #adb5bd;
        }
        
        .absen-hero {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 3rem 1.5rem 4rem;
            text-align: center;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            margin-bottom: -2rem;
        }
        
        .absen-hero-title {
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .absen-hero-sub {
            opacity: 0.9;
            font-weight: 400;
            font-size: 1rem;
        }
        
        .absen-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .absen-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 1.5rem 2rem;
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-light">

<!-- Hero Header -->
<div class="absen-hero">
    <div class="absen-hero-content">
        <div class="absen-logo">
            <i class="bi bi-file-earmark-bar-graph"></i>
        </div>
        <h1 class="absen-hero-title">Laporan Wali Murid</h1>
        <p class="absen-hero-sub">
            Silakan pilih kelas putra/putri Anda
        </p>
    </div>
</div>

<div class="absen-container">

    <!-- Flash messages -->
    <?= Flash::render() ?>

    <div class="mt-4">
        <?php if (empty($listWali)): ?>
            <div class="alert alert-warning text-center shadow-sm">
                Belum ada kelas atau wali kelas yang terdaftar.
            </div>
        <?php else: ?>
            <?php foreach ($listWali as $w): ?>
                <a href="<?= BASE_URL ?>/walimurid?wali=<?= $w['id'] ?>" class="wali-card">
                    <div class="wali-icon">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <div class="wali-info">
                        <div class="wali-kelas">Kelas <?= htmlspecialchars($w['kelas'] ?: 'Belum diatur') ?></div>
                        <div class="wali-nama">Wali: <?= htmlspecialchars($w['nama_lengkap'] ?: $w['username']) ?></div>
                    </div>
                    <div class="wali-arrow">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
