<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;

/**
 * Auth Controller
 * Login, Register, Logout
 */
class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Tampilkan halaman login
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }

        $this->view('auth/login', [
            'title' => 'Login - TIF Hub',
            'csrf'  => Session::getCsrfToken(),
        ], null);
    }

    /**
     * Proses login
     */
    public function login(): void
    {
        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid. Silakan coba lagi.');
        }

        // Kolom "email" menerima email ATAU NIM (nama field tetap 'email' agar kompatibel)
        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Email/NIM dan password wajib diisi.');
            Session::flash('old_email', $email);
            $this->redirect('/login');
        }

        $user = strpos($email, '@') !== false
            ? $this->userModel->findByEmail($email)
            : $this->userModel->findByNim($email);

        if (!$user) {
            Session::flash('error', 'Email/NIM atau password salah.');
            Session::flash('old_email', $email);
            $this->redirect('/login');
        }

        if (($user['status'] ?? 'active') !== 'active') {
            Session::flash('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            $this->redirect('/login');
        }

        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            Session::flash('error', 'Email/NIM atau password salah.');
            Session::flash('old_email', $email);
            $this->redirect('/login');
        }

        Auth::login($user);
        Session::flash('welcome', $user['name']);
        Auth::redirectByRole();
    }

    /**
     * Tampilkan halaman register
     */
    public function showRegister(): void
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }

        $this->view('auth/register', [
            'title' => 'Daftar - TIF Hub',
            'csrf'  => Session::getCsrfToken(),
        ], null);
    }

    /**
     * Proses register mahasiswa
     */
    public function register(): void
    {
        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid. Silakan coba lagi.');
        }

        $nim      = trim($this->input('nim', ''));
        $name     = trim($this->input('name', ''));
        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $passwordConfirm = $this->input('password_confirm', '');
        $class    = trim($this->input('class', ''));
        $semester = (int) $this->input('semester', 1);

        // Validasi
        $errors = [];

        if ($nim === '') {
            $errors[] = 'NIM wajib diisi.';
        } elseif ($this->userModel->findByNim($nim)) {
            $errors[] = 'NIM sudah terdaftar.';
        }

        if ($name === '') {
            $errors[] = 'Nama wajib diisi.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid.';
        } elseif (!email_domain_allowed($email)) {
            $errors[] = email_domain_hint();
        } elseif ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email sudah terdaftar.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }

        if ($semester < 1 || $semester > 14) {
            $semester = 1;
        }

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            Session::flash('old', [
                'nim' => $nim,
                'name' => $name,
                'email' => $email,
                'class' => $class,
                'semester' => $semester,
            ]);
            $this->redirect('/register');
        }

        // Buat akun mahasiswa
        $this->userModel->createUser([
            'nim'      => $nim,
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'role'     => 'mahasiswa',
            'class'    => $class ?: null,
            'semester' => $semester,
            'status'   => 'active',
        ]);

        Session::flash('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
        $this->redirect('/login');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Anda berhasil logout.');
        $this->redirect('/login');
    }
}