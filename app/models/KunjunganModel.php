<?php
require_once APP_PATH . '/core/Model.php';

class KunjunganModel extends Model
{
    // Record a visit
    public function record(string $halaman, ?int $waliId = null): void
    {
        $ip = $this->getIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        $this->db->query("INSERT INTO kunjungan (ip, user_agent, halaman, referer, wali_id) VALUES (:ip, :user_agent, :halaman, :referer, :wali_id)");
        $this->db->bind(':ip', $ip);
        $this->db->bind(':user_agent', $userAgent);
        $this->db->bind(':halaman', $halaman);
        $this->db->bind(':referer', $referer);
        $this->db->bind(':wali_id', $waliId);
        $this->db->execute();
    }

    // Get IP from request
    private function getIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }
}
