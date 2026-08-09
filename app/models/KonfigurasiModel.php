<?php
require_once APP_PATH . '/core/Model.php';

/**
 * KonfigurasiModel.php
 * Mengelola data konfigurasi dari data.json (kelas, kategori, auth)
 */
class KonfigurasiModel extends Model
{
    private string $configFile = 'data.json';

    /**
     * Ambil semua konfigurasi
     */
    public function getAll(): array
    {
        return $this->db->readRoot($this->configFile);
    }

    /**
     * Ambil nama kelas
     */
    public function getKelas(): string
    {
        $config = $this->getAll();
        return $config['kelas'] ?? 'Tidak Diketahui';
    }

    /**
     * Ambil daftar kategori presensi
     */
    public function getKategori(): array
    {
        $config = $this->getAll();
        return $config['kategori'] ?? [];
    }

    /**
     * Ambil daftar siswa
     */
    public function getSiswa(): array
    {
        $config = $this->getAll();
        return $config['data_siswa'] ?? [];
    }

    /**
     * Ambil konfigurasi auth
     */
    public function getAuth(): array
    {
        $config = $this->getAll();
        return $config['auth'] ?? ['username' => 'admin', 'password' => password_hash('wali1234', PASSWORD_BCRYPT)];
    }

    /**
     * Simpan daftar siswa baru
     */
    public function saveSiswa(array $siswa): bool
    {
        $config = $this->getAll();
        $config['data_siswa'] = array_values($siswa);
        return $this->db->writeRoot($this->configFile, $config);
    }

    /**
     * Update password admin
     */
    public function updatePassword(string $newPassword): bool
    {
        $config = $this->getAll();
        $config['auth']['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->db->writeRoot($this->configFile, $config);
    }
}
