<?php
require_once APP_PATH . "/core/Model.php";

class WalimuridModel extends Model
{
    public function getSiswaById(int $id)
    {
        $this->db->query("SELECT * FROM siswa WHERE id = :id");
        $this->db->bind(":id", $id);
        return $this->db->single();
    }

    public function verifyNoHp(int $id, string $noHp): bool
    {
        $siswa = $this->getSiswaById($id);
        if (!$siswa) return false;
        
        // Remove formatting from both sides
        $dbHp = preg_replace("/[^0-9]/", "", $siswa["no_hp"] ?? "");
        $inputHp = preg_replace("/[^0-9]/", "", $noHp);

        if (empty($dbHp) || empty($inputHp)) return false;
        return $dbHp === $inputHp;
    }

    public function getProgress(int $id_siswa): array
    {
        // Get total poin per day
        $this->db->query("
            SELECT h.tanggal, SUM(d.poin) as total_poin
            FROM absen_header h
            JOIN absen_detail d ON h.id = d.id_absen
            WHERE h.id_siswa = :id_siswa
            GROUP BY h.id, h.tanggal
            ORDER BY h.tanggal ASC
        ");
        $this->db->bind(":id_siswa", $id_siswa);
        return $this->db->resultSet();
    }

    public function getRanking(int $user_id): array
    {
        // Get total poin for all students in the same class (user_id)
        $this->db->query("
            SELECT s.id, s.nama, COALESCE(SUM(d.poin), 0) as total_poin
            FROM siswa s
            LEFT JOIN absen_header h ON s.id = h.id_siswa
            LEFT JOIN absen_detail d ON h.id = d.id_absen
            WHERE s.user_id = :user_id
            GROUP BY s.id, s.nama
            ORDER BY total_poin DESC
        ");
        $this->db->bind(":user_id", $user_id);
        
        $results = $this->db->resultSet();
        $rankings = [];
        $rank = 1;
        foreach ($results as $row) {
            $rankings[$row["id"]] = [
                "rank" => $rank,
                "nama" => $row["nama"],
                "total_poin" => $row["total_poin"] ?? 0
            ];
            $rank++;
        }
        return $rankings;
    }
}

