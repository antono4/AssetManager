<?php /** Halaman Checklist Patching per Aset (centang item) */
$c = $checklist;
$sch = $schedule;
?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-box mr-1"></i> Aset</h3></div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary mb-2" style="width:70px;height:70px;border-radius:12px">
                        <i class="fas fa-desktop text-white" style="font-size:2rem"></i>
                    </div>
                    <h5 class="mb-0"><?= e($c['asset_name']) ?></h5>
                    <p class="text-muted asset-code mb-0"><?= e($c['asset_code']) ?></p>
                    <?= patch_status_badge($c['status']) ?>
                </div>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kategori</dt><dd class="col-sm-8"><?= e($c['category_name']) ?></dd>
                    <dt class="col-sm-4">Lokasi</dt><dd class="col-sm-8"><?= e($c['location']) ?: '-' ?></dd>
                    <dt class="col-sm-4">Brand</dt><dd class="col-sm-8"><small><?= e($c['brand_spec']) ?: '-' ?></small></dd>
                </dl>
            </div>
        </div>

        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Progress</h3></div>
            <div class="card-body text-center">
                <h2 class="text-<?= $progress>=100?'success':'primary' ?>"><?= $progress ?>%</h2>
                <div class="progress mb-2" style="height:20px">
                    <div class="progress-bar bg-<?= $progress>=100?'success':'primary' ?>" style="width:<?= $progress ?>%"><?= $done ?>/<?= $total ?></div>
                </div>
                <p class="text-muted small mb-0"><?= $done ?> dari <?= $total ?> item selesai</p>
                <?php if ($c['patched_by']): ?>
                    <p class="text-muted small">Dikerjakan oleh: <strong><?= e($c['patched_by_name'] ?? '') ?></strong></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-1"></i> Aksi</h3></div>
            <div class="card-body">
                <a href="<?= url('patching/' . $sch['id']) ?>" class="btn btn-default btn-block"><i class="fas fa-arrow-left"></i> Kembali ke Jadwal</a>
                <?php if ($c['status'] !== 'completed'): ?>
                <form action="<?= url('patching/checklist/' . $c['id'] . '/status') ?>" method="post" class="mt-2">
                    <input type="hidden" name="status" value="skipped">
                    <div class="form-group">
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Alasan skip (opsional)">
                    </div>
                    <button class="btn btn-dark btn-block btn-sm"><i class="fas fa-forward"></i> Skip Aset Ini</button>
                </form>
                <?php endif; ?>
                <?php if (Auth::isAdmin() && $c['status'] !== 'pending'): ?>
                <form action="<?= url('patching/checklist/' . $c['id'] . '/status') ?>" method="post" class="mt-2">
                    <input type="hidden" name="status" value="pending">
                    <button class="btn btn-secondary btn-block btn-sm btn-delete" data-confirm="Reset semua centang item?"><i class="fas fa-undo"></i> Reset Checklist</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-check mr-1"></i> Checklist Patching</h3>
                <span class="text-muted small">Jadwal: <?= e($sch['name']) ?></span>
            </div>
            <div class="card-body">
                <p class="text-muted small">Centang setiap item setelah dilakukan. Checklist otomatis berstatus <strong>Selesai</strong> saat semua item tercentang.</p>
                <?php if (empty($items)): ?>
                    <div class="empty-state"><i class="fas fa-list-check"></i><p class="mt-3">Belum ada item checklist.</p></div>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($items as $it): ?>
                    <div class="list-group-item list-group-item-action <?= (int)$it['is_checked']?'list-group-item-success':'' ?>">
                        <div class="d-flex align-items-start">
                            <div class="mr-3 pt-1">
                                <form action="<?= url('patching/checklist/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline toggle-form" data-item="<?= $it['item_id'] ?>">
                                    <input type="hidden" name="item_id" value="<?= $it['item_id'] ?>">
                                    <input type="hidden" name="checked" value="<?= (int)$it['is_checked'] ? '0' : '1' ?>">
                                    <input type="hidden" name="patch_code" value="<?= e($it['patch_code'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-toggle-<?= (int)$it['is_checked']?'success':'outline-secondary' ?>" style="font-size:1.1rem">
                                        <i class="fas fa-<?= (int)$it['is_checked']?'check-square':'square' ?>"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-0 <?= (int)$it['is_checked']?'text-success':'' ?>">
                                            <?= e($it['item_name']) ?>
                                            <?php if ((int)$it['is_checked']): ?><i class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><?= e($it['item_desc']) ?></small>
                                    </div>
                                    <?php if (!empty($it['patch_code'])): ?>
                                    <span class="badge badge-info ml-2" title="<?= t('patch_code') ?>"><i class="fas fa-tag mr-1"></i><?= e($it['patch_code']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ((int)$it['is_checked'] && !empty($it['checked_by_name'])): ?>
                                    <div class="text-muted small"><i class="far fa-clock"></i> <?= tglwaktu($it['checked_at']) ?> — <?= e($it['checked_by_name']) ?></div>
                                <?php endif; ?>
                                <!-- Input kode patching -->
                                <div class="input-group input-group-sm mt-2 patch-code-group" style="max-width:340px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-code"></i> <?= t('patch_code') ?></span>
                                    </div>
                                    <input type="text" class="form-control patch-code-input" placeholder="<?= t('patch_code_placeholder') ?>" value="<?= e($it['patch_code'] ?? '') ?>" data-checklist="<?= $c['id'] ?>" data-item="<?= $it['item_id'] ?>">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btn-save-code" data-checklist="<?= $c['id'] ?>" data-item="<?= $it['item_id'] ?>"><i class="fas fa-save"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle via AJAX untuk UX lebih cepat
document.querySelectorAll('.toggle-form').forEach(function(form){
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var fd = new FormData(form);
        // Sertakan kode patching yang sedang diinput
        var item = fd.get('item_id');
        var codeInput = document.querySelector('.patch-code-input[data-item="' + item + '"]');
        if (codeInput) { fd.set('patch_code', codeInput.value); }
        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function(r){return r.json();}).then(function(data){
            if(data.ok){ location.reload(); }
        }).catch(function(){ form.submit(); });
    });
});

// Simpan kode patching via AJAX
document.querySelectorAll('.btn-save-code').forEach(function(btn){
    btn.addEventListener('click', function(){
        var checklistId = btn.dataset.checklist;
        var itemId = btn.dataset.item;
        var input = document.querySelector('.patch-code-input[data-item="' + itemId + '"]');
        var code = input ? input.value.trim() : '';
        var fd = new FormData();
        fd.append('item_id', itemId);
        fd.append('patch_code', code);
        fetch('<?= url('patching/checklist/' . $c['id'] . '/save-code') ?>', {
            method: 'POST',
            body: fd,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function(r){return r.json();}).then(function(data){
            if(data.ok){
                // feedback visual
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                setTimeout(function(){
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 1200);
            }
        }).catch(function(){ /* ignore */ });
    });
});
</script>
