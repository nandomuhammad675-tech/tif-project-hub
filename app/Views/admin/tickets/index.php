<div class="page-head"><div><h1>Kelola Tiket</h1><p>Pantau dan balas tiket mahasiswa.</p></div></div>

<form method="GET" class="filter-bar">
    <div class="form-group">
        <label>Cari</label>
        <input type="text" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Kode / judul / nama">
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="">Semua</option>
            <?php foreach (['open','diproses','selesai','ditolak'] as $st): ?>
            <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Prioritas</label>
        <select name="priority" class="form-control">
            <option value="">Semua</option>
            <?php foreach (['rendah','normal','tinggi'] as $pr): ?>
            <option value="<?= $pr ?>" <?= $filters['priority'] === $pr ? 'selected' : '' ?>><?= ucfirst($pr) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>

<div class="ticket-grid">
<?php foreach ($tickets as $t): ?>
<article class="ticket-card status-<?= e($t['status']) ?>">
    <div class="ticket-code"><?= icon('ticket') ?> <?= e($t['ticket_code']) ?></div>
    <h3><?= e($t['title']) ?></h3>
    <div class="meta">
        <span><?= e($t['user_name']) ?> (<?= e($t['nim'] ?? '-') ?>)</span>
        <span><?= e($t['category_name'] ?? '-') ?></span>
        <?= status_badge($t['status']) ?>
        <?= status_badge($t['priority']) ?>
        <span><?= format_date($t['created_at']) ?></span>
    </div>
    <a href="/admin/tickets/<?= $t['id'] ?>" class="btn btn-outline btn-sm">Kelola Tiket</a>
</article>
<?php endforeach; ?>
</div>
<?php if (empty($tickets)): ?>
<div class="empty-state"><div class="icon"><?= icon('ticket') ?></div><p>Tidak ada tiket.</p></div>
<?php endif; ?>
