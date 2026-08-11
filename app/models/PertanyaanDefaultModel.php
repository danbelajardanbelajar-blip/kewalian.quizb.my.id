<?php
require_once APP_PATH . '/core/Model.php';

class PertanyaanDefaultModel extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT * FROM pertanyaan_default ORDER BY urutan ASC, id ASC");
        return $this->db->resultSet();
    }

    public function getById(int $id): ?array
    {
        $this->db->query("SELECT * FROM pertanyaan_default WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result ?: null;
    }

    public function insert(array $data): bool
    {
        $this->db->query("INSERT INTO pertanyaan_default (judul, tipe, opsi, urutan, is_active) VALUES (:judul, :tipe, :opsi, :urutan, :is_active)");
        $this->db->bind(':judul', $data['judul']);
        $this->db->bind(':tipe', $data['tipe']);
        $this->db->bind(':opsi', $data['opsi']);
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        return $this->db->execute();
    }

    public function update(array $data): bool
    {
        $this->db->query("UPDATE pertanyaan_default SET judul = :judul, tipe = :tipe, opsi = :opsi, is_active = :is_active WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':judul', $data['judul']);
        $this->db->bind(':tipe', $data['tipe']);
        $this->db->bind(':opsi', $data['opsi']);
        $this->db->bind(':is_active', $data['is_active']);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM pertanyaan_default WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function toggleActive(int $id): bool
    {
        $this->db->query("UPDATE pertanyaan_default SET is_active = NOT is_active WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function updateUrutan(int $id, int $urutan): bool
    {
        $this->db->query("UPDATE pertanyaan_default SET urutan = :urutan WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':urutan', $urutan);
        return $this->db->execute();
    }

    // Get only active ones (for use in createDefaultPertanyaan)
    public function getActive(): array
    {
        $this->db->query("SELECT * FROM pertanyaan_default WHERE is_active = 1 ORDER BY urutan ASC, id ASC");
        return $this->db->resultSet();
    }
}
