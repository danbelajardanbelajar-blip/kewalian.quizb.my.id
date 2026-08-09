<?php
/**
 * Database.php — JSON File Database Handler
 * Menangani operasi baca/tulis data dalam format JSON
 */
class Database
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = ROOT_PATH . '/storage';
    }

    /**
     * Baca file JSON, kembalikan sebagai array
     */
    public function read(string $filename): array
    {
        $file = $this->storagePath . '/' . $filename;

        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        if ($content === false || trim($content) === '') {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Tulis data ke file JSON (overwrite)
     */
    public function write(string $filename, array $data): bool
    {
        $file  = $this->storagePath . '/' . $filename;
        $dir   = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($file, $json) !== false;
    }

    /**
     * Baca file JSON dari root project (bukan storage)
     */
    public function readRoot(string $filename): array
    {
        $file = ROOT_PATH . '/' . $filename;

        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        if ($content === false || trim($content) === '') {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Tulis data ke file JSON di root project
     */
    public function writeRoot(string $filename, array $data): bool
    {
        $file = ROOT_PATH . '/' . $filename;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($file, $json) !== false;
    }

    /**
     * Hapus file JSON
     */
    public function delete(string $filename): bool
    {
        $file = $this->storagePath . '/' . $filename;
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * List semua file dalam folder storage
     */
    public function listFiles(string $folder, string $pattern = '*.json'): array
    {
        $path  = $this->storagePath . '/' . $folder;
        $files = glob($path . '/' . $pattern);

        if ($files === false) {
            return [];
        }

        return array_map('basename', $files);
    }

    /**
     * Cek apakah file storage ada
     */
    public function exists(string $filename): bool
    {
        return file_exists($this->storagePath . '/' . $filename);
    }
}
