<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AuthModel.php';

/**
 * AuthController.php
 * Mengelola Login, Logout, Register, dan Google OAuth
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
            Flash::set('error', 'Username sudah digunakan. Coba username lain.');
            $this->redirect('auth/daftar');
        }
    }

    /**
     * GET /auth/logout — Proses logout
     */
    public function logout(): void
    {
        $this->authModel->logout();
        Flash::set('success', 'Anda telah berhasil logout.');
        $this->redirect('auth/login');
    }

    /**
     * POST /auth/kembaliAdmin — Kembali ke akun Admin setelah masukSebagai
     */
    public function kembaliAdmin(): void
    {
        $adminId = Session::get('impersonator_id');
        if (!$adminId) {
            $this->redirect('dashboard');
        }

        $db = new Database();
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $adminId);
        $adminUser = $db->single();

        if ($adminUser) {
            Session::set('logged_in', true);
            Session::set('username', $adminUser['username']);
            Session::set('user_id', $adminUser['id']);
            Session::set('nama_lengkap', $adminUser['nama_lengkap'] ?? $adminUser['username']);
            Session::set('is_admin', !empty($adminUser['is_admin']) && (int)$adminUser['is_admin'] === 1);
            if (!empty($adminUser['google_avatar'])) {
                Session::set('google_avatar', $adminUser['google_avatar']);
            } else {
                Session::set('google_avatar', null);
            }
            Session::set('impersonator_id', null);
            
            Flash::set('success', 'Berhasil kembali ke akun Admin.');
            $this->redirect('admin/users');
        } else {
            $this->authModel->logout();
            $this->redirect('auth/login');
        }
    }

    // ── Google OAuth ─────────────────────────────────────────

    /**
     * GET /auth/google — Redirect ke halaman login Google
     */
    public function google(): void
    {
        if ($this->authModel->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $googleAuth = new GoogleAuth();
        $authUrl    = $googleAuth->getAuthUrl();

        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * GET /auth/google/callback — Proses callback dari Google
     */
    public function callback(): void
    {
        // Tangani error dari Google (user klik cancel, dll)
        if (isset($_GET['error'])) {
            Flash::set('error', 'Login Google dibatalkan: ' . htmlspecialchars($_GET['error']));
            $this->redirect('auth/login');
        }

        $code  = $_GET['code']  ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code)) {
            Flash::set('error', 'Kode otorisasi tidak ditemukan. Silakan coba lagi.');
            $this->redirect('auth/login');
        }

        $googleAuth = new GoogleAuth();

        // Validasi CSRF state
        if (!$googleAuth->validateState($state)) {
            Flash::set('error', 'Permintaan tidak valid (state mismatch). Silakan coba lagi.');
            $this->redirect('auth/login');
        }

        // Tukar code dengan access token
        $tokenData = $googleAuth->getAccessToken($code);
        if (!$tokenData) {
            Flash::set('error', 'Gagal mendapatkan token dari Google. Silakan coba lagi.');
            $this->redirect('auth/login');
        }

        // Ambil info user dari Google
        $userInfo = $googleAuth->getUserInfo($tokenData['access_token']);
        if (!$userInfo) {
            Flash::set('error', 'Gagal mengambil profil dari Google. Silakan coba lagi.');
            $this->redirect('auth/login');
        }

        $googleId    = $userInfo['sub'];
        $googleEmail = $userInfo['email'] ?? '';
        $googleName  = $userInfo['name'] ?? '';
        $googleAvatar= $userInfo['picture'] ?? '';

        // Cari user berdasarkan google_id
        $db = new Database();
        $db->query("SELECT * FROM users WHERE google_id = :google_id LIMIT 1");
        $db->bind(':google_id', $googleId);
        $user = $db->single();

        if ($user) {
            // ── Sudah pernah login Google → langsung masuk ──
            $this->loginByUser($user);
            Flash::set('success', 'Selamat datang kembali, ' . htmlspecialchars($user['nama_lengkap'] ?? $user['username']) . '!');
            $this->redirect('dashboard');
        }

        // Coba cari berdasarkan email (akun manual yang pakai email sama)
        if (!empty($googleEmail)) {
            $db->query("SELECT * FROM users WHERE google_email = :email LIMIT 1");
            $db->bind(':email', $googleEmail);
            $existUser = $db->single();

            if ($existUser) {
                // Hubungkan akun dengan google_id
                $db->query("UPDATE users SET google_id = :gid, google_avatar = :avatar WHERE id = :id");
                $db->bind(':gid',    $googleId);
                $db->bind(':avatar', $googleAvatar);
                $db->bind(':id',     $existUser['id']);
                $db->execute();

                $this->loginByUser($existUser);
                Flash::set('success', 'Akun berhasil dihubungkan dengan Google. Selamat datang!');
                $this->redirect('dashboard');
            }
        }

        // ── Belum ada akun → simpan ke session dan minta isi data kelas ──
        Session::set('google_pending', [
            'google_id'    => $googleId,
            'email'        => $googleEmail,
            'name'         => $googleName,
            'avatar'       => $googleAvatar,
        ]);

        $this->redirect('auth/google/setup');
    }

    /**
     * GET /auth/google/setup — Form isi data kelas (untuk akun Google baru)
     */
    public function setup(): void
    {
        $pending = Session::get('google_pending');
        if (empty($pending)) {
            $this->redirect('auth/login');
        }

        $this->view('auth/google_setup', [
            'title'   => 'Lengkapi Profil — Dashboard Wali Kelas',
            'pending' => $pending,
        ], false);
    }

    /**
     * POST /auth/google/simpan — Simpan akun Google baru
     */
    public function simpan(): void
    {
        if (!$this->isPost()) {
            $this->redirect('auth/login');
        }

        $pending = Session::get('google_pending');
        if (empty($pending)) {
            Flash::set('error', 'Sesi tidak valid. Silakan login ulang.');
            $this->redirect('auth/login');
        }

        $username = trim($_POST['username'] ?? '');
        $kelas    = trim($_POST['kelas'] ?? '');

        if (empty($username) || empty($kelas)) {
            Flash::set('error', 'Username dan nama kelas wajib diisi.');
            $this->redirect('auth/google/setup');
        }

        // Cek username unik
        $db = new Database();
        $db->query("SELECT id FROM users WHERE username = :username");
        $db->bind(':username', $username);
        if ($db->single()) {
            Flash::set('error', 'Username sudah digunakan. Pilih username lain.');
            $this->redirect('auth/google/setup');
        }

        // Insert user baru
        $db->query("INSERT INTO users (username, password, nama_lengkap, kelas, google_id, google_email, google_avatar)
                    VALUES (:username, NULL, :nama, :kelas, :gid, :gemail, :gavatar)");
        $db->bind(':username', $username);
        $db->bind(':nama',     $pending['name']);
        $db->bind(':kelas',    $kelas);
        $db->bind(':gid',      $pending['google_id']);
        $db->bind(':gemail',   $pending['email']);
        $db->bind(':gavatar',  $pending['avatar']);
        $db->execute();

        $newUserId = (int) $db->lastInsertId();

        // Buat pertanyaan default
        $this->pertanyaanModel->createDefaultPertanyaan($newUserId);

        // Ambil user baru untuk login
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $newUserId);
        $newUser = $db->single();

        Session::set('google_pending', null);
        $this->loginByUser($newUser);

        Flash::set('success', 'Akun berhasil dibuat! Selamat datang, ' . htmlspecialchars($pending['name']) . '!');
        $this->redirect('dashboard');
    }

    // ── Private Helper ────────────────────────────────────────

    /**
     * Set session login dari data user array
     */
    private function loginByUser(array $user): void
    {
        Session::set('logged_in',    true);
        Session::set('username',     $user['username']);
        Session::set('user_id',      $user['id']);
        Session::set('nama_lengkap', $user['nama_lengkap'] ?? $user['username']);
        Session::set('is_admin',     !empty($user['is_admin']) && (int)$user['is_admin'] === 1);
        Session::set('login_time',   time());
        // Simpan avatar Google jika ada
        if (!empty($user['google_avatar'])) {
            Session::set('google_avatar', $user['google_avatar']);
        }
        // Update last_login_at
        try {
            $db = new Database();
            $db->query("UPDATE users SET last_login_at = NOW() WHERE id = :id");
            $db->bind(':id', $user['id']);
            $db->execute();
        } catch (Exception $e) {}
    }
}
