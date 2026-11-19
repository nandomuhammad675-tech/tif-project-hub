<?php

namespace App\Helpers;

/**
 * Upload gambar yang aman:
 * - cek MIME asli lewat finfo (bukan dari nama file)
 * - cek benar-benar gambar lewat getimagesize
 * - nama file diacak, ekstensi ditentukan server
 */
class ImageUploader
{
    public const MAX_SIZE = 5 * 1024 * 1024; // 5 MB per foto
    public const MAX_FILES = 10;             // maksimal foto per upload

    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Ubah nilai ini PHP ("2M", "8M", "1G") menjadi byte
     */
    public static function iniBytes(string $key): int
    {
        $v = trim((string) ini_get($key));
        if ($v === '') {
            return 0;
        }
        $num = (float) $v;
        switch (strtolower(substr($v, -1))) {
            case 'g': $num *= 1024;
            // no break
            case 'm': $num *= 1024;
            // no break
            case 'k': $num *= 1024;
        }
        return max(0, (int) $num);
    }

    /**
     * Batas upload yang berlaku di server ini (dipakai form + JS)
     *
     * @return array{file: int, post: int, count: int}
     */
    public static function limits(): array
    {
        $perFile = self::iniBytes('upload_max_filesize');
        $post    = self::iniBytes('post_max_size'); // 0 = tanpa batas

        return [
            'file'  => $perFile > 0 ? min(self::MAX_SIZE, $perFile) : self::MAX_SIZE,
            'post'  => $post,
            'count' => self::MAX_FILES,
        ];
    }

    public static function dir(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/projects';
    }

    public static function url(string $filename): string
    {
        return '/uploads/projects/' . rawurlencode($filename);
    }

    /**
     * Simpan semua file dari input <input type="file" name="images[]" multiple>
     *
     * @return array{saved: string[], errors: string[]}
     */
    public static function saveMany(string $field): array
    {
        $saved = [];
        $errors = [];

        if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
            return ['saved' => [], 'errors' => []];
        }

        $dir = self::dir();
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['saved' => [], 'errors' => ['Folder upload tidak bisa dibuat.']];
        }

        $f = $_FILES[$field];
        $count = count($f['name']);

        for ($i = 0; $i < $count; $i++) {
            $name = (string) $f['name'][$i];
            $err  = (int) $f['error'][$i];

            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (count($saved) >= self::MAX_FILES) {
                $errors[] = 'Maksimal ' . self::MAX_FILES . ' foto per sekali simpan.';
                break;
            }
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                $errors[] = "\"{$name}\" terlalu besar (batas server " . ini_get('upload_max_filesize') . ').';
                continue;
            }
            if ($err !== UPLOAD_ERR_OK) {
                $errors[] = "\"{$name}\" gagal diunggah (kode {$err}).";
                continue;
            }

            $tmp = (string) $f['tmp_name'][$i];
            if (!is_uploaded_file($tmp)) {
                continue;
            }
            if ((int) $f['size'][$i] > self::MAX_SIZE) {
                $errors[] = "\"{$name}\" lebih dari 5 MB.";
                continue;
            }

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
            if (!isset(self::ALLOWED[$mime]) || @getimagesize($tmp) === false) {
                $errors[] = "\"{$name}\" bukan gambar yang valid (JPG, PNG, WEBP, GIF).";
                continue;
            }

            $filename = bin2hex(random_bytes(12)) . '.' . self::ALLOWED[$mime];
            if (!move_uploaded_file($tmp, $dir . '/' . $filename)) {
                $errors[] = "\"{$name}\" gagal disimpan.";
                continue;
            }
            $saved[] = $filename;
        }

        return ['saved' => $saved, 'errors' => $errors];
    }

    public static function delete(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        // basename() mencegah path traversal
        $path = self::dir() . '/' . basename($filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
