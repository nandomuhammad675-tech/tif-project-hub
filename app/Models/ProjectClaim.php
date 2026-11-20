<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

class ProjectClaim extends Model
{
    protected string $table = 'project_claims';

    public function findByUserAndSemester(int $userId, int $semesterId): ?array
    {
        $sql = "SELECT pc.*, p.title AS project_title, p.slug AS project_slug,
                       s.name AS semester_name, s.code AS semester_code
                FROM project_claims pc
                JOIN projects p ON p.id = pc.project_id
                JOIN semesters s ON s.id = pc.semester_id
                WHERE pc.user_id = ? AND pc.semester_id = ? AND pc.status = 'claimed'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $semesterId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $sql = "SELECT pc.*, p.title AS project_title, p.slug AS project_slug, p.course_name,
                       p.technologies, c.name AS category_name,
                       s.name AS semester_name, s.code AS semester_code, s.academic_year,
                       u.name AS student_name, u.nim AS student_nim, u.email AS student_email
                FROM project_claims pc
                JOIN projects p ON p.id = pc.project_id
                LEFT JOIN categories c ON c.id = p.category_id
                JOIN semesters s ON s.id = pc.semester_id
                JOIN users u ON u.id = pc.user_id
                WHERE pc.ticket_code = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findOwnedByUser(int $claimId, int $userId): ?array
    {
        $sql = "SELECT pc.*, p.title AS project_title, p.slug AS project_slug, p.course_name,
                       p.technologies, c.name AS category_name,
                       s.name AS semester_name, s.code AS semester_code, s.academic_year,
                       u.name AS student_name, u.nim AS student_nim
                FROM project_claims pc
                JOIN projects p ON p.id = pc.project_id
                LEFT JOIN categories c ON c.id = p.category_id
                JOIN semesters s ON s.id = pc.semester_id
                JOIN users u ON u.id = pc.user_id
                WHERE pc.id = ? AND pc.user_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$claimId, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByUser(int $userId): array
    {
        $sql = "SELECT pc.*, p.title AS project_title, p.slug AS project_slug,
                       s.name AS semester_name, s.code AS semester_code, s.academic_year
                FROM project_claims pc
                JOIN projects p ON p.id = pc.project_id
                JOIN semesters s ON s.id = pc.semester_id
                WHERE pc.user_id = ?
                ORDER BY pc.claimed_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAllAdmin(?int $semesterId = null): array
    {
        $where = ['1=1'];
        $params = [];
        if ($semesterId) {
            $where[] = 'pc.semester_id = ?';
            $params[] = $semesterId;
        }
        $sql = "SELECT pc.*, p.title AS project_title, u.name AS student_name, u.nim AS student_nim,
                       s.name AS semester_name, s.code AS semester_code
                FROM project_claims pc
                JOIN projects p ON p.id = pc.project_id
                JOIN users u ON u.id = pc.user_id
                JOIN semesters s ON s.id = pc.semester_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pc.claimed_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countClaimedInSemester(int $semesterId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM project_claims WHERE semester_id = ? AND status = 'claimed'"
        );
        $stmt->execute([$semesterId]);
        return (int) $stmt->fetchColumn();
    }

    public function getAvailableProjects(int $semesterId): array
    {
        $sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                       s.name AS semester_name
                FROM projects p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN semesters s ON s.id = p.semester_id
                WHERE p.is_published = 1
                  AND COALESCE(p.is_claimable, 1) = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM project_claims pc
                      WHERE pc.project_id = p.id
                        AND pc.semester_id = ?
                        AND pc.status = 'claimed'
                  )
                ORDER BY p.title ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$semesterId]);
        return $stmt->fetchAll();
    }

    private function generateTicketCode(): string
    {
        $year = date('Y');
        for ($i = 0; $i < 8; $i++) {
            $code = sprintf('CLAIM-%s-%s', $year, strtoupper(bin2hex(random_bytes(3))));
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM project_claims WHERE ticket_code = ?');
            $stmt->execute([$code]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $code;
            }
        }
        return 'CLAIM-' . $year . '-' . strtoupper(uniqid());
    }

    /**
     * @return array{success:bool,message:string,ticket_code?:string,claim_id?:int}
     */
    public function claim(int $projectId, int $userId): array
    {
        $violationModel = new ClaimViolation();

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->query('SELECT * FROM war_settings ORDER BY id ASC LIMIT 1 FOR UPDATE');
            $war = $stmt->fetch();
            if (!$war) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Pengaturan war belum dikonfigurasi admin.'];
            }

            $semesterId = (int) ($war['active_semester_id'] ?? 0);
            if ($semesterId <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Semester aktif belum ditentukan admin.'];
            }

            if (!(int) $war['is_open']) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Periode war belum dibuka admin.'];
            }

            $now = time();
            if (!empty($war['starts_at']) && $now < strtotime($war['starts_at'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Periode war belum dimulai.'];
            }
            if (!empty($war['ends_at']) && $now > strtotime($war['ends_at'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Periode war sudah berakhir.'];
            }

            $stmt = $this->db->prepare(
                "SELECT id FROM project_claims WHERE user_id = ? AND semester_id = ? AND status = 'claimed' FOR UPDATE"
            );
            $stmt->execute([$userId, $semesterId]);
            if ($stmt->fetch()) {
                $violationModel->log($userId, $projectId, $semesterId, 'Percobaan klaim kedua pada semester yang sama');
                $this->db->commit();
                return [
                    'success' => false,
                    'message' => 'Batas klaim semester ini sudah digunakan. Setiap mahasiswa hanya boleh satu proyek per semester. Percobaan ini dicatat sebagai pelanggaran.',
                ];
            }

            $stmt = $this->db->prepare(
                'SELECT * FROM projects WHERE id = ? AND is_published = 1 FOR UPDATE'
            );
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();
            if (!$project) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Proyek tidak ditemukan atau tidak dipublikasikan.'];
            }
            if (isset($project['is_claimable']) && !(int) $project['is_claimable']) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Proyek ini tidak dibuka untuk klaim.'];
            }

            $stmt = $this->db->prepare(
                "SELECT id FROM project_claims WHERE project_id = ? AND semester_id = ? AND status = 'claimed' FOR UPDATE"
            );
            $stmt->execute([$projectId, $semesterId]);
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Proyek ini sudah diklaim mahasiswa lain.'];
            }

            $ticketCode = $this->generateTicketCode();
            $claimedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare(
                'INSERT INTO project_claims (ticket_code, project_id, user_id, semester_id, claimed_at, status)
                 VALUES (?, ?, ?, ?, ?, \'claimed\')'
            );
            $stmt->execute([$ticketCode, $projectId, $userId, $semesterId, $claimedAt]);
            $claimId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM project_members WHERE project_id = ? AND user_id = ?'
            );
            $stmt->execute([$projectId, $userId]);
            if ((int) $stmt->fetchColumn() === 0) {
                $stmt = $this->db->prepare(
                    'INSERT INTO project_members (project_id, user_id, role_in_project, created_at) VALUES (?, ?, ?, NOW())'
                );
                $stmt->execute([$projectId, $userId, 'Ketua']);
            }

            $this->db->commit();

            return [
                'success'     => true,
                'message'     => 'Klaim berhasil. Tiket digital Anda telah diterbitkan.',
                'ticket_code' => $ticketCode,
                'claim_id'    => $claimId,
            ];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate')) {
                return ['success' => false, 'message' => 'Proyek atau slot klaim sudah diambil. Silakan pilih proyek lain.'];
            }
            error_log('[ProjectClaim] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memproses klaim. Silakan coba lagi.'];
        }
    }
}
