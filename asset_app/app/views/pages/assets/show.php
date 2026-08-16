<?php /** Detail Aset + riwayat log + ubah status cepat */
$a = $asset;
?>
<div class="row">
    <div class="col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Aset</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-info" style="width:90px;height:90px;border-radius:12px">
                        <i class="fas fa-box text-white" style="font-size:2.5rem"></i>
                    </div>
                    <h4 class="mt-2 mb-0"><?= e($a['name']) ?></h4>
                    <p class="text-muted asset-code"><?= e($a['asset_code']) ?></p>
                    <?= status_badge($a['status']) ?>
                </div>
                <dl class="asset-detail row">
                    <dt class="col-sm-4">Kategori</dt><dd class="col-sm-8"><?= e($a['category_name']) ?></dd>
                    <dt class="col-sm-4">Brand / Spec</dt><dd class="col-sm-8"><?= e($a['brand_spec']) ?: '-' ?></dd>
                    <dt class="col-sm-4">Lokasi</dt><dd class="col-sm-8"><?= e($a['location']) ?: '-' ?></dd>
                    <dt class="col-sm-4">Tgl. Beli</dt><dd class="col-sm-8"><?= tgl($a['purchase_date']) ?></dd>
                    <dt class="col-sm-4">Harga</dt><dd class="col-sm-8"><strong><?= rp($a['price']) ?></strong></dd>
                    <dt class="col-sm-4">Dibuat</dt><dd class="col-sm-8"><?= tglwaktu($a['created_at']) ?></dd>
                    <dt class="col-sm-4">Diperbarui</dt><dd class="col-sm-8"><?= tglwaktu($a['updated_at']) ?></dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="<?= url('assets') ?>" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
                <?php if (Auth::isAdmin()): ?>
                <a href="<?= url('assets/' . $a['id'] . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                <form action="<?= url('assets/' . $a['id'] . '/delete') ?>" method="post" class="d-inline">
                    <button type="submit" class="btn btn-danger btn-delete" data-confirm="Hapus aset <?= e($a['asset_code']) ?>? Riwayat log juga akan terhapus.">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-exchange-alt mr-1"></i> Ubah Status Cepat</h3></div>
            <div class="card-body">
                <form action="<?= url('assets/' . $a['id'] . '/status') ?>" method="post">
                    <div class="form-group">
                        <label>Status Baru</label>
                        <select name="status" class="form-control">
                            <option value="tersedia" <?= $a['status']==='tersedia'?'selected':'' ?>>Tersedia</option>
                            <option value="dipinjam" <?= $a['status']==='dipinjam'?'selected':'' ?>>Dipinjam</option>
                            <option value="rusak"    <?= $a['status']==='rusak'?'selected':'' ?>>Rusak</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Mis: dipinjam oleh ... / kerusakan ..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-block"><i class="fas fa-save"></i> Simpan Status</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Aktivitas Aset</h3></div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="empty-state"><i class="fas fa-clock"></i><p class="mt-3">Belum ada riwayat untuk aset ini.</p></div>
                <?php else: ?>
                <ul class="timeline-log">
                    <?php foreach ($logs as $l): ?>
                        <li>
                            <div class="d-flex justify-content-between">
                                <strong>
                                    <span class="badge badge-<?= $l['action']==='dipinjam'?'warning':($l['action']==='rusak'?'danger':($l['action']==='tersedia'?'success':'secondary')) ?>">
                                        <?= e($l['action']) ?>
                                    </span>
                                </strong>
                                <small class="text-muted"><i class="far fa-clock"></i> <?= tglwaktu($l['created_at']) ?></small>
                            </div>
                            <div class="mt-1">
                                <small>oleh <strong><?= e($l['user_name'] ?? ($l['user_username'] ?? 'System')) ?></strong></small>
                            </div>
                            <?php if ($l['note']): ?>
                                <div class="text-muted small mt-1">"<?= e($l['note']) ?>"</div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
