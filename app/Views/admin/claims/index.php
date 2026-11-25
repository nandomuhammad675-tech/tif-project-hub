<style>
    .cl-filter { display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
    .cl-filter-label { font-size: .82rem; font-weight: 700; color: var(--muted); }
    .cl-dd { position: relative; width: 260px; max-width: 100%; }
    .cl-native { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; pointer-events: none; }
    .cl-dd-btn { width: 100%; display: flex; align-items: center; gap: .65rem; text-align: left; font: inherit; font-size: .9rem; font-weight: 700; padding: .65rem .9rem; background: #fff; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; color: var(--text); transition: border-color .15s, box-shadow .15s; }
    .cl-dd-btn:hover, .cl-dd.open .cl-dd-btn { border-color: var(--primary); }
    .cl-dd.open .cl-dd-btn, .cl-dd-btn:focus-visible { box-shadow: 0 0 0 3px rgba(3,121,146,.15); outline: none; }
    .cl-dd-btn .lead { width: 17px; height: 17px; flex: none; color: var(--primary); }
    .cl-dd-btn .chev { width: 18px; height: 18px; flex: none; color: var(--muted); transition: transform .2s ease; }
    .cl-dd.open .chev { transform: rotate(180deg); color: var(--primary); }
    .cl-dd-text { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cl-dd-pop { position: absolute; z-index: 60; top: calc(100% + 8px); left: 0; right: 0; margin: 0; padding: .4rem; list-style: none; max-height: 280px; overflow-y: auto; background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 24px 60px rgba(2,37,51,.28); }
    .cl-dd-pop.up { top: auto; bottom: calc(100% + 8px); }
    .cl-dd-pop[hidden] { display: none; }
    .cl-dd-opt { display: flex; align-items: center; gap: .6rem; padding: .6rem .75rem; border-radius: 10px; font-size: .9rem; font-weight: 600; cursor: pointer; color: var(--text); transition: background .12s; }
    .cl-dd-opt .ck { width: 16px; height: 16px; flex: none; opacity: 0; }
    .cl-dd-opt.act { background: var(--primary-light); }
    .cl-dd-opt.sel { font-weight: 800; color: var(--primary-dark); }
    .cl-dd-opt.sel .ck { opacity: 1; color: var(--primary); }
    .cl-link { color: inherit; }
    .cl-row { cursor: pointer; transition: background .15s ease; }
    .cl-row:hover, .cl-row:focus-visible { background: #f2f9fb; outline: none; }
    .cl-row:hover td:first-child, .cl-row:focus-visible td:first-child { box-shadow: inset 3px 0 0 var(--primary); }
    .cl-status { gap: .4rem; padding: .25rem .75rem; font-size: .72rem; }
    .cl-status i { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .badge-success.cl-status i { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
</style>

<?php
/**
 * @var array  $claims
 * @var array  $semesters
 * @var string $semesterId
 */
?>
<div class="page-head">
    <div>
        <h1>Tiket Klaim</h1>
        <p><?= count($claims) ?> tiket klaim</p>
    </div>
</div>

<form method="GET" class="cl-filter" id="cl-filter" data-reveal="up">
    <span class="cl-filter-label">Filter semester</span>
    <div class="cl-dd" id="cl-dd" data-ph="Semua semester">
        <select id="semester_id" name="semester_id" class="cl-native" tabindex="-1" aria-hidden="true" data-fade-submit>
            <option value="">Semua semester</option>
            <?php foreach ($semesters as $semester): ?>
            <option value="<?= (int) $semester['id'] ?>" <?= (string) $semester['id'] === $semesterId ? 'selected' : '' ?>><?= e($semester['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="cl-dd-btn" aria-haspopup="listbox" aria-expanded="false">
            <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 3H2l8 9.46V19l4 2v-8.54z"/></svg>
            <span class="cl-dd-text"></span>
            <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <ul class="cl-dd-pop" role="listbox" hidden></ul>
    </div>
</form>

<?php if (empty($claims)): ?>
<div class="empty-state">
    <div class="icon"><?= icon('ticket') ?></div>
    <p><strong>Belum ada tiket klaim.</strong></p>
    <p class="text-sm">Tiket akan muncul di sini setelah mahasiswa berhasil mengklaim proyek.</p>
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Kode tiket</th>
                <th>Mahasiswa</th>
                <th>Proyek</th>
                <th>Semester</th>
                <th>Waktu klaim</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($claims as $claim): ?>
            <tr class="cl-row" data-href="/admin/claims/<?= (int) $claim['id'] ?>" tabindex="0" role="link" aria-label="Lihat detail tiket <?= e($claim['ticket_code']) ?>">
                <td><a class="cl-link" href="/admin/claims/<?= (int) $claim['id'] ?>"><strong><?= e($claim['ticket_code']) ?></strong></a></td>
                <td>
                    <?= e($claim['student_name']) ?><br>
                    <small class="text-muted"><?= e($claim['student_nim'] ?? '-') ?></small>
                </td>
                <td><?= e($claim['project_title']) ?></td>
                <td><?= e($claim['semester_name']) ?></td>
                <td><?= e(format_datetime($claim['claimed_at'])) ?></td>
                <td>
                    <?php if ($claim['status'] === 'claimed'): ?>
                        <span class="badge badge-success cl-status"><i></i>Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-danger cl-status"><i></i>Dicabut</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
(function () {
    var root = document.getElementById('cl-dd');
    if (!root) return;
    var sel = root.querySelector('select'), btn = root.querySelector('.cl-dd-btn'),
        text = root.querySelector('.cl-dd-text'), pop = root.querySelector('.cl-dd-pop');
    var items = [], act = -1;
    var CK = '<svg class="ck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    Array.prototype.forEach.call(sel.options, function (o) {
        var li = document.createElement('li');
        li.className = 'cl-dd-opt'; li.setAttribute('role', 'option'); li.dataset.v = o.value;
        li.innerHTML = CK + '<span>' + esc(o.text.trim()) + '</span>';
        pop.appendChild(li); items.push(li);
    });
    function paint() {
        text.textContent = sel.options[sel.selectedIndex].text.trim();
        items.forEach(function (li) { var on = li.dataset.v === sel.value; li.classList.toggle('sel', on); li.setAttribute('aria-selected', on ? 'true' : 'false'); });
    }
    function setAct(i) {
        act = (i + items.length) % items.length;
        items.forEach(function (li, k) { li.classList.toggle('act', k === act); });
        items[act].scrollIntoView({ block: 'nearest' });
    }
    function open() {
        pop.classList.remove('up'); pop.hidden = false; root.classList.add('open'); btn.setAttribute('aria-expanded', 'true');
        var r = pop.getBoundingClientRect(), f = root.getBoundingClientRect(), vh = window.innerHeight;
        if (r.bottom > vh - 12 && f.top > r.height + 24 && f.top > vh - f.bottom) pop.classList.add('up');
        setAct(Math.max(0, items.findIndex(function (li) { return li.dataset.v === sel.value; })));
    }
    function close() { pop.hidden = true; root.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); }
    function pick(li) {
        if (sel.value !== li.dataset.v) { sel.value = li.dataset.v; paint(); close(); sel.dispatchEvent(new Event('change', { bubbles: true })); }
        else { close(); btn.focus(); }
    }
    btn.addEventListener('click', function () { pop.hidden ? open() : close(); });
    btn.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); if (pop.hidden) open(); else setAct(act + (e.key === 'ArrowDown' ? 1 : -1)); }
        else if ((e.key === 'Enter' || e.key === ' ') && !pop.hidden) { e.preventDefault(); pick(items[act]); }
        else if (e.key === 'Escape' || e.key === 'Tab') close();
    });
    pop.addEventListener('click', function (e) { var li = e.target.closest('.cl-dd-opt'); if (li) pick(li); });
    pop.addEventListener('mousemove', function (e) { var li = e.target.closest('.cl-dd-opt'); if (li) setAct(items.indexOf(li)); });
    document.addEventListener('mousedown', function (e) { if (!root.contains(e.target)) close(); });
    paint();
})();
</script>

<script>
(function () {
    document.querySelectorAll('.cl-row').forEach(function (tr) {
        function go() { var a = tr.querySelector('a.cl-link'); if (a) a.click(); else window.location.href = tr.dataset.href; }
        tr.addEventListener('click', function (e) { if (!e.target.closest('a, button')) go(); });
        tr.addEventListener('keydown', function (e) { if (e.key === 'Enter') go(); });
    });
})();
</script>
<script>
(function () {
    var sel = document.getElementById('semester_id');
    if (!sel) return;
    sel.addEventListener('change', function () {
        var f = sel.form, h = document.documentElement;
        h.classList.add('leaving');
        setTimeout(function () { f.submit(); }, 220);
        setTimeout(function () { h.classList.remove('leaving'); }, 2500);
    });
})();
</script>
