<a href="/projects" class="back-link">← Kembali ke Katalog</a>

<div class="detail-header mt-2">
    <?php
    $imageUrls = array_map(fn($im) => \App\Helpers\ImageUploader::url($im['filename']), $images ?? []);
?>
    <div class="gallery" data-gallery="<?= e(json_encode($imageUrls)) ?>">
        <div class="gallery-main">
            <?php if ($imageUrls): ?>
                <img src="<?= e($imageUrls[0]) ?>" alt="<?= e($project['title']) ?>">
                <span class="zoom-hint"><?= icon('zoom-in') ?> Klik untuk memperbesar</span>
            <?php else: ?>
                <span class="thumb-icon"><?= category_icon($project['category_icon'] ?? null) ?></span>
            <?php endif; ?>
        </div>
        <?php if (count($imageUrls) > 1): ?>
        <div class="gallery-thumbs">
            <?php foreach ($imageUrls as $i => $u): ?>
            <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Foto <?= $i + 1 ?>"><img src="<?= e($u) ?>" alt="" loading="lazy"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div>
        <div class="detail-meta">
            <span class="badge badge-primary"><?= e($project['category_name'] ?? 'Lainnya') ?></span>
            <span class="badge badge-secondary"><?= e($project['semester_name'] ?? '-') ?></span>
            <?php if ($project['academic_year']): ?>
            <span class="badge badge-secondary"><?= e($project['academic_year']) ?></span>
            <?php endif; ?>
            <span class="badge badge-info"><?= (int)$project['view_count'] ?> views</span>
            <?php if ($isTaken): ?>
                <span class="badge badge-warning">Sudah dikerjakan</span>
            <?php else: ?>
                <span class="badge badge-success">Tersedia</span>
            <?php endif; ?>
        </div>
        <h1 style="font-size:1.9rem;font-weight:800;letter-spacing:-.8px;line-height:1.2;margin-bottom:0.5rem;"><?= e($project['title']) ?></h1>
        <p class="text-muted"><?= e($project['short_desc'] ?? '') ?></p>

        <ul class="detail-info-list">
            <?php if ($project['course_name']): ?>
            <li><strong>Mata Kuliah</strong> <?= e($project['course_name']) ?></li>
            <?php endif; ?>
            <li><strong>Teknologi</strong>
                <span class="tech" style="display:inline-flex;gap:0.3rem;flex-wrap:wrap;">
                    <?= tech_tags($project['technologies']) ?>
                </span>
            </li>
            <?php if (!empty($members)): ?>
            <li><strong>Dikerjakan oleh</strong>
                <?php foreach ($members as $m): ?>
                    <span class="member-chip"><span class="avatar"><?= e(initials($m['name'])) ?></span><?= e($m['name']) ?> · <?= e($m['role_in_project']) ?></span>
                <?php endforeach; ?>
            </li>
            <?php endif; ?>
        </ul>

                <div class="flex gap-1 mt-2" style="flex-wrap:wrap;">
            <?php if ($userHasThis): ?>
<<<<<<< HEAD
                <span class="btn btn-primary" style="cursor:default;opacity:0.9;">✓ Anda terkait proyek ini</span>
=======
                <span class="btn btn-primary" style="cursor:default;opacity:0.9;"><?= icon('check') ?> Anda mengerjakan proyek ini</span>
>>>>>>> 4df4a391ccf29bbb02558fea3cbb6400b69af555
            <?php elseif ($isTaken): ?>
                <span class="badge badge-warning" style="align-self:center;">Sudah diklaim / dikerjakan</span>
            <?php else: ?>
<<<<<<< HEAD
                <span class="badge badge-success" style="align-self:center;">Dokumentasi karya</span>
=======
                <form method="POST" action="/projects/<?= (int)$project['id'] ?>/claim" onsubmit="return confirm('Ambil proyek ini? Anda hanya boleh mengerjakan satu proyek.')">
                    <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">
                    <button type="submit" class="btn btn-primary"><?= icon('plus') ?> Ambil Proyek Ini</button>
                </form>
>>>>>>> 4df4a391ccf29bbb02558fea3cbb6400b69af555
            <?php endif; ?>
            <span class="text-sm text-muted" style="align-self:center;">Klaim tiket hanya lewat Beranda</span>

            <?php if ($project['github_url']): ?>
            <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline"><?= icon('git-branch') ?> Repository GitHub</a>
            <?php endif; ?>
            <?php if ($project['demo_url']): ?>
            <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline"><?= icon('external') ?> Lihat Demo</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Deskripsi Proyek</h2>
    <div style="line-height:1.75;white-space:pre-line;"><?= e($project['description']) ?></div>
</div>

<div class="lightbox" id="lightbox" aria-modal="true" role="dialog">
    <button type="button" class="lb-btn lb-close" aria-label="Tutup">&times;</button>
    <button type="button" class="lb-btn lb-prev" aria-label="Sebelumnya">&#8249;</button>
    <img src="" alt="Foto proyek">
    <button type="button" class="lb-btn lb-next" aria-label="Berikutnya">&#8250;</button>
    <span class="lb-counter"></span>
</div>
