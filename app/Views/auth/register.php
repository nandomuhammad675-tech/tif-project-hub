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
        .logo img { height: 44px; width: auto; margin: 0 auto 0.65rem; }
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
        .logo img { background: #fff; padding: 6px 12px; border-radius: 14px; box-shadow: 0 8px 22px rgba(3,121,146,.25); }
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
                <img src="/assets/images/logo-jti.png" alt="JTI">
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
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Password *</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                </div>
                <button type="submit" class="btn">Daftar Sekarang</button>
            </form>

            <div class="footer-link">
                Sudah punya akun? <a href="/login">Login di sini</a>
            </div>
        </div>
    </div>
</body>
</html>