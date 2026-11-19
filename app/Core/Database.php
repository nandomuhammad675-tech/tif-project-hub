<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Connection (Singleton)
 * 
 * Mengelola koneksi PDO ke MySQL.
 * Hanya membuat satu instance koneksi selama request.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Ambil instance PDO (singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                if (getenv('APP_DEBUG') === 'true') {
                    die('Database Connection Error: ' . $e->getMessage());
                }
                die('Database connection failed. Please try again later.');
            }
        }

        return self::$instance;
    }

    /**
     * Cegah cloning
     */
    private function __clone() {}

    /**
     * Cegah unserialize
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
