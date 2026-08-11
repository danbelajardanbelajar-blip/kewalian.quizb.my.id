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

        $userId = Session::get('user_id');

        // Cek duplikasi langsung dari DB
        $db = new Database();
        $db->query("SELECT id FROM siswa WHERE user_id = :uid AND nama = :nama");
        $db->bind(':uid', $userId);
        $db->bind(':nama', $namaBaru);
        if ($db->single()) {
            Flash::set('warning', 'Siswa "' . htmlspecialchars($namaBaru) . '" sudah ada dalam daftar.');
            $this->redirect('siswa');
        }

        $alamat = trim($_POST['alamat'] ?? '');
        $noHp   = preg_replace('/[^0-9]/', '', $_POST['no_hp'] ?? '');

        $fotoName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/foto_siswa/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                $fotoName = uniqid('foto_') . '.' . $fileExt;
                move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoName);
            } else {
                Flash::set('error', 'Format foto tidak didukung (harus JPG/PNG/GIF).');
                $this->redirect('siswa');
            }
        }

        // INSERT langsung tanpa mengelola id manual
        $db->query("INSERT INTO siswa (user_id, nama, no_hp, alamat, foto) VALUES (:uid, :nama, :no_hp, :alamat, :foto)");
        $db->bind(':uid',    $userId);
        $db->bind(':nama',   $namaBaru);
        $db->bind(':no_hp',  $noHp);
        $db->bind(':alamat', $alamat ?: null);
        $db->bind(':foto',   $fotoName);

        if ($db->execute()) {
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
        $userId  = Session::get('user_id');

        if ($idHapus <= 0) {
            Flash::set('error', 'ID siswa tidak valid.');
            $this->redirect('siswa');
        }

        $db = new Database();

        // Ambil data siswa dulu untuk nama & foto
        $db->query("SELECT nama, foto FROM siswa WHERE id = :id AND user_id = :uid");
        $db->bind(':id',  $idHapus);
        $db->bind(':uid', $userId);
        $row = $db->single();

        if (!$row) {
            Flash::set('error', 'Siswa tidak ditemukan.');
            $this->redirect('siswa');
        }

        // Hapus foto fisik jika ada
        if (!empty($row['foto'])) {
            $fotoPath = ROOT_PATH . '/public/uploads/foto_siswa/' . $row['foto'];
            if (file_exists($fotoPath)) unlink($fotoPath);
        }

        // DELETE langsung
        $db->query("DELETE FROM siswa WHERE id = :id AND user_id = :uid");
        $db->bind(':id',  $idHapus);
        $db->bind(':uid', $userId);

        if ($db->execute()) {
            Flash::set('success', 'Siswa "' . htmlspecialchars($row['nama']) . '" berhasil dihapus.');
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

        $idEdit     = (int)($_POST['id'] ?? 0);
        $namaBaru   = strtoupper(trim($_POST['nama'] ?? ''));
        $noHpBaru   = preg_replace('/[^0-9]/', '', $_POST['no_hp'] ?? '');
        $alamatBaru = trim($_POST['alamat'] ?? '');
        $userId     = Session::get('user_id');

        if ($idEdit <= 0 || empty($namaBaru)) {
            Flash::set('error', 'Data tidak valid.');
            $this->redirect('siswa');
        }

        $db = new Database();

        // Cek duplikasi nama untuk ID lain
        $db->query("SELECT id FROM siswa WHERE user_id = :uid AND nama = :nama AND id != :id");
        $db->bind(':uid',  $userId);
        $db->bind(':nama', $namaBaru);
        $db->bind(':id',   $idEdit);
        if ($db->single()) {
            Flash::set('warning', 'Siswa dengan nama "' . htmlspecialchars($namaBaru) . '" sudah ada.');
            $this->redirect('siswa');
        }

        // Ambil data lama untuk foto
        $db->query("SELECT foto FROM siswa WHERE id = :id AND user_id = :uid");
        $db->bind(':id',  $idEdit);
        $db->bind(':uid', $userId);
        $existing = $db->single();

        if (!$existing) {
            Flash::set('error', 'Siswa tidak ditemukan.');
            $this->redirect('siswa');
        }

        $fotoName = $existing['foto'];

        // Handle foto baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/foto_siswa/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                // Hapus foto lama
                if (!empty($fotoName) && file_exists($uploadDir . $fotoName)) {
                    unlink($uploadDir . $fotoName);
                }
                $fotoName = uniqid('foto_') . '.' . $fileExt;
                move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoName);
            } else {
                Flash::set('error', 'Format foto tidak didukung (harus JPG/PNG/GIF).');
                $this->redirect('siswa');
            }
        }

        // UPDATE langsung
        $db->query("UPDATE siswa SET nama = :nama, no_hp = :no_hp, alamat = :alamat, foto = :foto WHERE id = :id AND user_id = :uid");
        $db->bind(':nama',   $namaBaru);
        $db->bind(':no_hp',  $noHpBaru);
        $db->bind(':alamat', $alamatBaru ?: null);
        $db->bind(':foto',   $fotoName);
        $db->bind(':id',     $idEdit);
        $db->bind(':uid',    $userId);

        if ($db->execute()) {
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
