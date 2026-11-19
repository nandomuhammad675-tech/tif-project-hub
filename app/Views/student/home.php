<?php
$totalProjects = 0; foreach ($categories as $c) { $totalProjects += (int) $c['total']; }
?>
<div class="hero">
    <img src="<?= asset('images/logo-jti.png') ?>" alt="JTI" class="hero-logo">
    <h1>Jelajahi Karya<br>Mahasiswa TIF</h1>
    <p>Temukan, lihat, dan ambil proyek terbaik dari Program Studi Teknik Informatika.</p>
    <form class="hero-search" method="GET" action="/projects">
        <input type="text" name="q" placeholder="Cari proyek atau teknologi…" aria-label="Cari proyek">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>
    <div class="hero-stats">
        <div><strong><?= (int) $totalProjects ?></strong>Proyek</div>
        <div><strong><?= count($categories) ?></strong>Kategori</div>
        <div><strong><?= count($semesters) ?></strong>Semester</div>
    </div>
</div>

<div class="section-title">
    <span>🔥 Proyek Terbaru</span>
    <a href="/projects">Lihat Semua →</a>
</div>
<div class="project-grid">
    <?php foreach ($latestProjects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<div class="section-title"><span>⭐ Proyek Populer</span></div>
<div class="project-grid">
    <?php foreach ($popularProjects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<div class="section-title"><span>📂 Kategori</span></div>
<div class="stats-grid">
    <?php foreach ($categories as $c): ?>
    <a href="/projects?category_id=<?= (int) $c['id'] ?>" class="stat-card" style="color:inherit;">
        <div class="label"><?= e(category_icon($c['icon'])) ?> <?= e($c['name']) ?></div>
        <div class="value primary"><?= (int) $c['total'] ?></div>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!empty($semesters)): ?>
<div class="section-title"><span>📅 Berdasarkan Semester</span></div>
<div class="stats-grid">
    <?php foreach ($semesters as $s): ?>
    <a href="/projects?semester_id=<?= (int) $s['id'] ?>" class="stat-card" style="color:inherit;">
        <div class="label"><?= e($s['name']) ?></div>
        <div class="value"><?= (int) $s['total'] ?> <span class="text-muted text-sm" style="font-weight:600">proyek</span></div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
