<?php /** Form Tambah/Edit Aset */
$a = $asset;
$isEdit = $action === 'edit';
?>
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-<?= $isEdit?'edit':'plus' ?> mr-1"></i> <?= $isEdit ? t('edit_asset') : t('add_new_asset') ?></h3></div>
    <form method="post" action="<?= $isEdit ? url('assets/' . $a['id']) : url('assets') ?>" enctype="multipart/form-data">
        <div class="card-body">
            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label><?= t('asset_code') ?> <small class="text-muted">({<?= t('auto') ?>})</small></label>
                <input type="text" class="form-control" value="<?= e(Asset::generateCode()) ?>" disabled>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label><?= t('asset_code') ?></label>
                <input type="text" class="form-control" value="<?= e($a['asset_code']) ?>" disabled>
            </div>
            <?php endif; ?>

            <?php if ($isEdit && !empty($a['photo'])): ?>
            <div class="form-group">
                <label><?= t('current_photo') ?></label>
                <div class="d-flex align-items-center">
                    <img src="<?= e(asset_photo_url($a['photo'])) ?>" class="mr-3" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6">
                    <form action="<?= url('assets/' . $a['id'] . '/remove-photo') ?>" method="post" class="d-inline">
                        <button type="submit" class="btn btn-danger btn-sm btn-delete" data-confirm="<?= t('remove_photo') ?>?">
                            <i class="fas fa-trash"></i> <?= t('remove_photo') ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label><?= t('photo') ?></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-image"></i></span></div>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>
                <small class="text-muted"><?= t('photo_hint') ?></small>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label><?= t('name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= e($a['name'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?= t('category') ?> <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $a && (string)$a['category_id']===(string)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><?= t('brand_spec') ?></label>
                <input type="text" name="brand_spec" class="form-control" value="<?= e($a['brand_spec'] ?? '') ?>" placeholder="Mis: Dell OptiPlex 7090 / i7 / 16GB">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= t('location') ?></label>
                        <input type="text" name="location" class="form-control" value="<?= e($a['location'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= t('status') ?></label>
                        <select name="status" class="form-control">
                            <option value="tersedia" <?= ($a['status'] ?? '')==='tersedia'?'selected':'' ?>><?= t('status_tersedia') ?></option>
                            <option value="dipinjam" <?= ($a['status'] ?? '')==='dipinjam'?'selected':'' ?>><?= t('status_dipinjam') ?></option>
                            <option value="rusak"    <?= ($a['status'] ?? '')==='rusak'?'selected':'' ?>><?= t('status_rusak') ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= t('purchase_date') ?></label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= e($a['purchase_date'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= t('price') ?> (Rp)</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="number" name="price" class="form-control" min="0" step="1000" value="<?= e($a['price'] ?? '0') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('save') ?></button>
            <a href="<?= $isEdit ? url('assets/' . $a['id']) : url('assets') ?>" class="btn btn-default"><?= t('cancel') ?></a>
        </div>
    </form>
</div>
