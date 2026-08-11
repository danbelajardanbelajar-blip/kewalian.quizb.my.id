<?php
/**
 * update_db_multitenant.php
 * Skrip untuk mengubah struktur database menjadi multi-wali kelas.
 */
require_once __DIR__ . '/app/config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Memulai update struktur database untuk Multi-Tenant...</h3>";

    // 1. Tambah kolom di tabel users
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN nama_lengkap VARCHAR(255) DEFAULT NULL");
        echo "<p>✔ Kolom 'nama_lengkap' ditambahkan ke tabel users.</p>";
    } catch(Exception $e) { }

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN kelas VARCHAR(100) DEFAULT NULL");
        echo "<p>✔ Kolom 'kelas' ditambahkan ke tabel users.</p>";
    } catch(Exception $e) { }

    // 2. Tambah kolom user_id di tabel siswa
    try {
        $pdo->exec("ALTER TABLE siswa ADD COLUMN user_id INT DEFAULT NULL");
        echo "<p>✔ Kolom 'user_id' ditambahkan ke tabel siswa.</p>";
    } catch(Exception $e) { }

    // 3. Pindahkan data kelas dari tabel pengaturan ke user pertama (admin lama)
    $stmt = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    $firstUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($firstUser) {
        $userId = $firstUser['id'];
        
        // Ambil kelas lama
        $stmtConfig = $pdo->query("SELECT key_value FROM pengaturan WHERE key_name = 'kelas'");
        $kelasLama = $stmtConfig->fetch(PDO::FETCH_ASSOC);
        if ($kelasLama) {
            $pdo->exec("UPDATE users SET kelas = '" . addslashes($kelasLama['key_value']) . "' WHERE id = $userId");
            echo "<p>✔ Data kelas lama dipindahkan ke user admin.</p>";
        }

        // Set semua siswa lama menjadi milik user pertama
        $pdo->exec("UPDATE siswa SET user_id = $userId WHERE user_id IS NULL");
        echo "<p>✔ Siswa lama telah ditetapkan ke user admin.</p>";
        
        // Hapus pengaturan kelas dari tabel pengaturan agar tidak bingung
        $pdo->exec("DELETE FROM pengaturan WHERE key_name = 'kelas'");
    }

    echo "<h3>Selesai! Database siap untuk sistem Multi-Wali. Silakan hapus file ini.</h3>";
} catch (PDOException $e) {
    echo "Error koneksi database: " . $e->getMessage();
}
