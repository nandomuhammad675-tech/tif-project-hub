<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\ProjectClaim;
use App\Models\WarSetting;

class ClaimController extends Controller
{
    private ProjectClaim $claimModel;
    private WarSetting $warModel;

    public function __construct()
    {
        $this->claimModel = new ProjectClaim();
        $this->warModel = new WarSetting();
    }

    public function select(): void
    {
        Auth::requireStudent();

        $war = $this->warModel->current();
        $warActive = $this->warModel->isWarActive($war);
        $userId = (int) Auth::id();

        $userClaim = null;
        if ($war && !empty($war['active_semester_id'])) {
            $userClaim = $this->claimModel->findByUserAndSemester($userId, (int) $war['active_semester_id']);
        }

        if ($userClaim) {
            Session::flash('info', 'Anda sudah memiliki tiket proyek pada semester aktif.');
            $this->redirect('/profile');
            return;
        }

        if (!$warActive) {
            Session::flash('error', 'Periode war belum aktif. Klaim tidak dapat dilakukan.');
            $this->redirect('/');
            return;
        }

        $projects = $this->claimModel->getAvailableProjects((int) $war['active_semester_id']);

        $this->view('claims/select', [
            'title'    => 'Pilih Proyek - Klaim Tiket',
            'user'     => Auth::user(),
            'war'      => $war,
            'projects' => $projects,
            'csrf'     => Session::getCsrfToken(),
        ]);
    }

    public function store(): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid.');
            return;
        }

        $projectId = (int) $this->input('project_id', 0);
        if ($projectId <= 0) {
            Session::flash('error', 'Pilih satu proyek terlebih dahulu.');
            $this->redirect('/claims/select');
            return;
        }

        $result = $this->claimModel->claim($projectId, (int) Auth::id());

        if ($result['success']) {
            Session::flash('success', $result['message']);
            if (!empty($result['claim_id'])) {
                $this->redirect('/claims/' . $result['claim_id']);
                return;
            }
            $this->redirect('/profile');
            return;
        }

        Session::flash('error', $result['message']);
        $this->redirect('/claims/select');
    }

    public function show(string $id): void
    {
        Auth::requireStudent();

        $claim = $this->claimModel->findOwnedByUser((int) $id, (int) Auth::id());
        if (!$claim) {
            http_response_code(404);
            echo 'Tiket tidak ditemukan.';
            return;
        }

        $this->view('claims/ticket', [
            'title' => 'Tiket ' . $claim['ticket_code'] . ' - TIF Hub',
            'user'  => Auth::user(),
            'claim' => $claim,
        ]);
    }
}
