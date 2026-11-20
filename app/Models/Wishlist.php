<?php

namespace App\Models;

use App\Core\Model;

class Wishlist extends Model
{
    protected string $table = 'wishlists';

    public function getByUser(int $userId): array
    {
        $sql = "SELECT w.*, p.title, p.slug, p.short_desc, p.thumbnail, c.name AS category_name, c.icon AS category_icon
                FROM wishlists w
                JOIN projects p ON p.id = w.project_id
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $projectId): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO wishlists (user_id, project_id, created_at) VALUES (?, ?, NOW())'
            );
            return $stmt->execute([$userId, $projectId]);
        } catch (\PDOException $e) {
            // Abaikan error duplikasi (SQLSTATE 23000)
            if ($e->getCode() === '23000') {
                return true;
            }
            throw $e;
        }
    }

    public function remove(int $userId, int $projectId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM wishlists WHERE user_id = ? AND project_id = ?');
        return $stmt->execute([$userId, $projectId]);
    }

    public function isWishlisted(int $userId, int $projectId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND project_id = ?');
        $stmt->execute([$userId, $projectId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
