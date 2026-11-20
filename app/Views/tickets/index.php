<div class="flex-between mb-2">
    <h1 style="font-size:1.4rem;font-weight:800;">Tiket Saya</h1>
    <a href="/tickets/create" class="btn btn-primary"><?= icon('plus') ?> Buat Tiket</a>
</div>

<?php if (empty($tickets)): ?>
<div class="empty-state">
    <div class="icon"><?= icon('ticket') ?></div>
    <p>Anda belum memiliki tiket.</p>
    <a href="/tickets/create" class="btn btn-primary mt-2">Buat Tiket Pertama</a>
</div>
<?php else: ?>
<div class="ticket-grid">
    <?php foreach ($tickets as $t): ?>
    <article class="ticket-card status-<?= e($t['status']) ?>">
        <div class="ticket-code"><?= icon('ticket') ?> <?= e($t['ticket_code']) ?></div>
        <h3><?= e($t['title']) ?></h3>
        <div class="meta">
            <span><?= e($t['category_name'] ?? 'Umum') ?></span>
            <?= status_badge($t['status']) ?>
            <?= status_badge($t['priority']) ?>
            <span><?= format_date($t['created_at']) ?></span>
        </div>
        <a href="/tickets/<?= $t['id'] ?>" class="btn btn-outline btn-sm">Lihat Tiket</a>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
