<?php
use App\Helpers\ImageUploader;

$first = explode(' ', trim((string) ($user['name'] ?? 'Admin')))[0] ?: 'Admin';

$total = (int) $stats['projects'];
$taken = (int) $stats['taken'];
$free  = (int) $stats['free'];
$pct   = $total > 0 ? (int) round($taken / $total * 100) : 0;
$circ  = 2 * M_PI * 48;
$len   = $total > 0 ? $circ * $taken / $total : 0;
$studPct = (int) $stats['students'] > 0 ? (int) round($stats['with_project'] / $stats['students'] * 100) : 0;

// ---- ikon ----
$P = [
    'users'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'box'    => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/>',
    'check'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
    'ticket' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/>',
    'alert'  => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/>',
    'plus'   => '<path d="M12 5v14M5 12h14"/>',
    'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'zap'    => '<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>',
    'clock'  => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'edit'   => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/>',
    'chart'  => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
];
$ic = fn(string $k, string $cls = '') => '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($P[$k] ?? '') . '</svg>';

// ---- kartu statistik ----
$kpis = [
    ['c1' => '#024c5d', 'c2' => '#1fa0bd', 'icon' => 'users',  'value' => (int) $stats['students'],   'label' => 'Total Mahasiswa',    'sub' => (int) $stats['with_project'] . ' sudah punya proyek', 'href' => '/admin/students',   'bar' => $studPct],
    ['c1' => '#0369a1', 'c2' => '#38bdf8', 'icon' => 'box',    'value' => $total,                     'label' => 'Total Proyek',        'sub' => (int) $stats['published'] . ' dipublikasikan',          'href' => '/admin/projects',   'bar' => $total > 0 ? (int) round($stats['published'] / $total * 100) : 0],
    ['c1' => '#047857', 'c2' => '#34d399', 'icon' => 'check',  'value' => $taken,                     'label' => 'Proyek Dikerjakan',   'sub' => $free . ' masih tersedia',                              'href' => '/admin/projects',   'bar' => $pct],
    ['c1' => '#b45309', 'c2' => '#fbbf24', 'icon' => 'ticket', 'value' => (int) $stats['claims'],     'label' => 'Tiket Klaim',         'sub' => 'Lihat semua tiket',                                    'href' => '/admin/claims',     'bar' => null],
    ['c1' => '#be123c', 'c2' => '#fb7185', 'icon' => 'alert',  'value' => (int) $stats['violations'], 'label' => 'Pelanggaran',         'sub' => (int) $stats['violations'] > 0 ? 'menunggu peninjauan' : 'tidak ada antrean',  'href' => '/admin/violations', 'bar' => null, 'alert' => (int) $stats['violations'] > 0],
];

// ---- status Project War ----
$ws  = $war['settings'] ?? null;
$wst = $war['status'] ?? 'closed';
$warMap = [
    'active'    => ['Sedang dibuka', 'ok'],
    'scheduled' => ['Terjadwal',     'info'],
    'ended'     => ['Sudah berakhir', 'off'],
    'closed'    => ['Ditutup',       'off'],
];
[$warLabel, $warTone] = $warMap[$wst] ?? $warMap['closed'];
$warSem = $ws['semester_name'] ?? null;
$warWin = (!empty($ws['starts_at']) && !empty($ws['ends_at']))
    ? format_date($ws['starts_at'], 'd M H:i') . ' – ' . format_date($ws['ends_at'], 'd M H:i')
    : null;

// ---- data grafik ----
$maxCat = 1;
foreach ($byCategory as $r) { $maxCat = max($maxCat, (int) $r['total']); }
$maxSem = 1;
foreach ($bySemester as $r) { $maxSem = max($maxSem, (int) $r['total']); }
$semLabel = function (string $name): string {
    $n = preg_replace('/\D+/', '', $name);
    return $n !== '' ? 'S' . $n : $name;
};
?>
<style>
    .dsh { --c-ink: #04202c; display: grid; gap: 1.4rem; }
    .dsh [hidden] { display: none; }

    /* ================= HERO ================= */
    .dsh-hero { position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 1.6rem; align-items: center; padding: 2rem 2.2rem; border-radius: 28px; color: #fff; background: linear-gradient(120deg, #023745 0%, #037992 58%, #1fa0bd 125%); box-shadow: 0 24px 54px rgba(2,60,80,.28); isolation: isolate; }
    .dsh-hero::before { content: ""; position: absolute; inset: 0; z-index: -1; opacity: .5; background-image: radial-gradient(rgba(255,255,255,.16) 1.2px, transparent 1.4px); background-size: 22px 22px; mask-image: linear-gradient(100deg, transparent 10%, #000 90%); -webkit-mask-image: linear-gradient(100deg, transparent 10%, #000 90%); }
    .dsh-orb { position: absolute; border-radius: 50%; z-index: -1; filter: blur(2px); background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.28), rgba(255,255,255,.04) 70%); }
    .dsh-orb.o1 { width: 280px; height: 280px; right: -70px; top: -110px; }
    .dsh-orb.o2 { width: 150px; height: 150px; right: 28%; bottom: -80px; }
    .dsh-orb.o3 { width: 90px; height: 90px; left: 44%; top: -34px; }
    .dsh-pill { display: inline-flex; align-items: center; gap: .45rem; font-size: .74rem; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; padding: .35rem .8rem; border-radius: 999px; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28); backdrop-filter: blur(6px); }
    .dsh-pill i { width: 7px; height: 7px; border-radius: 50%; background: #6ee7b7; box-shadow: 0 0 0 0 rgba(110,231,183,.7); }
    .dsh-hero h1 { font-size: clamp(1.6rem, 2.6vw, 2.25rem); font-weight: 800; letter-spacing: -.8px; line-height: 1.15; margin: .85rem 0 .4rem; }
    .dsh-wave { display: inline-block; transform-origin: 70% 70%; }
    .dsh-hero p { max-width: 520px; color: rgba(255,255,255,.86); font-size: .95rem; line-height: 1.6; }

    .dsh-side { display: grid; gap: .8rem; min-width: 270px; }
    .dsh-clock { padding: 1.1rem 1.3rem; border-radius: 20px; background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(10px); }
    .dsh-clock b { display: block; font-size: 2.1rem; font-weight: 800; letter-spacing: -1px; line-height: 1; font-variant-numeric: tabular-nums; }
    .dsh-clock span { display: block; margin-top: .35rem; font-size: .8rem; color: rgba(255,255,255,.82); font-weight: 600; }
    .dsh-war { display: flex; align-items: center; gap: .85rem; padding: .9rem 1.1rem; border-radius: 20px; color: #fff; background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(10px); transition: transform .22s ease, background .2s ease; }
    .dsh-war:hover { transform: translateY(-3px); background: rgba(255,255,255,.22); }
    .dsh-war .dot { width: 38px; height: 38px; border-radius: 12px; display: grid; place-items: center; flex: none; background: rgba(255,255,255,.18); position: relative; }
    .dsh-war .dot svg { width: 19px; height: 19px; }
    .dsh-war.is-ok .dot::after { content: ""; position: absolute; right: -3px; top: -3px; width: 11px; height: 11px; border-radius: 50%; background: #34d399; border: 2px solid #0a6b80; }
    .dsh-war small { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; opacity: .8; }
    .dsh-war strong { display: block; font-size: .98rem; font-weight: 800; }
    .dsh-war em { display: block; font-style: normal; font-size: .76rem; opacity: .85; margin-top: .1rem; }

    /* ================= KPI ================= */
    .dsh-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem; }
    .dsh-kpi { --mx: 50%; --my: 0%; position: relative; overflow: hidden; display: flex; flex-direction: column; gap: .9rem; padding: 1.15rem 1.15rem 1.05rem; background: var(--card); border: 1px solid var(--border); border-radius: 22px; box-shadow: var(--shadow-sm); color: var(--text); transition: transform .28s cubic-bezier(.34,1.56,.64,1), box-shadow .28s ease, border-color .25s ease; }
    .dsh-kpi::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: linear-gradient(90deg, var(--c1), var(--c2)); }
    .dsh-kpi::after { content: ""; position: absolute; inset: 0; pointer-events: none; opacity: 0; transition: opacity .3s ease; background: radial-gradient(260px circle at var(--mx) var(--my), color-mix(in srgb, var(--c2) 22%, transparent), transparent 62%); }
    .dsh-kpi:hover { transform: translateY(-6px); box-shadow: 0 20px 40px color-mix(in srgb, var(--c1) 22%, transparent); border-color: color-mix(in srgb, var(--c2) 45%, var(--border)); }
    .dsh-kpi:hover::after { opacity: 1; }
    .dsh-kpi-top { display: flex; align-items: center; justify-content: space-between; }
    .dsh-kpi-ico { width: 46px; height: 46px; border-radius: 15px; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, var(--c1), var(--c2)); box-shadow: 0 10px 20px color-mix(in srgb, var(--c1) 35%, transparent); transition: transform .35s cubic-bezier(.34,1.56,.64,1); }
    .dsh-kpi-ico svg { width: 22px; height: 22px; }
    .dsh-kpi:hover .dsh-kpi-ico { transform: rotate(-8deg) scale(1.12); }
    .dsh-kpi-go { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; color: var(--muted); background: #f1f7f9; transition: background .2s ease, color .2s ease, transform .3s cubic-bezier(.34,1.56,.64,1); }
    .dsh-kpi-go svg { width: 15px; height: 15px; }
    .dsh-kpi:hover .dsh-kpi-go { background: var(--c1); color: #fff; transform: translateX(3px); }
    .dsh-kpi-val { display: block; font-size: 2.15rem; font-weight: 800; letter-spacing: -1.2px; line-height: 1; font-variant-numeric: tabular-nums; position: relative; z-index: 1; }
    .dsh-kpi-lbl { display: block; margin-top: .4rem; font-size: .86rem; font-weight: 800; position: relative; z-index: 1; }
    .dsh-kpi small { display: block; margin-top: .15rem; font-size: .76rem; color: var(--muted); font-weight: 600; position: relative; z-index: 1; }
    .dsh-kpi-bar { height: 6px; border-radius: 6px; background: #e8f1f5; overflow: hidden; position: relative; z-index: 1; margin-top: auto; }
    .dsh-kpi-bar i { display: block; height: 100%; width: 0; border-radius: 6px; background: linear-gradient(90deg, var(--c1), var(--c2)); transition: width 1.2s cubic-bezier(.22,1,.36,1) .3s; }
    .dsh-go .dsh-kpi-bar i { width: var(--w); }
    .dsh-kpi.is-alert .dsh-kpi-ico { animation: dshShake 3.6s ease-in-out infinite; }

    /* ================= KARTU & GRAFIK ================= */
    .dsh-card { position: relative; overflow: hidden; background: var(--card); border: 1px solid var(--border); border-radius: 24px; box-shadow: var(--shadow-sm); padding: 1.3rem 1.4rem 1.4rem; transition: box-shadow .3s ease, transform .3s ease; min-width: 0; }
    .dsh-card:hover { box-shadow: var(--shadow); }
    .dsh-ch { display: flex; align-items: flex-start; justify-content: space-between; gap: .8rem; margin-bottom: 1.1rem; }
    .dsh-ch h2 { font-size: 1.02rem; font-weight: 800; letter-spacing: -.2px; }
    .dsh-ch p { font-size: .78rem; color: var(--muted); margin-top: .15rem; }
    .dsh-more { flex: none; display: inline-flex; align-items: center; gap: .3rem; font-size: .8rem; font-weight: 800; color: var(--primary); padding: .35rem .75rem; border-radius: 999px; background: var(--primary-light); transition: background .2s ease, transform .2s ease; }
    .dsh-more svg { width: 14px; height: 14px; transition: transform .25s ease; }
    .dsh-more:hover { background: #cfe9f1; }
    .dsh-more:hover svg { transform: translateX(3px); }
    .dsh-row3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.2rem; }
    .dsh-row2 { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 1.2rem; align-items: start; }
    .dsh-stack { display: grid; gap: 1.2rem; min-width: 0; }
    .dsh-empty { padding: 1.6rem .8rem; text-align: center; font-size: .85rem; color: var(--muted); border: 2px dashed #d3e4ec; border-radius: 16px; }

    /* donat */
    .dsh-donut { display: grid; place-items: center; position: relative; margin: .2rem auto 1.1rem; width: 168px; height: 168px; }
    .dsh-donut svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .dsh-donut .trk { fill: none; stroke: #e8f1f5; stroke-width: 13; }
    .dsh-donut .ring { fill: none; stroke: url(#dshGrad); stroke-width: 13; stroke-linecap: round; stroke-dasharray: 0 400; transition: stroke-dasharray 1.6s cubic-bezier(.22,1,.36,1) .25s; filter: drop-shadow(0 4px 8px rgba(3,121,146,.3)); }
    .dsh-go .dsh-donut .ring { stroke-dasharray: var(--len) 400; }
    .dsh-donut .mid { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; }
    .dsh-donut .mid b { font-size: 2rem; font-weight: 800; letter-spacing: -1px; line-height: 1; }
    .dsh-donut .mid > span { font-size: .72rem; color: var(--muted); font-weight: 700; margin-top: .25rem; }
    .dsh-legend { display: grid; gap: .5rem; }
    .dsh-legend div { display: flex; align-items: center; gap: .6rem; padding: .55rem .75rem; border-radius: 12px; background: #f6fafc; font-size: .84rem; font-weight: 700; }
    .dsh-legend i { width: 10px; height: 10px; border-radius: 50%; flex: none; }
    .dsh-legend b { margin-left: auto; font-size: .95rem; }

    /* batang kategori */
    .dsh-bars { display: grid; gap: .95rem; }
    .dsh-bar .lbl { display: flex; justify-content: space-between; gap: .6rem; font-size: .84rem; font-weight: 700; margin-bottom: .38rem; }
    .dsh-bar .lbl span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dsh-bar .lbl b { color: var(--primary-dark); }
    .dsh-bar .trk { height: 10px; border-radius: 10px; background: #e8f1f5; overflow: hidden; }
    .dsh-bar .fill { display: block; height: 100%; width: 0; border-radius: 10px; background: linear-gradient(90deg, #024c5d, #1fa0bd); transition: width 1.1s cubic-bezier(.22,1,.36,1) var(--d, 0s); position: relative; }
    .dsh-bar .fill::after { content: ""; position: absolute; inset: 0; background: linear-gradient(100deg, transparent 30%, rgba(255,255,255,.45), transparent 70%); transform: translateX(-100%); }
    .dsh-go .dsh-bar .fill { width: var(--w); }
    .dsh-go .dsh-bar .fill::after { animation: dshSheen 2.6s ease-in-out 1.4s 1; }

    /* kolom semester */
    .dsh-cols { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: .55rem; align-items: end; height: 220px; padding-top: 1.6rem; }
    .dsh-col { height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: .45rem; position: relative; }
    .dsh-col b { font-size: .8rem; font-weight: 800; color: var(--primary-dark); opacity: 0; transition: opacity .4s ease calc(var(--i) * .08s + .9s); }
    .dsh-go .dsh-col b { opacity: 1; }
    .dsh-col .bar { width: 100%; max-width: 38px; height: 0; min-height: 0; border-radius: 12px 12px 6px 6px; background: linear-gradient(180deg, #1fa0bd, #024c5d); transition: height 1.1s cubic-bezier(.22,1,.36,1) calc(var(--i) * .08s + .2s), filter .2s ease; box-shadow: 0 8px 16px rgba(3,121,146,.22); }
    .dsh-col .bar.zero { background: #dbe8ee; box-shadow: none; }
    .dsh-go .dsh-col .bar { height: var(--h); }
    .dsh-col:hover .bar { filter: brightness(1.15); }
    .dsh-col span { font-size: .72rem; font-weight: 800; color: var(--muted); }

    /* daftar */
    .dsh-list { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: minmax(0, 1fr); gap: .25rem; }
    .dsh-item { display: flex; align-items: center; gap: .85rem; padding: .65rem .6rem; border-radius: 16px; min-width: 0; transition: background .2s ease, box-shadow .2s ease; }
    .dsh-item:hover { background: #f4fafc; box-shadow: inset 3px 0 0 var(--primary); }
    .dsh-thumb { width: 50px; height: 50px; border-radius: 15px; flex: none; overflow: hidden; background: var(--grad-soft); display: grid; place-items: center; }
    .dsh-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .dsh-thumb .thumb-icon { color: var(--primary); }
    .dsh-info { flex: 1; min-width: 0; }
    .dsh-info strong { display: block; font-size: .9rem; font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dsh-info span { display: block; font-size: .76rem; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dsh-tag { flex: none; min-width: 92px; text-align: center; white-space: nowrap; font-size: .68rem; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; padding: .22rem .65rem; border-radius: 999px; }
    .dsh-tag.ok { color: #047857; background: #d1fae5; }
    .dsh-tag.free { color: #0369a1; background: #e0f2fe; }
    .dsh-tag.draft { color: #92400e; background: #fef3c7; }
    .dsh-tag.off { color: #9f1239; background: #ffe4e6; }
    .dsh-edit { flex: none; width: 34px; height: 34px; border-radius: 11px; display: grid; place-items: center; color: var(--muted); background: #f1f7f9; transition: background .2s ease, color .2s ease, transform .25s cubic-bezier(.34,1.56,.64,1); }
    .dsh-edit svg { width: 16px; height: 16px; }
    .dsh-edit:hover { background: var(--primary); color: #fff; transform: scale(1.1) rotate(-6deg); }
    .dsh-info .dsh-code { width: fit-content; max-width: 100%; font-family: "JetBrains Mono", ui-monospace, Consolas, monospace; font-size: .72rem; font-weight: 700; color: var(--primary-dark); background: var(--primary-light); padding: .18rem .55rem; border-radius: 8px; display: inline-block; margin-bottom: .2rem; }
    .dsh-av { width: 42px; height: 42px; border-radius: 14px; flex: none; overflow: hidden; display: grid; place-items: center; font-size: .8rem; font-weight: 800; color: #fff; background: var(--grad); }
    .dsh-av img { width: 100%; height: 100%; object-fit: cover; }
    .dsh-av svg { width: 20px; height: 20px; }

    @keyframes dshSheen { to { transform: translateX(100%); } }
    @keyframes dshFloat { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-18px, 14px) scale(1.06); } }
    @keyframes dshWave { 0%,60%,100% { transform: rotate(0); } 10%,30% { transform: rotate(14deg); } 20%,40% { transform: rotate(-8deg); } 50% { transform: rotate(10deg); } }
    @keyframes dshPing { 0% { box-shadow: 0 0 0 0 rgba(110,231,183,.7); } 70%,100% { box-shadow: 0 0 0 9px rgba(110,231,183,0); } }
    @keyframes dshShake { 0%,86%,100% { transform: rotate(0); } 90% { transform: rotate(-12deg); } 94% { transform: rotate(10deg); } 97% { transform: rotate(-6deg); } }
    @media (prefers-reduced-motion: no-preference) {
        .dsh-orb.o1 { animation: dshFloat 11s ease-in-out infinite; }
        .dsh-orb.o2 { animation: dshFloat 9s ease-in-out -3s infinite reverse; }
        .dsh-orb.o3 { animation: dshFloat 13s ease-in-out -6s infinite; }
        .dsh-wave { animation: dshWave 2.8s ease-in-out 1.2s 2; }
        .dsh-pill i { animation: dshPing 1.9s ease-out infinite; }
    }
    @media (prefers-reduced-motion: reduce) {
        .dsh-bar .fill, .dsh-kpi-bar i, .dsh-donut .ring, .dsh-col .bar { transition: none; }
    }

    @media (max-width: 1280px) {
        .dsh-row2 { grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); }
        .dsh-tag { min-width: 0; }
        .dsh-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dsh-row3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dsh-row3 > :last-child { grid-column: 1 / -1; }
    }
    @media (max-width: 1000px) {
        .dsh-hero { grid-template-columns: 1fr; }
        .dsh-side { grid-template-columns: 1fr 1fr; min-width: 0; }
        .dsh-row2 { grid-template-columns: 1fr; }
    }
    @media (max-width: 680px) {
        .dsh-hero { padding: 1.5rem 1.25rem; border-radius: 22px; }
        .dsh-side { grid-template-columns: 1fr; }
        .dsh-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dsh-row3 { grid-template-columns: 1fr; }
        .dsh-tag { display: none; }
        .dsh-cols { gap: .3rem; }
    }
</style>

<div class="dsh">

    <!-- ================= HERO ================= -->
    <section class="dsh-hero" data-reveal="zoom">
        <span class="dsh-orb o1"></span><span class="dsh-orb o2"></span><span class="dsh-orb o3"></span>
        <div>
            <span class="dsh-pill"><i></i> Panel Admin</span>
            <h1><span id="dsh-greet">Halo</span>, <?= e($first) ?> <span class="dsh-wave">👋</span></h1>
            <p>Pantau proyek, mahasiswa, dan jalannya Project War dalam satu tampilan. Semua yang perlu perhatianmu hari ini ada di bawah.</p>
        </div>
        <div class="dsh-side">
            <div class="dsh-clock">
                <b id="dsh-time">--:--</b>
                <span id="dsh-date"><?= e(date('d M Y')) ?></span>
            </div>
            <a href="/admin/war" class="dsh-war is-<?= e($warTone) ?>">
                <span class="dot"><?= $ic('zap') ?></span>
                <span>
                    <small>Project War</small>
                    <strong><?= e($warLabel) ?></strong>
                    <em><?= $warSem ? 'Untuk ' . e($warSem) : 'Belum ada semester dipilih' ?><?= $warWin ? ' · ' . e($warWin) : '' ?></em>
                </span>
            </a>
        </div>
    </section>

    <!-- ================= KPI ================= -->
    <section class="dsh-kpis" data-watch>
        <?php foreach ($kpis as $k): ?>
        <a href="<?= e($k['href']) ?>" class="dsh-kpi<?= !empty($k['alert']) ? ' is-alert' : '' ?>" style="--c1: <?= e($k['c1']) ?>; --c2: <?= e($k['c2']) ?>;" data-reveal="zoom">
            <span class="dsh-kpi-top">
                <span class="dsh-kpi-ico"><?= $ic($k['icon']) ?></span>
                <span class="dsh-kpi-go"><?= $ic('arrow') ?></span>
            </span>
            <span>
                <b class="dsh-kpi-val" data-count="<?= (int) $k['value'] ?>"><?= (int) $k['value'] ?></b>
                <span class="dsh-kpi-lbl"><?= e($k['label']) ?></span>
                <small><?= e($k['sub']) ?></small>
            </span>
            <?php if ($k['bar'] !== null): ?>
            <span class="dsh-kpi-bar" aria-hidden="true"><i style="--w: <?= max(0, min(100, (int) $k['bar'])) ?>%"></i></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </section>

    <!-- ================= GRAFIK ================= -->
    <section class="dsh-row3">
        <div class="dsh-card" data-reveal="up" data-watch>
            <div class="dsh-ch"><div><h2>Status Proyek</h2><p>Sudah dikerjakan vs masih tersedia</p></div></div>
            <?php if ($total > 0): ?>
            <div class="dsh-donut" style="--len: <?= number_format($len, 2, '.', '') ?>;">
                <svg viewBox="0 0 120 120" aria-hidden="true">
                    <defs><linearGradient id="dshGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#024c5d"/><stop offset="100%" stop-color="#29c1dd"/></linearGradient></defs>
                    <circle class="trk" cx="60" cy="60" r="48"/>
                    <circle class="ring" cx="60" cy="60" r="48"/>
                </svg>
                <div class="mid"><b><span data-count="<?= $pct ?>"><?= $pct ?></span>%</b><span>dikerjakan</span></div>
            </div>
            <div class="dsh-legend">
                <div><i style="background:#037992"></i> Dikerjakan <b><?= $taken ?></b></div>
                <div><i style="background:#cfe0e8"></i> Tersedia <b><?= $free ?></b></div>
            </div>
            <?php else: ?>
            <div class="dsh-empty">Belum ada proyek. Tambahkan proyek pertama untuk melihat statistiknya.</div>
            <?php endif; ?>
        </div>

        <div class="dsh-card" data-reveal="up" data-watch>
            <div class="dsh-ch"><div><h2>Proyek per Kategori</h2><p>Kategori dengan proyek terbanyak</p></div></div>
            <?php if (empty($byCategory)): ?>
            <div class="dsh-empty">Belum ada data kategori.</div>
            <?php else: ?>
            <div class="dsh-bars">
                <?php foreach ($byCategory as $i => $r): $w = round((int) $r['total'] / $maxCat * 100); ?>
                <div class="dsh-bar">
                    <div class="lbl"><span><?= e($r['name']) ?></span><b><?= (int) $r['total'] ?></b></div>
                    <div class="trk"><span class="fill" style="--w: <?= $w ?>%; --d: <?= number_format(.15 + $i * .1, 2, '.', '') ?>s;"></span></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="dsh-card" data-reveal="up" data-watch>
            <div class="dsh-ch"><div><h2>Proyek per Semester</h2><p>Sebaran proyek di tiap semester</p></div></div>
            <?php if (empty($bySemester)): ?>
            <div class="dsh-empty">Belum ada data semester.</div>
            <?php else: ?>
            <div class="dsh-cols">
                <?php foreach (array_slice($bySemester, 0, 8) as $i => $r):
                    $n = (int) $r['total'];
                    $h = $n > 0 ? max(10, round($n / $maxSem * 100)) : 4;
                ?>
                <div class="dsh-col" style="--i: <?= $i ?>;" title="<?= e($r['name']) ?>: <?= $n ?> proyek">
                    <b><?= $n ?></b>
                    <div class="bar<?= $n === 0 ? ' zero' : '' ?>" style="--h: <?= $h ?>%;"></div>
                    <span><?= e($semLabel((string) $r['name'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================= DAFTAR ================= -->
    <section class="dsh-row2">
        <div class="dsh-card" data-reveal="up">
            <div class="dsh-ch">
                <div><h2>Proyek Terbaru</h2><p>Enam proyek yang terakhir ditambahkan</p></div>
                <a href="/admin/projects" class="dsh-more">Lihat semua <?= $ic('arrow') ?></a>
            </div>
            <?php if (empty($recentProjects)): ?>
            <div class="dsh-empty">Belum ada proyek.</div>
            <?php else: ?>
            <ul class="dsh-list">
                <?php foreach ($recentProjects as $p):
                    $isTaken = (int) ($p['member_count'] ?? 0) > 0;
                    $draft   = isset($p['is_published']) && (int) $p['is_published'] === 0;
                ?>
                <li class="dsh-item">
                    <div class="mini-thumb dsh-thumb"><?= project_thumb($p + ['category_icon' => null]) ?></div>
                    <div class="dsh-info">
                        <strong><?= e($p['title']) ?></strong>
                        <span><?= e($p['category_name'] ?? 'Tanpa kategori') ?> · <?= e($p['semester_name'] ?? '-') ?> · <?= format_date($p['created_at']) ?><?php if ($isTaken && !empty($p['leader_name'])): ?> · Ketua: <?= e($p['leader_name']) ?><?php endif; ?></span>
                    </div>
                    <?php if ($draft): ?>
                        <span class="dsh-tag draft">Draft</span>
                    <?php elseif ($isTaken): ?>
                        <span class="dsh-tag ok">Dikerjakan</span>
                    <?php else: ?>
                        <span class="dsh-tag free">Tersedia</span>
                    <?php endif; ?>
                    <a href="/admin/projects/<?= (int) $p['id'] ?>/edit" class="dsh-edit" title="Edit proyek" aria-label="Edit <?= e($p['title']) ?>"><?= $ic('edit') ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <div class="dsh-stack">
            <div class="dsh-card" data-reveal="up">
                <div class="dsh-ch">
                    <div><h2>Tiket Klaim Terbaru</h2><p>Klaim proyek oleh mahasiswa</p></div>
                    <a href="/admin/claims" class="dsh-more">Semua <?= $ic('arrow') ?></a>
                </div>
                <?php if (empty($recentClaims)): ?>
                <div class="dsh-empty">Belum ada tiket klaim.</div>
                <?php else: ?>
                <ul class="dsh-list">
                    <?php foreach ($recentClaims as $c): $revoked = ($c['status'] ?? '') === 'revoked'; ?>
                    <li class="dsh-item">
                        <span class="dsh-av" style="background: linear-gradient(135deg,#b45309,#fbbf24);"><?= $ic('ticket') ?></span>
                        <div class="dsh-info">
                            <span class="dsh-code"><?= e($c['ticket_code']) ?></span>
                            <strong><?= e($c['student_name']) ?></strong>
                            <span><?= e($c['project_title']) ?> · <?= format_date($c['claimed_at'], 'd M H:i') ?></span>
                        </div>
                        <span class="dsh-tag <?= $revoked ? 'off' : 'ok' ?>"><?= $revoked ? 'Dicabut' : 'Terklaim' ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <div class="dsh-card" data-reveal="up">
                <div class="dsh-ch">
                    <div><h2>Mahasiswa Terbaru</h2><p>Akun yang baru mendaftar</p></div>
                    <a href="/admin/students" class="dsh-more">Semua <?= $ic('arrow') ?></a>
                </div>
                <?php if (empty($recentStudents)): ?>
                <div class="dsh-empty">Belum ada mahasiswa.</div>
                <?php else: ?>
                <ul class="dsh-list">
                    <?php foreach ($recentStudents as $s):
                        $avatar = ImageUploader::avatarUrl($s['avatar'] ?? null);
                        $meta = trim((string) ($s['nim'] ?? '') . (!empty($s['class']) ? ' · ' . $s['class'] : ''));
                    ?>
                    <li class="dsh-item">
                        <span class="dsh-av"><?php if ($avatar): ?><img src="<?= e($avatar) ?>" alt="" loading="lazy"><?php else: ?><?= e(initials($s['name'])) ?><?php endif; ?></span>
                        <div class="dsh-info">
                            <strong><?= e($s['name']) ?></strong>
                            <span><?= e($meta !== '' ? $meta : '-') ?></span>
                        </div>
                        <span class="dsh-tag <?= (int) $s['has_project'] > 0 ? 'ok' : 'free' ?>"><?= (int) $s['has_project'] > 0 ? 'Punya proyek' : 'Belum ada' ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };

    /* ---------- Sapaan, jam, dan tanggal (waktu perangkat) ---------- */
    var greet = $('dsh-greet'), timeEl = $('dsh-time'), dateEl = $('dsh-date');
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var d = new Date(), h = d.getHours();
        if (greet) greet.textContent = h < 4 ? 'Selamat malam' : h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 19 ? 'Selamat sore' : 'Selamat malam';
        if (timeEl) timeEl.textContent = pad(h) + ':' + pad(d.getMinutes());
        if (dateEl) {
            try { dateEl.textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); } catch (e) {}
        }
    }
    tick();
    setInterval(tick, 20000);

    /* ---------- Grafik & bar baru bergerak saat terlihat ---------- */
    var watch = document.querySelectorAll('[data-watch]');
    function go(el) { el.classList.add('dsh-go'); }
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { go(e.target); io.unobserve(e.target); } });
        }, { threshold: 0.2 });
        watch.forEach(function (el) { io.observe(el); });
        setTimeout(function () {   // pengaman bila observer tidak terpicu
            watch.forEach(function (el) {
                var r = el.getBoundingClientRect();
                if (r.top < window.innerHeight) go(el);
            });
        }, 1500);
    } else {
        watch.forEach(go);
    }

    /* ---------- Sorotan cahaya mengikuti kursor di kartu statistik ---------- */
    var fine = window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (fine && !reduce) {
        document.querySelectorAll('.dsh-kpi').forEach(function (card) {
            card.addEventListener('pointermove', function (e) {
                var r = card.getBoundingClientRect();
                card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                card.style.setProperty('--my', (e.clientY - r.top) + 'px');
            });
        });
    }
})();
</script>
