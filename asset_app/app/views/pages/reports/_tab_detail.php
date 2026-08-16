<?php /** Tab Detail Aset */
$detTotal = $detailTotal ?? count($assets);
$detPerPage = $detailPerPage ?? 50;
$detPage = $detailPage ?? 1;
$detTotalPages = $detailTotalPages ?? 1;
// Base URL untuk pagination detail (pertahankan filter + tab)
$detQs = http_build_query(array_filter(array_merge($filters, ['tab' => 'detail'])));
$realBase = url('reports?') . ($detQs ? $detQs . '&' : '');
?>
<div class="card card-outline card-primary">
    <div class="card-header"><h6 class="card-title">Daftar Detail Aset <small class="text-muted">(<?= number_format($detTotal) ?> data)</small></h6></div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th width="50"><?= t('photo') ?></th><th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('category') ?></th><th><?= t('brand_spec') ?></th>
                <th><?= t('location') ?></th><th><?= t('purchase_date') ?></th><th><?= t('status') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('price') ?></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $totalNilai = 0.0; foreach ($assets as $a): $totalNilai += (float)$a['price']; ?>
            <tr>
                <td class="text-center"><?= asset_photo_img($a['photo'] ?? null, 36) ?></td>
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
        </table>
    </div>
    <?php if ($detTotalPages > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination pagination-sm justify-content-center mb-0">
            <?php
            $window = 5;
            $dstart = max(1, $detPage - $window);
            $dend = min($detTotalPages, $detPage + $window);
            ?>
            <li class="page-item <?= $detPage<=1?'disabled':'' ?>"><a class="page-link" href="<?= $realBase ?>detail_page=<?= $detPage-1 ?>">&laquo;</a></li>
            <?php if ($dstart > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= $realBase ?>detail_page=1">1</a></li>
                <?php if ($dstart > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($i=$dstart; $i<=$dend; $i++): ?>
                <li class="page-item <?= $i===$detPage?'active':'' ?>"><a class="page-link" href="<?= $realBase ?>detail_page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($dend < $detTotalPages): ?>
                <?php if ($dend < $detTotalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                <li class="page-item"><a class="page-link" href="<?= $realBase ?>detail_page=<?= $detTotalPages ?>"><?= $detTotalPages ?></a></li>
            <?php endif; ?>
            <li class="page-item <?= $detPage>=$detTotalPages?'disabled':'' ?>"><a class="page-link" href="<?= $realBase ?>detail_page=<?= $detPage+1 ?>">&raquo;</a></li>
        </ul></nav>
        <div class="text-center text-muted small mt-1"><?= t('showing') ?> <?= number_format(($detPage-1)*$detPerPage+1) ?>–<?= number_format(min($detPage*$detPerPage, $detTotal)) ?> <?= t('of') ?> <?= number_format($detTotal) ?></div>
    </div>
    <?php endif; ?>
</div>
<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> <?= e(ReportController::describeFilters($filters)) ?></p>
