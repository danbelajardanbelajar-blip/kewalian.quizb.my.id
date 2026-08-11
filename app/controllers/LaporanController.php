<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/LaporanModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/PertanyaanModel.php';

/**
 * LaporanController.php
 * CRUD laporan presensi harian
 */
class LaporanController extends Controller
{
    private LaporanModel     $laporanModel;
    private KonfigurasiModel $konfig;
    private PertanyaanModel  $pertanyaanModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->konfig       = new KonfigurasiModel();
        $this->pertanyaanModel = new PertanyaanModel();
    }

    /**
     * GET /laporan — Daftar semua laporan
     */
    public function index(): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        $semua = $this->laporanModel->getAll($userId);

        $this->view('laporan/index', [
            'title'   => 'Riwayat Laporan Presensi',
            'laporan' => $semua,
        ]);
    }

    /**
     * GET /laporan/lihat/{tanggal} — Detail laporan
     */
    public function lihat(string $tanggal = ''): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal, $userId);

        if (empty($data)) {
            Flash::set('error', 'Laporan untuk tanggal ' . $tanggal . ' tidak ditemukan.');
            $this->redirect('laporan');
        }

        $pertanyaan = $this->pertanyaanModel->getActive($userId);

        $this->view('laporan/show', [
            'title'      => 'Detail Laporan — ' . date('d F Y', strtotime($tanggal)),
            'laporan'    => $data,
            'tanggal'    => $tanggal,
            'pertanyaan' => $pertanyaan
        ]);
    }

    /**
     * GET /laporan/edit/{tanggal} — Form edit laporan
     */
    public function edit(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        // Dashboard/Rekap sekarang menangani mode edit secara implisit melalui form siswa individual
        $this->redirect('absen/rekap?tanggal=' . $tanggal);
    }

    /**
     * POST /laporan/hapus/{tanggal} — Hapus laporan
     */
    public function hapus(string $tanggal = ''): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        if ($this->laporanModel->delete($tanggal, $userId)) {
            Flash::set('success', 'Laporan tanggal ' . $tanggal . ' berhasil dihapus.');
        } else {
            Flash::set('error', 'Gagal menghapus laporan.');
        }

        $this->redirect('laporan');
    }

    /**
     * GET /laporan/rekap — Rekap kehadiran/poin per siswa secara kumulatif
     */
    public function rekap(): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        $rekap = $this->laporanModel->getRekapPerSiswa($userId);
        $kelas = $this->konfig->getKelas($userId);

        $this->view('laporan/rekap', [
            'title' => 'Rekap Kumulatif — Kelas ' . $kelas,
            'rekap' => $rekap,
            'kelas' => $kelas,
        ]);
    }

    /**
     * GET /laporan/export/{tanggal} — Export laporan CSV
     */
    public function export(string $tanggal = ''): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal, $userId);

        if (empty($data)) {
            Flash::set('error', 'Laporan tidak ditemukan untuk diexport.');
            $this->redirect('laporan');
        }

        $pertanyaan = $this->pertanyaanModel->getActive($userId);

        $filename = 'laporan_' . $tanggal . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // BOM untuk Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        $headers = ['No', 'Nama Siswa'];
        foreach ($pertanyaan as $p) {
            $headers[] = $p['judul'];
            $headers[] = $p['judul'] . ' (Keterangan)';
        }
        $headers[] = 'Total Poin';
        
        fputcsv($output, $headers);

        // Data rows
        $no = 1;
        foreach ($data['siswa'] ?? [] as $siswa) {
            $row = [$no++, $siswa['nama']];
            
            foreach ($pertanyaan as $p) {
                $pId = $p['id'];
                $ans = $siswa['jawaban'][$pId] ?? null;
                if ($ans) {
                    $row[] = $ans['jawaban'];
                    $row[] = $ans['keterangan'] ?? '';
                } else {
                    $row[] = '-';
                    $row[] = '-';
                }
            }

            $row[] = $siswa['total_poin'] ?? 0;
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
