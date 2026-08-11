<?php
/**
 * migrate_siswa.php
 * Tambahkan kolom no_hp, alamat, foto ke tabel siswa
 * Jalankan SEKALI lalu hapus file ini!
 */
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

echo "<h2>Migrasi Tabel Siswa</h2>";

try {
    $db = new Database();

    // Cek dan tambah kolom no_hp
    $db->query("SHOW COLUMNS FROM siswa LIKE 'no_hp'");
    if (!$db->single()) {
        $db->query("ALTER TABLE siswa ADD COLUMN no_hp VARCHAR(20) NULL AFTER nama");
        $db->execute();
        echo "<p style='color:green;'>✅ Kolom <code>no_hp</code> berhasil ditambahkan.</p>";
    } else {
        echo "<p style='color:gray;'>ℹ️ Kolom <code>no_hp</code> sudah ada, dilewati.</p>";
    }

    // Cek dan tambah kolom alamat
    $db->query("SHOW COLUMNS FROM siswa LIKE 'alamat'");
    if (!$db->single()) {
        $db->query("ALTER TABLE siswa ADD COLUMN alamat TEXT NULL AFTER no_hp");
        $db->execute();
        echo "<p style='color:green;'>✅ Kolom <code>alamat</code> berhasil ditambahkan.</p>";
    } else {
        echo "<p style='color:gray;'>ℹ️ Kolom <code>alamat</code> sudah ada, dilewati.</p>";
    }

    // Cek dan tambah kolom foto
    $db->query("SHOW COLUMNS FROM siswa LIKE 'foto'");
    if (!$db->single()) {
        $db->query("ALTER TABLE siswa ADD COLUMN foto VARCHAR(255) NULL AFTER alamat");
        $db->execute();
        echo "<p style='color:green;'>✅ Kolom <code>foto</code> berhasil ditambahkan.</p>";
    } else {
        echo "<p style='color:gray;'>ℹ️ Kolom <code>foto</code> sudah ada, dilewati.</p>";
    }

    echo "<h2 style='color:green;'>✅ Migrasi selesai! Tabel siswa siap digunakan.</h2>";
    echo "<p style='color:red;'><strong>⚠️ Segera hapus file ini dari server setelah selesai!</strong></p>";
    echo "<a href='" . BASE_URL . "/siswa'>→ Pergi ke Halaman Siswa</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>
