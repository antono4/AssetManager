<?php
/** Halaman Dashboard */
$s = $stats;
?>
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-info">
            <div class="inner"><h3><?= $s['total'] ?></h3><p>Total Aset</p></div>
            <div class="icon"><i class="fas fa-boxes"></i></div>
            <a href="<?= url('assets') ?>" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-success">
            <div class="inner"><h3><?= $s['tersedia'] ?></h3><p>Tersedia</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="<?= url('assets?status=tersedia') ?>" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3><?= $s['dipinjam'] ?></h3><p>Dipinjam</p></div>
            <div class="icon"><i class="fas fa-hand-paper"></i></div>
            <a href="<?= url('assets?status=dipinjam') ?>" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3><?= $s['rusak'] ?></h3><p>Rusak</p></div>
            <div class="icon"><i class="fas fa-tools"></i></div>
            <a href="<?= url('assets?status=rusak') ?>" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Distribusi Aset per Kategori</h3></div>
            <div class="card-body"><div id="chart-category" style="min-height:320px"></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Status Aset</h3></div>
            <div class="card-body"><div id="chart-status" style="min-height:320px"></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Patching (Kuartalan)</h3>
                <div class="card-tools">
                    <a href="<?= url('patching') ?>" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex mb-2">
                    <div class="text-center mr-4">
                        <h4 class="mb-0 text-warning"><?= $patching['ongoing'] + $patching['draft'] ?></h4>
                        <small class="text-muted">Jadwal Aktif</small>
                    </div>
                    <div class="text-center mr-4">
                        <h4 class="mb-0 text-info"><?= $patching['checklists'] ?></h4>
                        <small class="text-muted">Total Checklist</small>
                    </div>
                    <div class="text-center">
                        <h4 class="mb-0 text-success"><?= $patching['done'] ?></h4>
                        <small class="text-muted">Selesai</small>
                    </div>
                </div>
                <?php
                $pPct = $patching['checklists'] > 0 ? round(($patching['done'] / $patching['checklists']) * 100) : 0;
                ?>
                <div class="progress" style="height:18px">
                    <div class="progress-bar bg-success" style="width:<?= $pPct ?>%"><?= $pPct ?>%</div>
                </div>
                <p class="text-muted small mt-2 mb-0">Progress patching semua jadwal (<?= $patching['done'] ?>/<?= $patching['checklists'] ?> aset selesai)</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box mr-1"></i> Aset Terbaru</h3>
                <div class="card-tools">
                    <a href="<?= url('assets') ?>" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm mb-0">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentAssets as $a): ?>
                        <tr data-href="<?= url('assets/' . $a['id']) ?>">
                            <td class="asset-code"><?= e($a['asset_code']) ?></td>
                            <td><?= e($a['name']) ?></td>
                            <td><?= status_badge($a['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-1"></i> Aktivitas Terbaru</h3>
                <div class="card-tools">
                    <a href="<?= url('logs') ?>" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="card-body">
                <ul class="timeline-log">
                    <?php foreach ($recentLogs as $l): ?>
                        <li>
                            <small class="text-muted"><i class="far fa-clock"></i> <?= tglwaktu($l['created_at']) ?></small>
                            <div>
                                <strong><?= e($l['user_name'] ?? 'System') ?></strong>
                                <span class="badge badge-<?= $l['action']==='dipinjam'?'warning':($l['action']==='rusak'?'danger':($l['action']==='tersedia'?'success':'secondary')) ?>">
                                    <?= e($l['action']) ?>
                                </span>
                            </div>
                            <small><?= e($l['asset_code']) ?> &middot; <?= e($l['asset_name']) ?></small>
                            <?php if ($l['note']): ?><div class="text-muted small">"<?= e($l['note']) ?>"</div><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Siapkan data untuk chart
$catLabels = json_encode(array_map(fn($c) => $c['name'], $byCategory));
$catTotals = json_encode(array_map(fn($c) => (int)$c['total'], $byCategory));
$catValues = json_encode(array_map(fn($c) => (float)$c['nilai'], $byCategory));
?>
<script>
$(function () {
    // Chart kategori (bar)
    var catLabels = <?= $catLabels ?>;
    var catTotals = <?= $catTotals ?>;
    new ApexCharts(document.querySelector('#chart-category'), {
        chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'Source Sans Pro' },
        plotOptions: { bar: { borderRadius: 6, distributed: true } },
        series: [{ name: 'Jumlah Aset', data: catTotals }],
        colors: ['#3c5184','#2b3a55','#5b7cfa','#7b8b9f','#a3b1cc','#c2cbe0'],
        xaxis: { categories: catLabels },
        dataLabels: { enabled: true },
        legend: { show: false },
        tooltip: { y: { formatter: v => v + ' aset' } }
    }).render();

    // Chart status (donut)
    new ApexCharts(document.querySelector('#chart-status'), {
        chart: { type: 'donut', height: 320, fontFamily: 'Source Sans Pro' },
        series: [<?= $statusChart['tersedia'] ?>, <?= $statusChart['dipinjam'] ?>, <?= $statusChart['rusak'] ?>],
        labels: ['Tersedia', 'Dipinjam', 'Rusak'],
        colors: ['#28a745', '#ffc107', '#dc3545'],
        legend: { position: 'bottom' },
        dataLabels: { formatter: (v) => v.toFixed(0) },
        plotOptions: { pie: { donut: { size: '62%' } } }
    }).render();
});
</script>
