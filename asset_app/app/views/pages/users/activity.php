<?php /** Activity by User */ $u = $user; ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-clock mr-1"></i> <?= t('activity_by_user') ?>: <?= e($u['name']) ?></h3>
        <div class="card-tools">
            <a href="<?= url('users') ?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> <?= t('back') ?></a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($activities)): ?>
        <div class="empty-state"><i class="fas fa-clock"></i><p class="mt-3"><?= t('no_activity') ?></p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><?= t('time') ?></th><th><?= t('module') ?></th><th><?= t('action_col') ?></th><th>Description</th></tr></thead>
                <tbody>
                <?php foreach ($activities as $a): ?>
                <tr>
                    <td><small><?= tglwaktu($a['created_at']) ?></small></td>
                    <td><span class="badge badge-info"><?= e($a['module']) ?></span></td>
                    <td><span class="badge badge-secondary"><?= e($a['action']) ?></span></td>
                    <td><?= e($a['description'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
