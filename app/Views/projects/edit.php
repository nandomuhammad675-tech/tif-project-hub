<?php
$images = $images ?? [];
$limits = $limits ?? \App\Helpers\ImageUploader::limits();
$fmtMb = fn(int $b): string => $b >= 1048576 ? rtrim(rtrim(number_format($b / 1048576, 1), '0'), '.') . ' MB' : round($b / 1024) . ' KB';
$hasCover = false;
foreach ($images as $im) {
    if ((int) $im['is_cover'] === 1) {
        $hasCover = true;
        break;
    }
}
?>
<a href="/projects/<?= e($project['slug']) ?>" class="back-link">← Kembali ke Detail Proyek</a>
<div class="page-head mt-2">
    <div>
        <h1>Edit Proyek</h1>
        <p>Perbarui informasi dan foto proyek yang telah Anda klaim.</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="/projects/<?= (int) $project['id'] ?>/update">
    <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">

    <div class="card mb-3">
        <div class="form-section">
            <h3>Informasi Proyek</h3>
            <div class="form-group">
                <label>Judul Proyek *</label>
                <input type="text" name="title" class="form-control" required maxlength="200" value="<?= e($project['title'] ?? '') ?>" placeholder="Judul Proyek">
            </div>
            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <input type="text" name="short_desc" class="form-control" maxlength="300" value="<?= e($project['short_desc'] ?? '') ?>" placeholder="Ringkasan proyek">
            </div>
            <div class="form-group">
                <label>Deskripsi Lengkap *</label>
                <textarea name="description" class="form-control" rows="6" required><?= e($project['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Foto Proyek</h3>
            <?php if (!empty($images)): ?>
            <div class="subhead">Foto saat ini (<?= count($images) ?>)</div>
            <span class="form-hint" style="margin-bottom:.6rem;">Pilih satu foto sebagai <b>cover</b> (tampil di katalog dan selalu pertama di galeri). Geser foto atau pakai tombol ‹ › untuk mengatur urutan. Centang <b>Hapus</b> untuk membuang foto — semua berlaku setelah menekan Simpan.</span>
            <div class="img-grid">
                <?php foreach ($images as $i => $im):
                    $isCover = (int) $im['is_cover'] === 1 || (!$hasCover && $i === 0);
                ?>
                <div class="img-tile <?= $isCover ? 'is-cover' : '' ?>" data-existing draggable="true">
                    <input type="hidden" name="image_order[]" value="<?= (int) $im['id'] ?>">
                    <span class="cover-flag">COVER</span>
                    <div class="mv-group">
                        <button type="button" class="mv-btn" data-move="-1" aria-label="Geser ke kiri" title="Geser ke kiri">‹</button>
                        <button type="button" class="mv-btn" data-move="1" aria-label="Geser ke kanan" title="Geser ke kanan">›</button>
                    </div>
                    <div class="img-box"><img src="<?= e(\App\Helpers\ImageUploader::url($im['filename'])) ?>" alt="Foto proyek" loading="lazy"></div>
                    <div class="tile-bar">
                        <label><input type="radio" name="cover_image" value="<?= (int) $im['id'] ?>" <?= $isCover ? 'checked' : '' ?>> Cover</label>
                        <label style="color:var(--danger);"><input type="checkbox" name="delete_images[]" value="<?= (int) $im['id'] ?>"> Hapus</label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="dropzone" id="dropzone" data-existing="<?= count($images) ?>"
                 data-max-file="<?= (int) $limits['file'] ?>" data-max-post="<?= (int) $limits['post'] ?>" data-max-count="<?= (int) $limits['count'] ?>">
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                <div class="dz-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                </div>
                <h4>Seret &amp; lepas foto di sini</h4>
                <p>atau <b>klik untuk memilih</b> · JPG, PNG, WEBP, GIF · maks <?= e($fmtMb((int) $limits['file'])) ?>/foto · hingga <?= (int) $limits['count'] ?> foto</p>
            </div>
            <div class="img-grid" id="newImages"></div>
            <div class="upload-note"><span id="uploadNote" role="status" aria-live="polite"></span><span>Foto otomatis dikecilkan agar upload cepat<?= $limits['post'] > 0 ? ' · total maks ' . e($fmtMb((int) $limits['post'])) . ' per simpan' : '' ?></span></div>
            <?php if (empty($images)): ?>
            <span class="form-hint">Foto pertama yang diunggah otomatis menjadi cover. Anda bisa mengubahnya nanti.</span>
            <?php endif; ?>
        </div>

        <div class="form-section">
            <h3>Teknologi &amp; Tautan</h3>
            <div class="form-group">
                <label>Teknologi (pisahkan dengan koma)</label>
                <input type="text" name="technologies" class="form-control" maxlength="500" value="<?= e($project['technologies'] ?? '') ?>" placeholder="PHP, MySQL, JavaScript">
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Repository GitHub</label>
                    <input type="url" name="github_url" class="form-control" maxlength="500" value="<?= e($project['github_url'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
                <div class="form-group">
                    <label>Link Demo</label>
                    <input type="url" name="demo_url" class="form-control" maxlength="500" value="<?= e($project['demo_url'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>
        </div>

        <div class="form-actions mt-3">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="/projects/<?= e($project['slug']) ?>" class="btn btn-outline">Batal</a>
        </div>
    </div>
</form>
