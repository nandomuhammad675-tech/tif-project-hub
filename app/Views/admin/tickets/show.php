<a href="/admin/tickets" class="back-link">← Kembali</a>

<div class="card mt-2 mb-3">
    <div class="ticket-code" style="font-weight:700;color:var(--primary);">🎫 <?= e($ticket['ticket_code']) ?></div>
    <h1 style="font-size:1.3rem;font-weight:800;margin:0.4rem 0;"><?= e($ticket['title']) ?></h1>
    <div class="flex gap-1 mb-2" style="flex-wrap:wrap;">
        <?= status_badge($ticket['status']) ?>
        <?= status_badge($ticket['priority']) ?>
        <span class="badge badge-secondary"><?= e($ticket['category_name'] ?? '-') ?></span>
        <span class="badge badge-secondary"><?= format_datetime($ticket['created_at']) ?></span>
    </div>
    <p class="text-sm text-muted mb-1">
        Dari: <strong><?= e($ticket['user_name']) ?></strong>
        (<?= e($ticket['user_nim'] ?? '-') ?> · <?= e($ticket['user_email']) ?>)
    </p>
    <div style="line-height:1.7;white-space:pre-line;margin-top:0.75rem;"><?= e($ticket['description']) ?></div>
</div>

<h2 style="font-size:1.05rem;font-weight:700;margin-bottom:1rem;">Timeline</h2>
<div class="timeline mb-3">
    <?php foreach ($replies as $r): ?>
    <div class="timeline-item">
        <div class="time"><?= format_datetime($r['created_at']) ?></div>
        <div class="author">
            <?= e($r['user_name']) ?>
            <?php if ($r['user_role'] === 'admin'): ?><span class="badge badge-primary">Admin</span><?php endif; ?>
            <?php if ($r['is_internal']): ?><span class="badge badge-warning">Internal</span><?php endif; ?>
        </div>
        <div class="message"><?= e($r['message']) ?></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($replies)): ?>
    <p class="text-muted text-sm">Belum ada balasan.</p>
    <?php endif; ?>
</div>

<div class="card" style="max-width:640px;">
    <h3 style="font-size:1rem;margin-bottom:0.75rem;">Balas / Ubah Status</h3>
    <form method="POST" action="/admin/tickets/<?= $ticket['id'] ?>">
        <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <?php foreach (['open','diproses','selesai','ditolak'] as $st): ?>
                <option value="<?= $st ?>" <?= $ticket['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Pesan</label>
            <textarea name="message" class="form-control" placeholder="Tulis balasan untuk mahasiswa..."></textarea>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_internal" value="1"> Catatan internal (tidak terlihat mahasiswa)</label>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
