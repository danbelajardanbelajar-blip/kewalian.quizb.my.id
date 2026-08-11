<?php
require_once APP_PATH . '/core/Model.php';

class FeedbackModel extends Model
{
    public function getAll(bool $unreadOnly = false): array
    {
        $query = "SELECT * FROM feedback";
        if ($unreadOnly) {
            $query .= " WHERE is_read = 0";
        }
        $query .= " ORDER BY created_at DESC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    public function getById(int $id): ?array
    {
        $this->db->query("SELECT * FROM feedback WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result ?: null;
    }

    public function insert(array $data): bool
    {
        $this->db->query("INSERT INTO feedback (user_id, nama, email, pesan, rating) VALUES (:user_id, :nama, :email, :pesan, :rating)");
        $this->db->bind(':user_id', $data['user_id'] ?? null);
        $this->db->bind(':nama', $data['nama']);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':pesan', $data['pesan']);
        $this->db->bind(':rating', $data['rating'] ?? null);
        return $this->db->execute();
    }

    public function markRead(int $id): bool
    {
        $this->db->query("UPDATE feedback SET is_read = 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function markAllRead(): bool
    {
        $this->db->query("UPDATE feedback SET is_read = 1");
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM feedback WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getUnreadCount(): int
    {
        $this->db->query("SELECT COUNT(*) as count FROM feedback WHERE is_read = 0");
        return $this->db->single()['count'];
    }

    public function getAvgRating(): float
    {
        $this->db->query("SELECT AVG(rating) as avg_rating FROM feedback WHERE rating IS NOT NULL");
        $result = $this->db->single();
        return (float) ($result['avg_rating'] ?? 0);
    }
}
