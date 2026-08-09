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
        require_once APP_PATH . '/models/KonfigurasiModel.php';
        $konfig = new KonfigurasiModel();
        $auth   = $konfig->getAuth();

        if ($username !== $auth['username']) {
            return false;
        }

        // Support password bcrypt dan plain text (backward compat)
        if (str_starts_with($auth['password'], '$2')) {
            return password_verify($password, $auth['password']);
        }

        return $password === $auth['password'];
    }

    /**
     * Login: set session
     */
    public function login(string $username): void
    {
        Session::set('logged_in', true);
        Session::set('username', $username);
        Session::set('login_time', time());
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
