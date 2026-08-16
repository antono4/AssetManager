<?php /** Halaman Error */ ?>
<div class="error-page">
    <h2 class="headline text-warning"><?= e($code ?? 404) ?></h2>
    <div class="error-content">
        <h3><i class="fas fa-exclamation-triangle text-warning"></i> <?= t('oops') ?></h3>
        <p><?= e($message ?? t('not_found_message')) ?></p>
        <p>
            <?= t('you_can_go') ?>
            <a href="<?= url('dashboard') ?>"><?= t('go_dashboard') ?></a>
            <?= t('or_try_other') ?>
        </p>
    </div>
</div>
