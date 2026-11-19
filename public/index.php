<?php

/**
 * TIF Hub - Front Controller
 */

declare(strict_types=1);

// Load .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Jakarta');

if (getenv('APP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Penanganan error global: tidak ada lagi halaman putih kosong
set_exception_handler(function (Throwable $e): void {
    error_log('[TIF Hub] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
    }

    $debug = getenv('APP_DEBUG') === 'true';
    $msg = $e->getMessage();
    $hint = '';
    if ($e instanceof PDOException && stripos($msg, 'project_images') !== false) {
        $hint = 'Tabel galeri foto belum ada. Jalankan database/add_project_images.sql (atau import ulang migrations.sql).';
    } elseif ($e instanceof PDOException && stripos($msg, 'thumbnail') !== false) {
        $hint = 'Kolom projects.thumbnail belum ada. Import ulang database/migrations.sql.';
    } elseif ($e instanceof PDOException) {
        $hint = 'Terjadi masalah pada database. Pastikan MySQL menyala dan .env sudah benar.';
    }

    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Terjadi kesalahan - TIF Hub</title>'
        . '<style>body{font-family:system-ui,sans-serif;background:#f1f5f9;color:#0f172a;margin:0;display:grid;place-items:center;min-height:100vh;padding:1rem}'
        . '.box{background:#fff;border-radius:16px;padding:2rem;max-width:640px;box-shadow:0 10px 30px rgba(0,0,0,.08)}'
        . 'h1{margin:0 0 .5rem;font-size:1.4rem}p{color:#475569;line-height:1.6}'
        . 'pre{background:#0f172a;color:#e2e8f0;padding:1rem;border-radius:10px;overflow:auto;font-size:.8rem;white-space:pre-wrap}'
        . 'a{color:#6366f1;font-weight:600}</style></head><body><div class="box"><h1>Ups, terjadi kesalahan</h1>'
        . '<p>' . htmlspecialchars($hint ?: 'Halaman tidak dapat ditampilkan saat ini. Silakan coba lagi.', ENT_QUOTES, 'UTF-8') . '</p>';
    if ($debug) {
        echo '<pre>' . htmlspecialchars(get_class($e) . ': ' . $msg . "\n" . $e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8') . '</pre>';
    }
    echo '<p><a href="/">← Kembali ke beranda</a></p></div></body></html>';
});

// Autoloader
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    if (!str_starts_with($class, $prefix)) return;
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

// Helpers
require dirname(__DIR__) . '/app/Helpers/helpers.php';

// Session
\App\Core\Session::start();

// Router
$router = new \App\Core\Router();
require dirname(__DIR__) . '/routes/web.php';
$router->dispatch();
