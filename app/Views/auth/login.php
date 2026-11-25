<?php
/** @var string $csrf */
$oldEmail = \App\Core\Session::flash('old_email') ?? '';
$error    = \App\Core\Session::flash('error');
$success  = \App\Core\Session::flash('success');
// Akun demo hanya tampil di lingkungan lokal (APP_ENV=local di .env)
$showDemo = getenv('APP_ENV') === 'local';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Login - TIF Hub') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #037992;
            --primary-dark: #025a70;
            --text: #062534;
            --muted: #587486;
            --border: #dbe8ee;
            --danger: #b91c1c;
            --grad: linear-gradient(135deg, #024c5d, #037992 55%, #1fa0bd 130%);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(160deg, #021624 0%, #024c5d 42%, #037992 78%, #299cb8 120%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--text);
            position: relative;
            overflow-x: hidden;
        }
        /* Bola cahaya dekoratif */
        body::before, body::after { content: ''; position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; }
        body::before { width: 520px; height: 520px; top: -180px; right: -120px; background: radial-gradient(circle, rgba(255,255,255,.25), transparent 70%); animation: floaty 8s ease-in-out infinite; }
        body::after { width: 460px; height: 460px; bottom: -200px; left: -140px; background: radial-gradient(circle, rgba(41,182,208,.45), transparent 70%); animation: floaty 10s ease-in-out infinite reverse; }
        @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-24px); } }
        @keyframes rise { from { opacity: 0; transform: translateY(24px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes spin { to { transform: rotate(360deg); } }

        .shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            display: grid;
            gap: 2.5rem;
            align-items: center;
            animation: rise .6s cubic-bezier(.2, .7, .2, 1) both;
        }

        /* ---------- Panel brand (hanya desktop) ---------- */
        .brand { display: none; color: #fff; }
        .brand .tag {
            display: inline-flex; align-items: center; gap: .45rem;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28);
            padding: .35rem .85rem; border-radius: 999px; font-size: .78rem; font-weight: 700;
            backdrop-filter: blur(6px);
        }
        .ico { width: 1.15em; height: 1.15em; flex-shrink: 0; }
        .demo-btn b { display: flex !important; align-items: center; gap: .35rem; }
        .hint .ico { width: 1em; height: 1em; vertical-align: -.15em; margin-right: .2rem; }
        .brand h2 { font-size: 2.5rem; line-height: 1.12; font-weight: 800; letter-spacing: -1px; margin: 1.1rem 0 .9rem; text-wrap: balance; }
        .brand > p { color: rgba(255,255,255,.85); line-height: 1.7; max-width: 31rem; text-wrap: pretty; }
        .features { list-style: none; margin-top: 1.8rem; display: grid; gap: .9rem; }
        .features li { display: flex; align-items: flex-start; gap: .8rem; }
        .features .ico {
            flex: none; width: 40px; height: 40px; border-radius: 12px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.25);
            display: flex; align-items: center; justify-content: center;
        }
        .features svg { width: 20px; height: 20px; }
        .features b { display: block; font-size: .95rem; margin-bottom: .1rem; }
        .features span { display: block; font-size: .84rem; line-height: 1.5; max-width: 28rem; color: rgba(255,255,255,.8); text-wrap: pretty; }

        /* ---------- Kartu login ---------- */
        .card {
            background: rgba(255,255,255,.98);
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 26px;
            box-shadow: 0 30px 80px rgba(2,37,51,.35);
            padding: 2.2rem 2rem 1.8rem;
        }
        .logo { text-align: center; margin-bottom: 1.6rem; }
        /* PNG logo berlatar putih & tulisan jurusannya abu sangat muda (tidak terbaca).
           Jadi hanya 3 emblem yang dipotong dari PNG (lewat background), tulisannya dibuat teks asli. */
        .logo-pill {
            display: inline-flex; align-items: center; gap: .85rem;
            background: #fff; border: 1px solid var(--border);
            padding: 10px 18px 10px 14px; border-radius: 16px;
            box-shadow: 0 10px 26px rgba(3,121,146,.18);
            max-width: 100%;
        }
        .logo-emblems {
            flex: none; height: 44px; width: 120px;
            background: url('/assets/images/logo-jti.png') left center / auto 100% no-repeat;
        }
        .logo-text {
            border-left: 2px solid var(--border); padding-left: .85rem; text-align: left;
            font-size: .64rem; font-weight: 800; letter-spacing: .7px; line-height: 1.25;
            text-transform: uppercase; color: #334155;
        }
        .logo-text b { display: block; font-size: 1.05rem; letter-spacing: -.3px; color: var(--primary-dark); line-height: 1; margin-bottom: .2rem; }
        .logo h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -.6px; margin-top: 1.1rem; }
        .logo h1 span { background: var(--grad); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .logo p { color: var(--muted); font-size: .88rem; margin-top: .35rem; }

        /* ---------- Notifikasi: hilang sendiri setelah beberapa detik ---------- */
        .alert {
            position: relative; overflow: hidden;
            display: flex; align-items: flex-start; gap: .6rem;
            padding: .75rem .5rem .85rem .95rem; border-radius: 12px; margin-bottom: 1.1rem;
            font-size: .86rem; line-height: 1.45;
            max-height: 140px;
            animation: alertIn .45s cubic-bezier(.2, .7, .2, 1) both;
            transition: opacity .35s ease, transform .35s ease, max-height .4s ease,
                        padding .4s ease, margin .4s ease, border-width .4s ease;
        }
        .alert > svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
        .alert > span { flex: 1; }
        .alert-error { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-close {
            flex: none; width: 28px; height: 28px; border: none; background: transparent;
            border-radius: 8px; color: inherit; opacity: .6; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: opacity .2s, background .2s;
        }
        .alert-close:hover { opacity: 1; background: rgba(0,0,0,.06); }
        .alert-close:focus-visible { outline: 2px solid currentColor; outline-offset: 1px; opacity: 1; }
        .alert-close svg { width: 16px; height: 16px; }
        /* Garis waktu mundur di bagian bawah notifikasi */
        .alert-bar {
            position: absolute; left: 0; bottom: 0; height: 3px; width: 100%;
            background: currentColor; opacity: .35; transform-origin: left;
            animation: alertBar linear forwards;
        }
        .alert.paused .alert-bar { animation-play-state: paused; }
        .alert.hide {
            opacity: 0; transform: translateY(-8px); max-height: 0;
            padding-top: 0; padding-bottom: 0; margin-bottom: 0; border-width: 0;
        }
        @keyframes alertIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: none; } }
        @keyframes alertBar { from { transform: scaleX(1); } to { transform: scaleX(0); } }

        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: .86rem; font-weight: 700; margin-bottom: .4rem; }
        .field { position: relative; }
        .field .lead {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 19px; height: 19px; color: #94a3b8; pointer-events: none; transition: color .2s;
        }
        .field input {
            width: 100%;
            padding: .8rem .95rem .8rem 2.8rem;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font: inherit; font-size: .95rem; color: var(--text);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }
        .field input::placeholder { color: #94a3b8; }
        .field input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(3,121,146,.15); }
        .field:focus-within .lead { color: var(--primary); }
        .field.has-toggle input { padding-right: 3rem; }
        .toggle {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 38px; height: 38px; border: none; background: transparent; border-radius: 10px;
            color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .2s, color .2s;
        }
        .toggle:hover { background: #f1f5f9; color: var(--primary); }
        .toggle:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
        .toggle svg { width: 20px; height: 20px; }
        .toggle .eye-off { display: none; }
        .toggle[aria-pressed="true"] .eye { display: none; }
        .toggle[aria-pressed="true"] .eye-off { display: block; }
        .id-mode { display: block; max-height: 0; overflow: hidden; margin-top: 0; font-size: .76rem; font-weight: 700; color: var(--primary); opacity: 0; transition: max-height .2s ease, margin-top .2s ease, opacity .2s ease; }
        .id-mode.show { max-height: 1.6em; margin-top: .4rem; opacity: 1; }
        .hint { display: none; margin-top: .4rem; font-size: .78rem; font-weight: 600; color: #b45309; }
        .hint.show { display: block; }

        .btn {
            width: 100%; margin-top: .3rem;
            padding: .85rem; border: none; border-radius: 12px;
            background: var(--grad); color: #fff;
            font: inherit; font-size: .97rem; font-weight: 700; cursor: pointer;
            box-shadow: 0 10px 26px rgba(3,121,146,.45);
            display: flex; align-items: center; justify-content: center; gap: .6rem;
            transition: transform .2s, box-shadow .25s, opacity .2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(3,121,146,.6); }
        .btn:active { transform: scale(.98); }
        .btn:focus-visible { outline: 3px solid rgba(3,121,146,.4); outline-offset: 2px; }
        .btn[disabled] { opacity: .8; cursor: wait; transform: none; }
        .btn .spinner { display: none; width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
        .btn.loading .spinner { display: inline-block; }

        .footer-link { text-align: center; margin-top: 1.2rem; font-size: .875rem; color: var(--muted); }
        .footer-link a { color: var(--primary-dark); font-weight: 700; text-decoration: none; }
        .footer-link a:hover { text-decoration: underline; }

        /* ---------- Akun demo ---------- */
        .demo { margin-top: 1.4rem; padding-top: 1.1rem; border-top: 1px dashed var(--border); }
        .demo h4 { font-size: .78rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: .6rem; }
        .demo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
        .demo-btn {
            text-align: left; padding: .6rem .75rem; border-radius: 12px;
            border: 1.5px solid var(--border); background: #f8fafc; cursor: pointer; font: inherit;
            transition: border-color .2s, background .2s, transform .2s;
        }
        .demo-btn:hover { border-color: var(--primary); background: #e3f3f7; transform: translateY(-1px); }
        .demo-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
        .demo-btn b { display: block; font-size: .82rem; color: var(--text); }
        .demo-btn span { display: block; font-size: .72rem; color: var(--muted); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .demo-note { margin-top: .5rem; font-size: .72rem; color: var(--muted); }

        .page-foot { margin-top: 1rem; text-align: center; font-size: .75rem; color: rgba(255,255,255,.8); }

        @media (min-width: 900px) {
            .shell { max-width: 1180px; grid-template-columns: minmax(0, 1fr) 440px; column-gap: 4.5rem; row-gap: 1.25rem; }
            .brand { display: block; max-width: 36rem; }
            .page-foot { grid-column: 1 / -1; margin-top: 0; }
        }
        /* Laptop / layar sedang: rapatkan sedikit supaya panel kiri tidak sempit */
        @media (min-width: 900px) and (max-width: 1100px) {
            .shell { column-gap: 2.5rem; }
            .brand h2 { font-size: 2.05rem; }
        }
        /* Layar pendek: padatkan jarak vertikal supaya kartu dan footer muat tanpa terpotong */
        @media (min-width: 900px) and (max-height: 860px) {
            body { padding: 1rem 1.5rem; }
            .shell { row-gap: .9rem; }
            .card { padding: 1.6rem 2rem 1.4rem; }
            .logo { margin-bottom: 1.1rem; }
            .logo h1 { margin-top: .85rem; }
            .form-group { margin-bottom: .9rem; }
            .demo { margin-top: 1rem; padding-top: .9rem; }
            .features { margin-top: 1.4rem; gap: .8rem; }
        }
        @media (max-width: 420px) {
            body { padding: 1rem; }
            .card { padding: 1.8rem 1.25rem 1.5rem; border-radius: 22px; }
            .logo-pill { gap: .6rem; padding: 9px 14px 9px 10px; }
            .logo-emblems { height: 36px; width: 98px; }
            .demo-grid { grid-template-columns: 1fr; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>
    <main class="shell">

        <!-- Panel brand: tampil di layar lebar -->
        <section class="brand" aria-hidden="false">
            <span class="tag"><?= icon('graduation') ?> Jurusan Teknologi Informasi</span>
            <h2>Temukan proyek, klaim yang kamu incar.</h2>
            <p>TIF Hub memudahkan kamu menjelajahi proyek mahasiswa TIF Politeknik Negeri Jember dari PSDKU Nganjuk dan mengklaim proyek untuk semester kamu.</p>
            <ul class="features">
                <li>
                    <div class="ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    </div>
                    <div><b>Jelajahi proyek</b><span>Cari lewat kata kunci, lalu saring berdasarkan kategori, semester, atau teknologi. Simpan favoritmu ke wishlist.</span></div>
                </li>
                <li>
                    <div class="ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div><b>Klaim proyek semestermu</b><span>Saat jadwal klaim dibuka, pilih satu proyek yang masih tersedia dan dapatkan tiket digitalnya.</span></div>
                </li>
                <li>
                    <div class="ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <div><b>Lihat karya secara lengkap</b><span>Buka detail proyek beserta foto, teknologi, anggota kelompok, serta link GitHub dan demo-nya.</span></div>
                </li>
            </ul>
        </section>

        <!-- Kartu login -->
        <section class="card" aria-labelledby="judul">
            <div class="logo">
                <div class="logo-pill" role="img" aria-label="Logo Jurusan Teknologi Informasi">
                    <span class="logo-emblems" aria-hidden="true"></span>
                    <span class="logo-text" aria-hidden="true"><b>JTI</b>Jurusan<br>Teknologi<br>Informasi</span>
                </div>
                <h1 id="judul">Masuk ke <span>TIF Hub</span></h1>
                <p>Platform Karya Mahasiswa Teknik Informatika</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" role="alert" data-autohide="7000">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><?= e($error) ?></span>
                    <button type="button" class="alert-close" aria-label="Tutup pemberitahuan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    <i class="alert-bar" style="animation-duration: 7000ms" aria-hidden="true"></i>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" role="status" data-autohide="4500">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span><?= e($success) ?></span>
                    <button type="button" class="alert-close" aria-label="Tutup pemberitahuan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    <i class="alert-bar" style="animation-duration: 4500ms" aria-hidden="true"></i>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" id="loginForm" novalidate>
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">

                <div class="form-group">
                    <label for="email">Email atau NIM</label>
                    <div class="field">
                        <svg class="lead" id="idIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m22 7-10 6L2 7"/></svg>
                        <input type="text" id="email" name="email" value="<?= e($oldEmail) ?>"
                               placeholder="nama@email.com atau NIM" autocomplete="username"
                               autocapitalize="none" autocorrect="off" spellcheck="false" required>
                    </div>
                    <span class="id-mode" id="idMode" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="field has-toggle">
                        <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="3"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="password" name="password"
                               placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="toggle" id="togglePw" aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <div class="hint" id="capsHint"><?= icon('alert') ?> Caps Lock sedang aktif</div>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    <span class="spinner" aria-hidden="true"></span>
                    <span class="label">Masuk</span>
                </button>
            </form>

            <div class="footer-link">
                Belum punya akun? <a href="/register">Daftar di sini</a>
            </div>

            <?php if ($showDemo): ?>
            <div class="demo">
                <h4>Akun demo</h4>
                <div class="demo-grid">
                    <button type="button" class="demo-btn" data-email="admin@tifhub.local" data-pass="password123">
                        <b><?= icon('shield') ?> Admin</b><span>admin@tifhub.local</span>
                    </button>
                    <button type="button" class="demo-btn" data-email="mahasiswa@tifhub.local" data-pass="password123">
                        <b><?= icon('graduation') ?> Mahasiswa</b><span>mahasiswa@tifhub.local</span>
                    </button>
                </div>
                <p class="demo-note">Klik salah satu untuk mengisi otomatis. Hanya tampil di lingkungan lokal.</p>
            </div>
            <?php endif; ?>
        </section>

        <p class="page-foot">&copy; <?= date('Y') ?> TIF Hub · Jurusan Teknologi Informasi</p>
    </main>

    <script>
    (function () {
        'use strict';
        var form = document.getElementById('loginForm');
        var email = document.getElementById('email');
        var pw = document.getElementById('password');
        var toggle = document.getElementById('togglePw');
        var caps = document.getElementById('capsHint');
        var btn = document.getElementById('submitBtn');
        var label = btn.querySelector('.label');

        // Fokus otomatis: ke password bila email sudah terisi (setelah salah login)
        (email.value ? pw : email).focus();

        // Deteksi email vs NIM: ganti ikon dan tampilkan petunjuk singkat
        var idIcon = document.getElementById('idIcon');
        var idMode = document.getElementById('idMode');
        var ICON_MAIL = '<rect x="2" y="4" width="20" height="16" rx="3"/><path d="m22 7-10 6L2 7"/>';
        var ICON_NIM  = '<path d="M16 10h2M16 14h2M6.17 15a3 3 0 0 1 5.66 0"/><circle cx="9" cy="11" r="2"/><rect x="2" y="5" width="20" height="14" rx="3"/>';
        var curMode = '';
        function detectMode() {
            var v = email.value.trim();
            var m = v === '' ? '' : (v.indexOf('@') > -1 ? 'email' : 'nim');
            if (m === curMode) return;
            curMode = m;
            idIcon.innerHTML = (m === 'nim') ? ICON_NIM : ICON_MAIL;
            idMode.textContent = m === 'nim' ? 'Masuk menggunakan NIM' : (m === 'email' ? 'Masuk menggunakan email' : '');
            idMode.classList.toggle('show', m !== '');
            email.setAttribute('inputmode', m === 'nim' ? 'numeric' : 'email');
        }
        email.addEventListener('input', detectMode);
        detectMode();

        // Tampilkan / sembunyikan password
        toggle.addEventListener('click', function () {
            var show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            pw.focus();
        });

        // Peringatan Caps Lock
        function checkCaps(e) {
            if (!e.getModifierState) return;
            caps.classList.toggle('show', e.getModifierState('CapsLock'));
        }
        pw.addEventListener('keydown', checkCaps);
        pw.addEventListener('keyup', checkCaps);
        pw.addEventListener('blur', function () { caps.classList.remove('show'); });

        // Isi otomatis akun demo
        document.querySelectorAll('.demo-btn').forEach(function (b) {
            b.addEventListener('click', function () {
                email.value = b.dataset.email;
                detectMode();
                pw.value = b.dataset.pass;
                btn.focus();
            });
        });

        // Notifikasi hilang otomatis; berhenti sementara saat disorot kursor; bisa ditutup manual
        document.querySelectorAll('.alert[data-autohide]').forEach(function (el) {
            var remaining = parseInt(el.dataset.autohide, 10) || 5000;
            var startedAt = 0, timer = null;
            function hide() {
                clearTimeout(timer);
                el.classList.add('hide');
                setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 450);
            }
            function start() { el.classList.remove('paused'); startedAt = Date.now(); timer = setTimeout(hide, remaining); }
            function pause() { el.classList.add('paused'); clearTimeout(timer); remaining -= Date.now() - startedAt; if (remaining < 600) remaining = 600; }
            el.addEventListener('mouseenter', pause);
            el.addEventListener('mouseleave', start);
            el.querySelector('.alert-close').addEventListener('click', hide);
            start();
        });

        // Cegah klik ganda + tampilkan loading
        function reset() { btn.disabled = false; btn.classList.remove('loading'); label.textContent = 'Masuk'; }
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                return;
            }
            btn.disabled = true;
            btn.classList.add('loading');
            label.textContent = 'Memproses…';
        });
        // Tombol "kembali" browser bisa memulihkan halaman dalam keadaan loading
        window.addEventListener('pageshow', reset);
    })();
    </script>
</body>
</html>