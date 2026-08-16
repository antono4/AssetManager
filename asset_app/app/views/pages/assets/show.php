<?php /** Detail Aset + riwayat log + ubah status cepat */
$a = $asset;
?>
<div class="row">
    <div class="col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> <?= t('asset_info') ?></h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?= asset_photo_img($a['photo'] ?? null, 110, 'mb-2') ?>
                    <h4 class="mt-2 mb-0"><?= e($a['name']) ?></h4>
                    <p class="text-muted asset-code"><?= e($a['asset_code']) ?></p>
                    <?= status_badge($a['status']) ?>
                </div>
                <dl class="asset-detail row">
                    <dt class="col-sm-4"><?= t('category') ?></dt><dd class="col-sm-8"><?= e($a['category_name']) ?></dd>
                    <dt class="col-sm-4"><?= t('brand_spec') ?></dt><dd class="col-sm-8"><?= e($a['brand_spec']) ?: '-' ?></dd>
                    <dt class="col-sm-4"><?= t('location') ?></dt><dd class="col-sm-8"><?= e($a['location']) ?: '-' ?></dd>
                    <dt class="col-sm-4"><?= t('purchase_date') ?></dt><dd class="col-sm-8"><?= tgl($a['purchase_date']) ?></dd>
                    <?php if (price_visible()): ?>
                    <dt class="col-sm-4"><?= t('price') ?></dt><dd class="col-sm-8"><strong><?= rp_currency($a['price'], $a['currency'] ?? 'IDR') ?></strong></dd>
                    <?php
                    // Depreciation calculation
                    $dep = Asset::depreciation($a);
                    if (price_visible() && $dep['years_elapsed'] > 0):
                    ?>
                    <dt class="col-sm-4"><?= t('book_value') ?></dt><dd class="col-sm-8"><strong class="text-info"><?= rp_currency($dep['book_value'], $a['currency'] ?? 'IDR') ?></strong> <small class="text-muted">(<?= t('depreciation') ?>: <?= rp_currency($dep['accumulated_depreciation'], $a['currency'] ?? 'IDR') ?>, <?= $dep['years_elapsed'] ?>y / <?= $dep['useful_life'] ?>y)</small></dd>
                    <?php endif; ?>
                    <?php endif; ?>
                    <dt class="col-sm-4"><?= t('created_at') ?></dt><dd class="col-sm-8"><?= tglwaktu($a['created_at']) ?></dd>
                    <dt class="col-sm-4"><?= t('updated_at') ?></dt><dd class="col-sm-8"><?= tglwaktu($a['updated_at']) ?></dd>
                </dl>
                <!-- QR Code -->
                <div class="text-center mt-2">
                    <img src="<?= e(qr_code_url(url('assets/' . $a['id']), 120)) ?>" alt="QR" class="img-fluid" style="width:120px;height:120px;border:1px solid #dee2e6;border-radius:8px">
                    <br><small class="text-muted"><?= t('qr_code') ?> — <?= e($a['asset_code']) ?></small>
                </div>
            </div>
            <div class="card-footer">
                <a href="<?= url('assets') ?>" class="btn btn-default"><i class="fas fa-arrow-left"></i> <?= t('back') ?></a>
                <?php if ($a['status'] === 'tersedia'): ?>
                <a href="<?= url('assets/' . $a['id'] . '/borrow') ?>" class="btn btn-warning"><i class="fas fa-hand-paper"></i> <?= t('borrow') ?></a>
                <?php endif; ?>
                <?php if (Auth::isAdmin()): ?>
                <a href="<?= url('assets/' . $a['id'] . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit"></i> <?= t('edit') ?></a>
                <form action="<?= url('assets/' . $a['id'] . '/delete') ?>" method="post" class="d-inline">
                    <button type="submit" class="btn btn-danger btn-delete" data-confirm="<?= t('delete') ?> <?= e($a['asset_code']) ?>?">
                        <i class="fas fa-trash"></i> <?= t('delete') ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-exchange-alt mr-1"></i> <?= t('change_status_quick') ?></h3></div>
            <div class="card-body">
                <form action="<?= url('assets/' . $a['id'] . '/status') ?>" method="post">
                    <div class="form-group">
                        <label><?= t('new_status') ?></label>
                        <select name="status" class="form-control">
                            <option value="tersedia" <?= $a['status']==='tersedia'?'selected':'' ?>><?= t('status_tersedia') ?></option>
                            <option value="dipinjam" <?= $a['status']==='dipinjam'?'selected':'' ?>><?= t('status_dipinjam') ?></option>
                            <option value="rusak"    <?= $a['status']==='rusak'?'selected':'' ?>><?= t('status_rusak') ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?= t('note_optional') ?></label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-block"><i class="fas fa-save"></i> <?= t('save_status') ?></button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> <?= t('asset_history') ?></h3></div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="empty-state"><i class="fas fa-clock"></i><p class="mt-3"><?= t('no_history') ?></p></div>
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
                                <small><?= t('by') ?> <strong><?= e($l['user_name'] ?? ($l['user_username'] ?? 'System')) ?></strong></small>
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
