<?php
/**
 * Model.php — Base Model
 * Semua model mewarisi class ini
 */
class Model
{
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }
}
