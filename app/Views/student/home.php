<?php
/**
 * Variabel di bawah dikirim dari controller (via extract/render).
 * Docblock ini hanya untuk editor (Intelephense) agar tidak menampilkan "Undefined variable".
 *
 * @var array      $categories
 * @var array      $semesters
 * @var array      $latestProjects
 * @var array      $popularProjects
 * @var array|null $war
 * @var string|null $warStatus
 * @var bool|null  $warActive
 * @var int|null   $availableCount
 * @var array|null $userClaim
 */
$totalProjects = 0; foreach ($categories as $c) { $totalProjects += (int) $c['total']; }
?>
<div class="hero">
    <img src="<?= asset('images/logo-jti.png') ?>" alt="JTI" class="hero-logo">
    <h1>Karya Keren Lahir<br>dari Mahasiswa TIF</h1>
    <p>Cari inspirasi, intip karya teman-temanmu, lalu amankan proyek incaranmu sebelum keduluan!</p>
    <form class="hero-search" method="GET" action="/projects">
        <input type="text" name="q" placeholder="Cari proyek, teknologi, atau ide seru…" aria-label="Cari proyek">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>
    <div class="hero-stats">
        <div><strong><?= (int) $totalProjects ?></strong>Proyek</div>
        <div><strong><?= count($categories) ?></strong>Kategori</div>
        <div><strong><?= count($semesters) ?></strong>Semester</div>
    </div>
</div>


<!-- Project War / Klaim Tiket -->
<?php
$ws       = $warStatus ?? 'closed';
$hasClaim = !empty($userClaim);
$isActive = !empty($warActive);

if ($hasClaim) {
    $headline = 'Tiketmu sudah aman! 🎉';
    $subline  = 'Kamu sudah mengamankan satu proyek untuk semester ini. Selamat mengerjakan!';
} elseif ($isActive) {
    $headline = 'Project War sedang berlangsung!';
    $subline  = 'Pilih satu proyek incaranmu dan klaim sebelum diambil teman lain.';
} elseif ($ws === 'scheduled') {
    $headline = 'Project War segera dimulai';
    $subline  = 'Siapkan dulu proyek incaranmu. Tombol klaim aktif begitu war dibuka.';
} elseif ($ws === 'ended') {
    $headline = 'Project War telah berakhir';
    $subline  = 'Terima kasih sudah ikut! Pantau terus, periode berikutnya akan dibuka admin.';
} else {
    $headline = 'Project War belum dibuka';
    $subline  = 'Admin akan membuka periode war. Sambil menunggu, intip dulu karya-karya di bawah.';
}

$statusMap = [
    'active'    => ['Sedang berlangsung', 'is-active'],
    'scheduled' => ['Terjadwal', 'is-scheduled'],
    'ended'     => ['Selesai', 'is-closed'],
    'closed'    => ['Belum dibuka', 'is-closed'],
];
[$statusLabel, $statusCls] = $statusMap[$ws] ?? $statusMap['closed'];

$semesterText = ($war['semester_name'] ?? null)
    ? $war['semester_name'] . (!empty($war['academic_year']) ? ' · ' . $war['academic_year'] : '')
    : 'Belum diatur';
?>
<style>
    .war { margin-bottom: 2rem; scroll-margin-top: 96px; }
    .war-hero {
        position: relative; overflow: hidden; border-radius: var(--radius-lg); color: #fff;
        padding: 1.8rem 2rem; display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 1.5rem; align-items: center;
        background: linear-gradient(135deg, #03435a 0%, #037992 62%, #0aa5bf 100%);
        box-shadow: 0 18px 40px rgba(3,121,146,.28);
    }
    .war-hero::before, .war-hero::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.09); pointer-events: none; }
    .war-hero::before { width: 260px; height: 260px; right: -70px; top: -110px; }
    .war-hero::after { width: 130px; height: 130px; left: 38%; bottom: -70px; }
    .war-hero > * { position: relative; z-index: 1; }
    .war-tag { display: inline-flex; align-items: center; gap: .55rem; font-size: .72rem; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; }
    .war-tag .pill { display: inline-flex; align-items: center; gap: .4rem; padding: .28rem .7rem; border-radius: 999px; background: rgba(255,255,255,.16); letter-spacing: .3px; text-transform: none; font-size: .78rem; }
    .war-tag .dot { width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; }
    .war-tag .is-active .dot, .war-tag .pill.is-active .dot { background: #34d399; box-shadow: 0 0 0 0 rgba(52,211,153,.7); animation: warPulse 1.6s infinite; }
    .war-tag .pill.is-scheduled .dot { background: #fbbf24; }
    @keyframes warPulse { 70% { box-shadow: 0 0 0 9px rgba(52,211,153,0); } 100% { box-shadow: 0 0 0 0 rgba(52,211,153,0); } }
    .war-hero h2 { font-size: 1.7rem; font-weight: 800; letter-spacing: -.5px; line-height: 1.2; margin: .8rem 0 .5rem; }
    .war-hero .war-sub { color: rgba(255,255,255,.85); font-size: .95rem; max-width: 46ch; }

    .war-actions { display: flex; flex-wrap: wrap; gap: .7rem; align-items: center; margin-top: 1.3rem; }
    .war-cta {
        display: inline-flex; align-items: center; gap: .55rem; padding: .85rem 1.5rem; border-radius: 14px; border: 0; cursor: pointer;
        font: inherit; font-weight: 800; font-size: 1rem; text-decoration: none; color: #03435a; background: #fff;
        box-shadow: 0 10px 24px rgba(2,37,51,.28); transition: transform .15s ease, box-shadow .15s ease;
    }
    .war-cta:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(2,37,51,.34); }
    .war-cta.is-live { background: #fde047; color: #3b2f00; animation: warCta 2s ease-in-out infinite; }
    @keyframes warCta { 50% { box-shadow: 0 10px 24px rgba(2,37,51,.28), 0 0 0 8px rgba(253,224,71,.28); } }
    .war-cta[disabled] { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; animation: none; }
    .war-cta.is-done { cursor: default; background: rgba(255,255,255,.18); color: #fff; box-shadow: none; }
    .war-cta.is-done:hover { transform: none; }
    .war-link { color: #fff; font-weight: 700; font-size: .9rem; text-decoration: underline; text-underline-offset: 3px; opacity: .92; }
    .war-note { font-size: .82rem; color: rgba(255,255,255,.8); }

    .war-timer { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22); border-radius: 18px; padding: 1.1rem 1.2rem; backdrop-filter: blur(4px); }
    .war-timer[hidden] { display: none; }
    .war-timer .t-label { font-size: .78rem; font-weight: 700; letter-spacing: .4px; opacity: .9; text-align: center; margin-bottom: .75rem; }
    .war-timer .t-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; }
    .war-timer .t-cell { background: rgba(2,37,51,.35); border-radius: 12px; padding: .65rem .2rem; text-align: center; }
    .war-timer .t-cell b { display: block; font-size: 1.6rem; font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .war-timer .t-cell span { font-size: .68rem; font-weight: 600; opacity: .8; }
    .war-timer .t-foot { font-size: .7rem; opacity: .7; text-align: center; margin-top: .65rem; }
    .war-closed-art { display: grid; place-items: center; text-align: center; opacity: .95; }
    .war-closed-art .big { font-size: 3.6rem; line-height: 1; }

    .war-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin: 1rem 0; }
    .war-fact { display: flex; align-items: center; gap: .9rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.15rem; box-shadow: var(--shadow-sm); }
    .war-fact .f-ico { width: 44px; height: 44px; border-radius: 13px; flex: none; display: grid; place-items: center; background: var(--primary-light); color: var(--primary); }
    .war-fact .f-ico svg { width: 22px; height: 22px; }
    .war-fact .f-label { font-size: .75rem; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: var(--muted); }
    .war-fact .f-value { font-size: 1.02rem; font-weight: 800; margin-top: .1rem; }
    .war-fact .f-value small { display: block; font-size: .76rem; font-weight: 600; color: var(--muted); margin-top: .1rem; }
    .war-fact .f-value.big { font-size: 1.6rem; color: var(--primary-dark); line-height: 1.1; }

    .war-steps-title { font-size: 1.05rem; font-weight: 800; margin: 1.6rem 0 .9rem; }
    .war-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; counter-reset: step; }
    .war-step { position: relative; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.2rem 1.2rem 1.2rem 1.2rem; box-shadow: var(--shadow-sm); }
    .war-step .n { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; color: #fff; background: var(--grad, #037992); margin-bottom: .7rem; }
    .war-step h3 { font-size: .98rem; font-weight: 800; margin-bottom: .25rem; }
    .war-step p { font-size: .84rem; color: var(--muted); line-height: 1.5; }


    /* Aturan main Project War */
    .war-rules { margin-top: 1.6rem; }
    .wr-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem 1rem; margin-bottom: .9rem; }
    .wr-head h3 { display: flex; align-items: center; gap: .6rem; font-size: 1.05rem; font-weight: 800; margin: 0; }
    .wr-head h3 svg { width: 22px; height: 22px; color: var(--primary); }
    .wr-head span { font-size: .8rem; font-weight: 700; color: var(--muted); }
    .wr-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 1rem; }
    .wr { position: relative; overflow: hidden; grid-column: span 2; display: flex; flex-direction: column; gap: .35rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.15rem 1.2rem 1.2rem; box-shadow: var(--shadow-sm); transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    .wr:nth-child(n+4) { grid-column: span 3; }
    .wr:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); border-color: rgba(3,121,146,.35); }
    .wr-no { position: absolute; top: .55rem; right: 1rem; font-size: 2.4rem; font-weight: 800; line-height: 1; letter-spacing: -1px; color: var(--primary); opacity: .08; pointer-events: none; }
    .wr-ico { width: 44px; height: 44px; border-radius: 14px; display: grid; place-items: center; margin-bottom: .45rem; background: var(--primary-light); color: var(--primary); }
    .wr-ico svg { width: 22px; height: 22px; }
    .wr h4 { font-size: .98rem; font-weight: 800; margin: 0; line-height: 1.3; }
    .wr p { font-size: .86rem; color: var(--muted); line-height: 1.55; margin: 0; }
    .wr p strong { color: var(--text); }
    .wr.is-warn { border-color: #fde7b0; background: linear-gradient(180deg, #fffdf6, #fff 60%); }
    .wr.is-warn .wr-ico { background: #fef3c7; color: #b45309; }
    .wr.is-warn .wr-no { color: #b45309; }
    .wr-tag { align-self: flex-start; margin-top: .4rem; font-size: .68rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; padding: .2rem .6rem; border-radius: 999px; background: #fef3c7; color: #92400e; }

    /* Tombol "Lihat Tiketku" */
    .war-cta.is-ticket { background: #fff; color: #03435a; }
    .war-cta.is-ticket svg { width: 20px; height: 20px; flex: none; }
    .war-cta.is-ticket .arr { transition: transform .2s ease; }
    .war-cta.is-ticket:hover .arr { transform: translateX(4px); }

    /* Kartu tiket mini di hero */
    .war-ticket { position: relative; display: block; text-decoration: none; color: var(--text); background: #fff; border-radius: 20px; padding: 1.15rem 1.3rem 1.05rem; max-width: 360px; width: 100%; justify-self: center; rotate: -2deg; box-shadow: 0 22px 44px rgba(2,37,51,.35); transition: rotate .3s ease, transform .3s ease, box-shadow .3s ease; }
    .war-ticket:hover { rotate: 0deg; transform: translateY(-4px); box-shadow: 0 28px 54px rgba(2,37,51,.42); }
    .war-ticket::before, .war-ticket::after { content: ""; position: absolute; top: 61%; width: 24px; height: 24px; border-radius: 50%; background: #03688a; translate: 0 -50%; }
    .war-ticket::before { left: -12px; }
    .war-ticket::after  { right: -12px; }
    .wt-top { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .wt-label { font-size: .7rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--primary); }
    .wt-ok { font-size: .72rem; font-weight: 800; color: #065f46; background: #d1fae5; padding: .2rem .6rem; border-radius: 999px; }
    .wt-title { font-size: 1.15rem; font-weight: 800; line-height: 1.3; letter-spacing: -.2px; margin-top: .7rem; }
    .wt-sem { font-size: .82rem; color: var(--muted); font-weight: 600; margin-top: .2rem; }
    .wt-cut { border-top: 2px dashed var(--border); margin: 1rem -.3rem .85rem; }
    .wt-bottom { display: flex; align-items: flex-end; justify-content: space-between; gap: .75rem; }
    .wt-bottom small { display: block; font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
    .wt-bottom code { display: block; font-size: 1.02rem; font-weight: 800; letter-spacing: .06em; color: var(--primary-dark); margin-top: .1rem; word-break: break-all; }
    .wt-open { flex: none; font-size: .82rem; font-weight: 800; color: var(--primary); }

    /* Jelajahi per semester */
    .sem-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .sem-card { position: relative; display: flex; align-items: center; gap: .95rem; padding: 1rem 1.1rem; text-decoration: none; color: var(--text); background: var(--card); border: 1.5px solid var(--border); border-radius: 18px; box-shadow: var(--shadow-sm); transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    .sem-card:hover { transform: translateY(-4px); border-color: var(--primary); box-shadow: var(--shadow-hover); }
    .sem-num { flex: none; width: 54px; height: 54px; border-radius: 16px; display: grid; place-items: center; font-size: 1.5rem; font-weight: 800; color: #fff; background: var(--grad); box-shadow: 0 8px 18px rgba(3,121,146,.3); }
    .sem-info { flex: 1; min-width: 0; }
    .sem-info strong { display: flex; align-items: center; gap: .4rem; font-size: 1rem; font-weight: 800; line-height: 1.2; }
    .sem-info small { display: block; font-size: .82rem; color: var(--muted); font-weight: 600; margin-top: .15rem; }
    .sem-info small b { color: var(--primary-dark); font-weight: 800; font-size: .95rem; }
    .sem-bar { display: block; height: 6px; border-radius: 999px; background: var(--primary-light); margin-top: .55rem; overflow: hidden; }
    .sem-bar i { display: block; height: 100%; border-radius: inherit; background: var(--grad); }
    .sem-go { flex: none; width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; color: var(--primary); background: var(--primary-light); font-weight: 800; opacity: 0; translate: -6px 0; transition: all .25s ease; }
    .sem-card:hover .sem-go { opacity: 1; translate: 0 0; }
    .sem-card.is-empty { opacity: .6; }
    .sem-card.is-empty .sem-num { background: #cbd9df; box-shadow: none; }

    @media (max-width: 860px) {
        .wr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .wr, .wr:nth-child(n+4) { grid-column: auto; }
        .wr:last-child { grid-column: 1 / -1; }
        .war-ticket { rotate: 0deg; }
        .war-hero { grid-template-columns: 1fr; padding: 1.5rem; }
        .war-facts, .war-steps { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .wr-grid { grid-template-columns: 1fr; }
        .wr:last-child { grid-column: auto; }
    }
    @media (prefers-reduced-motion: reduce) { .wr { transition: none; } .war-cta.is-live, .war-tag .dot { animation: none !important; } }
</style>

<section class="war" id="klaim-tiket">
    <div class="war-hero">
        <div>
            <div class="war-tag">
                <span>Project War</span>
                <span class="pill <?= e($statusCls) ?>"><span class="dot"></span><?= e($statusLabel) ?></span>
            </div>
            <h2><?= e($headline) ?></h2>
            <p class="war-sub"><?= e($subline) ?></p>

            <div class="war-actions">
                <?php if ($hasClaim): ?>
                    <a href="/claims/<?= (int) $userClaim['id'] ?>" class="war-cta is-ticket">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>
                        Lihat Tiketku
                        <svg class="arr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <span class="war-note">✓ Diklaim <?= e(format_datetime($userClaim['claimed_at'] ?? null)) ?></span>
                <?php elseif ($isActive): ?>
                    <a href="/claims/select" class="war-cta is-live" id="btn-klaim-sekarang">🎯 Klaim Tiketmu Sekarang!</a>
                <?php else: ?>
                    <button type="button" class="war-cta" disabled>🎯 Klaim Tiket Sekarang</button>
                    <span class="war-note">Aktif saat Project War dibuka admin.</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasClaim): ?>
        <a href="/claims/<?= (int) $userClaim['id'] ?>" class="war-ticket" aria-label="Buka tiket proyek <?= e($userClaim['project_title'] ?? '') ?>">
            <div class="wt-top">
                <span class="wt-label">Tiket Proyek</span>
                <span class="wt-ok">✓ Aman</span>
            </div>
            <div class="wt-title"><?= e($userClaim['project_title'] ?? '-') ?></div>
            <div class="wt-sem"><?= e($userClaim['semester_name'] ?? '') ?></div>
            <div class="wt-cut"></div>
            <div class="wt-bottom">
                <div>
                    <small>Kode tiket</small>
                    <code><?= e($userClaim['ticket_code'] ?? '-') ?></code>
                </div>
                <span class="wt-open">Buka →</span>
            </div>
        </a>
        <?php elseif (!empty($war['starts_at']) || !empty($war['ends_at'])): ?>
        <div class="war-timer" id="war-countdown"
             data-starts="<?= e($war['starts_at'] ?? '') ?>" data-ends="<?= e($war['ends_at'] ?? '') ?>" data-status="<?= e($ws) ?>" hidden>
            <div class="t-label" id="war-cd-label"></div>
            <div class="t-grid">
                <div class="t-cell"><b id="war-d">00</b><span>Hari</span></div>
                <div class="t-cell"><b id="war-h">00</b><span>Jam</span></div>
                <div class="t-cell"><b id="war-m">00</b><span>Menit</span></div>
                <div class="t-cell"><b id="war-s">00</b><span>Detik</span></div>
            </div>
            <div class="t-foot">Perkiraan tampilan, validasi tetap dilakukan server.</div>
        </div>
        <?php else: ?>
        <div class="war-closed-art">
            <div class="big"><?= $hasClaim ? '🏆' : ($isActive ? '🔥' : '🎯') ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="war-facts">
        <div class="war-fact">
            <span class="f-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg></span>
            <div>
                <div class="f-label">Semester aktif</div>
                <div class="f-value"><?= e($semesterText) ?></div>
            </div>
        </div>
        <div class="war-fact">
            <span class="f-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
            <div>
                <div class="f-label">Jadwal war</div>
                <div class="f-value">
                    <?php if (!empty($war['starts_at']) || !empty($war['ends_at'])): ?>
                        <?php if (!empty($war['starts_at'])): ?><small style="margin:0;">Mulai</small><?= e(format_datetime($war['starts_at'])) ?><?php endif; ?>
                        <?php if (!empty($war['ends_at'])): ?><small>Berakhir <?= e(format_datetime($war['ends_at'])) ?></small><?php endif; ?>
                    <?php else: ?>
                        Belum dijadwalkan
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="war-fact">
            <span class="f-ico"><?= icon('folder') ?></span>
            <div>
                <div class="f-label">Proyek tersedia</div>
                <div class="f-value big"><?= (int) ($availableCount ?? 0) ?></div>
            </div>
        </div>
    </div>

    <h3 class="war-steps-title">Cara ikut Project War</h3>
    <div class="war-steps">
        <div class="war-step">
            <div class="n">1</div>
            <h3>Pantau status war</h3>
            <p>Lihat status di atas. Tombol klaim aktif saat admin membuka periode war.</p>
        </div>
        <div class="war-step">
            <div class="n">2</div>
            <h3>Klaim &amp; pilih proyek</h3>
            <p>Klik tombol klaim, lalu pilih satu proyek yang masih tersedia.</p>
        </div>
        <div class="war-step">
            <div class="n">3</div>
            <h3>Tiket jadi milikmu</h3>
            <p>Tiketmu tersimpan di halaman Profil. Setelah itu, proyek siap kamu kerjakan.</p>
        </div>
    </div>

    <?php
    $rules = [
        ['bolt',   'Satu proyek, satu pemilik', 'Siapa cepat, dia dapat! Proyek yang sudah diklaim tidak bisa diambil mahasiswa lain.', '', ''],
        ['ticket', 'Satu proyek per semester',  'Setiap mahasiswa hanya bisa mengklaim <strong>satu proyek per semester</strong>, jadi pilih yang paling kamu suka.', '', ''],
        ['ban',    'Klaim kedua ditolak',       'Mencoba klaim kedua di semester yang sama akan ditolak dan dicatat sebagai pelanggaran.', 'is-warn', 'Dicatat sebagai pelanggaran'],
        ['shield', 'Sanksi lewat peninjauan',   'Pengurangan nilai baru diberlakukan setelah ditinjau dan dikonfirmasi admin.', 'is-warn', ''],
        ['server', 'Server yang menentukan',    'Waktu dan validasi ditentukan server, bukan jam di perangkatmu, jadi tidak bisa diakali.', '', ''],
    ];
    $ruleIcons = [
        'bolt'   => '<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>',
        'ticket' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2M13 17v2M13 11v2"/>',
        'ban'    => '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'server' => '<rect x="2" y="3" width="20" height="8" rx="2"/><rect x="2" y="13" width="20" height="8" rx="2"/><path d="M6 7h.01M6 17h.01"/>',
    ];
    ?>
    <div class="war-rules">
        <div class="wr-head">
            <h3>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Aturan main Project War
            </h3>
            <span>Baca sebelum klaim ya!</span>
        </div>
        <div class="wr-grid">
            <?php foreach ($rules as $i => [$ic, $ttl, $txt, $cls, $tag]): ?>
            <div class="wr <?= e($cls) ?>">
                <span class="wr-no"><?= sprintf('%02d', $i + 1) ?></span>
                <span class="wr-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $ruleIcons[$ic] ?></svg></span>
                <h4><?= e($ttl) ?></h4>
                <p><?= $txt ?></p>
                <?php if ($tag): ?><span class="wr-tag"><?= e($tag) ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($semesters)):
    $maxSem   = max(1, ...array_map(fn($x) => (int) $x['total'], $semesters));
?>
<div class="section-title">
    <span>🎓 Jelajahi per Semester</span>
    <a href="/projects">Semua proyek →</a>
</div>
<div class="sem-grid">
    <?php foreach ($semesters as $sm):
        $tot = (int) $sm['total'];
        $num = preg_match('/\d+/', (string) $sm['name'], $mm) ? $mm[0] : '•';
        $pct = $tot > 0 ? max(10, (int) round($tot / $maxSem * 100)) : 0;
    ?>
    <a href="/projects?semester_id=<?= (int) $sm['id'] ?>" class="sem-card<?= $tot === 0 ? ' is-empty' : '' ?>">
        <span class="sem-num"><?= e($num) ?></span>
        <span class="sem-info">
            <strong><?= e($sm['name']) ?></strong>
            <small><b><?= $tot ?></b> proyek</small>
            <span class="sem-bar"><i style="width:<?= $pct ?>%"></i></span>
        </span>
        <span class="sem-go" aria-hidden="true">→</span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="section-title">
    <span>✨ Proyek Terbaru</span>
    <a href="/projects">Lihat Semua →</a>
</div>
<div class="project-grid">
    <?php foreach ($latestProjects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<div class="section-title"><span>🔥 Proyek Populer</span></div>
<div class="project-grid">
    <?php foreach ($popularProjects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>


<script>
(function () {
  var box = document.getElementById('war-countdown');
  if (!box) return;
  var starts = box.getAttribute('data-starts');
  var ends   = box.getAttribute('data-ends');
  var status = box.getAttribute('data-status');
  var label  = document.getElementById('war-cd-label');
  var cells  = { d: document.getElementById('war-d'), h: document.getElementById('war-h'), m: document.getElementById('war-m'), s: document.getElementById('war-s') };
  function pad(n) { return n < 10 ? '0' + n : '' + n; }
  function parse(v) { return new Date(v.replace(' ', 'T')).getTime(); }
  function tick() {
    var target, text;
    if (status === 'scheduled' && starts) { target = parse(starts); text = '⏳ War dimulai dalam'; }
    else if (status === 'active' && ends) { target = parse(ends);   text = '🔥 War berakhir dalam'; }
    else { box.hidden = true; return; }
    box.hidden = false;
    label.textContent = text;
    var s = Math.max(0, Math.floor((target - Date.now()) / 1000));
    var d = Math.floor(s / 86400); s %= 86400;
    var h = Math.floor(s / 3600);  s %= 3600;
    var m = Math.floor(s / 60);    s %= 60;
    cells.d.textContent = pad(d); cells.h.textContent = pad(h);
    cells.m.textContent = pad(m); cells.s.textContent = pad(s);
  }
  tick();
  setInterval(tick, 1000);
})();
</script>