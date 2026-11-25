<?php
use App\Helpers\ImageUploader;

/** @var array $claim */
$claimed = ($claim['status'] ?? '') === 'claimed';
$avatar  = ImageUploader::avatarUrl($claim['student_avatar'] ?? null);
$techs   = array_filter(array_map('trim', explode(',', (string) ($claim['technologies'] ?? ''))));
?>
<style>
    .cd { display: grid; gap: 1.2rem; }
    .cd-hero { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 1.2rem; flex-wrap: wrap; padding: 1.6rem 1.8rem; border-radius: 24px; color: #fff; background: linear-gradient(120deg, #023745 0%, #037992 60%, #1fa0bd 130%); box-shadow: 0 20px 44px rgba(2,60,80,.25); }
    .cd-hero::before { content: ""; position: absolute; inset: 0; opacity: .45; background-image: radial-gradient(rgba(255,255,255,.16) 1.2px, transparent 1.4px); background-size: 22px 22px; pointer-events: none; }
    .cd-hero > * { position: relative; }
    .cd-hero small { display: block; font-size: .72rem; font-weight: 800; letter-spacing: .7px; text-transform: uppercase; opacity: .8; }
    .cd-code { margin-top: .3rem; font-family: "JetBrains Mono", ui-monospace, Consolas, monospace; font-size: clamp(1.3rem, 2.4vw, 1.9rem); font-weight: 800; letter-spacing: .5px; word-break: break-all; }
    .cd-copy { display: inline-flex; align-items: center; gap: .4rem; margin-top: .8rem; font: inherit; font-size: .8rem; font-weight: 800; color: #fff; padding: .4rem .85rem; border-radius: 999px; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.3); cursor: pointer; transition: background .2s ease; }
    .cd-copy:hover { background: rgba(255,255,255,.26); }
    .cd-copy svg { width: 14px; height: 14px; }
    .cd-state { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: 999px; font-size: .85rem; font-weight: 800; background: #fff; }
    .cd-state i { width: 9px; height: 9px; border-radius: 50%; }
    .cd-state.ok { color: #047857; } .cd-state.ok i { background: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,.2); }
    .cd-state.off { color: #b91c1c; } .cd-state.off i { background: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.2); }
    .cd-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 1.2rem; align-items: start; }
    .cd-card { background: var(--card); border: 1px solid var(--border); border-radius: 22px; box-shadow: var(--shadow-sm); padding: 1.3rem 1.4rem; min-width: 0; }
    .cd-card h2 { font-size: .78rem; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: var(--muted); margin-bottom: 1rem; }
    .cd-who { display: flex; align-items: center; gap: .9rem; margin-bottom: 1rem; }
    .cd-av { width: 54px; height: 54px; border-radius: 17px; flex: none; overflow: hidden; display: grid; place-items: center; font-weight: 800; color: #fff; background: var(--grad); }
    .cd-av img { width: 100%; height: 100%; object-fit: cover; }
    .cd-thumb { width: 54px; height: 54px; border-radius: 17px; flex: none; overflow: hidden; display: grid; place-items: center; background: var(--grad-soft); color: var(--primary); }
    .cd-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .cd-who strong { display: block; font-size: 1.05rem; font-weight: 800; line-height: 1.25; }
    .cd-who span { display: block; font-size: .82rem; color: var(--muted); }
    .cd-rows { display: grid; gap: 0; }
    .cd-row { display: flex; justify-content: space-between; gap: 1rem; padding: .7rem 0; border-top: 1px dashed var(--border); font-size: .87rem; }
    .cd-row span { color: var(--muted); font-weight: 600; flex: none; }
    .cd-row b { text-align: right; min-width: 0; overflow-wrap: anywhere; }
    .cd-chips { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: flex-end; }
    .cd-chips em { font-style: normal; font-size: .72rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px; background: var(--primary-light); color: var(--primary-dark); }
    .cd-link { display: inline-flex; align-items: center; gap: .35rem; margin-top: 1rem; font-size: .85rem; font-weight: 800; color: var(--primary); padding: .45rem .9rem; border-radius: 999px; background: var(--primary-light); transition: background .2s ease; }
    .cd-link:hover { background: #cfe9f1; }
    @media (max-width: 900px) { .cd-grid { grid-template-columns: 1fr; } }
</style>

<a href="/admin/claims" class="back-link">← Kembali ke tiket klaim</a>

<div class="page-head mt-2">
    <div>
        <h1>Detail Tiket Klaim</h1>
        <p>Informasi lengkap klaim proyek oleh mahasiswa.</p>
    </div>
</div>

<div class="cd">
    <section class="cd-hero" data-reveal="zoom">
        <div>
            <small>Kode tiket</small>
            <div class="cd-code" id="cd-code"><?= e($claim['ticket_code']) ?></div>
            <button type="button" class="cd-copy" id="cd-copy">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span>Salin kode</span>
            </button>
        </div>
        <span class="cd-state <?= $claimed ? 'ok' : 'off' ?>"><i></i><?= $claimed ? 'Aktif' : 'Dicabut' ?></span>
    </section>

    <div class="cd-grid">
        <section class="cd-card" data-reveal="up">
            <h2>Mahasiswa</h2>
            <div class="cd-who">
                <span class="cd-av"><?php if ($avatar): ?><img src="<?= e($avatar) ?>" alt=""><?php else: ?><?= e(initials($claim['student_name'])) ?><?php endif; ?></span>
                <div>
                    <strong><?= e($claim['student_name']) ?></strong>
                    <span><?= e($claim['student_nim'] ?? '-') ?></span>
                </div>
            </div>
            <div class="cd-rows">
                <div class="cd-row" data-reveal="soft"><span>Email</span><b><?= e($claim['student_email'] ?? '-') ?></b></div>
                <div class="cd-row" data-reveal="soft"><span>Kelas</span><b><?= e($claim['student_class'] ?? '-') ?></b></div>
                <div class="cd-row" data-reveal="soft"><span>Status akun</span><b><?= status_badge((string) ($claim['student_status'] ?? 'active')) ?></b></div>
            </div>
            <a href="/admin/students/<?= (int) $claim['user_id'] ?>" class="cd-link">Lihat profil mahasiswa →</a>
        </section>

        <section class="cd-card" data-reveal="up">
            <h2>Proyek yang diklaim</h2>
            <div class="cd-who">
                <span class="cd-thumb"><?= project_thumb($claim + ['category_icon' => null]) ?></span>
                <div>
                    <strong><?= e($claim['project_title']) ?></strong>
                    <span><?= e($claim['category_name'] ?? 'Tanpa kategori') ?></span>
                </div>
            </div>
            <div class="cd-rows">
                <div class="cd-row" data-reveal="soft"><span>Semester</span><b><?= e($claim['semester_name']) ?></b></div>
                <?php if (!empty($claim['course_name'])): ?>
                <div class="cd-row" data-reveal="soft"><span>Mata kuliah</span><b><?= e($claim['course_name']) ?></b></div>
                <?php endif; ?>
                <?php if ($techs): ?>
                <div class="cd-row" data-reveal="soft"><span>Teknologi</span><div class="cd-chips"><?php foreach ($techs as $t): ?><em><?= e($t) ?></em><?php endforeach; ?></div></div>
                <?php endif; ?>
                <div class="cd-row" data-reveal="soft"><span>Waktu klaim</span><b><?= e(format_datetime($claim['claimed_at'])) ?></b></div>
            </div>
            <a href="/admin/projects/<?= (int) $claim['project_id'] ?>/edit" class="cd-link">Buka proyek →</a>
        </section>
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('cd-copy'), code = document.getElementById('cd-code');
    if (!btn || !code) return;
    btn.addEventListener('click', function () {
        var label = btn.querySelector('span'), txt = code.textContent.trim();
        function done() { label.textContent = 'Tersalin!'; setTimeout(function () { label.textContent = 'Salin kode'; }, 1600); }
        if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(txt).then(done, done); }
        else { var t = document.createElement('textarea'); t.value = txt; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (e) {} document.body.removeChild(t); done(); }
    });
})();
</script>
