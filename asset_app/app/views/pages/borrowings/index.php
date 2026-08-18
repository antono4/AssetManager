<?php /** Borrowing list */
$borTotal = $total ?? count($borrowings);
$stats = $stats ?? ['total' => 0, 'active' => 0, 'returned' => 0, 'overdue' => 0];
$statuses = [
    ''          => t('all_borrowings'),
    'active'    => t('active_loan'),
    'overdue'   => t('overdue'),
    'returned'  => t('returned'),
];
?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-hand-paper mr-1"></i> <?= t('borrowing') ?> <small class="text-muted">(<?= number_format($borTotal) ?>)</small></h3>
    </div>

    <!-- Statistik ringkasan -->
    <div class="card-body pb-0">
        <div class="row">
            <div class="col-6 col-md-3">
                <a href="<?= url('borrowings') ?>" class="small-box bg-info mb-3 text-decoration-none">
                    <div class="inner"><h4><?= number_format($stats['total']) ?></h4><p><?= t('all_borrowings') ?></p></div>
                    <i class="fas fa-hand-paper fa-2x opacity-25"></i>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= url('borrowings?status=active') ?>" class="small-box bg-warning mb-3 text-decoration-none">
                    <div class="inner"><h4><?= number_format($stats['active']) ?></h4><p><?= t('active_loan') ?></p></div>
                    <i class="fas fa-clock fa-2x opacity-25"></i>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= url('borrowings?status=overdue') ?>" class="small-box bg-danger mb-3 text-decoration-none">
                    <div class="inner"><h4><?= number_format($stats['overdue']) ?></h4><p><?= t('overdue') ?></p></div>
                    <i class="fas fa-exclamation-circle fa-2x opacity-25"></i>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= url('borrowings?status=returned') ?>" class="small-box bg-success mb-3 text-decoration-none">
                    <div class="inner"><h4><?= number_format($stats['returned']) ?></h4><p><?= t('returned') ?></p></div>
                    <i class="fas fa-check-circle fa-2x opacity-25"></i>
                </a>
            </div>
        </div>

        <!-- Filter + search -->
        <form method="get" class="mb-3">
            <input type="hidden" name="r" value="borrowings">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="small text-muted"><?= t('search') ?></label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="<?= t('search_borrowing') ?>" value="<?= e($search ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted"><?= t('status') ?></label>
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <?php foreach ($statuses as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($status ?? '') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-default btn-sm btn-block"><i class="fas fa-filter"></i> <?= t('search') ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0 pt-2">
        <?php if (empty($borrowings)): ?>
        <div class="empty-state"><i class="fas fa-hand-paper"></i><p class="mt-3"><?= t('no_data') ?></p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('borrower') ?></th><th><?= t('borrow_date') ?></th><th><?= t('return_date') ?></th><th><?= t('actual_return') ?></th><th><?= t('status') ?></th><th class="text-center"><?= t('action') ?></th></tr></thead>
                <tbody>
                <?php foreach ($borrowings as $b): ?>
                <tr>
                    <td class="asset-code"><?= e($b['asset_code']) ?></td>
                    <td><?= e($b['asset_name']) ?></td>
                    <td><?= e($b['borrower_name'] ?: ($b['user_name'] ?? '-')) ?></td>
                    <td><small><?= tglwaktu($b['borrow_date']) ?></small></td>
                    <td><small><?= $b['expected_return'] ? tglwaktu($b['expected_return']) : '-' ?></small></td>
                    <td><small><?= $b['actual_return'] ? tglwaktu($b['actual_return']) : '-' ?></small></td>
                    <td>
                        <?php if ($b['status'] === 'borrowed'): ?>
                            <?php if ($b['expected_return'] && strtotime($b['expected_return']) < time()): ?>
                            <span class="badge badge-danger"><?= t('overdue') ?></span>
                            <?php else: ?>
                            <span class="badge badge-warning"><?= t('active_loan') ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                        <span class="badge badge-success"><?= t('returned') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($b['status'] === 'borrowed'): ?>
                        <form action="<?= url('borrowings/' . $b['id'] . '/return') ?>" method="post" class="d-inline">
                            <button class="btn btn-success btn-sm" data-confirm="<?= t('return_asset') ?>?"><i class="fas fa-undo"></i> <?= t('return_asset') ?></button>
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
    <?= pagination($page ?? 1, $totalPages ?? 1, $base ?? url('borrowings') . '&', $borTotal, $perPage ?? 20) ?>
</div>
