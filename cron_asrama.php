<?php
/**
 * cron_asrama.php
 * Script untuk mengirim WA pengingat ke pengurus asrama secara otomatis.
 * Disarankan dijalankan melalui cron job (misal: 0 18 * * *, 0 21 * * *)
 */

if (php_sapi_name() !== 'cli' && !isset($_GET['run'])) {
    die("Akses ditolak. Harus dijalankan via CLI atau gunakan parameter ?run=1\n");
}

// Hari Jumat libur (tidak ada pengisian form absen)
if (date('N') == 5) {
    die("Hari Jumat libur. Cron dihentikan.\n");
}

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/models/AbsenModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/AsramaModel.php';

// Definisi URL statis
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://wali.quizb.my.id'); 
}
Session::start(); // Mencegah error jika dipanggil oleh model

$db = new Database();
$absenModel = new AbsenModel();
$konfigModel = new KonfigurasiModel();
$asramaModel = new AsramaModel();

$tanggal = date('Y-m-d');
$tglIndo = date('d F Y');

// Ambil semua user (Wali Kelas) dari database
$db->query("SELECT id, kelas FROM users");
$users = $db->resultSet();

$logs = [];
$logs[] = "Menjalankan cron asrama pada: " . date('Y-m-d H:i:s');

foreach ($users as $user) {
    $userId = (int)$user['id'];
    
    // 1. Ambil data siswa
    $siswa = $konfigModel->getSiswa($userId);
    if (empty($siswa)) continue;
    
    // 2. Ambil data absen hari ini
    $dataTanggal = $absenModel->getByTanggal($tanggal, $userId);
    
    // 3. Ambil pengaturan pengurus asrama
    $listPengurus = $asramaModel->getAll($userId);
    $pengurusMap = [];
    foreach ($listPengurus as $p) {
        $pengurusMap[$p['nama_asrama']] = $p;
    }
    
    // 4. Kelompokkan siswa per asrama
    $grupAsrama = [];
    foreach ($siswa as $s) {
        $asrama = trim($s['asrama'] ?? '');
        if (empty($asrama) || $asrama === 'Tanpa Asrama') continue; 
        $grupAsrama[$asrama][] = $s;
    }
    
    // 5. Proses per asrama
    foreach ($grupAsrama as $namaAsrama => $siswaAsrama) {
        if (!isset($pengurusMap[$namaAsrama])) continue; // Pengurus tidak disetting
        
        $pengurus = $pengurusMap[$namaAsrama];
        if (empty($pengurus['no_hp'])) continue;
        
        // Cek siapa yang belum isi
        $siswaBelumIsi = [];
        foreach ($siswaAsrama as $s) {
            if (!isset($dataTanggal['siswa'][$s['id']])) {
                $siswaBelumIsi[] = $s['nama'];
            }
        }
        
        if (empty($siswaBelumIsi)) {
            $logs[] = "[$namaAsrama - WaliID $userId] Semua siswa sudah mengisi.";
            continue; 
        }
        
        // Susun pesan WA
        $msg = "*LAPORAN KEDISIPLINAN ASRAMA*\n";
        $msg .= "Asrama: *{$namaAsrama}*\n";
        $msg .= "Pengurus: *{$pengurus['nama_pengurus']}*\n";
        $msg .= "Tanggal: *{$tglIndo}*\n\n";
        $msg .= "Anak-anak berikut belum mengisi laporan kedisiplinan:\n";
        
        $no = 1;
        foreach ($siswaBelumIsi as $nama) {
            $msg .= "{$no}. {$nama}\n";
            $no++;
        }
        
        $link = BASE_URL . "/absen?wali=" . $userId;
        $msg .= "\nMohon meminjamkan hp ke anak-anak di atas untuk mengisi laporan di link ini:\n{$link}\n";
        $msg .= "\n_Pesan otomatis dari Sistem Kedisiplinan Santri._";
        
        // Kirim
        $apiKey = 'wa-key-1e0a672693117e4d09db166e49979691';
        $dataWa = [
            'phone_number'   => $pengurus['no_hp'],
            'message'        => $msg,
            'scheduled_time' => date('Y-m-d\TH:i', strtotime('+5 seconds'))
        ];

        $ch = curl_init('https://wa.quizb.my.id/api/send.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataWa));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $logs[] = "[$namaAsrama - WaliID $userId] WA terkirim ke {$pengurus['no_hp']} (" . count($siswaBelumIsi) . " anak belum isi).";
        } else {
            $logs[] = "[$namaAsrama - WaliID $userId] GAGAL kirim WA ke {$pengurus['no_hp']}. HTTP $httpCode.";
        }
        
        // Beri jeda 2 detik agar tidak terlalu membebani API WA
        sleep(2);
    }
}

echo implode("\n", $logs) . "\n";
echo "Cron Selesai.\n";
