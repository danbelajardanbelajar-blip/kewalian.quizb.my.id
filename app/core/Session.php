<?php
/**
 * Session.php — Session Helper
 */
class Session
{
    /**
     * Start session jika belum dimulai
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set nilai session
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get nilai session
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Hapus satu key session
     */
    public static function unset(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy seluruh session (logout)
     */
    public static function destroy(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Cek apakah key session ada
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }
}
