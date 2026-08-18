<?php
require_once APP_PATH . '/core/Model.php';

/**
 * KonfigurasiModel.php
 * Mengelola data konfigurasi dari database (tabel pengaturan, siswa, users)
 */
class KonfigurasiModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        try {
            $this->db->query("ALTER TABLE siswa ADD COLUMN kode_akses VARCHAR(50) DEFAULT NULL AFTER no_hp");
            $this->db->execute();
        } catch (Exception $e) {}
    }
    /**
     * Ambil nama kelas
     */
    public function getKelas(int $userId = null): string
    {
        $userId = $userId ?? Session::get('user_id');
        if (!$userId) return 'Tidak Diketahui';
        
        $this->db->query("SELECT kelas FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);
        $result = $this->db->single();
        return $result ? $result['kelas'] : 'Tidak Diketahui';
    }

    /**
     * Ambil daftar kategori presensi
     */
    public function getKategori(): array
    {
        $this->db->query("SELECT key_value FROM pengaturan WHERE key_name = 'kategori'");
        $result = $this->db->single();
        if ($result && !empty($result['key_value'])) {
            return json_decode($result['key_value'], true) ?: [];
        }
        return [];
    }

    /**
     * Ambil daftar siswa
     */
    public function getSiswa(int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        if (!$userId) return [];
        
        $this->db->query("SELECT * FROM siswa WHERE user_id = :user_id ORDER BY id ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Ambil konfigurasi auth
     */
    public function getAuth(): array
    {
        // Secara default mengambil user pertama (atau admin)
        $this->db->query("SELECT username, password FROM users LIMIT 1");
        $result = $this->db->single();
        if ($result) {
            return $result;
        }
        return ['username' => 'admin', 'password' => password_hash('wali1234', PASSWORD_BCRYPT)];
    }

    /**
     * Simpan daftar siswa (sinkronisasi)
     */
    public function saveSiswa(array $siswa, int $userId = null): bool
    {
        $userId = $userId ?? Session::get('user_id');
        if (!$userId) return false;
        
        try {
            // Hapus hanya siswa milik user ini
            $this->db->query("DELETE FROM siswa WHERE user_id = :user_id");
            $this->db->bind(':user_id', $userId);
            $this->db->execute();

            foreach ($siswa as $s) {
                $this->db->query("INSERT INTO siswa (id, user_id, nama, no_hp, alamat, foto) VALUES (:id, :user_id, :nama, :no_hp, :alamat, :foto)");
                $this->db->bind(':id', $s['id']);
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':nama', $s['nama']);
                $this->db->bind(':no_hp', $s['no_hp'] ?? '');
                $this->db->bind(':alamat', $s['alamat'] ?? null);
                $this->db->bind(':foto', $s['foto'] ?? null);
                $this->db->execute();
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update password admin
     */
    public function updatePassword(string $newPassword): bool
    {
        $userId = Session::get('user_id');
        if (!$userId) return false;
        
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $this->db->query("UPDATE users SET password = :password WHERE id = :id");
        $this->db->bind(':password', $hash);
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }
}
