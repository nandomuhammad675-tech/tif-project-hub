<?php
/**
 * Notifikasi (toast) untuk pesan flash: welcome, success, error, info.
 * Dipakai di layout mahasiswa & admin, menggantikan kotak alert biasa.
 */
use App\Core\Session;

$ftUser = $user ?? [];
$toasts = [];

if ($ftWelcome = Session::flash('welcome')) {
    $toasts[] = ['type' => 'welcome', 'name' => (string) $ftWelcome];
}
if ($ftMsg = Session::flash('success')) { $toasts[] = ['type' => 'success', 'title' => 'Berhasil', 'msg' => (string) $ftMsg]; }
if ($ftMsg = Session::flash('error'))   { $toasts[] = ['type' => 'error',   'title' => 'Ups, ada masalah', 'msg' => (string) $ftMsg]; }
if ($ftMsg = Session::flash('info'))    { $toasts[] = ['type' => 'info',    'title' => 'Info', 'msg' => (string) $ftMsg]; }

$ftIcons = [
    'success' => '<path d="M20 6 9 17l-5-5"/>',
    'error'   => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/>',
    'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.5v.01"/>',
];
$ftClose = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
?>
<?php if ($toasts): ?>
<style>
    .toast-stack { position: fixed; top: 92px; right: 20px; z-index: 1200; display: grid; gap: .75rem; width: min(380px, calc(100vw - 32px)); pointer-events: none; }
    .toast {
        position: relative; overflow: hidden; pointer-events: auto;
        display: flex; align-items: flex-start; gap: .85rem; padding: .95rem 2.6rem .95rem 1rem;
        background: #fff; color: var(--text, #062534); border-radius: 16px;
        border: 1px solid var(--border, #d7e5ea); box-shadow: 0 16px 40px rgba(2,37,51,.2);
        animation: toastIn .45s cubic-bezier(.2,.9,.3,1.2) both;
    }
    .toast.is-leaving { animation: toastOut .3s ease forwards; }
    @keyframes toastIn { from { opacity: 0; transform: translateX(40px) scale(.96); } to { opacity: 1; transform: none; } }
    @keyframes toastOut { to { opacity: 0; transform: translateX(40px) scale(.96); } }

    .toast .t-ico { width: 40px; height: 40px; border-radius: 12px; flex: none; display: grid; place-items: center; color: #fff; }
    .toast .t-ico svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round; }
    .toast .t-body { min-width: 0; padding-top: .05rem; }
    .toast .t-title { font-weight: 800; font-size: .95rem; line-height: 1.25; }
    .toast .t-msg { font-size: .86rem; color: var(--muted, #5b7480); margin-top: .15rem; line-height: 1.45; word-break: break-word; }
    .toast .t-close { position: absolute; top: .55rem; right: .55rem; width: 28px; height: 28px; border-radius: 50%; border: 0; background: transparent; color: var(--muted, #5b7480); cursor: pointer; display: grid; place-items: center; }
    .toast .t-close:hover { background: rgba(2,37,51,.08); color: var(--text, #062534); }
    .toast .t-bar { position: absolute; left: 0; bottom: 0; height: 4px; width: 100%; transform-origin: left; animation: toastBar linear forwards; animation-duration: var(--t, 5s); }
    .toast:hover .t-bar { animation-play-state: paused; }
    @keyframes toastBar { to { transform: scaleX(0); } }

    .toast-success .t-ico, .toast-success .t-bar { background: #10b981; }
    .toast-error   .t-ico, .toast-error   .t-bar { background: #ef4444; }
    .toast-info    .t-ico, .toast-info    .t-bar { background: #0284c7; }

    /* Toast sambutan (setelah login) */
    .toast-welcome { padding: 1.1rem 2.8rem 1.15rem 1.1rem; color: #fff; border: 0; background: linear-gradient(135deg, #03435a 0%, #037992 62%, #0aa5bf 100%); box-shadow: 0 20px 48px rgba(3,121,146,.45); align-items: center; }
    .toast-welcome .t-avatar { width: 52px; height: 52px; border-radius: 50%; flex: none; overflow: hidden; display: grid; place-items: center; font-weight: 800; background: rgba(255,255,255,.2); border: 3px solid rgba(255,255,255,.85); }
    .toast-welcome .t-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .toast-welcome .t-title { font-size: 1.1rem; }
    .toast-welcome .t-msg { color: rgba(255,255,255,.88); }
    .toast-welcome .t-close { color: rgba(255,255,255,.8); }
    .toast-welcome .t-close:hover { background: rgba(255,255,255,.18); color: #fff; }
    .toast-welcome .t-bar { background: rgba(255,255,255,.75); }
    .toast-welcome .wave { display: inline-block; transform-origin: 70% 70%; animation: toastWave 1.8s ease-in-out .5s 2; }
    @keyframes toastWave { 0%,60%,100% { transform: rotate(0); } 10%,30% { transform: rotate(16deg); } 20%,40% { transform: rotate(-10deg); } 50% { transform: rotate(8deg); } }

    @media (max-width: 520px) { .toast-stack { top: 76px; right: 16px; left: 16px; width: auto; } }
    @media (prefers-reduced-motion: reduce) { .toast, .toast .t-bar, .toast-welcome .wave { animation-duration: .01s !important; animation-delay: 0s !important; } }
</style>

<div class="toast-stack" id="toast-stack" aria-live="polite" aria-atomic="false">
    <?php foreach ($toasts as $t): ?>
        <?php if ($t['type'] === 'welcome'):
            $first   = trim(explode(' ', trim($t['name']))[0] ?? '') ?: 'kamu';
            $isAdmin = (($ftUser['role'] ?? '') === 'admin');
            $avatar  = !empty($ftUser['avatar']) ? \App\Helpers\ImageUploader::avatarUrl($ftUser['avatar']) : null;
        ?>
        <div class="toast toast-welcome" role="status" data-timeout="6500" style="--t:6.5s;">
            <span class="t-avatar">
                <?php if ($avatar): ?><img src="<?= e($avatar) ?>" alt=""><?php else: ?><?= e(initials($t['name'])) ?><?php endif; ?>
            </span>
            <div class="t-body">
                <div class="t-title">Halo, <?= e($first) ?>! <span class="wave">👋</span></div>
                <div class="t-msg"><?= $isAdmin ? 'Selamat datang kembali di panel admin TIF Hub.' : 'Selamat datang kembali di TIF Hub. Yuk, jelajahi karya terbaru teman-temanmu!' ?></div>
            </div>
            <button type="button" class="t-close" aria-label="Tutup notifikasi"><?= $ftClose ?></button>
            <span class="t-bar"></span>
        </div>
        <?php else: ?>
        <div class="toast toast-<?= e($t['type']) ?>" role="<?= $t['type'] === 'error' ? 'alert' : 'status' ?>" data-timeout="<?= $t['type'] === 'error' ? 8000 : 5000 ?>" style="--t:<?= $t['type'] === 'error' ? 8 : 5 ?>s;">
            <span class="t-ico"><svg viewBox="0 0 24 24" aria-hidden="true"><?= $ftIcons[$t['type']] ?></svg></span>
            <div class="t-body">
                <div class="t-title"><?= e($t['title']) ?></div>
                <div class="t-msg"><?= e($t['msg']) ?></div>
            </div>
            <button type="button" class="t-close" aria-label="Tutup notifikasi"><?= $ftClose ?></button>
            <span class="t-bar"></span>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<script>
(function () {
    var stack = document.getElementById('toast-stack');
    if (!stack) return;
    function dismiss(t) {
        if (!t || t.classList.contains('is-leaving')) return;
        t.classList.add('is-leaving');
        setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 320);
    }
    Array.prototype.forEach.call(stack.querySelectorAll('.toast'), function (t) {
        var ms = parseInt(t.getAttribute('data-timeout'), 10) || 5000;
        var timer, left = ms, started;
        function start() { started = Date.now(); timer = setTimeout(function () { dismiss(t); }, left); }
        function pause() { clearTimeout(timer); left -= Date.now() - started; }
        t.addEventListener('mouseenter', pause);
        t.addEventListener('mouseleave', start);
        t.querySelector('.t-close').addEventListener('click', function () { clearTimeout(timer); dismiss(t); });
        start();
    });
})();
</script>
<?php endif; ?>