<?php
/**
 * migrate_google.php
 * Tambah kolom google_id dan google_email ke tabel users
 * Jalankan SEKALI: https://wali.quizb.my.id/migrate_google.php
 * HAPUS file ini setelah berhasil dijalankan.
 */

define('ROOT_PATH', __DIR__);
define('APP_PATH',  __DIR__ . '/app');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host);

require_once APP_PATH . '/config/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('<p style="color:red">Koneksi gagal: ' . $e->getMessage() . '</p>');
}

$results = [];

// 1. Cek apakah kolom google_id sudah ada
$cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_id'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER kelas");
    $results[] = '✅ Kolom <code>google_id</code> berhasil ditambahkan.';
} else {
    $results[] = '⚠️ Kolom <code>google_id</code> sudah ada, dilewati.';
}

// 2. Cek apakah kolom google_email sudah ada
$cols2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_email'")->fetchAll();
if (empty($cols2)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN google_email VARCHAR(255) NULL AFTER google_id");
    $results[] = '✅ Kolom <code>google_email</code> berhasil ditambahkan.';
} else {
    $results[] = '⚠️ Kolom <code>google_email</code> sudah ada, dilewati.';
}

// 3. Cek apakah kolom google_avatar sudah ada
$cols3 = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_avatar'")->fetchAll();
if (empty($cols3)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN google_avatar VARCHAR(500) NULL AFTER google_email");
    $results[] = '✅ Kolom <code>google_avatar</code> berhasil ditambahkan.';
} else {
    $results[] = '⚠️ Kolom <code>google_avatar</code> sudah ada, dilewati.';
}

// 4. Buat index untuk google_id
try {
    $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX idx_google_id (google_id)");
    $results[] = '✅ Index <code>google_id</code> berhasil dibuat.';
} catch (PDOException $e) {
    $results[] = '⚠️ Index <code>google_id</code> sudah ada atau dilewati: ' . $e->getMessage();
}

// 5. Ubah password agar bisa NULL (untuk akun Google-only)
$pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
$results[] = '✅ Kolom <code>password</code> diizinkan NULL (untuk akun Google).';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Migrasi Google OAuth</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; }
        h2 { color: #1a73e8; }
        li { margin: 8px 0; padding: 10px; background: #f8f9fa; border-radius: 6px; }
        .warning { color: #d93025; font-weight: bold; margin-top: 20px; padding: 15px; background: #fce8e6; border-radius: 8px; }
    </style>
</head>
<body>
    <h2>🔧 Migrasi Database: Google OAuth</h2>
    <ul>
        <?php foreach ($results as $r): ?>
            <li><?= $r ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="warning">
        ⚠️ <strong>PENTING:</strong> Hapus file <code>migrate_google.php</code> dari server setelah migrasi selesai!
    </p>
</body>
</html>
