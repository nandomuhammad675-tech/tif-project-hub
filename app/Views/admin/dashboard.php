<?php
$svg = [
    'users'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'projects' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
];
?>
<div class="page-head">
    <div>
        <h1>Halo, <?= e(explode(' ', trim($user['name']))[0]) ?></h1>
        <p>Ringkasan aktivitas TIF Hub hari ini.</p>
    </div>
    <a href="/admin/projects/create" class="btn btn-primary"><?= icon('plus') ?> Tambah Proyek</a>
</div>

<div class="kpi-grid">
    <div class="kpi k1"><div class="kpi-icon"><?= $svg['users'] ?></div><div class="kpi-value" data-count="<?= (int) $stats['students'] ?>">0</div><div class="kpi-label">Total Mahasiswa</div></div>
    <div class="kpi k2"><div class="kpi-icon"><?= $svg['projects'] ?></div><div class="kpi-value" data-count="<?= (int) $stats['projects'] ?>">0</div><div class="kpi-label">Total Proyek</div></div>
</div>

<div class="dash-grid">
    <div class="card">
        <div class="card-title" style="justify-content:space-between;">
            <span>Proyek Terbaru</span>
            <a href="/admin/projects" class="text-sm">Lihat semua →</a>
        </div>
        <?php if (empty($recentProjects)): ?>
            <p class="text-muted text-sm">Belum ada proyek.</p>
        <?php else: ?>
        <ul class="mini-list">
            <?php foreach ($recentProjects as $p): ?>
            <li>
                <div class="mini-thumb"><?= project_thumb($p + ['category_icon' => null]) ?></div>
                <div class="mini-body">
                    <strong><?= e($p['title']) ?></strong>
                    <span><?= e($p['category_name'] ?? 'Tanpa kategori') ?> · <?= (int) ($p['image_count'] ?? 0) ?> foto · <?= format_date($p['created_at']) ?></span>
                </div>
                <a href="/admin/projects/<?= (int) $p['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
