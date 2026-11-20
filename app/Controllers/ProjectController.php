<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Project;
use App\Models\Category;
use App\Models\Semester;

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
        $userProject = $this->projectModel->getUserProject((int) Auth::id());
        $isTaken = (int)($project['member_count'] ?? 0) > 0;
        $userHasThis = false;
        foreach ($members as $m) {
            if ((int)$m['id'] === (int)Auth::id()) {
                $userHasThis = true;
                break;
            }
        }

        $this->view('projects/show', [
            'title'       => $project['title'] . ' - TIF Hub',
            'user'        => Auth::user(),
            'project'     => $project,
            'members'     => $members,
            'images'      => $this->projectModel->getImages((int) $project['id']),
            'isTaken'     => $isTaken,
            'userHasThis' => $userHasThis,
            'userProject' => $userProject,
            'csrf'        => Session::getCsrfToken(),
        ]);
    }

    public function claim(string $id): void
    {
        Auth::requireStudent();
        Session::flash('info', 'Klaim tiket hanya dapat dilakukan dari tombol KLAIM TIKET SEKARANG di Beranda.');
        $this->redirect('/');
    }
}
