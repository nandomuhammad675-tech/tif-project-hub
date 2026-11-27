<?php
use App\Helpers\ImageUploader;

$avatarUrl = ImageUploader::avatarUrl($row['avatar'] ?? null);
?>
<style>
.ap-view-head { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.ap-view-head h1 { font-size:1.75rem; font-weight:800; letter-spacing:-.5px; }
.ap-view-head p { margin-top:.25rem; color:var(--muted); }
.ap-view-layout { display:grid; grid-template-columns:280px minmax(0,1fr); gap:1.25rem; align-items:start; }
.ap-view-card { background:var(--card); border:1px solid var(--border); border-radius:22px; box-shadow:var(--shadow-sm); overflow:hidden; }
.ap-view-identity { padding:1.5rem; text-align:center; }
.ap-view-cover { height:76px; margin:-1.5rem -1.5rem 0; background:var(--grad); position:relative; overflow:hidden; }
.ap-view-cover::before,.ap-view-cover::after { content:""; position:absolute; border-radius:50%; background:rgba(255,255,255,.15); }
.ap-view-cover::before { width:130px; height:130px; right:-25px; top:-70px; }
.ap-view-cover::after { width:70px; height:70px; left:24px; bottom:-42px; }
.ap-view-avatar { width:112px; height:112px; margin:-56px auto 0; position:relative; display:grid; place-items:center; overflow:hidden; border:5px solid #fff; border-radius:50%; background:var(--grad); color:#fff; font-size:2rem; font-weight:800; box-shadow:0 12px 28px rgba(2,37,51,.18); }
.ap-view-avatar img { width:100%; height:100%; display:block; object-fit:cover; }
.ap-view-identity h2 { margin-top:.8rem; font-size:1.12rem; font-weight:800; }
.ap-view-identity p { margin-top:.15rem; color:var(--muted); font-size:.84rem; word-break:break-all; }
.ap-view-details { padding:1.4rem 1.5rem; }
.ap-view-details h2 { font-size:1.08rem; margin-bottom:.8rem; }
.ap-view-row { display:flex; justify-content:space-between; gap:1rem; padding:.9rem 0; border-bottom:1px solid #eef4f7; }
.ap-view-row:last-child { border-bottom:0; }
.ap-view-row span { color:var(--muted); font-weight:600; }
.ap-view-row strong { text-align:right; overflow-wrap:anywhere; }
.ap-view-actions { display:flex; justify-content:flex-end; margin-top:1rem; }
.ap-view-actions .ico { width:16px; height:16px; }
@media (max-width:900px) { .ap-view-layout { grid-template-columns:1fr; } }
@media (max-width:560px) { .ap-view-row { flex-direction:column; gap:.2rem; } .ap-view-row strong { text-align:left; } }
</style>

<div class="ap-view-head">
    <div>
        <h1>Profil Administrator</h1>
        <p>Informasi akun administrator.</p>
    </div>
</div>

<div class="ap-view-layout">
    <section class="ap-view-card ap-view-identity">
        <div class="ap-view-cover"></div>
        <div class="ap-view-avatar">
            <?php if ($avatarUrl): ?>
                <img src="<?= e($avatarUrl) ?>" alt="Foto profil <?= e($row['name']) ?>">
            <?php else: ?>
                <?= e(initials($row['name'] ?? 'Admin')) ?>
            <?php endif; ?>
        </div>
        <h2><?= e($row['name'] ?? 'Administrator') ?></h2>
        <p><?= e($row['email'] ?? '') ?></p>
        <div class="ap-view-actions">
            <a href="/admin/profile/edit" class="btn btn-primary btn-block"><?= icon('pencil') ?> Edit Profil</a>
        </div>
    </section>

    <section class="ap-view-card ap-view-details">
        <h2>Informasi Akun</h2>
        <div class="ap-view-row"><span>Nama Lengkap</span><strong><?= e($row['name'] ?? '-') ?></strong></div>
        <div class="ap-view-row"><span>Email</span><strong><?= e($row['email'] ?? '-') ?></strong></div>
        <div class="ap-view-row"><span>Peran</span><strong>Administrator</strong></div>
        <div class="ap-view-row"><span>Status</span><strong><?= ($row['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif' ?></strong></div>
    </section>
</div>
