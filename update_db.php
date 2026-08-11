<?php
require_once __DIR__ . '/app/config/config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        $pdo->exec("ALTER TABLE siswa ADD COLUMN alamat TEXT DEFAULT NULL");
    } catch(Exception $e) {
        // Kolom mungkin sudah ada
    }
    
    try {
        $pdo->exec("ALTER TABLE siswa ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
    } catch(Exception $e) {
        // Kolom mungkin sudah ada
    }
    
    echo "<h3>Tabel siswa berhasil diperbarui. Silakan hapus file ini.</h3>";
} catch (PDOException $e) {
    echo "Error koneksi database: " . $e->getMessage();
}
