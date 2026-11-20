<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:1rem;">Tiket Klaim Proyek</h1>

<form method="GET" class="flex gap-1 mb-2" style="flex-wrap:wrap;align-items:end;">
    <div>
        <label class="text-sm">Semester</label>
        <select name="semester_id" class="form-control" onchange="this.form.submit()">
            <option value="">Semua</option>
            <?php foreach ($semesters as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= ((string)$semesterId === (string)$s['id']) ? 'selected' : '' ?>>
                <?= e($s['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="card" style="overflow-x:auto;">
<?php if (empty($claims)): ?>
<p class="text-muted">Belum ada tiket klaim.</p>
<?php else: ?>
<table style="width:100%;border-collapse:collapse;font-size:.9rem;">
    <thead>
        <tr style="text-align:left;border-bottom:1px solid #e2e8f0;">
            <th style="padding:.5rem;">Kode</th>
            <th style="padding:.5rem;">Mahasiswa</th>
            <th style="padding:.5rem;">NIM</th>
            <th style="padding:.5rem;">Proyek</th>
            <th style="padding:.5rem;">Semester</th>
            <th style="padding:.5rem;">Waktu</th>
            <th style="padding:.5rem;">Status</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($claims as $c): ?>
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:.5rem;"><code><?= e($c['ticket_code']) ?></code></td>
            <td style="padding:.5rem;"><?= e($c['student_name']) ?></td>
            <td style="padding:.5rem;"><?= e($c['student_nim'] ?? '-') ?></td>
            <td style="padding:.5rem;"><?= e($c['project_title']) ?></td>
            <td style="padding:.5rem;"><?= e($c['semester_name'] ?? '-') ?></td>
            <td style="padding:.5rem;"><?= e(format_datetime($c['claimed_at'])) ?></td>
            <td style="padding:.5rem;"><span class="badge badge-success"><?= e($c['status']) ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
