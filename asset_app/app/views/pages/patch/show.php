<?php /** Detail Jadwal Patching + daftar checklist aset */
$s = $schedule;
$pct = $s['total_aset'] > 0 ? round(($s['done_aset'] / $s['total_aset']) * 100) : 0;
?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> <?= e($s['name']) ?></h3>
                <div class="card-tools">
                    <a href="<?= url('patching') ?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('patching/' . $s['id'] . '/edit') ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <form action="<?= url('patching/' . $s['id'] . '/delete') ?>" method="post" class="d-inline">
                        <button class="btn btn-danger btn-sm btn-delete" data-confirm="Hapus jadwal beserta semua checklist?"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="small-box bg-info">
                            <div class="inner"><h3><?= $s['total_aset'] ?></h3><p>Total Aset</p></div>
                            <div class="icon"><i class="fas fa-boxes"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="small-box bg-success">
                            <div class="inner"><h3><?= $s['done_aset'] ?></h3><p>Selesai</p></div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="small-box bg-warning">
                            <div class="inner"><h3><?= $s['progress_aset'] + $s['pending_aset'] ?></h3><p>Belum Selesai</p></div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="small-box bg-dark">
                            <div class="inner"><h3><?= $s['skipped_aset'] ?></h3><p>Skipped</p></div>
                            <div class="icon"><i class="fas fa-forward"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-8">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Periode</dt><dd class="col-sm-9">Kuartal <?= (int)$s['quarter'] ?> / <?= (int)$s['year'] ?> (per 3 bulan)</dd>
                            <dt class="col-sm-3">Rentang Tanggal</dt><dd class="col-sm-9"><?= tgl($s['start_date']) ?> &rarr; <?= tgl($s['due_date']) ?></dd>
                            <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><?= patch_status_badge($s['status']) ?></dd>
                            <?php if (!empty($s['description'])): ?><dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9"><?= e($s['description']) ?></dd><?php endif; ?>
                        </dl>
                    </div>
                    <div class="col-md-4">
                        <label class="small">Progress Keseluruhan: <strong><?= $pct ?>%</strong></label>
                        <div class="progress" style="height:22px">
                            <div class="progress-bar bg-<?= $pct>=100?'success':($pct>0?'warning':'secondary') ?>" style="width:<?= $pct ?>%"><?= $pct ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (Auth::isAdmin() && !empty($availableAssets)): ?>
<div class="card card-success card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Generate Checklist Aset IT</h3>
        <div class="card-tools">
            <form action="<?= url('patching/' . $s['id'] . '/generate-all') ?>" method="post" class="d-inline">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-magic"></i> Generate Semua Aset IT</button>
            </form>
        </div>
    </div>
    <form method="post" action="<?= url('patching/' . $s['id'] . '/generate') ?>">
        <div class="card-body">
            <p class="text-muted small">Pilih aset IT untuk dibuatkan checklist patching pada jadwal ini:</p>
            <div class="table-responsive" style="max-height:280px;overflow-y:auto">
                <table class="table table-sm table-hover">
                    <thead><tr><th width="40"><input type="checkbox" id="chk-all" checked></th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th></tr></thead>
                    <tbody>
                    <?php foreach ($availableAssets as $a): ?>
                        <tr>
                            <td><input type="checkbox" name="asset_ids[]" value="<?= $a['id'] ?>" class="chk-asset" checked></td>
                            <td class="asset-code"><?= e($a['asset_code']) ?></td>
                            <td><?= e($a['name']) ?></td>
                            <td><span class="badge badge-light"><?= e($a['category_name']) ?></span></td>
                            <td><?= e($a['location']) ?: '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Buat Checklist Terpilih</button>
        </div>
    </form>
</div>
<script>document.getElementById('chk-all').addEventListener('change',function(){document.querySelectorAll('.chk-asset').forEach(function(c){c.checked=this.checked;}.bind(this));});</script>
<?php endif; ?>

<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-check mr-1"></i> <?= t('checklists') ?> (<?= number_format($totalChecklists ?? count($checklists)) ?>)</h3>
        <div class="card-tools">
            <a href="<?= url('patching/' . $s['id'] . '/computers') ?>" class="btn btn-info btn-sm">
                <i class="fas fa-laptop-code"></i> <?= t('view_patch_list') ?>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($checklists)): ?>
            <div class="empty-state"><i class="fas fa-clipboard-list"></i><p class="mt-3">Belum ada checklist. <?= Auth::isAdmin() ? 'Generate dari aset IT di atas.' : 'Hubungi admin.' ?></p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Kode</th><th>Nama Aset</th><th>Kategori</th><th>Lokasi</th>
                    <th>Status</th><th class="text-center">Progress Item</th><th class="text-center">Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($checklists as $c):
                    // Progress di-pre-load batch (hindari N+1 query)
                    $p = $progress[$c['id']] ?? ['done' => 0, 'total' => 0];
                    $dn = $p['done'];
                    $tot = $p['total'];
                    $ip = $tot > 0 ? round(($dn / $tot) * 100) : 0;
                ?>
                    <tr>
                        <td class="asset-code"><?= e($c['asset_code']) ?></td>
                        <td><?= e($c['asset_name']) ?></td>
                        <td><span class="badge badge-light"><?= e($c['category_name']) ?></span></td>
                        <td><?= e($c['location']) ?: '-' ?></td>
                        <td><?= patch_status_badge($c['status']) ?></td>
                        <td class="text-center">
                            <div style="min-width:100px">
                                <div class="progress progress-sm mb-1">
                                    <div class="progress-bar bg-<?= $ip>=100?'success':($ip>0?'warning':'secondary') ?>" style="width:<?= $ip ?>%"></div>
                                </div>
                                <small><?= $dn ?>/<?= $tot ?></small>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="<?= url('patching/checklist/' . $c['id']) ?>" class="btn btn-primary btn-sm" title="Buka Checklist"><i class="fas fa-clipboard-check"></i> Ceklis</a>
                            <?php if (Auth::isAdmin()): ?>
                            <form action="<?= url('patching/checklist/' . $c['id'] . '/delete') ?>" method="post" class="d-inline">
                                <button class="btn btn-danger btn-sm btn-delete" data-confirm="Hapus checklist aset ini dari jadwal?"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="card-footer">
            <nav><ul class="pagination pagination-sm justify-content-center mb-0">
                <?php
                $pbase = url('patching/' . $s['id']) . '?';
                $tp = $totalPages ?? 1;
                $window = 5;
                $pstart = max(1, $page - $window);
                $pend = min($tp, $page + $window);
                ?>
                <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= $pbase ?>page=<?= $page-1 ?>">&laquo;</a></li>
                <?php if ($pstart > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= $pbase ?>page=1">1</a></li>
                    <?php if ($pstart > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                <?php endif; ?>
                <?php for ($i=$pstart; $i<=$pend; $i++): ?>
                    <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="<?= $pbase ?>page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($pend < $tp): ?>
                    <?php if ($pend < $tp - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= $pbase ?>page=<?= $tp ?>"><?= $tp ?></a></li>
                <?php endif; ?>
                <li class="page-item <?= $page>=$tp?'disabled':'' ?>"><a class="page-link" href="<?= $pbase ?>page=<?= $page+1 ?>">&raquo;</a></li>
            </ul></nav>
            <div class="text-center text-muted small mt-1"><?= t('showing') ?> <?= number_format(($page-1)*$perPage+1) ?>–<?= number_format(min($page*$perPage, $totalChecklists)) ?> <?= t('of') ?> <?= number_format($totalChecklists) ?></div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
