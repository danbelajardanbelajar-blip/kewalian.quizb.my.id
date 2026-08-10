<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/AbsenModel.php';

/**
 * DashboardController.php
 * Halaman utama: Grafik Analitik Kehadiran Siswa
 */
class DashboardController extends Controller
{
    private KonfigurasiModel $konfig;
    private AbsenModel       $absen;

    public function __construct()
    {
        $this->konfig = new KonfigurasiModel();
        $this->absen  = new AbsenModel();
    }

    /**
     * GET / — Halaman utama: Grafik Analitik
     */
    public function index(): void
    {
        $this->requireAuth();

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelas   = $this->konfig->getKelas();
        $siswa   = $this->konfig->getSiswa();
        
        // Ambil statistik dari AbsenModel
        $stats = $this->absen->getStatistik($tanggal, $siswa);
        $totalSiswa = $stats['total'];
        
        // Siapkan data grafik
        $grafikData = [
            'labels' => ['Sekolah', 'Al-Miftah', 'Diniyah', 'Ngaji Pagi', 'Al-Qur\'an', 'Dluha', 'Belajar'],
            'persentase' => [0, 0, 0, 0, 0, 0, 0],
            'warna' => [
                'rgba(54, 162, 235, 0.7)',  // Sekolah
                'rgba(255, 99, 132, 0.7)',  // Al-Miftah
                'rgba(255, 206, 86, 0.7)',  // Diniyah
                'rgba(75, 192, 192, 0.7)',  // Subuh
                'rgba(153, 102, 255, 0.7)', // Quran
                'rgba(255, 159, 64, 0.7)',  // Dluha
                'rgba(199, 199, 199, 0.7)'  // Belajar
            ]
        ];

        if ($totalSiswa > 0) {
            // Hitung persentase dari per_kategori
            $kategoriIndex = ['sekolah' => 0, 'almiftah' => 1, 'diniyah' => 2, 'subuh' => 3];
            foreach ($kategoriIndex as $kat => $idx) {
                $hadir = $stats['per_kategori'][$kat]['hadir'] ?? 0;
                $grafikData['persentase'][$idx] = round(($hadir / $totalSiswa) * 100);
            }

            // Hitung untuk Al-Qur'an, Dluha, Belajar (Kita hitung manual dari data Absen)
            $absenData = $this->absen->getByTanggal($tanggal);
            $siswaData = $absenData['siswa'] ?? [];
            
            $quranBaca = 0;
            $dluhaIkut = 0;
            $belajarIya = 0;

            foreach ($siswaData as $s) {
                // Quran: dihitung sudah baca jika type != 'tidak' dan tidak kosong
                $q = $s['quran'] ?? [];
                if (!empty($q) && ($q['type'] ?? '') !== 'tidak') {
                    $quranBaca++;
                }

                // Dluha: dihitung jika ikut atau udzur
                $dl = $s['dluha']['status'] ?? '';
                if ($dl === 'ikut' || $dl === 'udzur_haid') {
                    $dluhaIkut++;
                }

                // Belajar: dihitung jika iya
                $bl = $s['belajar']['status'] ?? '';
                if ($bl === 'iya') {
                    $belajarIya++;
                }
            }

            $grafikData['persentase'][4] = round(($quranBaca / $totalSiswa) * 100);
            $grafikData['persentase'][5] = round(($dluhaIkut / $totalSiswa) * 100);
            $grafikData['persentase'][6] = round(($belajarIya / $totalSiswa) * 100);
        }

        $this->view('dashboard/index', [
            'title'      => 'Dashboard Wali Kelas — ' . $kelas,
            'kelas'      => $kelas,
            'tanggal'    => $tanggal,
            'stats'      => $stats,
            'grafikData' => $grafikData
        ]);
    }
}
