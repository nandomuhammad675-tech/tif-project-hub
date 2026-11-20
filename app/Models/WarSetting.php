<?php

namespace App\Models;

use App\Core\Model;

class WarSetting extends Model
{
    protected string $table = 'war_settings';

    public function current(): ?array
    {
        $sql = "SELECT w.*, s.name AS semester_name, s.code AS semester_code, s.academic_year
                FROM war_settings w
                LEFT JOIN semesters s ON s.id = w.active_semester_id
                ORDER BY w.id ASC
                LIMIT 1";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isWarActive(?array $settings = null): bool
    {
        $settings = $settings ?? $this->current();
        if (!$settings) {
            return false;
        }
        if (!(int) ($settings['is_open'] ?? 0)) {
            return false;
        }
        $now = time();
        if (!empty($settings['starts_at']) && $now < strtotime($settings['starts_at'])) {
            return false;
        }
        if (!empty($settings['ends_at']) && $now > strtotime($settings['ends_at'])) {
            return false;
        }
        return !empty($settings['active_semester_id']);
    }

    public function statusLabel(?array $settings = null): string
    {
        $settings = $settings ?? $this->current();
        if (!$settings || !(int) ($settings['is_open'] ?? 0)) {
            return 'closed';
        }
        $now = time();
        if (!empty($settings['starts_at']) && $now < strtotime($settings['starts_at'])) {
            return 'scheduled';
        }
        if (!empty($settings['ends_at']) && $now > strtotime($settings['ends_at'])) {
            return 'ended';
        }
        return 'active';
    }

    public function updateSettings(array $data, int $adminId): void
    {
        $current = $this->current();
        if (!$current) {
            $stmt = $this->db->prepare(
                'INSERT INTO war_settings (is_open, starts_at, ends_at, active_semester_id, updated_by) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                (int) ($data['is_open'] ?? 0),
                $data['starts_at'] ?: null,
                $data['ends_at'] ?: null,
                $data['active_semester_id'] ?: null,
                $adminId,
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE war_settings SET is_open = ?, starts_at = ?, ends_at = ?, active_semester_id = ?, updated_by = ? WHERE id = ?'
        );
        $stmt->execute([
            (int) ($data['is_open'] ?? 0),
            $data['starts_at'] ?: null,
            $data['ends_at'] ?: null,
            $data['active_semester_id'] ?: null,
            $adminId,
            $current['id'],
        ]);
    }
}
