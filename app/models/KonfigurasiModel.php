<?php
require_once APP_PATH . '/core/Model.php';

/**
 * KonfigurasiModel.php
 * Mengelola data konfigurasi dari database (tabel pengaturan, siswa, users)
 */
class KonfigurasiModel extends Model
{
    /**
     * Ambil nama kelas
     */
    public function getKelas(): string
    {
        $this->db->query("SELECT key_value FROM pengaturan WHERE key_name = 'kelas'");
        $result = $this->db->single();
        return $result ? $result['key_value'] : 'Tidak Diketahui';
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
    public function getSiswa(): array
    {
        $this->db->query("SELECT * FROM siswa ORDER BY id ASC");
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
    public function saveSiswa(array $siswa): bool
    {
        // Cara simpel: hapus semua data lama, masukkan data baru
        // Pada MySQL, ini bisa dilakukan dalam transaksi
        try {
            $this->db->query("TRUNCATE TABLE siswa");
            $this->db->execute();

            foreach ($siswa as $s) {
                $this->db->query("INSERT INTO siswa (id, nama, no_hp, alamat, foto) VALUES (:id, :nama, :no_hp, :alamat, :foto)");
                $this->db->bind(':id', $s['id']);
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
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $this->db->query("SELECT id FROM users LIMIT 1");
        $user = $this->db->single();
        
        if ($user) {
            $this->db->query("UPDATE users SET password = :password WHERE id = :id");
            $this->db->bind(':password', $hash);
            $this->db->bind(':id', $user['id']);
            return $this->db->execute();
        } else {
            // Jika belum ada user, buat baru
            $this->db->query("INSERT INTO users (username, password) VALUES (:username, :password)");
            $this->db->bind(':username', 'admin');
            $this->db->bind(':password', $hash);
            return $this->db->execute();
        }
    }
}
