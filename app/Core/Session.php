<?php

namespace App\Core;

/**
 * Session Helper
 * 
 * Wrapper sederhana untuk manajemen session PHP.
 */
class Session
{
    /**
     * Mulai session jika belum dimulai
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }
    }

    /**
     * Set nilai session
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Ambil nilai session
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Cek apakah key ada di session
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Hapus key dari session
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Hapus seluruh session
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Flash message (muncul sekali lalu hilang)
     */
    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            self::set('_flash_' . $key, $value);
            return null;
        }

        $value = self::get('_flash_' . $key);
        self::remove('_flash_' . $key);
        return $value;
    }

    /**
     * Generate & store CSRF token
     */
    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        self::set('_csrf_token', $token);
        return $token;
    }

    /**
     * Ambil CSRF token yang ada (atau generate baru)
     */
    public static function getCsrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            return self::generateCsrfToken();
        }
        return self::get('_csrf_token');
    }

    /**
     * Validasi CSRF token
     */
    public static function validateCsrfToken(?string $token): bool
    {
        $sessionToken = self::get('_csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }
}
