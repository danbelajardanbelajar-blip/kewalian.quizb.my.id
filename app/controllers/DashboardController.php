<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/AbsenModel.php';
require_once APP_PATH . '/models/PertanyaanModel.php';

/**
 * DashboardController.php
 * Halaman utama: Grafik Analitik Kehadiran Siswa
 */
class DashboardController extends Controller
{
    private KonfigurasiModel $konfig;
    private AbsenModel       $absen;
    private PertanyaanModel  $pertanyaanModel;

    public function __construct()
    {
        $this->konfig = new KonfigurasiModel();
        $this->absen  = new AbsenModel();
        $this->pertanyaanModel = new PertanyaanModel();
    }

    /**
     * GET / — Halaman utama: Grafik Analitik
     */
    public function index(): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelas   = $this->konfig->getKelas($userId);
        $siswa   = $this->konfig->getSiswa($userId);
        
        $stats = $this->absen->getStatistik($tanggal, $siswa, $userId);
        $totalSiswa = $stats['total'];
        
        $pertanyaan = $this->pertanyaanModel->getActive($userId);
        
        $labels = [];
        $persentase = [];
        $warna = [];
        
        $colors = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)',
            'rgba(0, 123, 255, 0.7)',
            'rgba(232, 62, 140, 0.7)',
            'rgba(32, 201, 151, 0.7)',
            'rgba(253, 126, 20, 0.7)',
            'rgba(111, 66, 193, 0.7)'
        ];

        // Inisialisasi data grafik
        foreach ($pertanyaan as $i => $p) {
            $labels[] = $p['judul'];
            $persentase[$p['id']] = 0;
            $warna[] = $colors[$i % count($colors)];
        }

        if ($totalSiswa > 0) {
            $absenData = $this->absen->getByTanggal($tanggal, $userId);
            $siswaData = $absenData['siswa'] ?? [];
            
            $countPoinLebihNol = array_fill_keys(array_column($pertanyaan, 'id'), 0);
            
            foreach ($siswaData as $s) {
                foreach ($pertanyaan as $p) {
                    $pId = $p['id'];
                    $ans = $s['jawaban'][$pId] ?? null;
                    if ($ans && $ans['poin'] > 0) {
                        $countPoinLebihNol[$pId]++;
                    }
                }
            }
            
            $persentaseArray = [];
            foreach ($pertanyaan as $p) {
                $pId = $p['id'];
                $persentaseArray[] = round(($countPoinLebihNol[$pId] / $totalSiswa) * 100);
            }
            $persentase = $persentaseArray;
        } else {
            $persentase = array_fill(0, count($pertanyaan), 0);
        }

        $grafikData = [
            'labels' => $labels,
            'persentase' => $persentase,
            'warna' => $warna
        ];

        $this->view('dashboard/index', [
            'title'      => 'Dashboard Wali Kelas — ' . $kelas,
            'kelas'      => $kelas,
            'tanggal'    => $tanggal,
            'stats'      => $stats,
            'grafikData' => $grafikData
        ]);
    }
}
