<?php
require_once APP_PATH . "/core/Controller.php";
require_once APP_PATH . "/models/WalimuridModel.php";
require_once APP_PATH . "/models/PertanyaanModel.php";

class WalimuridController extends Controller
{
    private WalimuridModel $walimuridModel;

    public function __construct()
    {
        $this->walimuridModel = new WalimuridModel();
    }

    public function index(): void
    {
        $idStr = $_GET["id"] ?? "";
        if (!empty($idStr)) {
            $this->showLaporan((int)$idStr);
            return;
        }

        $idWali = $_GET["wali"] ?? "";
        $db = new Database();

        if (empty($idWali)) {
            // Tampilkan daftar kelas
            $db->query("SELECT id, username, nama_lengkap, kelas FROM users ORDER BY kelas ASC");
            $listWali = $db->resultSet();
            
            $this->view('walimurid/pilih_kelas', [
                'title' => 'Pilih Kelas - Laporan Wali Murid',
                'listWali' => $listWali
            ], false);
            return;
        }

        // Tampilkan daftar siswa dalam kelas tersebut
        $db->query("SELECT id, kelas FROM users WHERE id = :id");
        $db->bind(':id', $idWali);
        $userWali = $db->single();

        if (!$userWali) {
            die("Wali kelas tidak ditemukan. Silakan cek kembali link Anda.");
        }

        $db->query("SELECT id, nama, no_hp, foto FROM siswa WHERE user_id = :user_id ORDER BY nama ASC");
        $db->bind(':user_id', $userWali['id']);
        $siswa = $db->resultSet();

        $this->view('walimurid/pilih_siswa', [
            'title' => 'Pilih Siswa - Kelas ' . htmlspecialchars($userWali['kelas']),
            'kelas' => $userWali['kelas'],
            'siswa' => $siswa,
            'idWali' => $idWali
        ], false);
    }

    private function showLaporan(int $id): void
    {
        $siswa = $this->walimuridModel->getSiswaById($id);
        if (!$siswa) {
            die("Data siswa tidak ditemukan.");
        }

        // Cek apakah sudah login sebagai wali murid
        $sessionKey = "walimurid_logged_in_" . $id;
        if (!Session::get($sessionKey)) {
            $this->view("walimurid/login", [
                "title" => "Login Wali Murid - " . htmlspecialchars($siswa["nama"]),
                "siswa" => $siswa
            ], false);
            return;
        }

        // Kalau sudah login, tampilkan laporan
        $progress = $this->walimuridModel->getProgress($id);
        
        $rankMingguan = $this->walimuridModel->getRanking($siswa["user_id"], 7);
        $rankBulanan  = $this->walimuridModel->getRanking($siswa["user_id"], 30);
        $rankTahunan  = $this->walimuridModel->getRanking($siswa["user_id"], 365);
        $rankSemua    = $this->walimuridModel->getRanking($siswa["user_id"]);
        
        $myRank = [
            'mingguan' => $rankMingguan[$id] ?? ["rating" => 0, "total_poin" => 0, "avg_kelas" => 0],
            'bulanan'  => $rankBulanan[$id] ?? ["rating" => 0, "total_poin" => 0, "avg_kelas" => 0],
            'tahunan'  => $rankTahunan[$id] ?? ["rating" => 0, "total_poin" => 0, "avg_kelas" => 0],
            'semua'    => $rankSemua[$id] ?? ["rating" => 0, "total_poin" => 0, "avg_kelas" => 0]
        ];

        $riwayatDetail = $this->walimuridModel->getRiwayatDetail($id);
        
        $this->view("walimurid/laporan", [
            "title" => "Laporan Siswa - " . htmlspecialchars($siswa["nama"]),
            "siswa" => $siswa,
            "progress" => $progress,
            "myRank" => $myRank,
            "riwayatDetail" => $riwayatDetail
        ], false);
    }

    public function verify(): void
    {
        if (!$this->isPost()) {
            $this->redirect("");
        }

        $id = (int)($_POST["id"] ?? 0);
        $noHp = $_POST["no_hp"] ?? "";

        if ($id <= 0 || empty($noHp)) {
            Flash::set("error", "Harap masukkan nomor WhatsApp yang terdaftar.");
            $this->redirect("walimurid?id=" . $id);
        }

        if ($this->walimuridModel->verifyNoHp($id, $noHp)) {
            Session::set("walimurid_logged_in_" . $id, true);
            $this->redirect("walimurid?id=" . $id);
        } else {
            Flash::set("error", "Nomor WhatsApp tidak cocok dengan data kami.");
            $this->redirect("walimurid?id=" . $id);
        }
    }

    public function logout(): void
    {
        $id = (int)($_GET["id"] ?? 0);
        Session::set("walimurid_logged_in_" . $id, false);
        $this->redirect("walimurid?id=" . $id);
    }
}

