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
        $usernameWali = $_GET['wali'] ?? '';
        
        $db = new Database();
        if (empty($usernameWali)) {
            $db->query("SELECT username, nama_lengkap, kelas FROM users ORDER BY kelas ASC");
            $listWali = $db->resultSet();
            
            $this->view('absen/pilih_wali', [
                'title' => 'Pilih Kelas',
                'listWali' => $listWali
            ], false);
            return;
        }

        $db->query("SELECT id, kelas FROM users WHERE username = :username");
        $db->bind(':username', $usernameWali);
        $userWali = $db->single();

        if (!$userWali) {
            die("Wali kelas tidak ditemukan. Silakan cek kembali link Anda.");
        }

        $userId = $userWali['id'];
        $kelas  = $userWali['kelas'];
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        
        $siswa   = $this->konfig->getSiswa($userId);

        // Tandai siapa yang sudah isi
        $sudahIsi = [];
        foreach ($siswa as $s) {
            $sudahIsi[$s['id']] = $this->absenModel->sudahIsi($tanggal, $s['id']);
        }

        $this->view('absen/index', [
            'title'    => 'Absen Mandiri — Kelas ' . htmlspecialchars($kelas),
            'siswa'    => $siswa,
            'kelas'    => $kelas,
            'tanggal'  => $tanggal,
            'sudahIsi' => $sudahIsi,
            'usernameWali' => $usernameWali
        ], false);
    }

    /**
     * GET /absen/isi/{id} — Form pertanyaan untuk siswa
     */
    public function isi(string $idStr = ''): void
    {
        $usernameWali = $_GET['wali'] ?? '';
        if (empty($idStr) || empty($usernameWali)) {
            $this->redirect('absen');
        }

        $id      = (int)$idStr;
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        
        $db = new Database();
        $db->query("SELECT id, kelas FROM users WHERE username = :username");
        $db->bind(':username', $usernameWali);
        $userWali = $db->single();
        
        if (!$userWali) {
            $this->redirect('absen');
        }
        
        $userId = $userWali['id'];
        $kelas  = $userWali['kelas'];
        $siswa   = $this->konfig->getSiswa($userId);

        // Validasi: ID harus ada di daftar
        $daftarId = array_column($siswa, 'id');
        if (!in_array($id, $daftarId)) {
            Flash::set('error', 'Data siswa tidak ditemukan.');
            $this->redirect('absen?wali=' . urlencode($usernameWali));
        }

        $nama = '';
        foreach ($siswa as $s) {
            if ($s['id'] === $id) $nama = $s['nama'];
        }

        // Ambil data existing jika sudah pernah isi
        $existing = $this->absenModel->getSiswaByTanggal($tanggal, $id, $userId);

        $this->view('absen/form', [
            'title'    => 'Absen Harian — ' . htmlspecialchars($nama),
            'id'       => $id,
            'nama'     => $nama,
            'tanggal'  => $tanggal,
            'kelas'    => $kelas,
            'existing' => $existing,
            'isEdit'   => !empty($existing),
            'usernameWali' => $usernameWali
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

        $usernameWali = $_POST['usernameWali'] ?? '';
        
        $db = new Database();
        $db->query("SELECT id FROM users WHERE username = :username");
        $db->bind(':username', $usernameWali);
        $userWali = $db->single();
        $userId = $userWali ? $userWali['id'] : null;

        // Validasi ID ada di daftar siswa milik wali ini
        $daftarSiswa = $this->konfig->getSiswa($userId);
        $daftarId = array_column($daftarSiswa, 'id');
        if (!in_array($id, $daftarId)) {
            Flash::set('error', 'Siswa tidak ditemukan.');
            $this->redirect('absen?wali=' . urlencode($usernameWali));
        }

        // Bangun data absen
        $data = $this->buildAbsenData($_POST);

        $isEdit = $this->absenModel->sudahIsi($tanggal, $id);

        if ($this->absenModel->simpanSiswa($tanggal, $id, $nama, $data)) {
            $this->redirect('absen/selesai?id=' . $id . '&nama=' . rawurlencode($nama) . '&tanggal=' . $tanggal . '&edit=' . ($isEdit ? '1' : '0') . '&wali=' . urlencode($usernameWali));
        } else {
            Flash::set('error', 'Gagal menyimpan. Silakan coba lagi.');
            $this->redirect('absen/isi/' . $id . '?tanggal=' . $tanggal . '&wali=' . urlencode($usernameWali));
        }
    }

    /**
     * GET /absen/selesai — Halaman konfirmasi selesai
     */
    public function selesai(): void
    {
        $id      = (int)($_GET['id'] ?? 0);
        $nama    = urldecode($_GET['nama'] ?? '');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $isEdit  = ($_GET['edit'] ?? '0') === '1';
        $usernameWali = $_GET['wali'] ?? '';
        
        $db = new Database();
        $db->query("SELECT kelas FROM users WHERE username = :username");
        $db->bind(':username', $usernameWali);
        $userWali = $db->single();
        $kelas = $userWali ? $userWali['kelas'] : '';

        $this->view('absen/selesai', [
            'title'   => 'Absen Tersimpan!',
            'id'      => $id,
            'nama'    => $nama,
            'tanggal' => $tanggal,
            'isEdit'  => $isEdit,
            'kelas'   => $kelas,
            'usernameWali' => $usernameWali
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

        // Baca Buku
        $bukuSudah  = $post['buku_sudah'] ?? 'belum';
        $bukuJumlah = (int) ($post['buku_jumlah'] ?? 0);
        if ($bukuSudah === 'belum') $bukuJumlah = 0;
        $data['baca_buku'] = [
            'status' => $bukuSudah,
            'jumlah' => $bukuJumlah,
        ];

        // Shalat Dluha
        $data['dluha'] = ['status' => $post['dluha'] ?? 'tidak_ikut'];

        // Belajar di kamar
        $data['belajar'] = ['status' => $post['belajar'] ?? 'tidak'];

        // Tambahan 4 pertanyaan baru (Memaafkan, Doakan Muslimin, Doakan Ortu, Shadaqah)
        $data['memaafkan'] = ['status' => $post['memaafkan'] ?? 'tidak'];
        $data['mendoakan_muslimin'] = ['status' => $post['mendoakan_muslimin'] ?? 'tidak'];
        $data['mendoakan_ortu'] = ['status' => $post['mendoakan_ortu'] ?? 'tidak'];
        $data['shadaqah'] = ['status' => $post['shadaqah'] ?? 'tidak'];

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

    /**
     * POST /absen/hapus_siswa — Hapus data absen satu siswa pada hari tertentu
     */
    public function hapus_siswa(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->redirect('absen/rekap');
        }

        $tanggal = $_POST['tanggal'] ?? '';
        $idSiswa = (int)($_POST['id_siswa'] ?? 0);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) && $idSiswa > 0) {
            if ($this->absenModel->deleteSiswa($tanggal, $idSiswa)) {
                Flash::set('success', 'Data absen siswa berhasil dihapus.');
            } else {
                Flash::set('error', 'Gagal menghapus data siswa atau data tidak ditemukan.');
            }
        } else {
            Flash::set('error', 'Data tidak valid.');
        }

        $this->redirect('absen/rekap?tanggal=' . $tanggal);
    }
}
