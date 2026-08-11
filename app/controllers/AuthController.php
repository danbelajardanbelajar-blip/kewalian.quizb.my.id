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
    private PertanyaanModel $pertanyaanModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        
        require_once APP_PATH . '/models/PertanyaanModel.php';
        $this->pertanyaanModel = new PertanyaanModel();
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
     * GET /auth/daftar — Tampilkan halaman registrasi
     */
    public function daftar(): void
    {
        if ($this->authModel->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/daftar', ['title' => 'Daftar — Dashboard Wali Kelas'], false);
    }

    /**
     * POST /auth/register — Proses pendaftaran wali kelas baru
     */
    public function register(): void
    {
        if (!$this->isPost()) {
            $this->redirect('auth/daftar');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');

        if (empty($username) || empty($password) || empty($nama_lengkap) || empty($kelas)) {
            Flash::set('error', 'Semua field wajib diisi.');
            $this->redirect('auth/daftar');
        }

        $newUserId = $this->authModel->register($username, $password, $nama_lengkap, $kelas);
        if ($newUserId !== false) {
            // Buat pertanyaan default untuk wali kelas baru ini
            $this->pertanyaanModel->createDefaultPertanyaan($newUserId);
            
            Flash::set('success', 'Pendaftaran berhasil! Silakan login.');
            $this->redirect('auth/login');
        } else {
            Flash::set('error', 'Username sudah terdaftar atau terjadi kesalahan. Silakan coba lagi.');
            $this->redirect('auth/daftar');
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
