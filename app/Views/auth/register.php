<?php
/** @var string $csrf */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Daftar') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #025a70;
            --primary-dark: #024c5d;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(160deg, #021624 0%, #024c5d 42%, #037992 78%, #299cb8 120%); background-attachment: fixed; position: relative; overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            padding: 1.5rem 0;
        }
        .login-container { width: 100%; max-width: 440px; padding: 1.5rem; }
        .login-card {
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(3,121,146, 0.12);
            padding: 2.2rem 2rem;
            border: 1px solid var(--border);
        }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo h1 { font-size: 1.6rem; font-weight: 800; color: var(--primary); }
        .logo p { color: var(--muted); font-size: 0.88rem; margin-top: 0.25rem; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.1rem;
            font-size: 0.875rem;
        }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .form-group { margin-bottom: 0.95rem; }
        .form-group label {
            display: block;
            font-size: 0.84rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.62rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.94rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(3,121,146, 0.14);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
        .btn {
            width: 100%;
            padding: 0.78rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 0.3rem;
            box-shadow: 0 2px 10px rgba(3,121,146,0.25);
        }
        .btn:hover { background: var(--primary-dark); }
        .footer-link {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.875rem;
            color: var(--muted);
        }
        .footer-link a { color: var(--primary); font-weight: 700; text-decoration: none; }

        body::before, body::after { content:''; position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; }
        body::before { width: 520px; height: 520px; top: -180px; right: -120px; background: radial-gradient(circle, rgba(255,255,255,.25), transparent 70%); animation: floaty 8s ease-in-out infinite; }
        body::after { width: 460px; height: 460px; bottom: -200px; left: -140px; background: radial-gradient(circle, rgba(41,182,208,.45), transparent 70%); animation: floaty 10s ease-in-out infinite reverse; }
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-24px); } }
        @keyframes rise { from { opacity: 0; transform: translateY(24px) scale(.98); } to { opacity: 1; transform: none; } }
        .login-container { position: relative; z-index: 1; animation: rise .6s cubic-bezier(.2,.7,.2,1) both; }
        .login-card { background: rgba(255,255,255,.97) !important; border: 1px solid rgba(255,255,255,.6) !important; border-radius: 26px !important; box-shadow: 0 30px 80px rgba(2,37,51,.35) !important; }
        /* Logo JTI: 3 emblem dipotong dari PNG (lewat background), tulisannya dibuat teks asli
           supaya terbaca jelas (PNG aslinya berlatar putih dengan tulisan abu sangat muda). */
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
        .logo h1 { margin-top: 1.1rem; }

        /* Tombol mata untuk menampilkan / menyembunyikan password */
        .field { position: relative; }
        .field.has-toggle input { padding-right: 3rem; }
        .toggle {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 38px; height: 38px; border: none; background: transparent; border-radius: 10px;
            color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .2s, color .2s;
        }
        .toggle:hover { background: #f1f5f9; color: #037992; }
        .toggle:focus-visible { outline: 2px solid #037992; outline-offset: 1px; }
        .toggle svg { width: 20px; height: 20px; }
        .toggle .eye-off { display: none; }
        .toggle[aria-pressed="true"] .eye { display: none; }
        .toggle[aria-pressed="true"] .eye-off { display: block; }
        @media (max-width: 420px) {
            .logo-pill { gap: .6rem; padding: 9px 14px 9px 10px; }
            .logo-emblems { height: 36px; width: 98px; }
        }
        .logo h1 { background: linear-gradient(135deg,#024c5d,#037992,#1fa0bd); -webkit-background-clip: text; background-clip: text; color: transparent !important; font-size: 1.9rem !important; }
        .form-group input, .form-group select { border-width: 1.5px !important; padding-top: .75rem !important; padding-bottom: .75rem !important; transition: all .2s; }
        .form-group input:focus, .form-group select:focus { border-color: #037992 !important; box-shadow: 0 0 0 4px rgba(3,121,146,.15) !important; outline: none; }
        .btn { background: linear-gradient(135deg,#024c5d,#037992 55%,#1fa0bd 130%) !important; box-shadow: 0 10px 26px rgba(3,121,146,.45) !important; transition: all .25s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(3,121,146,.6) !important; }
        .btn:active { transform: scale(.98); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo-pill" role="img" aria-label="Logo Jurusan Teknologi Informasi">
                    <span class="logo-emblems" aria-hidden="true"></span>
                    <span class="logo-text" aria-hidden="true"><b>JTI</b>Jurusan<br>Teknologi<br>Informasi</span>
                </div>
                <h1>TIF Hub</h1>
                <p>Daftar akun mahasiswa</p>
            </div>

            <?php if ($error = \App\Core\Session::flash('error')): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php $old = \App\Core\Session::flash('old') ?? []; ?>

            <form method="POST" action="/register">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="form-group">
                    <label for="nim">NIM *</label>
                    <input type="text" id="nim" name="nim" value="<?= htmlspecialchars($old['nim'] ?? '') ?>" placeholder="2024001" required autofocus>
                </div>
                <div class="form-group">
                    <label for="name">Nama Lengkap *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Nama lengkap" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="nama@email.com" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="class">Kelas</label>
                        <input type="text" id="class" name="class" value="<?= htmlspecialchars($old['class'] ?? '') ?>" placeholder="TI-1A">
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= ($old['semester'] ?? 1) == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="field has-toggle">
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" autocomplete="new-password" required minlength="6">
                        <button type="button" class="toggle" data-target="password" aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Password *</label>
                    <div class="field has-toggle">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" autocomplete="new-password" required>
                        <button type="button" class="toggle" data-target="password_confirm" aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn">Daftar Sekarang</button>
            </form>

            <div class="footer-link">
                Sudah punya akun? <a href="/login">Login di sini</a>
            </div>
        </div>
    </div>

    <script>
    (function () {
        'use strict';
        // Tampilkan / sembunyikan password (berlaku untuk kedua kolom)
        document.querySelectorAll('.toggle').forEach(function (btn) {
            var input = document.getElementById(btn.dataset.target);
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                input.focus();
            });
        });
    })();
    </script>
</body>
</html>