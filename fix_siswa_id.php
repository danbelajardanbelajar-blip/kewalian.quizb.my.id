<?php
/**
 * fix_siswa_id.php
 * Mengubah kolom id pada tabel siswa agar memiliki atribut AUTO_INCREMENT.
 * Jalankan file ini SEKALI dari browser lalu hapus.
 */
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

echo "<h2>Fix Tabel Siswa</h2>";

try {
    $db = new Database();

    // Pastikan tabel siswa sudah ada
    $db->query("SHOW TABLES LIKE 'siswa'");
    if ($db->single()) {
        // Cek struktur kolom id
        $db->query("SHOW COLUMNS FROM siswa LIKE 'id'");
        $col = $db->single();
        
        if ($col) {
            if (strpos(strtolower($col['Extra']), 'auto_increment') === false) {
                // Hapus data dengan id = 0 jika ada karena akan bermasalah saat alter table
                // $db->query("DELETE FROM siswa WHERE id = 0");
                // $db->execute();
                
                // Coba untuk mengubah tabel
                // MySQL tidak mengizinkan mengubah ke AUTO_INCREMENT jika masih ada constraint/masalah di datanya
                // Cara paling aman jika tabel ini adalah foreign key di tabel lain (seperti absen_header):
                // Jika error, kita harus handle errornya.
                
                $db->query("ALTER TABLE siswa MODIFY COLUMN id INT AUTO_INCREMENT");
                try {
                    $db->execute();
                    echo "<p style='color:green;'>✅ Kolom <code>id</code> berhasil diubah menjadi AUTO_INCREMENT.</p>";
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>❌ Gagal mengubah ke AUTO_INCREMENT: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<p>Coba jalankan query ini secara manual di phpMyAdmin:</p>";
                    echo "<code>ALTER TABLE siswa MODIFY COLUMN id INT AUTO_INCREMENT;</code>";
                }
            } else {
                echo "<p style='color:gray;'>ℹ️ Kolom <code>id</code> sudah memiliki atribut AUTO_INCREMENT.</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Kolom <code>id</code> tidak ditemukan di tabel siswa.</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Tabel <code>siswa</code> tidak ditemukan.</p>";
    }

    echo "<h2 style='color:green;'>Selesai!</h2>";
    echo "<p style='color:red;'><strong>⚠️ Segera hapus file ini dari server setelah selesai!</strong></p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>
