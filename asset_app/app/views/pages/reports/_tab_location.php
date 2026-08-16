<?php /** Tab Per Lokasi */
?>
<div class="card card-outline card-primary">
    <div class="card-header"><h6 class="card-title">Rekapitulasi Aset per Lokasi</h6></div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th width="40">#</th><th><?= t('location') ?></th>
                <th class="text-center"><?= t('status_tersedia') ?></th><th class="text-center"><?= t('status_dipinjam') ?></th><th class="text-center"><?= t('status_rusak') ?></th>
                <th class="text-center"><?= t('total') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('total_value') ?></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $no = 1; $gtTot = $gtTer = $gtDip = $gtRus = 0; $gtNilai = 0.0;
            foreach ($byLocation as $l):
                $gtTer += (int)$l['tersedia']; $gtDip += (int)$l['dipinjam'];
                $gtRus += (int)$l['rusak']; $gtTot += (int)$l['total'];
                $gtNilai += (float)$l['nilai'];
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><i class="fas fa-map-marker-alt text-muted mr-1"></i><strong><?= e($l['location']) ?></strong></td>
                <td class="text-center"><span class="badge badge-success"><?= (int)$l['tersedia'] ?></span></td>
                <td class="text-center"><span class="badge badge-warning"><?= (int)$l['dipinjam'] ?></span></td>
                <td class="text-center"><span class="badge badge-danger"><?= (int)$l['rusak'] ?></span></td>
                <td class="text-center"><strong><?= (int)$l['total'] ?></strong></td>
                <?php if (price_visible()): ?><td class="text-right"><?= rp($l['nilai']) ?></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="bg-light">
                <th colspan="2"><?= t('total') ?></th>
                <th class="text-center"><?= $gtTer ?></th>
                <th class="text-center"><?= $gtDip ?></th>
                <th class="text-center"><?= $gtRus ?></th>
                <th class="text-center"><?= $gtTot ?></th>
                <?php if (price_visible()): ?><th class="text-right"><?= rp($gtNilai) ?></th><?php endif; ?>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> <?= e(ReportController::describeFilters($filters)) ?></p>
