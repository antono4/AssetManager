<?php
/** Daftar Komputer & Kode Patching per jadwal */
$sch = $schedule;
$compTotal = $total ?? count($computers);
?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-laptop-code mr-1"></i> <?= t('computer_patch_list') ?> (<?= number_format($compTotal) ?>)</h3>
        <div class="card-tools">
            <a href="<?= url('patching/' . $sch['id']) ?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> <?= t('back') ?></a>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle"></i> <?= t('patch_code_hint') ?>
        </p>

        <?php if (empty($computers)): ?>
            <div class="empty-state">
                <i class="fas fa-laptop"></i>
                <p class="mt-3"><?= t('no_checklists') ?></p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="40">#</th>
                        <th><?= t('asset_code') ?></th>
                        <th><?= t('computer') ?> / <?= t('asset') ?></th>
                        <th><?= t('location') ?></th>
                        <th><?= t('status') ?></th>
                        <th><?= t('patch_codes') ?></th>
                        <th class="text-center"><?= t('patched_items') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($computers as $c): 
                    $patchedCount = count(array_filter($c['patch_codes'], fn($i) => (int)$i['is_checked'] === 1));
                    $totalItems = count($c['patch_codes']);
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="asset-code"><?= e($c['asset_code']) ?></td>
                        <td>
                            <strong><?= e($c['asset_name']) ?></strong>
                            <br><small class="text-muted"><?= e($c['category_name']) ?> • <?= e($c['brand_spec']) ?></small>
                        </td>
                        <td><?= e($c['location']) ?: '-' ?></td>
                        <td><?= patch_status_badge($c['status']) ?></td>
                        <td>
                            <?php if (!empty($c['patch_codes'])): ?>
                                <div class="d-flex flex-wrap" style="gap:4px">
                                <?php foreach ($c['patch_codes'] as $pc): ?>
                                    <?php if (!empty($pc['patch_code'])): ?>
                                        <span class="badge badge-<?= (int)$pc['is_checked']?'success':'info' ?>" title="<?= e($pc['item_name']) ?>">
                                            <i class="fas fa-tag mr-1"></i><?= e($pc['patch_code']) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </div>
                                <?php if (empty($c['patch_codes_summary'])): ?>
                                    <span class="text-muted small"><em><?= t('no_patch_codes') ?></em></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?= $patchedCount>0?'primary':'secondary' ?>"><?= $patchedCount ?>/<?= $totalItems ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detail kode patching per komputer -->
        <hr>
        <h5 class="mb-3"><i class="fas fa-list-ul mr-1"></i> <?= t('patched_items') ?> — <?= t('patch_codes') ?></h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th><?= t('computer') ?></th>
                        <?php foreach ($items as $it): ?>
                            <th class="text-center" title="<?= e($it['name']) ?>"><?= e($it['name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($computers as $c): ?>
                    <tr>
                        <td>
                            <span class="asset-code"><?= e($c['asset_code']) ?></span><br>
                            <small><?= e($c['asset_name']) ?></small>
                        </td>
                        <?php
                        // Buat map item_id => patch_code untuk komputer ini
                        $codeMap = [];
                        foreach ($c['patch_codes'] as $pc) {
                            $codeMap[$pc['item_name']] = $pc;
                        }
                        ?>
                        <?php foreach ($items as $it): 
                            $pc = $codeMap[$it['name']] ?? null;
                            $hasCode = $pc && !empty($pc['patch_code']);
                            $isChecked = $pc && (int)$pc['is_checked'] === 1;
                        ?>
                        <td class="text-center">
                            <?php if ($hasCode): ?>
                                <span class="badge badge-<?= $isChecked?'success':'info' ?>"><?= e($pc['patch_code']) ?></span>
                                <?php if ($isChecked): ?><br><i class="fas fa-check text-success"></i><?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?= pagination($page ?? 1, $totalPages ?? 1, url('patching/' . $sch['id'] . '/computers?'), $compTotal, $perPage ?? 20) ?>
</div>
