<?php
// Tentukan path file JSON
$json_file = 'data.json';

// Inisialisasi variabel default jika file tidak ditemukan
$kelas = "Tidak Diketahui";
$kategori = [];
$data_siswa = [];

// Cek apakah file JSON ada, lalu muat datanya
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    // Decode JSON menjadi associative array
    $config = json_decode($json_data, true); 
    
    if ($config !== null) {
        $kelas = $config['kelas'] ?? $kelas;
        $kategori = $config['kategori'] ?? [];
        $data_siswa = $config['data_siswa'] ?? [];
    }
} else {
    // Error handling sederhana jika file hilang
    die("Error: File konfigurasi data.json tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wali Kelas - Kelas <?= htmlspecialchars($kelas) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .table-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table thead th { text-align: center; vertical-align: middle; font-size: 0.85rem; background-color: #0d6efd; color: white; border-bottom: none; }
        .table tbody td { vertical-align: middle; font-size: 0.9rem; }
        .form-check-input { width: 1.2em; height: 1.2em; cursor: pointer; }
        .col-nama { min-width: 250px; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-primary">Dashboard Wali Kelas</h2>
            <p class="text-muted">Manajemen Kehadiran dan Kegiatan Harian Santri/Siswa - Kelas <strong><?= htmlspecialchars($kelas) ?></strong></p>
        </div>
    </div>

    <div class="table-container">
        <form action="proses_simpan.php" method="POST">
            
            <div class="row mb-3 align-items-center">
                <div class="col-md-3">
                    <label for="tanggal" class="form-label fw-bold">Tanggal Input</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-9 text-md-end mt-3 mt-md-0">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save"></i> Simpan Laporan Harian
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th rowspan="2" width="5%">No</th>
                            <th rowspan="2" class="col-nama">Nama Siswa</th>
                            <th colspan="4">Kehadiran (Check = Hadir)</th>
                            <th colspan="3">Amaliyah & Kegiatan (Check = Selesai)</th>
                        </tr>
                        <tr>
                            <?php foreach ($kategori as $key => $label) : ?>
                                <th><?= htmlspecialchars($label) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (!empty($data_siswa)) {
                            foreach ($data_siswa as $index => $nama) : 
                                $input_name = "data[" . $index . "]";
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="fw-medium">
                                <?= htmlspecialchars($nama); ?>
                                <input type="hidden" name="<?= $input_name ?>[nama]" value="<?= htmlspecialchars($nama) ?>">
                            </td>
                            
                            <?php foreach ($kategori as $key => $label) : ?>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-0">
                                    <input class="form-check-input shadow-sm" type="checkbox" 
                                           name="<?= $input_name ?>[<?= htmlspecialchars($key) ?>]" 
                                           value="1" checked>
                                </div>
                            </td>
                            <?php endforeach; ?>
                            
                        </tr>
                        <?php 
                            endforeach; 
                        } else {
                            echo '<tr><td colspan="9" class="text-center text-danger">Data siswa tidak ditemukan di konfigurasi JSON.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>