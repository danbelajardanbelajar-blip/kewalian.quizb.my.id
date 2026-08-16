<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AbsenModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/PertanyaanModel.php';
require_once APP_PATH . '/models/KunjunganModel.php';
/**
 * AbsenController.php
 * Absen Mandiri Siswa — TIDAK memerlukan login
 * Siswa mengisi sendiri data kehadiran & kegiatan harian
 */
class AbsenController extends Controller
{
    private AbsenModel     $absenModel;
    private KonfigurasiModel $konfig;
    private PertanyaanModel  $pertanyaanModel;

    public function __construct()
    {
        $this->absenModel = new AbsenModel();
        $this->konfig     = new KonfigurasiModel();
        $this->pertanyaanModel = new PertanyaanModel();
    }

    /**
     * GET /absen — Halaman pilih nama siswa
     */
    public function index(): void
    {
        $idWali = $_GET['wali'] ?? '';

        $db = new Database();
        if (empty($idWali)) {
            $db->query("SELECT id, username, nama_lengkap, kelas FROM users ORDER BY kelas ASC");
            $listWali = $db->resultSet();
            
            $this->view('absen/pilih_wali', [
                'title' => 'Pilih Kelas',
                'listWali' => $listWali
            ], false);
            return;
        }

        $db->query("SELECT id, kelas FROM users WHERE id = :id");
        $db->bind(':id', $idWali);
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
            'idWali' => $idWali
        ], false);
    }

    /**
     * GET /absen/isi/{id} — Form pertanyaan untuk siswa
     */
    public function isi(string $idStr = ''): void
    {
        $idWali = $_GET['wali'] ?? '';
        if (empty($idStr) || empty($idWali)) {
            $this->redirect('absen');
        }

        $id      = (int)$idStr;
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        
        $db = new Database();
        $db->query("SELECT id, kelas FROM users WHERE id = :idWali");
        $db->bind(':idWali', $idWali);
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
            $this->redirect('absen?wali=' . $idWali);
        }

        $nama = '';
        foreach ($siswa as $s) {
            if ($s['id'] === $id) $nama = $s['nama'];
        }

        // Ambil data existing jika sudah pernah isi
        $existing = $this->absenModel->getSiswaByTanggal($tanggal, $id, $userId);
        
        // Ambil daftar pertanyaan aktif untuk kelas ini
        $pertanyaan = $this->pertanyaanModel->getActive($userId);
        
        // Ambil setting acak
        $settings = $this->pertanyaanModel->getUserSettings($userId);
        
        // Acak pertanyaan jika diset
        if (!empty($settings['acak_pertanyaan'])) {
            shuffle($pertanyaan);
        }

        $this->view('absen/form', [
            'title'    => 'Absen Harian — ' . htmlspecialchars($nama),
            'id'       => $id,
            'nama'     => $nama,
            'tanggal'  => $tanggal,
            'kelas'    => $kelas,
            'existing' => $existing,
            'isEdit'   => !empty($existing),
            'pertanyaan' => $pertanyaan,
            'settings' => $settings,
            'idWali' => $idWali
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

        $idWali = $_POST['idWali'] ?? '';
        
        $db = new Database();
        $db->query("SELECT id FROM users WHERE id = :id");
        $db->bind(':id', $idWali);
        $userWali = $db->single();
        $userId = $userWali ? $userWali['id'] : null;

        if (!$userWali) {
            Flash::set('error', 'Wali kelas tidak valid.');
            $this->redirect('absen?wali=' . $idWali);
        }

        // Bangun data absen dinamis
        $pertanyaanAktif = $this->pertanyaanModel->getActive($userId);
        $data = [];
        
        $postJawaban = $_POST['jawaban'] ?? [];
        $postAngka = $_POST['jawaban_angka'] ?? [];
        $postKet = $_POST['keterangan'] ?? [];

        foreach ($pertanyaanAktif as $p) {
            $pId = $p['id'];
            $ans = $postJawaban[$pId] ?? '';
            $ket = $postKet[$pId] ?? '';
            $poin_didapat = 0;
            
            $opsi = json_decode($p['opsi'], true);
            if ($p['tipe'] === 'pilihan_ganda') {
                foreach ($opsi as $op) {
                    if ($op['value'] === $ans) {
                        $poin_didapat = (int)$op['poin'];
                        break;
                    }
                }
            } else if ($p['tipe'] === 'angka') {
                $nilaiAngka = (float)$ans;
                $poin_per_angka = (float)($opsi['poin_per_angka'] ?? 1);
                $poin_didapat = (int)($nilaiAngka * $poin_per_angka);
            } else if ($p['tipe'] === 'ganda_dan_angka') {
                $nilaiAngkaInput = (float)($postAngka[$pId] ?? 0);
                
                // Cari opsi pilihan
                $basePoin = 0;
                $reqAngka = false;
                if (isset($opsi['pilihan'])) {
                    foreach ($opsi['pilihan'] as $op) {
                        if ($op['value'] === $ans) {
                            $basePoin = (int)$op['poin'];
                            $reqAngka = !empty($op['require_angka']);
                            break;
                        }
                    }
                }
                
                $poinAngka = 0;
                if ($reqAngka) {
                    $poin_per_angka = (float)($opsi['angka']['poin_per_angka'] ?? 1);
                    $poinAngka = (int)($nilaiAngkaInput * $poin_per_angka);
                    $ans = $ans . ':' . $nilaiAngkaInput; // simpan format "value:number"
                } else {
                    $ans = $ans . ':0'; // default kalau tidak req angka
                }
                
                $poin_didapat = $basePoin + $poinAngka;
            }
            
            $data[$pId] = [
                'jawaban' => $ans,
                'keterangan' => $ket,
                'poin' => $poin_didapat
            ];
        }

        $isEdit = $this->absenModel->sudahIsi($tanggal, $id);

        if ($this->absenModel->simpanSiswa($tanggal, $id, $data)) {
            // Mengirim notifikasi WA ke orang tua
            $dbSiswa = new Database();
            $dbSiswa->query("SELECT nama, no_hp FROM siswa WHERE id = :id");
            $dbSiswa->bind(':id', $id);
            $siswaRow = $dbSiswa->single();
            
            if ($siswaRow && !empty($siswaRow['no_hp'])) {
                $noHp = $siswaRow['no_hp'];
                $namaSiswa = $siswaRow['nama'];
                $aksi = $isEdit ? "diperbarui" : "disubmit";
                
                $pesan = "Halo Bapak/Ibu, laporan kegiatan dan absen untuk ananda *" . $namaSiswa . "* pada tanggal *" . date('d F Y', strtotime($tanggal)) . "* telah berhasil " . $aksi . ". Terima kasih.";
                
                $waData = [
                    'phone_number' => $noHp,
                    'message' => $pesan,
                    'scheduled_time' => date('Y-m-d\TH:i', strtotime('+1 minute'))
                ];
                
                $ch = curl_init("https://wa.quizb.my.id/api/send.php");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($waData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "x-api-key: wa-key-923332d62d67d2511393e0c6d8ff5e59"
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
                curl_exec($ch);
                curl_close($ch);
            }

            $this->redirect('absen/selesai?id=' . $id . '&nama=' . rawurlencode($nama) . '&tanggal=' . $tanggal . '&edit=' . ($isEdit ? '1' : '0') . '&wali=' . $idWali);
        } else {
            Flash::set('error', 'Gagal menyimpan. Silakan coba lagi.');
            $this->redirect('absen/isi/' . $id . '?tanggal=' . $tanggal . '&wali=' . $idWali);
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
        $idWali  = $_GET['wali'] ?? '';
        
        $db = new Database();
        $db->query("SELECT kelas FROM users WHERE id = :id");
        $db->bind(':id', $idWali);
        $userWali = $db->single();
        $kelas = $userWali ? $userWali['kelas'] : '';

        $this->view('absen/selesai', [
            'title'   => 'Absen Tersimpan!',
            'id'      => $id,
            'nama'    => $nama,
            'tanggal' => $tanggal,
            'isEdit'  => $isEdit,
            'kelas'   => $kelas,
            'idWali' => $idWali
        ], false);
    }

    /**
     * GET /absen/rekap — Rekap absen mandiri (perlu login wali)
     */
    public function rekap(): void
    {
        $this->requireAuth();

        $userId = Session::get('user_id');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $siswa   = $this->konfig->getSiswa($userId);
        $kelas   = $this->konfig->getKelas($userId);

        $dataTanggal = $this->absenModel->getByTanggal($tanggal, $userId);
        $statistik   = $this->absenModel->getStatistik($tanggal, $siswa, $userId);
        $allDates    = $this->absenModel->getAllDates($userId);
        $pertanyaan  = $this->pertanyaanModel->getActive($userId);
        $idWali      = Session::get('user_id');

        $this->view('absen/rekap', [
            'title'       => 'Rekap Absen Mandiri — ' . date('d F Y', strtotime($tanggal)),
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'dataTanggal' => $dataTanggal,
            'siswa'       => $siswa,
            'statistik'   => $statistik,
            'allDates'    => $allDates,
            'pertanyaan'  => $pertanyaan,
            'idWali'      => $idWali,
        ]);
    }

    /**
     * DELETE LAMA buildAbsenData
     */

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
