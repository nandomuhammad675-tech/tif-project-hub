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

    /** Pesan error lalu kembali ke halaman profil */
    private function profileError(string $message): void
    {
        Session::flash('error', $message);
        $this->redirect('/profile');
    }
}