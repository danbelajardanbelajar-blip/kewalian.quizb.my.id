<?php
require_once APP_PATH . '/core/Model.php';

/**
 * PeerReviewModel.php
 * Mengelola data pertanyaan dan jawaban Peer Review
 */
class PeerReviewModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        
        // Auto Migration
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS peer_pertanyaan (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    pertanyaan VARCHAR(255) NOT NULL,
                    sifat ENUM('positif', 'negatif') NOT NULL DEFAULT 'positif',
                    status TINYINT(1) NOT NULL DEFAULT 1
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $this->db->execute();
        } catch (Exception $e) {}

        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS peer_vote (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_pertanyaan INT NOT NULL,
                    id_siswa_terpilih INT NOT NULL,
                    tanggal DATE NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $this->db->execute();
        } catch (Exception $e) {}
    }

    /**
     * Ambil semua pertanyaan milik Wali Kelas
     */
    public function getPertanyaan(int $userId): array
    {
        $this->db->query("SELECT * FROM peer_pertanyaan WHERE user_id = :uid ORDER BY id DESC");
        $this->db->bind(':uid', $userId);
        return $this->db->resultSet();
    }

    /**
     * Ambil pertanyaan aktif
     */
    public function getActivePertanyaan(int $userId): array
    {
        $this->db->query("SELECT * FROM peer_pertanyaan WHERE user_id = :uid AND status = 1 ORDER BY id ASC");
        $this->db->bind(':uid', $userId);
        return $this->db->resultSet();
    }

    /**
     * Simpan vote baru secara anonim
     */
    public function saveVote(int $idPertanyaan, int $idSiswaTerpilih, string $tanggal): bool
    {
        $this->db->query("INSERT INTO peer_vote (id_pertanyaan, id_siswa_terpilih, tanggal) VALUES (:idp, :ids, :tgl)");
        $this->db->bind(':idp', $idPertanyaan);
        $this->db->bind(':ids', $idSiswaTerpilih);
        $this->db->bind(':tgl', $tanggal);
        return $this->db->execute();
    }

    /**
     * Ambil leaderboard untuk satu pertanyaan
     */
    public function getLeaderboard(int $idPertanyaan, string $rentang = 'semua'): array
    {
        $sql = "SELECT p.id_siswa_terpilih, s.nama, COUNT(p.id) as total_vote 
                FROM peer_vote p
                JOIN siswa s ON p.id_siswa_terpilih = s.id
                WHERE p.id_pertanyaan = :idp ";
                
        if ($rentang === 'bulan_ini') {
            $sql .= "AND MONTH(p.tanggal) = MONTH(CURRENT_DATE()) AND YEAR(p.tanggal) = YEAR(CURRENT_DATE()) ";
        } elseif ($rentang === 'minggu_ini') {
            $sql .= "AND YEARWEEK(p.tanggal, 1) = YEARWEEK(CURRENT_DATE(), 1) ";
        }
        
        $sql .= "GROUP BY p.id_siswa_terpilih ORDER BY total_vote DESC LIMIT 5";
        
        $this->db->query($sql);
        $this->db->bind(':idp', $idPertanyaan);
        return $this->db->resultSet();
    }
}
