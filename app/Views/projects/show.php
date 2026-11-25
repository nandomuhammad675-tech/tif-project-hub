<?php
$imageUrls = array_map(fn($im) => \App\Helpers\ImageUploader::url($im['filename']), $images ?? []);
?>
<style>
    /* =====================================================================
       Detail proyek: efek & animasi tambahan (semua di-scope ke .pd)
       ===================================================================== */
    .pd { position: relative; isolation: isolate; --mx: 50%; --my: 50%; }

    /* ---------- Latar blob mengambang ---------- */
    .pd-bg { position: absolute; inset: -30px -20px auto -20px; height: 560px; overflow: hidden; pointer-events: none; z-index: -1; }
    .pd-bg i { position: absolute; border-radius: 50%; filter: blur(60px); opacity: .55; }
    .pd-bg i:nth-child(1) { width: 340px; height: 340px; left: -60px; top: 40px; background: radial-gradient(circle, rgba(41,156,184,.55), transparent 70%); }
    .pd-bg i:nth-child(2) { width: 420px; height: 420px; right: -80px; top: -40px; background: radial-gradient(circle, rgba(3,121,146,.40), transparent 70%); }
    .pd-bg i:nth-child(3) { width: 260px; height: 260px; left: 42%; top: 300px; background: radial-gradient(circle, rgba(94,200,220,.45), transparent 70%); }

    /* ---------- Tombol kembali ---------- */
    .pd-back { position: relative; padding-bottom: 2px; }
    .pd-back .ico { width: 16px; height: 16px; transition: transform .25s cubic-bezier(.34,1.56,.64,1); }
    .pd-back:hover .ico { transform: translateX(-5px); }
    .pd-back::after { content: ""; position: absolute; left: 0; right: 0; bottom: -2px; height: 2px; border-radius: 2px; background: var(--grad); transform: scaleX(0); transform-origin: left; transition: transform .3s ease; }
    .pd-back:hover::after { transform: scaleX(1); }

    /* ---------- Galeri ---------- */
    .pd .gallery-main { transform: perspective(900px) rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)); transition: transform .18s ease-out, box-shadow .3s ease, border-color .3s ease; will-change: transform; background: linear-gradient(135deg, #e3f3f7, #f4fbfd, #d6eef5); background-size: 200% 200%; }
    .pd .gallery-main:hover { box-shadow: 0 26px 60px rgba(3,121,146,.28); border-color: rgba(3,121,146,.35); }
    .pd .gallery-main .zoom-hint { z-index: 4; transition: transform .25s ease, background .25s ease; }
    .pd .gallery-main:hover .zoom-hint { transform: translateY(-3px); background: rgba(3,121,146,.88); }
    /* sorotan mengikuti kursor */
    .pd .gallery-main::after { content: ""; position: absolute; inset: 0; z-index: 3; pointer-events: none; opacity: 0; transition: opacity .3s ease; background: radial-gradient(420px circle at var(--gx, 50%) var(--gy, 50%), rgba(255,255,255,.38), transparent 60%); }
    .pd .gallery-main:hover::after { opacity: 1; }
    /* kilau yang menyapu */
    .pd-shine { position: absolute; top: 0; bottom: 0; left: -60%; width: 40%; z-index: 3; pointer-events: none; background: linear-gradient(100deg, transparent, rgba(255,255,255,.5), transparent); transform: skewX(-18deg); }
    /* ikon besar saat belum ada foto */
    .pd .gallery-main .thumb-icon { position: relative; z-index: 2; box-shadow: 0 18px 40px rgba(3,121,146,.25), 0 0 0 10px rgba(255,255,255,.35); }
    .pd-ring { position: absolute; left: 50%; top: 50%; border-radius: 50%; border: 1.5px dashed rgba(3,121,146,.35); transform: translate(-50%, -50%); pointer-events: none; }
    .pd-ring.r1 { width: 210px; height: 210px; }
    .pd-ring.r2 { width: 300px; height: 300px; border-color: rgba(41,156,184,.28); }
    .pd-ring.r3 { width: 400px; height: 400px; border-color: rgba(94,200,220,.25); }
    .pd .gallery-thumbs button { transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s ease, border-color .2s ease; }
    .pd .gallery-thumbs button:hover { transform: translateY(-4px) scale(1.05); box-shadow: 0 10px 22px rgba(3,121,146,.25); }

    /* ---------- Lencana ---------- */
    .pd .detail-meta .badge { transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s ease; cursor: default; }
    .pd .detail-meta .badge:hover { transform: translateY(-3px) scale(1.06); box-shadow: 0 8px 16px rgba(3,121,146,.18); }
    .pd-dot::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block; margin-right: .3rem; box-shadow: 0 0 0 0 rgba(16,185,129,.6); }

    /* ---------- Judul ---------- */
    .pd-title { font-size: 1.9rem; font-weight: 800; letter-spacing: -.8px; line-height: 1.2; margin-bottom: .5rem; color: var(--primary-dark); }
    @supports ((-webkit-background-clip: text) or (background-clip: text)) {
        .pd-title { background: linear-gradient(90deg, #024c5d, #037992, #299cb8, #037992, #024c5d); background-size: 220% 100%; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    }
    .pd-bar { display: block; width: 64px; height: 4px; border-radius: 4px; margin: 0 0 .85rem; background: var(--grad); transform-origin: left; }

    /* ---------- Daftar info ---------- */
    .pd .detail-info-list { display: grid; gap: .3rem; margin: 1.1rem 0; }
    .pd .detail-info-list li { border-bottom: 0; padding: .65rem .8rem; border-radius: 14px; border: 1px solid transparent; transition: background .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .pd .detail-info-list li:hover { background: #fff; border-color: var(--border); box-shadow: 0 8px 22px rgba(3,121,146,.10); }
    .pd-ico { width: 34px; height: 34px; border-radius: 11px; display: grid; place-items: center; flex: none; color: var(--primary); background: var(--primary-light); transition: transform .3s cubic-bezier(.34,1.56,.64,1), background .22s ease, color .22s ease; }
    .pd-ico .ico { width: 18px; height: 18px; }
    .pd .detail-info-list li:hover .pd-ico { transform: rotate(-8deg) scale(1.12); background: var(--grad); color: #fff; }
    .pd .detail-info-list li strong { min-width: 112px; }
    .pd .tech span { transition: transform .2s cubic-bezier(.34,1.56,.64,1), background .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease; cursor: default; }
    .pd .tech span:hover { transform: translateY(-3px); background: var(--primary-light); border-color: var(--primary); color: var(--primary-dark); box-shadow: 0 6px 14px rgba(3,121,146,.18); }
    .pd .member-chip { transition: transform .2s ease, background .2s ease, box-shadow .2s ease; }
    .pd .member-chip:hover { transform: translateY(-2px); background: var(--primary-light); box-shadow: 0 6px 14px rgba(3,121,146,.16); }
    .pd .member-chip .avatar { box-shadow: 0 0 0 2px #fff; }

    /* ---------- Tombol aksi ---------- */
    .pd-actions .btn { position: relative; overflow: hidden; transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s ease, background .2s ease, border-color .2s ease; }
    .pd-actions .btn:hover { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(3,121,146,.22); }
    .pd-actions .btn:active { transform: translateY(0) scale(.96); }
    .pd-actions .btn-primary::after { content: ""; position: absolute; top: 0; bottom: 0; left: -70%; width: 45%; background: linear-gradient(100deg, transparent, rgba(255,255,255,.45), transparent); transform: skewX(-20deg); transition: left .6s ease; pointer-events: none; }
    .pd-actions .btn-primary:hover::after { left: 130%; }
    .pd-actions .btn .ico { transition: transform .25s cubic-bezier(.34,1.56,.64,1); }
    .pd-actions .btn:hover .ico { transform: scale(1.18) rotate(-6deg); }
    .pd-star { display: inline-block; transition: transform .35s cubic-bezier(.34,1.56,.64,1), color .2s ease; }
    .pd-actions .btn:hover .pd-star { transform: scale(1.35) rotate(72deg); color: #f59e0b; }
    .pd-ripple { position: absolute; border-radius: 50%; background: rgba(255,255,255,.55); transform: scale(0); pointer-events: none; animation: pdRipple .6s ease-out forwards; }
    .pd-actions .btn-outline .pd-ripple { background: rgba(3,121,146,.22); }

    /* ---------- Kartu deskripsi ---------- */
    .pd-desc { position: relative; overflow: hidden; transition: box-shadow .3s ease, transform .3s ease; }
    .pd-desc::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--grad); }
    .pd-desc:hover { box-shadow: 0 18px 44px rgba(3,121,146,.14); }
    .pd-desc .card-title { display: flex; align-items: center; gap: .65rem; }
    .pd-desc .pd-ico { width: 32px; height: 32px; }
    .pd-desc-body { line-height: 1.8; white-space: pre-line; }

    /* ---------- Keyframes (hanya bila gerak diizinkan) ---------- */
    @keyframes pdRipple { to { transform: scale(3.4); opacity: 0; } }
    @media (prefers-reduced-motion: no-preference) {
        @keyframes pdFloat { 0%,100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-12px) rotate(2deg); } }
        @keyframes pdDrift { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(26px,-22px) scale(1.12); } }
        @keyframes pdMesh { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes pdSweep { 0%,62% { left: -60%; } 100% { left: 140%; } }
        @keyframes pdSpin { to { transform: translate(-50%, -50%) rotate(360deg); } }
        @keyframes pdGrad { to { background-position: -220% 0; } }
        @keyframes pdBar { from { transform: scaleX(0); } to { transform: scaleX(1); } }
        @keyframes pdPulse { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,.6); } 70%,100% { box-shadow: 0 0 0 8px rgba(16,185,129,0); } }
        @keyframes pdBeat { 0%,100% { transform: scale(1); } 15% { transform: scale(1.32); } 30% { transform: scale(1); } 45% { transform: scale(1.18); } }
        @keyframes pdTag { from { opacity: 0; transform: translateY(8px) scale(.85); } to { opacity: 1; transform: none; } }

        .pd-bg i:nth-child(1) { animation: pdDrift 14s ease-in-out infinite; }
        .pd-bg i:nth-child(2) { animation: pdDrift 18s ease-in-out -4s infinite reverse; }
        .pd-bg i:nth-child(3) { animation: pdDrift 16s ease-in-out -8s infinite; }
        .pd .gallery-main { animation: pdMesh 9s ease-in-out infinite; }
        .pd-shine { animation: pdSweep 7s ease-in-out 1.2s infinite; }
        .pd .gallery-main .thumb-icon { animation: pdFloat 5s ease-in-out infinite; }
        .pd-ring.r1 { animation: pdSpin 22s linear infinite; }
        .pd-ring.r2 { animation: pdSpin 34s linear infinite reverse; }
        .pd-ring.r3 { animation: pdSpin 48s linear infinite; }
        .pd-title { animation: pdGrad 9s linear infinite; }
        .pd-bar { animation: pdBar .8s cubic-bezier(.22,1,.36,1) .5s both; }
        .pd-dot::before { animation: pdPulse 1.8s ease-out infinite; }
        .pd .tech span { animation: pdTag .5s cubic-bezier(.34,1.56,.64,1) both; }
        .pd .tech span:nth-child(1) { animation-delay: .7s; }
        .pd .tech span:nth-child(2) { animation-delay: .8s; }
        .pd .tech span:nth-child(3) { animation-delay: .9s; }
        .pd .tech span:nth-child(4) { animation-delay: 1s; }
        .pd .tech span:nth-child(n+5) { animation-delay: 1.1s; }
        .pd-wish-on .pd-star { animation: pdBeat 2.4s ease-in-out infinite; }
    }
    @media (max-width: 860px) {
        .pd-ring.r3 { display: none; }
        .pd-title { font-size: 1.55rem; }
        .pd .gallery-main { transform: none; }
    }
</style>

<div class="pd" id="pd">
    <div class="pd-bg" aria-hidden="true"><i></i><i></i><i></i></div>

    <a href="/projects" class="back-link pd-back"><?= icon('arrow-left') ?> Kembali ke Katalog</a>

    <div class="detail-header mt-2">
        <div class="gallery" data-gallery="<?= e(json_encode($imageUrls)) ?>">
            <div class="gallery-main" id="pd-tilt">
                <?php if ($imageUrls): ?>
                    <img src="<?= e($imageUrls[0]) ?>" alt="<?= e($project['title']) ?>">
                    <span class="zoom-hint"><?= icon('zoom-in') ?> Klik untuk memperbesar</span>
                <?php else: ?>
                    <i class="pd-ring r3" aria-hidden="true"></i>
                    <i class="pd-ring r2" aria-hidden="true"></i>
                    <i class="pd-ring r1" aria-hidden="true"></i>
                    <span class="thumb-icon"><?= category_icon($project['category_icon'] ?? null) ?></span>
                <?php endif; ?>
                <span class="pd-shine" aria-hidden="true"></span>
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
                <?php if ($isTaken): ?>
                    <span class="badge badge-warning">Sudah dikerjakan</span>
                <?php else: ?>
                    <span class="badge badge-success pd-dot">Tersedia</span>
                <?php endif; ?>
            </div>
            <h1 class="pd-title"><?= e($project['title']) ?></h1>
            <span class="pd-bar" aria-hidden="true"></span>
            <p class="text-muted"><?= e($project['short_desc'] ?? '') ?></p>

            <ul class="detail-info-list">
                <?php if ($project['course_name']): ?>
                <li><span class="pd-ico"><?= icon('graduation') ?></span><strong>Mata Kuliah</strong> <?= e($project['course_name']) ?></li>
                <?php endif; ?>
                <li><span class="pd-ico"><?= icon('cpu') ?></span><strong>Teknologi</strong>
                    <span class="tech" style="display:inline-flex;gap:0.3rem;flex-wrap:wrap;">
                        <?= tech_tags($project['technologies']) ?>
                    </span>
                </li>
                <?php if (!empty($members)): ?>
                <li><span class="pd-ico"><?= icon('users') ?></span><strong>Dikerjakan oleh</strong>
                    <?php foreach ($members as $m): ?>
                        <span class="member-chip"><span class="avatar"><?= e(initials($m['name'])) ?></span><?= e($m['name']) ?> · <?= e($m['role_in_project']) ?></span>
                    <?php endforeach; ?>
                </li>
                <?php endif; ?>
            </ul>

            <div class="flex gap-1 mt-2 pd-actions" style="flex-wrap:wrap;">
                <?php if ($canEdit): ?>
                    <span class="btn btn-primary" style="cursor:default;opacity:0.9;"><?= icon('check') ?> Anda mengerjakan proyek ini</span>
                    <a href="/projects/<?= (int)$project['id'] ?>/edit" class="btn btn-outline"><?= icon('pencil') ?> Edit Proyek</a>
                <?php elseif ($isTaken): ?>
                    <span class="badge badge-warning" style="align-self:center;">Sudah diklaim / dikerjakan</span>
                <?php else: ?>
                    <form method="POST" action="/projects/<?= (int)$project['id'] ?>/claim" data-confirm="Kamu hanya boleh mengerjakan satu proyek." data-confirm-title="Ambil proyek ini?" data-confirm-ok="Ya, ambil" data-confirm-detail="<?= e($project['title']) ?>">
                        <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-primary"><?= icon('plus') ?> Ambil Proyek Ini</button>
                    </form>
                <?php endif; ?>
                <span class="text-sm text-muted" style="align-self:center;">Klaim tiket hanya lewat Beranda</span>

                <?php if ($project['github_url']): ?>
                <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline"><?= icon('git-branch') ?> Repository GitHub</a>
                <?php endif; ?>
                <?php if ($project['demo_url']): ?>
                <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline"><?= icon('external') ?> Lihat Demo</a>
                <?php endif; ?>

                <?php if ($isWishlisted): ?>
                    <form method="POST" action="/wishlists/<?= (int)$project['id'] ?>/remove" data-confirm="Proyek ini akan dihapus dari daftar wishlist kamu." data-confirm-title="Hapus dari wishlist?" data-confirm-ok="Ya, hapus" data-confirm-variant="danger">
                        <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-outline pd-wish-on" style="color:#dc2626; border-color:#fca5a5;"><span class="pd-star">★</span> Sudah di Wishlist</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/wishlists/<?= (int)$project['id'] ?>/add">
                        <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-outline"><span class="pd-star">☆</span> Tambah ke Wishlist</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card pd-desc">
        <h2 class="card-title"><span class="pd-ico"><?= icon('folder') ?></span> Deskripsi Proyek</h2>
        <div class="pd-desc-body"><?= e($project['description']) ?></div>
    </div>
</div>

<div class="lightbox" id="lightbox" aria-modal="true" role="dialog">
    <button type="button" class="lb-btn lb-close" aria-label="Tutup">&times;</button>
    <button type="button" class="lb-btn lb-prev" aria-label="Sebelumnya">&#8249;</button>
    <img src="" alt="Foto proyek">
    <button type="button" class="lb-btn lb-next" aria-label="Berikutnya">&#8250;</button>
    <span class="lb-counter"></span>
</div>

<script>
(function () {
    var root = document.getElementById('pd');
    if (!root) return;
    var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fine = window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches;

    /* ---------- Ripple saat tombol diklik ---------- */
    root.querySelectorAll('.pd-actions .btn').forEach(function (b) {
        b.addEventListener('pointerdown', function (e) {
            if (reduce) return;
            var r = b.getBoundingClientRect(), d = Math.max(r.width, r.height);
            var s = document.createElement('span');
            s.className = 'pd-ripple';
            s.style.width = s.style.height = d + 'px';
            s.style.left = (e.clientX - r.left - d / 2) + 'px';
            s.style.top = (e.clientY - r.top - d / 2) + 'px';
            b.appendChild(s);
            setTimeout(function () { s.remove(); }, 650);
        });
    });

    if (reduce || !fine) return;

    /* ---------- Galeri: miring 3D + sorotan cahaya ---------- */
    var tilt = document.getElementById('pd-tilt'), raf = null, px = 0, py = 0;
    if (tilt) {
        tilt.addEventListener('pointermove', function (e) {
            var r = tilt.getBoundingClientRect();
            px = (e.clientX - r.left) / r.width;
            py = (e.clientY - r.top) / r.height;
            if (raf) return;
            raf = requestAnimationFrame(function () {
                raf = null;
                tilt.style.setProperty('--ry', ((px - .5) * 9).toFixed(2) + 'deg');
                tilt.style.setProperty('--rx', ((.5 - py) * 7).toFixed(2) + 'deg');
                tilt.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
                tilt.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
            });
        });
        tilt.addEventListener('pointerleave', function () {
            tilt.style.setProperty('--rx', '0deg');
            tilt.style.setProperty('--ry', '0deg');
        });
    }

    /* ---------- Blob latar bergeser halus mengikuti kursor ---------- */
    var bg = root.querySelector('.pd-bg'), t = null;
    if (bg) {
        window.addEventListener('pointermove', function (e) {
            if (t) return;
            t = requestAnimationFrame(function () {
                t = null;
                var x = (e.clientX / window.innerWidth - .5) * 24;
                var y = (e.clientY / window.innerHeight - .5) * 16;
                bg.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px)';
            });
        }, { passive: true });
        bg.style.transition = 'transform .6s ease-out';
    }
})();
</script>
