<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$is = fn(string $p, bool $exact = false) => $exact ? $path === $p : str_starts_with($path, $p);
try { $openTickets = (new \App\Models\Ticket())->countByStatus('open'); } catch (\Throwable $e) { $openTickets = 0; }

$ico = [
    'dash'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>',
    'users'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'projects' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg>',
    'ticket'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>',
    'logout'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>',
    'menu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
    'globe'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin - TIF Hub') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/animations.css') ?>">
</head>
<body>
<div class="admin-shell">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <a href="/admin" class="brand">
            <img src="<?= asset('images/logo-jti.png') ?>" alt="JTI Logo">
            <div class="brand-text">TIF Hub <span>Admin Panel</span></div>
        </a>

        <div class="side-label">Menu Utama</div>
        <nav class="side-nav">
            <a href="/admin" class="<?= $is('/admin', true) ? 'active' : '' ?>"><?= $ico['dash'] ?> Dashboard</a>
            <a href="/admin/projects" class="<?= $is('/admin/projects') ? 'active' : '' ?>"><?= $ico['projects'] ?> Proyek</a>
            <a href="/admin/students" class="<?= $is('/admin/students') ? 'active' : '' ?>"><?= $ico['users'] ?> Mahasiswa</a>
            <a href="/admin/tickets" class="<?= $is('/admin/tickets') ? 'active' : '' ?>"><?= $ico['ticket'] ?> Tiket
                <?php if ($openTickets > 0): ?><span class="count"><?= (int) $openTickets ?></span><?php endif; ?>
            </a>
            <a href="/admin/war" class="<?= $is('/admin/war') ? 'active' : '' ?>"><?= $ico['projects'] ?> Project War</a>
            <a href="/admin/claims" class="<?= $is('/admin/claims') ? 'active' : '' ?>"><?= $ico['ticket'] ?> Tiket Klaim</a>
            <a href="/admin/violations" class="<?= $is('/admin/violations') ? 'active' : '' ?>"><?= $ico['users'] ?> Pelanggaran</a>
        </nav>

        <div class="side-label">Pintasan</div>
        <nav class="side-nav">
            <a href="/admin/projects/create"><?= $ico['projects'] ?> + Proyek Baru</a>
        </nav>

        <div class="side-user">
            <span class="avatar"><?= e(initials($user['name'] ?? 'Admin')) ?></span>
            <div class="who"><strong><?= e($user['name'] ?? 'Admin') ?></strong><span>Administrator</span></div>
            <a href="/logout" title="Logout"><?= $ico['logout'] ?></a>
        </div>
    </aside>

    <div class="admin-main">
        <div class="topbar">
            <button type="button" id="sidebarToggle" aria-label="Menu"><?= $ico['menu'] ?></button>
            <strong>TIF Hub Admin</strong>
            <span class="avatar"><?= e(initials($user['name'] ?? 'Admin')) ?></span>
        </div>

        <main class="admin-content">
            <?php if ($msg = \App\Core\Session::flash('success')): ?>
                <div class="alert alert-success"><?= icon('check-circle') ?> <span><?= e($msg) ?></span></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::flash('error')): ?>
                <div class="alert alert-error"><?= icon('alert') ?> <span><?= e($msg) ?></span></div>
            <?php endif; ?>
            <?= $content ?? '' ?>
        </main>
        <footer class="admin-footer">TIF Hub Admin · Jurusan Teknologi Informasi · &copy; <?= date('Y') ?></footer>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/animations.js') ?>"></script>
</body>
</html>
