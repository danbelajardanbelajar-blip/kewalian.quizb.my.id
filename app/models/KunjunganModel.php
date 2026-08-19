<?php
require_once APP_PATH . '/core/Model.php';

class KunjunganModel extends Model
{
    // Record a visit
    public function record(string $halaman, ?int $waliId = null, ?int $id_siswa = null, ?string $no_hp_walimurid = null): void
    {
        $ip = $this->getIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        $this->db->query("INSERT INTO kunjungan (ip, user_agent, halaman, referer, wali_id, id_siswa, no_hp_walimurid) VALUES (:ip, :user_agent, :halaman, :referer, :wali_id, :id_siswa, :no_hp_walimurid)");
        $this->db->bind(':ip', $ip);
        $this->db->bind(':user_agent', $userAgent);
        $this->db->bind(':halaman', $halaman);
        $this->db->bind(':referer', $referer);
        $this->db->bind(':wali_id', $waliId);
        $this->db->bind(':id_siswa', $id_siswa);
        $this->db->bind(':no_hp_walimurid', $no_hp_walimurid);
        $this->db->execute();
    }

    // Get IP from request
    private function getIp(): string
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        // Handle comma-separated IPs (e.g., from proxies) and limit to 45 chars for DB safety
        $ips = explode(',', $ip);
        return substr(trim($ips[0]), 0, 45);
    }

    /**
     * Ambil data tracking wali murid per siswa:
     * - apakah no_hp sudah diisi di tabel siswa
     * - jumlah kunjungan ke halaman laporan
     * - tanggal kunjungan terakhir
     */
    public function getWalimuridTracking(int $userId): array
    {
        $this->db->query("
            SELECT
                s.id,
                s.nama,
                s.no_hp,
                COUNT(k.id)             AS total_kunjungan,
                MAX(k.created_at)       AS kunjungan_terakhir
            FROM siswa s
            LEFT JOIN kunjungan k ON k.id_siswa = s.id
                AND k.halaman LIKE '%laporan%'
            WHERE s.user_id = :user_id
            GROUP BY s.id, s.nama, s.no_hp
            ORDER BY s.nama ASC
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
}
