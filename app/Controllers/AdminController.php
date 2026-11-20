<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Project;
use App\Models\Category;
use App\Models\Semester;
use App\Models\WarSetting;
use App\Models\ProjectClaim;
use App\Models\ClaimViolation;
use App\Helpers\ImageUploader;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $userModel = new User();
        $projectModel = new Project();

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard - TIF Hub',
            'user'  => Auth::user(),
            'recentProjects' => array_slice($projectModel->getAllAdmin(), 0, 5),
            'stats' => [
                'students'       => $userModel->countStudents(),
                'projects'       => $projectModel->countAll(),
            ],
        ]);
    }

    // ========== STUDENTS ==========
    public function students(): void
    {
        Auth::requireAdmin();
        $userModel = new User();

        $this->view('admin/students/index', [
            'title'    => 'Kelola Mahasiswa - TIF Hub',
            'user'     => Auth::user(),
            'students' => $userModel->getStudents(),
        ]);
    }

    public function studentCreate(): void
    {
        Auth::requireAdmin();
        $this->view('admin/students/form', [
            'title'   => 'Tambah Mahasiswa - TIF Hub',
            'user'    => Auth::user(),
            'student' => null,
            'csrf'    => Session::getCsrfToken(),
        ]);
    }

    public function studentStore(): void
    {
        Auth::requireAdmin();
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
        }

        $userModel = new User();
        $data = [
            'nim'      => trim($this->input('nim', '')),
            'name'     => trim($this->input('name', '')),
            'email'    => trim($this->input('email', '')),
            'password' => $this->input('password', ''),
            'class'    => trim($this->input('class', '')),
            'semester' => (int) $this->input('semester', 1),
            'role'     => 'mahasiswa',
            'status'   => $this->input('status', 'active'),
        ];

        if ($data['name'] === '' || $data['email'] === '' || $data['password'] === '') {
            Session::flash('error', 'Nama, email, dan password wajib diisi.');
            $this->redirect('/admin/students/create');
        }

        if ($userModel->findByEmail($data['email'])) {
            Session::flash('error', 'Email sudah terdaftar.');
            $this->redirect('/admin/students/create');
        }

        $userModel->createUser($data);
        Session::flash('success', 'Mahasiswa berhasil ditambahkan.');
        $this->redirect('/admin/students');
    }

    public function studentEdit(string $id): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $student = $userModel->find((int) $id);

        if (!$student || $student['role'] !== 'mahasiswa') {
            Session::flash('error', 'Mahasiswa tidak ditemukan.');
            $this->redirect('/admin/students');
        }

        $this->view('admin/students/form', [
            'title'   => 'Edit Mahasiswa - TIF Hub',
            'user'    => Auth::user(),
            'student' => $student,
            'csrf'    => Session::getCsrfToken(),
        ]);
    }

    public function studentUpdate(string $id): void
    {
        Auth::requireAdmin();
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
        }

        $userModel = new User();
        $student = $userModel->find((int) $id);
        if (!$student || $student['role'] !== 'mahasiswa') {
            $this->redirect('/admin/students');
        }

        $data = [
            'nim'      => trim($this->input('nim', '')),
            'name'     => trim($this->input('name', '')),
            'email'    => trim($this->input('email', '')),
            'class'    => trim($this->input('class', '')),
            'semester' => (int) $this->input('semester', 1),
            'status'   => $this->input('status', 'active'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $password = $this->input('password', '');
        if ($password !== '') {
            $data['password'] = $userModel->hashPassword($password);
        }

        $userModel->update((int) $id, $data);
        Session::flash('success', 'Data mahasiswa diperbarui.');
        $this->redirect('/admin/students');
    }

    public function studentDelete(string $id): void
    {
        Auth::requireAdmin();
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
        }

        $userModel = new User();
        $student = $userModel->find((int) $id);
        if ($student && $student['role'] === 'mahasiswa') {
            $userModel->update((int) $id, ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
            Session::flash('success', 'Mahasiswa dinonaktifkan.');
        }
        $this->redirect('/admin/students');
    }

    /**
     * Jika total upload melebihi post_max_size, PHP mengosongkan $_POST.
     * Tanpa pengecekan ini admin hanya melihat "Token tidak valid".
     */
    private function checkPostTooLarge(string $redirectTo): void
    {
        if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            Session::flash('error', 'Total ukuran foto terlalu besar (batas server ' . ini_get('post_max_size') . '). Kurangi jumlah/ukuran foto.');
            $this->redirect($redirectTo);
        }
    }

    /**
     * Proses upload foto + tampilkan pesan error bila ada yang ditolak
     */
    private function handleImageUpload(Project $projectModel, int $projectId): int
    {
        $result = ImageUploader::saveMany('images');
        if ($result['saved']) {
            $projectModel->addImages($projectId, $result['saved']);
        }
        if ($result['errors']) {
            Session::flash('error', implode(' ', $result['errors']));
        }
        return count($result['saved']);
    }

    // ========== PROJECTS ==========
    public function projects(): void
    {
        Auth::requireAdmin();
        $projectModel = new Project();

        $this->view('admin/projects/index', [
            'title'    => 'Kelola Proyek - TIF Hub',
            'user'     => Auth::user(),
            'projects' => $projectModel->getAllAdmin(),
        ]);
    }

    public function projectCreate(): void
    {
        Auth::requireAdmin();
        $this->view('admin/projects/form', [
            'title'      => 'Tambah Proyek - TIF Hub',
            'user'       => Auth::user(),
            'project'    => null,
            'images'     => [],
            'members'    => [],
            'categories' => (new Category())->forProjects(),
            'semesters'  => (new Semester())->allOrdered(),
            'students'   => (new User())->getStudents(),
            'csrf'       => Session::getCsrfToken(),
        ]);
    }

    public function projectStore(): void
    {
        Auth::requireAdmin();
        $this->checkPostTooLarge('/admin/projects/create');
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
        }

        $projectModel = new Project();
        $data = [
            'title'         => trim($this->input('title', '')),
            'description'   => trim($this->input('description', '')),
            'short_desc'    => trim($this->input('short_desc', '')),
            'category_id'   => (int) $this->input('category_id', 0) ?: null,
            'semester_id'   => (int) $this->input('semester_id', 0) ?: null,
            'course_name'   => trim($this->input('course_name', '')),
            'academic_year' => trim($this->input('academic_year', '')),
            'technologies'  => trim($this->input('technologies', '')),
            'github_url'    => trim($this->input('github_url', '')),
            'demo_url'      => trim($this->input('demo_url', '')),
            'is_published'  => (int) $this->input('is_published', 1),
            'created_by'    => Auth::id(),
        ];

        if ($data['title'] === '' || $data['description'] === '') {
            Session::flash('error', 'Judul dan deskripsi wajib diisi.');
            $this->redirect('/admin/projects/create');
        }

        $id = $projectModel->createProject($data);

        $memberIds = array_filter(array_map('intval', (array) ($this->input('members', []) ?: [])));
        if ($memberIds) {
            $projectModel->syncMembers($id, $memberIds);
        }

        $uploaded = $this->handleImageUpload($projectModel, $id);
        // Foto pertama yang diunggah otomatis menjadi cover.

        if (!Session::has('_flash_error')) {
            Session::flash('success', 'Proyek berhasil ditambahkan' . ($uploaded ? " dengan {$uploaded} foto." : '.'));
        }
        $this->redirect('/admin/projects');
    }

    public function projectEdit(string $id): void
    {
        Auth::requireAdmin();
        $projectModel = new Project();
        $project = $projectModel->findWithDetails((int) $id);

        if (!$project) {
            Session::flash('error', 'Proyek tidak ditemukan.');
            $this->redirect('/admin/projects');
        }

        $members = $projectModel->getMembers((int) $id);
        $memberIds = array_column($members, 'id');

        $this->view('admin/projects/form', [
            'title'      => 'Edit Proyek - TIF Hub',
            'user'       => Auth::user(),
            'project'    => $project,
            'images'     => $projectModel->getImages((int) $id),
            'members'    => $memberIds,
            'categories' => (new Category())->forProjects(),
            'semesters'  => (new Semester())->allOrdered(),
            'students'   => (new User())->getStudents(),
            'csrf'       => Session::getCsrfToken(),
        ]);
    }

    public function projectUpdate(string $id): void
    {
        Auth::requireAdmin();
        $this->checkPostTooLarge('/admin/projects/' . (int) $id . '/edit');
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
        }

        $projectModel = new Project();
        $project = $projectModel->find((int) $id);
        if (!$project) {
            $this->redirect('/admin/projects');
        }

        $data = [
            'title'         => trim($this->input('title', '')),
            'description'   => trim($this->input('description', '')),
            'short_desc'    => trim($this->input('short_desc', '')),
            'category_id'   => (int) $this->input('category_id', 0) ?: null,
            'semester_id'   => (int) $this->input('semester_id', 0) ?: null,
            'course_name'   => trim($this->input('course_name', '')),
            'academic_year' => trim($this->input('academic_year', '')),
            'technologies'  => trim($this->input('technologies', '')),
            'github_url'    => trim($this->input('github_url', '')),
            'demo_url'      => trim($this->input('demo_url', '')),
            'is_published'  => (int) $this->input('is_published', 1),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $projectModel->update((int) $id, $data);

        $memberIds = array_filter(array_map('intval', (array) ($this->input('members', []) ?: [])));
        $projectModel->syncMembers((int) $id, $memberIds);

        // Foto: hapus yang dicentang, tambah baru, lalu atur cover
        $projectModel->deleteImages((int) $id, (array) ($this->input('delete_images', []) ?: []));
        $projectModel->reorderImages((int) $id, (array) ($this->input('image_order', []) ?: []));
        $this->handleImageUpload($projectModel, (int) $id);
        $coverId = (int) $this->input('cover_image', 0);
        if ($coverId > 0) {
            $projectModel->setCover((int) $id, $coverId);
        }

        if (!Session::has('_flash_error')) {
            Session::flash('success', 'Proyek berhasil diperbarui.');
        }
        $this->redirect('/admin/projects');
    }

    public function projectDelete(string $id): void
    {
        Auth::requireAdmin();
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
        }

        $projectModel = new Project();
        $projectModel->deleteWithImages((int) $id);
        Session::flash('success', 'Proyek dihapus.');
        $this->redirect('/admin/projects');
    }

    // ========== PROJECT WAR ==========
    public function war(): void
    {
        Auth::requireAdmin();
        $warModel = new WarSetting();
        $claimModel = new ProjectClaim();
        $war = $warModel->current();
        $semesterId = $war['active_semester_id'] ?? null;
        $claimedCount = $semesterId ? $claimModel->countClaimedInSemester((int) $semesterId) : 0;
        $availableCount = $semesterId ? count($claimModel->getAvailableProjects((int) $semesterId)) : 0;

        $this->view('admin/war/index', [
            'title'          => 'Project War - Admin',
            'user'           => Auth::user(),
            'war'            => $war,
            'warStatus'      => $warModel->statusLabel($war),
            'semesters'      => (new Semester())->allOrdered(),
            'claimedCount'   => $claimedCount,
            'availableCount' => $availableCount,
            'csrf'           => Session::getCsrfToken(),
        ]);
    }

    public function warUpdate(): void
    {
        Auth::requireAdmin();
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
            return;
        }

        $starts = trim($this->input('starts_at', ''));
        $ends = trim($this->input('ends_at', ''));
        $starts = $starts !== '' ? str_replace('T', ' ', $starts) . (strlen($starts) === 16 ? ':00' : '') : null;
        $ends = $ends !== '' ? str_replace('T', ' ', $ends) . (strlen($ends) === 16 ? ':00' : '') : null;

        $data = [
            'is_open'            => (int) $this->input('is_open', 0),
            'starts_at'          => $starts,
            'ends_at'            => $ends,
            'active_semester_id' => (int) $this->input('active_semester_id', 0) ?: null,
        ];

        (new WarSetting())->updateSettings($data, (int) Auth::id());
        Session::flash('success', 'Pengaturan Project War disimpan.');
        $this->redirect('/admin/war');
    }

    public function claims(): void
    {
        Auth::requireAdmin();
        $semesterId = $this->query('semester_id', '');
        $claimModel = new ProjectClaim();

        $this->view('admin/claims/index', [
            'title'     => 'Tiket Klaim - Admin',
            'user'      => Auth::user(),
            'claims'    => $claimModel->getAllAdmin($semesterId !== '' ? (int) $semesterId : null),
            'semesters' => (new Semester())->allOrdered(),
            'semesterId'=> $semesterId,
        ]);
    }

    public function violations(): void
    {
        Auth::requireAdmin();
        $status = $this->query('status', '');
        $model = new ClaimViolation();

        $this->view('admin/violations/index', [
            'title'      => 'Pelanggaran Klaim - Admin',
            'user'       => Auth::user(),
            'violations' => $model->getAll($status !== '' ? $status : null),
            'status'     => $status,
            'csrf'       => Session::getCsrfToken(),
        ]);
    }

    public function violationReview(string $id): void
    {
        Auth::requireAdmin();
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
            return;
        }

        $status = $this->input('status', '');
        $note = trim($this->input('admin_note', ''));
        $penalty = $this->input('penalty_points', '');
        $penaltyVal = $penalty === '' ? null : (float) $penalty;

        $ok = (new ClaimViolation())->review((int) $id, $status, $penaltyVal, $note !== '' ? $note : null, (int) Auth::id());
        if ($ok) {
            Session::flash('success', 'Pelanggaran berhasil ditinjau.');
        } else {
            Session::flash('error', 'Gagal meninjau pelanggaran (mungkin sudah diproses).');
        }
        $this->redirect('/admin/violations');
    }
}
