<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 40%, #8b5cf6 75%, #ec4899 120%); background-attachment: fixed; position: relative; overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
        }
        .login-container { width: 100%; max-width: 420px; padding: 1.5rem; }
        .login-card {
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(79, 70, 229, 0.12);
            padding: 2.4rem 2rem;
            border: 1px solid var(--border);
        }
        .logo { text-align: center; margin-bottom: 1.75rem; }
        .logo img { height: 48px; width: auto; margin: 0 auto 0.75rem; }
        .logo h1 { font-size: 1.7rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }
        .logo p { color: var(--muted); font-size: 0.88rem; margin-top: 0.3rem; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.2rem;
            font-size: 0.875rem;
        }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.14);
        }
        .btn {
            width: 100%;
            padding: 0.8rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(79,70,229,0.25);
        }
        .btn:hover { background: var(--primary-dark); }
        .footer-link {
            text-align: center;
            margin-top: 1.3rem;
            font-size: 0.875rem;
            color: var(--muted);
        }
        .footer-link a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .footer-link a:hover { text-decoration: underline; }
        .demo-accounts {
            margin-top: 1.4rem;
            padding-top: 1.1rem;
            border-top: 1px solid var(--border);
            font-size: 0.78rem;
            color: var(--muted);
        }
        .demo-accounts h4 { font-size: 0.78rem; margin-bottom: 0.45rem; color: var(--text); }
        .demo-accounts code {
            background: #f1f5f9;
            padding: 0.12rem 0.38rem;
            border-radius: 4px;
            font-size: 0.74rem;
        }
        .demo-accounts p { margin-bottom: 0.3rem; }
    
        body::before, body::after { content:''; position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; }
        body::before { width: 520px; height: 520px; top: -180px; right: -120px; background: radial-gradient(circle, rgba(255,255,255,.25), transparent 70%); animation: floaty 8s ease-in-out infinite; }
        body::after { width: 460px; height: 460px; bottom: -200px; left: -140px; background: radial-gradient(circle, rgba(6,182,212,.5), transparent 70%); animation: floaty 10s ease-in-out infinite reverse; }
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-24px); } }
        @keyframes rise { from { opacity: 0; transform: translateY(24px) scale(.98); } to { opacity: 1; transform: none; } }
        .login-container { position: relative; z-index: 1; animation: rise .6s cubic-bezier(.2,.7,.2,1) both; }
        .login-card { background: rgba(255,255,255,.97) !important; border: 1px solid rgba(255,255,255,.6) !important; border-radius: 26px !important; box-shadow: 0 30px 80px rgba(15,23,42,.35) !important; }
        .logo img { background: #fff; padding: 6px 12px; border-radius: 14px; box-shadow: 0 8px 22px rgba(99,102,241,.25); }
        .logo h1 { background: linear-gradient(135deg,#4f46e5,#8b5cf6,#ec4899); -webkit-background-clip: text; background-clip: text; color: transparent !important; font-size: 1.9rem !important; }
        .form-group input, .form-group select { border-width: 1.5px !important; padding-top: .75rem !important; padding-bottom: .75rem !important; transition: all .2s; }
        .form-group input:focus, .form-group select:focus { border-color: #6366f1 !important; box-shadow: 0 0 0 4px rgba(99,102,241,.15) !important; outline: none; }
        .btn { background: linear-gradient(135deg,#6366f1,#8b5cf6 60%,#ec4899 130%) !important; box-shadow: 0 10px 26px rgba(99,102,241,.45) !important; transition: all .25s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(99,102,241,.6) !important; }
        .btn:active { transform: scale(.98); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="/assets/images/logo-jti.png" alt="JTI">
                <h1>TIF Hub</h1>
                <p>Platform Karya Mahasiswa Teknik Informatika</p>
            </div>

            <?php if ($error = \App\Core\Session::flash('error')): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success = \App\Core\Session::flash('success')): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars(\App\Core\Session::flash('old_email') ?? '') ?>"
                        placeholder="nama@email.com" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn">Masuk</button>
            </form>

            <div class="footer-link">
                Belum punya akun? <a href="/register">Daftar di sini</a>
            </div>

            <div class="demo-accounts">
                <h4>Akun Demo (opsional)</h4>
                <p>Admin: <code>admin@tifhub.local</code> / password123</p>
                <p>Mahasiswa: <code>mahasiswa@tifhub.local</code> / password123</p>
            </div>
        </div>
    </div>
</body>
</html>
