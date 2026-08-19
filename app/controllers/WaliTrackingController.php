<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';
require_once APP_PATH . '/models/KunjunganModel.php';

class WaliTrackingController extends Controller
{
    private KonfigurasiModel $konfig;
    private KunjunganModel   $kunjungan;

    public function __construct()
    {
        $this->konfig    = new KonfigurasiModel();
        $this->kunjungan = new KunjunganModel();
    }

    /**
     * GET /wali_tracking — Halaman Wali Murid Tracking
     */
    public function index(): void
    {
        $this->requireAuth();

        $userId  = Session::get('user_id');
        $kelas   = $this->konfig->getKelas($userId);
        $data    = $this->kunjungan->getWalimuridTracking($userId);

        $this->view('wali_tracking/index', [
            'title'  => 'Wali Murid Tracking — Kelas ' . $kelas,
            'kelas'  => $kelas,
            'data'   => $data,
        ]);
    }
}
