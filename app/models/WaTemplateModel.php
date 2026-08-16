<?php
require_once APP_PATH . "/core/Model.php";

class WaTemplateModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        try {
            $this->db->query("ALTER TABLE users ADD COLUMN wa_template TEXT DEFAULT NULL");
            $this->db->execute();
        } catch (Exception $e) {}
    }

    public function getTemplate(int $userId): ?string
    {
        $this->db->query("SELECT wa_template FROM users WHERE id = :id");
        $this->db->bind(":id", $userId);
        $res = $this->db->single();
        return $res ? $res["wa_template"] : null;
    }

    public function saveTemplate(int $userId, string $template): bool
    {
        $this->db->query("UPDATE users SET wa_template = :tpl WHERE id = :id");
        $this->db->bind(":tpl", $template);
        $this->db->bind(":id", $userId);
        return $this->db->execute();
    }
}

