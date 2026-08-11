<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/models/PertanyaanModel.php';

echo "<h2>Mulai Memasukkan Soal Default...</h2>";

try {
    $db = new Database();
    $pertanyaanModel = new PertanyaanModel();
    
    // Ambil semua user (Wali Kelas)
    $db->query("SELECT id, username FROM users");
    $users = $db->resultSet();

    if (empty($users)) {
        die("<p style='color:red;'>Belum ada data wali kelas di tabel users.</p>");
    }

    foreach ($users as $user) {
        $userId = $user['id'];
        $username = $user['username'];
        echo "<h3>Memproses Wali Kelas: {$username}</h3>";

        // Cek apakah user ini sudah punya pertanyaan
        $db->query("SELECT COUNT(*) as total FROM pertanyaan WHERE user_id = :user_id");
        $db->bind(':user_id', $userId);
        $cek = $db->single();

        if ($cek['total'] > 0) {
            echo "<p style='color:orange;'>Wali kelas ini sudah memiliki soal yang dikonfigurasi. Lewati penambahan soal default.</p>";
            continue;
        }

        // Insert soal menggunakan fungsi model
        if ($pertanyaanModel->createDefaultPertanyaan($userId)) {
            echo "<p>✔️ Berhasil menambahkan 12 soal default untuk <strong>{$username}</strong></p>";
        } else {
            echo "<p style='color:red;'>❌ Gagal menambahkan soal default untuk {$username}</p>";
        }
    }

    echo "<h2 style='color:green;'>Semua soal default berhasil ditambahkan!</h2>";
    echo "<p>Bapak bisa menghapus file ini kembali setelah selesai.</p>";
    echo "<a href='" . BASE_URL . "'>Kembali ke Halaman Utama</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan:</h2>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
}

