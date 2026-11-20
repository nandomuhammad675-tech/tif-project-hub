<a href="/profile" class="back-link">← Kembali ke Profil</a>

<div class="card mt-2" style="max-width:520px;margin-left:auto;margin-right:auto;border:2px dashed rgba(3,121,146,.35);">
    <div style="text-align:center;margin-bottom:1rem;">
        <div style="font-size:2rem;">🎫</div>
        <h1 style="font-size:1.25rem;font-weight:800;margin:.25rem 0;">Tiket Digital Proyek</h1>
        <p class="text-muted text-sm">TIF Hub · Project War</p>
    </div>

    <table style="width:100%;font-size:.95rem;border-collapse:collapse;">
        <tr><td class="text-muted" style="padding:.45rem 0;width:42%;">ID Tiket</td>
            <td style="padding:.45rem 0;"><code style="font-weight:700;"><?= e($claim['ticket_code']) ?></code></td></tr>
        <tr><td class="text-muted" style="padding:.45rem 0;">Nama mahasiswa</td>
            <td style="padding:.45rem 0;"><?= e($claim['student_name'] ?? $user['name']) ?></td></tr>
        <tr><td class="text-muted" style="padding:.45rem 0;">NIM</td>
            <td style="padding:.45rem 0;"><?= e($claim['student_nim'] ?? $user['nim'] ?? '-') ?></td></tr>
        <tr><td class="text-muted" style="padding:.45rem 0;">Semester</td>
            <td style="padding:.45rem 0;"><?= e($claim['semester_name'] ?? '-') ?><?= !empty($claim['academic_year']) ? ' · ' . e($claim['academic_year']) : '' ?></td></tr>
        <tr><td class="text-muted" style="padding:.45rem 0;">Nama proyek</td>
            <td style="padding:.45rem 0;font-weight:600;"><?= e($claim['project_title']) ?></td></tr>
        <?php if (!empty($claim['category_name'])): ?>
        <tr><td class="text-muted" style="padding:.45rem 0;">Kategori</td>
            <td style="padding:.45rem 0;"><?= e($claim['category_name']) ?></td></tr>
        <?php endif; ?>
        <tr><td class="text-muted" style="padding:.45rem 0;">Tanggal & waktu klaim</td>
            <td style="padding:.45rem 0;"><?= e(format_datetime($claim['claimed_at'])) ?></td></tr>
        <tr><td class="text-muted" style="padding:.45rem 0;">Status</td>
            <td style="padding:.45rem 0;">
                <?php if (($claim['status'] ?? '') === 'claimed'): ?>
                    <span class="badge badge-success">Terklaim</span>
                <?php else: ?>
                    <span class="badge badge-secondary"><?= e($claim['status']) ?></span>
                <?php endif; ?>
            </td></tr>
    </table>

    <p class="text-sm text-muted mt-2" style="text-align:center;">
        Tiket ini tidak dapat dihapus atau diganti sendiri oleh mahasiswa.
    </p>
</div>
