<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:1rem;">Pelanggaran Klaim</h1>
<p class="text-sm text-muted mb-2">Percobaan klaim kedua dicatat di sini. Pengurangan nilai hanya setelah Anda setujui.</p>

<form method="GET" class="mb-2">
    <select name="status" class="form-control" style="max-width:220px;" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Disetujui (ada sanksi)</option>
        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Ditolak (tanpa sanksi)</option>
    </select>
</form>

<div class="card" style="overflow-x:auto;">
<?php if (empty($violations)): ?>
<p class="text-muted">Tidak ada catatan pelanggaran.</p>
<?php else: ?>
<table style="width:100%;border-collapse:collapse;font-size:.88rem;">
    <thead>
        <tr style="text-align:left;border-bottom:1px solid #e2e8f0;">
            <th style="padding:.5rem;">Waktu</th>
            <th style="padding:.5rem;">Mahasiswa</th>
            <th style="padding:.5rem;">Proyek (target)</th>
            <th style="padding:.5rem;">Semester</th>
            <th style="padding:.5rem;">Alasan</th>
            <th style="padding:.5rem;">Status</th>
            <th style="padding:.5rem;">Tinjau</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($violations as $v): ?>
        <tr style="border-bottom:1px solid #f1f5f9;vertical-align:top;">
            <td style="padding:.5rem;"><?= e(format_datetime($v['attempted_at'])) ?></td>
            <td style="padding:.5rem;"><?= e($v['student_name']) ?><br><span class="text-muted text-sm"><?= e($v['student_nim'] ?? '') ?></span></td>
            <td style="padding:.5rem;"><?= e($v['project_title'] ?? '-') ?></td>
            <td style="padding:.5rem;"><?= e($v['semester_name'] ?? '-') ?></td>
            <td style="padding:.5rem;"><?= e($v['reason']) ?></td>
            <td style="padding:.5rem;">
                <span class="badge <?= $v['status']==='pending'?'badge-warning':($v['status']==='approved'?'badge-danger':'badge-secondary') ?>">
                    <?= e($v['status']) ?>
                </span>
                <?php if ($v['penalty_points'] !== null): ?>
                    <div class="text-sm">−<?= e((string)$v['penalty_points']) ?> poin</div>
                <?php endif; ?>
            </td>
            <td style="padding:.5rem;min-width:200px;">
                <?php if ($v['status'] === 'pending'): ?>
                <form method="POST" action="/admin/violations/<?= (int)$v['id'] ?>/review" style="display:grid;gap:.35rem;">
                    <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                    <input type="number" step="0.01" name="penalty_points" class="form-control" placeholder="Poin pengurangan">
                    <input type="text" name="admin_note" class="form-control" placeholder="Catatan admin">
                    <div class="flex gap-1">
                        <button type="submit" name="status" value="approved" class="btn btn-primary btn-sm">Setujui sanksi</button>
                        <button type="submit" name="status" value="rejected" class="btn btn-outline btn-sm">Tolak (abaikan)</button>
                    </div>
                </form>
                <?php else: ?>
                    <span class="text-sm text-muted"><?= e($v['reviewer_name'] ?? '') ?> · <?= e(format_datetime($v['reviewed_at'] ?? null)) ?></span>
                    <?php if (!empty($v['admin_note'])): ?><div class="text-sm"><?= e($v['admin_note']) ?></div><?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
