<?php
/** View Cetak Laporan - semua section dalam satu halaman print-friendly */
$f = $filters;
$s = $summary;
$filterDesc = ReportController::describeFilters($f);
?>
<div class="report-header">
    <h2><i class="fas fa-cubes"></i> <?= e(Setting::companyName()) ?> — <?= t('asset_report') ?></h2>
    <?php $addr = Setting::companyAddress(); $phone = Setting::companyPhone(); $cemail = Setting::companyEmail(); ?>
    <?php if ($addr !== '' || $phone !== '' || $cemail !== ''): ?>
    <p class="subtitle">
        <?= $addr !== '' ? e($addr) : '' ?>
        <?php if ($phone !== ''): ?> &middot; <i class="fas fa-phone"></i> <?= e($phone) ?><?php endif; ?>
        <?php if ($cemail !== ''): ?> &middot; <i class="fas fa-envelope"></i> <?= e($cemail) ?><?php endif; ?>
    </p>
    <?php endif; ?>
    <div class="report-meta">
        <span><i class="far fa-calendar"></i> <?= t('report_printed') ?>: <?= tglwaktu(date('Y-m-d H:i:s')) ?></span>
        <span><i class="fas fa-filter"></i> <?= e($filterDesc) ?></span>
    </div>
</div>

<!-- Ringkasan -->
<div class="report-section">
    <h4><i class="fas fa-chart-line"></i> <?= t('summary') ?></h4>
    <div class="print-cards">
        <div class="print-card"><div class="label"><?= t('total_assets') ?></div><div class="value"><?= $s['total'] ?></div></div>
        <div class="print-card"><div class="label"><?= t('status_tersedia') ?></div><div class="value text-success"><?= $s['tersedia'] ?></div></div>
        <div class="print-card"><div class="label"><?= t('status_dipinjam') ?></div><div class="value text-warning"><?= $s['dipinjam'] ?></div></div>
        <div class="print-card"><div class="label"><?= t('status_rusak') ?></div><div class="value text-danger"><?= $s['rusak'] ?></div></div>
        <?php if (price_visible()): ?>
        <div class="print-card"><div class="label"><?= t('total_value') ?></div><div class="value"><?= rp($s['nilai_total']) ?></div></div>
        <?php endif; ?>
    </div>
    <table class="report-table">
        <thead><tr><th><?= t('status') ?></th><th class="text-center"><?= t('total') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('total_value') ?></th><?php endif; ?></tr></thead>
        <tbody>
            <tr><td><?= t('status_tersedia') ?></td><td class="text-center"><?= $s['tersedia'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_tersedia']) ?></td><?php endif; ?></tr>
            <tr><td><?= t('status_dipinjam') ?></td><td class="text-center"><?= $s['dipinjam'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_dipinjam']) ?></td><?php endif; ?></tr>
            <tr><td><?= t('status_rusak') ?></td><td class="text-center"><?= $s['rusak'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_rusak']) ?></td><?php endif; ?></tr>
        </tbody>
        <tfoot><tr><td><?= t('total') ?></td><td class="text-center"><?= $s['total'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_total']) ?></td><?php endif; ?></tr></tfoot>
    </table>
</div>

<!-- Rekap per Kategori -->
<div class="report-section">
    <h4><i class="fas fa-tags"></i> <?= t('by_category') ?></h4>
    <table class="report-table">
        <thead><tr><th>#</th><th><?= t('category') ?></th><th class="text-center"><?= t('status_tersedia') ?></th><th class="text-center"><?= t('status_dipinjam') ?></th><th class="text-center"><?= t('status_rusak') ?></th><th class="text-center"><?= t('total') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('total_value') ?></th><?php endif; ?></tr></thead>
        <tbody>
        <?php $no=1; foreach ($byCategory as $c): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($c['category_name']) ?></td>
                <td class="text-center"><?= (int)$c['tersedia'] ?></td>
                <td class="text-center"><?= (int)$c['dipinjam'] ?></td>
                <td class="text-center"><?= (int)$c['rusak'] ?></td>
                <td class="text-center"><?= (int)$c['total'] ?></td>
                <?php if (price_visible()): ?><td class="text-right"><?= rp($c['nilai']) ?></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Rekap per Lokasi -->
<div class="report-section">
    <h4><i class="fas fa-map-marker-alt"></i> <?= t('by_location') ?></h4>
    <table class="report-table">
        <thead><tr><th>#</th><th><?= t('location') ?></th><th class="text-center"><?= t('status_tersedia') ?></th><th class="text-center"><?= t('status_dipinjam') ?></th><th class="text-center"><?= t('status_rusak') ?></th><th class="text-center"><?= t('total') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('total_value') ?></th><?php endif; ?></tr></thead>
        <tbody>
        <?php $no=1; foreach ($byLocation as $l): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($l['location']) ?></td>
                <td class="text-center"><?= (int)$l['tersedia'] ?></td>
                <td class="text-center"><?= (int)$l['dipinjam'] ?></td>
                <td class="text-center"><?= (int)$l['rusak'] ?></td>
                <td class="text-center"><?= (int)$l['total'] ?></td>
                <?php if (price_visible()): ?><td class="text-right"><?= rp($l['nilai']) ?></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Detail Aset -->
<div class="report-section">
    <h4><i class="fas fa-list"></i> <?= t('asset_detail_list') ?> (<?= count($assets) ?>)</h4>
    <table class="report-table">
        <thead><tr><th width="40"><?= t('photo') ?></th><th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('category') ?></th><th><?= t('location') ?></th><th><?= t('purchase_date') ?></th><th><?= t('status') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('price') ?></th><?php endif; ?></tr></thead>
        <tbody>
        <?php $totalNilai = 0.0; foreach ($assets as $a): $totalNilai += (float)$a['price']; ?>
            <tr>
                <td><?= asset_photo_img($a['photo'] ?? null, 30) ?></td>
                <td><?= e($a['asset_code']) ?></td>
                <td><?= e($a['name']) ?><br><small class="text-muted"><?= e($a['brand_spec']) ?></small></td>
                <td><?= e($a['category_name']) ?></td>
                <td><?= e($a['location']) ?: '-' ?></td>
                <td><?= tgl($a['purchase_date']) ?></td>
                <td><?= ucfirst($a['status']) ?></td>
                <?php if (price_visible()): ?><td class="text-right"><?= rp($a['price']) ?></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if (price_visible()): ?>
        <tfoot><tr><td colspan="6"><?= t('total_value') ?></td><td class="text-right"><?= rp($totalNilai) ?></td></tr></tfoot>
        <?php endif; ?>
    </table>
</div>

<!-- Tanda tangan -->
<div class="tanda-tangan">
    <div class="tt-label"><?= date('j') . ' ' . ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)date('n')-1] . ' ' . date('Y') ?></div>
    <div class="tt-label mt-2">Mengetahui,</div>
    <div class="tt-name"><?= e(Auth::user()['name']) ?></div>
</div>

<div class="report-footer">
    <span><?= APP_NAME ?> v<?= APP_VERSION ?></span>
    <span>Database: <?= Database::driver() === 'mysql' ? 'MySQL' : 'SQLite' ?></span>
</div>
