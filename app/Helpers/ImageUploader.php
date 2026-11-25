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
    public const AVATAR_MAX_SIZE = 2 * 1024 * 1024; // 2 MB untuk foto profil

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

    // ------------------------------------------------------------
    // Foto profil (avatar)
    // ------------------------------------------------------------

    public static function avatarDir(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/avatars';
    }

    public static function avatarUrl(?string $filename): ?string
    {
        return $filename ? '/uploads/avatars/' . rawurlencode(basename($filename)) : null;
    }

    /** Batas ukuran foto profil yang berlaku (byte): 2 MB atau batas PHP, mana yang lebih kecil */
    public static function avatarMaxBytes(): int
    {
        $perFile = self::iniBytes('upload_max_filesize');
        return $perFile > 0 ? min(self::AVATAR_MAX_SIZE, $perFile) : self::AVATAR_MAX_SIZE;
    }

    /**
     * Simpan satu foto profil dari <input type="file" name="avatar">
     *
     * @return array{filename: ?string, error: ?string}
     */
    public static function saveAvatar(string $field): array
    {
        if (empty($_FILES[$field]) || is_array($_FILES[$field]['name'])) {
            return ['filename' => null, 'error' => 'Pilih foto terlebih dahulu.'];
        }

        $f   = $_FILES[$field];
        $err = (int) $f['error'];

        if ($err === UPLOAD_ERR_NO_FILE) {
            return ['filename' => null, 'error' => 'Pilih foto terlebih dahulu.'];
        }
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return ['filename' => null, 'error' => 'Foto terlalu besar (maksimal ' . round(self::avatarMaxBytes() / 1048576, 1) . ' MB).'];
        }
        if ($err !== UPLOAD_ERR_OK) {
            return ['filename' => null, 'error' => "Foto gagal diunggah (kode {$err})."];
        }

        $tmp = (string) $f['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            return ['filename' => null, 'error' => 'Berkas tidak valid.'];
        }
        if ((int) $f['size'] > self::avatarMaxBytes()) {
            return ['filename' => null, 'error' => 'Foto lebih dari ' . round(self::avatarMaxBytes() / 1048576, 1) . ' MB.'];
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime]) || @getimagesize($tmp) === false) {
            return ['filename' => null, 'error' => 'Format foto harus JPG, PNG, atau WEBP.'];
        }

        $dir = self::avatarDir();
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['filename' => null, 'error' => 'Folder foto profil tidak bisa dibuat.'];
        }

        $filename = 'u' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($tmp, $dir . '/' . $filename)) {
            return ['filename' => null, 'error' => 'Foto gagal disimpan.'];
        }

        return ['filename' => $filename, 'error' => null];
    }

    public static function deleteAvatar(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = self::avatarDir() . '/' . basename($filename);
        if (is_file($path)) {
            @unlink($path);
        }
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