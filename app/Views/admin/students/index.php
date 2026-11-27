<?php use App\Helpers\ImageUploader; ?>
<style>
.student-toolbar { display:flex; align-items:center; justify-content:flex-end; gap:.55rem; margin:-.3rem 0 1rem; color:var(--muted); font-size:.82rem; font-weight:700; }
.student-toolbar select { width:auto; min-width:78px; height:38px; padding:.35rem 2rem .35rem .7rem; border:1px solid var(--border); border-radius:10px; background:#fff; color:var(--text); font:inherit; font-weight:700; }
.student-status-action { min-width:108px; justify-content:center; }
.student-status-action.is-active { color:#047857; border-color:#a7f3d0; background:#ecfdf5; }
.student-status-action.is-inactive { color:#be123c; border-color:#fecdd3; background:#fff1f2; }
.student-pagination { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:.9rem; color:var(--muted); font-size:.82rem; }
.student-pages { display:flex; align-items:center; gap:.35rem; }
.student-pages a { min-width:36px; height:36px; display:grid; place-items:center; border:1px solid var(--border); border-radius:9px; background:#fff; color:var(--text); font-weight:700; }
.student-pages a.active { border-color:var(--primary); background:var(--primary); color:#fff; }
.student-pages a.disabled { pointer-events:none; opacity:.45; }
@media (max-width:640px) { .student-pagination { align-items:flex-start; flex-direction:column; } }
</style>
<div class="page-head">
    <div><h1>Kelola Mahasiswa</h1><p><?= (int) $totalStudents ?> mahasiswa terdaftar</p></div>
</div>

<form class="student-toolbar" method="GET" action="/admin/students" id="student-page-size">
    <label for="per-page">Tampilkan</label>
    <select id="per-page" name="per_page" onchange="this.form.submit()">
        <option value="10" <?= $perPage === '10' ? 'selected' : '' ?>>10</option>
        <option value="50" <?= $perPage === '50' ? 'selected' : '' ?>>50</option>
        <option value="100" <?= $perPage === '100' ? 'selected' : '' ?>>100</option>
        <option value="all" <?= $perPage === 'all' ? 'selected' : '' ?>>Semua</option>
    </select>
</form>

<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Kelas</th>
            <th>Semester</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($students as $s): ?>
        <?php $studentAvatar = ImageUploader::avatarUrl($s['avatar'] ?? null); ?>
        <tr>
            <td><?= e($s['nim']) ?></td>
            <td>
                <div class="cell-project" style="min-width:0">
                    <span class="avatar">
                        <?php if ($studentAvatar): ?>
                            <img src="<?= e($studentAvatar) ?>" alt="Foto <?= e($s['name']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                        <?php else: ?>
                            <?= e(initials($s['name'])) ?>
                        <?php endif; ?>
                    </span>
                    <strong><?= e($s['name']) ?></strong>
                </div>
            </td>
            <td><?= e($s['email']) ?></td>
            <td><?= e($s['class'] ?? '-') ?></td>
            <td><?= e((string)($s['semester'] ?? '-')) ?></td>
            <td><?= status_badge($s['status']) ?></td>
            <td>
                <div class="row-actions">
                    <a href="/admin/students/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Lihat mahasiswa" aria-label="Lihat mahasiswa <?= e($s['name']) ?>"><?= icon('eye') ?></a>
                    <?php if ($s['status'] === 'active'): ?>
                    <form method="POST" action="/admin/students/<?= (int) $s['id'] ?>/delete"
                          data-confirm="Akun akan dinonaktifkan dan tidak bisa login. Riwayat klaim serta tiket tetap tersimpan."
                          data-confirm-title="Nonaktifkan akun?"
                          data-confirm-ok="Ya, nonaktifkan"
                          data-confirm-variant="danger"
                          data-confirm-detail="<?= e($s['name']) ?>">
                        <input type="hidden" name="_token" value="<?= e(\App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-sm btn-outline student-status-action is-active" title="Nonaktifkan akun" aria-label="Nonaktifkan akun <?= e($s['name']) ?>">Nonaktifkan</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="/admin/students/<?= (int) $s['id'] ?>/activate"
                          data-confirm="Akun akan diaktifkan kembali dan dapat login."
                          data-confirm-title="Aktifkan akun?"
                          data-confirm-ok="Ya, aktifkan"
                          data-confirm-variant="primary"
                          data-confirm-detail="<?= e($s['name']) ?>">
                        <input type="hidden" name="_token" value="<?= e(\App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-sm btn-outline student-status-action is-inactive" title="Aktifkan akun" aria-label="Aktifkan akun <?= e($s['name']) ?>">Aktifkan</button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="student-pagination">
    <span>
        Menampilkan <?= (int) $firstShown ?>–<?= (int) $lastShown ?> dari <?= (int) $totalStudents ?> mahasiswa
    </span>
    <?php if ($perPage !== 'all' && $totalPages > 1): ?>
    <div class="student-pages" aria-label="Navigasi halaman mahasiswa">
        <a href="?per_page=<?= e($perPage) ?>&page=<?= max(1, $page - 1) ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>" aria-label="Halaman sebelumnya">&lsaquo;</a>
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $startPage + 4);
        $startPage = max(1, $endPage - 4);
        ?>
        <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
            <a href="?per_page=<?= e($perPage) ?>&page=<?= $p ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <a href="?per_page=<?= e($perPage) ?>&page=<?= min($totalPages, $page + 1) ?>" class="<?= $page >= $totalPages ? 'disabled' : '' ?>" aria-label="Halaman berikutnya">&rsaquo;</a>
    </div>
    <?php endif; ?>
</div>
