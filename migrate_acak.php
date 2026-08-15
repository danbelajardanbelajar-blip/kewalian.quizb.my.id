<?php
define("ROOT_PATH", __DIR__);
define("APP_PATH", __DIR__."/app");
require_once APP_PATH . "/config/config.php";
require_once APP_PATH . "/core/Database.php";
$db = new Database();
echo "<pre>";
try {
    $db->query("ALTER TABLE users ADD COLUMN acak_pertanyaan TINYINT(1) DEFAULT 0");
    $db->execute();
    echo "Kolom acak_pertanyaan berhasil ditambahkan.\n";
} catch(Exception $e) {
    echo "Kolom acak_pertanyaan sudah ada atau gagal: " . $e->getMessage() . "\n";
}
try {
    $db->query("ALTER TABLE users ADD COLUMN acak_jawaban TINYINT(1) DEFAULT 0");
    $db->execute();
    echo "Kolom acak_jawaban berhasil ditambahkan.\n";
} catch(Exception $e) {
    echo "Kolom acak_jawaban sudah ada atau gagal: " . $e->getMessage() . "\n";
}
echo "Selesai. Silakan hapus file migrate_acak.php ini.</pre>";
