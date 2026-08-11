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

        $alamat = trim($_POST['alamat'] ?? '');
        $noHp = preg_replace('/[^0-9]/', '', $_POST['no_hp'] ?? '');
        
        $fotoName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/foto_siswa/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileTmp = $_FILES['foto']['tmp_name'];
            $fileName = $_FILES['foto']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExt, $allowedExt)) {
                $fotoName = uniqid('foto_') . '.' . $fileExt;
                move_uploaded_file($fileTmp, $uploadDir . $fotoName);
            } else {
                Flash::set('error', 'Format foto tidak didukung (harus JPG/PNG/GIF).');
                $this->redirect('siswa');
            }
        }

        $maxId = array_reduce($siswa, fn($max, $s) => max($max, $s['id']), 0);
        $siswa[] = [
            'id' => $maxId + 1,
            'nama' => $namaBaru,
            'no_hp' => $noHp,
            'alamat' => $alamat,
            'foto' => $fotoName
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

        $siswa = array_filter($siswa, function($s) use ($idHapus) {
            if ($s['id'] === $idHapus) {
                if (!empty($s['foto'])) {
                    $uploadDir = ROOT_PATH . '/public/uploads/foto_siswa/';
                    if (file_exists($uploadDir . $s['foto'])) {
                        unlink($uploadDir . $s['foto']);
                    }
                }
                return false;
            }
            return true;
        });

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
        $alamatBaru = trim($_POST['alamat'] ?? '');

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
                $s['alamat'] = $alamatBaru;
                
                // Handle foto update
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = ROOT_PATH . '/public/uploads/foto_siswa/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileTmp = $_FILES['foto']['tmp_name'];
                    $fileName = $_FILES['foto']['name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($fileExt, $allowedExt)) {
                        // Hapus foto lama jika ada
                        if (!empty($s['foto']) && file_exists($uploadDir . $s['foto'])) {
                            unlink($uploadDir . $s['foto']);
                        }
                        
                        $fotoName = uniqid('foto_') . '.' . $fileExt;
                        if (move_uploaded_file($fileTmp, $uploadDir . $fotoName)) {
                            $s['foto'] = $fotoName;
                        }
                    } else {
                        Flash::set('error', 'Format foto tidak didukung (harus JPG/PNG/GIF).');
                        $this->redirect('siswa');
                    }
                }
                
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
