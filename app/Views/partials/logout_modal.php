<?php
/**
 * Pop up konfirmasi logout (dipakai di layout mahasiswa & admin).
 * Otomatis menangkap semua link ke /logout, tidak perlu mengubah tombolnya.
 *
 * @var array|null $user
 */
$loUser   = $user ?? [];
$loName   = (string) ($loUser['name'] ?? 'Pengguna');
$loRole   = (($loUser['role'] ?? '') === 'admin') ? 'Administrator' : 'Mahasiswa';
$loAvatar = !empty($loUser['avatar']) ? \App\Helpers\ImageUploader::avatarUrl($loUser['avatar']) : null;
?>
<style>
    .lo-overlay { position: fixed; inset: 0; z-index: 1100; display: grid; place-items: center; padding: 1rem; background: rgba(2,37,51,.62); backdrop-filter: blur(3px); }
    .lo-overlay[hidden] { display: none; }
    .lo-dialog { width: 100%; max-width: 380px; background: #fff; color: var(--text, #062534); border-radius: 22px; padding: 1.8rem 1.5rem 1.4rem; text-align: center; box-shadow: 0 30px 80px rgba(2,37,51,.4); animation: loPop .22s ease; }
    @keyframes loPop { from { opacity: 0; transform: translateY(14px) scale(.96); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .lo-dialog { animation: none; } }
    .lo-icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; display: grid; place-items: center; background: #fef2f2; color: #dc2626; box-shadow: 0 0 0 8px #fff5f5; }
    .lo-icon svg { width: 32px; height: 32px; }
    .lo-dialog h3 { font-size: 1.25rem; font-weight: 800; letter-spacing: -.3px; margin: 0; }
    .lo-sub { font-size: .9rem; color: var(--muted, #5b7480); margin: .4rem 0 0; line-height: 1.5; }
    .lo-user { display: flex; align-items: center; gap: .75rem; text-align: left; margin: 1.15rem 0 0; padding: .7rem .9rem; background: #f1f7f9; border-radius: 14px; }
    .lo-avatar { width: 42px; height: 42px; border-radius: 50%; flex: none; overflow: hidden; display: grid; place-items: center; color: #fff; font-weight: 800; font-size: .95rem; background: linear-gradient(135deg, #024c5d, #19849c 60%, #299cb8); }
    .lo-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .lo-user strong { display: block; font-size: .95rem; line-height: 1.2; }
    .lo-user span { font-size: .78rem; color: var(--muted, #5b7480); font-weight: 600; }
    .lo-btns { display: grid; gap: .55rem; margin-top: 1.3rem; }
    .lo-btn { font: inherit; font-weight: 700; font-size: .95rem; border-radius: 12px; padding: .75rem 1rem; cursor: pointer; border: 1.5px solid transparent; transition: background .15s ease, transform .15s ease; }
    .lo-btn:hover { transform: translateY(-1px); }
    .lo-btn.danger { background: #dc2626; color: #fff; border-color: #dc2626; }
    .lo-btn.danger:hover { background: #b91c1c; border-color: #b91c1c; }
    .lo-btn.danger[disabled] { opacity: .75; cursor: wait; transform: none; }
    .lo-btn.ghost { background: #fff; color: var(--text, #062534); border-color: var(--border, #d7e5ea); }
    .lo-btn.ghost:hover { border-color: var(--primary, #037992); color: var(--primary, #037992); }
    .lo-btn:focus-visible { outline: 3px solid rgba(3,121,146,.35); outline-offset: 2px; }
</style>

<div class="lo-overlay" id="logout-modal" hidden role="alertdialog" aria-modal="true" aria-labelledby="lo-title" aria-describedby="lo-desc">
    <div class="lo-dialog">
        <div class="lo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
        </div>
        <h3 id="lo-title">Yakin ingin keluar?</h3>
        <p class="lo-sub" id="lo-desc">Kamu akan keluar dari akun ini dan perlu login lagi untuk masuk ke TIF Hub.</p>

        <div class="lo-user">
            <span class="lo-avatar">
                <?php if ($loAvatar): ?>
                    <img src="<?= e($loAvatar) ?>" alt="">
                <?php else: ?>
                    <?= e(initials($loName)) ?>
                <?php endif; ?>
            </span>
            <div>
                <strong><?= e($loName) ?></strong>
                <span><?= e($loRole) ?></span>
            </div>
        </div>

        <div class="lo-btns">
            <button type="button" class="lo-btn danger" id="lo-confirm">Ya, keluar</button>
            <button type="button" class="lo-btn ghost" id="lo-cancel">Batal</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('logout-modal');
    var okBtn = document.getElementById('lo-confirm');
    var noBtn = document.getElementById('lo-cancel');
    if (!modal) return;
    var href = '/logout', lastFocus = null;

    function open(link) {
        lastFocus = document.activeElement;
        href = link.getAttribute('href') || '/logout';
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        noBtn.focus();                       // fokus ke "Batal" agar tidak sengaja logout
    }
    function close() {
        modal.hidden = true;
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    // Tangkap semua link logout (navbar mahasiswa, sidebar admin, dll.)
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest ? e.target.closest('a[href="/logout"]') : null;
        if (!a) return;
        e.preventDefault();
        open(a);
    });

    okBtn.addEventListener('click', function () {
        okBtn.disabled = true;
        okBtn.textContent = 'Keluar…';
        window.location.href = href;
    });
    noBtn.addEventListener('click', close);
    modal.addEventListener('mousedown', function (e) { if (e.target === modal) close(); });

    document.addEventListener('keydown', function (e) {
        if (modal.hidden) return;
        if (e.key === 'Escape') { close(); return; }
        if (e.key === 'Tab') {               // fokus hanya berputar di dalam pop up
            var first = okBtn, last = noBtn;
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
})();
</script>