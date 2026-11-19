<h1 style="font-size:1.4rem;font-weight:800;margin-bottom:1.25rem;">Profil Saya</h1>

<div class="card mb-3">
    <div class="flex gap-2" style="align-items:flex-start;">
        <span class="avatar" style="width:76px;height:76px;font-size:1.6rem;"><?= e(initials($user['name'])) ?></span>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700;"><?= e($user['name']) ?></h2>
            <p class="text-muted text-sm"><?= e($user['email']) ?></p>
            <div class="flex gap-1 mt-1" style="flex-wrap:wrap;">
                <span class="badge badge-primary">NIM: <?= e($user['nim'] ?? '-') ?></span>
                <span class="badge badge-secondary">Kelas: <?= e($user['class'] ?? '-') ?></span>
                <span class="badge badge-secondary">Semester: <?= e((string)($user['semester'] ?? '-')) ?></span>
                <span class="badge badge-info">Teknik Informatika</span>
            </div>
        </div>
    </div>
</div>

<div class="section-title"><span>Proyek Saya</span></div>
<?php if (empty($myProjects)): ?>
<div class="empty-state">
    <div class="icon">📂</div>
    <p>Belum ada proyek yang terkait dengan akun Anda.</p>
</div>
<?php else: ?>
<div class="project-grid">
    <?php foreach ($myProjects as $p) { $opts = ['role' => $p['role_in_project']]; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>
<?php endif; ?>
