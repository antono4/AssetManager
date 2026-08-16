<?php /** Trash (soft-deleted assets) */ ?>
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-trash-restore mr-1"></i> <?= t('trash') ?> (<?= count($assets) ?>)</h3>
        <div class="card-tools">
            <a href="<?= url('assets') ?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> <?= t('back') ?></a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($assets)): ?>
        <div class="empty-state"><i class="fas fa-trash"></i><p class="mt-3"><?= t('no_data') ?></p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('category') ?></th><th><?= t('deleted_at') ?></th><th class="text-center"><?= t('action') ?></th></tr></thead>
                <tbody>
                <?php foreach ($assets as $a): ?>
                <tr>
                    <td class="asset-code"><?= e($a['asset_code']) ?></td>
                    <td><?= e($a['name']) ?></td>
                    <td><?= e($a['category_name']) ?></td>
                    <td><small class="text-muted"><?= tglwaktu($a['deleted_at']) ?></small></td>
                    <td class="text-center">
                        <form action="<?= url('assets/' . $a['id'] . '/restore') ?>" method="post" class="d-inline">
                            <button class="btn btn-success btn-sm" title="<?= t('restore') ?>"><i class="fas fa-undo"></i> <?= t('restore') ?></button>
                        </form>
                        <form action="<?= url('assets/' . $a['id'] . '/force-delete') ?>" method="post" class="d-inline">
                            <button class="btn btn-danger btn-sm btn-delete" data-confirm="<?= t('permanent_delete_confirm') ?>"><i class="fas fa-times"></i> <?= t('permanent_delete') ?></button>
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
