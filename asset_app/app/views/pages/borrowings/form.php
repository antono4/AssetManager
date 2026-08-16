<?php /** Borrow form */ $a = $asset; ?>
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-hand-paper mr-1"></i> <?= t('borrow') ?>: <?= e($a['asset_code']) ?></h3></div>
    <form method="post" action="<?= url('assets/' . $a['id'] . '/borrow') ?>">
        <div class="card-body">
            <p><?= t('borrow') ?> <strong><?= e($a['name']) ?></strong> (<?= e($a['asset_code']) ?>)</p>
            <div class="form-group">
                <label><?= t('borrower') ?> <span class="text-danger">*</span></label>
                <input type="text" name="borrower_name" class="form-control" required placeholder="<?= t('borrower') ?>">
            </div>
            <div class="form-group">
                <label><?= t('return_date') ?></label>
                <input type="datetime-local" name="expected_return" class="form-control">
            </div>
            <div class="form-group">
                <label><?= t('note_optional') ?></label>
                <textarea name="note" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-hand-paper"></i> <?= t('borrow') ?></button>
            <a href="<?= url('assets/' . $a['id']) ?>" class="btn btn-default"><?= t('cancel') ?></a>
        </div>
    </form>
</div>
