<?php
/**
 * @var array      $user
 * @var array      $myProjects
 * @var array      $claims
 * @var array|null $activeClaim
 * @var array|null $war
 */
use App\Helpers\ImageUploader;
use App\Core\Session;

$avatarUrl   = ImageUploader::avatarUrl($user['avatar'] ?? null);
$maxBytes    = ImageUploader::avatarMaxBytes();
$maxMb       = round($maxBytes / 1048576, 1);
$csrf        = Session::getCsrfToken();
$projectCnt  = count($myProjects);
$ticketCnt   = count($claims);
?>
<style>
    .pf-head { margin-bottom: 1.6rem; display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .pf-head h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -.5px; }
    .pf-head p { color: var(--muted); margin-top: .2rem; }

    .pf-layout { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .pf-side { position: sticky; top: 92px; }

    /* ---------- Kartu profil ---------- */
    .pf-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow); overflow: hidden; }
    .pf-cover { height: 112px; background: var(--grad); position: relative; overflow: hidden; }
    .pf-cover::before, .pf-cover::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.12); }
    .pf-cover::before { width: 150px; height: 150px; right: -40px; top: -60px; }
    .pf-cover::after { width: 80px; height: 80px; left: 30px; bottom: -40px; }
    .pf-body { padding: 0 1.5rem 1.5rem; text-align: center; }

    .pf-avatar-wrap { position: relative; width: 112px; height: 112px; margin: -56px auto 0; }
    .pf-avatar {
        width: 112px; height: 112px; border-radius: 50%; overflow: hidden;
        border: 5px solid #fff; background: var(--grad); color: #fff;
        display: grid; place-items: center; font-size: 2.2rem; font-weight: 800;
        box-shadow: 0 8px 24px rgba(2,37,51,.18);
    }
    .pf-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pf-cam {
        position: absolute; right: 2px; bottom: 4px; width: 36px; height: 36px; border-radius: 50%;
        display: grid; place-items: center; cursor: pointer;
        background: var(--primary); color: #fff; border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(3,121,146,.35); transition: transform .15s ease, background .15s ease;
    }
    .pf-cam:hover, .pf-cam:focus-visible { transform: scale(1.1); background: var(--primary-dark); outline: none; }
    .pf-cam svg { width: 17px; height: 17px; }

    .pf-actions { display: flex; justify-content: center; gap: .5rem; margin-top: .9rem; }
    .pf-actions[hidden], .pf-error[hidden] { display: none; }
    .pf-error { margin: .75rem 0 0; font-size: .82rem; font-weight: 600; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: .5rem .75rem; }
    .pf-name { font-size: 1.3rem; font-weight: 800; margin-top: .9rem; letter-spacing: -.3px; }
    .pf-email { color: var(--muted); font-size: .88rem; margin: .15rem 0 .7rem; word-break: break-all; }
    .pf-hint { font-size: .75rem; color: var(--muted); margin-top: .7rem; }
    .pf-remove { background: none; border: 0; cursor: pointer; font: inherit; font-size: .78rem; font-weight: 700; color: #b91c1c; padding: .25rem .5rem; margin-top: .75rem; }
    .pf-remove:hover { text-decoration: underline; }

    .pf-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; margin: 1.2rem 0; }
    .pf-stat { background: linear-gradient(180deg, #f4fafc, #e9f4f8); border: 1px solid #dcecf2; border-radius: 14px; padding: .8rem .4rem; transition: transform .18s ease, box-shadow .18s ease; }
    .pf-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(3,121,146,.14); }
    .pf-stat b { display: block; font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); line-height: 1.1; }
    .pf-stat span { font-size: .72rem; font-weight: 600; color: var(--muted); }

    .pf-info { margin: 0; text-align: left; border-top: 1px dashed var(--border); padding-top: .4rem; }
    .pf-info div { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .65rem 0; border-bottom: 1px solid #eef4f7; font-size: .88rem; }
    .pf-info dt { display: flex; align-items: center; gap: .55rem; }
    .pf-info dt .ico { width: 16px; height: 16px; color: var(--primary); opacity: .85; }
    .pf-status { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; color: #047857; background: #d1fae5; border-radius: 999px; padding: .15rem .65rem; }
    .pf-status::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.25); }
    .pf-edit-btn { margin-top: 1.1rem; }
    .pf-edit-btn .ico { width: 16px; height: 16px; }
    .pf-info div:last-child { border-bottom: 0; }
    .pf-info dt { color: var(--muted); font-weight: 600; }
    .pf-info dd { margin: 0; font-weight: 700; text-align: right; }

    /* ---------- Konten kanan ---------- */
    .pf-section { margin-bottom: 2rem; }
    .pf-section-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
    .pf-section-head h2 { font-size: 1.2rem; font-weight: 800; letter-spacing: -.3px; display: flex; align-items: center; gap: .6rem; }
    .pf-section-head h2 .count { font-size: .75rem; font-weight: 800; background: var(--primary-light); color: var(--primary-dark); border-radius: 999px; padding: .15rem .6rem; }

    .pf-ticket {
        display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
        box-shadow: var(--shadow-sm); padding: 1rem 1.15rem; margin-bottom: .8rem;
        position: relative; transition: transform .15s ease, box-shadow .15s ease;
    }
    .pf-ticket::before { content: ""; position: absolute; left: 0; top: 14px; bottom: 14px; width: 4px; border-radius: 0 4px 4px 0; background: var(--primary); }
    .pf-ticket:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
    .pf-ticket .t-ico { width: 46px; height: 46px; border-radius: 14px; flex: none; display: grid; place-items: center; background: var(--primary-light); color: var(--primary); }
    .pf-ticket .t-ico .ico { width: 22px; height: 22px; }
    .pf-ticket .t-main { flex: 1; min-width: 200px; }
    .pf-ticket .t-code { display: inline-block; font-family: ui-monospace, Consolas, monospace; font-size: .75rem; font-weight: 700; color: var(--primary-dark); background: #eef6f9; border-radius: 8px; padding: .15rem .55rem; }
    .pf-ticket h3 { font-size: 1rem; font-weight: 800; margin: .3rem 0 .15rem; }
    .pf-ticket .t-meta { font-size: .8rem; color: var(--muted); }
    .pf-ticket .t-side { display: flex; align-items: center; gap: .6rem; }


    /* ---------- Pengatur foto (crop) ---------- */
    .crop-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 1rem; background: rgba(2,37,51,.62); backdrop-filter: blur(3px); }
    .crop-modal[hidden] { display: none; }
    .crop-dialog { width: 100%; max-width: 380px; background: #fff; border-radius: 22px; padding: 1.4rem; text-align: center; box-shadow: 0 30px 80px rgba(2,37,51,.4); }
    .crop-dialog h3 { font-size: 1.15rem; font-weight: 800; }
    .crop-sub { font-size: .83rem; color: var(--muted); margin-top: .25rem; }
    .crop-stage { position: relative; width: min(300px, 100%); aspect-ratio: 1; margin: 1.1rem auto; overflow: hidden; border-radius: 16px; background: #0b1f2a; touch-action: none; cursor: grab; user-select: none; }
    .crop-stage.is-drag { cursor: grabbing; }
    .crop-stage img { position: absolute; max-width: none; user-select: none; pointer-events: none; }
    .crop-mask { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle closest-side at center, transparent 99%, rgba(2,37,51,.62) 100%); }
    .crop-mask::after { content: ""; position: absolute; inset: 0; border-radius: 50%; border: 2px solid rgba(255,255,255,.92); }
    .crop-zoom { display: flex; align-items: center; gap: .6rem; padding: 0 .25rem; }
    .crop-zoom input[type=range] { flex: 1; accent-color: var(--primary); }
    .crop-zoom button { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); background: #fff; font-size: 1.15rem; font-weight: 700; line-height: 1; cursor: pointer; color: var(--primary-dark); }
    .crop-zoom button:hover { border-color: var(--primary); color: var(--primary); }
    .crop-btns { display: flex; gap: .5rem; justify-content: center; margin-top: 1.2rem; }
    .crop-tip { font-size: .75rem; color: var(--muted); margin-top: .7rem; }


    /* ---------- Pop up konfirmasi ---------- */
    .crop-dialog { animation: cfPop .22s ease; }
    @keyframes cfPop { from { opacity: 0; transform: translateY(14px) scale(.96); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .crop-dialog { animation: none; } }
    .cf-dialog { max-width: 360px; padding: 1.8rem 1.5rem 1.4rem; }
    .cf-preview { position: relative; width: 148px; height: 148px; margin: 0 auto 1.1rem; border-radius: 50%; padding: 5px; background: var(--grad); box-shadow: 0 12px 30px rgba(3,121,146,.3); }
    .cf-preview > div, .cf-preview img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: grid; place-items: center; border: 4px solid #fff; background: var(--grad); color: #fff; font-size: 2.2rem; font-weight: 800; }
    .cf-badge { position: absolute; right: 2px; bottom: 6px; width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: #10b981; color: #fff; border: 3px solid #fff; }
    .cf-badge svg { width: 16px; height: 16px; }
    .cf-badge.is-danger { background: #dc2626; }
    .cf-btns { display: grid; gap: .55rem; margin-top: 1.3rem; }
    .cf-btns .row { display: grid; grid-template-columns: 1fr 1fr; gap: .55rem; }
    .cf-btns .btn { width: 100%; justify-content: center; }
    .cf-btns .btn[disabled] { opacity: .7; cursor: wait; }
    .cf-danger { background: #dc2626; border-color: #dc2626; color: #fff; }
    .cf-danger:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }

    @media (max-width: 960px) {
        .pf-layout { grid-template-columns: 1fr; }
        .pf-side { position: static; }
    }
    @media (max-width: 520px) {
        .pf-ticket .t-side { width: 100%; justify-content: space-between; }
    }
    /* ---------- Kelompok proyek ---------- */
    .team-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 1rem; transition: box-shadow .25s ease, transform .25s ease; }
    .team-card:hover { box-shadow: var(--shadow); }
    .team-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.1rem 1.3rem; background: var(--grad-soft); border-bottom: 1px solid var(--border); }
    .team-kicker { font-size: .7rem; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: var(--primary); }
    .team-kicker.is-lead { color: #b45309; }
    .team-head h3 { font-size: 1.05rem; font-weight: 800; letter-spacing: -.2px; margin-top: .15rem; }
    .team-count { flex: none; font-size: .75rem; font-weight: 800; color: var(--primary-dark); background: #fff; border: 1px solid var(--border); border-radius: 999px; padding: .25rem .8rem; }
    .team-list { list-style: none; margin: 0; padding: .5rem .8rem; }
    .team-row { display: flex; align-items: center; gap: .8rem; padding: .65rem .5rem; border-radius: 14px; transition: background .15s ease; }
    .team-row + .team-row { border-top: 1px solid #eef4f7; }
    .team-row:hover { background: #f6fbfc; }
    .team-av { width: 42px; height: 42px; border-radius: 14px; flex: none; display: grid; place-items: center; font-size: .85rem; font-weight: 800; color: var(--primary-dark); background: var(--primary-light); }
    .team-av.is-lead { background: var(--grad); color: #fff; box-shadow: 0 6px 14px rgba(3,121,146,.25); }
    .team-info { flex: 1; min-width: 0; }
    .team-info strong { display: block; font-size: .92rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .team-info strong em { font-style: normal; font-weight: 600; color: var(--muted); font-size: .8rem; }
    .team-info small { display: block; font-size: .78rem; color: var(--muted); }
    .team-role { flex: none; font-size: .7rem; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; border-radius: 999px; padding: .2rem .65rem; color: var(--primary-dark); background: var(--primary-light); }
    .team-role.is-lead { color: #92400e; background: #fef3c7; }
    .team-del { flex: none; }
    .team-del button { width: 34px; height: 34px; border-radius: 10px; border: 0; display: grid; place-items: center; cursor: pointer; background: #f1f7f9; color: var(--muted); transition: background .15s ease, color .15s ease, transform .2s cubic-bezier(.34,1.56,.64,1); }
    .team-del button:hover { background: #fee2e2; color: #b91c1c; transform: scale(1.08); }
    .team-del button .ico { width: 17px; height: 17px; }
    .team-add { padding: 1rem 1.3rem 1.2rem; border-top: 1px dashed var(--border); background: #fbfdfe; }
    .team-add > label { display: block; font-size: .85rem; font-weight: 800; margin-bottom: .5rem; }
    .team-search { position: relative; }
    .team-search svg { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--muted); pointer-events: none; }
    .team-search input { width: 100%; padding: .75rem 1rem .75rem 2.7rem; border: 1px solid var(--border); border-radius: 14px; font: inherit; font-size: .92rem; background: #fff; transition: border-color .15s ease, box-shadow .15s ease; }
    .team-search input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(3,121,146,.14); }
    .team-results { margin-top: .6rem; display: grid; gap: .35rem; }
    .team-results[hidden] { display: none; }
    .team-res { display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left; font: inherit; padding: .55rem .65rem; border: 1px solid var(--border); border-radius: 14px; background: #fff; cursor: pointer; animation: tmIn .22s ease both; transition: border-color .15s ease, background .15s ease, transform .15s ease; }
    .team-res:hover { border-color: var(--primary); background: var(--primary-light); transform: translateX(3px); }
    .team-res:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
    .team-res .team-av { width: 36px; height: 36px; border-radius: 12px; font-size: .78rem; }
    .team-res .team-info strong { font-size: .88rem; }
    .team-res .go { flex: none; display: inline-flex; align-items: center; gap: .3rem; font-size: .78rem; font-weight: 800; color: #fff; background: var(--grad); border-radius: 999px; padding: .3rem .8rem; }
    .team-res .go svg { width: 14px; height: 14px; }
    .team-msg { padding: .8rem .9rem; font-size: .84rem; color: var(--muted); text-align: center; background: #fff; border: 1px dashed var(--border); border-radius: 14px; }
    .team-hint { display: block; margin-top: .6rem; font-size: .76rem; color: var(--muted); }
    .team-foot { padding: .9rem 1.3rem 1.1rem; border-top: 1px dashed var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; font-size: .8rem; color: var(--muted); }
    .btn-leave { background: #fff; color: #b91c1c; border: 1px solid #fecaca; }
    .btn-leave:hover { background: #fee2e2; }
    @keyframes tmIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .team-res { animation: none; } }
    @media (max-width: 560px) { .team-role { display: none; } }
</style>

<div class="pf-head">
    <div>
        <h1>Profil Saya</h1>
        <p>Kelola akun, foto profil, serta pantau tiket dan proyek yang kamu kerjakan.</p>
    </div>
</div>

<div class="pf-layout">
    <!-- ===================== KOLOM KIRI: PROFIL ===================== -->
    <aside class="pf-side">
        <div class="pf-card">
            <div class="pf-cover"></div>
            <div class="pf-body">
                <form id="avatar-form" method="POST" action="/profile/avatar" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                    <div class="pf-avatar-wrap">
                        <div class="pf-avatar" id="avatar-preview">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= e($avatarUrl) ?>" alt="Foto profil <?= e($user['name']) ?>">
                            <?php else: ?>
                                <?= e(initials($user['name'])) ?>
                            <?php endif; ?>
                        </div>
                        <label for="avatar-input" class="pf-cam" tabindex="0" title="<?= $avatarUrl ? 'Ganti foto' : 'Tambah foto' ?>" aria-label="<?= $avatarUrl ? 'Ganti foto profil' : 'Tambah foto profil' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"/><circle cx="12" cy="13" r="3"/></svg>
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>

                    <p class="pf-error" id="avatar-error" hidden></p>
                </form>

                <h2 class="pf-name"><?= e($user['name']) ?></h2>
                <p class="pf-email"><?= e($user['email']) ?></p>
                <span class="badge badge-primary">Mahasiswa Teknik Informatika</span>

                <a href="/profile/edit" class="btn btn-primary btn-block pf-edit-btn"><?= icon('pencil') ?> Edit Akun</a>

                <div class="pf-stats">
                    <div class="pf-stat"><b><?= (int) $projectCnt ?></b><span>Proyek</span></div>
                    <div class="pf-stat"><b><?= (int) $ticketCnt ?></b><span>Tiket</span></div>
                    <div class="pf-stat"><b><?= e((string) ($user['semester'] ?? '-')) ?></b><span>Semester</span></div>
                </div>

                <dl class="pf-info">
                    <div><dt><?= icon('id-card') ?>NIM</dt><dd><?= e($user['nim'] ?? '-') ?></dd></div>
                    <div><dt><?= icon('mail') ?>Email</dt><dd style="word-break:break-all;"><?= e($user['email'] ?? '-') ?></dd></div>
                    <div><dt><?= icon('users-class') ?>Kelas</dt><dd><?= e($user['class'] ?: '-') ?></dd></div>
                    <div><dt><?= icon('calendar') ?>Semester</dt><dd><?= e((string) ($user['semester'] ?? '-')) ?></dd></div>
                    <div><dt><?= icon('graduation') ?>Program Studi</dt><dd>Teknik Informatika</dd></div>
                    <div><dt><?= icon('shield') ?>Status</dt><dd><span class="pf-status">Aktif</span></dd></div>
                </dl>
            </div>
        </div>
    </aside>

    <!-- ===================== KOLOM KANAN: TIKET & PROYEK ===================== -->
    <div class="pf-main">
        <section class="pf-section">
            <div class="pf-section-head">
                <h2>Tiket Proyek Saya <?php if ($ticketCnt): ?><span class="count"><?= (int) $ticketCnt ?></span><?php endif; ?></h2>
            </div>

            <?php if (empty($claims)): ?>
            <div class="empty-state">
                <div class="icon"><?= icon('ticket') ?></div>
                <p><strong>Belum ada tiket proyek.</strong></p>
                <p class="text-sm">Klaim tiketmu lewat tombol <strong>KLAIM TIKETMU SEKARANG!</strong> di Beranda saat Project War dibuka.</p>
                <a href="/#klaim-tiket" class="btn btn-primary btn-sm" style="margin-top:1rem;">Ke Beranda</a>
            </div>
            <?php else: ?>
                <?php foreach ($claims as $c): ?>
                <div class="pf-ticket">
                    <span class="t-ico"><?= icon('ticket') ?></span>
                    <div class="t-main">
                        <span class="t-code"><?= e($c['ticket_code']) ?></span>
                        <h3><?= e($c['project_title']) ?></h3>
                        <div class="t-meta">
                            <?= e($c['semester_name'] ?? '-') ?><?= !empty($c['academic_year']) ? ' · ' . e($c['academic_year']) : '' ?>
                            · Diklaim <?= e(format_datetime($c['claimed_at'])) ?>
                        </div>
                    </div>
                    <div class="t-side">
                        <?php if (($c['status'] ?? '') === 'claimed'): ?>
                            <span class="badge badge-success">Terklaim</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?= e($c['status'] ?? '-') ?></span>
                        <?php endif; ?>
                        <a href="/claims/<?= (int) $c['id'] ?>" class="btn btn-outline btn-sm">Lihat detail</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="pf-section">
            <div class="pf-section-head">
                <h2>Proyek Saya <?php if ($projectCnt): ?><span class="count"><?= (int) $projectCnt ?></span><?php endif; ?></h2>
            </div>

            <?php if (empty($myProjects)): ?>
            <div class="empty-state">
                <div class="icon"><?= icon('folder') ?></div>
                <p><strong>Belum ada proyek.</strong></p>
                <p class="text-sm">Proyek yang kamu kerjakan akan tampil di sini.</p>
                <a href="/projects" class="btn btn-primary btn-sm" style="margin-top:1rem;">Jelajahi Proyek</a>
            </div>
            <?php else: ?>
            <div class="project-grid">
                <?php foreach ($myProjects as $p) { $opts = ['role' => $p['role_in_project']]; include __DIR__ . '/../partials/project_card.php'; } ?>
            </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($teams)): ?>
        <section class="pf-section" id="kelompok">
            <div class="pf-section-head">
                <h2>Kelompok Proyek</h2>
            </div>
            <?php foreach ($teams as $t):
                $isLead = ($t['role'] ?? '') === 'Ketua';
                $pid = (int) $t['id'];
            ?>
            <div class="team-card" data-project="<?= $pid ?>" data-token="<?= e($csrf) ?>" data-lead="<?= $isLead ? '1' : '0' ?>">
                <div class="team-head">
                    <div>
                        <span class="team-kicker<?= $isLead ? ' is-lead' : '' ?>"><?= $isLead ? 'Kamu adalah Ketua' : 'Kamu adalah Anggota' ?></span>
                        <h3><?= e($t['title']) ?></h3>
                    </div>
                    <span class="team-count"><?= count($t['members']) ?> anggota</span>
                </div>

                <ul class="team-list">
                    <?php foreach ($t['members'] as $m):
                        $isMe = (int) $m['id'] === (int) ($user['id'] ?? 0);
                        $isK  = ($m['role_in_project'] ?? '') === 'Ketua';
                        $meta = trim((string) ($m['nim'] ?? '') . (!empty($m['class']) ? ' · ' . $m['class'] : ''));
                    ?>
                    <li class="team-row">
                        <span class="team-av<?= $isK ? ' is-lead' : '' ?>"><?= e(initials($m['name'])) ?></span>
                        <span class="team-info">
                            <strong><?= e($m['name']) ?><?php if ($isMe): ?> <em>(Kamu)</em><?php endif; ?></strong>
                            <small><?= e($meta) ?></small>
                        </span>
                        <span class="team-role<?= $isK ? ' is-lead' : '' ?>"><?= $isK ? 'Ketua' : 'Anggota' ?></span>
                        <?php if ($isLead && !$isK): ?>
                        <form class="team-del" method="POST" action="/profile/projects/<?= $pid ?>/members/remove"
                              data-confirm="<?= e($m['name']) ?> akan dikeluarkan dari kelompok dan bisa bergabung ke proyek lain."
                              data-confirm-title="Keluarkan anggota?" data-confirm-ok="Ya, keluarkan" data-confirm-variant="danger">
                            <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                            <input type="hidden" name="user_id" value="<?= (int) $m['id'] ?>">
                            <button type="submit" aria-label="Keluarkan <?= e($m['name']) ?>" title="Keluarkan dari kelompok"><?= icon('trash') ?></button>
                        </form>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($isLead): ?>
                <div class="team-add">
                    <label for="ts-<?= $pid ?>">Tambah anggota kelompok</label>
                    <div class="team-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="search" id="ts-<?= $pid ?>" placeholder="Ketik nama atau NIM mahasiswa…" autocomplete="off">
                    </div>
                    <div class="team-results" hidden aria-live="polite"></div>
                    <small class="team-hint">Hanya mahasiswa yang belum punya proyek yang muncul di sini.</small>
                </div>
                <?php else: ?>
                <div class="team-foot">
                    <span>Anggota kelompok dikelola oleh Ketua.</span>
                    <form method="POST" action="/profile/projects/<?= $pid ?>/leave"
                          data-confirm="Kamu akan keluar dari kelompok ini dan tidak lagi tercatat sebagai anggota proyeknya."
                          data-confirm-title="Keluar dari kelompok?" data-confirm-ok="Ya, keluar" data-confirm-variant="danger">
                        <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-leave">Keluar dari kelompok</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </div>
</div>

<div class="crop-modal" id="crop-modal" hidden role="dialog" aria-modal="true" aria-labelledby="crop-title">
    <div class="crop-dialog">
        <h3 id="crop-title">Atur Foto Profil</h3>
        <p class="crop-sub">Geser foto dan atur zoom sampai posisinya pas di dalam lingkaran.</p>
        <div class="crop-stage" id="crop-stage">
            <img id="crop-img" alt="" draggable="false">
            <div class="crop-mask"></div>
        </div>
        <div class="crop-zoom">
            <button type="button" id="crop-zout" aria-label="Perkecil">&minus;</button>
            <input type="range" id="crop-zoom" min="1" max="4" step="0.01" value="1" aria-label="Zoom">
            <button type="button" id="crop-zin" aria-label="Perbesar">+</button>
        </div>
        <p class="crop-tip">Tips: scroll mouse juga bisa untuk zoom.</p>
        <div class="crop-btns">
            <button type="button" class="btn btn-outline btn-sm" id="crop-reset">Reset</button>
            <button type="button" class="btn btn-outline btn-sm" id="crop-cancel">Batal</button>
            <button type="button" class="btn btn-primary btn-sm" id="crop-apply">Lanjut</button>
        </div>
    </div>
</div>

<!-- Pop up konfirmasi simpan foto -->
<div class="crop-modal" id="confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="cf-title">
    <div class="crop-dialog cf-dialog">
        <div class="cf-preview">
            <img id="cf-img" alt="Pratinjau foto profil baru">
            <span class="cf-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
        </div>
        <h3 id="cf-title">Pakai foto ini?</h3>
        <p class="crop-sub">Beginilah tampilan fotomu di TIF Hub. Foto profil lamamu akan diganti.</p>
        <div class="cf-btns">
            <button type="button" class="btn btn-primary" id="cf-save">Ya, simpan foto</button>
            <div class="row">
                <button type="button" class="btn btn-outline btn-sm" id="cf-again">Atur ulang</button>
                <button type="button" class="btn btn-outline btn-sm" id="cf-cancel">Batal</button>
            </div>
        </div>
    </div>
</div>


<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var form = $('avatar-form'), input = $('avatar-input'), errBox = $('avatar-error');
    var modal = $('crop-modal'), stage = $('crop-stage'), cimg = $('crop-img'), zoomEl = $('crop-zoom');
    var confirmModal = $('confirm-modal'), cfImg = $('cf-img'), cfSave = $('cf-save');
    var delModal = $('delete-modal');
    if (!form || !input || !modal || !confirmModal) return;

    var maxBytes    = <?= (int) $maxBytes ?>;
    var maxOriginal = 10 * 1024 * 1024;          // foto asli maks. 10 MB (hasil crop jauh lebih kecil)
    var allowed     = ['image/jpeg', 'image/png', 'image/webp'];
    var OUT         = 512;                        // hasil akhir 512x512 px
    var st          = { w: 0, h: 0, S: 300, zoom: 1, cx: 0, cy: 0 };
    var sourceUrl = null, previewUrl = null, croppedFile = null;

    function showError(m) { errBox.textContent = m; errBox.hidden = false; }
    function hideError() { errBox.hidden = true; }
    function revoke(u) { if (u) URL.revokeObjectURL(u); }

    function openModal(m)  { m.hidden = false; document.body.style.overflow = 'hidden'; }
    function closeModal(m) { m.hidden = true; if (modal.hidden && confirmModal.hidden && (!delModal || delModal.hidden)) document.body.style.overflow = ''; }

    function syncInput(file) {
        try { var dt = new DataTransfer(); if (file) dt.items.add(file); input.files = dt.files; }
        catch (e) { if (!file) input.value = ''; }
    }
    function resetAll() {
        syncInput(null); input.value = '';
        croppedFile = null;
        revoke(sourceUrl); sourceUrl = null;
        revoke(previewUrl); previewUrl = null;
        hideError();
    }

    /* ---------- geometri crop ---------- */
    function base() { return st.S / Math.min(st.w, st.h); }
    function clamp() {
        var sc = base() * st.zoom, dw = st.w * sc, dh = st.h * sc;
        st.cx = Math.min(dw / 2, Math.max(st.S - dw / 2, st.cx));
        st.cy = Math.min(dh / 2, Math.max(st.S - dh / 2, st.cy));
    }
    function render() {
        var sc = base() * st.zoom, dw = st.w * sc, dh = st.h * sc;
        cimg.style.width = dw + 'px';  cimg.style.height = dh + 'px';
        cimg.style.left = (st.cx - dw / 2) + 'px';  cimg.style.top = (st.cy - dh / 2) + 'px';
        zoomEl.value = st.zoom;
    }
    function setZoom(z) {
        z = Math.min(4, Math.max(1, z));
        var r = (base() * z) / (base() * st.zoom), h = st.S / 2;
        st.zoom = z;
        st.cx = h + (st.cx - h) * r;  st.cy = h + (st.cy - h) * r;
        clamp(); render();
    }
    function fit() {
        st.S = stage.clientWidth;
        st.zoom = 1; st.cx = st.S / 2; st.cy = st.S / 2;
        clamp(); render();
    }
    function openCropper() {
        openModal(modal);
        cimg.onload = function () { st.w = cimg.naturalWidth; st.h = cimg.naturalHeight; fit(); };
        cimg.src = sourceUrl;
        if (cimg.complete && cimg.naturalWidth) { st.w = cimg.naturalWidth; st.h = cimg.naturalHeight; fit(); }
    }
    function cancelAll() {
        closeModal(modal); closeModal(confirmModal);
        resetAll();
    }

    /* ---------- interaksi: geser, scroll, slider ---------- */
    var drag = null;
    stage.addEventListener('pointerdown', function (e) {
        drag = { x: e.clientX, y: e.clientY, cx: st.cx, cy: st.cy };
        stage.setPointerCapture(e.pointerId);
        stage.classList.add('is-drag');
    });
    stage.addEventListener('pointermove', function (e) {
        if (!drag) return;
        st.cx = drag.cx + (e.clientX - drag.x);
        st.cy = drag.cy + (e.clientY - drag.y);
        clamp(); render();
    });
    function endDrag() { drag = null; stage.classList.remove('is-drag'); }
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);
    stage.addEventListener('wheel', function (e) {
        e.preventDefault();
        setZoom(st.zoom + (e.deltaY < 0 ? 0.1 : -0.1));
    }, { passive: false });
    zoomEl.addEventListener('input', function () { setZoom(parseFloat(zoomEl.value)); });
    $('crop-zin').addEventListener('click', function () { setZoom(st.zoom + 0.2); });
    $('crop-zout').addEventListener('click', function () { setZoom(st.zoom - 0.2); });
    $('crop-reset').addEventListener('click', fit);
    $('crop-cancel').addEventListener('click', cancelAll);
    window.addEventListener('resize', function () { if (!modal.hidden && st.w) { fit(); } });

    /* ---------- lanjut: potong persegi 512x512 lalu tampilkan pop up konfirmasi ---------- */
    function toBlob(canvas, q) {
        return new Promise(function (res) { canvas.toBlob(res, 'image/jpeg', q); });
    }
    $('crop-apply').addEventListener('click', async function () {
        var canvas = document.createElement('canvas');
        canvas.width = OUT; canvas.height = OUT;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, OUT, OUT);   // PNG transparan -> latar putih
        ctx.imageSmoothingQuality = 'high';
        var k = OUT / st.S, sc = base() * st.zoom, dw = st.w * sc, dh = st.h * sc;
        ctx.drawImage(cimg, (st.cx - dw / 2) * k, (st.cy - dh / 2) * k, dw * k, dh * k);

        var blob = null, q = 0.9;
        while (q >= 0.5) {
            blob = await toBlob(canvas, q);
            if (blob && blob.size <= maxBytes) break;
            q -= 0.1;
        }
        if (!blob || blob.size > maxBytes) { cancelAll(); showError('Foto gagal diproses. Coba foto lain.'); return; }

        croppedFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
        syncInput(croppedFile);
        revoke(previewUrl);
        previewUrl = URL.createObjectURL(blob);
        cfImg.src = previewUrl;

        closeModal(modal);
        openModal(confirmModal);
    });

    /* ---------- pop up konfirmasi simpan ---------- */
    cfSave.addEventListener('click', function () {
        cfSave.disabled = true;
        cfSave.textContent = 'Menyimpan…';
        form.submit();
    });
    $('cf-again').addEventListener('click', function () {
        closeModal(confirmModal);
        if (sourceUrl) openCropper();
    });
    $('cf-cancel').addEventListener('click', cancelAll);

    /* ---------- pilih file ---------- */
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        hideError();
        if (allowed.indexOf(file.type) === -1) { resetAll(); showError('Format foto harus JPG, PNG, atau WEBP.'); return; }
        if (file.size > maxOriginal) { resetAll(); showError('Foto asli terlalu besar (maksimal 10 MB).'); return; }
        revoke(sourceUrl);
        sourceUrl = URL.createObjectURL(file);
        openCropper();
    });

    /* ---------- tutup dengan Esc / klik latar ---------- */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!confirmModal.hidden) cancelAll();
        else if (!modal.hidden) cancelAll();
        else if (delModal && !delModal.hidden) closeModal(delModal);
    });
    [modal, confirmModal, delModal].forEach(function (m) {
        if (!m) return;
        m.addEventListener('mousedown', function (e) {
            if (e.target !== m) return;
            if (m === delModal) closeModal(delModal); else cancelAll();
        });
    });

    // Label kamera bisa dibuka dengan keyboard
    var cam = document.querySelector('.pf-cam');
    cam.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
    });
})();
</script>

<script>
(function () {
    var cards = document.querySelectorAll('.team-card[data-lead="1"]');
    if (!cards.length) return;

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text != null) n.textContent = text;
        return n;
    }
    function initials(name) {
        return String(name).trim().split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0).toUpperCase(); }).join('') || '?';
    }

    cards.forEach(function (card) {
        var input = card.querySelector('.team-search input');
        var box = card.querySelector('.team-results');
        var projectId = card.getAttribute('data-project');
        var token = card.getAttribute('data-token');
        var timer = null, ctrl = null;

        function show(node) { box.textContent = ''; box.appendChild(node); box.hidden = false; }
        function message(text) { show(el('div', 'team-msg', text)); }

        function submitAdd(userId) {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = '/profile/projects/' + projectId + '/members/add';
            [['_token', token], ['user_id', userId]].forEach(function (p) {
                var i = document.createElement('input');
                i.type = 'hidden'; i.name = p[0]; i.value = p[1];
                f.appendChild(i);
            });
            document.body.appendChild(f);
            f.submit();
        }

        function render(rows) {
            if (!rows.length) { message('Tidak ada mahasiswa tersedia dengan kata kunci itu.'); return; }
            box.textContent = '';
            rows.forEach(function (r) {
                var b = el('button', 'team-res');
                b.type = 'button';
                b.appendChild(el('span', 'team-av', initials(r.name)));
                var info = el('span', 'team-info');
                info.appendChild(el('strong', '', r.name));
                info.appendChild(el('small', '', [r.nim, r.class].filter(Boolean).join(' · ')));
                b.appendChild(info);
                var go = el('span', 'go');
                go.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';
                go.appendChild(document.createTextNode('Tambah'));
                b.appendChild(go);
                b.addEventListener('click', function () { b.disabled = true; submitAdd(r.id); });
                box.appendChild(b);
            });
            box.hidden = false;
        }

        function search() {
            var q = input.value.trim();
            if (q.length < 2) { box.hidden = true; box.textContent = ''; return; }
            if (ctrl) ctrl.abort();
            ctrl = ('AbortController' in window) ? new AbortController() : null;
            message('Mencari…');
            fetch('/profile/students/search?q=' + encodeURIComponent(q), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                signal: ctrl ? ctrl.signal : undefined
            }).then(function (r) {
                if (!r.ok) throw new Error('http');
                return r.json();
            }).then(function (d) {
                if (input.value.trim() !== q) return;
                render(d.results || []);
            }).catch(function (e) {
                if (e && e.name === 'AbortError') return;
                message('Gagal memuat daftar mahasiswa. Coba lagi.');
            });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(search, 250);
        });
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    });
})();
</script>
