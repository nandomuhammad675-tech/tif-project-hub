<?php
$totalProjects = 0; foreach ($categories as $c) { $totalProjects += (int) $c['total']; }
?>
<div class="hero">
    <img src="<?= asset('images/logo-jti.png') ?>" alt="JTI" class="hero-logo">
    <h1>Jelajahi Karya<br>Mahasiswa TIF</h1>
    <p>Temukan, lihat, dan ambil proyek terbaik dari Program Studi Teknik Informatika.</p>
    <form class="hero-search" method="GET" action="/projects">
        <input type="text" name="q" placeholder="Cari proyek atau teknologi…" aria-label="Cari proyek">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>
    <div class="hero-stats">
        <div><strong><?= (int) $totalProjects ?></strong>Proyek</div>
        <div><strong><?= count($categories) ?></strong>Kategori</div>
        <div><strong><?= count($semesters) ?></strong>Semester</div>
    </div>
</div>


<!-- Project War / Klaim Tiket -->
<div class="card mb-3" id="klaim-tiket" style="border:1px solid rgba(3,121,146,.25);background:linear-gradient(135deg,#f0f9fb 0%,#fff 60%);">
    <div class="section-title" style="margin-top:0;"><span>🎯 Klaim Tiket Proyek</span></div>
    <p class="text-muted text-sm" style="margin-bottom:1rem;">Satu tombol utama untuk mengikuti Project War. Katalog di bawah hanya galeri karya — klaim tidak dilakukan dari kartu proyek.</p>

    <div class="stats-grid" style="margin-bottom:1rem;">
        <div class="stat-card">
            <div class="label">Semester aktif</div>
            <div class="value" style="font-size:1.1rem;">
                <?= e(($war['semester_name'] ?? null) ? $war['semester_name'] . (!empty($war['academic_year']) ? ' · ' . $war['academic_year'] : '') : 'Belum diatur') ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="label">Status war</div>
            <div class="value" style="font-size:1.1rem;">
                <?php
                $statusMap = [
                    'active'    => ['● Aktif', 'badge-success'],
                    'scheduled' => ['⏳ Terjadwal', 'badge-warning'],
                    'ended'     => ['Selesai', 'badge-secondary'],
                    'closed'    => ['Ditutup', 'badge-secondary'],
                ];
                [$label, $cls] = $statusMap[$warStatus ?? 'closed'] ?? $statusMap['closed'];
                ?>
                <span class="badge <?= $cls ?>"><?= $label ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="label">Proyek tersedia</div>
            <div class="value primary"><?= (int) ($availableCount ?? 0) ?></div>
        </div>
    </div>

    <?php if (!empty($war['starts_at']) || !empty($war['ends_at'])): ?>
    <p class="text-sm mb-2">
        <strong>Jadwal:</strong>
        <?php if (!empty($war['starts_at'])): ?>
            mulai <?= e(format_datetime($war['starts_at'])) ?>
        <?php endif; ?>
        <?php if (!empty($war['ends_at'])): ?>
            — berakhir <?= e(format_datetime($war['ends_at'])) ?>
        <?php endif; ?>
    </p>
    <div id="war-countdown" class="text-sm mb-2" data-starts="<?= e($war['starts_at'] ?? '') ?>" data-ends="<?= e($war['ends_at'] ?? '') ?>" data-status="<?= e($warStatus ?? 'closed') ?>"></div>
    <?php endif; ?>

    <div class="card" style="background:#fff;margin-bottom:1rem;padding:1rem;">
        <strong style="display:block;margin-bottom:.5rem;">Aturan klaim</strong>
        <ul class="text-sm text-muted" style="margin:0;padding-left:1.2rem;line-height:1.6;">
            <li>Satu proyek hanya boleh dimiliki satu mahasiswa.</li>
            <li>Satu mahasiswa hanya boleh mengklaim <strong>satu proyek per semester</strong>.</li>
            <li>Klaim kedua pada semester yang sama ditolak dan dicatat sebagai pelanggaran.</li>
            <li>Pengurangan nilai hanya setelah ditinjau dan dikonfirmasi admin.</li>
            <li>Waktu dan validasi ditentukan server, bukan jam perangkat Anda.</li>
        </ul>
    </div>

    <div class="flex gap-1" style="flex-wrap:wrap;align-items:center;">
        <?php if (!empty($userClaim)): ?>
            <span class="btn btn-primary" style="cursor:default;opacity:.95;">✓ Tiket Sudah Dimiliki</span>
            <a href="/claims/<?= (int) $userClaim['id'] ?>" class="btn btn-outline">Lihat Tiket</a>
            <a href="/profile" class="btn btn-outline">Profil Saya</a>
        <?php elseif (!empty($warActive)): ?>
            <a href="/claims/select" class="btn btn-primary" id="btn-klaim-sekarang">🎯 KLAIM TIKET SEKARANG</a>
        <?php else: ?>
            <button type="button" class="btn btn-primary" disabled style="opacity:.55;cursor:not-allowed;">🎯 KLAIM TIKET SEKARANG</button>
            <span class="text-sm text-muted">Tombol aktif saat periode war dibuka admin.</span>
        <?php endif; ?>
    </div>
</div>

<div class="section-title">
    <span>🔥 Proyek Terbaru</span>
    <a href="/projects">Lihat Semua →</a>
</div>
<div class="project-grid">
    <?php foreach ($latestProjects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<div class="section-title"><span>⭐ Proyek Populer</span></div>
<div class="project-grid">
    <?php foreach ($popularProjects as $p) { $opts = []; include __DIR__ . '/../partials/project_card.php'; } ?>
</div>

<div class="section-title"><span>📂 Kategori</span></div>
<div class="stats-grid">
    <?php foreach ($categories as $c): ?>
    <a href="/projects?category_id=<?= (int) $c['id'] ?>" class="stat-card" style="color:inherit;">
        <div class="label"><?= e(category_icon($c['icon'])) ?> <?= e($c['name']) ?></div>
        <div class="value primary"><?= (int) $c['total'] ?></div>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!empty($semesters)): ?>
<div class="section-title"><span>📅 Berdasarkan Semester</span></div>
<div class="stats-grid">
    <?php foreach ($semesters as $s): ?>
    <a href="/projects?semester_id=<?= (int) $s['id'] ?>" class="stat-card" style="color:inherit;">
        <div class="label"><?= e($s['name']) ?></div>
        <div class="value"><?= (int) $s['total'] ?> <span class="text-muted text-sm" style="font-weight:600">proyek</span></div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
(function () {
  var el = document.getElementById('war-countdown');
  if (!el) return;
  var starts = el.getAttribute('data-starts');
  var ends = el.getAttribute('data-ends');
  var status = el.getAttribute('data-status');
  function pad(n) { return n < 10 ? '0' + n : '' + n; }
  function tick() {
    var now = Date.now();
    var target, label;
    if (status === 'scheduled' && starts) {
      target = new Date(starts.replace(' ', 'T')).getTime();
      label = 'Dimulai dalam';
    } else if (status === 'active' && ends) {
      target = new Date(ends.replace(' ', 'T')).getTime();
      label = 'Berakhir dalam';
    } else {
      el.textContent = '';
      return;
    }
    var diff = Math.max(0, target - now);
    var s = Math.floor(diff / 1000);
    var d = Math.floor(s / 86400); s %= 86400;
    var h = Math.floor(s / 3600); s %= 3600;
    var m = Math.floor(s / 60); s %= 60;
    el.innerHTML = '<strong>' + label + ':</strong> ' +
      (d ? d + 'h ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s) +
      ' <span class="text-muted">(perkiraan tampilan; validasi di server)</span>';
  }
  tick();
  setInterval(tick, 1000);
})();
</script>
