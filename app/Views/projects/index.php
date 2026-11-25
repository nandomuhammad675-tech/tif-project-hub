<?php
/**
 * @var array      $projects
 * @var array      $categories
 * @var array      $semesters
 * @var array      $filters
 * @var string     $sort
 * @var int        $page
 * @var int        $totalPages
 * @var int        $total
 * @var array|null $userProject
 */

// Bangun URL katalog dengan mempertahankan filter lain (dipakai chip & tombol)
$catalogUrl = function (array $override = []) use ($filters, $sort): string {
    $params = array_merge($filters, ['sort' => $sort], $override);
    $params = array_filter($params, fn($v, $k) => $v !== '' && $v !== null && !($k === 'sort' && $v === 'newest'), ARRAY_FILTER_USE_BOTH);
    return '/projects' . ($params ? '?' . http_build_query($params) : '');
};

$availabilityOptions = [
    ''          => 'Semua',
    'available' => 'Tersedia',
    'taken'     => 'Sudah dikerjakan',
];
$sortOptions = [
    'newest'  => 'Terbaru',
    'popular' => 'Terpopuler',
    'title'   => 'Judul A-Z',
];

// Daftar filter yang sedang aktif (untuk chip yang bisa dihapus satu per satu)
$activeFilters = [];
if ($filters['q'] !== '') {
    $activeFilters[] = ['label' => 'Kata kunci: ' . $filters['q'], 'remove' => $catalogUrl(['q' => '', 'page' => ''])];
}
foreach ($categories as $c) {
    if ((string) $filters['category_id'] === (string) $c['id']) {
        $activeFilters[] = ['label' => 'Kategori: ' . $c['name'], 'remove' => $catalogUrl(['category_id' => '', 'page' => ''])];
    }
}
foreach ($semesters as $s) {
    if ((string) $filters['semester_id'] === (string) $s['id']) {
        $activeFilters[] = ['label' => $s['name'], 'remove' => $catalogUrl(['semester_id' => '', 'page' => ''])];
    }
}
if (($filters['availability'] ?? '') !== '' && isset($availabilityOptions[$filters['availability']])) {
    $activeFilters[] = ['label' => 'Status: ' . $availabilityOptions[$filters['availability']], 'remove' => $catalogUrl(['availability' => '', 'page' => ''])];
}
$hasFilter = !empty($activeFilters);
$iconX = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
?>
<style>
    /* ---------- Katalog: header & banner ---------- */
    .catalog-head { margin-bottom: 1.25rem; }
    .catalog-head h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; }
    .catalog-head p { color: var(--muted); margin-top: .2rem; }
    .work-banner {
        display: flex; align-items: center; gap: .85rem; flex-wrap: wrap;
        padding: .9rem 1.15rem; margin-bottom: 1.25rem;
        background: linear-gradient(135deg, #e3f3f7, #f3fafc);
        border: 1px solid rgba(3,121,146,.25); border-radius: var(--radius);
    }
    .work-banner .wb-ico {
        width: 40px; height: 40px; border-radius: 12px; flex: none;
        display: grid; place-items: center; background: var(--primary); color: #fff;
    }
    .work-banner .wb-ico .ico { width: 20px; height: 20px; }
    .work-banner .wb-text { flex: 1; min-width: 200px; font-size: .9rem; color: var(--muted); }
    .work-banner .wb-text strong { display: block; color: var(--text); font-size: 1rem; }

    /* ---------- Katalog: panel filter ---------- */
    .filter-panel {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); box-shadow: var(--shadow);
        padding: 1.15rem 1.25rem; margin-bottom: 1.25rem;
    }
    .fp-search { position: relative; display: flex; gap: .6rem; }
    .fp-search .fp-search-ico {
        position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
        color: var(--muted); pointer-events: none; display: flex;
    }
    .fp-search .fp-search-ico .ico { width: 18px; height: 18px; }
    .fp-search input { padding-left: 2.8rem; height: 48px; font-size: .95rem; }
    .fp-search .btn { height: 48px; padding: 0 1.5rem; }

    .fp-row { margin-top: 1.05rem; }
    .fp-label {
        display: block; font-size: .72rem; font-weight: 700; letter-spacing: .6px;
        text-transform: uppercase; color: var(--muted); margin-bottom: .5rem;
    }
    .chip-row { display: flex; flex-wrap: wrap; gap: .5rem; }
    .chip {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .45rem .9rem; border-radius: 999px; font-size: .85rem; font-weight: 600;
        color: var(--text); background: #fff; border: 1.5px solid var(--border);
        text-decoration: none; transition: all .15s ease; white-space: nowrap;
    }
    .chip .ico { width: 15px; height: 15px; }
    .chip:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-1px); }
    .chip.is-active { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 4px 12px rgba(3,121,146,.25); }
    .chip.is-active:hover { color: #fff; }

    .fp-grid { display: grid; grid-template-columns: 1fr 1fr 1.3fr; gap: 1rem; margin-top: 1.05rem; align-items: end; }
    .segmented { display: inline-flex; background: #eef5f8; border-radius: 12px; padding: 4px; gap: 2px; width: 100%; }
    .segmented a {
        flex: 1; text-align: center; padding: .55rem .7rem; border-radius: 9px;
        font-size: .85rem; font-weight: 600; color: var(--muted); text-decoration: none;
        transition: all .15s ease; white-space: nowrap;
    }
    .segmented a:hover { color: var(--primary); }
    .segmented a.is-active { background: #fff; color: var(--primary-dark); box-shadow: 0 1px 4px rgba(2,37,51,.12); }
    .fp-grid .form-control { height: 44px; padding-top: 0; padding-bottom: 0; }

    .active-bar {
        display: flex; align-items: center; flex-wrap: wrap; gap: .5rem;
        margin-top: 1.05rem; padding-top: 1rem; border-top: 1px dashed var(--border);
    }
    .active-bar .ab-title { font-size: .8rem; color: var(--muted); font-weight: 600; margin-right: .25rem; }
    .tag-active {
        display: inline-flex; align-items: center; gap: .4rem;
        background: var(--primary-light); color: var(--primary-dark);
        border-radius: 999px; padding: .3rem .4rem .3rem .8rem; font-size: .8rem; font-weight: 600;
    }
    .tag-active a {
        display: grid; place-items: center; width: 20px; height: 20px; border-radius: 50%;
        background: rgba(3,121,146,.15); color: var(--primary-dark);
    }
    .tag-active a:hover { background: var(--primary); color: #fff; }
    .reset-link { margin-left: auto; font-size: .82rem; font-weight: 700; color: #b91c1c; text-decoration: none; }
    .reset-link:hover { text-decoration: underline; }

    .result-bar { display: flex; justify-content: space-between; align-items: center; margin: 0 0 1rem; color: var(--muted); font-size: .9rem; }
    .result-bar strong { color: var(--text); }


    /* ---------- Dropdown custom (selalu membuka ke bawah) ---------- */
    .dd { position: relative; }
    .dd.open { z-index: 40; }
    .dd-btn {
        width: 100%; height: 44px; display: flex; align-items: center; justify-content: space-between; gap: .6rem;
        padding: 0 .9rem 0 1rem; font: inherit; font-size: .92rem; color: var(--text); text-align: left;
        background: #fff; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .dd-btn:hover { border-color: rgba(3,121,146,.45); }
    .dd-btn:focus-visible, .dd.open .dd-btn { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(3,121,146,.14); }
    .dd-btn .dd-val { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dd-btn .dd-chev { flex: none; width: 18px; height: 18px; color: var(--muted); transition: transform .2s ease; }
    .dd.open .dd-chev { transform: rotate(180deg); color: var(--primary); }
    .dd-menu {
        position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 41;
        margin: 0; padding: .35rem; list-style: none; max-height: 280px; overflow-y: auto;
        background: #fff; border: 1px solid var(--border); border-radius: 14px;
        box-shadow: 0 18px 44px rgba(2,37,51,.18); overscroll-behavior: contain;
        opacity: 0; visibility: hidden; transform: translateY(-6px);
        transition: opacity .15s ease, transform .15s ease, visibility .15s;
    }
    .dd.open .dd-menu { opacity: 1; visibility: visible; transform: none; }
    .dd-opt {
        display: flex; align-items: center; justify-content: space-between; gap: .6rem;
        padding: .6rem .75rem; border-radius: 10px; font-size: .9rem; font-weight: 600; color: var(--text); cursor: pointer;
    }
    .dd-opt.is-focus { background: var(--primary-light); color: var(--primary-dark); }
    .dd-opt[aria-selected="true"] { color: var(--primary); font-weight: 800; }
    .dd-opt svg { width: 16px; height: 16px; flex: none; opacity: 0; color: var(--primary); }
    .dd-opt[aria-selected="true"] svg { opacity: 1; }
    .dd-native { display: none !important; }
    @media (prefers-reduced-motion: reduce) { .dd-menu, .dd-chev { transition: none; } }

    @media (max-width: 768px) {
        .fp-grid { grid-template-columns: 1fr; }
        .chip-row.scroll { flex-wrap: nowrap; overflow-x: auto; padding-bottom: .35rem; scrollbar-width: thin; }
        .fp-search .btn { padding: 0 1.1rem; }
        .reset-link { margin-left: 0; }
    }
</style>

<div class="catalog-head">
    <h1>Katalog Proyek</h1>
    <p>Temukan proyek yang cocok denganmu: cari lewat kata kunci atau saring berdasarkan kategori dan status.</p>
</div>

<?php if (!empty($userProject)): ?>
<div class="work-banner">
    <span class="wb-ico"><?= icon('ticket') ?></span>
    <div class="wb-text">
        Proyek yang sedang kamu kerjakan
        <strong><?= e($userProject['title']) ?></strong>
    </div>
    <a href="/projects/<?= e($userProject['slug']) ?>" class="btn btn-primary btn-sm">Lihat proyek →</a>
</div>
<?php endif; ?>

<form method="GET" action="/projects" class="filter-panel" id="filter-form">
    <!-- Nilai filter berbasis chip/segmen ikut terkirim saat mencari -->
    <input type="hidden" name="category_id" value="<?= e((string) $filters['category_id']) ?>">
    <input type="hidden" name="availability" value="<?= e((string) ($filters['availability'] ?? '')) ?>">

    <div class="fp-search">
        <span class="fp-search-ico"><?= icon('search') ?></span>
        <input type="text" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Cari nama proyek atau teknologi, misalnya: Laravel, IoT, kasir…" aria-label="Cari proyek">
        <button type="submit" class="btn btn-primary">Cari</button>
    </div>

    <div class="fp-row">
        <span class="fp-label">Kategori</span>
        <div class="chip-row scroll">
            <a href="<?= e($catalogUrl(['category_id' => '', 'page' => ''])) ?>" class="chip <?= (string) $filters['category_id'] === '' ? 'is-active' : '' ?>">Semua</a>
            <?php foreach ($categories as $c): ?>
            <a href="<?= e($catalogUrl(['category_id' => $c['id'], 'page' => ''])) ?>"
               class="chip <?= (string) $filters['category_id'] === (string) $c['id'] ? 'is-active' : '' ?>">
                <?= category_icon($c['icon'] ?? '') ?><?= e($c['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="fp-grid">
        <div>
            <span class="fp-label">Status proyek</span>
            <div class="segmented">
                <?php foreach ($availabilityOptions as $val => $label): ?>
                <a href="<?= e($catalogUrl(['availability' => $val, 'page' => ''])) ?>"
                   class="<?= (string) ($filters['availability'] ?? '') === (string) $val ? 'is-active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <label class="fp-label" for="f-semester">Semester</label>
            <select name="semester_id" id="f-semester" class="form-control" data-dd onchange="this.form.submit()">
                <option value="">Semua semester</option>
                <?php foreach ($semesters as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= (string) $filters['semester_id'] === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="fp-label" for="f-sort">Urutkan berdasarkan</label>
            <select name="sort" id="f-sort" class="form-control" data-dd onchange="this.form.submit()">
                <?php foreach ($sortOptions as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if ($hasFilter): ?>
    <div class="active-bar">
        <span class="ab-title">Filter aktif:</span>
        <?php foreach ($activeFilters as $f): ?>
        <span class="tag-active"><?= e($f['label']) ?>
            <a href="<?= e($f['remove']) ?>" title="Hapus filter ini" aria-label="Hapus filter <?= e($f['label']) ?>"><?= $iconX ?></a>
        </span>
        <?php endforeach; ?>
        <a href="/projects" class="reset-link">Reset semua filter</a>
    </div>
    <?php endif; ?>
</form>

<div class="result-bar">
    <span><strong><?= (int) $total ?></strong> proyek ditemukan<?= $hasFilter ? ' sesuai filter' : '' ?></span>
</div>

<?php if (empty($projects)): ?>
<div class="empty-state">
    <div class="icon"><?= icon('search') ?></div>
    <p>Belum ada proyek yang cocok dengan pencarianmu.<br>Coba ganti kata kunci atau hapus beberapa filter.</p>
    <?php if ($hasFilter): ?>
    <a href="/projects" class="btn btn-primary" style="margin-top:1rem;">Reset semua filter</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="project-grid">
    <?php foreach ($projects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= e($catalogUrl(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
/* Dropdown custom: menggantikan <select data-dd>, selalu terbuka ke bawah */
(function () {
    var CHEV  = '<svg class="dd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
    var CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    var openDd = null;

    function closeAll(except) {
        document.querySelectorAll('.dd.open').forEach(function (d) { if (d !== except) d._close(); });
    }

    function build(sel) {
        var wrap = document.createElement('div');
        wrap.className = 'dd';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dd-btn';
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        var lab = sel.id ? document.querySelector('label[for="' + sel.id + '"]') : null;
        if (sel.id) btn.id = sel.id + '-btn';
        var menu = document.createElement('ul');
        menu.className = 'dd-menu';
        menu.setAttribute('role', 'listbox');
        if (lab) { if (!lab.id) lab.id = sel.id + '-lbl'; btn.setAttribute('aria-labelledby', lab.id + ' ' + btn.id); menu.setAttribute('aria-labelledby', lab.id); }

        var opts = [].slice.call(sel.options), items = [], idx = Math.max(0, sel.selectedIndex), focusI = idx;

        opts.forEach(function (o, i) {
            var li = document.createElement('li');
            li.className = 'dd-opt';
            li.setAttribute('role', 'option');
            li.innerHTML = '<span></span>' + CHECK;
            li.firstChild.textContent = o.textContent;
            li.addEventListener('mousedown', function (e) { e.preventDefault(); });
            li.addEventListener('click', function () { choose(i); });
            li.addEventListener('mousemove', function () { setFocus(i, false); });
            menu.appendChild(li);
            items.push(li);
        });

        function paint() {
            btn.innerHTML = '<span class="dd-val"></span>' + CHEV;
            btn.firstChild.textContent = opts[idx] ? opts[idx].textContent : '';
            items.forEach(function (li, i) { li.setAttribute('aria-selected', i === idx ? 'true' : 'false'); });
        }
        function setFocus(i, scroll) {
            focusI = Math.max(0, Math.min(items.length - 1, i));
            items.forEach(function (li, n) { li.classList.toggle('is-focus', n === focusI); });
            if (scroll !== false) {
                var li = items[focusI];
                if (li.offsetTop < menu.scrollTop) menu.scrollTop = li.offsetTop - 4;
                else if (li.offsetTop + li.offsetHeight > menu.scrollTop + menu.clientHeight) menu.scrollTop = li.offsetTop + li.offsetHeight - menu.clientHeight + 4;
            }
        }
        function open() {
            closeAll(wrap);
            wrap.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
            setFocus(idx);
            // Pastikan menu terlihat penuh: scroll halaman ke bawah bila terpotong
            requestAnimationFrame(function () {
                var r = menu.getBoundingClientRect();
                var over = r.bottom - window.innerHeight + 16;
                if (over > 0) window.scrollBy({ top: over, behavior: 'smooth' });
            });
        }
        function close() {
            wrap.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
        function choose(i) {
            var changed = i !== idx;
            idx = i; paint(); close(); btn.focus();
            if (changed) {
                sel.selectedIndex = i;
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        wrap._close = close;

        btn.addEventListener('click', function () { wrap.classList.contains('open') ? close() : open(); });
        btn.addEventListener('keydown', function (e) {
            var isOpen = wrap.classList.contains('open');
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!isOpen) open(); else setFocus(focusI + (e.key === 'ArrowDown' ? 1 : -1));
            } else if (e.key === 'Home' && isOpen) { e.preventDefault(); setFocus(0); }
            else if (e.key === 'End' && isOpen) { e.preventDefault(); setFocus(items.length - 1); }
            else if ((e.key === 'Enter' || e.key === ' ') && isOpen) { e.preventDefault(); choose(focusI); }
            else if (e.key === 'Escape' && isOpen) { e.preventDefault(); close(); }
            else if (e.key === 'Tab' && isOpen) { close(); }
        });

        if (lab) lab.addEventListener('click', function (e) { e.preventDefault(); btn.focus(); });

        sel.classList.add('dd-native');
        sel.setAttribute('tabindex', '-1');
        sel.setAttribute('aria-hidden', 'true');
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(btn);
        wrap.appendChild(menu);
        wrap.appendChild(sel);
        paint();
    }

    document.querySelectorAll('select[data-dd]').forEach(build);
    document.addEventListener('mousedown', function (e) { if (!e.target.closest('.dd')) closeAll(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
    window.addEventListener('pageshow', function () { closeAll(); });
})();
</script>
