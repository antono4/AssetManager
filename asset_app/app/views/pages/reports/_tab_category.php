<?php /** Tab Per Kategori */
?>
<div class="card card-outline card-primary">
    <div class="card-header"><h6 class="card-title">Rekapitulasi Aset per Kategori</h6></div>
    <div class="card-body p-0">
        <?php if (empty($byCategory) || (count($byCategory) === 1 && $byCategory[0]['total'] == 0 && empty(array_filter($filters)))): ?>
        <?php endif; ?>
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th width="40">#</th><th>Kategori</th>
                <th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th>
                <th class="text-center">Total</th><th class="text-right">Nilai Aset</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $no = 1; $gtTot = $gtTer = $gtDip = $gtRus = 0; $gtNilai = 0.0;
            foreach ($byCategory as $c):
                $gtTer += (int)$c['tersedia']; $gtDip += (int)$c['dipinjam'];
                $gtRus += (int)$c['rusak']; $gtTot += (int)$c['total'];
                $gtNilai += (float)$c['nilai'];
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><strong><?= e($c['category_name']) ?></strong></td>
                <td class="text-center"><span class="badge badge-success"><?= (int)$c['tersedia'] ?></span></td>
                <td class="text-center"><span class="badge badge-warning"><?= (int)$c['dipinjam'] ?></span></td>
                <td class="text-center"><span class="badge badge-danger"><?= (int)$c['rusak'] ?></span></td>
                <td class="text-center"><strong><?= (int)$c['total'] ?></strong></td>
                <td class="text-right"><?= rp($c['nilai']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="bg-light">
                <th colspan="2">TOTAL</th>
                <th class="text-center"><?= $gtTer ?></th>
                <th class="text-center"><?= $gtDip ?></th>
                <th class="text-center"><?= $gtRus ?></th>
                <th class="text-center"><?= $gtTot ?></th>
                <th class="text-right"><?= rp($gtNilai) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> <?= e(ReportController::describeFilters($filters)) ?></p>
