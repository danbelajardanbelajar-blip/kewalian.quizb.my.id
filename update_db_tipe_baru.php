<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

echo "<h2>Mulai Proses Update Struktur Database...</h2>";

try {
    $db = new Database();
    
    // Update ENUM kolom tipe di tabel pertanyaan
    echo "<p>Mengubah struktur tipe pertanyaan...</p>";
    $db->query("ALTER TABLE pertanyaan MODIFY COLUMN tipe ENUM('pilihan_ganda', 'angka', 'ganda_dan_angka') NOT NULL");
    $db->execute();
    echo "<p style='color:green;'>Berhasil menambahkan tipe 'ganda_dan_angka'!</p>";

    echo "<h2 style='color:green;'>Update berhasil!</h2>";
    echo "<p>Bapak bisa menghapus file ini kembali setelah selesai.</p>";
    echo "<a href='" . BASE_URL . "'>Kembali ke Halaman Utama</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan:</h2>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
}
