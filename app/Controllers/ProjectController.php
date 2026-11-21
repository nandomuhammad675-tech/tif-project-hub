<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Helpers\ImageUploader;
use App\Models\Project;
use App\Models\Category;
use App\Models\Semester;
use App\Models\ProjectClaim;
use App\Models\WarSetting;
use App\Models\Wishlist;

class ProjectController extends Controller
{
    private Project $projectModel;
    private Category $categoryModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->categoryModel = new Category();
        $this->semesterModel = new Semester();
    }

    /**
     * Helper untuk memvalidasi apakah user adalah pemilik klaim proyek yang sah
     */
    private function validateClaimOwnership(int $projectId): bool
    {
        $war = (new WarSetting())->current();
        $semesterId = (int) ($war['active_semester_id'] ?? 0);
        if ($semesterId <= 0) {
            return false;
        }

        return $this->projectModel->hasActiveClaim($projectId, (int) Auth::id(), $semesterId);
    }

    private function inputString(string $key): string
    {
        $value = $this->input($key, '');
        return is_string($value) ? trim($value) : '';
    }

    private function isValidUrl(string $url): bool
    {
        if ($url === '') {
            return true;
        }

        $parts = parse_url($url);
        return $parts !== false
            && isset($parts['scheme'], $parts['host'])
            && in_array(strtolower($parts['scheme']), ['http', 'https'], true);
    }

    private function checkPostTooLarge(string $redirectTo): void
    {
        if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            Session::flash('error', 'Total ukuran foto terlalu besar (batas server ' . ini_get('post_max_size') . '). Kurangi jumlah/ukuran foto.');
            $this->redirect($redirectTo);
        }
    }

    private function handleImageUpload(int $projectId): int
    {
        $result = ImageUploader::saveMany('images');
        if ($result['saved']) {
            $this->projectModel->addImages($projectId, $result['saved']);
        }
        if ($result['errors']) {
            Session::flash('error', implode(' ', $result['errors']));
        }
        return count($result['saved']);
    }

    public function index(): void
    {
        Auth::requireStudent();

        $filters = [
            'q'            => trim($this->query('q', '')),
            'category_id'  => $this->query('category_id', ''),
            'semester_id'  => $this->query('semester_id', ''),
            'course'       => trim($this->query('course', '')),
            'tech'         => trim($this->query('tech', '')),
            'availability' => $this->query('availability', ''),
        ];
        $sort = $this->query('sort', 'newest');
        $page = max(1, (int) $this->query('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $projects = $this->projectModel->getPublished($filters, $sort, $perPage, $offset);
        $total = $this->projectModel->countPublished($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $userProject = $this->projectModel->getUserProject((int) Auth::id());

        $this->view('projects/index', [
            'title'       => 'Katalog Proyek - TIF Hub',
            'user'        => Auth::user(),
            'projects'    => $projects,
            'categories'  => $this->categoryModel->forProjects(),
            'semesters'   => $this->semesterModel->allOrdered(),
            'filters'     => $filters,
            'sort'        => $sort,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'userProject' => $userProject,
        ]);
    }

    public function show(string $slug): void
    {
        Auth::requireStudent();

        $project = $this->projectModel->findBySlug($slug);
        if (!$project) {
            http_response_code(404);
            echo 'Proyek tidak ditemukan.';
            return;
        }

        $this->projectModel->incrementViews((int) $project['id']);
        $members = $this->projectModel->getMembers((int) $project['id']);

        $wishlistModel = new Wishlist();
        $isWishlisted = $wishlistModel->isWishlisted((int) Auth::id(), (int) $project['id']);

        // Cek hak edit berdasarkan klaim yang sah
        $canEdit = $this->validateClaimOwnership((int)$project['id']);

        $this->view('projects/show', [
            'title'        => $project['title'] . ' - TIF Hub',
            'user'         => Auth::user(),
            'project'      => $project,
            'members'      => $members,
            'images'       => $this->projectModel->getImages((int) $project['id']),
            'isTaken'      => (int)($project['member_count'] ?? 0) > 0,
            'canEdit'      => $canEdit,
            'isWishlisted' => $isWishlisted,
            'csrf'         => Session::getCsrfToken(),
        ]);
    }

    public function claim(string $id): void
    {
        Auth::requireStudent();
        Session::flash('info', 'Klaim tiket hanya dapat dilakukan dari tombol KLAIM TIKET SEKARANG di Beranda.');
        $this->redirect('/');
    }

    public function edit(string $id): void
    {
        Auth::requireStudent();
        $project = $this->projectModel->find((int)$id);
        if (!$project) {
            $this->redirect('/projects');
            return;
        }

        if (!$this->validateClaimOwnership((int)$id)) {
            Session::flash('error', 'Akses ditolak. Anda tidak memiliki klaim aktif atas proyek ini.');
            $this->redirect('/projects/' . $project['slug']);
            return;
        }

        $this->view('projects/edit', [
            'title'   => 'Edit Proyek - TIF Hub',
            'user'    => Auth::user(),
            'project' => $project,
            'images'  => $this->projectModel->getImages((int) $id),
            'limits'  => ImageUploader::limits(),
            'csrf'    => Session::getCsrfToken(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireStudent();
        $this->checkPostTooLarge('/projects/' . (int) $id . '/edit');
        if (!$this->validateCsrf()) {
            $this->backWithError('Token tidak valid.');
            return;
        }

        $project = $this->projectModel->find((int)$id);
        if (!$project || !$this->validateClaimOwnership((int)$id)) {
            Session::flash('error', 'Akses ditolak.');
            $this->redirect('/projects');
            return;
        }

        $data = [
            'title'        => $this->inputString('title'),
            'short_desc'   => $this->inputString('short_desc'),
            'description'  => $this->inputString('description'),
            'technologies' => $this->inputString('technologies'),
            'github_url'   => $this->inputString('github_url'),
            'demo_url'     => $this->inputString('demo_url'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($data['title'] === '' || $data['description'] === '') {
            Session::flash('error', 'Judul dan deskripsi wajib diisi.');
            $this->redirect('/projects/' . (int) $id . '/edit');
            return;
        }

        if (mb_strlen($data['title']) > 200
            || mb_strlen($data['short_desc']) > 300
            || mb_strlen($data['technologies']) > 500
            || mb_strlen($data['github_url']) > 500
            || mb_strlen($data['demo_url']) > 500) {
            Session::flash('error', 'Judul, ringkasan, teknologi, atau tautan terlalu panjang.');
            $this->redirect('/projects/' . (int) $id . '/edit');
            return;
        }

        if (!$this->isValidUrl($data['github_url']) || !$this->isValidUrl($data['demo_url'])) {
            Session::flash('error', 'Tautan GitHub dan demo harus menggunakan http:// atau https://.');
            $this->redirect('/projects/' . (int) $id . '/edit');
            return;
        }

        $this->projectModel->updateStudentFields((int)$id, $data);

        $this->projectModel->deleteImages((int)$id, (array) ($this->input('delete_images', []) ?: []));
        $this->projectModel->reorderImages((int)$id, (array) ($this->input('image_order', []) ?: []));
        $uploaded = $this->handleImageUpload((int)$id);
        $coverId = (int) $this->input('cover_image', 0);
        if ($coverId > 0) {
            $this->projectModel->setCover((int)$id, $coverId);
        }

        if (!Session::has('_flash_error')) {
            Session::flash('success', 'Proyek berhasil diperbarui.' . ($uploaded ? " dengan {$uploaded} foto baru." : ''));
        }
        $this->redirect('/projects/' . $project['slug']);
    }
}
