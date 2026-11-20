<a href="/" class="back-link">← Kembali ke Beranda</a>

<h1 style="font-size:1.35rem;font-weight:800;margin:1rem 0;">Pilih Proyek untuk Diklaim</h1>
<p class="text-muted text-sm mb-2">
    Semester aktif: <strong><?= e($war['semester_name'] ?? '-') ?></strong>
    <?php if (!empty($war['academic_year'])): ?> · <?= e($war['academic_year']) ?><?php endif; ?>
</p>
<p class="text-sm mb-3">Pilih <strong>satu</strong> proyek yang masih tersedia. Tiket hanya diterbitkan setelah server berhasil menyimpan klaim.</p>

<?php if (empty($projects)): ?>
<div class="empty-state">
    <div class="icon">📭</div>
    <p>Tidak ada proyek tersedia untuk diklaim saat ini.</p>
</div>
<?php else: ?>
<form method="POST" action="/claims" id="form-claim-select" onsubmit="return onClaimSubmit(this)">
    <input type="hidden" name="_token" value="<?= e($csrf) ?>">
    <div class="project-grid" style="margin-bottom:1.25rem;">
        <?php foreach ($projects as $p): ?>
        <label class="project-card" style="cursor:pointer;display:block;">
            <div class="thumb">
                <?= project_thumb($p) ?>
                <span class="thumb-status"><span class="badge badge-glass" style="color:#065f46">● Tersedia</span></span>
            </div>
            <div class="body">
                <div class="badges">
                    <span class="badge badge-primary"><?= e($p['category_name'] ?? 'Lainnya') ?></span>
                    <span class="badge badge-secondary"><?= e($p['semester_name'] ?? '-') ?></span>
                </div>
                <h3 style="margin:.4rem 0;"><?= e($p['title']) ?></h3>
                <p class="desc"><?= e($p['short_desc'] ?: truncate($p['description'] ?? '', 90)) ?></p>
                <?php if (!empty($p['course_name'])): ?>
                <p class="text-sm text-muted">MK: <?= e($p['course_name']) ?></p>
                <?php endif; ?>
                <div class="tech"><?= tech_tags($p['technologies'] ?? '') ?></div>
                <div class="actions" style="margin-top:.75rem;">
                    <span class="flex gap-1" style="align-items:center;">
                        <input type="radio" name="project_id" value="<?= (int)$p['id'] ?>" required style="width:1.1rem;height:1.1rem;">
                        <span class="text-sm">Pilih proyek ini</span>
                    </span>
                </div>
            </div>
        </label>
        <?php endforeach; ?>
    </div>
    <div class="card" style="position:sticky;bottom:1rem;z-index:5;">
        <button type="submit" class="btn btn-primary btn-block" id="btn-confirm-claim">Konfirmasi Klaim Proyek</button>
        <p class="text-sm text-muted mt-1" style="text-align:center;">Setelah dikonfirmasi, pilihan tidak dapat diganti sendiri.</p>
    </div>
</form>
<script>
function onClaimSubmit(form) {
  var btn = document.getElementById('btn-confirm-claim');
  if (btn.dataset.loading === '1') return false;
  if (!form.querySelector('input[name=project_id]:checked')) {
    alert('Pilih satu proyek terlebih dahulu.');
    return false;
  }
  if (!confirm('Konfirmasi klaim proyek ini? Anda hanya boleh satu proyek per semester.')) return false;
  btn.dataset.loading = '1';
  btn.disabled = true;
  btn.textContent = 'Memproses klaim…';
  return true;
}
</script>
<?php endif; ?>
