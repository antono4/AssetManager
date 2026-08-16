<?php /** Borrowing list */
$borTotal = $total ?? count($borrowings);
?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-hand-paper mr-1"></i> <?= t('borrowing') ?> <small class="text-muted">(<?= number_format($borTotal) ?>)</small></h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($borrowings)): ?>
        <div class="empty-state"><i class="fas fa-hand-paper"></i><p class="mt-3"><?= t('no_data') ?></p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('borrower') ?></th><th><?= t('borrow_date') ?></th><th><?= t('return_date') ?></th><th><?= t('status') ?></th><th class="text-center"><?= t('action') ?></th></tr></thead>
                <tbody>
                <?php foreach ($borrowings as $b): ?>
                <tr>
                    <td class="asset-code"><?= e($b['asset_code']) ?></td>
                    <td><?= e($b['asset_name']) ?></td>
                    <td><?= e($b['borrower_name'] ?: ($b['user_name'] ?? '-')) ?></td>
                    <td><small><?= tglwaktu($b['borrow_date']) ?></small></td>
                    <td><small><?= $b['expected_return'] ? tglwaktu($b['expected_return']) : '-' ?></small></td>
                    <td>
                        <?php if ($b['status'] === 'borrowed'): ?>
                            <?php if ($b['expected_return'] && strtotime($b['expected_return']) < time()): ?>
                            <span class="badge badge-danger">Overdue</span>
                            <?php else: ?>
                            <span class="badge badge-warning"><?= ucfirst($b['status']) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                        <span class="badge badge-success"><?= ucfirst($b['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($b['status'] === 'borrowed'): ?>
                        <form action="<?= url('borrowings/' . $b['id'] . '/return') ?>" method="post" class="d-inline">
                            <button class="btn btn-success btn-sm"><i class="fas fa-undo"></i> <?= t('return_asset') ?></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?= pagination($page ?? 1, $totalPages ?? 1, url('borrowings?'), $borTotal, $perPage ?? 20) ?>
</div>
