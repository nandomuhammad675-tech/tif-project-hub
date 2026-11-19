<?php

namespace App\Core;

use PDO;

/**
 * Base Model
 * 
 * Semua model harus extend class ini.
 * Menyediakan akses ke database connection.
 */
class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil semua data dari tabel
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    /**
     * Cari data berdasarkan ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cari data berdasarkan kolom tertentu
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cari banyak data berdasarkan kolom
     */
    public function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$orderBy}");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    /**
     * Insert data baru
     * 
     * @return int Last insert ID
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update data berdasarkan ID
     */
    public function update(int $id, array $data): bool
    {
        $columns = array_keys($data);
        $set = implode(' = ?, ', $columns) . ' = ?';

        $sql = "UPDATE {$this->table} SET {$set} WHERE id = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Hapus data berdasarkan ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Hitung jumlah baris
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
