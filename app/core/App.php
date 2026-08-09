<?php
/**
 * App.php — Front Controller Router
 * Menangani routing URL ke Controller yang sesuai
 */
class App
{
    protected string $controller  = 'DashboardController';
    protected string $method      = 'index';
    protected array  $params      = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // Tentukan Controller
        if (!empty($url[0])) {
            $controllerName = ucfirst(strtolower($url[0])) . 'Controller';
            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
            } else {
                $this->notFound();
                return;
            }
            unset($url[0]);
        }

        require_once APP_PATH . '/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Tentukan Method
        if (!empty($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
            } else {
                $this->notFound();
                return;
            }
            unset($url[1]);
        }

        // Kumpulkan Parameter
        $this->params = $url ? array_values($url) : [];

        // Panggil method dengan params
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parse URL dari query string
     */
    private function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
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
