<?php /** Pengaturan Perusahaan — nama, alamat, telepon, email */
$c = $company;
?>
<div class="row">
    <div class="col-md-3">
        <div class="card card-info card-outline">
            <div class="card-body text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-info mb-3" style="width:110px;height:110px;border-radius:50%">
                    <i class="fas fa-building text-white" style="font-size:3rem"></i>
                </div>
                <h4 class="mb-0"><?= e($c['name']) ?></h4>
                <p class="text-muted small"><?= t('company_settings') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building mr-1"></i> <?= t('company_info') ?></h3>
            </div>
            <form method="post" action="<?= url('settings') ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label><?= t('company_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" required
                               value="<?= e($c['name']) ?>"
                               placeholder="<?= e(APP_NAME) ?>">
                        <small class="text-muted"><?= t('company_name_hint') ?></small>
                    </div>
                    <div class="form-group">
                        <label><?= t('company_address') ?></label>
                        <textarea name="company_address" class="form-control" rows="3"
                                  placeholder="<?= t('company_address_placeholder') ?>"><?= e($c['address']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-phone mr-1"></i> <?= t('company_phone') ?></label>
                                <input type="text" name="company_phone" class="form-control"
                                       value="<?= e($c['phone']) ?>"
                                       placeholder="021-1234567">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-envelope mr-1"></i> <?= t('email') ?></label>
                                <input type="email" name="company_email" class="form-control"
                                       value="<?= e($c['email']) ?>"
                                       placeholder="info@perusahaan.com">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('save') ?></button>
                    <a href="<?= url('dashboard') ?>" class="btn btn-default"><i class="fas fa-arrow-left"></i> <?= t('back') ?></a>
                </div>
            </form>
        </div>

        <div class="card card-default card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> <?= t('company_usage') ?></h3></div>
            <div class="card-body">
                <p class="text-muted small mb-0"><?= t('company_usage_desc') ?></p>
                <ul class="small text-muted mb-0">
                    <li><?= t('company_usage_sidebar') ?></li>
                    <li><?= t('company_usage_report') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
