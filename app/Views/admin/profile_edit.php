<?php
use App\Helpers\ImageUploader;

$avatarUrl = ImageUploader::avatarUrl($row['avatar'] ?? null);
$val = function (string $key, string $default = '') use ($old, $row): string {
    return e((string) ($old[$key] ?? $row[$key] ?? $default));
};
$err = function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<span class="ap-error">' . e($errors[$key]) . '</span>' : '';
};
$invalid = fn(string $key): string => isset($errors[$key]) ? ' is-invalid' : '';
?>
<style>
.ap-head { margin-bottom: 1.25rem; }
.ap-head h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -.5px; }
.ap-head p { margin-top: .25rem; color: var(--muted); }
.ap-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
.ap-card { background: var(--card); border: 1px solid var(--border); border-radius: 22px; box-shadow: var(--shadow-sm); overflow: hidden; }
.ap-avatar-card { padding: 1.6rem; text-align: center; }
.ap-avatar-cover { height: 72px; margin: -1.6rem -1.6rem 0; background: var(--grad); position: relative; }
.ap-avatar-wrap { width: 112px; height: 112px; margin: -56px auto 0; border-radius: 50%; border: 5px solid #fff; background: var(--grad); color: #fff; display: grid; place-items: center; font-size: 2rem; font-weight: 800; overflow: hidden; box-shadow: 0 12px 28px rgba(2,37,51,.18); position: relative; }
.ap-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ap-avatar-card h2 { margin-top: .8rem; font-size: 1.12rem; }
.ap-avatar-card p { color: var(--muted); font-size: .82rem; word-break: break-all; }
.ap-photo-actions { display: grid; gap: .55rem; margin-top: 1.1rem; }
.ap-photo-actions form { margin: 0; }
.ap-photo-actions .btn, .ap-photo-actions label { width: 100%; justify-content: center; }
.ap-photo-actions input[type=file] { display: none; }
.ap-photo-error { display: none; margin-top: .65rem; color: #b91c1c; background: #fee2e2; border-radius: 10px; padding: .55rem .7rem; font-size: .78rem; font-weight: 700; }
.ap-photo-error.is-show { display: block; }
.ap-crop-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 1rem; background: rgba(2,37,51,.62); backdrop-filter: blur(3px); }
.ap-crop-modal[hidden] { display: none; }
.ap-crop-dialog { width: 100%; max-width: 380px; background: #fff; border-radius: 22px; padding: 1.4rem; text-align: center; box-shadow: 0 30px 80px rgba(2,37,51,.4); animation: apCropIn .22s ease; }
.ap-crop-dialog h3 { font-size: 1.15rem; font-weight: 800; }
.ap-crop-sub { font-size: .83rem; color: var(--muted); margin-top: .25rem; }
.ap-crop-stage { position: relative; width: min(300px,100%); aspect-ratio: 1; margin: 1.1rem auto; overflow: hidden; border-radius: 16px; background: #0b1f2a; touch-action: none; cursor: grab; user-select: none; }
.ap-crop-stage.is-drag { cursor: grabbing; }
.ap-crop-stage img { position: absolute; max-width: none; user-select: none; pointer-events: none; }
.ap-crop-mask { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle closest-side at center, transparent 99%,rgba(2,37,51,.62) 100%); }
.ap-crop-mask::after { content: ""; position: absolute; inset: 0; border-radius: 50%; border: 2px solid rgba(255,255,255,.92); }
.ap-crop-zoom { display: flex; align-items: center; gap: .6rem; }
.ap-crop-zoom input { flex: 1; accent-color: var(--primary); }
.ap-crop-zoom button { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); background: #fff; color: var(--primary-dark); font-size: 1.15rem; cursor: pointer; }
.ap-crop-btns { display: flex; gap: .5rem; justify-content: center; margin-top: 1.1rem; }
.ap-crop-tip { font-size: .75rem; color: var(--muted); margin-top: .65rem; }
.ap-confirm-preview { width: 112px; height: 112px; margin: 0 auto .85rem; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 12px 28px rgba(2,37,51,.18); overflow: hidden; background: #eef6f9; }
.ap-confirm-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
@keyframes apCropIn { from { opacity: 0; transform: translateY(12px) scale(.96); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .ap-crop-dialog { animation: none; } }
.ap-form { padding: 1.35rem 1.5rem 1.5rem; }
.ap-form h2 { font-size: 1.08rem; margin-bottom: 1rem; }
.ap-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.ap-form .full { grid-column: 1 / -1; }
.ap-error { display: block; margin-top: .35rem; color: #b91c1c; font-size: .78rem; font-weight: 700; }
.form-control.is-invalid { border-color: #ef4444; background: #fffafa; }
.ap-actions { display: flex; justify-content: flex-end; gap: .65rem; margin-top: 1.15rem; padding-top: 1.15rem; border-top: 1px dashed var(--border); }
@media (max-width: 900px) { .ap-layout { grid-template-columns: 1fr; } .ap-avatar-card { max-width: none; } }
@media (max-width: 560px) { .ap-form-grid { grid-template-columns: 1fr; } .ap-form .full { grid-column: auto; } }
</style>

<div class="ap-head">
    <a href="/admin/profile" class="btn btn-outline btn-sm" style="margin-bottom:.8rem;">&larr; Kembali ke Profil</a>
    <h1>Edit Profil Administrator</h1>
    <p>Perbarui biodata dan foto profil akun administrator.</p>
</div>

<div class="ap-layout">
    <aside class="ap-card ap-avatar-card">
        <div class="ap-avatar-cover"></div>
        <div class="ap-avatar-wrap">
            <?php if ($avatarUrl): ?>
                <img src="<?= e($avatarUrl) ?>" alt="Foto profil <?= e($row['name']) ?>">
            <?php else: ?>
                <?= e(initials($row['name'] ?? 'Admin')) ?>
            <?php endif; ?>
        </div>
        <h2><?= e($row['name'] ?? 'Administrator') ?></h2>
        <p><?= e($row['email'] ?? '') ?></p>

        <div class="ap-photo-actions">
            <form method="POST" action="/admin/profile/avatar" enctype="multipart/form-data" id="ap-avatar-form">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <input type="file" id="ap-avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp">
                <label for="ap-avatar-input" class="btn btn-primary"><?= $avatarUrl ? 'Ganti Foto' : 'Tambah Foto' ?></label>
            </form>
            <p class="ap-photo-error" id="ap-avatar-error"></p>
            <?php if ($avatarUrl): ?>
            <form method="POST" action="/admin/profile/avatar/remove">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <button type="submit" class="btn btn-outline" onclick="return confirm('Hapus foto profil administrator?')">Hapus Foto</button>
            </form>
            <?php endif; ?>
        </div>
    </aside>

    <section class="ap-card ap-form">
        <h2>Informasi Biodata</h2>
        <form method="POST" action="/admin/profile/update">
            <input type="hidden" name="_token" value="<?= e($csrf) ?>">
            <div class="ap-form-grid">
                <div class="form-group full">
                    <label for="ap-name">Nama Lengkap</label>
                    <input type="text" id="ap-name" name="name" class="form-control<?= $invalid('name') ?>" value="<?= $val('name') ?>" maxlength="100" autocomplete="name" required>
                    <?= $err('name') ?>
                </div>
                <div class="form-group full">
                    <label for="ap-email">Email</label>
                    <input type="email" id="ap-email" name="email" class="form-control<?= $invalid('email') ?>" value="<?= $val('email') ?>" maxlength="150" autocomplete="email" required>
                    <?= $err('email') ?>
                </div>
                <div class="form-group full">
                    <label>Peran</label>
                    <input type="text" class="form-control" value="Administrator" readonly>
                </div>
            </div>
            <div class="ap-actions">
                <a href="/admin/profile" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </section>
</div>

<div class="ap-crop-modal" id="ap-crop-modal" hidden role="dialog" aria-modal="true" aria-labelledby="ap-crop-title">
    <div class="ap-crop-dialog">
        <h3 id="ap-crop-title">Atur Foto Profil</h3>
        <p class="ap-crop-sub">Geser foto dan atur zoom agar posisi wajah pas di lingkaran.</p>
        <div class="ap-crop-stage" id="ap-crop-stage">
            <img id="ap-crop-img" alt="" draggable="false">
            <div class="ap-crop-mask"></div>
        </div>
        <div class="ap-crop-zoom">
            <button type="button" id="ap-crop-zout" aria-label="Perkecil">&minus;</button>
            <input type="range" id="ap-crop-zoom" min="1" max="4" step="0.01" value="1" aria-label="Zoom">
            <button type="button" id="ap-crop-zin" aria-label="Perbesar">+</button>
        </div>
        <p class="ap-crop-tip">Geser foto dan gunakan zoom atau scroll mouse.</p>
        <div class="ap-crop-btns">
            <button type="button" class="btn btn-outline btn-sm" id="ap-crop-reset">Reset</button>
            <button type="button" class="btn btn-outline btn-sm" id="ap-crop-cancel">Batal</button>
            <button type="button" class="btn btn-primary btn-sm" id="ap-crop-apply">Lanjut</button>
        </div>
    </div>
</div>

<div class="ap-crop-modal" id="ap-confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="ap-confirm-title">
    <div class="ap-crop-dialog">
        <div class="ap-confirm-preview"><img id="ap-confirm-img" alt="Pratinjau foto profil baru"></div>
        <h3 id="ap-confirm-title">Pakai foto ini?</h3>
        <p class="ap-crop-sub">Foto ini akan disimpan sebagai foto profil administrator.</p>
        <div class="ap-crop-btns">
            <button type="button" class="btn btn-primary" id="ap-confirm-save">Ya, simpan foto</button>
            <button type="button" class="btn btn-outline" id="ap-confirm-again">Atur ulang</button>
            <button type="button" class="btn btn-outline" id="ap-confirm-cancel">Batal</button>
        </div>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('ap-avatar-input');
    var form = document.getElementById('ap-avatar-form');
    var errorBox = document.getElementById('ap-avatar-error');
    var modal = document.getElementById('ap-crop-modal');
    var stage = document.getElementById('ap-crop-stage');
    var cropImg = document.getElementById('ap-crop-img');
    var zoomInput = document.getElementById('ap-crop-zoom');
    var confirmModal = document.getElementById('ap-confirm-modal');
    var confirmImg = document.getElementById('ap-confirm-img');
    if (!input || !form || !modal || !confirmModal) return;

    var maxBytes = <?= (int) ImageUploader::avatarMaxBytes() ?>;
    var allowed = ['image/jpeg', 'image/png', 'image/webp'];
    var OUT = 512;
    var state = { w: 0, h: 0, size: 300, zoom: 1, cx: 0, cy: 0 };
    var sourceUrl = null;
    var previewUrl = null;

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.add('is-show');
    }
    function hideError() {
        errorBox.textContent = '';
        errorBox.classList.remove('is-show');
    }
    function openModal(target) {
        target.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeModal(target) {
        target.hidden = true;
        if (modal.hidden && confirmModal.hidden) document.body.style.overflow = '';
    }
    function syncInput(file) {
        try {
            var data = new DataTransfer();
            if (file) data.items.add(file);
            input.files = data.files;
        } catch (e) {
            if (!file) input.value = '';
        }
    }
    function resetAll() {
        syncInput(null);
        input.value = '';
        if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        sourceUrl = null;
        previewUrl = null;
    }
    function baseScale() {
        return state.size / Math.min(state.w, state.h);
    }
    function clamp() {
        var scale = baseScale() * state.zoom;
        var width = state.w * scale;
        var height = state.h * scale;
        state.cx = Math.min(width / 2, Math.max(state.size - width / 2, state.cx));
        state.cy = Math.min(height / 2, Math.max(state.size - height / 2, state.cy));
    }
    function render() {
        var scale = baseScale() * state.zoom;
        var width = state.w * scale;
        var height = state.h * scale;
        cropImg.style.width = width + 'px';
        cropImg.style.height = height + 'px';
        cropImg.style.left = (state.cx - width / 2) + 'px';
        cropImg.style.top = (state.cy - height / 2) + 'px';
        zoomInput.value = state.zoom;
    }
    function setZoom(value) {
        value = Math.min(4, Math.max(1, value));
        var ratio = (baseScale() * value) / (baseScale() * state.zoom);
        var center = state.size / 2;
        state.zoom = value;
        state.cx = center + (state.cx - center) * ratio;
        state.cy = center + (state.cy - center) * ratio;
        clamp();
        render();
    }
    function fit() {
        state.size = stage.clientWidth;
        state.zoom = 1;
        state.cx = state.size / 2;
        state.cy = state.size / 2;
        clamp();
        render();
    }
    function openCropper() {
        openModal(modal);
        cropImg.onload = function () {
            state.w = cropImg.naturalWidth;
            state.h = cropImg.naturalHeight;
            fit();
        };
        cropImg.src = sourceUrl;
        if (cropImg.complete && cropImg.naturalWidth) {
            state.w = cropImg.naturalWidth;
            state.h = cropImg.naturalHeight;
            fit();
        }
    }
    function cancelAll() {
        closeModal(modal);
        closeModal(confirmModal);
        resetAll();
        hideError();
    }

    var drag = null;
    stage.addEventListener('pointerdown', function (event) {
        drag = { x: event.clientX, y: event.clientY, cx: state.cx, cy: state.cy };
        stage.setPointerCapture(event.pointerId);
        stage.classList.add('is-drag');
    });
    stage.addEventListener('pointermove', function (event) {
        if (!drag) return;
        state.cx = drag.cx + (event.clientX - drag.x);
        state.cy = drag.cy + (event.clientY - drag.y);
        clamp();
        render();
    });
    function endDrag() {
        drag = null;
        stage.classList.remove('is-drag');
    }
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);
    stage.addEventListener('wheel', function (event) {
        event.preventDefault();
        setZoom(state.zoom + (event.deltaY < 0 ? 0.1 : -0.1));
    }, { passive: false });
    zoomInput.addEventListener('input', function () { setZoom(parseFloat(zoomInput.value)); });
    document.getElementById('ap-crop-zin').addEventListener('click', function () { setZoom(state.zoom + 0.2); });
    document.getElementById('ap-crop-zout').addEventListener('click', function () { setZoom(state.zoom - 0.2); });
    document.getElementById('ap-crop-reset').addEventListener('click', fit);
    document.getElementById('ap-crop-cancel').addEventListener('click', cancelAll);
    window.addEventListener('resize', function () { if (!modal.hidden && state.w) fit(); });

    function toBlob(canvas, quality) {
        return new Promise(function (resolve) { canvas.toBlob(resolve, 'image/jpeg', quality); });
    }
    document.getElementById('ap-crop-apply').addEventListener('click', async function () {
        var canvas = document.createElement('canvas');
        canvas.width = OUT;
        canvas.height = OUT;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, OUT, OUT);
        ctx.imageSmoothingQuality = 'high';
        var scale = baseScale() * state.zoom;
        var sourceSize = state.size / scale;
        var sourceX = state.w / 2 - state.cx / scale;
        var sourceY = state.h / 2 - state.cy / scale;
        ctx.drawImage(cropImg, sourceX, sourceY, sourceSize, sourceSize, 0, 0, OUT, OUT);

        var blob = null;
        var quality = 0.9;
        while (quality >= 0.5) {
            blob = await toBlob(canvas, quality);
            if (blob && blob.size <= maxBytes) break;
            quality -= 0.1;
        }
        if (!blob || blob.size > maxBytes) {
            cancelAll();
            showError('Foto gagal diproses. Coba gunakan foto lain.');
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(blob);
        confirmImg.src = previewUrl;
        syncInput(new File([blob], 'avatar.jpg', { type: 'image/jpeg' }));
        closeModal(modal);
        openModal(confirmModal);
    });

    document.getElementById('ap-confirm-save').addEventListener('click', function () {
        var save = document.getElementById('ap-confirm-save');
        save.disabled = true;
        save.textContent = 'Menyimpan…';
        form.submit();
    });
    document.getElementById('ap-confirm-again').addEventListener('click', function () {
        closeModal(confirmModal);
        openCropper();
    });
    document.getElementById('ap-confirm-cancel').addEventListener('click', cancelAll);

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        hideError();
        if (allowed.indexOf(file.type) === -1) {
            resetAll();
            showError('Format foto harus JPG, PNG, atau WEBP.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            resetAll();
            showError('Foto asli terlalu besar (maksimal 10 MB).');
            return;
        }
        if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        sourceUrl = URL.createObjectURL(file);
        openCropper();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            if (!confirmModal.hidden || !modal.hidden) cancelAll();
        }
    });
})();
</script>
