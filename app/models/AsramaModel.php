<?php
require_once APP_PATH . '/core/Model.php';

class AsramaModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS asrama_pengurus (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                nama_asrama VARCHAR(100) NOT NULL,
                nama_pengurus VARCHAR(150),
                no_hp VARCHAR(50)
            )");
            $this->db->execute();
        } catch (Exception $e) {}
    }

    public function getAll(int $userId): array
    {
        $this->db->query("SELECT * FROM asrama_pengurus WHERE user_id = :uid ORDER BY nama_asrama ASC");
        $this->db->bind(':uid', $userId);
        return $this->db->resultSet();
    }
    
    public function getByName(int $userId, string $namaAsrama)
    {
        $this->db->query("SELECT * FROM asrama_pengurus WHERE user_id = :uid AND nama_asrama = :nama");
        $this->db->bind(':uid', $userId);
        $this->db->bind(':nama', $namaAsrama);
        return $this->db->single();
    }

    public function save(int $userId, string $namaAsrama, string $namaPengurus, string $noHp): bool
    {
        $existing = $this->getByName($userId, $namaAsrama);
        if ($existing) {
            $this->db->query("UPDATE asrama_pengurus SET nama_pengurus = :pengurus, no_hp = :hp WHERE id = :id");
            $this->db->bind(':pengurus', $namaPengurus);
            $this->db->bind(':hp', $noHp);
            $this->db->bind(':id', $existing['id']);
        } else {
            $this->db->query("INSERT INTO asrama_pengurus (user_id, nama_asrama, nama_pengurus, no_hp) VALUES (:uid, :nama, :pengurus, :hp)");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':nama', $namaAsrama);
            $this->db->bind(':pengurus', $namaPengurus);
            $this->db->bind(':hp', $noHp);
        }
        return $this->db->execute();
    }
    
    public function hapus(int $id, int $userId): bool
    {
        $this->db->query("DELETE FROM asrama_pengurus WHERE id = :id AND user_id = :uid");
        $this->db->bind(':id', $id);
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }
}
