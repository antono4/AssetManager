<?php /** Form Tambah/Edit Aset */
$a = $asset;
$isEdit = $action === 'edit';
?>
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-<?= $isEdit?'edit':'plus' ?> mr-1"></i> <?= $isEdit ? 'Edit Aset' : 'Tambah Aset Baru' ?></h3></div>
    <form method="post" action="<?= $isEdit ? url('assets/' . $a['id']) : url('assets') ?>">
        <div class="card-body">
            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label>Kode Aset <small class="text-muted">(otomatis)</small></label>
                <input type="text" class="form-control" value="<?= e(Asset::generateCode()) ?>" disabled>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label>Kode Aset</label>
                <input type="text" class="form-control" value="<?= e($a['asset_code']) ?>" disabled>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nama Aset <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= e($a['name'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $a && (string)$a['category_id']===(string)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Brand / Spesifikasi</label>
                <input type="text" name="brand_spec" class="form-control" value="<?= e($a['brand_spec'] ?? '') ?>" placeholder="Mis: Dell OptiPlex 7090 / i7 / 16GB">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" name="location" class="form-control" value="<?= e($a['location'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="tersedia" <?= ($a['status'] ?? '')==='tersedia'?'selected':'' ?>>Tersedia</option>
                            <option value="dipinjam" <?= ($a['status'] ?? '')==='dipinjam'?'selected':'' ?>>Dipinjam</option>
                            <option value="rusak"    <?= ($a['status'] ?? '')==='rusak'?'selected':'' ?>>Rusak</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= e($a['purchase_date'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="number" name="price" class="form-control" min="0" step="1000" value="<?= e($a['price'] ?? '0') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="<?= $isEdit ? url('assets/' . $a['id']) : url('assets') ?>" class="btn btn-default">Batal</a>
        </div>
    </form>
</div>
