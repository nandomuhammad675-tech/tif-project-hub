<?php

namespace App\Models;

use App\Core\Model;

class ClaimViolation extends Model
{
    protected string $table = 'claim_violations';

    public function log(int $userId, ?int $projectId, int $semesterId, string $reason): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO claim_violations (user_id, project_id, semester_id, attempted_at, reason, status)
             VALUES (?, ?, ?, NOW(), ?, \'pending\')'
        );
        $stmt->execute([$userId, $projectId, $semesterId, $reason]);
    }

    public function getAll(?string $status = null): array
    {
        $where = ['1=1'];
        $params = [];
        if ($status !== null && $status !== '') {
            $where[] = 'v.status = ?';
            $params[] = $status;
        }
        $sql = "SELECT v.*, u.name AS student_name, u.nim AS student_nim,
                       p.title AS project_title, s.name AS semester_name, s.code AS semester_code,
                       a.name AS reviewer_name
                FROM claim_violations v
                JOIN users u ON u.id = v.user_id
                LEFT JOIN projects p ON p.id = v.project_id
                JOIN semesters s ON s.id = v.semester_id
                LEFT JOIN users a ON a.id = v.reviewed_by
                WHERE " . implode(' AND ', $where) . "
                ORDER BY v.attempted_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPending(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM claim_violations WHERE status = 'pending'");
        return (int) $stmt->fetchColumn();
    }

    public function review(int $id, string $status, ?float $penaltyPoints, ?string $adminNote, int $adminId): bool
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return false;
        }
        $stmt = $this->db->prepare(
            'UPDATE claim_violations
             SET status = ?, penalty_points = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW()
             WHERE id = ? AND status = \'pending\''
        );
        $stmt->execute([$status, $penaltyPoints, $adminNote, $adminId, $id]);
        return $stmt->rowCount() > 0;
    }
}
