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
            $sudahIsi[$s['nama']] = $this->absenModel->sudahIsi($tanggal, $s['nama']);
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
     * GET /absen/isi/{nama_encoded} — Form pertanyaan untuk siswa
     */
    public function isi(string $namaEncoded = ''): void
    {
        if (empty($namaEncoded)) {
            $this->redirect('absen');
        }

        $nama    = urldecode($namaEncoded);
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelas   = $this->konfig->getKelas();
        $siswa   = $this->konfig->getSiswa();

        // Validasi: nama harus ada di daftar
        $daftarNama = array_column($siswa, 'nama');
        if (!in_array($nama, $daftarNama)) {
            Flash::set('error', 'Nama siswa tidak ditemukan.');
            $this->redirect('absen');
        }

        // Ambil data existing jika sudah pernah isi
        $existing = $this->absenModel->getSiswaByTanggal($tanggal, $nama);

        $this->view('absen/form', [
            'title'    => 'Absen Harian — ' . $nama,
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

        $nama    = trim($_POST['nama'] ?? '');
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));

        if (empty($nama) || empty($tanggal)) {
            Flash::set('error', 'Data tidak valid.');
            $this->redirect('absen');
        }

        // Validasi tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            Flash::set('error', 'Format tanggal tidak valid.');
            $this->redirect('absen');
        }

        // Validasi nama ada di daftar siswa
        $daftarSiswa = $this->konfig->getSiswa();
        $daftarNama = array_column($daftarSiswa, 'nama');
        if (!in_array($nama, $daftarNama)) {
            Flash::set('error', 'Nama siswa tidak ditemukan.');
            $this->redirect('absen');
        }

        // Bangun data absen
        $data = $this->buildAbsenData($_POST);

        $isEdit = $this->absenModel->sudahIsi($tanggal, $nama);

        if ($this->absenModel->simpanSiswa($tanggal, $nama, $data)) {
            $this->redirect('absen/selesai?nama=' . rawurlencode($nama) . '&tanggal=' . $tanggal . '&edit=' . ($isEdit ? '1' : '0'));
        } else {
            Flash::set('error', 'Gagal menyimpan. Silakan coba lagi.');
            $this->redirect('absen/isi/' . rawurlencode($nama) . '?tanggal=' . $tanggal);
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
        $daftarNama = array_column($siswa, 'nama');
        $statistik   = $this->absenModel->getStatistik($tanggal, $daftarNama);
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
}
