<div class="page-head">
    <div>
        <h1>Wishlist Saya</h1>
        <p>Daftar proyek yang telah Anda simpan untuk referensi atau persiapan.</p>
    </div>
</div>

<?php if (empty($wishlists)): ?>
<div class="empty-state">
    <div class="icon">🔖</div>
    <h2>Wishlist Masih Kosong</h2>
    <p>Anda belum menyimpan proyek apa pun ke dalam wishlist.</p>
    <a href="/projects" class="btn btn-primary mt-2">Jelajahi Katalog Proyek →</a>
</div>
<?php else: ?>
<div class="project-grid">
    <?php foreach ($wishlists as $w): ?>
    <article class="project-card">
        <a href="/projects/<?= e($w['slug']) ?>" class="thumb" aria-label="<?= e($w['title']) ?>">
            <?= project_thumb($w) ?>
        </a>
        <div class="body">
            <div class="badges">
                <span class="badge badge-primary"><?= e($w['category_name'] ?? 'Lainnya') ?></span>
            </div>
            <h3><?= e($w['title']) ?></h3>
            <p class="desc"><?= e($w['short_desc'] ?: truncate($w['description'] ?? '', 90)) ?></p>
            <div class="actions flex gap-1" style="justify-content:space-between; align-items:center;">
                <a href="/projects/<?= e($w['slug']) ?>" class="btn btn-primary btn-sm">Lihat Detail →</a>
                <form method="POST" action="/wishlists/<?= (int)$w['project_id'] ?>/remove" onsubmit="return confirm('Hapus proyek ini dari wishlist?')">
                    <input type="hidden" name="_token" value="<?= e(\App\Core\Session::getCsrfToken()) ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626; border-color:#fca5a5;">Hapus</button>
                </form>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
