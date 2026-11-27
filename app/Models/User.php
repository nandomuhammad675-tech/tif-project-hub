<?php

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 * 
 * Menangani data pengguna (mahasiswa & admin).
 */
class User extends Model
{
    protected string $table = 'users';

    /**
     * Cari user berdasarkan email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Cari user berdasarkan NIM
     */
    public function findByNim(string $nim): ?array
    {
        return $this->findBy('nim', $nim);
    }

    /**
     * Verifikasi password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Buat user baru (dengan hash password otomatis)
     */
    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Ambil semua mahasiswa aktif
     */
    /**
     * Cari mahasiswa aktif yang belum punya proyek (untuk menambah anggota kelompok)
     */
    public function searchAvailableStudents(string $q, int $limit = 8): array
    {
        $like = '%' . addcslashes($q, '\\%_') . '%';
        $stmt = $this->db->prepare(
            "SELECT id, nim, name, class FROM users
             WHERE role = 'mahasiswa' AND status = 'active'
               AND id NOT IN (SELECT user_id FROM project_members)
               AND (name LIKE ? OR nim LIKE ?)
             ORDER BY name ASC
             LIMIT " . (int) $limit
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function getStudents(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nim, name, email, class, semester, status, avatar, created_at
             FROM users 
             WHERE role = 'mahasiswa' 
             ORDER BY name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total mahasiswa
     */
    /**
     * Jumlah mahasiswa yang sudah tergabung di suatu proyek
     */
    public function countStudentsWithProject(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(DISTINCT pm.user_id)
             FROM project_members pm
             JOIN users u ON u.id = pm.user_id
             WHERE u.role = 'mahasiswa'"
        )->fetchColumn();
    }

    public function getRecentStudents(int $limit = 5): array
    {
        return $this->db->query(
            "SELECT u.id, u.name, u.nim, u.class, u.semester, u.avatar, u.created_at,
                    (SELECT COUNT(*) FROM project_members pm WHERE pm.user_id = u.id) AS has_project
             FROM users u
             WHERE u.role = 'mahasiswa'
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT " . (int) $limit
        )->fetchAll();
    }

    public function countStudents(): int
    {
        return $this->count("role = 'mahasiswa'");
    }
}
