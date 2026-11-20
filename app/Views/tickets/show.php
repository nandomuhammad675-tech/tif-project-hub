<a href="/tickets" class="text-sm text-muted">← Kembali ke Daftar Tiket</a>

<div class="card mt-2 mb-3">
    <div class="ticket-code" style="font-size:0.9rem;font-weight:700;color:var(--primary);"><?= icon('ticket') ?> <?= e($ticket['ticket_code']) ?></div>
    <h1 style="font-size:1.35rem;font-weight:800;margin:0.4rem 0;"><?= e($ticket['title']) ?></h1>
    <div class="flex gap-1 mb-2" style="flex-wrap:wrap;">
        <?= status_badge($ticket['status']) ?>
        <?= status_badge($ticket['priority']) ?>
        <span class="badge badge-secondary"><?= e($ticket['category_name'] ?? 'Umum') ?></span>
        <span class="badge badge-secondary"><?= format_datetime($ticket['created_at']) ?></span>
    </div>
    <div style="line-height:1.7;white-space:pre-line;"><?= e($ticket['description']) ?></div>
</div>

<h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;">Timeline / Riwayat</h2>
<div class="timeline mb-3">
    <div class="timeline-item">
        <div class="time"><?= format_datetime($ticket['created_at']) ?></div>
        <div class="author"><?= e($ticket['user_name']) ?></div>
        <div class="message">Tiket dibuat: <?= e($ticket['title']) ?></div>
    </div>
    <?php foreach ($replies as $r): ?>
    <div class="timeline-item">
        <div class="time"><?= format_datetime($r['created_at']) ?></div>
        <div class="author"><?= e($r['user_name']) ?> <?= $r['user_role'] === 'admin' ? '<span class="badge badge-primary">Admin</span>' : '' ?></div>
        <div class="message"><?= e($r['message']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!in_array($ticket['status'], ['selesai', 'ditolak'], true)): ?>
<div class="card" style="max-width:640px;">
    <h3 style="font-size:1rem;margin-bottom:0.75rem;">Balas Tiket</h3>
    <form method="POST" action="/tickets/<?= $ticket['id'] ?>/reply">
        <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">
        <div class="form-group">
            <textarea name="message" class="form-control" required placeholder="Tulis balasan Anda..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Balasan</button>
    </form>
</div>
<?php endif; ?>
