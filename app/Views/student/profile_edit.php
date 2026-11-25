<?php
/**
 * @var array  $user    data session (navbar)
 * @var array  $row     data lengkap dari database
 * @var array  $old     isian sebelumnya (jika validasi gagal)
 * @var array  $errors  pesan error per kolom
 * @var string $tab     'profile' | 'security'
 * @var string $csrf
 */
use App\Helpers\ImageUploader;

$avatarUrl = ImageUploader::avatarUrl($row['avatar'] ?? null);
$val = function (string $key, $default = '') use ($old, $row) {
    return e((string) ($old[$key] ?? $row[$key] ?? $default));
};
$err = function (string $key) use ($errors) {
    return isset($errors[$key]) ? '<span class="fe-err">' . e($errors[$key]) . '</span>' : '';
};
$has = function (string $key) use ($errors) {
    return isset($errors[$key]) ? ' is-invalid' : '';
};
$curSem = (int) ($old['semester'] ?? $row['semester'] ?? 1);
$maxSem = max(8, $curSem);
?>
<style>
    .ed-back { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 700; color: var(--muted); margin-bottom: .8rem; transition: color .15s ease, transform .15s ease; }
    .ed-back:hover { color: var(--primary); transform: translateX(-3px); }
    .ed-back .ico { width: 16px; height: 16px; }
    .ed-head { margin-bottom: 1.4rem; }
    .ed-head h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -.5px; }
    .ed-head p { color: var(--muted); margin-top: .2rem; }

    .ed-layout { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .ed-side { position: sticky; top: 92px; }
    .ed-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow); overflow: hidden; }

    /* ---------- Sidebar ---------- */
    .ed-cover { height: 92px; background: var(--grad); position: relative; overflow: hidden; }
    .ed-cover::before, .ed-cover::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.12); }
    .ed-cover::before { width: 140px; height: 140px; right: -36px; top: -56px; }
    .ed-cover::after { width: 70px; height: 70px; left: 26px; bottom: -34px; }
    .ed-who { text-align: center; padding: 0 1.4rem 1.2rem; }
    .ed-avatar { position: relative; z-index: 2; width: 92px; height: 92px; margin: -46px auto 0; border-radius: 50%; overflow: hidden; border: 4px solid #fff; background: var(--grad); color: #fff; display: grid; place-items: center; font-size: 1.8rem; font-weight: 800; box-shadow: 0 8px 22px rgba(2,37,51,.18); }
    .ed-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ed-who h2 { font-size: 1.15rem; font-weight: 800; margin-top: .75rem; letter-spacing: -.2px; }
    .ed-who .em { color: var(--muted); font-size: .82rem; word-break: break-all; }
    .ed-photo-actions { display: flex; justify-content: center; gap: .5rem; flex-wrap: wrap; margin-top: .8rem; }
    .ed-photo-actions .ed-photo-link { margin-top: 0; }
    .ed-photo-link.is-danger { border: 0; font-family: inherit; cursor: pointer; color: #b91c1c; background: #fee2e2; }
    .ed-photo-link.is-danger:hover { background: #fecaca; }
    .del-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 1rem; background: rgba(2,37,51,.62); backdrop-filter: blur(3px); }
    .del-modal[hidden] { display: none; }
    .del-dialog { width: 100%; max-width: 360px; background: #fff; border-radius: 22px; padding: 1.8rem 1.5rem 1.4rem; text-align: center; box-shadow: 0 30px 80px rgba(2,37,51,.4); animation: edIn .22s ease both; }
    .del-dialog img { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 4px solid #fee2e2; margin-bottom: 1rem; }
    .del-dialog h3 { font-size: 1.15rem; font-weight: 800; }
    .del-dialog p { font-size: .85rem; color: var(--muted); margin-top: .3rem; }
    .del-btns { display: grid; gap: .55rem; margin-top: 1.3rem; }
    .del-btns .btn { width: 100%; }
    .del-confirm { background: #dc2626; color: #fff; }
    .del-confirm:hover { background: #b91c1c; }
    .ed-photo-link { display: inline-flex; align-items: center; gap: .4rem; margin-top: .8rem; font-size: .8rem; font-weight: 700; color: var(--primary); padding: .35rem .8rem; border-radius: 999px; background: var(--primary-light); transition: background .15s ease, transform .15s ease; }
    .ed-photo-link:hover { background: #d3ecf3; transform: translateY(-1px); }
    .ed-photo-link .ico { width: 15px; height: 15px; }

    .ed-nav { display: grid; gap: .35rem; padding: .8rem; border-top: 1px dashed var(--border); }
    .ed-tab { display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left; padding: .7rem .85rem; border: 0; border-radius: 12px; background: transparent; font: inherit; font-size: .9rem; font-weight: 700; color: var(--muted); cursor: pointer; transition: background .15s ease, color .15s ease; }
    .ed-tab .ico { width: 19px; height: 19px; flex: none; }
    .ed-tab small { display: block; font-size: .72rem; font-weight: 500; color: var(--muted); }
    .ed-tab:hover { background: #f1f7f9; color: var(--text); }
    .ed-tab.is-active { background: var(--primary-light); color: var(--primary-dark); }
    .ed-tab.is-active small { color: var(--primary); }
    .ed-tab:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

    /* ---------- Panel form ---------- */
    .ed-panel[hidden] { display: none; }
    .ed-panel { animation: edIn .28s ease both; }
    @keyframes edIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .ed-panel { animation: none; } }
    .ed-panel-head { display: flex; align-items: center; gap: .9rem; padding: 1.3rem 1.6rem; border-bottom: 1px solid var(--border); background: var(--grad-soft); }
    .ed-panel-head .ph-ico { width: 44px; height: 44px; border-radius: 14px; display: grid; place-items: center; background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); flex: none; }
    .ed-panel-head .ph-ico .ico { width: 22px; height: 22px; }
    .ed-panel-head h2 { font-size: 1.1rem; font-weight: 800; letter-spacing: -.2px; }
    .ed-panel-head p { font-size: .82rem; color: var(--muted); }
    .ed-form { padding: 1.5rem 1.6rem 1.6rem; }
    .ed-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.25rem; }
    .ed-grid .full { grid-column: 1 / -1; }

    .ed-field { position: relative; }
    .ed-field > .ico-l { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--muted); pointer-events: none; }
    .ed-field .form-control { padding-left: 2.7rem; }
    .ed-field.has-eye .form-control { padding-right: 2.8rem; }
    .ed-field .form-control:focus ~ .ico-l, .ed-field:focus-within > .ico-l { color: var(--primary); }
    .ed-eye { position: absolute; right: .45rem; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; display: grid; place-items: center; border: 0; background: transparent; border-radius: 10px; color: var(--muted); cursor: pointer; }
    .ed-eye:hover { background: #f1f7f9; color: var(--primary); }
    .ed-eye .ico { width: 18px; height: 18px; }
    .ed-eye .off { display: none; }
    .ed-eye.is-on .on { display: none; }
    .ed-eye.is-on .off { display: block; }
    .form-control[readonly] { background: #f4f8fa; color: var(--muted); cursor: not-allowed; }
    .form-control.is-invalid { border-color: #ef4444; background: #fffafa; }
    .form-control.is-invalid:focus { box-shadow: 0 0 0 4px rgba(239,68,68,.14); }
    .fe-err { display: block; margin-top: .35rem; font-size: .78rem; font-weight: 600; color: #b91c1c; }
    .lock-tag { display: inline-flex; align-items: center; gap: .25rem; margin-left: .4rem; font-size: .68rem; font-weight: 700; color: var(--muted); background: #eef4f7; border-radius: 999px; padding: .1rem .5rem; vertical-align: middle; }
    .lock-tag .ico { width: 11px; height: 11px; }

    .ed-actions { display: flex; align-items: center; justify-content: flex-end; gap: .7rem; flex-wrap: wrap; margin-top: .6rem; padding-top: 1.2rem; border-top: 1px dashed var(--border); }
    .ed-dirty { margin-right: auto; display: inline-flex; align-items: center; gap: .45rem; font-size: .8rem; font-weight: 700; color: #b45309; opacity: 0; transition: opacity .2s ease; }
    .ed-dirty::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; }
    .ed-dirty.is-show { opacity: 1; }
    .btn[disabled] { opacity: .55; cursor: not-allowed; transform: none !important; box-shadow: none; }

    /* ---------- Kekuatan password ---------- */
    .pw-meter { display: grid; grid-template-columns: repeat(4, 1fr); gap: .35rem; margin-top: .6rem; }
    .pw-meter i { height: 5px; border-radius: 999px; background: #e3edf1; transition: background .2s ease; }
    .pw-meter[data-s="1"] i:nth-child(1) { background: #ef4444; }
    .pw-meter[data-s="2"] i:nth-child(-n+2) { background: #f59e0b; }
    .pw-meter[data-s="3"] i:nth-child(-n+3) { background: #22c55e; }
    .pw-meter[data-s="4"] i { background: #059669; }
    .pw-label { display: flex; justify-content: space-between; font-size: .76rem; margin-top: .35rem; color: var(--muted); font-weight: 600; }
    .pw-match { font-size: .78rem; font-weight: 700; margin-top: .35rem; display: block; min-height: 1.1em; }
    .pw-match.ok { color: #059669; }
    .pw-match.no { color: #b91c1c; }
    .ed-tips { display: grid; gap: .3rem; margin: 0 0 1.2rem; padding: .85rem 1rem; list-style: none; background: #f4fafc; border: 1px solid #d8ebf1; border-radius: 12px; font-size: .8rem; color: var(--muted); }
    .ed-tips li { display: flex; gap: .5rem; align-items: center; }
    .ed-tips li::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); flex: none; }

    /* ---------- Dropdown custom ---------- */
    .ed-field.cs-on > select { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .cs-trigger { display: flex; align-items: center; justify-content: space-between; gap: .6rem; width: 100%; text-align: left; font: inherit; color: var(--text); cursor: pointer; background: #fff; }
    .cs-trigger .cs-val { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cs-chev { width: 18px; height: 18px; flex: none; color: var(--muted); transition: transform .25s cubic-bezier(.34,1.56,.64,1), color .2s ease; }
    .cs-trigger[aria-expanded="true"] { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(3,121,146,.15); }
    .cs-trigger[aria-expanded="true"] .cs-chev { transform: rotate(180deg); color: var(--primary); }
    .cs-trigger:hover .cs-chev { color: var(--primary); }

    .cs-menu { position: fixed; z-index: 2000; margin: 0; padding: .4rem; list-style: none; background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 24px 60px rgba(2,37,51,.22), 0 4px 14px rgba(2,37,51,.08); max-height: 440px; overflow-y: auto; overscroll-behavior: contain;
        opacity: 0; visibility: hidden; pointer-events: none; transform: translateY(-8px) scale(.97); transform-origin: top center; transition: opacity .18s ease, transform .22s cubic-bezier(.22,1,.36,1), visibility 0s linear .22s; }
    .cs-menu.is-up { transform-origin: bottom center; transform: translateY(8px) scale(.97); }
    .cs-menu.is-open { opacity: 1; visibility: visible; pointer-events: auto; transform: none; transition-delay: 0s; }
    .cs-menu::-webkit-scrollbar { width: 8px; }
    .cs-menu::-webkit-scrollbar-thumb { background: #cfe3ea; border-radius: 8px; }
    .cs-menu-head { padding: .35rem .7rem .45rem; font-size: .68rem; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: var(--muted); }
    .cs-opt { display: flex; align-items: center; gap: .7rem; padding: .4rem .6rem; border-radius: 11px; cursor: pointer; font-size: .92rem; font-weight: 600; color: var(--text); transition: background .12s ease, color .12s ease; }
    .cs-opt .cs-num { width: 28px; height: 28px; border-radius: 9px; display: grid; place-items: center; flex: none; font-size: .82rem; font-weight: 800; color: var(--primary-dark); background: #eef6f9; transition: background .15s ease, color .15s ease, transform .2s cubic-bezier(.34,1.56,.64,1); }
    .cs-opt .cs-txt { flex: 1; min-width: 0; }
    .cs-opt .cs-check { width: 18px; height: 18px; flex: none; color: var(--primary); opacity: 0; transform: scale(.4); transition: opacity .15s ease, transform .25s cubic-bezier(.34,1.56,.64,1); }
    .cs-opt.is-active { background: var(--primary-light); }
    .cs-opt.is-active .cs-num { transform: scale(1.08); background: #fff; }
    .cs-opt[aria-selected="true"] { color: var(--primary-dark); font-weight: 800; }
    .cs-opt[aria-selected="true"] .cs-num { background: var(--grad); color: #fff; }
    .cs-opt[aria-selected="true"] .cs-check { opacity: 1; transform: none; }
    @media (prefers-reduced-motion: reduce) { .cs-menu, .cs-chev, .cs-opt .cs-num, .cs-opt .cs-check { transition: none; } }

    @media (max-width: 960px) {
        .ed-layout { grid-template-columns: 1fr; }
        .ed-side { position: static; }
        .ed-nav { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 560px) {
        .ed-grid { grid-template-columns: 1fr; }
        .ed-nav { grid-template-columns: 1fr; }
        .ed-form, .ed-panel-head { padding-left: 1.1rem; padding-right: 1.1rem; }
        .ed-actions .btn { flex: 1; }
    }
</style>

<a href="/profile" class="ed-back"><?= icon('arrow-left') ?> Kembali ke profil</a>
<div class="ed-head">
    <h1>Edit Profil</h1>
    <p>Perbarui data dirimu dan jaga keamanan akunmu.</p>
</div>

<div class="ed-layout">
    <!-- ===================== SIDEBAR ===================== -->
    <aside class="ed-side">
        <div class="ed-card">
            <div class="ed-cover"></div>
            <div class="ed-who">
                <div class="ed-avatar">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= e($avatarUrl) ?>" alt="Foto profil <?= e($row['name'] ?? '') ?>">
                    <?php else: ?>
                        <?= e(initials($row['name'] ?? '')) ?>
                    <?php endif; ?>
                </div>
                <h2><?= e($row['name'] ?? '') ?></h2>
                <p class="em"><?= e($row['email'] ?? '') ?></p>
                <div class="ed-photo-actions">
                    <a href="/profile" class="ed-photo-link"><?= icon('camera') ?> Ganti foto</a>
                    <?php if ($avatarUrl): ?>
                    <button type="button" class="ed-photo-link is-danger" id="open-del"><?= icon('trash') ?> Hapus foto</button>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="ed-nav" role="tablist" aria-label="Pengaturan akun">
                <button type="button" class="ed-tab <?= $tab === 'profile' ? 'is-active' : '' ?>" data-tab="profile" role="tab" aria-selected="<?= $tab === 'profile' ? 'true' : 'false' ?>">
                    <?= icon('user') ?><span>Data Diri<small>Nama, email, kelas</small></span>
                </button>
                <button type="button" class="ed-tab <?= $tab === 'security' ? 'is-active' : '' ?>" data-tab="security" role="tab" aria-selected="<?= $tab === 'security' ? 'true' : 'false' ?>">
                    <?= icon('lock') ?><span>Keamanan<small>Ganti password</small></span>
                </button>
            </nav>
        </div>
    </aside>

    <!-- ===================== KONTEN ===================== -->
    <div class="ed-main">
        <!-- ---------- Data diri ---------- -->
        <section class="ed-card ed-panel" id="panel-profile" <?= $tab === 'profile' ? '' : 'hidden' ?>>
            <div class="ed-panel-head">
                <span class="ph-ico"><?= icon('user') ?></span>
                <div>
                    <h2>Informasi Akun</h2>
                    <p>Data ini tampil di profil dan navbar kamu.</p>
                </div>
            </div>
            <form class="ed-form" id="form-profile" method="POST" action="/profile/update" novalidate>
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <div class="ed-grid">
                    <div class="form-group full">
                        <label for="name">Nama Lengkap</label>
                        <div class="ed-field">
                            <input type="text" class="form-control<?= $has('name') ?>" id="name" name="name" value="<?= $val('name') ?>" maxlength="100" autocomplete="name" required>
                            <?= icon('user', 'ico-l') ?>
                        </div>
                        <?= $err('name') ?>
                    </div>

                    <div class="form-group full">
                        <label for="email">Email</label>
                        <div class="ed-field">
                            <input type="email" class="form-control<?= $has('email') ?>" id="email" name="email" value="<?= $val('email') ?>" maxlength="150" autocomplete="email" required>
                            <?= icon('mail', 'ico-l') ?>
                        </div>
                        <?= $err('email') ?>
                    </div>

                    <div class="form-group full">
                        <label for="nim">NIM</label>
                        <div class="ed-field">
                            <input type="text" class="form-control<?= $has('nim') ?>" id="nim" name="nim" value="<?= $val('nim') ?>" maxlength="20" placeholder="Contoh: 2021001" inputmode="numeric" autocomplete="off" required>
                            <?= icon('id-card', 'ico-l') ?>
                        </div>
                        <?= $err('nim') ?>
                    </div>

                    <div class="form-group">
                        <label for="class">Kelas</label>
                        <div class="ed-field">
                            <input type="text" class="form-control<?= $has('class') ?>" id="class" name="class" value="<?= $val('class') ?>" maxlength="20" placeholder="Contoh: A" style="text-transform:uppercase;">
                            <?= icon('users-class', 'ico-l') ?>
                        </div>
                        <?= $err('class') ?>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <div class="ed-field">
                            <select class="form-control<?= $has('semester') ?>" id="semester" name="semester">
                                <?php for ($i = 1; $i <= $maxSem; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i === $curSem ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <?= icon('calendar', 'ico-l') ?>
                        </div>
                        <?= $err('semester') ?>
                    </div>
                </div>

                <div class="ed-actions">
                    <span class="ed-dirty" id="dirty-profile">Ada perubahan belum disimpan</span>
                    <a href="/profile" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary" id="save-profile" disabled><?= icon('save') ?> Simpan perubahan</button>
                </div>
            </form>
        </section>

        <!-- ---------- Keamanan ---------- -->
        <section class="ed-card ed-panel" id="panel-security" <?= $tab === 'security' ? '' : 'hidden' ?>>
            <div class="ed-panel-head">
                <span class="ph-ico"><?= icon('shield') ?></span>
                <div>
                    <h2>Ganti Password</h2>
                    <p>Masukkan password lama untuk memastikan ini benar-benar kamu.</p>
                </div>
            </div>
            <form class="ed-form" id="form-security" method="POST" action="/profile/password" novalidate>
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">

                <ul class="ed-tips">
                    <li>Minimal 6 karakter, makin panjang makin aman.</li>
                    <li>Kombinasikan huruf besar, huruf kecil, angka, dan simbol.</li>
                </ul>

                <div class="form-group">
                    <label for="current_password">Password saat ini</label>
                    <div class="ed-field has-eye">
                        <input type="password" class="form-control<?= $has('current_password') ?>" id="current_password" name="current_password" autocomplete="current-password" required>
                        <?= icon('lock', 'ico-l') ?>
                        <button type="button" class="ed-eye" data-eye="current_password" aria-label="Tampilkan password"><?= icon('eye', 'on') ?><?= icon('eye-off', 'off') ?></button>
                    </div>
                    <?= $err('current_password') ?>
                </div>

                <div class="ed-grid">
                    <div class="form-group">
                        <label for="new_password">Password baru</label>
                        <div class="ed-field has-eye">
                            <input type="password" class="form-control<?= $has('new_password') ?>" id="new_password" name="new_password" autocomplete="new-password" minlength="6" required>
                            <?= icon('lock', 'ico-l') ?>
                            <button type="button" class="ed-eye" data-eye="new_password" aria-label="Tampilkan password"><?= icon('eye', 'on') ?><?= icon('eye-off', 'off') ?></button>
                        </div>
                        <div class="pw-meter" id="pw-meter" data-s="0"><i></i><i></i><i></i><i></i></div>
                        <div class="pw-label"><span>Kekuatan password</span><b id="pw-text">-</b></div>
                        <?= $err('new_password') ?>
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirm">Ulangi password baru</label>
                        <div class="ed-field has-eye">
                            <input type="password" class="form-control<?= $has('new_password_confirm') ?>" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password" required>
                            <?= icon('lock', 'ico-l') ?>
                            <button type="button" class="ed-eye" data-eye="new_password_confirm" aria-label="Tampilkan password"><?= icon('eye', 'on') ?><?= icon('eye-off', 'off') ?></button>
                        </div>
                        <span class="pw-match" id="pw-match"></span>
                        <?= $err('new_password_confirm') ?>
                    </div>
                </div>

                <div class="ed-actions">
                    <a href="/profile" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary" id="save-security" disabled><?= icon('shield') ?> Ganti password</button>
                </div>
            </form>
        </section>
    </div>
</div>

<?php if ($avatarUrl): ?>
<div class="del-modal" id="del-modal" hidden role="alertdialog" aria-modal="true" aria-labelledby="del-title">
    <div class="del-dialog">
        <img src="<?= e($avatarUrl) ?>" alt="Foto profil saat ini">
        <h3 id="del-title">Hapus foto profil?</h3>
        <p>Fotomu akan diganti dengan inisial <strong><?= e(initials($row['name'] ?? '')) ?></strong>. Kamu bisa unggah foto baru kapan saja.</p>
        <form method="POST" action="/profile/avatar/remove" id="del-form" class="del-btns">
            <input type="hidden" name="_token" value="<?= e($csrf) ?>">
            <button type="submit" class="btn del-confirm" id="del-confirm">Ya, hapus foto</button>
            <button type="button" class="btn btn-outline btn-sm" id="del-cancel">Batal</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };

    /* ---------- Hapus foto ---------- */
    var dm = $('del-modal');
    if (dm) {
        $('open-del').addEventListener('click', function () { dm.hidden = false; });
        $('del-cancel').addEventListener('click', function () { dm.hidden = true; });
        dm.addEventListener('mousedown', function (e) { if (e.target === dm) dm.hidden = true; });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') dm.hidden = true; });
        $('del-form').addEventListener('submit', function () {
            var b = $('del-confirm'); b.disabled = true; b.textContent = 'Menghapus…';
        });
    }

    /* ---------- Dropdown custom (menggantikan select bawaan browser) ---------- */
    (function () {
        var CHEV = '<svg class="cs-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        var CHECK = '<svg class="cs-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

        function enhance(select) {
            var wrap = select.closest('.ed-field');
            if (!wrap) return;
            var isSem = select.id === 'semester';
            var uid = 'cs-' + select.id;

            var trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'form-control cs-trigger' + (select.classList.contains('is-invalid') ? ' is-invalid' : '');
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.setAttribute('aria-controls', uid);
            trigger.innerHTML = '<span class="cs-val"></span>' + CHEV;
            select.insertAdjacentElement('afterend', trigger);

            var menu = document.createElement('ul');
            menu.className = 'cs-menu';
            menu.id = uid;
            menu.setAttribute('role', 'listbox');
            menu.tabIndex = -1;
            if (isSem) {
                var head = document.createElement('li');
                head.className = 'cs-menu-head';
                head.setAttribute('role', 'presentation');
                head.textContent = 'Pilih semester';
                menu.appendChild(head);
            }
            var opts = [];
            Array.prototype.forEach.call(select.options, function (o, i) {
                var li = document.createElement('li');
                li.className = 'cs-opt';
                li.id = uid + '-' + i;
                li.setAttribute('role', 'option');
                li.dataset.value = o.value;
                li.innerHTML = (isSem ? '<span class="cs-num">' + o.value + '</span>' : '') +
                    '<span class="cs-txt">' + o.textContent + '</span>' + CHECK;
                menu.appendChild(li);
                opts.push(li);
            });
            document.body.appendChild(menu);
            wrap.classList.add('cs-on');

            var active = -1, buf = '', bufT = null;

            function selectedIndex() { return select.selectedIndex < 0 ? 0 : select.selectedIndex; }
            function paint() {
                var o = select.options[selectedIndex()];
                trigger.querySelector('.cs-val').textContent = o ? o.textContent : '';
                opts.forEach(function (li, i) { li.setAttribute('aria-selected', i === selectedIndex() ? 'true' : 'false'); });
            }
            function setActive(i, scroll) {
                if (i < 0 || i >= opts.length) return;
                if (active > -1) opts[active].classList.remove('is-active');
                active = i;
                opts[i].classList.add('is-active');
                menu.setAttribute('aria-activedescendant', opts[i].id);
                if (scroll !== false) opts[i].scrollIntoView({ block: 'nearest' });
            }
            function place() {
                var r = trigger.getBoundingClientRect();
                var h = Math.min(menu.scrollHeight, 440);
                var below = window.innerHeight - r.bottom, above = r.top;
                var up = below < h + 16 && above > below;
                menu.classList.toggle('is-up', up);
                menu.style.left = r.left + 'px';
                menu.style.width = r.width + 'px';
                menu.style.maxHeight = Math.max(160, Math.min(440, (up ? above : below) - 16)) + 'px';
                if (up) { menu.style.top = 'auto'; menu.style.bottom = (window.innerHeight - r.top + 8) + 'px'; }
                else { menu.style.bottom = 'auto'; menu.style.top = (r.bottom + 8) + 'px'; }
            }
            function isOpen() { return menu.classList.contains('is-open'); }
            function open() {
                if (isOpen()) return;
                place();
                menu.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                setActive(selectedIndex());
            }
            function close(focusBack) {
                if (!isOpen()) return;
                menu.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                if (focusBack) trigger.focus();
            }
            function choose(i) {
                var changed = select.selectedIndex !== i;
                select.selectedIndex = i;
                paint();
                close(true);
                if (changed) {
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            trigger.addEventListener('click', function () { isOpen() ? close(false) : open(); });
            trigger.addEventListener('keydown', function (e) {
                var k = e.key;
                if (!isOpen()) {
                    if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'Enter' || k === ' ') { e.preventDefault(); open(); }
                    return;
                }
                if (k === 'ArrowDown') { e.preventDefault(); setActive(Math.min(active + 1, opts.length - 1)); }
                else if (k === 'ArrowUp') { e.preventDefault(); setActive(Math.max(active - 1, 0)); }
                else if (k === 'Home') { e.preventDefault(); setActive(0); }
                else if (k === 'End') { e.preventDefault(); setActive(opts.length - 1); }
                else if (k === 'Enter' || k === ' ') { e.preventDefault(); choose(active); }
                else if (k === 'Escape') { e.preventDefault(); e.stopPropagation(); close(true); }
                else if (k === 'Tab') { close(false); }
                else if (/^[0-9a-z]$/i.test(k)) {
                    buf += k.toLowerCase();
                    clearTimeout(bufT); bufT = setTimeout(function () { buf = ''; }, 700);
                    for (var i = 0; i < opts.length; i++) {
                        if (select.options[i].value.toLowerCase().indexOf(buf) === 0) { setActive(i); break; }
                    }
                }
            });
            opts.forEach(function (li, i) {
                li.addEventListener('mousemove', function () { if (active !== i) setActive(i, false); });
                li.addEventListener('click', function () { choose(i); });
            });
            document.addEventListener('pointerdown', function (e) {
                if (isOpen() && !menu.contains(e.target) && !trigger.contains(e.target)) close(false);
            });
            window.addEventListener('resize', function () { if (isOpen()) place(); });
            window.addEventListener('scroll', function (e) {
                if (!isOpen() || menu.contains(e.target)) return;
                place();
            }, true);

            // klik label ikut membuka dropdown
            var lbl = document.querySelector('label[for="' + select.id + '"]');
            if (lbl) lbl.addEventListener('click', function (e) { e.preventDefault(); trigger.focus(); });

            paint();
        }

        document.querySelectorAll('.ed-field select').forEach(enhance);
    })();

    /* ---------- Tab ---------- */
    var tabs = document.querySelectorAll('.ed-tab');
    function showTab(name) {
        tabs.forEach(function (t) {
            var on = t.getAttribute('data-tab') === name;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        ['profile', 'security'].forEach(function (n) { $('panel-' + n).hidden = (n !== name); });
        try { history.replaceState(null, '', '#' + name); } catch (e) {}
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { showTab(t.getAttribute('data-tab')); }); });
    if (location.hash === '#security' || location.hash === '#profile') {
        // hash hanya dipakai bila server tidak sedang menampilkan error di tab tertentu
        if (!document.querySelector('.fe-err')) showTab(location.hash.slice(1));
    }

    /* ---------- Tombol simpan aktif hanya jika ada perubahan ---------- */
    var fp = $('form-profile'), sp = $('save-profile'), dp = $('dirty-profile');
    var initial = {};
    ['name', 'nim', 'email', 'class', 'semester'].forEach(function (k) { initial[k] = fp.elements[k].value; });
    // Bila halaman muncul kembali setelah error, anggap sudah berubah
    var startDirty = !!document.querySelector('#panel-profile .fe-err');
    function checkDirty() {
        var changed = startDirty;
        ['name', 'nim', 'email', 'class', 'semester'].forEach(function (k) {
            if (fp.elements[k].value.trim() !== initial[k].trim()) changed = true;
        });
        sp.disabled = !changed;
        dp.classList.toggle('is-show', changed);
    }
    fp.addEventListener('input', function () { startDirty = false; checkDirty(); });
    fp.addEventListener('change', function () { startDirty = false; checkDirty(); });
    checkDirty();

    fp.addEventListener('submit', function (e) {
        var ok = true;
        fp.querySelectorAll('.js-err').forEach(function (n) { n.remove(); });
        function bad(el, msg) {
            ok = false; el.classList.add('is-invalid');
            var s = document.createElement('span'); s.className = 'fe-err js-err'; s.textContent = msg;
            el.closest('.form-group').appendChild(s);
        }
        fp.querySelectorAll('.is-invalid').forEach(function (n) { n.classList.remove('is-invalid'); });
        var name = fp.elements.name, email = fp.elements.email, nim = fp.elements.nim;
        if (name.value.trim().length < 3) bad(name, 'Nama minimal 3 karakter.');
        if (!/^[A-Za-z0-9-]{3,20}$/.test(nim.value.trim())) bad(nim, 'NIM 3-20 karakter: huruf, angka, atau minus.');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) bad(email, 'Format email tidak valid.');
        if (!ok) { e.preventDefault(); return; }
        sp.disabled = true; sp.textContent = 'Menyimpan…';
    });

    /* ---------- Tampil/sembunyi password ---------- */
    document.querySelectorAll('.ed-eye').forEach(function (b) {
        b.addEventListener('click', function () {
            var inp = $(b.getAttribute('data-eye'));
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            b.classList.toggle('is-on', show);
            b.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    /* ---------- Kekuatan & kecocokan password ---------- */
    var cur = $('current_password'), pw = $('new_password'), pc = $('new_password_confirm');
    var meter = $('pw-meter'), ptxt = $('pw-text'), pm = $('pw-match'), ss = $('save-security');
    var labels = ['-', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'];
    function score(v) {
        if (!v) return 0;
        var s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        return Math.min(4, Math.max(v.length >= 6 ? 1 : 0, s));
    }
    function checkSec() {
        var s = score(pw.value);
        meter.setAttribute('data-s', s);
        ptxt.textContent = labels[s];
        if (pc.value === '') { pm.textContent = ''; pm.className = 'pw-match'; }
        else if (pc.value === pw.value) { pm.textContent = 'Password cocok'; pm.className = 'pw-match ok'; }
        else { pm.textContent = 'Password belum cocok'; pm.className = 'pw-match no'; }
        ss.disabled = !(cur.value !== '' && pw.value.length >= 6 && pw.value === pc.value);
    }
    [cur, pw, pc].forEach(function (i) { i.addEventListener('input', checkSec); });
    checkSec();
    $('form-security').addEventListener('submit', function () { ss.disabled = true; ss.textContent = 'Menyimpan…'; });
})();
</script>
