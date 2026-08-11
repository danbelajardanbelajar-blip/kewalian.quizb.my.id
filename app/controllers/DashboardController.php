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
            'labels' => ['Sekolah', 'Al-Miftah', 'Diniyah', 'Ngaji Pagi', 'Al-Qur\'an', 'Dluha', 'Belajar', 'Baca Buku', 'Memaafkan', 'Doa Muslim', 'Doa Ortu', 'Sedekah'],
            'persentase' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            'warna' => [
                'rgba(54, 162, 235, 0.7)',  // Sekolah
                'rgba(255, 99, 132, 0.7)',  // Al-Miftah
                'rgba(255, 206, 86, 0.7)',  // Diniyah
                'rgba(75, 192, 192, 0.7)',  // Subuh
                'rgba(153, 102, 255, 0.7)', // Quran
                'rgba(255, 159, 64, 0.7)',  // Dluha
                'rgba(199, 199, 199, 0.7)', // Belajar
                'rgba(0, 123, 255, 0.7)',   // Baca Buku
                'rgba(232, 62, 140, 0.7)',  // Memaafkan
                'rgba(32, 201, 151, 0.7)',  // Doa Muslim
                'rgba(253, 126, 20, 0.7)',  // Doa Ortu
                'rgba(111, 66, 193, 0.7)'   // Sedekah
            ]
        ];

        if ($totalSiswa > 0) {
            // Hitung persentase dari per_kategori
            $kategoriIndex = ['sekolah' => 0, 'almiftah' => 1, 'diniyah' => 2, 'subuh' => 3];
            foreach ($kategoriIndex as $kat => $idx) {
                $hadir = $stats['per_kategori'][$kat]['hadir'] ?? 0;
                $grafikData['persentase'][$idx] = round(($hadir / $totalSiswa) * 100);
            }

            // Hitung untuk sisa point secara manual
            $absenData = $this->absen->getByTanggal($tanggal);
            $siswaData = $absenData['siswa'] ?? [];
            
            $quranBaca = 0;
            $dluhaIkut = 0;
            $belajarIya = 0;
            $bukuIya = 0;
            $maafIya = 0;
            $doaMuslimIya = 0;
            $doaOrtuIya = 0;
            $sedekahIya = 0;

            foreach ($siswaData as $s) {
                // Quran
                $q = $s['quran'] ?? [];
                if (!empty($q) && ($q['type'] ?? '') !== 'tidak') {
                    $quranBaca++;
                }

                // Dluha
                $dl = $s['dluha']['status'] ?? '';
                if ($dl === 'ikut' || $dl === 'udzur_haid') {
                    $dluhaIkut++;
                }

                // Belajar & Pertanyaan Tambahan
                if (($s['belajar']['status'] ?? '') === 'iya') $belajarIya++;
                if (($s['baca_buku']['status'] ?? '') === 'iya') $bukuIya++;
                if (($s['memaafkan']['status'] ?? '') === 'iya') $maafIya++;
                if (($s['mendoakan_muslimin']['status'] ?? '') === 'iya') $doaMuslimIya++;
                if (($s['mendoakan_ortu']['status'] ?? '') === 'iya') $doaOrtuIya++;
                if (($s['shadaqah']['status'] ?? '') === 'iya') $sedekahIya++;
            }

            $grafikData['persentase'][4] = round(($quranBaca / $totalSiswa) * 100);
            $grafikData['persentase'][5] = round(($dluhaIkut / $totalSiswa) * 100);
            $grafikData['persentase'][6] = round(($belajarIya / $totalSiswa) * 100);
            $grafikData['persentase'][7] = round(($bukuIya / $totalSiswa) * 100);
            $grafikData['persentase'][8] = round(($maafIya / $totalSiswa) * 100);
            $grafikData['persentase'][9] = round(($doaMuslimIya / $totalSiswa) * 100);
            $grafikData['persentase'][10] = round(($doaOrtuIya / $totalSiswa) * 100);
            $grafikData['persentase'][11] = round(($sedekahIya / $totalSiswa) * 100);
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
