<?php
/** Halaman Laporan Aset - dengan filter & tab */
$f = $filters;
$s = $summary;
$filterDesc = ReportController::describeFilters($f);

// Bangun query string untuk link cetak & tab
$qs = http_build_query(array_filter(array_merge($f, ['tab' => $tab])));
?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Laporan Aset</h3>
        <div class="card-tools">
            <a href="<?= url('reports/print?' . $qs) ?>" target="_blank" class="btn btn-success btn-sm">
                <i class="fas fa-print"></i> Cetak / PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <form method="get" class="search-bar mb-3">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <div class="row">
                <div class="col-md-3">
                    <label class="small text-muted">Kategori</label>
                    <select name="category_id" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (string)$f['category_id']===(string)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="tersedia" <?= $f['status']==='tersedia'?'selected':'' ?>>Tersedia</option>
                        <option value="dipinjam" <?= $f['status']==='dipinjam'?'selected':'' ?>>Dipinjam</option>
                        <option value="rusak"    <?= $f['status']==='rusak'?'selected':'' ?>>Rusak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Lokasi</label>
                    <input type="text" name="location" class="form-control form-control-sm" list="loc-list" value="<?= e($f['location']) ?>">
                    <datalist id="loc-list">
                        <?php foreach ($locations as $l): ?><option value="<?= e($l['location']) ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Tgl. Beli Dari</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($f['date_from']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Tgl. Beli Sampai</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($f['date_to']) ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block btn-sm"><i class="fas fa-filter"></i></button>
                </div>
            </div>
            <?php if (array_filter($f)): ?>
            <div class="mt-2">
                <a href="<?= url('reports') ?>" class="btn btn-default btn-sm"><i class="fas fa-times"></i> Reset Filter</a>
                <span class="text-muted small ml-2">Filter aktif: <?= e($filterDesc) ?></span>
            </div>
            <?php endif; ?>
        </form>

        <!-- Tab navigasi -->
        <ul class="nav nav-pills mb-3">
            <?php
            $tabs = [
                'summary'   => 'Ringkasan',
                'category'  => 'Per Kategori',
                'location'  => 'Per Lokasi',
                'detail'    => 'Detail Aset',
            ];
            $baseQs = http_build_query(array_filter($f));
            foreach ($tabs as $key => $label):
                $href = url('reports?' . ($baseQs ? $baseQs . '&' : '') . 'tab=' . $key);
            ?>
            <li class="nav-item">
                <a class="nav-link <?= $tab===$key?'active':'' ?>" href="<?= $href ?>"><?= $label ?></a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Konten tab -->
        <?php if ($tab === 'summary') include __DIR__ . '/_tab_summary.php'; ?>
        <?php if ($tab === 'category') include __DIR__ . '/_tab_category.php'; ?>
        <?php if ($tab === 'location') include __DIR__ . '/_tab_location.php'; ?>
        <?php if ($tab === 'detail') include __DIR__ . '/_tab_detail.php'; ?>
    </div>
</div>

<?php
// Script grafik hanya untuk tab ringkasan
if ($tab === 'summary'):
    $catLab = json_encode($chartCategory['labels']);
    $catTot = json_encode($chartCategory['totals']);
    $catVal = json_encode($chartCategory['nilai']);
?>
<script>
$(function () {
    new ApexCharts(document.querySelector('#rep-chart-category'), {
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Source Sans Pro' },
        plotOptions: { bar: { borderRadius: 6 } },
        series: [{ name: '<?= t('total') ?>', data: <?= $catTot ?> }],
        colors: ['#3c5184'],
        xaxis: { categories: <?= $catLab ?> },
        dataLabels: { enabled: true },
        tooltip: { y: { formatter: v => v + ' aset' } }
    }).render();

    new ApexCharts(document.querySelector('#rep-chart-status'), {
        chart: { type: 'donut', height: 300, fontFamily: 'Source Sans Pro' },
        series: <?= json_encode($chartStatus) ?>,
        labels: ['Tersedia', 'Dipinjam', 'Rusak'],
        colors: ['#28a745', '#ffc107', '#dc3545'],
        legend: { position: 'bottom' },
        plotOptions: { pie: { donut: { size: '62%' } } }
    }).render();
});
</script>
<?php endif; ?>
