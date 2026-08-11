<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AdminModel.php';
require_once APP_PATH . '/models/FeedbackModel.php';
require_once APP_PATH . '/models/KunjunganModel.php';
require_once APP_PATH . '/models/PertanyaanDefaultModel.php';

/**
 * AdminController.php
 * Panel Admin — hanya untuk user dengan is_admin = 1
 * Routes: /admin, /admin/users, /admin/soal, /admin/kunjungan, /admin/feedback
 *         /admin/editUser/{id}, /admin/updateUser, /admin/hapusUser
 *         /admin/toggleAdmin, /admin/resetPassword
 *         /admin/simpanSoal, /admin/updateSoal, /admin/hapusSoal, /admin/toggleAktifSoal
 *         /admin/bacaFeedback, /admin/hapusFeedback, /admin/bacaSemuaFeedback
 */
class AdminController extends Controller
{
    private AdminModel $adminModel;
    private FeedbackModel $feedbackModel;
    private KunjunganModel $kunjunganModel;
    private PertanyaanDefaultModel $soalDefaultModel;

    public function __construct()
    {
        $this->adminModel       = new AdminModel();
        $this->feedbackModel    = new FeedbackModel();
        $this->kunjunganModel   = new KunjunganModel();
        $this->soalDefaultModel = new PertanyaanDefaultModel();
    }

    // ── Auth Guard ────────────────────────────────────────────

    private function requireAdmin(): void
    {
        if (!Session::get('logged_in')) {
            Flash::set('error', 'Silakan login terlebih dahulu.');
            $this->redirect('auth/login');
        }

        if (!Session::get('is_admin')) {
            Flash::set('error', 'Akses ditolak. Halaman ini hanya untuk administrator.');
            $this->redirect('');
        }
    }

    // ── Helper: update last_login_at on dashboard load ────────

    private function getUnreadCount(): int
    {
        return $this->feedbackModel->getUnreadCount();
    }

    // ── Dashboard ─────────────────────────────────────────────

    /**
     * GET /admin
     */
    public function index(): void
    {
        $this->requireAdmin();

        $stats       = $this->adminModel->getStats();
        $chartData   = $this->adminModel->getKunjunganPerDay(7);
        $recentUsers = $this->adminModel->getRecentUsers(5);
        $unreadFeedback = $this->getUnreadCount();

        $this->view('admin/dashboard', [
            'adminTitle'    => 'Dashboard Admin',
            'adminSubtitle' => 'Ringkasan sistem Wali Kelas',
            'adminActivePage' => 'dashboard',
            'stats'         => $stats,
            'chartData'     => $chartData,
            'recentUsers'   => $recentUsers,
            'unreadFeedback'=> $unreadFeedback,
        ], false);
    }

    // ── Users ─────────────────────────────────────────────────

    /**
     * GET /admin/users
     */
    public function users(): void
    {
        $this->requireAdmin();

        $users       = $this->adminModel->getAllUsers();
        $unreadFeedback = $this->getUnreadCount();

        $this->view('admin/users', [
            'adminTitle'    => 'Kelola User',
            'adminSubtitle' => 'Manajemen akun wali kelas',
            'adminActivePage' => 'users',
            'users'         => $users,
            'unreadFeedback'=> $unreadFeedback,
        ], false);
    }

    /**
     * GET /admin/editUser/{id}
     */
    public function editUser(string $idStr = ''): void
    {
        $this->requireAdmin();

        $id   = (int)$idStr;
        $user = $this->adminModel->getUserById($id);

        if (!$user) {
            Flash::set('error', 'User tidak ditemukan.');
            $this->redirect('admin/users');
        }

        $unreadFeedback = $this->getUnreadCount();

        $this->view('admin/user_edit', [
            'adminTitle'    => 'Edit User',
            'adminSubtitle' => 'Ubah data akun wali kelas',
            'adminActivePage' => 'users',
            'user'          => $user,
            'unreadFeedback'=> $unreadFeedback,
        ], false);
    }

    /**
     * POST /admin/updateUser
     */
    public function updateUser(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/users');

        $id          = (int)($_POST['id'] ?? 0);
        $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
        $kelas       = trim($_POST['kelas'] ?? '');
        $isAdmin     = isset($_POST['is_admin']) ? 1 : 0;

        if (!$id || empty($namaLengkap) || empty($kelas)) {
            Flash::set('error', 'Data tidak lengkap.');
            $this->redirect('admin/editUser/' . $id);
        }

        $ok = $this->adminModel->updateUser($id, [
            'nama_lengkap' => $namaLengkap,
            'kelas'        => $kelas,
            'is_admin'     => $isAdmin,
        ]);

        if ($ok) {
            Flash::set('success', 'Data user berhasil diperbarui.');
        } else {
            Flash::set('error', 'Gagal memperbarui data user.');
        }

        $this->redirect('admin/users');
    }

    /**
     * POST /admin/hapusUser
     */
    public function hapusUser(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/users');

        $id        = (int)($_POST['id'] ?? 0);
        $myId      = (int)Session::get('user_id');

        if ($id === $myId) {
            Flash::set('error', 'Tidak dapat menghapus akun sendiri.');
            $this->redirect('admin/users');
        }

        $ok = $this->adminModel->deleteUser($id);
        Flash::set($ok ? 'success' : 'error', $ok ? 'User berhasil dihapus.' : 'Gagal menghapus user.');
        $this->redirect('admin/users');
    }

    /**
     * POST /admin/toggleAdmin
     */
    public function toggleAdmin(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/users');

        $id   = (int)($_POST['id'] ?? 0);
        $myId = (int)Session::get('user_id');

        if ($id === $myId) {
            Flash::set('error', 'Tidak dapat mengubah status admin diri sendiri.');
            $this->redirect('admin/users');
        }

        $ok = $this->adminModel->toggleAdmin($id);
        Flash::set($ok ? 'success' : 'error', $ok ? 'Status admin berhasil diubah.' : 'Gagal mengubah status admin.');
        $this->redirect('admin/users');
    }

    /**
     * POST /admin/resetPassword
     */
    public function resetPassword(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/users');

        $id          = (int)($_POST['id'] ?? 0);
        $newPassword = trim($_POST['new_password'] ?? '');

        if (!$id || strlen($newPassword) < 6) {
            Flash::set('error', 'Password baru minimal 6 karakter.');
            $this->redirect('admin/users');
        }

        $ok = $this->adminModel->resetPassword($id, $newPassword);
        Flash::set($ok ? 'success' : 'error', $ok ? 'Password berhasil direset.' : 'Gagal mereset password.');
        $this->redirect('admin/users');
    }

    // ── Soal Default ──────────────────────────────────────────

    /**
     * GET /admin/soal
     */
    public function soal(): void
    {
        $this->requireAdmin();

        $soalList       = $this->soalDefaultModel->getAll();
        $unreadFeedback = $this->getUnreadCount();

        $this->view('admin/soal_default', [
            'adminTitle'    => 'Soal Default',
            'adminSubtitle' => 'Pertanyaan master untuk user baru',
            'adminActivePage' => 'soal',
            'soalList'      => $soalList,
            'unreadFeedback'=> $unreadFeedback,
        ], false);
    }

    /**
     * POST /admin/simpanSoal
     */
    public function simpanSoal(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/soal');

        $judul  = trim($_POST['judul'] ?? '');
        $tipe   = trim($_POST['tipe'] ?? 'pilihan_ganda');
        $opsi   = trim($_POST['opsi_json'] ?? '[]');
        $urutan = (int)($_POST['urutan'] ?? 0);

        if (empty($judul)) {
            Flash::set('error', 'Judul soal tidak boleh kosong.');
            $this->redirect('admin/soal');
        }

        // Validasi JSON
        json_decode($opsi);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Flash::set('error', 'Format JSON opsi tidak valid: ' . json_last_error_msg());
            $this->redirect('admin/soal');
        }

        $ok = $this->soalDefaultModel->insert([
            'judul'  => $judul,
            'tipe'   => $tipe,
            'opsi'   => $opsi,
            'urutan' => $urutan,
        ]);

        Flash::set($ok ? 'success' : 'error', $ok ? 'Soal berhasil ditambahkan.' : 'Gagal menambah soal.');
        $this->redirect('admin/soal');
    }

    /**
     * POST /admin/updateSoal
     */
    public function updateSoal(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/soal');

        $id     = (int)($_POST['id'] ?? 0);
        $judul  = trim($_POST['judul'] ?? '');
        $tipe   = trim($_POST['tipe'] ?? 'pilihan_ganda');
        $opsi   = trim($_POST['opsi_json'] ?? '[]');
        $urutan = (int)($_POST['urutan'] ?? 0);

        if (!$id || empty($judul)) {
            Flash::set('error', 'Data tidak lengkap.');
            $this->redirect('admin/soal');
        }

        json_decode($opsi);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Flash::set('error', 'Format JSON opsi tidak valid.');
            $this->redirect('admin/soal');
        }

        $ok = $this->soalDefaultModel->update([
            'id'     => $id,
            'judul'  => $judul,
            'tipe'   => $tipe,
            'opsi'   => $opsi,
            'urutan' => $urutan,
        ]);

        Flash::set($ok ? 'success' : 'error', $ok ? 'Soal berhasil diperbarui.' : 'Gagal memperbarui soal.');
        $this->redirect('admin/soal');
    }

    /**
     * POST /admin/hapusSoal
     */
    public function hapusSoal(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/soal');

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->soalDefaultModel->delete($id);
        Flash::set($ok ? 'success' : 'error', $ok ? 'Soal berhasil dihapus.' : 'Gagal menghapus soal.');
        $this->redirect('admin/soal');
    }

    /**
     * POST /admin/toggleAktifSoal
     */
    public function toggleAktifSoal(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/soal');

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->soalDefaultModel->toggleActive($id);
        Flash::set($ok ? 'success' : 'error', $ok ? 'Status soal berhasil diubah.' : 'Gagal mengubah status soal.');
        $this->redirect('admin/soal');
    }

    // ── Kunjungan ─────────────────────────────────────────────

    /**
     * GET /admin/kunjungan
     */
    public function kunjungan(): void
    {
        $this->requireAdmin();

        $page           = (int)($_GET['page'] ?? 1);
        $kunjunganList  = $this->adminModel->getKunjungan($page, 50);
        $perHalaman     = $this->adminModel->getKunjunganPerHalaman();
        $chartData7     = $this->adminModel->getKunjunganPerDay(7);
        $chartData30    = $this->adminModel->getKunjunganPerDay(30);
        $totalKunjungan = $this->adminModel->getKunjunganCount();
        $unreadFeedback = $this->getUnreadCount();

        $this->view('admin/kunjungan', [
            'adminTitle'    => 'Kunjungan & Tracking',
            'adminSubtitle' => 'Monitoring trafik halaman absen publik',
            'adminActivePage' => 'kunjungan',
            'kunjunganList' => $kunjunganList,
            'perHalaman'    => $perHalaman,
            'chartData7'    => $chartData7,
            'chartData30'   => $chartData30,
            'totalKunjungan'=> $totalKunjungan,
            'unreadFeedback'=> $unreadFeedback,
            'page'          => $page,
        ], false);
    }

    // ── Feedback ──────────────────────────────────────────────

    /**
     * GET /admin/feedback
     */
    public function feedback(): void
    {
        $this->requireAdmin();

        $filter    = $_GET['filter'] ?? 'all';
        $unreadOnly = $filter === 'unread';
        $feedbacks  = $this->feedbackModel->getAll($unreadOnly);
        $avgRating  = $this->feedbackModel->getAvgRating();
        $unreadCount= $this->feedbackModel->getUnreadCount();

        $this->view('admin/feedback', [
            'adminTitle'    => 'Kelola Feedback',
            'adminSubtitle' => 'Masukan dari wali kelas',
            'adminActivePage' => 'feedback',
            'feedbacks'     => $feedbacks,
            'avgRating'     => $avgRating,
            'unreadCount'   => $unreadCount,
            'unreadFeedback'=> $unreadCount,
            'filter'        => $filter,
        ], false);
    }

    /**
     * POST /admin/bacaFeedback
     */
    public function bacaFeedback(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/feedback');

        $id = (int)($_POST['id'] ?? 0);
        $this->feedbackModel->markRead($id);
        $this->redirect('admin/feedback');
    }

    /**
     * POST /admin/hapusFeedback
     */
    public function hapusFeedback(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/feedback');

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->feedbackModel->delete($id);
        Flash::set($ok ? 'success' : 'error', $ok ? 'Feedback dihapus.' : 'Gagal menghapus feedback.');
        $this->redirect('admin/feedback');
    }

    /**
     * POST /admin/bacaSemuaFeedback
     */
    public function bacaSemuaFeedback(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/feedback');

        $this->feedbackModel->markAllRead();
        Flash::set('success', 'Semua feedback ditandai sudah dibaca.');
        $this->redirect('admin/feedback');
    }
}
