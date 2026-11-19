<div class="page-head">
    <div><h1>Kelola Mahasiswa</h1><p><?= count($students) ?> mahasiswa terdaftar</p></div>
    <a href="/admin/students/create" class="btn btn-primary">＋ Tambah Mahasiswa</a>
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
            <td class="flex gap-1">
                <a href="/admin/students/<?= $s['id'] ?>/edit" class="btn btn-sm btn-outline">✏️ Edit</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
