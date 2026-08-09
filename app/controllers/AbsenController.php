<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AbsenModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';

/**
 * AbsenController.php
 * Absen Mandiri Siswa — TIDAK memerlukan login
 * Siswa mengisi sendiri data kehadiran & kegiatan harian
 */
class AbsenController extends Controller
{
    private AbsenModel     $absenModel;
    private KonfigurasiModel $konfig;

    public function __construct()
    {
        $this->absenModel = new AbsenModel();
        $this->konfig     = new KonfigurasiModel();
    }

    /**
     * GET /absen — Halaman pilih nama siswa
     */
    public function index(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $siswa   = $this->konfig->getSiswa();
        $kelas   = $this->konfig->getKelas();

        // Tandai siapa yang sudah isi
        $sudahIsi = [];
        foreach ($siswa as $s) {
            $sudahIsi[$s['id']] = $this->absenModel->sudahIsi($tanggal, $s['id']);
        }

        $this->view('absen/index', [
            'title'    => 'Absen Mandiri — Kelas ' . $kelas,
            'siswa'    => $siswa,
            'kelas'    => $kelas,
            'tanggal'  => $tanggal,
            'sudahIsi' => $sudahIsi,
        ], false); // tanpa layout wali kelas
    }

    /**
     * GET /absen/isi/{id} — Form pertanyaan untuk siswa
     */
    public function isi(string $idStr = ''): void
    {
        if (empty($idStr)) {
            $this->redirect('absen');
        }

        $id      = (int)$idStr;
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelas   = $this->konfig->getKelas();
        $siswa   = $this->konfig->getSiswa();

        // Validasi: ID harus ada di daftar
        $daftarId = array_column($siswa, 'id');
        if (!in_array($id, $daftarId)) {
            Flash::set('error', 'Data siswa tidak ditemukan.');
            $this->redirect('absen');
        }

        $nama = '';
        foreach ($siswa as $s) {
            if ($s['id'] === $id) $nama = $s['nama'];
        }

        // Ambil data existing jika sudah pernah isi
        $existing = $this->absenModel->getSiswaByTanggal($tanggal, $id);

        $this->view('absen/form', [
            'title'    => 'Absen Harian — ' . $nama,
            'id'       => $id,
            'nama'     => $nama,
            'tanggal'  => $tanggal,
            'kelas'    => $kelas,
            'existing' => $existing,
            'isEdit'   => !empty($existing),
        ], false);
    }

    /**
     * POST /absen/simpan — Proses simpan absen siswa
     */
    public function simpan(): void
    {
        if (!$this->isPost()) {
            $this->redirect('absen');
        }

        $id      = (int)($_POST['id'] ?? 0);
        $nama    = trim($_POST['nama'] ?? '');
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));

        if ($id <= 0 || empty($nama) || empty($tanggal)) {
            Flash::set('error', 'Data tidak valid.');
            $this->redirect('absen');
        }

        // Validasi tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            Flash::set('error', 'Format tanggal tidak valid.');
            $this->redirect('absen');
        }

        // Validasi ID ada di daftar siswa
        $daftarSiswa = $this->konfig->getSiswa();
        $daftarId = array_column($daftarSiswa, 'id');
        if (!in_array($id, $daftarId)) {
            Flash::set('error', 'Siswa tidak ditemukan.');
            $this->redirect('absen');
        }

        // Bangun data absen
        $data = $this->buildAbsenData($_POST);

        $isEdit = $this->absenModel->sudahIsi($tanggal, $id);

        if ($this->absenModel->simpanSiswa($tanggal, $id, $nama, $data)) {
            $this->redirect('absen/selesai?nama=' . rawurlencode($nama) . '&tanggal=' . $tanggal . '&edit=' . ($isEdit ? '1' : '0'));
        } else {
            Flash::set('error', 'Gagal menyimpan. Silakan coba lagi.');
            $this->redirect('absen/isi/' . $id . '?tanggal=' . $tanggal);
        }
    }

    /**
     * GET /absen/selesai — Halaman konfirmasi selesai
     */
    public function selesai(): void
    {
        $nama    = urldecode($_GET['nama'] ?? '');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $isEdit  = ($_GET['edit'] ?? '0') === '1';
        $kelas   = $this->konfig->getKelas();

        $this->view('absen/selesai', [
            'title'   => 'Absen Tersimpan!',
            'nama'    => $nama,
            'tanggal' => $tanggal,
            'isEdit'  => $isEdit,
            'kelas'   => $kelas,
        ], false);
    }

    /**
     * GET /absen/rekap — Rekap absen mandiri (perlu login wali)
     */
    public function rekap(): void
    {
        $this->requireAuth();

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $siswa   = $this->konfig->getSiswa();
        $kelas   = $this->konfig->getKelas();

        $dataTanggal = $this->absenModel->getByTanggal($tanggal);
        $statistik   = $this->absenModel->getStatistik($tanggal, $siswa);
        $allDates    = $this->absenModel->getAllDates();

        $this->view('absen/rekap', [
            'title'       => 'Rekap Absen Mandiri — ' . date('d F Y', strtotime($tanggal)),
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'dataTanggal' => $dataTanggal,
            'siswa'       => $siswa,
            'statistik'   => $statistik,
            'allDates'    => $allDates,
        ]);
    }

    /**
     * Bangun array data absen dari POST
     */
    private function buildAbsenData(array $post): array
    {
        // Kategori dengan status hadir/absen/sakit/izin
        $kategoriAbsen = ['sekolah', 'almiftah', 'diniyah', 'subuh'];
        $data = [];

        foreach ($kategoriAbsen as $kat) {
            $status = $post[$kat] ?? 'absen';
            $ket    = '';
            if ($status === 'izin') {
                $ket = strip_tags(trim($post[$kat . '_ket'] ?? ''));
            }
            $data[$kat] = ['status' => $status, 'ket' => $ket];
        }

        // Baca Al-Qur'an
        $quranType   = $post['quran_type'] ?? 'halaman';
        $quranJumlah = (int) ($post['quran_jumlah'] ?? 0);
        $data['quran'] = [
            'type'   => $quranType,
            'jumlah' => $quranJumlah,
        ];

        // Shalat Dluha
        $data['dluha'] = ['status' => $post['dluha'] ?? 'tidak_ikut'];

        // Belajar di kamar
        $data['belajar'] = ['status' => $post['belajar'] ?? 'tidak'];

        return $data;
    }

    /**
     * POST /absen/hapus — Hapus data absen satu hari penuh
     */
    public function hapus(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->redirect('absen/rekap');
        }

        $tanggal = $_POST['tanggal'] ?? '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            if ($this->absenModel->deleteTanggal($tanggal)) {
                Flash::set('success', 'Data absen mandiri tanggal ' . date('d F Y', strtotime($tanggal)) . ' berhasil dihapus.');
            } else {
                Flash::set('error', 'Gagal menghapus data atau data tidak ditemukan.');
            }
        } else {
            Flash::set('error', 'Format tanggal tidak valid.');
        }

        $this->redirect('absen/rekap');
    }
}
