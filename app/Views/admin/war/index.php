<?php
/**
 * @var array|null $war
 * @var string     $warStatus
 * @var array      $semesters
 * @var int        $claimedCount
 * @var int        $availableCount
 * @var string     $csrf
 */
$statusMap = [
    'active'    => ['Sedang berlangsung', 'is-active'],
    'scheduled' => ['Terjadwal', 'is-scheduled'],
    'ended'     => ['Selesai', 'is-closed'],
    'closed'    => ['Ditutup', 'is-closed'],
];
[$statusLabel, $statusCls] = $statusMap[$warStatus ?? 'closed'] ?? $statusMap['closed'];

$claimed   = (int) $claimedCount;
$available = (int) $availableCount;
$totalProj = $claimed + $available;
$pct       = $totalProj > 0 ? (int) round($claimed / $totalProj * 100) : 0;

$startsVal = !empty($war['starts_at']) ? date('Y-m-d\TH:i', strtotime($war['starts_at'])) : '';
$endsVal   = !empty($war['ends_at'])   ? date('Y-m-d\TH:i', strtotime($war['ends_at']))   : '';
$isOpen    = !empty($war['is_open']);
?>
<style>
    .wr-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .wr-head h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.4px; }
    .wr-head p { color: var(--muted); font-size: .9rem; margin-top: .15rem; }
    .wr-pill { display: inline-flex; align-items: center; gap: .5rem; padding: .4rem .9rem; border-radius: 999px; font-size: .85rem; font-weight: 800; background: #eef2f5; color: #475569; }
    .wr-pill .dot { width: 9px; height: 9px; border-radius: 50%; background: #94a3b8; }
    .wr-pill.is-active { background: #dcfce7; color: #15803d; }
    .wr-pill.is-active .dot { background: #22c55e; animation: wrPulse 1.6s infinite; }
    .wr-pill.is-scheduled { background: #fef3c7; color: #b45309; }
    .wr-pill.is-scheduled .dot { background: #f59e0b; }
    @keyframes wrPulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.55); } 70%,100% { box-shadow: 0 0 0 8px rgba(34,197,94,0); } }

    .wr-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    .wr-stat { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 1.05rem 1.2rem; }
    .wr-stat .l { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); }
    .wr-stat .v { font-size: 1.8rem; font-weight: 800; line-height: 1.15; margin-top: .25rem; color: var(--text); }
    .wr-stat .v.primary { color: var(--primary-dark); }
    .wr-bar { height: 8px; border-radius: 999px; background: #e6eff2; margin-top: .6rem; overflow: hidden; }
    .wr-bar i { display: block; height: 100%; border-radius: 999px; background: var(--grad, var(--primary)); }
    .wr-stat small { display: block; font-size: .76rem; color: var(--muted); margin-top: .4rem; }

    .wr-layout { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); gap: 1.25rem; align-items: start; }
    .wr-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1.5rem; }
    .wr-step { display: flex; gap: 1rem; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px dashed var(--border); }
    .wr-step:last-of-type { border-bottom: 0; padding-bottom: 0; margin-bottom: 0; }
    .wr-step .n { width: 32px; height: 32px; flex: none; border-radius: 50%; display: grid; place-items: center; font-weight: 800; font-size: .9rem; color: #fff; background: var(--grad, var(--primary)); }
    .wr-step .body { flex: 1; min-width: 0; }
    .wr-step h2 { font-size: 1.02rem; font-weight: 800; }
    .wr-step .hint { font-size: .84rem; color: var(--muted); margin: .15rem 0 .8rem; line-height: 1.5; }

    /* Saklar buka/tutup */
    .wr-switch { display: flex; align-items: center; gap: .9rem; padding: .85rem 1rem; border: 1.5px solid var(--border); border-radius: 14px; cursor: pointer; transition: border-color .15s, background .15s; user-select: none; }
    .wr-switch:hover { border-color: var(--primary); }
    .wr-switch.on { border-color: #86efac; background: #f0fdf4; }
    .wr-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .wr-track { width: 48px; height: 28px; border-radius: 999px; background: #cbd5e1; position: relative; flex: none; transition: background .2s; }
    .wr-track::after { content: ""; position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 50%; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.25); transition: transform .2s; }
    .wr-switch.on .wr-track { background: #22c55e; }
    .wr-switch.on .wr-track::after { transform: translateX(20px); }
    .wr-switch input:focus-visible + .wr-track { outline: 3px solid rgba(3,121,146,.35); outline-offset: 2px; }
    .wr-switch .t b { display: block; font-size: .95rem; }
    .wr-switch .t span { font-size: .8rem; color: var(--muted); }

    /* Pemilih tanggal & jam */
    .wr-times { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .wr-field > label { display: block; font-size: .8rem; font-weight: 700; margin-bottom: .4rem; }
    .wr-dt { position: relative; }
    .wr-dt-btn { width: 100%; display: flex; align-items: center; gap: .65rem; text-align: left; font: inherit; font-size: .92rem; padding: .75rem .9rem; background: #fff; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; color: var(--muted); transition: border-color .15s, box-shadow .15s; }
    .wr-dt-btn:hover, .wr-dt.open .wr-dt-btn { border-color: var(--primary); }
    .wr-dt.open .wr-dt-btn { box-shadow: 0 0 0 3px rgba(3,121,146,.15); }
    .wr-dt-btn svg { width: 18px; height: 18px; flex: none; color: var(--primary); }
    .wr-dt.has-value .wr-dt-btn { color: var(--text); font-weight: 700; }
    .wr-dt-text { flex: 1; min-width: 0; line-height: 1.3; }
    .wr-dt-text small { display: block; font-weight: 600; color: var(--muted); font-size: .76rem; }
    .wr-dt-pop { position: absolute; z-index: 60; top: calc(100% + 8px); left: 0; width: 292px; max-width: calc(100vw - 32px); background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 24px 60px rgba(2,37,51,.28); padding: .8rem; }
    .wr-dt-pop.up { top: auto; bottom: calc(100% + 8px); }
    .wr-dt-pop[hidden] { display: none; }
    .wr-cal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .35rem; }
    .wr-cal-head b { font-size: .95rem; }
    .wr-nav { width: 30px; height: 30px; border-radius: 50%; border: 0; background: #eef5f8; cursor: pointer; font-size: 1rem; color: var(--primary-dark); line-height: 1; }
    .wr-nav:hover { background: var(--primary); color: #fff; }
    .wr-cal { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
    .wr-cal .dow { font-size: .68rem; font-weight: 800; color: var(--muted); padding: .15rem 0 .25rem; }
    .wr-day { height: 32px; padding: 0; border: 0; background: transparent; border-radius: 50%; font: inherit; font-size: .85rem; font-weight: 600; cursor: pointer; color: var(--text); }
    .wr-day:hover { background: var(--primary-light); }
    .wr-day.today { box-shadow: inset 0 0 0 1.5px var(--primary); }
    .wr-day.sel { background: var(--primary); color: #fff; font-weight: 800; }
    .wr-time { display: flex; align-items: center; gap: .4rem; margin-top: .55rem; padding-top: .55rem; border-top: 1px solid var(--border); }
    .wr-time label { font-size: .78rem; font-weight: 700; color: var(--muted); margin-right: auto; }
    .wr-time select { font: inherit; font-size: .85rem; font-weight: 700; padding: .3rem .4rem; border: 1.5px solid var(--border); border-radius: 10px; background: #fff; }
    .wr-time .colon { font-weight: 800; }
    .wr-chips { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .5rem; }
    .wr-chip { font: inherit; font-size: .78rem; font-weight: 700; padding: .3rem .7rem; border-radius: 999px; border: 1.5px solid var(--border); background: #fff; cursor: pointer; color: var(--text); transition: all .15s; }
    .wr-chip:hover { border-color: var(--primary); color: var(--primary); }
    .wr-pop-foot { display: flex; justify-content: space-between; margin-top: .5rem; }
    .wr-field:nth-child(2) .wr-dt-pop { left: auto; right: 0; }
    .wr-link { background: none; border: 0; font: inherit; font-size: .85rem; font-weight: 700; cursor: pointer; padding: .3rem .2rem; color: var(--primary); }
    .wr-link.danger { color: #dc2626; }
    .wr-link:hover { text-decoration: underline; }

    .wr-presets { margin-top: 1rem; padding: .9rem 1rem; background: #f4f9fb; border-radius: 14px; }
    .wr-presets .t { font-size: .78rem; font-weight: 800; color: var(--muted); margin-bottom: .55rem; }

    /* Dropdown semester (custom, senada dengan pemilih tanggal) */
    .wr-dd { position: relative; }
    .wr-native { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; pointer-events: none; }
    .wr-dd-btn { width: 100%; display: flex; align-items: center; gap: .65rem; text-align: left; font: inherit; font-size: .92rem; padding: .75rem .9rem; background: #fff; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; color: var(--muted); transition: border-color .15s, box-shadow .15s; }
    .wr-dd-btn:hover, .wr-dd.open .wr-dd-btn { border-color: var(--primary); }
    .wr-dd.open .wr-dd-btn, .wr-dd-btn:focus-visible { box-shadow: 0 0 0 3px rgba(3,121,146,.15); outline: none; }
    .wr-dd-btn .lead { width: 18px; height: 18px; flex: none; color: var(--primary); }
    .wr-dd-btn .chev { width: 18px; height: 18px; flex: none; color: var(--muted); transition: transform .2s ease; }
    .wr-dd.open .chev { transform: rotate(180deg); color: var(--primary); }
    .wr-dd.has-value .wr-dd-btn { color: var(--text); font-weight: 700; }
    .wr-dd-text { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .wr-dd-text small { font-weight: 600; color: var(--muted); font-size: .78rem; margin-left: .35rem; }
    .wr-dd-pop { position: absolute; z-index: 60; top: calc(100% + 8px); left: 0; right: 0; margin: 0; padding: .4rem; list-style: none; max-height: 280px; overflow-y: auto; background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 24px 60px rgba(2,37,51,.28); }
    .wr-dd-pop.up { top: auto; bottom: calc(100% + 8px); }
    .wr-dd-pop[hidden] { display: none; }
    .wr-dd-opt { display: flex; align-items: center; gap: .6rem; padding: .6rem .75rem; border-radius: 10px; font-size: .9rem; font-weight: 600; cursor: pointer; color: var(--text); transition: background .12s; }
    .wr-dd-opt small { color: var(--muted); font-weight: 600; font-size: .78rem; margin-left: auto; }
    .wr-dd-opt .ck { width: 16px; height: 16px; flex: none; color: #fff; opacity: 0; }
    .wr-dd-opt.act { background: var(--primary-light); }
    .wr-dd-opt.sel { font-weight: 800; color: var(--primary-dark); }
    .wr-dd-opt.sel .ck { opacity: 1; color: var(--primary); }

    /* Panel ringkasan */
    .wr-side { position: sticky; top: 20px; }
    .wr-preview h3 { font-size: .8rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--muted); margin-bottom: .8rem; }
    .wr-preview .status { font-size: 1.25rem; font-weight: 800; display: flex; align-items: center; gap: .6rem; }
    .wr-preview .note { font-size: .85rem; color: var(--muted); margin: .45rem 0 1rem; line-height: 1.5; }
    .wr-rows { display: grid; gap: .65rem; border-top: 1px dashed var(--border); padding-top: 1rem; }
    .wr-row { display: flex; justify-content: space-between; gap: 1rem; font-size: .85rem; }
    .wr-row span { color: var(--muted); font-weight: 600; }
    .wr-row b { text-align: right; }
    .wr-error { display: none; margin-top: 1rem; padding: .7rem .9rem; border-radius: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: .84rem; font-weight: 600; }
    .wr-error.show { display: block; }
    .wr-save { width: 100%; margin-top: 1.2rem; padding: .85rem; font-size: 1rem; justify-content: center; }
    .wr-save[disabled] { opacity: .5; cursor: not-allowed; }
    .wr-links { margin-top: 1.25rem; font-size: .85rem; }

    @media (max-width: 1100px) { .wr-layout { grid-template-columns: 1fr; } .wr-side { position: static; } }
    @media (max-width: 640px) { .wr-stats { grid-template-columns: 1fr; } .wr-times { grid-template-columns: 1fr; } .wr-step { flex-direction: column; gap: .6rem; } }
</style>

<div class="wr-head page-head">
    <div>
        <h1>Project War</h1>
        <p>Atur kapan mahasiswa boleh mengklaim tiket proyek.</p>
    </div>
    <span class="wr-pill <?= e($statusCls) ?>"><span class="dot"></span><?= e($statusLabel) ?></span>
</div>

<div class="wr-stats">
    <div class="wr-stat" data-reveal="zoom">
        <div class="l">Tersedia diklaim</div>
        <div class="v primary"><?= $available ?></div>
        <small>Proyek yang masih bisa dipilih mahasiswa</small>
    </div>
    <div class="wr-stat" data-reveal="zoom">
        <div class="l">Sudah diklaim</div>
        <div class="v"><?= $claimed ?></div>
        <small>dari <?= $totalProj ?> proyek di semester aktif</small>
    </div>
    <div class="wr-stat" data-reveal="zoom">
        <div class="l">Progres klaim</div>
        <div class="v"><?= $pct ?>%</div>
        <div class="wr-bar"><i style="width:<?= $pct ?>%"></i></div>
    </div>
</div>

<form method="POST" action="/admin/war" id="war-form" class="wr-layout" novalidate>
    <input type="hidden" name="_token" value="<?= e($csrf) ?>">

    <div class="wr-card" data-reveal="up">
        <!-- 1. Semester -->
        <section class="wr-step" data-reveal="soft">
            <span class="n">1</span>
            <div class="body">
                <h2>Semester aktif</h2>
                <p class="hint">Mahasiswa hanya bisa mengklaim proyek dari semester ini.</p>
                <div class="wr-dd" id="wr-sem-dd" data-ph="Pilih semester">
                    <select name="active_semester_id" id="wr-semester" class="wr-native" required tabindex="-1" aria-hidden="true">
                    <option value="">— Pilih semester —</option>
                    <?php foreach ($semesters as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= ((int) ($war['active_semester_id'] ?? 0) === (int) $s['id']) ? 'selected' : '' ?>>
                        <?= e($s['name']) ?><?= !empty($s['academic_year']) ? ' · ' . e($s['academic_year']) : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                    <button type="button" class="wr-dd-btn" aria-haspopup="listbox" aria-expanded="false">
                        <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg>
                        <span class="wr-dd-text"></span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <ul class="wr-dd-pop" role="listbox" hidden></ul>
                </div>
            </div>
        </section>

        <!-- 2. Buka / tutup -->
        <section class="wr-step" data-reveal="soft">
            <span class="n">2</span>
            <div class="body">
                <h2>Buka war</h2>
                <p class="hint">Saklar utama. Kalau mati, tombol klaim terkunci walaupun jadwal sudah diisi.</p>
                <label class="wr-switch <?= $isOpen ? 'on' : '' ?>" id="wr-switch">
                    <input type="hidden" name="is_open" value="0">
                    <input type="checkbox" name="is_open" value="1" id="wr-open" <?= $isOpen ? 'checked' : '' ?>>
                    <span class="wr-track"></span>
                    <span class="t"><b id="wr-open-label"><?= $isOpen ? 'War dibuka' : 'War ditutup' ?></b><span>Klaim hanya valid di antara waktu mulai dan berakhir (waktu server).</span></span>
                </label>
            </div>
        </section>

        <!-- 3. Jadwal -->
        <section class="wr-step" data-reveal="soft">
            <span class="n">3</span>
            <div class="body">
                <h2>Jadwal war</h2>
                <p class="hint">Kosongkan salah satunya kalau tidak ingin membatasi waktu mulai atau berakhir.</p>

                <div class="wr-times">
                    <div class="wr-field">
                        <label>Waktu mulai</label>
                        <div class="wr-dt" id="dt-start" data-ph="Pilih tanggal &amp; jam mulai">
                            <input type="hidden" name="starts_at" value="<?= e($startsVal) ?>">
                            <button type="button" class="wr-dt-btn" aria-haspopup="dialog">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                <span class="wr-dt-text"></span>
                            </button>
                            <div class="wr-dt-pop" hidden role="dialog" aria-label="Pilih waktu mulai"></div>
                        </div>
                    </div>
                    <div class="wr-field">
                        <label>Waktu berakhir</label>
                        <div class="wr-dt" id="dt-end" data-ph="Pilih tanggal &amp; jam berakhir">
                            <input type="hidden" name="ends_at" value="<?= e($endsVal) ?>">
                            <button type="button" class="wr-dt-btn" aria-haspopup="dialog">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                <span class="wr-dt-text"></span>
                            </button>
                            <div class="wr-dt-pop" hidden role="dialog" aria-label="Pilih waktu berakhir"></div>
                        </div>
                    </div>
                </div>

                <div class="wr-presets">
                    <div class="t">⚡ Atur cepat</div>
                    <div class="wr-chips">
                        <button type="button" class="wr-chip" data-start-now>Mulai sekarang</button>
                        <button type="button" class="wr-chip" data-dur="60">Durasi 1 jam</button>
                        <button type="button" class="wr-chip" data-dur="180">3 jam</button>
                        <button type="button" class="wr-chip" data-dur="1440">1 hari</button>
                        <button type="button" class="wr-chip" data-dur="4320">3 hari</button>
                        <button type="button" class="wr-chip" data-dur="10080">1 minggu</button>
                        <button type="button" class="wr-chip" data-clear-all style="color:#dc2626;">Hapus jadwal</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="wr-side">
        <div class="wr-card wr-preview" data-reveal="up">
            <h3>Ringkasan pengaturan</h3>
            <div class="status"><span class="wr-pill" id="pv-pill"><span class="dot"></span><span id="pv-status">…</span></span></div>
            <p class="note" id="pv-note"></p>
            <div class="wr-rows">
                <div class="wr-row"><span>Semester</span><b id="pv-sem">-</b></div>
                <div class="wr-row"><span>Mulai</span><b id="pv-start">Tanpa batas</b></div>
                <div class="wr-row"><span>Berakhir</span><b id="pv-end">Tanpa batas</b></div>
                <div class="wr-row"><span>Durasi</span><b id="pv-dur">-</b></div>
            </div>
            <div class="wr-error" id="pv-error" role="alert"></div>
            <button type="submit" class="btn btn-primary wr-save" id="wr-save">Simpan pengaturan</button>
        </div>
        <p class="wr-links" data-reveal="soft">
            <a href="/admin/claims">Lihat semua tiket klaim →</a><br>
            <a href="/admin/violations">Lihat pelanggaran klaim →</a>
        </p>
    </aside>
</form>

<script>
(function () {
    var MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    var MON_S  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var DAY_S  = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    var DAY_L  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var DOW    = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function fmtVal(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes()); }
    function parseVal(v) { var m = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(v || ''); return m ? new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]) : null; }
    function dateStr(d) { return DAY_S[d.getDay()] + ', ' + d.getDate() + ' ' + MON_S[d.getMonth()] + ' ' + d.getFullYear(); }
    function timeStr(d) { return pad(d.getHours()) + '.' + pad(d.getMinutes()); }
    function human(d) { return dateStr(d) + ' · ' + timeStr(d); }
    function nowRounded() { var d = new Date(); d.setSeconds(0, 0); d.setMinutes(Math.ceil(d.getMinutes() / 5) * 5); return d; }
    function span(ms) {
        var m = Math.round(ms / 60000), d = Math.floor(m / 1440), h = Math.floor((m % 1440) / 60), mm = m % 60, out = [];
        if (d) out.push(d + ' hari'); if (h) out.push(h + ' jam'); if (mm && !d) out.push(mm + ' menit');
        return out.length ? out.join(' ') : '0 menit';
    }

    var pickers = [], onChange = function () {};

    function Picker(root) {
        var input = root.querySelector('input[type=hidden]');
        var btn   = root.querySelector('.wr-dt-btn');
        var text  = root.querySelector('.wr-dt-text');
        var pop   = root.querySelector('.wr-dt-pop');
        var ph    = root.getAttribute('data-ph');
        var value = parseVal(input.value);
        var view  = value ? new Date(value.getFullYear(), value.getMonth(), 1) : new Date(new Date().getFullYear(), new Date().getMonth(), 1);
        var self  = this;

        pop.innerHTML =
            '<div class="wr-cal-head"><button type="button" class="wr-nav" data-nav="-1" aria-label="Bulan sebelumnya">‹</button><b></b><button type="button" class="wr-nav" data-nav="1" aria-label="Bulan berikutnya">›</button></div>' +
            '<div class="wr-cal"></div>' +
            '<div class="wr-time"><label>Jam (24 jam)</label><select data-h aria-label="Jam"></select><span class="colon">:</span><select data-m aria-label="Menit"></select></div>' +
            '<div class="wr-chips"><button type="button" class="wr-chip" data-t="08:00">08.00</button><button type="button" class="wr-chip" data-t="12:00">12.00</button><button type="button" class="wr-chip" data-t="16:00">16.00</button><button type="button" class="wr-chip" data-t="23:59">23.59</button></div>' +
            '<div class="wr-pop-foot"><button type="button" class="wr-link danger" data-clear>Hapus</button><button type="button" class="wr-link" data-done>Selesai</button></div>';

        var title = pop.querySelector('.wr-cal-head b'), grid = pop.querySelector('.wr-cal');
        var selH = pop.querySelector('[data-h]'), selM = pop.querySelector('[data-m]');
        for (var i = 0; i < 24; i++) selH.add(new Option(pad(i), i));
        for (var j = 0; j < 60; j++) selM.add(new Option(pad(j), j));

        function renderCal() {
            title.textContent = MONTHS[view.getMonth()] + ' ' + view.getFullYear();
            var html = DOW.map(function (d) { return '<div class="dow">' + d + '</div>'; }).join('');
            var first = new Date(view.getFullYear(), view.getMonth(), 1);
            var offset = (first.getDay() + 6) % 7;
            var days = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
            var today = new Date();
            for (var b = 0; b < offset; b++) html += '<div></div>';
            for (var d = 1; d <= days; d++) {
                var cls = 'wr-day';
                if (today.getFullYear() === view.getFullYear() && today.getMonth() === view.getMonth() && today.getDate() === d) cls += ' today';
                if (value && value.getFullYear() === view.getFullYear() && value.getMonth() === view.getMonth() && value.getDate() === d) cls += ' sel';
                html += '<button type="button" class="' + cls + '" data-d="' + d + '">' + d + '</button>';
            }
            grid.innerHTML = html;
        }
        function sync() {
            input.value = value ? fmtVal(value) : '';
            root.classList.toggle('has-value', !!value);
            text.innerHTML = value ? dateStr(value) + '<small>pukul ' + timeStr(value) + '</small>' : ph;
            if (value) { selH.value = value.getHours(); selM.value = value.getMinutes(); }
            renderCal();
        }
        function setTimeParts(h, m) {
            var base = value ? new Date(value) : new Date();
            base.setHours(h, m, 0, 0);
            value = base;
            view = new Date(value.getFullYear(), value.getMonth(), 1);
            sync(); onChange();
        }
        this.get = function () { return value; };
        this.set = function (d) {
            value = d ? new Date(d) : null;
            if (value) view = new Date(value.getFullYear(), value.getMonth(), 1);
            sync(); onChange();
        };
        this.open = function () {
            pickers.forEach(function (p) { if (p !== self) p.close(); });
            // Utamakan membuka ke ATAS supaya kalender tidak terpotong di bawah layar
            pop.classList.add('up');
            pop.hidden = false; root.classList.add('open');
            // Hanya buka ke bawah bila di atas tidak muat DAN di bawah lebih lega
            var r = pop.getBoundingClientRect(), f = root.getBoundingClientRect(), vh = window.innerHeight;
            if (r.top < 12 && (vh - f.bottom) > f.top) pop.classList.remove('up');
            // Pastikan seluruh kalender terlihat (scroll seperlunya)
            pop.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        };
        this.close = function () { pop.hidden = true; root.classList.remove('open'); };
        this.root = root;

        btn.addEventListener('click', function () { pop.hidden ? self.open() : self.close(); });
        pop.addEventListener('click', function (e) {
            var t = e.target;
            if (t.dataset.nav) { view = new Date(view.getFullYear(), view.getMonth() + (+t.dataset.nav), 1); renderCal(); }
            else if (t.dataset.d) {
                var h = value ? value.getHours() : 8, m = value ? value.getMinutes() : 0;
                value = new Date(view.getFullYear(), view.getMonth(), +t.dataset.d, h, m);
                sync(); onChange();
            }
            else if (t.dataset.t) { var p = t.dataset.t.split(':'); setTimeParts(+p[0], +p[1]); }
            else if ('clear' in t.dataset) { self.set(null); self.close(); }
            else if ('done' in t.dataset) { self.close(); }
        });
        selH.addEventListener('change', function () { setTimeParts(+selH.value, +selM.value); });
        selM.addEventListener('change', function () { setTimeParts(+selH.value, +selM.value); });
        sync();
    }

    var pStart = new Picker(document.getElementById('dt-start'));
    var pEnd   = new Picker(document.getElementById('dt-end'));
    pickers = [pStart, pEnd];

    document.addEventListener('mousedown', function (e) {
        pickers.forEach(function (p) { if (!p.root.contains(e.target)) p.close(); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') pickers.forEach(function (p) { p.close(); }); });

    /* ---------- saklar & ringkasan ---------- */
    var sw = document.getElementById('wr-switch'), openBox = document.getElementById('wr-open'), openLbl = document.getElementById('wr-open-label');
    var sem = document.getElementById('wr-semester'), saveBtn = document.getElementById('wr-save'), errBox = document.getElementById('pv-error');
    var pill = document.getElementById('pv-pill'), $ = function (id) { return document.getElementById(id); };

    function summary() {
        var s = pStart.get(), e = pEnd.get(), open = openBox.checked, now = new Date();
        sw.classList.toggle('on', open);
        openLbl.textContent = open ? 'War dibuka' : 'War ditutup';

        var label, cls, note;
        if (!open) { label = 'Ditutup'; cls = ''; note = 'Mahasiswa belum bisa mengklaim tiket. Nyalakan saklar "Buka war" untuk memulai.'; }
        else if (s && now < s) { label = 'Terjadwal'; cls = 'is-scheduled'; note = 'War akan dimulai ' + span(s - now) + ' lagi. Tombol klaim aktif otomatis saat waktunya tiba.'; }
        else if (e && now > e) { label = 'Selesai'; cls = ''; note = 'Waktu berakhir sudah lewat, jadi klaim ditutup. Ubah jadwal untuk membuka kembali.'; }
        else { label = 'Sedang berlangsung'; cls = 'is-active'; note = e ? 'Mahasiswa bisa klaim sekarang. War berakhir ' + span(e - now) + ' lagi.' : 'Mahasiswa bisa klaim sekarang. Tidak ada batas waktu berakhir.'; }
        pill.className = 'wr-pill ' + cls;
        $('pv-status').textContent = label;
        $('pv-note').textContent = note;

        var semOpt = sem.options[sem.selectedIndex];
        $('pv-sem').textContent = sem.value ? semOpt.text.replace(/\s+/g, ' ').trim().split(' · ')[0] : 'Belum dipilih';
        $('pv-start').textContent = s ? human(s) : 'Tanpa batas';
        $('pv-end').textContent = e ? human(e) : 'Tanpa batas';
        $('pv-dur').textContent = (s && e && e > s) ? span(e - s) : '-';

        var err = '';
        if (s && e && e <= s) err = 'Waktu berakhir harus setelah waktu mulai.';
        else if (open && !sem.value) err = 'Pilih semester aktif sebelum membuka war.';
        errBox.textContent = err;
        errBox.classList.toggle('show', !!err);
        saveBtn.disabled = !!err;
    }
    onChange = summary;
    openBox.addEventListener('change', summary);
    sem.addEventListener('change', summary);

    /* ---------- atur cepat ---------- */
    document.querySelector('[data-start-now]').addEventListener('click', function () { pStart.set(nowRounded()); });
    Array.prototype.forEach.call(document.querySelectorAll('[data-dur]'), function (b) {
        b.addEventListener('click', function () {
            var start = pStart.get();
            if (!start) { start = nowRounded(); pStart.set(start); }
            pEnd.set(new Date(start.getTime() + (+b.dataset.dur) * 60000));
        });
    });
    document.querySelector('[data-clear-all]').addEventListener('click', function () { pStart.set(null); pEnd.set(null); });

    document.getElementById('war-form').addEventListener('submit', function (e) { if (saveBtn.disabled) e.preventDefault(); });
    summary();
    setInterval(summary, 30000);
})();
</script>

<script>
(function () {
    var root = document.getElementById('wr-sem-dd');
    if (!root) return;
    var sel = root.querySelector('select'), btn = root.querySelector('.wr-dd-btn'),
        text = root.querySelector('.wr-dd-text'), pop = root.querySelector('.wr-dd-pop');
    var ph = root.getAttribute('data-ph') || 'Pilih';
    var items = [], act = -1;
    var CK = '<svg class="ck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function split(o) { var p = o.text.replace(/\s+/g, ' ').trim().split(' · '); return { name: p[0], year: p[1] || '' }; }

    // bangun daftar dari <option> asli
    Array.prototype.forEach.call(sel.options, function (o) {
        if (!o.value) return;
        var t = split(o), li = document.createElement('li');
        li.className = 'wr-dd-opt'; li.setAttribute('role', 'option'); li.dataset.v = o.value;
        li.innerHTML = CK + '<span>' + esc(t.name) + '</span>';
        pop.appendChild(li); items.push(li);
    });

    function paint() {
        var o = sel.options[sel.selectedIndex];
        if (sel.value && o) { var t = split(o); text.textContent = t.name; }
        else text.textContent = ph;
        root.classList.toggle('has-value', !!sel.value);
        items.forEach(function (li) { var on = li.dataset.v === sel.value; li.classList.toggle('sel', on); li.setAttribute('aria-selected', on ? 'true' : 'false'); });
    }
    function setAct(i) {
        if (!items.length) return;
        act = (i + items.length) % items.length;
        items.forEach(function (li, k) { li.classList.toggle('act', k === act); });
        items[act].scrollIntoView({ block: 'nearest' });
    }
    function open() {
        pop.classList.remove('up'); pop.hidden = false; root.classList.add('open'); btn.setAttribute('aria-expanded', 'true');
        var r = pop.getBoundingClientRect(), f = root.getBoundingClientRect(), vh = window.innerHeight;
        if (r.bottom > vh - 12 && f.top > r.height + 24 && f.top > vh - f.bottom) pop.classList.add('up');
        var cur = items.findIndex(function (li) { return li.dataset.v === sel.value; });
        setAct(cur < 0 ? 0 : cur);
    }
    function close() { pop.hidden = true; root.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); }
    function pick(li) {
        if (sel.value !== li.dataset.v) { sel.value = li.dataset.v; sel.dispatchEvent(new Event('change', { bubbles: true })); }
        paint(); close(); btn.focus();
    }

    btn.addEventListener('click', function () { pop.hidden ? open() : close(); });
    btn.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); if (pop.hidden) open(); else setAct(act + (e.key === 'ArrowDown' ? 1 : -1)); }
        else if ((e.key === 'Enter' || e.key === ' ') && !pop.hidden) { e.preventDefault(); if (items[act]) pick(items[act]); }
        else if (e.key === 'Escape') close();
        else if (e.key === 'Tab') close();
    });
    pop.addEventListener('click', function (e) { var li = e.target.closest('.wr-dd-opt'); if (li) pick(li); });
    pop.addEventListener('mousemove', function (e) { var li = e.target.closest('.wr-dd-opt'); if (li) setAct(items.indexOf(li)); });
    document.addEventListener('mousedown', function (e) { if (!root.contains(e.target)) close(); });
    sel.addEventListener('change', paint);
    paint();
})();
</script>
