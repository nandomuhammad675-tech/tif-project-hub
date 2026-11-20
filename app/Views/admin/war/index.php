<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:1rem;">Project War</h1>

<div class="stats-grid mb-3">
    <div class="stat-card">
        <div class="label">Status</div>
        <div class="value" style="font-size:1rem;">
            <?php
            $map = ['active'=>'● Aktif','scheduled'=>'⏳ Terjadwal','ended'=>'Selesai','closed'=>'Ditutup'];
            echo e($map[$warStatus ?? 'closed'] ?? 'Ditutup');
            ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="label">Tersedia diklaim</div>
        <div class="value primary"><?= (int)$availableCount ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Sudah diklaim</div>
        <div class="value"><?= (int)$claimedCount ?></div>
    </div>
</div>

<div class="card mb-3">
    <h2 class="card-title">Pengaturan War</h2>
    <form method="POST" action="/admin/war">
        <input type="hidden" name="_token" value="<?= e($csrf) ?>">
        <div class="form-group mb-2">
            <label>Semester aktif</label>
            <select name="active_semester_id" class="form-control" required>
                <option value="">— Pilih semester —</option>
                <?php foreach ($semesters as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= ((int)($war['active_semester_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                    <?= e($s['name']) ?><?= !empty($s['academic_year']) ? ' · ' . e($s['academic_year']) : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-2">
            <label>Buka war</label>
            <select name="is_open" class="form-control">
                <option value="0" <?= empty($war['is_open']) ? 'selected' : '' ?>>Ditutup</option>
                <option value="1" <?= !empty($war['is_open']) ? 'selected' : '' ?>>Dibuka</option>
            </select>
            <p class="text-sm text-muted">Selain flag ini, klaim hanya valid di antara waktu mulai dan berakhir (waktu server).</p>
        </div>
        <div class="form-group mb-2">
            <label>Waktu mulai</label>
            <input type="datetime-local" name="starts_at" class="form-control"
                   value="<?= !empty($war['starts_at']) ? e(date('Y-m-d\TH:i', strtotime($war['starts_at']))) : '' ?>">
        </div>
        <div class="form-group mb-2">
            <label>Waktu berakhir</label>
            <input type="datetime-local" name="ends_at" class="form-control"
                   value="<?= !empty($war['ends_at']) ? e(date('Y-m-d\TH:i', strtotime($war['ends_at']))) : '' ?>">
        </div>
        <button type="submit" class="btn btn-primary">Simpan pengaturan</button>
    </form>
</div>

<p class="text-sm">
    <a href="/admin/claims">Lihat semua tiket klaim →</a>
    &nbsp;·&nbsp;
    <a href="/admin/violations">Lihat pelanggaran klaim →</a>
</p>
