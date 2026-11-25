<div class="page-head">
    <div><h1>Kelola Mahasiswa</h1><p><?= count($students) ?> mahasiswa terdaftar</p></div>
</div>

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
        <tr>
            <td><?= e($s['nim']) ?></td>
            <td><div class="cell-project" style="min-width:0"><span class="avatar"><?= e(initials($s['name'])) ?></span><strong><?= e($s['name']) ?></strong></div></td>
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
                          data-confirm-title="Hapus mahasiswa?"
                          data-confirm-ok="Ya, nonaktifkan"
                          data-confirm-variant="danger"
                          data-confirm-detail="<?= e($s['name']) ?>">
                        <input type="hidden" name="_token" value="<?= e(\App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus mahasiswa" aria-label="Hapus mahasiswa <?= e($s['name']) ?>"><?= icon('trash') ?></button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
