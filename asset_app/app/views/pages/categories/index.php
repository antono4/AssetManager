<?php /** Kategori Aset - daftar + modal inline (admin only) */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-1"></i> <?= t('category_list') ?></h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add">
                <i class="fas fa-plus"></i> <?= t('add_category') ?>
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="empty-state"><i class="fas fa-tags"></i><p class="mt-3"><?= t('no_data') ?></p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th width="60">#</th><th><?= t('name') ?></th><th><?= t('description') ?></th><th class="text-center"><?= t('asset_count') ?></th><th><?= t('created_at') ?></th><th class="text-center"><?= t('action') ?></th></tr></thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><strong><?= e($c['name']) ?></strong></td>
                        <td class="text-muted"><?= e($c['description']) ?: '-' ?></td>
                        <td class="text-center">
                            <span class="badge badge-<?= $c['asset_count']>0?'info':'light' ?>"><?= (int)$c['asset_count'] ?></span>
                        </td>
                        <td><?= tgl($c['created_at']) ?></td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-edit-<?= $c['id'] ?>"><i class="fas fa-edit"></i></button>
                            <form action="<?= url('categories/' . $c['id'] . '/delete') ?>" method="post" class="d-inline">
                                <button class="btn btn-danger btn-sm btn-delete" data-confirm="<?= t('delete') ?> <?= e($c['name']) ?>?<?= $c['asset_count']>0?' ':'' ?>"><?= '<i class="fas fa-trash"></i>' ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modal-add"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= url('categories') ?>">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus mr-1"></i> <?= t('add_category') ?></h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <div class="form-group"><label><?= t('name') ?> <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label><?= t('description') ?></label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?= t('cancel') ?></button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('save') ?></button></div>
    </form>
</div></div></div>

<!-- Modal Edit per item -->
<?php foreach ($categories as $c): ?>
<div class="modal fade" id="modal-edit-<?= $c['id'] ?>"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= url('categories/' . $c['id']) ?>">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit mr-1"></i> <?= t('edit_category') ?></h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <div class="form-group"><label><?= t('name') ?> <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="<?= e($c['name']) ?>"></div>
            <div class="form-group"><label><?= t('description') ?></label><textarea name="description" class="form-control" rows="2"><?= e($c['description']) ?></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?= t('cancel') ?></button><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> <?= t('save') ?></button></div>
    </form>
</div></div></div>
<?php endforeach; ?>
