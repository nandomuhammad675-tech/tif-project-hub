<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    public function forProjects(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE type IN ('project', 'both') ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    public function forTickets(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE type IN ('ticket', 'both') ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }
}
