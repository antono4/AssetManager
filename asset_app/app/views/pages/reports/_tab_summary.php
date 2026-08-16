<?php /** Tab Ringkasan */ /** @var array $s ringkasan */ ?>
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-info"><div class="inner"><h3><?= $s['total'] ?></h3><p>Total Aset</p></div><div class="icon"><i class="fas fa-boxes"></i></div></div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-success"><div class="inner"><h3><?= $s['tersedia'] ?></h3><p>Tersedia</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-warning"><div class="inner"><h3><?= $s['dipinjam'] ?></h3><p>Dipinjam</p></div><div class="icon"><i class="fas fa-hand-paper"></i></div></div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-danger"><div class="inner"><h3><?= $s['rusak'] ?></h3><p>Rusak</p></div><div class="icon"><i class="fas fa-tools"></i></div></div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card card-outline card-primary">
            <div class="card-header"><h6 class="card-title">Distribusi per Kategori</h6></div>
            <div class="card-body"><div id="rep-chart-category"></div></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-outline card-success">
            <div class="card-header"><h6 class="card-title">Komposisi Status</h6></div>
            <div class="card-body"><div id="rep-chart-status"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-info">
    <div class="card-header"><h6 class="card-title"><?= t('asset_value_summary') ?? 'Ringkasan Nilai Aset' ?></h6></div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th><?= t('category') ?></th><th class="text-center"><?= t('total') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('total_value') ?></th><?php endif; ?></tr></thead>
            <tbody>
                <tr><td><?= t('status_tersedia') ?></td><td class="text-center"><?= $s['tersedia'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_tersedia']) ?></td><?php endif; ?></tr>
                <tr><td><?= t('status_dipinjam') ?></td><td class="text-center"><?= $s['dipinjam'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_dipinjam']) ?></td><?php endif; ?></tr>
                <tr><td><?= t('status_rusak') ?></td><td class="text-center"><?= $s['rusak'] ?></td><?php if (price_visible()): ?><td class="text-right"><?= rp($s['nilai_rusak']) ?></td><?php endif; ?></tr>
            </tbody>
            <tfoot>
                <tr><th><?= t('total') ?></th><th class="text-center"><?= $s['total'] ?></th><?php if (price_visible()): ?><th class="text-right"><?= rp($s['nilai_total']) ?></th><?php endif; ?></tr>
            </tfoot>
        </table>
    </div>
</div>
