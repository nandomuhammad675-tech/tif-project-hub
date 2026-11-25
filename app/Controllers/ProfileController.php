<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\ImageUploader;
use App\Models\User;
use App\Models\ProjectClaim;
use App\Models\WarSetting;

class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireStudent();
        $user = Auth::user();

        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT p.id, p.title, p.slug, p.short_desc, p.technologies, c.name AS category_name, c.icon AS category_icon,
                    s.name AS semester_name, pm.role_in_project
             FROM project_members pm
             JOIN projects p ON p.id = pm.project_id AND p.is_published = 1
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN semesters s ON s.id = p.semester_id
             WHERE pm.user_id = ?
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$user['id']]);
        $myProjects = $stmt->fetchAll();

        $claimModel = new ProjectClaim();
        $warModel = new WarSetting();
        $claims = $claimModel->getByUser((int) $user['id']);
        $war = $warModel->current();
        $activeClaim = null;
        if ($war && !empty($war['active_semester_id'])) {
            $activeClaim = $claimModel->findByUserAndSemester((int) $user['id'], (int) $war['active_semester_id']);
        }

        $this->view('student/profile', [
            'title'       => 'Profil Saya - TIF Hub',
            'user'        => $user,
            'myProjects'  => $myProjects,
            'claims'      => $claims,
            'activeClaim' => $activeClaim,
            'war'         => $war,
        ]);
    }

    /**
     * Ganti foto profil
     */
    public function updateAvatar(): void
    {
        Auth::requireStudent();

        // Jika file melebihi post_max_size, PHP mengosongkan $_POST dan $_FILES
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->profileError('Ukuran foto melebihi batas server. Gunakan foto yang lebih kecil.');
        }
        if (!$this->validateCsrf()) {
            $this->profileError('Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        }

        $result = ImageUploader::saveAvatar('avatar');
        if ($result['error'] !== null) {
            $this->profileError($result['error']);
        }

        $userId = (int) Auth::id();
        $userModel = new User();
        $old = $userModel->find($userId)['avatar'] ?? null;

        $userModel->update($userId, ['avatar' => $result['filename']]);
        ImageUploader::deleteAvatar($old);
        Session::set('user_avatar', $result['filename']);

        $this->redirectWithSuccess('/profile', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil (kembali ke inisial)
     */
    public function removeAvatar(): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->profileError('Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        }

        $userId = (int) Auth::id();
        $userModel = new User();
        $old = $userModel->find($userId)['avatar'] ?? null;

        $userModel->update($userId, ['avatar' => null]);
        ImageUploader::deleteAvatar($old);
        Session::set('user_avatar', '');

        $this->redirectWithSuccess('/profile', 'Foto profil dihapus.');
    }

    // ------------------------------------------------------------
    // Edit akun
    // ------------------------------------------------------------

    /**
     * Halaman edit akun (data diri + ganti password)
     */
    public function edit(): void
    {
        Auth::requireStudent();
        $user = Auth::user();
        $row  = (new User())->find((int) $user['id']) ?? [];

        $old    = Session::flash('old');
        $errors = Session::flash('field_errors');
        $tab    = Session::flash('tab');

        $this->view('student/profile_edit', [
            'title'  => 'Edit Profil - TIF Hub',
            'user'   => $user,
            'row'    => $row,
            'old'    => is_array($old) ? $old : [],
            'errors' => is_array($errors) ? $errors : [],
            'tab'    => in_array($tab, ['profile', 'security'], true) ? $tab : 'profile',
            'csrf'   => Session::getCsrfToken(),
        ]);
    }

    /**
     * Simpan perubahan data diri
     */
    public function update(): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->editError('profile', ['_' => 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.']);
        }

        $userId    = (int) Auth::id();
        $userModel = new User();

        $name     = trim((string) $this->input('name', ''));
        $email    = trim((string) $this->input('email', ''));
        $class    = strtoupper(trim((string) $this->input('class', '')));
        $semester = (int) $this->input('semester', 0);

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($name) < 3) {
            $errors['name'] = 'Nama minimal 3 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama maksimal 100 karakter.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif (mb_strlen($email) > 150) {
            $errors['email'] = 'Email maksimal 150 karakter.';
        } else {
            $existing = $userModel->findByEmail($email);
            if ($existing && (int) $existing['id'] !== $userId) {
                $errors['email'] = 'Email ini sudah dipakai akun lain.';
            }
        }

        if ($class !== '' && !preg_match('/^[A-Z0-9\- ]{1,20}$/', $class)) {
            $errors['class'] = 'Kelas hanya boleh huruf, angka, spasi, atau tanda minus (maks. 20 karakter).';
        }

        if ($semester < 1 || $semester > 14) {
            $errors['semester'] = 'Semester harus antara 1 sampai 14.';
        }

        if ($errors) {
            $this->editError('profile', $errors, [
                'name' => $name, 'email' => $email, 'class' => $class, 'semester' => $semester,
            ]);
        }

        $userModel->update($userId, [
            'name'     => $name,
            'email'    => $email,
            'class'    => $class !== '' ? $class : null,
            'semester' => $semester,
        ]);

        // Sinkronkan session supaya navbar & profil langsung berubah
        Session::set('user_name', $name);
        Session::set('user_email', $email);
        Session::set('user_class', $class !== '' ? $class : null);
        Session::set('user_semester', $semester);

        $this->redirectWithSuccess('/profile', 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password (wajib password lama)
     */
    public function updatePassword(): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->editError('security', ['_' => 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.']);
        }

        $userId    = (int) Auth::id();
        $userModel = new User();
        $row       = $userModel->find($userId);

        $current = (string) $this->input('current_password', '');
        $new     = (string) $this->input('new_password', '');
        $confirm = (string) $this->input('new_password_confirm', '');

        $errors = [];

        if (!$row || $current === '' || !$userModel->verifyPassword($current, $row['password'])) {
            $errors['current_password'] = 'Password saat ini salah.';
        }

        if (strlen($new) < 6) {
            $errors['new_password'] = 'Password baru minimal 6 karakter.';
        } elseif ($current !== '' && hash_equals($current, $new)) {
            $errors['new_password'] = 'Password baru tidak boleh sama dengan yang lama.';
        }

        if ($new !== $confirm) {
            $errors['new_password_confirm'] = 'Konfirmasi password tidak cocok.';
        }

        if ($errors) {
            $this->editError('security', $errors);
        }

        $userModel->update($userId, ['password' => $userModel->hashPassword($new)]);

        // Ganti ID session setelah perubahan kredensial
        session_regenerate_id(true);

        $this->redirectWithSuccess('/profile', 'Password berhasil diganti.');
    }

    /**
     * Kembali ke halaman edit dengan pesan error per kolom
     */
    private function editError(string $tab, array $errors, array $old = []): void
    {
        Session::flash('error', $errors['_'] ?? 'Periksa kembali isian kamu, ada yang belum sesuai.');
        Session::flash('field_errors', $errors);
        Session::flash('tab', $tab);
        if ($old) {
            Session::flash('old', $old);
        }
        $this->redirect('/profile/edit');
    }

    /** Pesan error lalu kembali ke halaman profil */
    private function profileError(string $message): void
    {
        Session::flash('error', $message);
        $this->redirect('/profile');
    }
}