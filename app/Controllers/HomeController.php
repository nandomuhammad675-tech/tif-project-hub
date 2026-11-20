<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Project;
use App\Models\ProjectClaim;
use App\Models\WarSetting;

class HomeController extends Controller
{
    public function index(): void
    {
        Auth::requireStudent();

        $projectModel = new Project();
        $warModel = new WarSetting();
        $claimModel = new ProjectClaim();

        $war = $warModel->current();
        $warActive = $warModel->isWarActive($war);
        $warStatus = $warModel->statusLabel($war);

        $userClaim = null;
        $availableCount = 0;
        if ($war && !empty($war['active_semester_id'])) {
            $userClaim = $claimModel->findByUserAndSemester((int) Auth::id(), (int) $war['active_semester_id']);
            $availableCount = count($claimModel->getAvailableProjects((int) $war['active_semester_id']));
        }

        $this->view('student/home', [
            'title'           => 'TIF Hub - Beranda',
            'user'            => Auth::user(),
            'latestProjects'  => $projectModel->getLatest(6),
            'popularProjects' => $projectModel->getPopular(6),
            'categories'      => $projectModel->getByCategory(),
            'semesters'       => $projectModel->getBySemester(),
            'war'             => $war,
            'warActive'       => $warActive,
            'warStatus'       => $warStatus,
            'userClaim'       => $userClaim,
            'availableCount'  => $availableCount,
        ]);
    }
}
