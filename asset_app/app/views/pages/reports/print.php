<?php
/** View Cetak Laporan - semua section dalam satu halaman print-friendly */
$f = $filters;
$s = $summary;
$filterDesc = ReportController::describeFilters($f);
?>
<div class="report-header">
    <h2><i class="fas fa-cubes"></i> <?= APP_NAME ?> — Laporan Aset</h2>
    <p class="subtitle">Laporan Manajemen Aset IT &amp; Umum</p>
    <div class="report-meta">
        <span><i class="far fa-calendar"></i> Dicetak: <?= tglwaktu(date('Y-m-d H:i:s')) ?></span>
        <span><i class="fas fa-filter"></i> <?= e($filterDesc) ?></span>
    </div>
</div>

<!-- Ringkasan -->
<div class="report-section">
    <h4><i class="fas fa-chart-line"></i> Ringkasan</h4>
    <div class="print-cards">
        <div class="print-card"><div class="label">Total Aset</div><div class="value"><?= $s['total'] ?></div></div>
        <div class="print-card"><div class="label">Tersedia</div><div class="value text-success"><?= $s['tersedia'] ?></div></div>
        <div class="print-card"><div class="label">Dipinjam</div><div class="value text-warning"><?= $s['dipinjam'] ?></div></div>
        <div class="print-card"><div class="label">Rusak</div><div class="value text-danger"><?= $s['rusak'] ?></div></div>
        <div class="print-card"><div class="label">Total Nilai Aset</div><div class="value"><?= rp($s['nilai_total']) ?></div></div>
    </div>
    <table class="report-table">
        <thead><tr><th>Status</th><th class="text-center">Jumlah</th><th class="text-right">Nilai</th></tr></thead>
        <tbody>
            <tr><td>Tersedia</td><td class="text-center"><?= $s['tersedia'] ?></td><td class="text-right"><?= rp($s['nilai_tersedia']) ?></td></tr>
            <tr><td>Dipinjam</td><td class="text-center"><?= $s['dipinjam'] ?></td><td class="text-right"><?= rp($s['nilai_dipinjam']) ?></td></tr>
            <tr><td>Rusak</td><td class="text-center"><?= $s['rusak'] ?></td><td class="text-right"><?= rp($s['nilai_rusak']) ?></td></tr>
        </tbody>
        <tfoot><tr><td>TOTAL</td><td class="text-center"><?= $s['total'] ?></td><td class="text-right"><?= rp($s['nilai_total']) ?></td></tr></tfoot>
    </table>
</div>

<!-- Rekap per Kategori -->
<div class="report-section">
    <h4><i class="fas fa-tags"></i> Rekap per Kategori</h4>
    <table class="report-table">
        <thead><tr><th>#</th><th>Kategori</th><th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th><th class="text-center">Total</th><th class="text-right">Nilai</th></tr></thead>
        <tbody>
        <?php $no=1; foreach ($byCategory as $c): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($c['category_name']) ?></td>
                <td class="text-center"><?= (int)$c['tersedia'] ?></td>
                <td class="text-center"><?= (int)$c['dipinjam'] ?></td>
                <td class="text-center"><?= (int)$c['rusak'] ?></td>
                <td class="text-center"><?= (int)$c['total'] ?></td>
                <td class="text-right"><?= rp($c['nilai']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Rekap per Lokasi -->
<div class="report-section">
    <h4><i class="fas fa-map-marker-alt"></i> Rekap per Lokasi</h4>
    <table class="report-table">
        <thead><tr><th>#</th><th>Lokasi</th><th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th><th class="text-center">Total</th><th class="text-right">Nilai</th></tr></thead>
        <tbody>
        <?php $no=1; foreach ($byLocation as $l): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($l['location']) ?></td>
                <td class="text-center"><?= (int)$l['tersedia'] ?></td>
                <td class="text-center"><?= (int)$l['dipinjam'] ?></td>
                <td class="text-center"><?= (int)$l['rusak'] ?></td>
                <td class="text-center"><?= (int)$l['total'] ?></td>
                <td class="text-right"><?= rp($l['nilai']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Detail Aset -->
<div class="report-section">
    <h4><i class="fas fa-list"></i> Daftar Detail Aset (<?= count($assets) ?>)</h4>
    <table class="report-table">
        <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Tgl. Beli</th><th>Status</th><th class="text-right">Harga</th></tr></thead>
        <tbody>
        <?php $totalNilai = 0.0; foreach ($assets as $a): $totalNilai += (float)$a['price']; ?>
            <tr>
                <td><?= e($a['asset_code']) ?></td>
                <td><?= e($a['name']) ?><br><small class="text-muted"><?= e($a['brand_spec']) ?></small></td>
                <td><?= e($a['category_name']) ?></td>
                <td><?= e($a['location']) ?: '-' ?></td>
                <td><?= tgl($a['purchase_date']) ?></td>
                <td><?= ucfirst($a['status']) ?></td>
                <td class="text-right"><?= rp($a['price']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="6">TOTAL NILAI</td><td class="text-right"><?= rp($totalNilai) ?></td></tr></tfoot>
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
