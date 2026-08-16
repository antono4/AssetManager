<?php /** Import CSV */ ?>
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-file-import mr-1"></i> <?= t('import_assets') ?></h3></div>
    <form method="post" action="<?= url('assets/import') ?>" enctype="multipart/form-data">
        <div class="card-body">
            <p class="text-muted small"><?= t('import_hint') ?></p>
            <div class="form-group">
                <label>CSV File</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-file-csv"></i></span></div>
                    <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv" required>
                </div>
                <small class="text-muted">.csv file only</small>
            </div>
            <a href="<?= url('assets/csv-template') ?>" class="btn btn-default btn-sm"><i class="fas fa-download"></i> <?= t('download_template') ?></a>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> <?= t('import_csv') ?></button>
            <a href="<?= url('assets') ?>" class="btn btn-default"><?= t('cancel') ?></a>
        </div>
    </form>
</div>
