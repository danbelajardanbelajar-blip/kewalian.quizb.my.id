<?php
define('APP_PATH', __DIR__ . '/app');
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

echo "<h2>Mulai Sinkronisasi Judul Pertanyaan...</h2>";

try {
    $db = new Database();
    
    // Mapping judul lama yang singkat ke judul baru yang panjang (dari commit 6a27758)
    $mapping = [
        'Kehadiran Sekolah' => 'Apakah kemarin ananda {{nama}} hadir sekolah?',
        'Kehadiran Al-Miftah' => 'Apakah kemarin siang ananda {{nama}} hadir Al-Miftah?',
        'Kehadiran Diniyah' => 'Apakah tadi malam ananda {{nama}} hadir Diniyah?',
        'Kehadiran Ngaji Pagi' => 'Apakah kemarin pagi (bakda shubuh) ananda {{nama}} hadir Ngaji Pagi?',
        'Membaca Al-Qur\'an' => 'Apakah sejak kemarin hingga shubuh ini ananda {{nama}} membaca Al-Qur\'an secara mandiri (selain ngaji bersama di kelas atau di pondok)?',
        'Shalat Dluha' => 'Apakah kemarin pagi ananda {{nama}} ikut Shalat Dluha di madrasah?',
        'Belajar Mandiri di Kamar' => 'Apakah tadi malam ananda {{nama}} belajar di kamar?',
        'Membaca Buku' => 'Apakah kemarin ananda {{nama}} sudah membaca buku secara mandiri?',
        'Memaafkan Semua Teman' => 'Apakah tadi malam ananda {{nama}} sudah memaafkan semua orang?',
        'Mendoakan Sesama Muslim' => 'Apakah tadi malam ananda {{nama}} sudah mendoakan semua kaum muslimin?',
        'Mendoakan Orang Tua' => 'Apakah kemarin ananda {{nama}} sudah mendoakan kedua orang tua?',
        'Shadaqah / Membantu Teman' => 'Apakah kemarin ananda {{nama}} sudah membantu teman atau bersedekah?'
    ];

    $db->query("SELECT id, judul FROM pertanyaan");
    $pertanyaan_list = $db->resultSet();
    $berhasil = 0;

    foreach ($pertanyaan_list as $row) {
        $judul_sekarang = $row['judul'];
        // Jika judul cocok secara persis (case insensitive) atau mirip
        foreach ($mapping as $judul_lama => $judul_baru) {
            if (strtolower(trim($judul_sekarang)) === strtolower(trim($judul_lama))) {
                $db->query("UPDATE pertanyaan SET judul = :judul_baru WHERE id = :id");
                $db->bind(':judul_baru', $judul_baru);
                $db->bind(':id', $row['id']);
                $db->execute();
                
                echo "✅ Mengupdate (ID: {$row['id']}): '{$judul_sekarang}' &rarr; '{$judul_baru}'<br>";
                $berhasil++;
                break;
            }
        }
    }

    echo "<h2 style='color:green;'>Selesai! $berhasil pertanyaan berhasil diupdate.</h2>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan:</h2>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
}
