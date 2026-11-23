<?php
/**
 * Halaman pilih proyek untuk klaim tiket.
 * @var array $war
 * @var array $projects
 * @var string $csrf
 */
$total = count($projects);

// Kumpulkan kategori unik untuk filter
$cats = [];
foreach ($projects as $p) {
    $c = $p['category_name'] ?? 'Lainnya';
    $cats[$c] = ($cats[$c] ?? 0) + 1;
}
ksort($cats);

$endsAt = !empty($war['ends_at']) ? strtotime($war['ends_at']) : null;
$check  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
?>
<style>
    /* ===== Halaman Klaim Proyek (scoped: cs-*) ===== */
    .cs-hero { position: relative; overflow: hidden; border-radius: 26px; padding: 2rem 2rem 1.6rem; color: #fff; background: var(--grad-deep); box-shadow: 0 18px 44px rgba(2,37,51,.25); margin-top: 1rem; }
    .cs-hero::before, .cs-hero::after { content: ''; position: absolute; border-radius: 50%; pointer-events: none; }
    .cs-hero::before { width: 320px; height: 320px; right: -90px; top: -140px; background: radial-gradient(circle, rgba(94,200,220,.45), transparent 65%); }
    .cs-hero::after  { width: 240px; height: 240px; left: 30%; bottom: -150px; background: radial-gradient(circle, rgba(41,156,184,.35), transparent 65%); }
    .cs-hero > * { position: relative; z-index: 1; }
    .cs-eyebrow { display: inline-flex; align-items: center; gap: .4rem; font-size: .74rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; padding: .3rem .75rem; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); }
    .cs-eyebrow i { width: 7px; height: 7px; border-radius: 50%; background: #34d399; box-shadow: 0 0 0 0 rgba(52,211,153,.7); animation: csPulse 1.8s infinite; }
    @keyframes csPulse { 70% { box-shadow: 0 0 0 8px rgba(52,211,153,0); } 100% { box-shadow: 0 0 0 0 rgba(52,211,153,0); } }
    .cs-hero h1 { font-size: clamp(1.5rem, 3.2vw, 2.1rem); font-weight: 800; letter-spacing: -.6px; margin: .8rem 0 .35rem; line-height: 1.15; }
    .cs-hero p.lead { color: rgba(255,255,255,.82); font-size: .95rem; max-width: 640px; line-height: 1.6; margin: 0; }
    .cs-meta { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1.1rem; }
    .cs-chip { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; padding: .38rem .8rem; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2); }
    .cs-chip svg { width: 15px; height: 15px; }

    .cs-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin-top: 1.5rem; }
    .cs-step { display: flex; align-items: center; gap: .65rem; padding: .7rem .85rem; border-radius: 16px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); opacity: .7; transition: all .3s ease; }
    .cs-step b { flex: none; width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; font-size: .85rem; font-weight: 800; background: rgba(255,255,255,.18); transition: all .3s ease; }
    .cs-step b svg { width: 15px; height: 15px; }
    .cs-step span { font-size: .82rem; font-weight: 700; line-height: 1.25; }
    .cs-step small { display: block; font-size: .72rem; font-weight: 500; opacity: .8; }
    .cs-step.active { opacity: 1; background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.45); }
    .cs-step.active b { background: #fff; color: var(--primary-dark, #024c5d); }
    .cs-step.done { opacity: 1; }
    .cs-step.done b { background: #34d399; color: #064e3b; }

    /* Toolbar: cari + filter */
    .cs-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem 1rem; margin: 1.6rem 0 1.1rem; }
    .cs-search { position: relative; flex: 1 1 260px; max-width: 380px; }
    .cs-search svg { position: absolute; left: .9rem; top: 50%; translate: 0 -50%; width: 18px; height: 18px; color: var(--muted, #587486); pointer-events: none; }
    .cs-search input { width: 100%; font: inherit; font-size: .9rem; padding: .7rem 1rem .7rem 2.6rem; border-radius: 14px; border: 1.5px solid var(--border, #dbe8ee); background: #fff; color: var(--text, #062534); transition: border-color .2s, box-shadow .2s; }
    .cs-search input:focus { outline: none; border-color: var(--primary, #037992); box-shadow: 0 0 0 4px rgba(3,121,146,.14); }
    .cs-filters { display: flex; flex-wrap: wrap; gap: .45rem; }
    .cs-filter { font: inherit; font-size: .8rem; font-weight: 700; padding: .5rem .95rem; border-radius: 999px; cursor: pointer; border: 1.5px solid var(--border, #dbe8ee); background: #fff; color: var(--muted, #587486); transition: all .2s ease; }
    .cs-filter:hover { border-color: var(--primary, #037992); color: var(--primary, #037992); }
    .cs-filter.on { background: var(--primary, #037992); border-color: var(--primary, #037992); color: #fff; box-shadow: 0 6px 16px rgba(3,121,146,.3); }
    .cs-filter em { font-style: normal; opacity: .7; margin-left: .25rem; font-weight: 600; }
    .cs-count { margin-left: auto; font-size: .82rem; color: var(--muted, #587486); font-weight: 600; }

    /* Kartu proyek yang bisa dipilih */
    .cs-grid { margin-bottom: 1.5rem !important; }
    .cs-card { position: relative; display: flex; cursor: pointer; border: 2px solid var(--border, #dbe8ee); transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    .cs-card[hidden] { display: none; }
    .cs-card input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
    .cs-card .cs-check { position: absolute; top: .8rem; left: .8rem; z-index: 4; width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: rgba(255,255,255,.92); color: transparent; border: 2px solid rgba(3,121,146,.25); box-shadow: 0 4px 12px rgba(2,37,51,.12); transition: all .25s cubic-bezier(.2,.9,.3,1.4); }
    .cs-card .cs-check svg { width: 18px; height: 18px; scale: .4; transition: scale .25s cubic-bezier(.2,.9,.3,1.6); }
    .cs-card:hover .cs-check { border-color: var(--primary, #037992); }
    .cs-card.is-selected { border-color: var(--primary, #037992); box-shadow: 0 0 0 4px rgba(3,121,146,.18), 0 20px 44px rgba(3,121,146,.28); }
    .cs-card.is-selected .cs-check { background: var(--primary, #037992); border-color: var(--primary, #037992); color: #fff; }
    .cs-card.is-selected .cs-check svg { scale: 1; }
    .cs-card:focus-within { outline: 3px solid rgba(3,121,146,.35); outline-offset: 3px; }
    .cs-card .body { width: 100%; }
    .cs-meta-row { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: .4rem; }
    .cs-course { font-size: .8rem; color: var(--muted, #587486); margin: 0 0 .5rem; }
    .cs-course b { color: var(--text, #062534); font-weight: 700; }
    .cs-pick { margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding-top: .9rem; border-top: 1px dashed var(--border, #dbe8ee); }
    .cs-pick .cs-pill { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 700; color: var(--primary, #037992); padding: .45rem .9rem; border-radius: 999px; background: var(--primary-light, #e3f3f7); transition: all .25s ease; }
    .cs-pick .cs-pill svg { width: 15px; height: 15px; display: none; }
    .cs-card.is-selected .cs-pill { background: var(--primary, #037992); color: #fff; }
    .cs-card.is-selected .cs-pill svg { display: block; }
    .cs-detail { font-size: .78rem; font-weight: 700; color: var(--muted, #587486); text-decoration: none; }
    .cs-detail:hover { color: var(--primary, #037992); text-decoration: underline; }

    .cs-empty { display: none; text-align: center; padding: 2.5rem 1rem; color: var(--muted, #587486); background: #fff; border: 1.5px dashed var(--border, #dbe8ee); border-radius: 20px; margin-bottom: 1.5rem; }
    .cs-empty.show { display: block; }
    .cs-empty strong { display: block; color: var(--text, #062534); font-size: 1.05rem; margin-bottom: .25rem; }

    /* Bar aksi melayang */
    .cs-bar { position: sticky; bottom: 1rem; z-index: 20; display: flex; align-items: center; gap: 1rem; padding: .8rem .85rem .8rem 1.1rem; border-radius: 22px; background: rgba(255,255,255,.9); backdrop-filter: blur(14px); border: 1px solid rgba(3,121,146,.18); box-shadow: 0 18px 50px rgba(2,37,51,.22); }
    .cs-sel { display: flex; align-items: center; gap: .8rem; min-width: 0; flex: 1; }
    .cs-sel-ico { flex: none; width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; background: #f1f7f9; color: var(--muted, #587486); transition: all .3s ease; }
    .cs-sel-ico svg { width: 22px; height: 22px; }
    .cs-bar.has .cs-sel-ico { background: var(--grad); color: #fff; box-shadow: 0 8px 18px rgba(3,121,146,.35); }
    .cs-sel small { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--muted, #587486); }
    .cs-sel strong { display: block; font-size: .98rem; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cs-bar:not(.has) .cs-sel strong { color: var(--muted, #587486); font-weight: 600; }
    .cs-bar .btn { flex: none; padding: .85rem 1.5rem; font-size: .95rem; border-radius: 14px; }
    .cs-bar .btn[disabled] { opacity: .45; cursor: not-allowed; box-shadow: none; transform: none; filter: grayscale(.4); }
    .cs-bar .btn svg { width: 18px; height: 18px; }
    .cs-note { text-align: center; font-size: .8rem; color: var(--muted, #587486); margin: .8rem 0 0; }

    @media (max-width: 720px) {
        .cs-hero { padding: 1.5rem 1.15rem 1.2rem; border-radius: 22px; }
        .cs-steps { grid-template-columns: 1fr; gap: .5rem; }
        .cs-step small { display: none; }
        .cs-count { margin-left: 0; width: 100%; }
        .cs-bar { flex-direction: column; align-items: stretch; gap: .7rem; padding: .85rem; }
        .cs-bar .btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .cs-eyebrow i { animation: none; } .cs-card, .cs-check, .cs-check svg { transition: none; } }
</style>

<a href="/" class="back-link">← Kembali ke Beranda</a>

<section class="cs-hero">
    <span class="cs-eyebrow"><i></i> War Proyek Dibuka</span>
    <h1>Pilih Proyek untuk Diklaim</h1>
    <p class="lead">Pilih <strong>satu</strong> proyek yang masih tersedia. Tiket baru diterbitkan setelah server berhasil menyimpan klaim kamu.</p>

    <div class="cs-meta">
        <span class="cs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
            <?= e($war['semester_name'] ?? '-') ?><?php if (!empty($war['academic_year'])): ?> · <?= e($war['academic_year']) ?><?php endif; ?>
        </span>
        <span class="cs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            <?= (int) $total ?> proyek tersedia
        </span>
        <?php if ($endsAt): ?>
        <span class="cs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Berakhir <?= e(date('d M Y, H:i', $endsAt)) ?>
        </span>
        <?php endif; ?>
    </div>

    <div class="cs-steps" id="cs-steps">
        <div class="cs-step active" data-step="1"><b>1</b><span>Pilih proyek<small>Cari yang paling cocok</small></span></div>
        <div class="cs-step" data-step="2"><b>2</b><span>Konfirmasi klaim<small>Cek lagi pilihanmu</small></span></div>
        <div class="cs-step" data-step="3"><b>3</b><span>Tiket terbit<small>Simpan sebagai bukti</small></span></div>
    </div>
</section>

<?php if (empty($projects)): ?>
<div class="empty-state" style="margin-top:1.5rem;">
    <div class="icon">📭</div>
    <p>Tidak ada proyek tersedia untuk diklaim saat ini.</p>
</div>
<?php else: ?>

<div class="cs-toolbar">
    <label class="cs-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" id="cs-q" placeholder="Cari judul, teknologi, atau mata kuliah…" autocomplete="off" aria-label="Cari proyek">
    </label>
    <?php if (count($cats) > 1): ?>
    <div class="cs-filters" id="cs-filters" role="group" aria-label="Filter kategori">
        <button type="button" class="cs-filter on" data-cat="">Semua<em><?= (int) $total ?></em></button>
        <?php foreach ($cats as $name => $n): ?>
        <button type="button" class="cs-filter" data-cat="<?= e($name) ?>"><?= e($name) ?><em><?= (int) $n ?></em></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <span class="cs-count" id="cs-count" aria-live="polite">Menampilkan <?= (int) $total ?> proyek</span>
</div>

<form method="POST" action="/claims" id="form-claim-select" onsubmit="return onClaimSubmit(this)">
    <input type="hidden" name="_token" value="<?= e($csrf) ?>">

    <div class="project-grid cs-grid" id="cs-grid">
        <?php foreach ($projects as $p):
            $cat   = $p['category_name'] ?? 'Lainnya';
            $techs = (string) ($p['technologies'] ?? '');
            $hay   = mb_strtolower(($p['title'] ?? '') . ' ' . $techs . ' ' . ($p['course_name'] ?? '') . ' ' . ($p['short_desc'] ?? '') . ' ' . $cat);
        ?>
        <label class="project-card cs-card" data-cat="<?= e($cat) ?>" data-hay="<?= e($hay) ?>">
            <input type="radio" name="project_id" value="<?= (int) $p['id'] ?>">
            <span class="cs-check"><?= $check ?></span>
            <div style="display:flex;flex-direction:column;width:100%;">
                <div class="thumb">
                    <?= project_thumb($p) ?>
                    <span class="thumb-status"><span class="badge badge-glass" style="color:#065f46">● Tersedia</span></span>
                </div>
                <div class="body">
                    <div class="badges">
                        <span class="badge badge-primary"><?= e($cat) ?></span>
                        <span class="badge badge-secondary"><?= e($p['semester_name'] ?? '-') ?></span>
                    </div>
                    <h3><?= e($p['title']) ?></h3>
                    <p class="desc"><?= e($p['short_desc'] ?: truncate($p['description'] ?? '', 90)) ?></p>
                    <?php if (!empty($p['course_name'])): ?>
                    <p class="cs-course">MK: <b><?= e($p['course_name']) ?></b></p>
                    <?php endif; ?>
                    <div class="tech"><?= tech_tags($techs) ?></div>
                    <div class="cs-pick">
                        <span class="cs-pill"><?= $check ?><span class="cs-pill-t">Pilih proyek ini</span></span>
                        <?php if (!empty($p['slug'])): ?>
                        <a class="cs-detail" href="/projects/<?= e($p['slug']) ?>" target="_blank" rel="noopener">Lihat detail ↗</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </label>
        <?php endforeach; ?>
    </div>

    <div class="cs-empty" id="cs-empty">
        <strong>Proyek tidak ditemukan</strong>
        Coba kata kunci lain atau ganti filter kategori.
    </div>

    <div class="cs-bar" id="cs-bar">
        <div class="cs-sel">
            <span class="cs-sel-ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>
            </span>
            <div style="min-width:0;">
                <small>Proyek dipilih</small>
                <strong id="cs-sel-title">Belum ada proyek dipilih</strong>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" id="btn-confirm-claim" disabled>
            Konfirmasi Klaim Proyek
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </div>
    <p class="cs-note">🔒 Setelah dikonfirmasi, pilihan tidak dapat diganti sendiri.</p>
</form>

<script>
(function () {
  var cards   = [].slice.call(document.querySelectorAll('.cs-card'));
  var bar     = document.getElementById('cs-bar');
  var selT    = document.getElementById('cs-sel-title');
  var btn     = document.getElementById('btn-confirm-claim');
  var steps   = document.querySelectorAll('.cs-step');
  var q       = document.getElementById('cs-q');
  var filters = document.querySelectorAll('.cs-filter');
  var countEl = document.getElementById('cs-count');
  var emptyEl = document.getElementById('cs-empty');
  var total   = cards.length, activeCat = '';

  function setSteps(picked) {
    steps.forEach(function (s) {
      var n = +s.dataset.step;
      s.classList.toggle('done',   n === 1 && picked);
      s.classList.toggle('active', picked ? n === 2 : n === 1);
    });
  }

  function sync() {
    var picked = null;
    cards.forEach(function (c) {
      var on = c.querySelector('input').checked;
      c.classList.toggle('is-selected', on);
      c.querySelector('.cs-pill-t').textContent = on ? 'Dipilih' : 'Pilih proyek ini';
      if (on) picked = c;
    });
    bar.classList.toggle('has', !!picked);
    btn.disabled = !picked;
    selT.textContent = picked ? picked.querySelector('h3').textContent.trim() : 'Belum ada proyek dipilih';
    setSteps(!!picked);
  }

  function applyFilter() {
    var term = (q.value || '').trim().toLowerCase(), shown = 0;
    cards.forEach(function (c) {
      var ok = (!activeCat || c.dataset.cat === activeCat) && (!term || c.dataset.hay.indexOf(term) > -1);
      c.hidden = !ok;
      if (ok) shown++;
    });
    countEl.textContent = 'Menampilkan ' + shown + (shown !== total ? ' dari ' + total : '') + ' proyek';
    emptyEl.classList.toggle('show', shown === 0);
  }

  cards.forEach(function (c) { c.querySelector('input').addEventListener('change', sync); });
  q.addEventListener('input', applyFilter);
  filters.forEach(function (f) {
    f.addEventListener('click', function () {
      filters.forEach(function (x) { x.classList.remove('on'); });
      f.classList.add('on');
      activeCat = f.dataset.cat;
      applyFilter();
    });
  });
  sync(); // pulihkan state jika browser menyimpan pilihan (tombol Back)
})();

function onClaimSubmit(form) {
  var btn = document.getElementById('btn-confirm-claim');
  if (btn.dataset.loading === '1') return false;
  if (form.dataset.confirmed === '1') return true;

  var picked = form.querySelector('input[name=project_id]:checked');
  if (!picked) {
    tifAlert({
      title: 'Belum ada proyek dipilih',
      text: 'Pilih satu proyek terlebih dahulu sebelum mengonfirmasi klaim.',
      variant: 'warning',
      okText: 'Oke, aku pilih dulu'
    });
    return false;
  }

  var card = picked.closest('.project-card');
  var titleEl = card ? card.querySelector('h3') : null;

  tifConfirm({
    title: 'Klaim proyek ini?',
    text: 'Kamu hanya boleh satu proyek per semester, dan setelah dikonfirmasi pilihan tidak dapat diganti sendiri.',
    detail: titleEl ? titleEl.textContent.trim() : '',
    okText: 'Ya, klaim sekarang',
    cancelText: 'Cek lagi'
  }).then(function (ok) {
    if (!ok) return;
    form.dataset.confirmed = '1';
    btn.dataset.loading = '1';
    btn.disabled = true;
    btn.textContent = 'Memproses klaim…';
    document.querySelectorAll('.cs-step').forEach(function (s) {
      var n = +s.dataset.step;
      s.classList.toggle('done', n < 3);
      s.classList.toggle('active', n === 3);
    });
    form.submit();
  });
  return false;
}
</script>
<?php endif; ?>
