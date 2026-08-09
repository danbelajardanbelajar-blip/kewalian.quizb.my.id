<?php
/**
 * App.php — Front Controller Router
 * Menangani routing URL ke Controller yang sesuai
 */
class App
{
    // Nama controller (string) dan method (string) disimpan terpisah dari object-nya
    protected string $controllerName = 'DashboardController';
    protected string $method         = 'index';
    protected array  $params         = [];

    // Object controller yang aktif (tidak di-type-hint agar fleksibel)
    protected $controllerObj = null;

    public function __construct()
    {
        $url = $this->parseUrl();

        // Tentukan Controller dari URL segment pertama
        if (!empty($url[0])) {
            $name = ucfirst(strtolower($url[0])) . 'Controller';
            $file = APP_PATH . '/controllers/' . $name . '.php';

            if (file_exists($file)) {
                $this->controllerName = $name;
            } else {
                $this->notFound();
                return;
            }
            unset($url[0]);
        }

        // Load dan instantiate controller
        require_once APP_PATH . '/controllers/' . $this->controllerName . '.php';
        $this->controllerObj = new $this->controllerName();

        // Tentukan Method dari URL segment kedua
        if (!empty($url[1])) {
            if (method_exists($this->controllerObj, $url[1])) {
                $this->method = $url[1];
            } else {
                $this->notFound();
                return;
            }
            unset($url[1]);
        }

        // Kumpulkan Parameter (sisa URL segments)
        $this->params = $url ? array_values($url) : [];

        // Panggil method dengan params
        call_user_func_array([$this->controllerObj, $this->method], $this->params);
    }

    /**
     * Parse URL dari query string
     */
    private function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            // Gunakan urldecode agar %20 dan + sama-sama jadi spasi,
            // lalu hapus hanya karakter path traversal yang berbahaya.
            // JANGAN pakai FILTER_SANITIZE_URL — itu menghapus spasi
            // sehingga nama siswa dengan spasi akan rusak.
            $url = rtrim($_GET['url'], '/');
            // Cegah path traversal
            $url = str_replace(['..', '\\'], '', $url);
            return explode('/', $url);
        }
        return [];
    }

    /**
     * Halaman 404 Not Found
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo '<div style="text-align:center;padding:50px;font-family:sans-serif;">';
        echo '<h1>404</h1><p>Halaman tidak ditemukan.</p>';
        echo '<a href="' . BASE_URL . '">Kembali ke Beranda</a>';
        echo '</div>';
    }
}
