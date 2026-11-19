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
    return '/assets/' . ltrim($path, '/');
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

function category_icon(?string $icon, string $fallback = '📦'): string
{
    return $icon ?: $fallback;
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
 * Markup thumbnail kartu proyek: foto cover atau fallback gradient + emoji kategori
 */
function project_thumb(array $project): string
{
    $cover = project_cover($project);
    if ($cover) {
        return '<img src="' . e($cover) . '" alt="' . e($project['title'] ?? '') . '" loading="lazy">';
    }
    return '<span class="thumb-icon">' . e(category_icon($project['category_icon'] ?? null)) . '</span>';
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
