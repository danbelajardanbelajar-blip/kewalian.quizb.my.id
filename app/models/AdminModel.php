<?php
require_once APP_PATH . '/core/Model.php';

class AdminModel extends Model
{
    // Get all users with stats
    public function getAllUsers(): array
    {
        $this->db->query("SELECT u.*, (SELECT COUNT(*) FROM siswa s WHERE s.user_id = u.id) as total_siswa FROM users u ORDER BY u.id DESC");
        return $this->db->resultSet();
    }

    // Get single user by id  
    public function getUserById(int $id): ?array
    {
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result ?: null;
    }

    // Update user (nama_lengkap, kelas, is_admin)
    public function updateUser(int $id, array $data): bool
    {
        $this->db->query("UPDATE users SET nama_lengkap = :nama_lengkap, kelas = :kelas, is_admin = :is_admin WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':nama_lengkap', $data['nama_lengkap']);
        $this->db->bind(':kelas', $data['kelas']);
        $this->db->bind(':is_admin', $data['is_admin'] ? 1 : 0);
        return $this->db->execute();
    }

    // Delete user
    public function deleteUser(int $id): bool
    {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Reset user password
    public function resetPassword(int $id, string $newPassword): bool
    {
        $this->db->query("UPDATE users SET password = :password WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':password', password_hash($newPassword, PASSWORD_DEFAULT));
        return $this->db->execute();
    }

    // Toggle is_admin
    public function toggleAdmin(int $id): bool
    {
        $this->db->query("UPDATE users SET is_admin = NOT is_admin WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Get dashboard stats: ['total_users', 'total_kunjungan_today', 'total_kunjungan_all', 'unread_feedback', 'new_users_week']
    public function getStats(): array
    {
        $stats = [];
        
        $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $this->db->single()['count'];
        
        $this->db->query("SELECT COUNT(*) as count FROM kunjungan WHERE DATE(created_at) = CURDATE()");
        $stats['total_kunjungan_today'] = $this->db->single()['count'];
        
        $this->db->query("SELECT COUNT(*) as count FROM kunjungan");
        $stats['total_kunjungan_all'] = $this->db->single()['count'];
        
        $this->db->query("SELECT COUNT(*) as count FROM feedback WHERE is_read = 0");
        $stats['unread_feedback'] = $this->db->single()['count'];
        
        try {
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['new_users_week'] = $this->db->single()['count'];
        } catch (Exception $e) {
            $stats['new_users_week'] = 0; // Fallback jika kolom created_at belum ada
        }
        
        return $stats;
    }

    // Get kunjungan per day for last N days (for chart)
    public function getKunjunganPerDay(int $days = 7): array
    {
        $this->db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM kunjungan WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }

    // Get kunjungan list paginated
    public function getKunjungan(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $this->db->query("SELECT k.*, u.nama_lengkap FROM kunjungan k LEFT JOIN users u ON k.wali_id = u.id ORDER BY k.created_at DESC LIMIT :limit OFFSET :offset");
        $this->db->bind(':limit', $perPage);
        $this->db->bind(':offset', $offset);
        return $this->db->resultSet();
    }

    // Get kunjungan count total
    public function getKunjunganCount(): int
    {
        $this->db->query("SELECT COUNT(*) as count FROM kunjungan");
        return $this->db->single()['count'];
    }

    // Get kunjungan per halaman (page URL stats)
    public function getKunjunganPerHalaman(): array
    {
        $this->db->query("SELECT halaman, COUNT(*) as count FROM kunjungan GROUP BY halaman ORDER BY count DESC LIMIT 10");
        return $this->db->resultSet();
    }

    // Get recent N registered users
    public function getRecentUsers(int $limit = 5): array
    {
        $this->db->query("SELECT * FROM users ORDER BY id DESC LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}

