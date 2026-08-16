<?php
/** Halaman Dashboard — modern UI */
$s = $stats;
$usr = Auth::user()['name'] ?? '';
$greeting = (date('H') < 11) ? (Lang::is('id') ? 'Selamat pagi' : 'Good morning')
         : ((date('H') < 15) ? (Lang::is('id') ? 'Selamat siang' : 'Good afternoon')
         : ((date('H') < 18) ? (Lang::is('id') ? 'Selamat sore' : 'Good evening')
         : (Lang::is('id') ? 'Selamat malam' : 'Good evening')));
$dateStr = (Lang::is('id'))
    ? date('j') . ' ' . ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)date('n')-1] . ' ' . date('Y')
    : date('l, F j, Y');
$pPct = $patching['checklists'] > 0 ? round(($patching['done'] / $patching['checklists']) * 100) : 0;
// map warna untuk action log
$actDotMap = [
    'tersedia' => 'dot-ok', 'dipinjam' => 'dot-warn', 'rusak' => 'dot-bad',
    'created' => 'dot-info', 'updated' => 'dot-info', 'patching' => 'dot-info',
    'perawatan' => 'dot-mut', 'status_update' => 'dot-mut',
];
$actBadgeMap = [
    'tersedia' => 'success', 'dipinjam' => 'warning', 'rusak' => 'danger',
    'created' => 'info', 'updated' => 'info', 'patching' => 'info',
    'perawatan' => 'secondary', 'status_update' => 'secondary',
];
?>

<!-- Welcome header -->
<div class="dash-welcome">
    <div>
        <h1><?= $greeting ?>, <?= e($usr) ?>! <span class="wave">👋</span></h1>
        <div class="sub"><?= (Lang::is('id') ? 'Berikut ringkasan aset Anda hari ini' : 'Here is your asset summary for today') ?></div>
    </div>
    <div class="date-pill"><i class="far fa-calendar-alt"></i> <?= $dateStr ?></div>
</div>

<!-- Stat cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="<?= url('assets') ?>" class="stat-card sc-info">
            <div class="sc-bg"></div>
            <i class="fas fa-boxes-stacked sc-icon"></i>
            <div class="sc-body">
                <div class="sc-num"><?= $s['total'] ?></div>
                <div class="sc-label"><?= t('total_assets') ?></div>
            </div>
            <span class="sc-foot"><?= t('view_details') ?> <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="<?= url('assets?status=tersedia') ?>" class="stat-card sc-success">
            <div class="sc-bg"></div>
            <i class="fas fa-circle-check sc-icon"></i>
            <div class="sc-body">
                <div class="sc-num"><?= $s['tersedia'] ?></div>
                <div class="sc-label"><?= t('available') ?></div>
            </div>
            <span class="sc-foot"><?= t('view_details') ?> <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="<?= url('assets?status=dipinjam') ?>" class="stat-card sc-warning">
            <div class="sc-bg"></div>
            <i class="fas fa-hand-paper sc-icon"></i>
            <div class="sc-body">
                <div class="sc-num"><?= $s['dipinjam'] ?></div>
                <div class="sc-label"><?= t('borrowed') ?></div>
            </div>
            <span class="sc-foot"><?= t('view_details') ?> <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="<?= url('assets?status=rusak') ?>" class="stat-card sc-danger">
            <div class="sc-bg"></div>
            <i class="fas fa-screwdriver-wrench sc-icon"></i>
            <div class="sc-body">
                <div class="sc-num"><?= $s['rusak'] ?></div>
                <div class="sc-label"><?= t('broken') ?></div>
            </div>
            <span class="sc-foot"><?= t('view_details') ?> <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-8">
        <div class="card dash-card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-column mr-1"></i> <?= t('asset_distribution') ?></h3></div>
            <div class="card-body"><div id="chart-category" style="min-height:340px"></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dash-card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> <?= t('asset_status') ?></h3></div>
            <div class="card-body"><div id="chart-status" style="min-height:340px"></div></div>
        </div>
    </div>
</div>

<!-- Patching + Quick links -->
<div class="row">
    <div class="col-md-6">
        <div class="card dash-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-halved mr-1"></i> <?= t('patching_quarterly') ?></h3>
                <div class="card-tools"><a href="<?= url('patching') ?>" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="patch-widget">
                <div class="patch-stats">
                    <div class="patch-stat ps-warn">
                        <div class="ps-num"><?= $patching['ongoing'] + $patching['draft'] ?></div>
                        <div class="ps-lbl"><?= t('active_schedules') ?></div>
                    </div>
                    <div class="patch-stat ps-info">
                        <div class="ps-num"><?= $patching['checklists'] ?></div>
                        <div class="ps-lbl"><?= t('total_checklists') ?></div>
                    </div>
                    <div class="patch-stat ps-ok">
                        <div class="ps-num"><?= $patching['done'] ?></div>
                        <div class="ps-lbl"><?= t('completed') ?></div>
                    </div>
                </div>
                <div class="patch-progress-wrap">
                    <div class="pp-head">
                        <span><?= t('patching_progress', ['done' => $patching['done'], 'total' => $patching['checklists']]) ?></span>
                        <span><strong><?= $pPct ?>%</strong></span>
                    </div>
                    <div class="patch-progress"><div class="bar" style="width:<?= $pPct ?>%"></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card dash-card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-bolt mr-1"></i> <?= Lang::is('id') ? 'Akses Cepat' : 'Quick Access' ?></h3></div>
            <div class="card-body">
                <div class="quick-links">
                    <a href="<?= url('assets') ?>" class="quick-link"><i class="fas fa-box"></i> <?= t('assets') ?></a>
                    <a href="<?= url('patching') ?>" class="quick-link"><i class="fas fa-shield-halved"></i> <?= t('patching') ?></a>
                    <a href="<?= url('reports') ?>" class="quick-link"><i class="fas fa-file-lines"></i> <?= t('reports') ?></a>
                    <a href="<?= url('logs') ?>" class="quick-link"><i class="fas fa-clock-rotate-left"></i> <?= t('history') ?></a>
                    <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('assets/create') ?>" class="quick-link"><i class="fas fa-plus"></i> <?= t('add_asset') ?></a>
                    <a href="<?= url('categories') ?>" class="quick-link"><i class="fas fa-tags"></i> <?= t('categories') ?></a>
                    <a href="<?= url('users') ?>" class="quick-link"><i class="fas fa-users"></i> <?= t('user_management') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent assets + Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card dash-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box-open mr-1"></i> <?= t('recent_assets') ?></h3>
                <div class="card-tools"><a href="<?= url('assets') ?>" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="card-body p-0">
                <table class="table recent-table">
                    <thead><tr><th width="50"><?= t('photo') ?></th><th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('status') ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentAssets as $a): ?>
                        <tr data-href="<?= url('assets/' . $a['id']) ?>">
                            <td class="text-center"><?= asset_photo_img($a['photo'] ?? null, 36) ?></td>
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
        <div class="card dash-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wave-square mr-1"></i> <?= t('recent_activity') ?></h3>
                <div class="card-tools"><a href="<?= url('logs') ?>" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="card-body">
                <ul class="act-timeline">
                    <?php foreach ($recentLogs as $l):
                        $dot = $actDotMap[$l['action']] ?? 'dot-mut';
                        $bdg = $actBadgeMap[$l['action']] ?? 'secondary';
                        $icMap = ['tersedia'=>'check','dipinjam'=>'hand-paper','rusak'=>'wrench','created'=>'plus','updated'=>'pen','patching'=>'shield-halved','perawatan'=>'gear','status_update'=>'arrows-rotate'];
                        $ic = $icMap[$l['action']] ?? 'circle';
                    ?>
                    <li>
                        <span class="act-dot <?= $dot ?>"><i class="fas fa-<?= $ic ?>"></i></span>
                        <div class="act-body">
                            <div class="act-top">
                                <span class="act-user"><?= e($l['user_name'] ?? 'System') ?></span>
                                <span class="badge badge-<?= $bdg ?> act-badge"><?= e($l['action']) ?></span>
                                <span class="act-time ml-auto"><i class="far fa-clock"></i> <?= tglwaktu($l['created_at']) ?></span>
                            </div>
                            <div class="act-asset"><i class="fas fa-barcode text-muted"></i> <?= e($l['asset_code']) ?> &middot; <?= e($l['asset_name']) ?></div>
                            <?php if ($l['note']): ?><div class="act-note">"<?= e($l['note']) ?>"</div><?php endif; ?>
                        </div>
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
$statusLabels = json_encode([t('status_tersedia'), t('status_dipinjam'), t('status_rusak')]);
$totalAssetsLabel = t('total_assets');
// Tampung script ke variabel; dirender layout SETELAH library ApexCharts di-load
$scripts = <<<JS
<script>
$(function () {
    // Chart kategori (bar) — modern gradient
    var catLabels = {$catLabels};
    var catTotals = {$catTotals};
    new ApexCharts(document.querySelector('#chart-category'), {
        chart: { type: 'bar', height: 340, toolbar: { show: false }, fontFamily: 'Source Sans Pro', animations: { enabled: true, dynamicAnimation: { enabled: true, speed: 600 } } },
        plotOptions: { bar: { borderRadius: 8, columnWidth: '55%', distributed: true } },
        series: [{ name: '{$totalAssetsLabel}', data: catTotals }],
        colors: ['#3a6bdb','#2b4575','#5b7cfa','#1ea87a','#f0a020','#e5484d','#8898aa'],
        xaxis: { categories: catLabels, labels: { style: { colors: '#6c7a8c', fontSize: '12px' } } },
        yaxis: { labels: { style: { colors: '#8898aa' } } },
        dataLabels: { enabled: true, style: { colors: ['#fff'] }, fontWeight: 600 },
        grid: { borderColor: '#eef1f6', strokeDashArray: 4 },
        legend: { show: false },
        tooltip: { y: { formatter: function(v){ return v + ' '; } }, theme: 'light' }
    }).render();

    // Chart status (donut) — modern
    new ApexCharts(document.querySelector('#chart-status'), {
        chart: { type: 'donut', height: 340, fontFamily: 'Source Sans Pro', animations: { enabled: true } },
        series: [{$statusChart['tersedia']}, {$statusChart['dipinjam']}, {$statusChart['rusak']}],
        labels: {$statusLabels},
        colors: ['#1ea87a', '#f0a020', '#e5484d'],
        legend: { position: 'bottom', fontSize: '13px', labels: { colors: '#6c7a8c' } },
        dataLabels: { enabled: true, formatter: function(v, o){ return o.w.config.series[o.seriesIndex]; }, style: { colors: ['#fff'], fontSize: '14px', fontWeight: 700 } },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, name: { fontSize: '13px', color: '#8898aa' }, total: { show: true, fontSize: '22px', fontWeight: 700, color: '#2b3a55', label: '{$totalAssetsLabel}' } } } } },
        stroke: { width: 0 },
        tooltip: { theme: 'light' }
    }).render();
});
</script>
JS;
?>
