<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AuthModel.php';

/**
 * AuthController.php
 * Mengelola Login & Logout
 */
class AuthController extends Controller
{
    private AuthModel $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    /**
     * GET /auth/login — Tampilkan halaman login
     */
    public function login(): void
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->authModel->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login', ['title' => 'Login — Dashboard Wali Kelas'], false);
    }

    /**
     * POST /auth/login — Proses form login
     */
    public function proses(): void
    {
        if (!$this->isPost()) {
            $this->redirect('auth/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input kosong
        if (empty($username) || empty($password)) {
            Flash::set('error', 'Username dan password tidak boleh kosong.');
            $this->redirect('auth/login');
        }

        // Verifikasi kredensial
        if ($this->authModel->verify($username, $password)) {
            $this->authModel->login($username);
            Flash::set('success', 'Selamat datang, ' . htmlspecialchars($username) . '!');
            $this->redirect('dashboard');
        } else {
            Flash::set('error', 'Username atau password salah. Silakan coba lagi.');
            $this->redirect('auth/login');
        }
    }

    /**
     * GET /auth/logout — Logout
     */
    public function logout(): void
    {
        $this->authModel->logout();
        Flash::set('info', 'Anda telah berhasil logout.');
        $this->redirect('auth/login');
    }
}
