<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AbsenModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/PertanyaanModel.php';
require_once APP_PATH . '/models/KunjunganModel.php';
require_once APP_PATH . '/models/WalimuridModel.php';
require_once APP_PATH . '/models/WaTemplateModel.php';
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
        
        // Blokir form jika hari Jumat (5)
        if (date('N', strtotime($tanggal)) == 5) {
            $this->view('absen/libur', [], false);
            return;
        }

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

        // Validate access code authorization
        if (empty($_SESSION['auth_siswa_' . $id]) || $_SESSION['auth_siswa_' . $id] !== true) {
            Flash::set('error', 'Akses ditolak! Anda belum memasukkan kode akses.');
            $this->redirect('absen?tanggal=' . urlencode($tanggal) . '&wali=' . urlencode($idWali));
        }

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

        // Blokir form jika hari Jumat (5)
        $dayOfWeek = date('N', strtotime($tanggal));
        if ($dayOfWeek == 5) {
            $this->view('absen/libur', ['namaSiswa' => $nama], false);
            return;
        }

        // Ambil data existing jika sudah pernah isi
        $existing = $this->absenModel->getSiswaByTanggal($tanggal, $id, $userId);
        
        // Ambil daftar pertanyaan aktif untuk kelas ini
        $pertanyaan = $this->pertanyaanModel->getActive($userId);

        // Ubah kata 'kemarin' menjadi 'hari Kamis' secara live khusus di hari Sabtu (6)
        $isSabtu = ($dayOfWeek == 6);
        if ($isSabtu) {
            foreach ($pertanyaan as &$p) {
                // Gunakan str_ireplace agar case-insensitive (kemarin, Kemarin, KEMARIN)
                $p['judul'] = str_ireplace('kemarin', 'hari Kamis', $p['judul']);
                if (!empty($p['opsi'])) {
                    $p['opsi'] = str_ireplace('kemarin', 'hari Kamis', $p['opsi']);
                }
            }
            unset($p); // break reference
        }
        
        // Ambil setting acak
        $settings = $this->pertanyaanModel->getUserSettings($userId);
        
        // --- PEER REVIEW LOGIC ---
        require_once APP_PATH . '/models/PeerReviewModel.php';
        $peerModel = new PeerReviewModel();
        
        // 1. Ambil soal aktif, lalu acak dan ambil maksimal 2 (KECUALI JIKA MODE EDIT)
        $peerSoalAktif = $peerModel->getActivePertanyaan($userId);
        $peerPertanyaanTampil = [];
        if (!empty($peerSoalAktif) && empty($existing)) {
            shuffle($peerSoalAktif);
            $peerPertanyaanTampil = array_slice($peerSoalAktif, 0, 2);
        }
        
        // TEMPORARY LOGGING
        $logData = date('Y-m-d H:i:s') . " - userId: $userId, idSiswa: $id, existing: " . (empty($existing) ? 'EMPTY' : 'NOT_EMPTY') . ", peerSoalAktif count: " . count($peerSoalAktif) . "\n";
        file_put_contents(ROOT_PATH . '/public/debug_peer.txt', $logData, FILE_APPEND);
        
        // --- TEMP LOGGING ---
        file_put_contents('debug_peer.txt', print_r([
            'userId' => $userId,
            'existing_is_empty' => empty($existing),
            'peerSoalAktif_count' => count($peerSoalAktif),
            'peerPertanyaanTampil_count' => count($peerPertanyaanTampil)
        ], true));
        // --------------------
        
        // 2. Ambil data siswa yang satu jenis kelamin dengan penjawab (kecuali dirinya sendiri)
        $jkPenjawab = 'L'; // default
        foreach ($siswa as $s) {
            if ($s['id'] === $id) {
                $jkPenjawab = $s['jenis_kelamin'] ?? 'L';
                break;
            }
        }
        
        $temanSebaya = [];
        foreach ($siswa as $s) {
            // Abaikan diri sendiri dan beda jenis kelamin
            if ($s['id'] !== $id && ($s['jenis_kelamin'] ?? 'L') === $jkPenjawab) {
                $temanSebaya[] = $s;
            }
        }
        // ------------------------
        
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
            'idWali' => $idWali,
            'isSabtu' => $isSabtu,
            'peerPertanyaan' => $peerPertanyaanTampil,
            'temanSebaya' => $temanSebaya
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
                $namaSiswa = ucwords(strtolower($siswaRow['nama']));
                $aksi = $isEdit ? "diperbarui" : "disubmit";
                
                $waModel = new WaTemplateModel();
                $template = $waModel->getTemplate((int)$userId);
                
                if (empty($template)) {
                    $template = "Salam Bapak/Ibu, Semoga senantiasa diberi kesehatan.\nIzin menghaturkan rekap kegiatan ananda *{nama_siswa}* selama sehari di tanggal *{tanggal}*.\n\n*--- RINCIAN LAPORAN ---*\n\n{rincian}\n*Rating Harian:* {rating}/5\n\nUntuk melihat statistik dan riwayat lengkap ananda, silakan klik tautan berikut:\n{link_laporan}";
                }
                
                $walimuridModel = new WalimuridModel();
                $riwayat = $walimuridModel->getRiwayatDetail($id);
                $hariIni = $riwayat[$tanggal] ?? null;
                
                $rincian = "";
                if ($hariIni && !empty($hariIni['detail'])) {
                    foreach ($hariIni['detail'] as $det) {
                        if (!empty($det['label_singkat'])) {
                            $qText = trim($det['label_singkat']);
                        } else {
                            // Fallback: Simplify question text like in laporan, make it Proper Case
                            $qText = strtolower(trim($det['pertanyaan']));
                            $qText = str_replace(['apakah ', 'kemarin ', 'pagi ', 'sore ', 'malam ', 'siang ', 'hari ini', 'tadi ', 'ananda ', '{{nama}}', 'berapa ', 'kapan ', 'sudahkah ', 'telahkah ', 'tolong '], '', $qText);
                            $qText = str_replace(['?', ':', '!', '.', ','], '', $qText);
                            $qText = trim(preg_replace('/\s+/', ' ', $qText));
                            $qText = ucwords($qText);
                        }
                        
                        $rincian .= "*" . $qText . "*\n";
                        $rincian .= $det['jawaban'] . "\n";
                        if (!empty($det['keterangan'])) {
                            $rincian .= "_Catatan: " . $det['keterangan'] . "_\n";
                        }
                        $rincian .= "\n";
                    }
                }
                
                $dayOfWeek = date('N', strtotime($tanggal));
                if ($dayOfWeek == 6) {
                    $rincian = "_Catatan: Laporan yang dikirim pada hari Sabtu ini adalah rincian kegiatan ananda pada hari Kamis._\n\n" . $rincian;
                }
                
                $dbSiswaInfo = new Database();
                $dbSiswaInfo->query("SELECT kode_akses_wali FROM siswa WHERE id = :id");
                $dbSiswaInfo->bind(':id', $id);
                $siswaInfo = $dbSiswaInfo->single();
                
                $link = "https://wali.quizb.my.id/walimurid?id=" . $id;
                $kodeAuth = '';
                if ($siswaInfo) {
                    if (empty($siswaInfo['kode_akses_wali'])) {
                        $kodeAuth = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
                        $dbUpdate = new Database();
                        $dbUpdate->query("UPDATE siswa SET kode_akses_wali = :kode WHERE id = :id");
                        $dbUpdate->bind(':kode', $kodeAuth);
                        $dbUpdate->bind(':id', $id);
                        $dbUpdate->execute();
                    } else {
                        $kodeAuth = $siswaInfo['kode_akses_wali'];
                    }
                }
                if (!empty($kodeAuth)) {
                    $link .= "&auth=" . urlencode($kodeAuth);
                }
                
                $tanggalIndo = date('d F Y', strtotime($tanggal));
                
                $pesan = str_replace(
                    ['{nama_siswa}', '{tanggal}', '{rincian}', '{rating}', '{link_laporan}'],
                    [$namaSiswa, $tanggalIndo, trim($rincian), ($hariIni['rating'] ?? 0), $link],
                    $template
                );
                
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
                    "x-api-key: wa-key-1e0a672693117e4d09db166e49979691"
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
     * GET /absen/sorotan — Sorotan dan Kejanggalan
     */
    public function sorotan(): void
    {
        $this->requireAuth();

        $userId = Session::get('user_id');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $siswa   = $this->konfig->getSiswa($userId);
        $kelas   = $this->konfig->getKelas($userId);

        $dataTanggal = $this->absenModel->getByTanggal($tanggal, $userId);
        $allDates    = $this->absenModel->getAllDates($userId);
        $pertanyaan  = $this->pertanyaanModel->getActive($userId);
        $idWali      = Session::get('user_id');

        $this->view('absen/sorotan', [
            'title'       => 'Sorotan & Kejanggalan — ' . date('d F Y', strtotime($tanggal)),
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'dataTanggal' => $dataTanggal,
            'siswa'       => $siswa,
            'allDates'    => $allDates,
            'pertanyaan'  => $pertanyaan,
            'idWali'      => $idWali,
        ]);
    }

    /**
     * GET /absen/sorotan_mingguan — Sorotan Mingguan
     */
    public function sorotan_mingguan(): void
    {
        $this->requireAuth();

        $userId = Session::get('user_id');
        $siswa   = $this->konfig->getSiswa($userId);
        $kelas   = $this->konfig->getKelas($userId);
        $pertanyaan = $this->pertanyaanModel->getActive($userId);
        
        $week = $_GET['week'] ?? date('Y-\WW'); 
        
        $dates = [];
        $dto = new DateTime();
        $dto->setISODate(substr($week, 0, 4), substr($week, 6, 2));
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $dto->format('Y-m-d');
            $dto->modify('+1 day');
        }

        $weeklyData = [];
        foreach ($dates as $d) {
            $weeklyData[$d] = $this->absenModel->getByTanggal($d, $userId);
        }
        
        $startDate = date('Y-m-d', strtotime($dates[0] . ' -1 day')); // satu hari sebelum Senin
        $endDate   = $dates[6];
        $lateMap   = $this->absenModel->getLateSubmissionsByDate($startDate, $endDate, $userId);

        $this->view('absen/sorotan_mingguan', [
            'title'       => 'Sorotan Mingguan — ' . $week,
            'week'        => $week,
            'dates'       => $dates,
            'kelas'       => $kelas,
            'siswa'       => $siswa,
            'weeklyData'  => $weeklyData,
            'lateMap'     => $lateMap,
            'pertanyaan'  => $pertanyaan,
        ]);
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

    /**
     * POST /absen/set_kode
     */
    public function set_kode(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $id = (int)($_POST['siswa_id'] ?? 0);
        $kode = trim($_POST['kode_akses'] ?? '');

        if ($id <= 0 || strlen($kode) < 3 || preg_match('/\d/', $kode)) {
            $this->json(['success' => false, 'message' => 'Kode akses minimal 3 huruf dan tidak boleh mengandung angka.']);
            return;
        }

        // Update to DB
        $db = new Database();
        $db->query("UPDATE siswa SET kode_akses = :kode WHERE id = :id");
        $db->bind(':kode', strtoupper($kode));
        $db->bind(':id', $id);
        
        if ($db->execute()) {
            $_SESSION['auth_siswa_' . $id] = true;
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan kode akses.']);
        }
    }

    /**
     * POST /absen/verify_kode
     */
    public function verify_kode(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $id = (int)($_POST['siswa_id'] ?? 0);
        $kode = trim($_POST['kode_akses'] ?? '');

        if ($id <= 0 || empty($kode)) {
            $this->json(['success' => false, 'message' => 'Data tidak valid.']);
            return;
        }

        // Check DB
        $db = new Database();
        $db->query("SELECT kode_akses FROM siswa WHERE id = :id");
        $db->bind(':id', $id);
        $row = $db->single();

        if ($row && strtoupper($row['kode_akses']) === strtoupper($kode)) {
            $_SESSION['auth_siswa_' . $id] = true;
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Kode akses salah.']);
        }
    }

    /**
     * POST /absen/kirim_wa_manual
     * Mengirim pesan WA secara manual via API dari halaman rekap
     */
    public function kirim_wa_manual(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int)($input['id_siswa'] ?? 0);
        $tanggal = trim($input['tanggal'] ?? '');
        $userId = Session::get('user_id');

        if ($id <= 0 || empty($tanggal)) {
            $this->json(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        // Get student data
        $dbSiswa = new Database();
        $dbSiswa->query("SELECT nama, no_hp FROM siswa WHERE id = :id AND user_id = :user_id");
        $dbSiswa->bind(':id', $id);
        $dbSiswa->bind(':user_id', $userId);
        $siswaRow = $dbSiswa->single();

        if (!$siswaRow || empty($siswaRow['no_hp'])) {
            $this->json(['success' => false, 'message' => 'Siswa tidak ditemukan atau nomor HP kosong']);
            return;
        }

        $noHp = $siswaRow['no_hp'];
        $namaSiswa = ucwords(strtolower($siswaRow['nama']));

        require_once APP_PATH . '/models/WaTemplateModel.php';
        require_once APP_PATH . '/models/WalimuridModel.php';

        $waModel = new WaTemplateModel();
        $template = $waModel->getTemplate((int)$userId);
        
        if (empty($template)) {
            $template = "Salam Bapak/Ibu, Semoga senantiasa diberi kesehatan.\nIzin menghaturkan rekap kegiatan ananda *{nama_siswa}* selama sehari di tanggal *{tanggal}*.\n\n*--- RINCIAN LAPORAN ---*\n\n{rincian}\n*Rating Harian:* {rating}/5\n\nUntuk melihat statistik dan riwayat lengkap ananda, silakan klik tautan berikut:\n{link_laporan}";
        }
        
        $walimuridModel = new WalimuridModel();
        $riwayat = $walimuridModel->getRiwayatDetail($id);
        $hariIni = $riwayat[$tanggal] ?? null;

        if (!$hariIni) {
            $this->json(['success' => false, 'message' => 'Data absen pada tanggal tersebut tidak ditemukan']);
            return;
        }
        
        $rincian = "";
        if (!empty($hariIni['detail'])) {
            foreach ($hariIni['detail'] as $det) {
                if (!empty($det['label_singkat'])) {
                    $qText = trim($det['label_singkat']);
                } else {
                    $qText = strtolower(trim($det['pertanyaan']));
                    $qText = str_replace(['apakah ', 'kemarin ', 'pagi ', 'sore ', 'malam ', 'siang ', 'hari ini', 'tadi ', 'ananda ', '{{nama}}', 'berapa ', 'kapan ', 'sudahkah ', 'telahkah ', 'tolong '], '', $qText);
                    $qText = str_replace(['?', ':', '!', '.', ','], '', $qText);
                    $qText = trim(preg_replace('/\s+/', ' ', $qText));
                    $qText = ucwords($qText);
                }
                
                $rincian .= "*" . $qText . "*\n";
                $rincian .= $det['jawaban'] . "\n";
                if (!empty($det['keterangan'])) {
                    $rincian .= "_Catatan: " . $det['keterangan'] . "_\n";
                }
                $rincian .= "\n";
            }
        }
        
        $dayOfWeek = date('N', strtotime($tanggal));
        if ($dayOfWeek == 6) {
            $rincian = "_Catatan: Laporan yang dikirim pada hari Sabtu ini adalah rincian kegiatan ananda pada hari Kamis._\n\n" . $rincian;
        }

        $link = "https://wali.quizb.my.id/walimurid?id=" . $id;
        $dbSiswaInfo = new Database();
        $dbSiswaInfo->query("SELECT kode_akses_wali FROM siswa WHERE id = :id");
        $dbSiswaInfo->bind(':id', $id);
        $siswaInfo = $dbSiswaInfo->single();
        $kodeAuth = '';
        if ($siswaInfo) {
            if (empty($siswaInfo['kode_akses_wali'])) {
                $kodeAuth = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
                $dbUpdate = new Database();
                $dbUpdate->query("UPDATE siswa SET kode_akses_wali = :kode WHERE id = :id");
                $dbUpdate->bind(':kode', $kodeAuth);
                $dbUpdate->bind(':id', $id);
                $dbUpdate->execute();
            } else {
                $kodeAuth = $siswaInfo['kode_akses_wali'];
            }
        }
        if (!empty($kodeAuth)) {
            $link .= "&auth=" . urlencode($kodeAuth);
        }
        
        $tanggalIndo = date('d F Y', strtotime($tanggal));
        
        $pesan = str_replace(
            ['{nama_siswa}', '{tanggal}', '{rincian}', '{rating}', '{link_laporan}'],
            [$namaSiswa, $tanggalIndo, trim($rincian), ($hariIni['rating'] ?? 0), $link],
            $template
        );

        $waData = [
            'phone_number' => $noHp,
            'message' => $pesan,
            'scheduled_time' => date('Y-m-d\TH:i', strtotime('+5 seconds'))
        ];
        
        $ch = curl_init("https://wa.quizb.my.id/api/send.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($waData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-api-key: wa-key-1e0a672693117e4d09db166e49979691"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->json(['success' => true, 'message' => 'Pesan berhasil dikirim via API']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal mengirim pesan via API']);
        }
    }

    /**
     * GET /absen/rekap_asrama
     */
    public function rekap_asrama(): void
    {
        $this->requireAuth();

        $userId = Session::get('user_id');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $siswa   = $this->konfig->getSiswa($userId);
        $kelas   = $this->konfig->getKelas($userId);

        $dataTanggal = $this->absenModel->getByTanggal($tanggal, $userId);
        $allDates    = $this->absenModel->getAllDates($userId);
        
        require_once APP_PATH . '/models/AsramaModel.php';
        $asramaModel = new AsramaModel();
        $listPengurus = $asramaModel->getAll($userId);
        
        $pengurusMap = [];
        foreach ($listPengurus as $p) {
            $pengurusMap[$p['nama_asrama']] = $p;
        }

        $this->view('absen/rekap_asrama', [
            'title'       => 'Rekap Per Asrama - ' . date('d F Y', strtotime($tanggal)),
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'dataTanggal' => $dataTanggal,
            'siswa'       => $siswa,
            'allDates'    => $allDates,
            'pengurusMap' => $pengurusMap
        ]);
    }

    /**
     * POST /absen/kirim_wa_asrama
     */
    public function kirim_wa_asrama(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $json = json_decode(file_get_contents('php://input'), true);
        $tanggal = trim($json['tanggal'] ?? '');
        $namaAsrama = trim($json['nama_asrama'] ?? '');
        
        if (empty($tanggal) || empty($namaAsrama)) {
            $this->json(['success' => false, 'message' => 'Data tidak lengkap.']);
            return;
        }

        $userId = Session::get('user_id');
        
        require_once APP_PATH . '/models/AsramaModel.php';
        $asramaModel = new AsramaModel();
        $pengurus = $asramaModel->getByName($userId, $namaAsrama);
        
        if (!$pengurus || empty($pengurus['no_hp'])) {
            $this->json(['success' => false, 'message' => 'Pengurus atau nomor HP belum diatur.']);
            return;
        }

        $dataTanggal = $this->absenModel->getByTanggal($tanggal, $userId);
        $siswa = $this->konfig->getSiswa($userId);
        
        $siswaAsrama = [];
        foreach ($siswa as $s) {
            if (trim($s['asrama'] ?? '') === $namaAsrama) {
                $siswaAsrama[] = $s;
            }
        }
        
        if (empty($siswaAsrama)) {
            $this->json(['success' => false, 'message' => 'Tidak ada siswa di asrama ini.']);
            return;
        }

        $tglIndo = date('d F Y', strtotime($tanggal));
        
        // Filter siswa yang belum mengisi
        $siswaBelumIsi = [];
        foreach ($siswaAsrama as $s) {
            if (!isset($dataTanggal['siswa'][$s['id']])) {
                $siswaBelumIsi[] = $s['nama'];
            }
        }
        
        if (empty($siswaBelumIsi)) {
            $this->json(['success' => false, 'message' => 'Semua anak di asrama ini sudah mengisi laporan!']);
            return;
        }

        $msg = "*LAPORAN KEDISIPLINAN ASRAMA*\n";
        $msg .= "Asrama: *{$namaAsrama}*\n";
        $msg .= "Pengurus: *{$pengurus['nama_pengurus']}*\n";
        $msg .= "Tanggal: *{$tglIndo}*\n\n";
        $msg .= "Anak-anak berikut belum mengisi laporan kedisiplinan:\n";
        
        $no = 1;
        foreach ($siswaBelumIsi as $nama) {
            $msg .= "{$no}. {$nama}\n";
            $no++;
        }
        
        $link = BASE_URL . "/absen?wali=" . $userId;
        $msg .= "\nMohon meminjamkan hp ke anak-anak di atas untuk mengisi laporan di link ini:\n{$link}\n";
        
        $msg .= "\n_Pesan otomatis dari Sistem Kedisiplinan Santri._";

        $apiKey = 'wa-key-1e0a672693117e4d09db166e49979691';
        $url    = 'https://wa.quizb.my.id/api/send.php';
        $dataWa = [
            'phone_number'   => $pengurus['no_hp'],
            'message'        => $msg,
            'scheduled_time' => date('Y-m-d\TH:i', strtotime('+5 seconds'))
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataWa));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->json(['success' => true, 'message' => 'Pesan berhasil dikirim via API']);
        } else {
            $this->json(['success' => false, 'message' => "Gagal mengirim pesan via API (Kode: $httpCode). " . htmlspecialchars($response)]);
        }
    }

}

