<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Project;

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

        $this->view('student/profile', [
            'title'      => 'Profil Saya - TIF Hub',
            'user'       => $user,
            'myProjects' => $myProjects,
        ]);
    }
}
