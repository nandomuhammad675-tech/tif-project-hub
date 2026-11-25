<?php
/**
 * @var array  $war
 * @var array  $projects
 * @var string $csrf
 */
?>
<style>
    .cl-head { margin: 1rem 0 1.4rem; }
    .cl-head h1 { font-size: 1.6rem; font-weight: 800; letter-spacing: -.5px; }
    .cl-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; margin-top: .5rem; }
    .cl-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .85rem; border-radius: 999px; font-size: .82rem; font-weight: 700; background: var(--primary-light); color: var(--primary-dark); }
    .cl-tip { display: flex; gap: .75rem; align-items: flex-start; margin-top: 1rem; padding: .9rem 1.1rem; border-radius: 14px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: .88rem; line-height: 1.5; }
    .cl-tip svg { width: 20px; height: 20px; flex: none; margin-top: .1rem; }

    /* Kartu proyek yang bisa dipilih */
    .claim-card { position: relative; cursor: pointer; display: block; transition: transform .18s ease, box-shadow .18s ease, outline-color .18s ease; outline: 3px solid transparent; outline-offset: -3px; }
    .claim-card:hover { transform: translateY(-3px); }
    .claim-card input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
    .claim-card .cc-pick { display: inline-flex; align-items: center; gap: .55rem; font-size: .88rem; font-weight: 700; color: var(--muted); }
    .claim-card .cc-dot { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #b6cbd3; display: grid; place-items: center; transition: all .18s ease; background: #fff; }
    .claim-card .cc-dot svg { width: 13px; height: 13px; stroke: #fff; stroke-width: 3.2; fill: none; opacity: 0; transform: scale(.4); transition: all .18s ease; }
    .claim-card:has(input:focus-visible) { outline-color: rgba(3,121,146,.5); }
    .claim-card.is-selected { outline-color: var(--primary); box-shadow: 0 0 0 4px rgba(3,121,146,.14), 0 20px 44px rgba(3,121,146,.22); transform: translateY(-3px); }
    .claim-card.is-selected .cc-dot { background: var(--primary); border-color: var(--primary); }
    .claim-card.is-selected .cc-dot svg { opacity: 1; transform: none; }
    .claim-card.is-selected .cc-pick { color: var(--primary-dark); }
    .claim-card .cc-ribbon { position: absolute; left: 14px; top: 14px; z-index: 3; padding: .3rem .75rem; border-radius: 999px; font-size: .75rem; font-weight: 800; color: #fff; background: var(--primary); box-shadow: 0 6px 16px rgba(3,121,146,.4); opacity: 0; transform: translateY(-6px); transition: all .18s ease; }
    .claim-card.is-selected .cc-ribbon { opacity: 1; transform: none; }

    /* Bar konfirmasi di bawah */
    .claim-bar { position: sticky; bottom: 1rem; z-index: 5; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; padding: .9rem 1.1rem; background: rgba(255,255,255,.96); backdrop-filter: blur(8px); border: 1px solid var(--border); border-radius: 18px; box-shadow: 0 18px 44px rgba(2,37,51,.22); }
    .claim-bar .cb-info { flex: 1; min-width: 200px; }
    .claim-bar .cb-label { display: block; font-size: .72rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--muted); }
    .claim-bar .cb-title { display: block; font-size: 1rem; font-weight: 800; margin-top: .1rem; color: var(--muted); }
    .claim-bar.has-pick .cb-title { color: var(--text); }
    .claim-bar .btn { min-width: 220px; padding: .85rem 1.4rem; font-size: .98rem; }
    .claim-bar .btn[disabled] { opacity: .5; cursor: not-allowed; box-shadow: none; }
    .claim-bar.shake { animation: clShake .4s ease; }
    @keyframes clShake { 20%, 60% { transform: translateX(-7px); } 40%, 80% { transform: translateX(7px); } }
    .claim-note { text-align: center; font-size: .8rem; color: var(--muted); margin-top: .75rem; }

    /* Pop up konfirmasi */
    .cl-overlay { position: fixed; inset: 0; z-index: 1100; display: grid; place-items: center; padding: 1rem; background: rgba(2,37,51,.62); backdrop-filter: blur(3px); }
    .cl-overlay[hidden] { display: none; }
    .cl-dialog { width: 100%; max-width: 420px; background: #fff; border-radius: 24px; padding: 1.8rem 1.5rem 1.4rem; text-align: center; box-shadow: 0 30px 80px rgba(2,37,51,.4); animation: clPop .24s ease; }
    @keyframes clPop { from { opacity: 0; transform: translateY(14px) scale(.96); } to { opacity: 1; transform: none; } }
    .cl-icon { width: 74px; height: 74px; margin: 0 auto 1rem; border-radius: 50%; display: grid; place-items: center; font-size: 2rem; background: linear-gradient(135deg, #e3f3f7, #cfeaf1); box-shadow: 0 0 0 8px #f1f9fb; }
    .cl-dialog h3 { font-size: 1.3rem; font-weight: 800; letter-spacing: -.3px; }
    .cl-sub { font-size: .9rem; color: var(--muted); margin-top: .35rem; }
    .cl-pick { margin: 1.1rem 0 0; padding: .9rem 1rem; text-align: left; border-radius: 16px; background: #f1f7f9; border: 1.5px solid var(--primary); }
    .cl-pick small { display: block; font-size: .7rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--muted); }
    .cl-pick strong { display: block; font-size: 1.05rem; margin: .15rem 0 .45rem; }
    .cl-pick .b { display: inline-block; padding: .15rem .65rem; border-radius: 999px; font-size: .74rem; font-weight: 700; background: #fff; color: var(--primary-dark); margin-right: .3rem; }
    .cl-rules { list-style: none; margin: 1rem 0 0; padding: 0; display: grid; gap: .5rem; text-align: left; }
    .cl-rules li { display: flex; gap: .6rem; font-size: .84rem; color: var(--muted); line-height: 1.45; }
    .cl-rules li::before { content: "!"; flex: none; width: 20px; height: 20px; border-radius: 50%; display: grid; place-items: center; font-size: .72rem; font-weight: 800; background: #fef3c7; color: #b45309; margin-top: .05rem; }
    .cl-btns { display: grid; gap: .55rem; margin-top: 1.4rem; }
    .cl-btn { font: inherit; font-weight: 800; font-size: .95rem; border-radius: 13px; padding: .8rem 1rem; cursor: pointer; border: 1.5px solid transparent; transition: transform .15s ease, background .15s ease; }
    .cl-btn:hover { transform: translateY(-1px); }
    .cl-btn.go { background: var(--primary); color: #fff; box-shadow: 0 10px 24px rgba(3,121,146,.35); }
    .cl-btn.go:hover { background: var(--primary-dark); }
    .cl-btn.go[disabled] { opacity: .75; cursor: wait; transform: none; }
    .cl-btn.back { background: #fff; color: var(--text); border-color: var(--border); }
    .cl-btn.back:hover { border-color: var(--primary); color: var(--primary); }
    .cl-btn:focus-visible { outline: 3px solid rgba(3,121,146,.35); outline-offset: 2px; }
    @media (max-width: 560px) { .claim-bar .btn { width: 100%; } }
    @media (prefers-reduced-motion: reduce) { .cl-dialog, .claim-bar.shake { animation: none; } .claim-card, .claim-card:hover { transition: none; transform: none; } }
</style>

<a href="/" class="back-link">← Kembali ke Beranda</a>

<div class="cl-head">
    <h1>Pilih Proyek untuk Diklaim</h1>
    <div class="cl-meta">
        <span class="cl-chip">🎓 <?= e($war['semester_name'] ?? '-') ?><?php if (!empty($war['academic_year'])): ?> · <?= e($war['academic_year']) ?><?php endif; ?></span>
        <span class="cl-chip">📦 <?= count($projects) ?> proyek tersedia</span>
    </div>
    <div class="cl-tip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
        <div>Pilih <strong>satu</strong> proyek yang masih tersedia. Kamu hanya bisa mengklaim satu proyek per semester, dan tiket baru terbit setelah server berhasil menyimpan klaimmu.</div>
    </div>
</div>

<?php if (empty($projects)): ?>
<div class="empty-state">
    <div class="icon"><?= icon('folder') ?></div>
    <p><strong>Belum ada proyek yang bisa diklaim.</strong></p>
    <p class="text-sm">Semua proyek di semester ini sudah diambil, atau belum ada yang tersedia.</p>
    <a href="/" class="btn btn-primary btn-sm" style="margin-top:1rem;">Kembali ke Beranda</a>
</div>
<?php else: ?>
<form method="POST" action="/claims" id="form-claim-select">
    <input type="hidden" name="_token" value="<?= e($csrf) ?>">
    <div class="project-grid" style="margin-bottom:1.5rem;">
        <?php foreach ($projects as $p): ?>
        <label class="project-card claim-card"
               data-title="<?= e($p['title']) ?>"
               data-cat="<?= e($p['category_name'] ?? 'Lainnya') ?>"
               data-sem="<?= e($p['semester_name'] ?? '-') ?>">
            <span class="cc-ribbon">✓ Pilihanmu</span>
            <div class="thumb">
                <?= project_thumb($p) ?>
                <span class="thumb-status"><span class="badge badge-glass" style="color:#065f46">● Tersedia</span></span>
            </div>
            <div class="body">
                <div class="badges">
                    <span class="badge badge-primary"><?= e($p['category_name'] ?? 'Lainnya') ?></span>
                    <span class="badge badge-secondary"><?= e($p['semester_name'] ?? '-') ?></span>
                </div>
                <h3 style="margin:.4rem 0;"><?= e($p['title']) ?></h3>
                <p class="desc"><?= e($p['short_desc'] ?: truncate($p['description'] ?? '', 90)) ?></p>
                <?php if (!empty($p['course_name'])): ?>
                <p class="text-sm text-muted">MK: <?= e($p['course_name']) ?></p>
                <?php endif; ?>
                <div class="tech"><?= tech_tags($p['technologies'] ?? '') ?></div>
                <div class="actions" style="margin-top:.75rem;">
                    <span class="cc-pick">
                        <input type="radio" name="project_id" value="<?= (int) $p['id'] ?>" required>
                        <span class="cc-dot"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                        <span class="cc-text">Pilih proyek ini</span>
                    </span>
                </div>
            </div>
        </label>
        <?php endforeach; ?>
    </div>

    <div class="claim-bar" id="claim-bar">
        <div class="cb-info">
            <span class="cb-label">Proyek pilihanmu</span>
            <span class="cb-title" id="cb-title">Belum ada yang dipilih</span>
        </div>
        <button type="submit" class="btn btn-primary" id="btn-confirm-claim" disabled>Pilih satu proyek dulu</button>
    </div>
    <p class="claim-note">Setelah dikonfirmasi, pilihan tidak dapat diganti sendiri.</p>
</form>

<!-- Pop up konfirmasi klaim -->
<div class="cl-overlay" id="claim-modal" hidden role="alertdialog" aria-modal="true" aria-labelledby="cl-title">
    <div class="cl-dialog">
        <div class="cl-icon">🎯</div>
        <h3 id="cl-title">Klaim proyek ini?</h3>
        <p class="cl-sub">Pastikan pilihanmu sudah tepat sebelum melanjutkan.</p>

        <div class="cl-pick">
            <small>Proyek yang kamu pilih</small>
            <strong id="cl-name">-</strong>
            <span class="b" id="cl-cat">-</span><span class="b" id="cl-sem">-</span>
        </div>

        <ul class="cl-rules">
            <li>Kamu hanya boleh mengklaim <strong>satu proyek per semester</strong>.</li>
            <li>Setelah dikonfirmasi, pilihan <strong>tidak dapat diganti sendiri</strong>.</li>
        </ul>

        <div class="cl-btns">
            <button type="button" class="cl-btn go" id="cl-go">Ya, klaim sekarang!</button>
            <button type="button" class="cl-btn back" id="cl-back">Ganti pilihan</button>
        </div>
    </div>
</div>

<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var form = $('form-claim-select'), btn = $('btn-confirm-claim'), bar = $('claim-bar'), barTitle = $('cb-title');
    var modal = $('claim-modal'), go = $('cl-go'), back = $('cl-back');
    var cards = form.querySelectorAll('.claim-card');
    var lastFocus = null;

    function sync() {
        var picked = form.querySelector('input[name=project_id]:checked');
        Array.prototype.forEach.call(cards, function (c) {
            var on = c.contains(picked) && !!picked;
            c.classList.toggle('is-selected', on);
            c.querySelector('.cc-text').textContent = on ? 'Proyek ini kamu pilih' : 'Pilih proyek ini';
        });
        if (picked) {
            var card = picked.closest('.claim-card');
            barTitle.textContent = card.getAttribute('data-title');
            bar.classList.add('has-pick');
            btn.disabled = false;
            btn.textContent = 'Klaim Proyek Ini';
        } else {
            barTitle.textContent = 'Belum ada yang dipilih';
            bar.classList.remove('has-pick');
            btn.disabled = true;
            btn.textContent = 'Pilih satu proyek dulu';
        }
    }
    form.addEventListener('change', function (e) { if (e.target.name === 'project_id') sync(); });

    function openModal(card) {
        lastFocus = document.activeElement;
        $('cl-name').textContent = card.getAttribute('data-title');
        $('cl-cat').textContent  = card.getAttribute('data-cat');
        $('cl-sem').textContent  = card.getAttribute('data-sem');
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        back.focus();                      // fokus aman: "Ganti pilihan"
    }
    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (btn.dataset.loading === '1') return;
        var picked = form.querySelector('input[name=project_id]:checked');
        if (!picked) {
            bar.classList.remove('shake'); void bar.offsetWidth; bar.classList.add('shake');
            return;
        }
        openModal(picked.closest('.claim-card'));
    });

    go.addEventListener('click', function () {
        go.disabled = true; back.disabled = true;
        go.textContent = 'Memproses klaim…';
        btn.dataset.loading = '1'; btn.disabled = true; btn.textContent = 'Memproses klaim…';
        form.submit();
    });
    back.addEventListener('click', closeModal);
    modal.addEventListener('mousedown', function (e) { if (e.target === modal && !go.disabled) closeModal(); });
    document.addEventListener('keydown', function (e) {
        if (modal.hidden) return;
        if (e.key === 'Escape' && !go.disabled) { closeModal(); return; }
        if (e.key === 'Tab') {
            if (e.shiftKey && document.activeElement === go) { e.preventDefault(); back.focus(); }
            else if (!e.shiftKey && document.activeElement === back) { e.preventDefault(); go.focus(); }
        }
    });

    // Kembali dari halaman lain (cache browser): reset keadaan tombol
    window.addEventListener('pageshow', function () {
        go.disabled = false; back.disabled = false; go.textContent = 'Ya, klaim sekarang!';
        btn.dataset.loading = '0'; modal.hidden = true; document.body.style.overflow = '';
        sync();
    });
    sync();
})();
</script>
<?php endif; ?>