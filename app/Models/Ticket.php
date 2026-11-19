<?php

namespace App\Models;

use App\Core\Model;

class Ticket extends Model
{
    protected string $table = 'tickets';

    public function generateCode(): string
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM tickets');
        $num = (int) $stmt->fetchColumn() + 1;
        return 'TIF-' . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }

    public function getByUser(int $userId): array
    {
        $sql = "SELECT t.*, c.name AS category_name, c.icon AS category_icon
                FROM tickets t
                LEFT JOIN categories c ON c.id = t.category_id
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT t.*, c.name AS category_name, c.icon AS category_icon,
                       u.name AS user_name, u.nim AS user_nim, u.email AS user_email,
                       a.name AS assigned_name
                FROM tickets t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN users u ON u.id = t.user_id
                LEFT JOIN users a ON a.id = t.assigned_to
                WHERE t.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getReplies(int $ticketId, bool $includeInternal = false): array
    {
        $sql = "SELECT r.*, u.name AS user_name, u.role AS user_role
                FROM ticket_replies r
                JOIN users u ON u.id = r.user_id
                WHERE r.ticket_id = ?";
        if (!$includeInternal) {
            $sql .= " AND r.is_internal = 0";
        }
        $sql .= " ORDER BY r.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function addReply(int $ticketId, int $userId, string $message, bool $isInternal = false): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal, created_at) VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$ticketId, $userId, $message, $isInternal ? 1 : 0]);
        $this->db->prepare('UPDATE tickets SET updated_at = NOW() WHERE id = ?')->execute([$ticketId]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?int $assignedTo = null): bool
    {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($assignedTo !== null) {
            $data['assigned_to'] = $assignedTo;
        }
        if (in_array($status, ['selesai', 'ditolak'], true)) {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        return $this->update($id, $data);
    }

    public function getAllAdmin(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 't.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[] = 't.priority = ?';
            $params[] = $filters['priority'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(t.title LIKE ? OR t.ticket_code LIKE ? OR u.name LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q]);
        }

        $sql = "SELECT t.*, c.name AS category_name, u.name AS user_name, u.nim
                FROM tickets t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN users u ON u.id = t.user_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY FIELD(t.status, 'open', 'diproses', 'selesai', 'ditolak'), t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByStatus(string $status): int
    {
        return $this->count('status = ?', [$status]);
    }

    public function countAll(): int
    {
        return $this->count();
    }

    public function createTicket(array $data): int
    {
        $data['ticket_code'] = $this->generateCode();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }
}
