<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/LaporanModel.php';

/**
 * DashboardController.php
 * Halaman utama: form input presensi harian
 */
class DashboardController extends Controller
{
    private KonfigurasiModel $konfig;
    private LaporanModel     $laporan;

    public function __construct()
    {
        $this->konfig  = new KonfigurasiModel();
        $this->laporan = new LaporanModel();
    }

    /**
     * GET / — Halaman utama: form input presensi
     */
    public function index(): void
    {
        $this->requireAuth();

        $tanggal  = $_GET['tanggal'] ?? date('Y-m-d');
        $kelas    = $this->konfig->getKelas();
        $kategori = $this->konfig->getKategori();
        $siswa    = $this->konfig->getSiswa();

        // Cek apakah laporan untuk tanggal ini sudah ada
        $laporanExisting = $this->laporan->getByTanggal($tanggal);
        $isEdit = !empty($laporanExisting);

        // Jika ada laporan existing, gunakan data tersebut
        $existingSiswaData = [];
        if ($isEdit) {
            foreach ($laporanExisting['siswa'] ?? [] as $s) {
                $existingSiswaData[$s['nama']] = $s;
            }
        }

        $this->view('dashboard/index', [
            'title'             => 'Input Presensi Harian — Kelas ' . $kelas,
            'kelas'             => $kelas,
            'kategori'          => $kategori,
            'siswa'             => $siswa,
            'tanggal'           => $tanggal,
            'isEdit'            => $isEdit,
            'existingSiswaData' => $existingSiswaData,
        ]);
    }
}
