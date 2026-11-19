<a href="/tickets" class="text-sm text-muted">← Kembali</a>
<h1 style="font-size:1.4rem;font-weight:800;margin:0.75rem 0 1.25rem;">Buat Tiket Baru</h1>

<div class="card" style="max-width:640px;">
    <form method="POST" action="/tickets">
        <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">

        <div class="form-group">
            <label>Judul *</label>
            <input type="text" name="title" class="form-control" required placeholder="Ringkasan singkat kebutuhan Anda">
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" class="form-control">
                <option value="">Pilih kategori</option>
                <?php foreach (($categories ?? []) as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Prioritas</label>
            <select name="priority" class="form-control">
                <option value="rendah">Rendah</option>
                <option value="normal" selected>Normal</option>
                <option value="tinggi">Tinggi</option>
            </select>
        </div>

        <div class="form-group">
            <label>Deskripsi *</label>
            <textarea name="description" class="form-control" required placeholder="Jelaskan kebutuhan Anda secara detail..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Kirim Tiket</button>
    </form>
</div>
