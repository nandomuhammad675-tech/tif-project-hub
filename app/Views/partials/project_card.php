<?php
/** @var array $p  data proyek
 *  @var array $opts ['role' => string|null]  (opsional, untuk halaman profil) */
$taken = (int) ($p['member_count'] ?? 0) > 0;
$imgCount = (int) ($p['image_count'] ?? 0);
?>
<article class="project-card">
    <a href="/projects/<?= e($p['slug']) ?>" class="thumb" aria-label="<?= e($p['title']) ?>">
        <?= project_thumb($p) ?>
        <?php if (!isset($opts['role'])): ?>
        <span class="thumb-status">
            <?= $taken ? '<span class="badge badge-glass" style="color:#92400e">● Sudah dikerjakan</span>' : '<span class="badge badge-glass" style="color:#065f46">● Tersedia</span>' ?>
        </span>
        <?php endif; ?>
    </a>
    <div class="body">
        <div class="badges">
            <span class="badge badge-primary"><?= e($p['category_name'] ?? 'Lainnya') ?></span>
            <?php if (isset($opts['role'])): ?>
                <span class="badge badge-secondary"><?= e($opts['role']) ?></span>
            <?php else: ?>
                <span class="badge badge-secondary"><?= e($p['semester_name'] ?? '-') ?></span>
            <?php endif; ?>
        </div>
        <h3><?= e($p['title']) ?></h3>
        <p class="desc"><?= e($p['short_desc'] ?: truncate($p['description'] ?? '', 90)) ?></p>
        <div class="tech"><?= tech_tags($p['technologies'] ?? '') ?></div>
        <div class="actions">
            <a href="/projects/<?= e($p['slug']) ?>" class="btn btn-primary btn-sm btn-block">Lihat Detail →</a>
        </div>
    </div>
</article>
