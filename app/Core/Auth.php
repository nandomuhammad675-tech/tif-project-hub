<?php

namespace App\Core;

/**
 * Authentication & Authorization
 * 
 * Menangani login, logout, dan pengecekan role.
 */
class Auth
{
    /**
     * Cek apakah user sedang login
     */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Ambil data user yang sedang login
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'       => Session::get('user_id'),
            'nim'      => Session::get('user_nim'),
            'name'     => Session::get('user_name'),
            'email'    => Session::get('user_email'),
            'role'     => Session::get('user_role'),
            'class'    => Session::get('user_class'),
            'semester' => Session::get('user_semester'),
        ];
    }

    /**
     * Ambil ID user yang login
     */
    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    /**
     * Cek apakah user memiliki role tertentu
     */
    public static function hasRole(string $role): bool
    {
        return self::check() && Session::get('user_role') === $role;
    }

    /**
     * Cek apakah user adalah admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Cek apakah user adalah mahasiswa
     */
    public static function isStudent(): bool
    {
        return self::hasRole('mahasiswa');
    }

    /**
     * Login user (simpan ke session)
     */
    public static function login(array $user): void
    {
        Session::set('user_id', $user['id']);
        Session::set('user_nim', $user['nim'] ?? null);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('user_class', $user['class'] ?? null);
        Session::set('user_semester', $user['semester'] ?? null);

        // Regenerasi session ID untuk keamanan
        session_regenerate_id(true);
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Middleware: wajib login
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: /login');
            exit;
        }
    }

    /**
     * Middleware: wajib role admin
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Session::flash('error', 'Anda tidak memiliki akses ke halaman ini.');
            header('Location: /');
            exit;
        }
    }

    /**
     * Middleware: wajib role mahasiswa
     */
    public static function requireStudent(): void
    {
        self::requireLogin();
        if (!self::isStudent()) {
            Session::flash('error', 'Anda tidak memiliki akses ke halaman ini.');
            header('Location: /admin');
            exit;
        }
    }

    /**
     * Redirect berdasarkan role setelah login
     */
    public static function redirectByRole(): void
    {
        if (self::isAdmin()) {
            header('Location: /admin');
        } else {
            header('Location: /');
        }
        exit;
    }
}
