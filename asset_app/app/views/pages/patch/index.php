<?php /** Daftar Jadwal Patching */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Jadwal Patching (Kuartalan / 3 Bulan)</h3>
        <?php if (Auth::isAdmin()): ?>
        <div class="card-tools">
            <a href="<?= url('patching/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Jadwal</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p class="text-muted small">
            <i class="fas fa-info-circle"></i> Jadwal patching &amp; maintenance aset IT dilakukan setiap 3 bulan (per kuartal).
            Setiap jadwal berisi checklist patching per aset dengan item standar (update OS, antivirus, backup, dll).
        </p>
        <?php if (empty($schedules)): ?>
            <div class="empty-state">
                <i class="fas fa-shield-alt"></i>
                <p class="mt-3">Belum ada jadwal patching. <?= Auth::isAdmin() ? 'Klik "Buat Jadwal" untuk memulai.' : '' ?></p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr>
                    <th>Nama Jadwal</th><th>Periode</th><th>Tanggal</th>
                    <th>Status</th><th class="text-center">Progress</th><th class="text-center">Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($schedules as $s):
                    $pct = $s['total_aset'] > 0 ? round(($s['done_aset'] / $s['total_aset']) * 100) : 0;
                ?>
                    <tr>
                        <td>
                            <strong><?= e($s['name']) ?></strong>
                            <?php if (!empty($s['description'])): ?><br><small class="text-muted"><?= e($s['description']) ?></small><?php endif; ?>
                        </td>
                        <td>Q<?= (int)$s['quarter'] ?> / <?= (int)$s['year'] ?></td>
                        <td><small><?= tgl($s['start_date']) ?> <br><i class="fas fa-arrow-right text-muted"></i> <?= tgl($s['due_date']) ?></small></td>
                        <td><?= patch_status_badge($s['status']) ?></td>
                        <td class="text-center">
                            <div style="min-width:110px">
                                <div class="progress progress-sm mb-1">
                                    <div class="progress-bar bg-<?= $pct>=100?'success':($pct>0?'warning':'secondary') ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small><?= (int)$s['done_aset'] ?>/<?= (int)$s['total_aset'] ?> aset (<?= $pct ?>%)</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="<?= url('patching/' . $s['id']) ?>" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                            <?php if (Auth::isAdmin()): ?>
                            <a href="<?= url('patching/' . $s['id'] . '/edit') ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
