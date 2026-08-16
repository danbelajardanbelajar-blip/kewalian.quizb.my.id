<?php
define("ROOT_PATH", __DIR__);
define("APP_PATH", ROOT_PATH . "/app");
require_once "app/config/config.php";
require_once "app/core/Database.php";

$db = new Database();
$messages = [];

try {
    $db->query("ALTER TABLE kunjungan ADD COLUMN id_siswa INT NULL");
    $db->execute();
    $messages[] = "Kolom id_siswa berhasil ditambahkan.";
} catch (Exception $e) {
    $messages[] = "Kolom id_siswa gagal ditambahkan (mungkin sudah ada): " . $e->getMessage();
}

try {
    $db->query("ALTER TABLE kunjungan ADD COLUMN no_hp_walimurid VARCHAR(50) NULL");
    $db->execute();
    $messages[] = "Kolom no_hp_walimurid berhasil ditambahkan.";
} catch (Exception $e) {
    $messages[] = "Kolom no_hp_walimurid gagal ditambahkan (mungkin sudah ada): " . $e->getMessage();
}

echo implode("<br>", $messages);
?>
