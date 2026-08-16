<?php /** Notifications */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bell mr-1"></i> <?= t('notifications') ?></h3>
        <div class="card-tools">
            <form action="<?= url('notifications/read-all') ?>" method="post" class="d-inline">
                <button class="btn btn-default btn-sm"><i class="fas fa-check-double"></i> Mark all read</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($notifs)): ?>
        <div class="empty-state"><i class="far fa-bell"></i><p class="mt-3"><?= t('no_notifications') ?></p></div>
        <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifs as $n): ?>
            <div class="list-group-item list-group-item-action <?= !$n['is_read'] ? 'list-group-item-light' : '' ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <?php if (!$n['is_read']): ?><span class="badge badge-info">New</span> <?php endif; ?>
                        <strong><?= e($n['title']) ?></strong>
                        <?php if ($n['body']): ?><br><small class="text-muted"><?= e($n['body']) ?></small><?php endif; ?>
                    </div>
                    <div class="text-right">
                        <small class="text-muted"><i class="far fa-clock"></i> <?= tglwaktu($n['created_at']) ?></small><br>
                        <?php if ($n['link']): ?>
                        <a href="<?= e($n['link']) ?>" class="btn btn-xs btn-info mt-1"><?= t('view_details') ?></a>
                        <?php endif; ?>
                        <?php if (!$n['is_read']): ?>
                        <form action="<?= url('notifications/' . $n['id'] . '/read') ?>" method="post" class="d-inline mt-1">
                            <button class="btn btn-xs btn-default">Mark read</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
