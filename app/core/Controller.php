<?php
/**
 * Controller.php — Base Controller
 * Semua controller mewarisi class ini
 */
class Controller
{
    /**
     * Load sebuah model
     */
    protected function model(string $model): object
    {
        require_once APP_PATH . '/models/' . $model . '.php';
        return new $model();
    }

    /**
     * Render sebuah view dengan data
     * 
     * @param string $view   Path view relatif (misal: 'dashboard/index')
     * @param array  $data   Data yang di-extract ke view
     * @param bool   $layout Apakah menggunakan layout (header/footer)
     */
    protected function view(string $view, array $data = [], bool $layout = true): void
    {
        // Extract data supaya bisa dipakai langsung di view
        extract($data);

        if ($layout) {
            require_once APP_PATH . '/views/layouts/header.php';
        }

        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo '<div class="alert alert-danger">View tidak ditemukan: ' . htmlspecialchars($view) . '</div>';
        }

        if ($layout) {
            require_once APP_PATH . '/views/layouts/footer.php';
        }
    }

    /**
     * Redirect ke URL lain
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }

    /**
     * Cek apakah request adalah POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Sanitasi input
     */
    protected function sanitize(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(strip_tags(trim((string) $input)));
    }

    /**
     * Cek apakah user sudah login; jika belum, redirect ke login
     */
    protected function requireAuth(): void
    {
        if (!Session::get('logged_in')) {
            Flash::set('error', 'Silakan login terlebih dahulu.');
            $this->redirect('auth/login');
        }
    }

    /**
     * Kirim response JSON
     */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
