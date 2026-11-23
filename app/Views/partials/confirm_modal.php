<?php
/**
 * Pop up konfirmasi / peringatan global (pengganti confirm() & alert() bawaan browser).
 * Dipakai di layout mahasiswa & admin.
 *
 * 1) Deklaratif, cukup tambah atribut di <form>:
 *      <form data-confirm="Hapus proyek ini?"
 *            data-confirm-title="Hapus Proyek"      (opsional)
 *            data-confirm-ok="Ya, hapus"            (opsional)
 *            data-confirm-variant="danger|primary|warning"  (opsional, default primary)
 *            data-confirm-detail="Nama proyek">     (opsional, kotak info kecil)
 *
 * 2) Lewat JavaScript:
 *      tifConfirm({ title, text, detail, okText, cancelText, variant }).then(function (ok) { ... });
 *      tifAlert({ title, text, variant }).then(function () { ... });
 */
?>
<style>
    .cf-overlay { position: fixed; inset: 0; z-index: 1200; display: grid; place-items: center; padding: 1rem; background: rgba(2,37,51,.62); backdrop-filter: blur(3px); opacity: 0; transition: opacity .18s ease; }
    .cf-overlay[hidden] { display: none; }
    .cf-overlay.show { opacity: 1; }
    .cf-dialog { width: 100%; max-width: 400px; background: #fff; color: var(--text, #062534); border-radius: 22px; padding: 1.8rem 1.5rem 1.4rem; text-align: center; box-shadow: 0 30px 80px rgba(2,37,51,.4); transform: translateY(14px) scale(.96); transition: transform .22s cubic-bezier(.2,.9,.3,1.2); }
    .cf-overlay.show .cf-dialog { transform: none; }
    @media (prefers-reduced-motion: reduce) { .cf-overlay, .cf-dialog { transition: none; } }

    .cf-icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; display: grid; place-items: center; }
    .cf-icon svg { width: 32px; height: 32px; }
    .cf-primary .cf-icon { background: #e3f3f7; color: #037992; box-shadow: 0 0 0 8px #f1f9fb; }
    .cf-danger  .cf-icon { background: #fef2f2; color: #dc2626; box-shadow: 0 0 0 8px #fff5f5; }
    .cf-warning .cf-icon { background: #fffbeb; color: #d97706; box-shadow: 0 0 0 8px #fffdf5; }

    .cf-dialog h3 { font-size: 1.25rem; font-weight: 800; letter-spacing: -.3px; margin: 0; }
    .cf-text { font-size: .92rem; color: var(--muted, #587486); margin: .45rem 0 0; line-height: 1.55; }
    .cf-detail { margin: 1.1rem 0 0; padding: .75rem .95rem; background: #f1f7f9; border-radius: 14px; text-align: left; border-left: 4px solid var(--primary, #037992); }
    .cf-detail small { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--muted, #587486); }
    .cf-detail strong { display: block; font-size: .98rem; line-height: 1.3; margin-top: .15rem; word-break: break-word; }
    .cf-danger .cf-detail { border-left-color: #dc2626; }
    .cf-warning .cf-detail { border-left-color: #d97706; }

    .cf-btns { display: grid; gap: .55rem; margin-top: 1.3rem; }
    .cf-btn { font: inherit; font-weight: 700; font-size: .95rem; border-radius: 12px; padding: .75rem 1rem; cursor: pointer; border: 1.5px solid transparent; transition: background .15s ease, transform .15s ease, box-shadow .15s ease; }
    .cf-btn:hover { transform: translateY(-1px); }
    .cf-btn:focus-visible { outline: 3px solid rgba(3,121,146,.35); outline-offset: 2px; }
    .cf-primary .cf-ok { background: linear-gradient(135deg, #024c5d, #037992 60%, #1fa0bd); color: #fff; box-shadow: 0 8px 20px rgba(3,121,146,.3); }
    .cf-danger  .cf-ok { background: #dc2626; color: #fff; border-color: #dc2626; }
    .cf-danger  .cf-ok:hover { background: #b91c1c; border-color: #b91c1c; }
    .cf-warning .cf-ok { background: #d97706; color: #fff; border-color: #d97706; }
    .cf-warning .cf-ok:hover { background: #b45309; border-color: #b45309; }
    .cf-cancel { background: #fff; color: var(--text, #062534); border-color: var(--border, #dbe8ee); }
    .cf-cancel:hover { border-color: var(--primary, #037992); color: var(--primary, #037992); }
</style>

<div class="cf-overlay cf-primary" id="cf-modal" hidden role="alertdialog" aria-modal="true" aria-labelledby="cf-title" aria-describedby="cf-text">
    <div class="cf-dialog">
        <div class="cf-icon" id="cf-icon"></div>
        <h3 id="cf-title"></h3>
        <p class="cf-text" id="cf-text"></p>
        <div class="cf-detail" id="cf-detail" hidden><small>Pilihan kamu</small><strong id="cf-detail-val"></strong></div>
        <div class="cf-btns">
            <button type="button" class="cf-btn cf-ok" id="cf-ok">OK</button>
            <button type="button" class="cf-btn cf-cancel" id="cf-cancel">Batal</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('cf-modal');
    if (!modal) return;
    var $ = function (id) { return document.getElementById(id); };
    var elIcon = $('cf-icon'), elTitle = $('cf-title'), elText = $('cf-text'),
        elDetail = $('cf-detail'), elDetailVal = $('cf-detail-val'),
        okBtn = $('cf-ok'), noBtn = $('cf-cancel');

    var ICONS = {
        primary: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
        danger:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>'
    };

    var resolver = null, lastFocus = null, hideTimer = null;

    function finish(result) {
        if (!resolver) return;
        var r = resolver; resolver = null;
        modal.classList.remove('show');
        document.body.style.overflow = '';
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () { modal.hidden = true; }, 180);
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
        r(result);
    }

    function show(o, alertOnly) {
        if (resolver) finish(false);
        return new Promise(function (resolve) {
            resolver = resolve;
            lastFocus = document.activeElement;
            var v = ICONS[o.variant] ? o.variant : 'primary';
            modal.className = 'cf-overlay cf-' + v;
            elIcon.innerHTML = ICONS[v];
            elTitle.textContent = o.title || (alertOnly ? 'Perhatian' : 'Konfirmasi');
            elText.textContent = o.text || '';
            elText.hidden = !o.text;
            elDetail.hidden = !o.detail;
            elDetailVal.textContent = o.detail || '';
            okBtn.textContent = o.okText || (alertOnly ? 'Mengerti' : 'Ya, lanjutkan');
            noBtn.textContent = o.cancelText || 'Batal';
            noBtn.hidden = !!alertOnly;
            clearTimeout(hideTimer);
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            void modal.offsetWidth;
            modal.classList.add('show');
            // Aksi berbahaya: fokus ke "Batal" supaya tidak kepencet Enter. Selain itu fokus ke tombol utama.
            (v === 'danger' && !alertOnly ? noBtn : okBtn).focus();
        });
    }

    window.tifConfirm = function (o) { return show(o || {}, false); };
    window.tifAlert   = function (o) { return show(o || {}, true); };

    okBtn.addEventListener('click', function () { finish(true); });
    noBtn.addEventListener('click', function () { finish(false); });
    modal.addEventListener('mousedown', function (e) { if (e.target === modal) finish(false); });
    document.addEventListener('keydown', function (e) {
        if (modal.hidden || !resolver) return;
        if (e.key === 'Escape') { e.preventDefault(); finish(false); return; }
        if (e.key === 'Tab') {
            var items = noBtn.hidden ? [okBtn] : [okBtn, noBtn];
            var i = items.indexOf(document.activeElement);
            e.preventDefault();
            i = e.shiftKey ? (i <= 0 ? items.length - 1 : i - 1) : (i + 1) % items.length;
            items[i].focus();
        }
    });

    // Deklaratif: <form data-confirm="..."> otomatis memakai pop up ini
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.getAttribute || !form.hasAttribute('data-confirm')) return;
        if (form.dataset.cfOk === '1') { form.dataset.cfOk = ''; return; }
        e.preventDefault();
        tifConfirm({
            title: form.dataset.confirmTitle,
            text: form.getAttribute('data-confirm'),
            detail: form.dataset.confirmDetail,
            okText: form.dataset.confirmOk,
            cancelText: form.dataset.confirmCancel,
            variant: form.dataset.confirmVariant
        }).then(function (ok) {
            if (!ok) return;
            form.dataset.cfOk = '1';
            if (form.requestSubmit) form.requestSubmit(); else form.submit();
        });
    }, true);
})();
</script>
