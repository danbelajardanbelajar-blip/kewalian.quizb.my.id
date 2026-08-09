<?php
/**
 * index.php — Front Controller
 * Entry point tunggal seluruh aplikasi Dashboard Wali Kelas (MVC)
 */

// ── Konstanta Path ──────────────────────────────────────────
define('ROOT_PATH', __DIR__);
define('APP_PATH',  __DIR__ . '/app');

// ── Deteksi BASE_URL secara otomatis ────────────────────────
// Mendukung root domain maupun subfolder
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($script, '/');

define('BASE_PATH', $basePath);
define('BASE_URL',  $protocol . '://' . $host . $basePath);

// ── Error Reporting (matikan di produksi) ──────────────────
// Ganti E_ALL menjadi 0 di produksi untuk keamanan
if (defined('APP_ENV') && APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ── Autoload Core Classes ───────────────────────────────────
require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Flash.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/App.php';

// ── Mulai Session ──────────────────────────────────────────
Session::start();

// ── Jalankan Aplikasi ───────────────────────────────────────
new App();