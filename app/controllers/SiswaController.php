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
        foreach ($siswa as $s) {
            if ($s['nama'] === $namaBaru) {
                Flash::set('warning', 'Siswa "' . htmlspecialchars($namaBaru) . '" sudah ada dalam daftar.');
                $this->redirect('siswa');
            }
        }

        $maxId = array_reduce($siswa, fn($max, $s) => max($max, $s['id']), 0);
        $siswa[] = [
            'id' => $maxId + 1,
            'nama' => $namaBaru
        ];
        
        // Sort by name
        usort($siswa, fn($a, $b) => strcmp($a['nama'], $b['nama']));

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

        $idHapus = (int)($_POST['id'] ?? 0);

        if ($idHapus <= 0) {
            Flash::set('error', 'ID siswa tidak valid.');
            $this->redirect('siswa');
        }

        $siswa = $this->konfig->getSiswa();
        
        // Cari nama untuk flash message
        $namaHapus = '';
        foreach ($siswa as $s) {
            if ($s['id'] === $idHapus) {
                $namaHapus = $s['nama'];
                break;
            }
        }

        $siswa = array_filter($siswa, fn($s) => $s['id'] !== $idHapus);

        if ($this->konfig->saveSiswa(array_values($siswa))) {
            Flash::set('success', 'Siswa "' . htmlspecialchars($namaHapus) . '" berhasil dihapus.');
        } else {
            Flash::set('error', 'Gagal menghapus siswa.');
        }

        $this->redirect('siswa');
    }

    /**
     * POST /siswa/edit — Edit data siswa
     */
    public function edit(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('siswa');
        }

        $idEdit = (int)($_POST['id'] ?? 0);
        $namaBaru = strtoupper(trim($_POST['nama'] ?? ''));
        $noHpBaru = trim($_POST['no_hp'] ?? '');

        if ($idEdit <= 0 || empty($namaBaru)) {
            Flash::set('error', 'Data tidak valid.');
            $this->redirect('siswa');
        }

        $siswa = $this->konfig->getSiswa();
        
        // Cek duplikasi nama untuk ID lain
        foreach ($siswa as $s) {
            if ($s['id'] !== $idEdit && $s['nama'] === $namaBaru) {
                Flash::set('warning', 'Siswa dengan nama "' . htmlspecialchars($namaBaru) . '" sudah ada.');
                $this->redirect('siswa');
            }
        }

        // Format No HP (hapus karakter selain angka)
        $noHpBaru = preg_replace('/[^0-9]/', '', $noHpBaru);

        $found = false;
        foreach ($siswa as &$s) {
            if ($s['id'] === $idEdit) {
                $s['nama'] = $namaBaru;
                $s['no_hp'] = $noHpBaru;
                $found = true;
                break;
            }
        }
        unset($s);

        if (!$found) {
            Flash::set('error', 'Siswa tidak ditemukan.');
            $this->redirect('siswa');
        }

        // Sort by name
        usort($siswa, fn($a, $b) => strcmp($a['nama'], $b['nama']));

        if ($this->konfig->saveSiswa($siswa)) {
            Flash::set('success', 'Data siswa berhasil diperbarui.');
        } else {
            Flash::set('error', 'Gagal memperbarui data siswa.');
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

        // We receive an array of IDs from the frontend, but we need to save full objects
        $siswaLama = $this->konfig->getSiswa();
        $mapSiswa = [];
        foreach ($siswaLama as $s) {
            $mapSiswa[$s['id']] = $s;
        }

        $siswaBaru = [];
        foreach ($urutBaru as $idStr) {
            $id = (int)$idStr;
            if (isset($mapSiswa[$id])) {
                $siswaBaru[] = $mapSiswa[$id];
            }
        }

        if ($this->konfig->saveSiswa($siswaBaru)) {
            $this->json(['success' => true, 'message' => 'Urutan berhasil disimpan']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan urutan'], 500);
        }
    }
}
