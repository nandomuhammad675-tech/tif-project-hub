<a href="/admin/students" class="back-link">← Kembali</a>
<h1 style="font-size:1.5rem;font-weight:800;letter-spacing:-.5px;margin:0.75rem 0 1.25rem;"><?= $student ? 'Edit' : 'Tambah' ?> Mahasiswa</h1>

<div class="card" style="max-width:560px;">
<form method="POST" action="<?= $student ? '/admin/students/' . $student['id'] : '/admin/students' ?>">
    <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">

    <div class="form-group">
        <label>NIM</label>
        <input type="text" name="nim" class="form-control" value="<?= e($student['nim'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Nama *</label>
        <input type="text" name="name" class="form-control" required value="<?= e($student['name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" class="form-control" required value="<?= e($student['email'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Password <?= $student ? '(kosongkan jika tidak diubah)' : '*' ?></label>
        <input type="password" name="password" class="form-control" <?= $student ? '' : 'required' ?>>
    </div>
    <div class="form-group">
        <label>Kelas</label>
        <input type="text" name="class" class="form-control" value="<?= e($student['class'] ?? '') ?>" placeholder="TI-3A">
    </div>
    <div class="form-group">
        <label>Semester</label>
        <input type="number" name="semester" class="form-control" min="1" max="14" value="<?= e((string)($student['semester'] ?? '1')) ?>">
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="active" <?= ($student['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= ($student['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
</div>
