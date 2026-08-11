<?php
require_once APP_PATH . '/core/Model.php';

/**
 * PertanyaanModel.php
 * Mengelola konfigurasi pertanyaan dinamis per wali kelas (user_id)
 */
class PertanyaanModel extends Model
{
    /**
     * Ambil semua pertanyaan milik user tertentu
     */
    public function getAll(int $userId): array
    {
        $this->db->query("SELECT * FROM pertanyaan WHERE user_id = :user_id ORDER BY urutan ASC, id ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Ambil pertanyaan yang aktif saja (untuk form siswa)
     */
    public function getActive(int $userId): array
    {
        $this->db->query("SELECT * FROM pertanyaan WHERE user_id = :user_id AND is_active = 1 ORDER BY urutan ASC, id ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Ambil detail satu pertanyaan
     */
    public function getById(int $id, int $userId): ?array
    {
        $this->db->query("SELECT * FROM pertanyaan WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ?: null;
    }

    /**
     * Tambah pertanyaan baru
     */
    public function insert(array $data): bool
    {
        $this->db->query("INSERT INTO pertanyaan (user_id, judul, tipe, opsi, urutan, is_active) 
                          VALUES (:user_id, :judul, :tipe, :opsi, :urutan, :is_active)");
        
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':judul', $data['judul']);
        $this->db->bind(':tipe', $data['tipe']); // 'pilihan_ganda' atau 'angka'
        $this->db->bind(':opsi', $data['opsi']); // JSON string
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        
        return $this->db->execute();
    }

    /**
     * Update pertanyaan
     */
    public function update(array $data): bool
    {
        $this->db->query("UPDATE pertanyaan SET 
                            judul = :judul, 
                            tipe = :tipe, 
                            opsi = :opsi, 
                            is_active = :is_active 
                          WHERE id = :id AND user_id = :user_id");
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':judul', $data['judul']);
        $this->db->bind(':tipe', $data['tipe']);
        $this->db->bind(':opsi', $data['opsi']);
        $this->db->bind(':is_active', $data['is_active']);
        
        return $this->db->execute();
    }

    /**
     * Hapus pertanyaan
     */
    public function delete(int $id, int $userId): bool
    {
        $this->db->query("DELETE FROM pertanyaan WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Update urutan
     */
    public function updateUrutan(int $id, int $urutan, int $userId): bool
    {
        $this->db->query("UPDATE pertanyaan SET urutan = :urutan WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':urutan', $urutan);
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }
}
