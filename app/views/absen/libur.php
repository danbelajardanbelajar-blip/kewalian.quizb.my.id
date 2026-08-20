<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hari Libur - Kewalian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .libur-card {
            max-width: 500px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            padding: 3rem 2rem;
            background: white;
        }
        .icon-circle {
            width: 100px;
            height: 100px;
            background-color: #e8f5e9;
            color: #198754;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card libur-card mx-auto">
            <div class="icon-circle">
                <i class="bi bi-cup-hot-fill"></i>
            </div>
            <h2 class="fw-bold mb-3">Alhamdulillah, Hari Libur!</h2>
            <p class="text-muted mb-4">
                Hari Jumat adalah hari libur kegiatan. Tidak ada laporan absensi yang perlu diisi hari ini.
                <br><br>
                Selamat beristirahat dan manfaatkan waktu untuk kebaikan, <strong><?= htmlspecialchars($namaSiswa ?? 'Ananda') ?></strong>!
            </p>
        </div>
    </div>
</body>
</html>
