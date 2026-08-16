<?php
require_once APP_PATH . "/core/Controller.php";
require_once APP_PATH . "/models/WaTemplateModel.php";

class WatemplateController extends Controller
{
    private WaTemplateModel $waModel;

    public function __construct()
    {
        $this->waModel = new WaTemplateModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = Session::get("user_id");
        $template = $this->waModel->getTemplate($userId);
        
        $defaultTemplate = "Salam Bapak/Ibu, Semoga senantiasa diberi kesehatan.\nIzin menghaturkan rekap kegiatan ananda *{nama_siswa}* selama sehari di tanggal *{tanggal}*.\n\n*--- RINCIAN LAPORAN ---*\n\n{rincian}\n*Rating Harian:* {rating}/5\n\nUntuk melihat statistik dan riwayat lengkap ananda, silakan klik tautan berikut:\n{link_laporan}";

        if (empty($template)) {
            $template = $defaultTemplate;
        }

        $this->view("wa_template/index", [
            "title" => "Pengaturan Template WA",
            "template" => $template,
            "defaultTemplate" => $defaultTemplate
        ]);
    }

    public function simpan(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->redirect("watemplate");
        }

        $userId = Session::get("user_id");
        $template = trim($_POST["template"] ?? "");

        if (empty($template)) {
            Flash::set("error", "Template pesan tidak boleh kosong.");
            $this->redirect("watemplate");
        }

        if ($this->waModel->saveTemplate($userId, $template)) {
            Flash::set("success", "Template pesan WhatsApp berhasil disimpan.");
        } else {
            Flash::set("error", "Gagal menyimpan template pesan.");
        }
        
        $this->redirect("watemplate");
    }
}

