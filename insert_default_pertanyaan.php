<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

echo "<h2>Mulai Memasukkan Soal Default...</h2>";

try {
    $db = new Database();
    
    // Ambil semua user (Wali Kelas)
    $db->query("SELECT id, username FROM users");
    $users = $db->resultSet();

    if (empty($users)) {
        die("<p style='color:red;'>Belum ada data wali kelas di tabel users.</p>");
    }

    $defaultSoal = [
        [
            'judul' => 'Kehadiran Sekolah',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Kehadiran Al-Miftah',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Kehadiran Diniyah',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Kehadiran Ngaji Pagi',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Membaca Al-Qur\'an',
            'tipe' => 'angka',
            'opsi' => [
                'poin_per_angka' => 2,
                'require_ket' => false,
                'satuan' => 'Halaman'
            ]
        ],
        [
            'judul' => 'Shalat Dluha',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Ikut', 'value' => 'ikut', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Udzur Haid', 'value' => 'udzur_haid', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Tidak Ikut', 'value' => 'tidak_ikut', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Belajar Mandiri di Kamar',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Iya', 'value' => 'iya', 'poin' => 10, 'require_ket' => false],
                ['label' => 'Tidak', 'value' => 'tidak', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Membaca Buku',
            'tipe' => 'angka',
            'opsi' => [
                'poin_per_angka' => 1,
                'require_ket' => false,
                'satuan' => 'Halaman'
            ]
        ],
        [
            'judul' => 'Memaafkan Semua Teman',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Mendoakan Sesama Muslim',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Mendoakan Orang Tua',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
            ]
        ],
        [
            'judul' => 'Shadaqah / Membantu Teman',
            'tipe' => 'pilihan_ganda',
            'opsi' => [
                ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
            ]
        ]
    ];

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

        // Insert soal
        foreach ($defaultSoal as $index => $soal) {
            $db->query("INSERT INTO pertanyaan (user_id, judul, tipe, opsi, urutan, is_active) 
                        VALUES (:user_id, :judul, :tipe, :opsi, :urutan, 1)");
            $db->bind(':user_id', $userId);
            $db->bind(':judul', $soal['judul']);
            $db->bind(':tipe', $soal['tipe']);
            $db->bind(':opsi', json_encode($soal['opsi']));
            $db->bind(':urutan', $index + 1);
            $db->execute();
            echo "<p>✔️ Menambahkan soal: <strong>{$soal['judul']}</strong></p>";
        }
    }

    echo "<h2 style='color:green;'>Semua soal default berhasil ditambahkan!</h2>";
    echo "<p>Bapak bisa menghapus file ini kembali setelah selesai.</p>";
    echo "<a href='" . BASE_URL . "'>Kembali ke Halaman Utama</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan:</h2>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
}
