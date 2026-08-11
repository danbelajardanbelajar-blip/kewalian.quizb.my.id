<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

echo "<h2>Mulai Proses Update Database...</h2>";

try {
    $db = new Database();
    
    // 1. Drop tabel absen yang lama
    echo "<p>Menghapus tabel <code>absen</code> lama (jika ada)...</p>";
    $db->query("DROP TABLE IF EXISTS absen");
    $db->execute();
    echo "<p style='color:green;'>Berhasil!</p>";

    // 2. Buat tabel pertanyaan
    echo "<p>Membuat tabel <code>pertanyaan</code>...</p>";
    $db->query("CREATE TABLE IF NOT EXISTS pertanyaan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        judul VARCHAR(255) NOT NULL,
        tipe ENUM('pilihan_ganda', 'angka') NOT NULL,
        opsi TEXT,
        urutan INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->execute();
    echo "<p style='color:green;'>Berhasil!</p>";

    // 3. Buat tabel absen_header
    echo "<p>Membuat tabel <code>absen_header</code>...</p>";
    $db->query("CREATE TABLE IF NOT EXISTS absen_header (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        tanggal DATE NOT NULL,
        waktu_isi DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_siswa) REFERENCES siswa(id) ON DELETE CASCADE,
        UNIQUE KEY (id_siswa, tanggal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->execute();
    echo "<p style='color:green;'>Berhasil!</p>";

    // 4. Buat tabel absen_detail
    echo "<p>Membuat tabel <code>absen_detail</code>...</p>";
    $db->query("CREATE TABLE IF NOT EXISTS absen_detail (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_absen INT NOT NULL,
        id_pertanyaan INT NOT NULL,
        jawaban VARCHAR(255) NOT NULL,
        keterangan VARCHAR(255) DEFAULT NULL,
        poin INT DEFAULT 0,
        FOREIGN KEY (id_absen) REFERENCES absen_header(id) ON DELETE CASCADE,
        FOREIGN KEY (id_pertanyaan) REFERENCES pertanyaan(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->execute();
    echo "<p style='color:green;'>Berhasil!</p>";

    echo "<h2 style='color:green;'>Semua tabel berhasil dibuat/diperbarui!</h2>";
    echo "<p>Bapak bisa menghapus file ini kembali setelah selesai.</p>";
    echo "<a href='" . BASE_URL . "'>Kembali ke Halaman Utama</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan:</h2>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
}
