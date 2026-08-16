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
        if (empty($idStr)) {
            die("ID Siswa tidak valid.");
        }
        $id = (int)$idStr;

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
        $rankings = $this->walimuridModel->getRanking($siswa["user_id"]);
        $myRank = $rankings[$id] ?? ["rank" => "-", "total_poin" => 0];
        
        $this->view("walimurid/laporan", [
            "title" => "Laporan Siswa - " . htmlspecialchars($siswa["nama"]),
            "siswa" => $siswa,
            "progress" => $progress,
            "rankings" => $rankings,
            "myRank" => $myRank
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

