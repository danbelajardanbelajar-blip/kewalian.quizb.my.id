<?php
/**
 * fix_siswa_id.php - Versi 2 (Lebih Kuat)
 * Mengatasi error "Duplicate entry '0' for key 'PRIMARY'"
 */
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

echo "<h2>Fix Tabel Siswa (Versi Paksa)</h2>";

try {
    $db = new Database();

    // Matikan Foreign Key check agar alter table tidak terblokir
    $db->query("SET FOREIGN_KEY_CHECKS=0");
    $db->execute();

    // 1. Cek apakah ada data dengan id = 0
    $db->query("SELECT * FROM siswa WHERE id = 0");
    $hasZeroId = $db->single();

    if ($hasZeroId) {
        // Cari ID terbesar
        $db->query("SELECT MAX(id) as max_id FROM siswa");
        $maxId = (int)$db->single()['max_id'];
        $newId = $maxId > 0 ? $maxId + 1 : 1;

        // Update ID 0 menjadi ID baru di tabel siswa
        $db->query("UPDATE siswa SET id = :new_id WHERE id = 0");
        $db->bind(':new_id', $newId);
        $db->execute();

        // Update juga di tabel absen_header jika ada yang mengikat ke 0
        $db->query("UPDATE absen_header SET id_siswa = :new_id WHERE id_siswa = 0");
        $db->bind(':new_id', $newId);
        $db->execute();

        echo "<p style='color:orange;'>⚠️ Ditemukan siswa dengan ID 0. Berhasil dipindah ke ID {$newId} agar tidak bentrok.</p>";
    }

    // 2. Paksa alter tabel jadi AUTO_INCREMENT
    $db->query("ALTER TABLE siswa MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT");
    $db->execute();
    echo "<p style='color:green;'>✅ Kolom <code>id</code> berhasil dipaksa menjadi AUTO_INCREMENT!</p>";

    // Nyalakan kembali Foreign Key check
    $db->query("SET FOREIGN_KEY_CHECKS=1");
    $db->execute();

    echo "<h2 style='color:green;'>Selesai! Sistem siap digunakan.</h2>";
    echo "<p style='color:red;'><strong>⚠️ Hapus file ini setelah selesai.</strong></p>";
    echo "<a href='" . BASE_URL . "/siswa'>→ Pergi ke Halaman Siswa</a>";

} catch (PDOException $e) {
    // Pastikan FK check nyala lagi meski error
    $db = new Database();
    $db->query("SET FOREIGN_KEY_CHECKS=1");
    $db->execute();

    echo "<h2 style='color:red;'>❌ Error Database:</h2>";
    echo "<p><code>" . htmlspecialchars($e->getMessage()) . "</code></p>";
}
?>
