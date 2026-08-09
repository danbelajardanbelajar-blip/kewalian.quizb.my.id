<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/LaporanModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';

/**
 * LaporanController.php
 * CRUD laporan presensi harian
 */
class LaporanController extends Controller
{
    private LaporanModel     $laporanModel;
    private KonfigurasiModel $konfig;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->konfig       = new KonfigurasiModel();
    }

    /**
     * GET /laporan — Daftar semua laporan
     */
    public function index(): void
    {
        $this->requireAuth();

        $semua = $this->laporanModel->getAll();

        $this->view('laporan/index', [
            'title'   => 'Riwayat Laporan Presensi',
            'laporan' => $semua,
        ]);
    }

    /**
     * POST /laporan/simpan — Simpan laporan baru / update
     */
    public function simpan(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('dashboard');
        }

        $tanggal = trim($_POST['tanggal'] ?? '');

        // Validasi tanggal
        if (empty($tanggal) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            Flash::set('error', 'Tanggal tidak valid.');
            $this->redirect('dashboard');
        }

        $kategori = $this->konfig->getKategori();
        $kelas    = $this->konfig->getKelas();
        $rawData  = $_POST['data'] ?? [];
        $siswa    = [];

        foreach ($rawData as $index => $row) {
            $namaRaw = $row['nama'] ?? '';
            if (empty($namaRaw)) continue;

            $entry = ['nama' => htmlspecialchars(strip_tags($namaRaw))];

            foreach ($kategori as $key => $label) {
                $entry[$key] = isset($row[$key]) && $row[$key] == '1' ? true : false;
            }

            $siswa[] = $entry;
        }

        if (empty($siswa)) {
            Flash::set('error', 'Data siswa tidak boleh kosong.');
            $this->redirect('dashboard?tanggal=' . $tanggal);
        }

        $isUpdate = $this->laporanModel->exists($tanggal);
        $existing = $isUpdate ? $this->laporanModel->getByTanggal($tanggal) : [];

        $saved = $this->laporanModel->save($tanggal, [
            'kelas'      => $kelas,
            'kategori'   => $kategori,
            'siswa'      => $siswa,
            'created_at' => $existing['created_at'] ?? date('Y-m-d H:i:s'),
        ]);

        if ($saved) {
            $msg = $isUpdate
                ? "Laporan tanggal {$tanggal} berhasil diperbarui."
                : "Laporan tanggal {$tanggal} berhasil disimpan.";
            Flash::set('success', $msg);
            $this->redirect('laporan/lihat/' . $tanggal);
        } else {
            Flash::set('error', 'Gagal menyimpan laporan. Pastikan folder storage/laporan dapat ditulis.');
            $this->redirect('dashboard?tanggal=' . $tanggal);
        }
    }

    /**
     * GET /laporan/lihat/{tanggal} — Detail laporan
     */
    public function lihat(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal);

        if (empty($data)) {
            Flash::set('error', 'Laporan untuk tanggal ' . $tanggal . ' tidak ditemukan.');
            $this->redirect('laporan');
        }

        $this->view('laporan/show', [
            'title'    => 'Detail Laporan — ' . date('d F Y', strtotime($tanggal)),
            'laporan'  => $data,
            'tanggal'  => $tanggal,
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

        $data = $this->laporanModel->getByTanggal($tanggal);

        if (empty($data)) {
            Flash::set('error', 'Laporan tidak ditemukan.');
            $this->redirect('laporan');
        }

        $kategori = $this->konfig->getKategori();
        $siswa    = $this->konfig->getSiswa();

        // Map data existing siswa untuk pre-fill form
        $existingSiswaData = [];
        foreach ($data['siswa'] ?? [] as $s) {
            $existingSiswaData[$s['nama']] = $s;
        }

        $this->view('laporan/edit', [
            'title'             => 'Edit Laporan — ' . date('d F Y', strtotime($tanggal)),
            'tanggal'           => $tanggal,
            'kelas'             => $data['kelas'] ?? $this->konfig->getKelas(),
            'kategori'          => $kategori,
            'siswa'             => $siswa,
            'existingSiswaData' => $existingSiswaData,
        ]);
    }

    /**
     * POST /laporan/hapus/{tanggal} — Hapus laporan
     */
    public function hapus(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        if ($this->laporanModel->delete($tanggal)) {
            Flash::set('success', 'Laporan tanggal ' . $tanggal . ' berhasil dihapus.');
        } else {
            Flash::set('error', 'Gagal menghapus laporan.');
        }

        $this->redirect('laporan');
    }

    /**
     * GET /laporan/rekap — Rekap kehadiran per siswa
     */
    public function rekap(): void
    {
        $this->requireAuth();

        $rekap    = $this->laporanModel->getRekapPerSiswa();
        $kategori = $this->konfig->getKategori();
        $kelas    = $this->konfig->getKelas();

        $this->view('laporan/rekap', [
            'title'    => 'Rekap Kehadiran — Kelas ' . $kelas,
            'rekap'    => $rekap,
            'kategori' => $kategori,
            'kelas'    => $kelas,
        ]);
    }

    /**
     * GET /laporan/export/{tanggal} — Export laporan CSV
     */
    public function export(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal);

        if (empty($data)) {
            Flash::set('error', 'Laporan tidak ditemukan untuk diexport.');
            $this->redirect('laporan');
        }

        $filename = 'laporan_' . $tanggal . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM untuk Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        $headers = ['No', 'Nama Siswa'];
        foreach ($data['kategori'] ?? [] as $label) {
            $headers[] = $label;
        }
        fputcsv($output, $headers);

        // Data rows
        $no = 1;
        foreach ($data['siswa'] ?? [] as $siswa) {
            $row = [$no++, $siswa['nama']];
            foreach ($data['kategori'] ?? [] as $key => $label) {
                $row[] = !empty($siswa[$key]) ? 'Hadir' : 'Tidak';
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
