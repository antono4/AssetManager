<?php /** Riwayat Aktivitas (semua aset) */ ?>
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Aktivitas <small class="text-muted">(<?= $total ?> catatan)</small></h3>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="empty-state"><i class="fas fa-clock"></i><p class="mt-3">Belum ada aktivitas tercatat.</p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Waktu</th><th>Aset</th><th>Aksi</th><th>Oleh</th><th>Catatan</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td><small><?= tglwaktu($l['created_at']) ?></small></td>
                        <td>
                            <a href="<?= url('assets/' . $l['asset_id']) ?>">
                                <span class="asset-code"><?= e($l['asset_code']) ?></span><br>
                                <small class="text-muted"><?= e($l['asset_name']) ?></small>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?= $l['action']==='dipinjam'?'warning':($l['action']==='rusak'?'danger':($l['action']==='tersedia'?'success':'secondary')) ?>">
                                <?= e($l['action']) ?>
                            </span>
                        </td>
                        <td><small><?= e($l['user_name'] ?? ($l['user_username'] ?? 'System')) ?></small></td>
                        <td><small class="text-muted"><?= e($l['note']) ?: '-' ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?= pagination($page, $totalPages, url('logs?'), $total, 20) ?>
</div>
