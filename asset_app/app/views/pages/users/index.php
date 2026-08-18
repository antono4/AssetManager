<?php /** Manajemen User (admin only) */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Manajemen User</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add"><i class="fas fa-plus"></i> Tambah User</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Foto</th><th>#</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Dibuat</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= user_photo_img($u['photo'] ?? null, $u['name'], 36) ?></td>
                        <td><?= $u['id'] ?></td>
                        <td><strong><?= e($u['name']) ?></strong></td>
                        <td><code><?= e($u['username']) ?></code></td>
                        <td><?= e($u['email']) ?: '-' ?></td>
                        <td><?= role_badge($u['role']) ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= tgl($u['created_at']) ?></td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-edit-<?= $u['id'] ?>"><i class="fas fa-edit"></i></button>
                            <?php if (!empty($u['photo'])): ?>
                            <form action="<?= url('users/' . $u['id'] . '/remove-photo') ?>" method="post" class="d-inline">
                                <button class="btn btn-secondary btn-sm" title="<?= t('remove_photo') ?>" data-confirm="Hapus foto <?= e($u['username']) ?>?"><i class="fas fa-image"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if ($u['id'] !== Auth::id()): ?>
                            <form action="<?= url('users/' . $u['id'] . '/delete') ?>" method="post" class="d-inline">
                                <button class="btn btn-danger btn-sm btn-delete" data-confirm="Hapus user <?= e($u['username']) ?>?"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modal-add"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= url('users') ?>" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i> Tambah User</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="staff">Staff</option><option value="admin">Admin</option></select></div></div>
            </div>
            <div class="form-group">
                <label><?= t('photo') ?></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                <small class="text-muted"><?= t('photo_hint') ?></small>
            </div>
            <div class="form-group"><div class="icheck-primary"><input type="checkbox" name="is_active" value="1" checked id="new_active"><label for="new_active">Aktif</label></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
    </form>
</div></div></div>

<!-- Modal Edit -->
<?php foreach ($users as $u): ?>
<div class="modal fade" id="modal-edit-<?= $u['id'] ?>"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= url('users/' . $u['id']) ?>" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-edit mr-1"></i> Edit User</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <div class="form-group">
                <label><?= t('photo') ?></label>
                <div class="mb-2"><?= user_photo_img($u['photo'] ?? null, $u['name'], 64) ?></div>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                <small class="text-muted"><?= t('photo_hint') ?></small>
            </div>
            <div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="<?= e($u['name']) ?>"></div>
            <div class="form-group"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required value="<?= e($u['username']) ?>"></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>"></div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Password Baru <small class="text-muted">(kosongkan = tidak ubah)</small></label><input type="password" name="password" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="staff" <?= $u['role']==='staff'?'selected':'' ?>>Staff</option><option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option></select></div></div>
            </div>
            <div class="form-group"><div class="icheck-primary"><input type="checkbox" name="is_active" value="1" id="act_<?= $u['id'] ?>" <?= $u['is_active']?'checked':'' ?>><label for="act_<?= $u['id'] ?>">Aktif</label></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button></div>
    </form>
</div></div></div>
<?php endforeach; ?>
