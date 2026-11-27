<?php
use App\Helpers\ImageUploader;
$studentAvatar = ImageUploader::avatarUrl($student['avatar'] ?? null);
?>
<a href="/admin/students" class="back-link">← Kembali ke daftar mahasiswa</a>

<div class="page-head mt-2">
    <div>
        <h1>Detail Mahasiswa</h1>
        <p>Informasi akun mahasiswa (hanya lihat).</p>
    </div>
</div>

<div class="card" style="max-width:720px;">
    <div class="cell-project mb-2">
        <span class="avatar">
            <?php if ($studentAvatar): ?>
                <img src="<?= e($studentAvatar) ?>" alt="Foto <?= e($student['name']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
            <?php else: ?>
                <?= e(initials($student['name'])) ?>
            <?php endif; ?>
        </span>
        <strong><?= e($student['name']) ?></strong>
    </div>
    <div class="table-wrap">
        <table>
            <tbody>
                <tr><th>NIM</th><td><?= e($student['nim'] ?? '-') ?></td></tr>
                <tr><th>Email</th><td><?= e($student['email']) ?></td></tr>
                <tr><th>Kelas</th><td><?= e($student['class'] ?? '-') ?></td></tr>
                <tr><th>Semester</th><td><?= e((string) ($student['semester'] ?? '-')) ?></td></tr>
                <tr><th>Status</th><td><?= status_badge($student['status']) ?></td></tr>
                <tr><th>Terdaftar</th><td><?= e(format_datetime($student['created_at'] ?? null)) ?></td></tr>
            </tbody>
        </table>
    </div>
</div>
