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


<div class="section-title"><span>🎫 Tiket Proyek Saya</span></div>
<?php if (empty($claims)): ?>
<div class="empty-state mb-3">
    <div class="icon">🎫</div>
    <p>Kamu belum memiliki tiket proyek pada semester ini.</p>
    <p class="text-sm text-muted">Klaim tiket melalui tombol <strong>KLAIM TIKET SEKARANG</strong> di Beranda saat periode war aktif.</p>
    <a href="/#klaim-tiket" class="btn btn-outline btn-sm mt-1">Ke Beranda</a>
</div>
<?php else: ?>
<div class="card mb-3" style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.92rem;">
        <thead>
            <tr style="text-align:left;border-bottom:1px solid #e2e8f0;">
                <th style="padding:.6rem;">ID Tiket</th>
                <th style="padding:.6rem;">Proyek</th>
                <th style="padding:.6rem;">Semester</th>
                <th style="padding:.6rem;">Tanggal Klaim</th>
                <th style="padding:.6rem;">Status</th>
                <th style="padding:.6rem;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($claims as $c): ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:.6rem;"><code><?= e($c['ticket_code']) ?></code></td>
                <td style="padding:.6rem;"><?= e($c['project_title']) ?></td>
                <td style="padding:.6rem;"><?= e($c['semester_name'] ?? '-') ?><?= !empty($c['academic_year']) ? ' · ' . e($c['academic_year']) : '' ?></td>
                <td style="padding:.6rem;"><?= e(format_datetime($c['claimed_at'])) ?></td>
                <td style="padding:.6rem;">
                    <?php if (($c['status'] ?? '') === 'claimed'): ?>
                        <span class="badge badge-success">Terklaim</span>
                    <?php else: ?>
                        <span class="badge badge-secondary"><?= e($c['status'] ?? '-') ?></span>
                    <?php endif; ?>
                </td>
                <td style="padding:.6rem;"><a href="/claims/<?= (int)$c['id'] ?>" class="btn btn-outline btn-sm">Lihat detail</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

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
