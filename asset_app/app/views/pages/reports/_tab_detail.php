<?php /** Tab Detail Aset */
?>
<div class="card card-outline card-primary">
    <div class="card-header"><h6 class="card-title">Daftar Detail Aset <small class="text-muted">(<?= count($assets) ?> data)</small></h6></div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('category') ?></th><th><?= t('brand_spec') ?></th>
                <th><?= t('location') ?></th><th><?= t('purchase_date') ?></th><th><?= t('status') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('price') ?></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $totalNilai = 0.0; foreach ($assets as $a): $totalNilai += (float)$a['price']; ?>
            <tr>
                <td class="asset-code"><?= e($a['asset_code']) ?></td>
                <td><?= e($a['name']) ?></td>
                <td><span class="badge badge-light"><?= e($a['category_name']) ?></span></td>
                <td class="small text-muted"><?= e($a['brand_spec']) ?: '-' ?></td>
                <td><?= e($a['location']) ?: '-' ?></td>
                <td><?= tgl($a['purchase_date']) ?></td>
                <td><?= status_badge($a['status']) ?></td>
                <?php if (price_visible()): ?><td class="text-right"><?= rp($a['price']) ?></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="bg-light">
                <th colspan="<?= price_visible() ? 7 : 6 ?>"><?= t('total_value') ?> (<?= count($assets) ?> <?= t('data') ?>)</th>
                <?php if (price_visible()): ?><th class="text-right"><?= rp($totalNilai) ?></th><?php endif; ?>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> <?= e(ReportController::describeFilters($filters)) ?></p>
