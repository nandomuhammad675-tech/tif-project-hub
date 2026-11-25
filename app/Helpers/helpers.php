<?php

/**
 * Global helper functions
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    // ?v=<waktu ubah file> → browser otomatis ambil versi terbaru, tidak perlu Ctrl+F5
    $file = dirname(__DIR__, 2) . '/public/assets/' . $path;
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return '/assets/' . $path . $v;
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function status_badge(string $status): string
{
    $map = [
        'open'     => ['Open', 'badge-info'],
        'diproses' => ['Diproses', 'badge-warning'],
        'selesai'  => ['Selesai', 'badge-success'],
        'ditolak'  => ['Ditolak', 'badge-danger'],
        'active'   => ['Aktif', 'badge-success'],
        'inactive' => ['Nonaktif', 'badge-secondary'],
        'rendah'   => ['Rendah', 'badge-secondary'],
        'normal'   => ['Normal', 'badge-info'],
        'tinggi'   => ['Tinggi', 'badge-danger'],
    ];
    [$label, $class] = $map[$status] ?? [ucfirst($status), 'badge-secondary'];
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

function format_date(?string $datetime, string $format = 'd M Y'): string
{
    if (!$datetime) return '-';
    return date($format, strtotime($datetime));
}

function format_datetime(?string $datetime): string
{
    return format_date($datetime, 'd M Y H:i');
}

function tech_tags(?string $techs): string
{
    if (!$techs) return '';
    $tags = array_filter(array_map('trim', explode(',', $techs)));
    $html = '';
    foreach ($tags as $t) {
        $html .= '<span>' . e($t) . '</span>';
    }
    return $html;
}

function truncate(?string $text, int $length = 100): string
{
    if (!$text) return '';
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

/**
 * Ikon SVG (gaya garis, 24x24, mengikuti warna teks).
 * Pakai: <?= icon('plus') ?>  atau  <?= icon('check', 'ico-lg') ?>
 */
function icon(string $name, string $class = ''): string
{
    static $paths = [
        'globe'       => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
        'smartphone'  => '<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>',
        'monitor'     => '<rect width="20" height="14" x="2" y="3" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'wifi'        => '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/>',
        'cpu'         => '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2M15 20v2M2 15h2M2 9h2M20 15h2M20 9h2M9 2v2M9 20v2"/>',
        'gamepad'     => '<path d="M6 11h4M8 9v4M15 12h.01M18 10h.01"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258A4 4 0 0 0 17.32 5z"/>',
        'database'    => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>',
        'box'         => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'folder'      => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
        'search'      => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'zoom-in'     => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35M11 8v6M8 11h6"/>',
        'eye'         => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'trash'       => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>',
        'ticket'      => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/>',
        'pencil'      => '<path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>',
        'save'        => '<path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/>',
        'plus'        => '<path d="M5 12h14M12 5v14"/>',
        'check'       => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle'=> '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        'alert'       => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/>',
        'users'       => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'image'       => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
        'git-branch'  => '<path d="M6 3v12"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9"/>',
        'external'    => '<path d="M15 3h6v6M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'graduation'  => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
        'shield'      => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'star'        => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
        'user'        => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'lock'        => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'mail'        => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'eye'         => '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'     => '<path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>',
        'calendar'    => '<path d="M8 2v4M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
        'arrow-left'  => '<path d="m12 19-7-7 7-7M19 12H5"/>',
        'id-card'     => '<path d="M16 10h2M16 14h2M6.17 15a3 3 0 0 1 5.66 0"/><circle cx="9" cy="11" r="2"/><rect x="2" y="5" width="20" height="14" rx="2"/>',
        'trash'       => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/>',
        'camera'      => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"/><circle cx="12" cy="13" r="3"/>',
        'users-class' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
    ];
    $body = $paths[$name] ?? $paths['box'];
    return '<svg class="ico' . ($class ? ' ' . e($class) : '') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

/**
 * Ikon kategori (SVG). Nilai kolom `categories.icon` di database masih berupa emoji,
 * jadi di sini dipetakan ke ikon SVG — database tidak perlu diubah.
 */
function category_icon(?string $stored, string $fallback = 'box'): string
{
    static $map = [
        '🌐' => 'globe',      // Website
        '📱' => 'smartphone', // Mobile App
        '💻' => 'monitor',    // Desktop
        '🔌' => 'wifi',       // IoT
        '🤖' => 'cpu',        // AI
        '🎮' => 'gamepad',    // Game
        '📊' => 'database',   // Sistem Informasi
        '📦' => 'box',        // Lainnya
    ];
    $stored = trim((string) $stored);
    return icon($map[$stored] ?? $fallback);
}

/**
 * URL foto cover proyek (atau null jika belum ada)
 */
function project_cover(?array $project): ?string
{
    if (empty($project['thumbnail'])) return null;
    return '/uploads/projects/' . rawurlencode($project['thumbnail']);
}

/**
 * Markup thumbnail kartu proyek: foto cover atau fallback gradient + ikon kategori
 */
function project_thumb(array $project): string
{
    $cover = project_cover($project);
    if ($cover) {
        return '<img src="' . e($cover) . '" alt="' . e($project['title'] ?? '') . '" loading="lazy">';
    }
    return '<span class="thumb-icon">' . category_icon($project['category_icon'] ?? null) . '</span>';
}

function initials(?string $name): string
{
    $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: '?';
}


/**
 * Pesan untuk email yang domainnya tidak dikenal
 */
function email_domain_hint(): string
{
    return 'Gunakan email aktif, misalnya nama@gmail.com (Gmail, Yahoo, Outlook, iCloud, atau email kampus berakhiran .ac.id).';
}

/**
 * Hanya terima email dari penyedia umum atau email kampus (.ac.id),
 * sehingga alamat asal-asalan seperti andi@contoh.com ditolak.
 */
function email_domain_allowed(string $email): bool
{
    $at = strrpos($email, '@');
    if ($at === false) {
        return false;
    }
    $domain = strtolower(substr($email, $at + 1));

    $allowed = [
        'gmail.com', 'googlemail.com',
        'yahoo.com', 'yahoo.co.id', 'ymail.com',
        'outlook.com', 'outlook.co.id', 'hotmail.com', 'live.com', 'msn.com',
        'icloud.com', 'me.com',
        'proton.me', 'protonmail.com',
    ];

    if (in_array($domain, $allowed, true)) {
        return true;
    }
    if (substr($domain, -6) === '.ac.id') {
        return true;
    }
    // Akun demo bawaan hanya valid di lingkungan lokal
    if (getenv('APP_ENV') === 'local' && $domain === 'tifhub.local') {
        return true;
    }
    return false;
}
