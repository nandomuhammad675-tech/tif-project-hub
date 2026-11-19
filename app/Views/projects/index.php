<div class="page-head">
    <div>
        <h1>Katalog Proyek</h1>
        <p><?= (int) $total ?> proyek ditemukan</p>
    </div>
</div>

<?php if (!empty($userProject)): ?>
<div class="alert alert-info mb-2">
    Anda sedang mengerjakan: <strong><?= e($userProject['title']) ?></strong>
    · <a href="/projects/<?= e($userProject['slug']) ?>">Lihat proyek →</a>
</div>
<?php endif; ?>

<form method="GET" action="/projects" class="filter-bar">
    <div class="form-group">
        <label>Cari</label>
        <input type="text" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Nama, teknologi...">
    </div>
    <div class="form-group">
        <label>Kategori</label>
        <select name="category_id" class="form-control">
            <option value="">Semua</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filters['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Semester</label>
        <select name="semester_id" class="form-control">
            <option value="">Semua</option>
            <?php foreach ($semesters as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $filters['semester_id'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="availability" class="form-control">
            <option value="">Semua</option>
            <option value="available" <?= ($filters['availability'] ?? '') === 'available' ? 'selected' : '' ?>>Tersedia</option>
            <option value="taken" <?= ($filters['availability'] ?? '') === 'taken' ? 'selected' : '' ?>>Sudah dikerjakan</option>
        </select>
    </div>
    <div class="form-group">
        <label>Urutkan</label>
        <select name="sort" class="form-control">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Judul A-Z</option>
        </select>
    </div>
    <div class="form-group">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>

<?php if (empty($projects)): ?>
<div class="empty-state">
    <div class="icon">🔍</div>
    <p>Tidak ada proyek yang cocok dengan filter Anda.</p>
</div>
<?php else: ?>
<div class="project-grid">
    <?php foreach ($projects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++):
        $qs = http_build_query(array_merge($filters, ['sort' => $sort, 'page' => $i]));
    ?>
        <?php if ($i === $page): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="/projects?<?= $qs ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
