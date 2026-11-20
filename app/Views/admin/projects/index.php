<div class="page-head">
    <div>
        <h1>Kelola Proyek</h1>
        <p><?= count($projects) ?> proyek terdaftar · kelola foto, status, dan anggota.</p>
    </div>
    <a href="/admin/projects/create" class="btn btn-primary"><?= icon('plus') ?> Tambah Proyek</a>
</div>

<?php if (empty($projects)): ?>
<div class="empty-state">
    <div class="icon"><?= icon('box') ?></div>
    <p>Belum ada proyek. Mulai dengan menambahkan proyek pertama beserta fotonya.</p>
    <a href="/admin/projects/create" class="btn btn-primary mt-2"><?= icon('plus') ?> Tambah Proyek</a>
</div>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>Proyek</th>
            <th>Kategori</th>
            <th>Semester</th>
            <th>Foto</th>
            <th>Status</th>
            <th>Views</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($projects as $p): ?>
        <tr>
            <td>
                <div class="cell-project">
                    <div class="cell-thumb"><?= project_thumb($p + ['category_icon' => null]) ?></div>
                    <div>
                        <strong><?= e($p['title']) ?></strong>
                        <small><?= (int) $p['member_count'] > 0 ? icon('users', 'ico-sm') . ' ' . (int) $p['member_count'] . ' anggota' : 'Belum diambil' ?></small>
                    </div>
                </div>
            </td>
            <td><?= e($p['category_name'] ?? '-') ?></td>
            <td><?= e($p['semester_name'] ?? '-') ?></td>
            <td>
                <?php if ((int) $p['image_count'] > 0): ?>
                    <span class="badge badge-primary"><?= icon('image', 'ico-sm') ?> <?= (int) $p['image_count'] ?></span>
                <?php else: ?>
                    <span class="badge badge-secondary">Belum ada</span>
                <?php endif; ?>
            </td>
            <td><?= $p['is_published'] ? status_badge('active') : '<span class="badge badge-secondary">Draft</span>' ?></td>
            <td><?= (int) $p['view_count'] ?></td>
            <td>
                <div class="row-actions">
                    <a href="/admin/projects/<?= (int) $p['id'] ?>/edit" class="btn btn-sm btn-outline"><?= icon('pencil') ?> Edit</a>
                    <form method="POST" action="/admin/projects/<?= (int) $p['id'] ?>/delete" onsubmit="return confirm('Hapus proyek ini beserta semua fotonya?')">
                        <input type="hidden" name="_token" value="<?= e(\App\Core\Session::getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
