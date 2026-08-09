<?php
/**
 * Script Migrasi Data Absen & Laporan dari Key NAMA ke Key ID
 * Jalankan dari terminal: php migrate_ids.php
 */

define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
require_once APP_PATH . '/core/Database.php';

$db = new Database(__DIR__ . '/storage');
$konfigData = $db->readRoot('data.json');
$siswa = $konfigData['data_siswa'] ?? [];

// Buat map nama -> id
$namaToId = [];
foreach ($siswa as $s) {
    if (isset($s['id']) && isset($s['nama'])) {
        $namaToId[$s['nama']] = $s['id'];
    }
}

echo "=== Memulai Migrasi ===\n";
echo "Ditemukan " . count($namaToId) . " siswa dalam data.json\n\n";

// 1. Migrasi Data Absen
$absenFiles = $db->listFiles('absen');
echo "Memeriksa " . count($absenFiles) . " file absen...\n";

foreach ($absenFiles as $file) {
    $data = $db->read('absen/' . $file);
    $changed = false;
    $newSiswa = [];

    foreach ($data['siswa'] ?? [] as $key => $sData) {
        // Jika key bukan angka, berarti masih menggunakan nama
        if (!is_numeric($key)) {
            $nama = $sData['nama'] ?? $key;
            if (isset($namaToId[$nama])) {
                $newId = $namaToId[$nama];
                $sData['id'] = $newId; // Tambahkan ID ke dalam data
                $newSiswa[$newId] = $sData;
                $changed = true;
                echo "  [Absen: $file] Mengonversi '$nama' -> ID $newId\n";
            } else {
                echo "  [Absen: $file] WARNING: Siswa '$nama' tidak ditemukan di data utama! Tetap disimpan dengan nama.\n";
                $newSiswa[$key] = $sData;
            }
        } else {
            // Sudah menggunakan ID
            $newSiswa[$key] = $sData;
        }
    }

    if ($changed) {
        $data['siswa'] = $newSiswa;
        $db->write('absen/' . $file, $data);
        echo "  -> File absen/$file berhasil diperbarui.\n";
    }
}

// 2. Migrasi Data Laporan
$laporanFiles = $db->listFiles('laporan');
echo "\nMemeriksa " . count($laporanFiles) . " file laporan...\n";

foreach ($laporanFiles as $file) {
    $data = $db->read('laporan/' . $file);
    $changed = false;
    
    // Untuk laporan, data 'siswa' berupa array index 0,1,2,...
    // Kita pastikan di dalam object 'siswa' ada field 'id'
    if (isset($data['siswa'])) {
        foreach ($data['siswa'] as &$sData) {
            if (!isset($sData['id']) && isset($sData['nama'])) {
                $nama = $sData['nama'];
                if (isset($namaToId[$nama])) {
                    $sData['id'] = $namaToId[$nama];
                    $changed = true;
                    echo "  [Laporan: $file] Menambahkan ID {$namaToId[$nama]} untuk '$nama'\n";
                } else {
                    echo "  [Laporan: $file] WARNING: Siswa '$nama' tidak ditemukan di data utama!\n";
                }
            }
        }
    }

    if ($changed) {
        $db->write('laporan/' . $file, $data);
        echo "  -> File laporan/$file berhasil diperbarui.\n";
    }
}

echo "\n=== Migrasi Selesai ===\n";
