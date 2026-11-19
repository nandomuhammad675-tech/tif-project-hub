<?php $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'TIF Hub') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/animations.css') ?>">
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;">
<nav class="navbar">
    <a href="/" class="brand">
        <img src="<?= asset('images/logo-jti.png') ?>" alt="JTI Logo">
        <div class="brand-text">
            TIF Hub
            <span>Jurusan Teknologi Informasi</span>
        </div>
    </a>
    <div class="nav-links">
        <a href="/" class="<?= $uri === '/' ? 'active' : '' ?>">Beranda</a>
        <a href="/projects" class="<?= str_starts_with($uri, '/projects') ? 'active' : '' ?>">Proyek</a>
        <a href="/tickets" class="<?= str_starts_with($uri, '/tickets') ? 'active' : '' ?>">Tiket</a>
        <a href="/profile" class="<?= str_starts_with($uri, '/profile') ? 'active' : '' ?>">Profil</a>
        <div class="user-chip">
            <span class="avatar"><?= e(initials($user['name'] ?? '')) ?></span>
            <span class="user-name"><?= e($user['name'] ?? '') ?></span>
            <a href="/logout" class="btn btn-sm btn-outline">Logout</a>
        </div>
    </div>
</nav>
<main class="container" style="flex:1;width:100%;">
    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success">✅ <span><?= e($msg) ?></span></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-error">⚠️ <span><?= e($msg) ?></span></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</main>
<footer class="site-footer">
    <img src="<?= asset('images/logo-jti.png') ?>" alt="JTI">
    <p>TIF Hub · Jurusan Teknologi Informasi · &copy; <?= date('Y') ?></p>
</footer>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/animations.js') ?>"></script>
</body>
</html>
