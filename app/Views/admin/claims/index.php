<?php
/**
 * @var array  $claims
 * @var array  $semesters
 * @var string $semesterId
 */
?>
<div class="page-head">
    <div>
        <h1>Tiket Klaim</h1>
        <p><?= count($claims) ?> tiket klaim</p>
    </div>
    <a href="/admin/war" class="btn btn-outline">Pengaturan Project War</a>
</div>

<form method="GET" class="mb-2">
    <label for="semester_id" class="text-sm text-muted">Filter semester</label>
    <select id="semester_id" name="semester_id" class="form-control" style="max-width:320px;" onchange="this.form.submit()">
        <option value="">Semua semester</option>
        <?php foreach ($semesters as $semester): ?>
        <option value="<?= (int) $semester['id'] ?>" <?= (string) $semester['id'] === $semesterId ? 'selected' : '' ?>>
            <?= e($semester['name']) ?><?= !empty($semester['academic_year']) ? ' · ' . e($semester['academic_year']) : '' ?>
        </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (empty($claims)): ?>
<div class="empty-state">
    <div class="icon"><?= icon('ticket') ?></div>
    <p><strong>Belum ada tiket klaim.</strong></p>
    <p class="text-sm">Tiket akan muncul di sini setelah mahasiswa berhasil mengklaim proyek.</p>
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Kode tiket</th>
                <th>Mahasiswa</th>
                <th>Proyek</th>
                <th>Semester</th>
                <th>Waktu klaim</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($claims as $claim): ?>
            <tr>
                <td><strong><?= e($claim['ticket_code']) ?></strong></td>
                <td>
                    <?= e($claim['student_name']) ?><br>
                    <small class="text-muted"><?= e($claim['student_nim'] ?? '-') ?></small>
                </td>
                <td><?= e($claim['project_title']) ?></td>
                <td><?= e($claim['semester_name']) ?><?= !empty($claim['semester_code']) ? ' · ' . e($claim['semester_code']) : '' ?></td>
                <td><?= e(format_datetime($claim['claimed_at'])) ?></td>
                <td>
                    <?php if ($claim['status'] === 'claimed'): ?>
                        <span class="badge badge-primary">Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Dicabut</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
