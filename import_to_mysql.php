<?php
/**
 * Script untuk memigrasi data dari flat-file JSON ke MySQL.
 * Upload script ini ke server (wali.quizb.my.id) dan akses melalui browser satu kali.
 */

define('DB_NAME', 'quic1934_wali');
define('DB_USER', 'quic1934_zenhkm');
define('DB_PASS', '03Maret1990');
define('DB_HOST', 'localhost');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Buat Database jika belum ada (walaupun biasanya sudah dibuat via cPanel)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $pdo->exec("USE `" . DB_NAME . "`");

    echo "<h3>Memulai Migrasi ke Database: " . DB_NAME . "</h3>";

    // 2. Buat tabel `siswa`
    $sqlSiswa = "CREATE TABLE IF NOT EXISTS `siswa` (
        `id` INT NOT NULL,
        `nama` VARCHAR(255) NOT NULL,
        `no_hp` VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlSiswa);
    echo "<p>✔ Tabel `siswa` berhasil dibuat/dicek.</p>";

    // 3. Buat tabel `absen`
    $sqlAbsen = "CREATE TABLE IF NOT EXISTS `absen` (
        `id` INT AUTO_INCREMENT,
        `id_siswa` INT NOT NULL,
        `tanggal` DATE NOT NULL,
        `waktu_isi` DATETIME DEFAULT NULL,
        `sekolah_status` VARCHAR(50) DEFAULT NULL,
        `sekolah_ket` VARCHAR(255) DEFAULT NULL,
        `almiftah_status` VARCHAR(50) DEFAULT NULL,
        `almiftah_ket` VARCHAR(255) DEFAULT NULL,
        `diniyah_status` VARCHAR(50) DEFAULT NULL,
        `diniyah_ket` VARCHAR(255) DEFAULT NULL,
        `subuh_status` VARCHAR(50) DEFAULT NULL,
        `subuh_ket` VARCHAR(255) DEFAULT NULL,
        `quran_type` VARCHAR(50) DEFAULT NULL,
        `quran_jumlah` INT DEFAULT 0,
        `baca_buku_status` VARCHAR(50) DEFAULT NULL,
        `baca_buku_jumlah` INT DEFAULT 0,
        `dluha_status` VARCHAR(50) DEFAULT NULL,
        `belajar_status` VARCHAR(50) DEFAULT NULL,
        `memaafkan_status` VARCHAR(50) DEFAULT NULL,
        `mendoakan_muslimin_status` VARCHAR(50) DEFAULT NULL,
        `mendoakan_ortu_status` VARCHAR(50) DEFAULT NULL,
        `shadaqah_status` VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_absen_siswa` (`id_siswa`, `tanggal`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlAbsen);
    echo "<p>✔ Tabel `absen` berhasil dibuat/dicek.</p>";

    // 4. Migrasi data_siswa dari data.json
    $dataFile = __DIR__ . '/data.json';
    if (file_exists($dataFile)) {
        $json = file_get_contents($dataFile);
        $data = json_decode($json, true);
        if (isset($data['data_siswa']) && is_array($data['data_siswa'])) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO `siswa` (`id`, `nama`, `no_hp`) VALUES (:id, :nama, :no_hp) ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `no_hp` = VALUES(`no_hp`)");
            $countSiswa = 0;
            foreach ($data['data_siswa'] as $s) {
                $stmt->execute([
                    ':id'    => $s['id'],
                    ':nama'  => $s['nama'],
                    ':no_hp' => $s['no_hp'] ?? ''
                ]);
                $countSiswa++;
            }
            echo "<p>✔ Berhasil memigrasi $countSiswa data siswa.</p>";
        }
    } else {
        echo "<p>⚠ File data.json tidak ditemukan.</p>";
    }

    // 5. Migrasi data absen harian
    $absenDir = __DIR__ . '/storage/absen';
    if (is_dir($absenDir)) {
        $files = scandir($absenDir);
        $countAbsenTotal = 0;
        
        $stmtAbsen = $pdo->prepare("INSERT IGNORE INTO `absen` 
            (`id_siswa`, `tanggal`, `waktu_isi`, `sekolah_status`, `sekolah_ket`, `almiftah_status`, `almiftah_ket`, `diniyah_status`, `diniyah_ket`, `subuh_status`, `subuh_ket`, `quran_type`, `quran_jumlah`, `baca_buku_status`, `baca_buku_jumlah`, `dluha_status`, `belajar_status`, `memaafkan_status`, `mendoakan_muslimin_status`, `mendoakan_ortu_status`, `shadaqah_status`) 
            VALUES 
            (:id_siswa, :tanggal, :waktu_isi, :sekolah_status, :sekolah_ket, :almiftah_status, :almiftah_ket, :diniyah_status, :diniyah_ket, :subuh_status, :subuh_ket, :quran_type, :quran_jumlah, :baca_buku_status, :baca_buku_jumlah, :dluha_status, :belajar_status, :memaafkan_status, :mendoakan_muslimin_status, :mendoakan_ortu_status, :shadaqah_status)
            ON DUPLICATE KEY UPDATE 
            `waktu_isi` = VALUES(`waktu_isi`), `sekolah_status` = VALUES(`sekolah_status`), `sekolah_ket` = VALUES(`sekolah_ket`), `almiftah_status` = VALUES(`almiftah_status`), `almiftah_ket` = VALUES(`almiftah_ket`), `diniyah_status` = VALUES(`diniyah_status`), `diniyah_ket` = VALUES(`diniyah_ket`), `subuh_status` = VALUES(`subuh_status`), `subuh_ket` = VALUES(`subuh_ket`), `quran_type` = VALUES(`quran_type`), `quran_jumlah` = VALUES(`quran_jumlah`), `baca_buku_status` = VALUES(`baca_buku_status`), `baca_buku_jumlah` = VALUES(`baca_buku_jumlah`), `dluha_status` = VALUES(`dluha_status`), `belajar_status` = VALUES(`belajar_status`), `memaafkan_status` = VALUES(`memaafkan_status`), `mendoakan_muslimin_status` = VALUES(`mendoakan_muslimin_status`), `mendoakan_ortu_status` = VALUES(`mendoakan_ortu_status`), `shadaqah_status` = VALUES(`shadaqah_status`)");

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !preg_match('/^(\d{4}-\d{2}-\d{2})\.json$/', $file, $matches)) {
                continue;
            }
            
            $tanggal = $matches[1];
            $jsonAbsen = file_get_contents($absenDir . '/' . $file);
            $dataAbsen = json_decode($jsonAbsen, true);
            
            if (is_array($dataAbsen)) {
                $countHariIni = 0;
                foreach ($dataAbsen as $id_siswa => $ab) {
                    $stmtAbsen->execute([
                        ':id_siswa'                  => $id_siswa,
                        ':tanggal'                   => $tanggal,
                        ':waktu_isi'                 => $ab['waktu_isi'] ?? null,
                        ':sekolah_status'            => $ab['sekolah']['status'] ?? null,
                        ':sekolah_ket'               => $ab['sekolah']['ket'] ?? null,
                        ':almiftah_status'           => $ab['almiftah']['status'] ?? null,
                        ':almiftah_ket'              => $ab['almiftah']['ket'] ?? null,
                        ':diniyah_status'            => $ab['diniyah']['status'] ?? null,
                        ':diniyah_ket'               => $ab['diniyah']['ket'] ?? null,
                        ':subuh_status'              => $ab['subuh']['status'] ?? null,
                        ':subuh_ket'                 => $ab['subuh']['ket'] ?? null,
                        ':quran_type'                => $ab['quran']['type'] ?? null,
                        ':quran_jumlah'              => $ab['quran']['jumlah'] ?? 0,
                        ':baca_buku_status'          => $ab['baca_buku']['status'] ?? null,
                        ':baca_buku_jumlah'          => $ab['baca_buku']['jumlah'] ?? 0,
                        ':dluha_status'              => $ab['dluha']['status'] ?? null,
                        ':belajar_status'            => $ab['belajar']['status'] ?? null,
                        ':memaafkan_status'          => $ab['memaafkan']['status'] ?? null,
                        ':mendoakan_muslimin_status' => $ab['mendoakan_muslimin']['status'] ?? null,
                        ':mendoakan_ortu_status'     => $ab['mendoakan_ortu']['status'] ?? null,
                        ':shadaqah_status'           => $ab['shadaqah']['status'] ?? null
                    ]);
                    $countHariIni++;
                    $countAbsenTotal++;
                }
                echo "<p>✔ Memigrasi $countHariIni data absen untuk tanggal $tanggal.</p>";
            }
        }
        echo "<p><strong>Selesai! Total data absen berhasil dimigrasi: $countAbsenTotal.</strong></p>";
    } else {
        echo "<p>⚠ Direktori storage/absen tidak ditemukan.</p>";
    }
    
    echo "<h3>Migrasi Berhasil. Harap hapus file ini demi keamanan.</h3>";

} catch (PDOException $e) {
    echo "<h3>Gagal Migrasi</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
