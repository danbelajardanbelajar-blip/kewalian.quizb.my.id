<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']));

require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

$db = new Database();
$messages = [];

try {
    // 1. ALTER TABLE users ADD COLUMN is_admin
    $db->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if (empty($db->resultSet())) {
        $db->query("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER kelas");
        $db->execute();
        $messages[] = "Added 'is_admin' column to users table.";
    } else {
        $messages[] = "Column 'is_admin' already exists in users table.";
    }

    // 1.5. ALTER TABLE users ADD COLUMN created_at
    $db->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if (empty($db->resultSet())) {
        $db->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        $db->execute();
        $messages[] = "Added 'created_at' column to users table.";
    } else {
        $messages[] = "Column 'created_at' already exists in users table.";
    }

    // 2. ALTER TABLE users ADD COLUMN last_login_at
    $db->query("SHOW COLUMNS FROM users LIKE 'last_login_at'");
    if (empty($db->resultSet())) {
        $db->query("ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL");
        $db->execute();
        $messages[] = "Added 'last_login_at' column to users table.";
    } else {
        $messages[] = "Column 'last_login_at' already exists in users table.";
    }

    // 3. CREATE TABLE kunjungan
    $db->query("CREATE TABLE IF NOT EXISTS kunjungan (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        ip VARCHAR(45), 
        user_agent TEXT, 
        halaman VARCHAR(500), 
        referer VARCHAR(500), 
        wali_id INT NULL, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $db->execute();
    $messages[] = "Created 'kunjungan' table if it did not exist.";

    // 4. CREATE TABLE feedback
    $db->query("CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id INT NULL, 
        nama VARCHAR(255), 
        email VARCHAR(255) NULL, 
        pesan TEXT NOT NULL, 
        rating TINYINT(1) NULL, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
        is_read TINYINT(1) DEFAULT 0
    )");
    $db->execute();
    $messages[] = "Created 'feedback' table if it did not exist.";

    // 5. CREATE TABLE pertanyaan_default
    $db->query("CREATE TABLE IF NOT EXISTS pertanyaan_default (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        judul TEXT NOT NULL, 
        tipe VARCHAR(50) DEFAULT 'pilihan_ganda', 
        opsi JSON, 
        urutan INT DEFAULT 0, 
        is_active TINYINT(1) DEFAULT 1, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $db->execute();
    $messages[] = "Created 'pertanyaan_default' table if it did not exist.";

    // 6. INSERT default questions
    if (isset($_GET['force']) && $_GET['force'] == '1') {
        $db->query("TRUNCATE TABLE pertanyaan_default");
        $db->execute();
        $messages[] = "Truncated 'pertanyaan_default' table via force flag.";
    }

    $db->query("SELECT COUNT(*) as count FROM pertanyaan_default");
    $result = $db->single();
    if ($result['count'] == 0) {
        $default_pertanyaan = [
            [
                'judul' => 'Apakah kemarin ananda {{nama}} hadir sekolah?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah kemarin siang ananda {{nama}} hadir Al-Miftah?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah tadi malam ananda {{nama}} hadir Diniyah?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah kemarin pagi (bakda shubuh) ananda {{nama}} hadir Ngaji Pagi?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah sejak kemarin hingga shubuh ini ananda {{nama}} membaca Al-Qur\'an secara mandiri (selain ngaji bersama di kelas atau di pondok)?',
                'tipe' => 'ganda_dan_angka',
                'opsi' => [
                    'pilihan' => [
                        ['label' => 'Iya', 'value' => 'iya', 'poin' => 0, 'require_ket' => false, 'require_angka' => true],
                        ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false, 'require_angka' => false]
                    ],
                    'angka' => [
                        'poin_per_angka' => 2,
                        'satuan' => 'Halaman'
                    ]
                ]
            ],
            [
                'judul' => 'Apakah kemarin ananda {{nama}} sudah membaca buku secara mandiri?',
                'tipe' => 'ganda_dan_angka',
                'opsi' => [
                    'pilihan' => [
                        ['label' => 'Iya', 'value' => 'iya', 'poin' => 0, 'require_ket' => false, 'require_angka' => true],
                        ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false, 'require_angka' => false]
                    ],
                    'angka' => [
                        'poin_per_angka' => 1,
                        'satuan' => 'Halaman'
                    ]
                ]
            ],
            [
                'judul' => 'Apakah kemarin pagi ananda {{nama}} ikut Shalat Dluha di madrasah?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Ikut', 'value' => 'ikut', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Udzur Haid', 'value' => 'udzur_haid', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Tidak Ikut', 'value' => 'tidak_ikut', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah tadi malam ananda {{nama}} belajar di kamar?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Tidak', 'value' => 'tidak', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah tadi malam ananda {{nama}} sudah memaafkan semua orang?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah tadi malam ananda {{nama}} sudah mendoakan semua kaum muslimin?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah kemarin ananda {{nama}} sudah mendoakan kedua orang tua?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Apakah kemarin ananda {{nama}} sudah membantu teman atau bersedekah?',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ]
        ];
        
        $urutan = 1;
        foreach ($default_pertanyaan as $dp) {
            $db->query("INSERT INTO pertanyaan_default (judul, tipe, opsi, urutan, is_active) VALUES (:judul, :tipe, :opsi, :urutan, 1)");
            $db->bind(':judul', $dp['judul']);
            $db->bind(':tipe', $dp['tipe']);
            $db->bind(':opsi', json_encode($dp['opsi']));
            $db->bind(':urutan', $urutan);
            $db->execute();
            $urutan++;
        }
        $messages[] = "Inserted 12 default questions into 'pertanyaan_default'.";
    } else {
        $messages[] = "Table 'pertanyaan_default' is not empty, skipping insertion.";
    }
} catch (Exception $e) {
    $messages[] = "<strong style='color:red;'>Error: " . $e->getMessage() . "</strong>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Migration Results</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .success { color: green; }
        .warning { color: red; font-weight: bold; border: 1px solid red; padding: 10px; background-color: #fee; display: inline-block; margin-top: 20px;}
        ul { line-height: 1.6; }
    </style>
</head>
<body>
    <h1>Migration Results</h1>
    <ul>
        <?php foreach ($messages as $msg): ?>
            <li><?= $msg ?></li>
        <?php endforeach; ?>
    </ul>
    
    <div class="warning">
        WARNING: Please delete this file (migrate_admin.php) after running it to prevent security issues!
    </div>
</body>
</html>
