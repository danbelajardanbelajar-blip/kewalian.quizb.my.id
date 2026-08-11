<?php
/**
 * config.php
 * Konfigurasi Database dan Aplikasi
 */

// Konfigurasi Database MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'quic1934_wali');
define('DB_USER', 'quic1934_zenhkm');
define('DB_PASS', '03Maret1990');

// ── Google OAuth 2.0 ─────────────────────────────────────────
// Baca dari file .env (tidak di-track git, ada di server)
$_envFile = ROOT_PATH . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (str_starts_with(trim($_line), '#')) continue;
        [$_k, $_v] = array_map('trim', explode('=', $_line, 2) + [1 => '']);
        if (!empty($_k)) putenv("$_k=$_v");
    }
}
define('GOOGLE_CLIENT_ID',     getenv('GOOGLE_CLIENT_ID')     ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI',  getenv('GOOGLE_REDIRECT_URI')  ?: 'https://wali.quizb.my.id/auth/google/callback');
