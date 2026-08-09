<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';

/**
 * SiswaController.php
 * Manajemen data siswa
 */
class SiswaController extends Controller
{
    private KonfigurasiModel $konfig;

    public function __construct()
    {
        $this->konfig = new KonfigurasiModel();
    }

    /**
     * GET /siswa — Daftar siswa
     */
    public function index(): void
    {
        $this->requireAuth();

        $this->view('siswa/index', [
            'title'  => 'Manajemen Data Siswa',
            'kelas'  => $this->konfig->getKelas(),
            'siswa'  => $this->konfig->getSiswa(),
        ]);
    }

    /**
     * POST /siswa/tambah — Tambah siswa baru
     */
    public function tambah(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('siswa');
        }

        $namaBaru = strtoupper(trim($_POST['nama'] ?? ''));

        if (empty($namaBaru)) {
            Flash::set('error', 'Nama siswa tidak boleh kosong.');
            $this->redirect('siswa');
        }

        $siswa = $this->konfig->getSiswa();

        // Cek duplikasi
        if (in_array($namaBaru, $siswa)) {
            Flash::set('warning', 'Siswa "' . htmlspecialchars($namaBaru) . '" sudah ada dalam daftar.');
            $this->redirect('siswa');
        }

        $siswa[] = $namaBaru;
        sort($siswa);

        if ($this->konfig->saveSiswa($siswa)) {
            Flash::set('success', 'Siswa "' . htmlspecialchars($namaBaru) . '" berhasil ditambahkan.');
        } else {
            Flash::set('error', 'Gagal menyimpan data siswa.');
        }

        $this->redirect('siswa');
    }

    /**
     * POST /siswa/hapus — Hapus siswa
     */
    public function hapus(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('siswa');
        }

        $namaHapus = trim($_POST['nama'] ?? '');

        if (empty($namaHapus)) {
            Flash::set('error', 'Nama siswa tidak valid.');
            $this->redirect('siswa');
        }

        $siswa = $this->konfig->getSiswa();
        $siswa = array_filter($siswa, fn($s) => $s !== $namaHapus);

        if ($this->konfig->saveSiswa(array_values($siswa))) {
            Flash::set('success', 'Siswa "' . htmlspecialchars($namaHapus) . '" berhasil dihapus.');
        } else {
            Flash::set('error', 'Gagal menghapus siswa.');
        }

        $this->redirect('siswa');
    }

    /**
     * POST /siswa/urut — Simpan urutan siswa baru (drag & drop)
     */
    public function urut(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $urutBaru = json_decode(file_get_contents('php://input'), true)['urutan'] ?? [];

        if (empty($urutBaru)) {
            $this->json(['success' => false, 'message' => 'Data urutan kosong'], 400);
        }

        $urutBaru = array_map('strval', $urutBaru);

        if ($this->konfig->saveSiswa($urutBaru)) {
            $this->json(['success' => true, 'message' => 'Urutan berhasil disimpan']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan urutan'], 500);
        }
    }
}
