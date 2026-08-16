<?php /** Tab Detail Aset */
?>
<div class="card card-outline card-primary">
    <div class="card-header"><h6 class="card-title">Daftar Detail Aset <small class="text-muted">(<?= count($assets) ?> data)</small></h6></div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th>Kode</th><th>Nama</th><th>Kategori</th><th>Brand/Spec</th>
                <th>Lokasi</th><th>Tgl. Beli</th><th>Status</th><th class="text-right">Harga</th>
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
                <td class="text-right"><?= rp($a['price']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="bg-light">
                <th colspan="7">TOTAL NILAI (<?= count($assets) ?> aset)</th>
                <th class="text-right"><?= rp($totalNilai) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> <?= e(ReportController::describeFilters($filters)) ?></p>
