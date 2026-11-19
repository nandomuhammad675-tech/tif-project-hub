<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Project;

class HomeController extends Controller
{
    public function index(): void
    {
        Auth::requireStudent();

        $projectModel = new Project();

        $this->view('student/home', [
            'title'          => 'TIF Hub - Beranda',
            'user'           => Auth::user(),
            'latestProjects' => $projectModel->getLatest(6),
            'popularProjects'=> $projectModel->getPopular(6),
            'categories'     => $projectModel->getByCategory(),
            'semesters'      => $projectModel->getBySemester(),
        ]);
    }
}
