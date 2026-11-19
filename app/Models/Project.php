<?php

namespace App\Models;

use App\Core\Model;
use App\Helpers\ImageUploader;

class Project extends Model
{
    protected string $table = 'projects';

    public function getPublished(array $filters = [], string $sort = 'newest', int $limit = 12, int $offset = 0): array
    {
        $where = ['p.is_published = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(p.title LIKE ? OR p.description LIKE ? OR p.technologies LIKE ? OR p.short_desc LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['semester_id'])) {
            $where[] = 'p.semester_id = ?';
            $params[] = $filters['semester_id'];
        }
        if (!empty($filters['course'])) {
            $where[] = 'p.course_name LIKE ?';
            $params[] = '%' . $filters['course'] . '%';
        }
        if (!empty($filters['tech'])) {
            $where[] = 'p.technologies LIKE ?';
            $params[] = '%' . $filters['tech'] . '%';
        }
        // Filter status: available = belum ada anggota, taken = sudah ada anggota
        if (!empty($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $where[] = '(SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) = 0';
            } elseif ($filters['availability'] === 'taken') {
                $where[] = '(SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) > 0';
            }
        }

        $order = match ($sort) {
            'popular' => 'p.view_count DESC',
            'title'   => 'p.title ASC',
            default   => 'p.created_at DESC',
        };

        $sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon, c.slug AS category_slug,
                       s.name AS semester_name, s.code AS semester_code,
                       (SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) AS member_count
                FROM projects p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN semesters s ON s.id = p.semester_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$order}
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPublished(array $filters = []): int
    {
        $where = ['p.is_published = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(p.title LIKE ? OR p.description LIKE ? OR p.technologies LIKE ? OR p.short_desc LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['semester_id'])) {
            $where[] = 'p.semester_id = ?';
            $params[] = $filters['semester_id'];
        }
        if (!empty($filters['course'])) {
            $where[] = 'p.course_name LIKE ?';
            $params[] = '%' . $filters['course'] . '%';
        }
        if (!empty($filters['tech'])) {
            $where[] = 'p.technologies LIKE ?';
            $params[] = '%' . $filters['tech'] . '%';
        }
        if (!empty($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $where[] = '(SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) = 0';
            } elseif ($filters['availability'] === 'taken') {
                $where[] = '(SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) > 0';
            }
        }

        $sql = "SELECT COUNT(*) FROM projects p WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                       s.name AS semester_name, s.code AS semester_code,
                       (SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) AS member_count
                FROM projects p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN semesters s ON s.id = p.semester_id
                WHERE p.slug = ? AND p.is_published = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                       s.name AS semester_name,
                       (SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) AS member_count
                FROM projects p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN semesters s ON s.id = p.semester_id
                WHERE p.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getMembers(int $projectId): array
    {
        $sql = "SELECT u.id, u.name, u.nim, u.class, pm.role_in_project
                FROM project_members pm
                JOIN users u ON u.id = pm.user_id
                WHERE pm.project_id = ?
                ORDER BY pm.role_in_project = 'Ketua' DESC, u.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function incrementViews(int $id): void
    {
        $this->db->prepare('UPDATE projects SET view_count = view_count + 1 WHERE id = ?')->execute([$id]);
    }

    public function getLatest(int $limit = 6): array
    {
        return $this->getPublished([], 'newest', $limit);
    }

    public function getPopular(int $limit = 6): array
    {
        return $this->getPublished([], 'popular', $limit);
    }

    public function getByCategory(): array
    {
        $sql = "SELECT c.id, c.name, c.icon, c.slug, COUNT(p.id) AS total
                FROM categories c
                LEFT JOIN projects p ON p.category_id = c.id AND p.is_published = 1
                WHERE c.type IN ('project', 'both')
                GROUP BY c.id
                ORDER BY total DESC, c.name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getBySemester(): array
    {
        $sql = "SELECT s.id, s.name, s.code, COUNT(p.id) AS total
                FROM semesters s
                LEFT JOIN projects p ON p.semester_id = s.id AND p.is_published = 1
                GROUP BY s.id
                HAVING total > 0
                ORDER BY s.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getAllAdmin(): array
    {
        $sql = "SELECT p.*, c.name AS category_name, s.name AS semester_name,
                       (SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) AS member_count,
                       (SELECT COUNT(*) FROM project_images pi WHERE pi.project_id = p.id) AS image_count
                FROM projects p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN semesters s ON s.id = p.semester_id
                ORDER BY p.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function createProject(array $data): int
    {
        $data['slug'] = $this->makeSlug($data['title']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Cek apakah proyek sudah diambil (punya anggota)
     */
    public function isTaken(int $projectId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM project_members WHERE project_id = ?');
        $stmt->execute([$projectId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Cek apakah mahasiswa sudah tergabung di proyek manapun
     */
    public function userHasProject(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM project_members WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Ambil proyek yang sedang dikerjakan mahasiswa
     */
    public function getUserProject(int $userId): ?array
    {
        $sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                       s.name AS semester_name, pm.role_in_project
                FROM project_members pm
                JOIN projects p ON p.id = pm.project_id
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN semesters s ON s.id = p.semester_id
                WHERE pm.user_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Mahasiswa mengambil / mengklaim proyek
     * Aturan: 1 mahasiswa = 1 proyek, proyek yang sudah ada anggota tidak bisa diambil
     */
    public function claimProject(int $projectId, int $userId): array
    {
        // Sudah punya proyek?
        if ($this->userHasProject($userId)) {
            return ['success' => false, 'message' => 'Anda sudah tergabung di satu proyek. Setiap mahasiswa hanya boleh mengerjakan satu proyek.'];
        }

        // Proyek sudah diambil?
        if ($this->isTaken($projectId)) {
            return ['success' => false, 'message' => 'Proyek ini sudah dikerjakan mahasiswa lain dan tidak bisa diambil lagi.'];
        }

        // Proyek ada & published?
        $project = $this->find($projectId);
        if (!$project || !(int)$project['is_published']) {
            return ['success' => false, 'message' => 'Proyek tidak ditemukan.'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO project_members (project_id, user_id, role_in_project, created_at) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$projectId, $userId, 'Ketua']);

        return ['success' => true, 'message' => 'Berhasil mengambil proyek. Selamat mengerjakan!'];
    }

    public function syncMembers(int $projectId, array $userIds): void
    {
        // Validasi: setiap user hanya boleh 1 proyek
        foreach ($userIds as $uid) {
            if (!$uid) continue;
            $stmt = $this->db->prepare(
                'SELECT project_id FROM project_members WHERE user_id = ? AND project_id != ? LIMIT 1'
            );
            $stmt->execute([$uid, $projectId]);
            if ($stmt->fetch()) {
                // Skip user yang sudah di proyek lain
                continue;
            }
        }

        $this->db->prepare('DELETE FROM project_members WHERE project_id = ?')->execute([$projectId]);
        $stmt = $this->db->prepare('INSERT INTO project_members (project_id, user_id, role_in_project) VALUES (?, ?, ?)');
        $first = true;
        foreach ($userIds as $uid) {
            if (!$uid) continue;
            // Cek lagi apakah user free
            $check = $this->db->prepare('SELECT COUNT(*) FROM project_members WHERE user_id = ?');
            $check->execute([$uid]);
            if ((int)$check->fetchColumn() > 0) continue;

            $stmt->execute([$projectId, $uid, $first ? 'Ketua' : 'Anggota']);
            $first = false;
        }
    }

    private function makeSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $base = $slug;
        $i = 1;
        while ($this->findBy('slug', $slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    // ========== GALERI FOTO ==========

    public function getImages(int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM project_images WHERE project_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC'
        );
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function addImages(int $projectId, array $filenames): void
    {
        if (!$filenames) {
            return;
        }
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM project_images WHERE project_id = ?');
        $stmt->execute([$projectId]);
        $order = (int) $stmt->fetchColumn();

        $ins = $this->db->prepare(
            'INSERT INTO project_images (project_id, filename, is_cover, sort_order) VALUES (?, ?, 0, ?)'
        );
        foreach ($filenames as $fn) {
            $ins->execute([$projectId, $fn, ++$order]);
        }
        $this->ensureCover($projectId);
    }

    /**
     * Hapus foto (file + record) milik proyek tertentu
     */
    public function deleteImages(int $projectId, array $imageIds): void
    {
        $imageIds = array_values(array_filter(array_map('intval', $imageIds)));
        if (!$imageIds) {
            return;
        }
        $sel = $this->db->prepare('SELECT id, filename FROM project_images WHERE id = ? AND project_id = ?');
        $del = $this->db->prepare('DELETE FROM project_images WHERE id = ?');
        foreach ($imageIds as $id) {
            $sel->execute([$id, $projectId]);
            if ($row = $sel->fetch()) {
                ImageUploader::delete($row['filename']);
                $del->execute([$row['id']]);
            }
        }
        $this->ensureCover($projectId);
    }

    public function setCover(int $projectId, int $imageId): void
    {
        $chk = $this->db->prepare('SELECT COUNT(*) FROM project_images WHERE id = ? AND project_id = ?');
        $chk->execute([$imageId, $projectId]);
        if ((int) $chk->fetchColumn() === 0) {
            return;
        }
        $this->db->prepare('UPDATE project_images SET is_cover = 0 WHERE project_id = ?')->execute([$projectId]);
        $this->db->prepare('UPDATE project_images SET is_cover = 1 WHERE id = ?')->execute([$imageId]);
        $this->syncThumbnail($projectId);
    }

    /**
     * Simpan urutan foto sesuai urutan id yang dikirim form (1, 2, 3, ...)
     * Hanya foto milik proyek ini yang ikut diubah.
     */
    public function reorderImages(int $projectId, array $orderedIds): void
    {
        $orderedIds = array_values(array_unique(array_filter(array_map('intval', $orderedIds))));
        if (!$orderedIds) {
            return;
        }
        $upd = $this->db->prepare('UPDATE project_images SET sort_order = ? WHERE id = ? AND project_id = ?');
        foreach ($orderedIds as $pos => $imageId) {
            $upd->execute([$pos + 1, $imageId, $projectId]);
        }
    }

    /**
     * Pastikan selalu ada 1 cover bila proyek punya foto
     */
    private function ensureCover(int $projectId): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM project_images WHERE project_id = ? AND is_cover = 1');
        $stmt->execute([$projectId]);
        if ((int) $stmt->fetchColumn() === 0) {
            $this->db->prepare(
                'UPDATE project_images SET is_cover = 1 WHERE project_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1'
            )->execute([$projectId]);
        }
        $this->syncThumbnail($projectId);
    }

    /**
     * Kolom projects.thumbnail = nama file cover (dipakai kartu katalog)
     */
    private function syncThumbnail(int $projectId): void
    {
        $stmt = $this->db->prepare('SELECT filename FROM project_images WHERE project_id = ? AND is_cover = 1 LIMIT 1');
        $stmt->execute([$projectId]);
        $thumb = $stmt->fetchColumn() ?: null;
        $this->db->prepare('UPDATE projects SET thumbnail = ? WHERE id = ?')->execute([$thumb, $projectId]);
    }

    /**
     * Hapus proyek beserta semua file fotonya
     */
    public function deleteWithImages(int $projectId): void
    {
        $stmt = $this->db->prepare('SELECT filename FROM project_images WHERE project_id = ?');
        $stmt->execute([$projectId]);
        foreach ($stmt->fetchAll() as $row) {
            ImageUploader::delete($row['filename']);
        }
        $this->delete($projectId);
    }

    public function countAll(): int
    {
        return $this->count();
    }
}
