<?php
require_once APP_PATH . '/core/Model.php';

/**
 * AuthModel.php
 * Mengelola autentikasi pengguna
 */
class AuthModel extends Model
{
    /**
     * Verifikasi username dan password
     */
    public function verify(string $username, string $password): bool
    {
        $this->db->query("SELECT * FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        if (!$user) {
            return false;
        }

        // Support password bcrypt dan plain text (backward compat)
        if (str_starts_with($user['password'], '$2')) {
            return password_verify($password, $user['password']);
        }

        return $password === $user['password'];
    }

    /**
     * Daftarkan user baru
     * @return int|bool Mengembalikan ID user baru jika sukses, false jika gagal
     */
    public function register(string $username, string $password, string $nama_lengkap, string $kelas): int|bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $this->db->query("INSERT INTO users (username, password, nama_lengkap, kelas) VALUES (:username, :password, :nama_lengkap, :kelas)");
            $this->db->bind(':username', $username);
            $this->db->bind(':password', $hash);
            $this->db->bind(':nama_lengkap', $nama_lengkap);
            $this->db->bind(':kelas', $kelas);
            $this->db->execute();
            return (int) $this->db->lastInsertId();
        } catch (Exception $e) {
            // Error jika username duplikat (UNIQUE)
            return false;
        }
    }

    /**
     * Login: set session
     */
    public function login(string $username): void
    {
        $this->db->query("SELECT * FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        Session::set('logged_in',  true);
        Session::set('username',   $username);
        Session::set('user_id',    $user['id'] ?? null);
        Session::set('is_admin',   !empty($user['is_admin']) && (int)$user['is_admin'] === 1);
        Session::set('login_time', time());
        if (!empty($user['google_avatar'])) {
            Session::set('google_avatar', $user['google_avatar']);
        }

        // Update last_login_at
        try {
            $this->db->query("UPDATE users SET last_login_at = NOW() WHERE id = :id");
            $this->db->bind(':id', $user['id']);
            $this->db->execute();
        } catch (Exception $e) {
            // Kolom belum ada (sebelum migrasi) — abaikan
        }
    }

    /**
     * Logout: destroy session
     */
    public function logout(): void
    {
        Session::destroy();
    }

    /**
     * Cek apakah sudah login
     */
    public function isLoggedIn(): bool
    {
        return (bool) Session::get('logged_in', false);
    }
}
