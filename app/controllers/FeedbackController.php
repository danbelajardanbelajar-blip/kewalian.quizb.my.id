<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/FeedbackModel.php';

class FeedbackController extends Controller
{
    private FeedbackModel $feedbackModel;

    public function __construct()
    {
        $this->feedbackModel = new FeedbackModel();
    }

    /**
     * GET /feedback — Halaman form feedback (Bisa diakses siswa/umum tanpa login)
     */
    public function index(): void
    {
        $this->view('feedback/index', [
            'title' => 'Kirim Feedback'
        ]);
    }

    /**
     * POST /feedback/submit — Proses penyimpanan feedback
     */
    public function submit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama   = trim($_POST['nama'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $pesan  = trim($_POST['pesan'] ?? '');
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

            if (empty($nama) || empty($pesan)) {
                Flash::set('danger', 'Nama dan Pesan wajib diisi!');
                $this->redirect('feedback');
                return;
            }

            // Jika yang mengirim adalah wali kelas yang sedang login
            $userId = Session::get('user_id') ?: null;

            $data = [
                'user_id' => $userId,
                'nama'    => $nama,
                'email'   => $email,
                'pesan'   => $pesan,
                'rating'  => $rating
            ];

            if ($this->feedbackModel->insert($data)) {
                Flash::set('success', 'Terima kasih atas masukan dan feedback Anda!');
            } else {
                Flash::set('danger', 'Terjadi kesalahan saat mengirim feedback. Silakan coba lagi.');
            }
        }
        
        $this->redirect('feedback');
    }
}
