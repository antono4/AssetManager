<?php /** Profil Saya */
$u = $user;
?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body text-center">
                <div class="d-flex justify-content-center mb-3">
                    <?= user_photo_img($u['photo'] ?? null, $u['name'], 110) ?>
                </div>
                <h4 class="mb-0"><?= e($u['name']) ?></h4>
                <p class="text-muted">@<?= e($u['username']) ?></p>
                <?= role_badge($u['role']) ?>
                <hr>
                <p class="text-muted small"><i class="fas fa-envelope mr-1"></i> <?= e($u['email']) ?: '-' ?></p>
                <p class="text-muted small"><i class="fas fa-calendar mr-1"></i> Bergabung: <?= tgl($u['created_at']) ?></p>
                <?php if (!empty($u['photo'])): ?>
                <form action="<?= url('profile/remove-photo') ?>" method="post">
                    <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="<?= t('remove_photo') ?>?"><i class="fas fa-times"></i> <?= t('remove_photo') ?></button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-warning card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit mr-1"></i> Edit Profil</h3></div>
            <form method="post" action="<?= url('profile') ?>" enctype="multipart/form-data">
                <div class="card-body">
                    <div class="form-group">
                        <label><?= t('photo') ?></label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                        <small class="text-muted"><?= t('photo_hint') ?></small>
                    </div>
                    <div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="<?= e($u['name']) ?>"></div>
                    <div class="form-group"><label>Username <small class="text-muted">(tidak bisa diubah)</small></label><input type="text" class="form-control" value="<?= e($u['username']) ?>" disabled></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>"></div>
                    <hr>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
                        <small class="text-muted">Minimal 6 karakter.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
