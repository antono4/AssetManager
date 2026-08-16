<?php /** Global Search Results */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-search mr-1"></i> <?= t('search_results') ?>: "<?= e($q) ?>"</h3>
    </div>
    <div class="card-body">
        <?php
        $total = count($results['assets'] ?? []) + count($results['users'] ?? []) + count($results['categories'] ?? []) + count($results['patching'] ?? []);
        if ($total === 0):
        ?>
        <div class="empty-state"><i class="fas fa-search"></i><p class="mt-3"><?= t('no_results') ?></p></div>
        <?php else: ?>
            <?php if (!empty($results['assets'])): ?>
            <h5><i class="fas fa-box mr-1"></i> <?= t('assets') ?></h5>
            <div class="list-group mb-3">
                <?php foreach ($results['assets'] as $a): ?>
                <a href="<?= url('assets/' . $a['id']) ?>" class="list-group-item list-group-item-action">
                    <span class="asset-code text-primary"><?= e($a['asset_code']) ?></span> — <?= e($a['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($results['users'])): ?>
            <h5><i class="fas fa-users mr-1"></i> <?= t('user_management') ?></h5>
            <div class="list-group mb-3">
                <?php foreach ($results['users'] as $u): ?>
                <a href="<?= url('users/' . $u['id'] . '/activity') ?>" class="list-group-item list-group-item-action">
                    <strong><?= e($u['title']) ?></strong> — <?= e($u['sub'] ?? '') ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($results['categories'])): ?>
            <h5><i class="fas fa-tags mr-1"></i> <?= t('categories') ?></h5>
            <div class="list-group mb-3">
                <?php foreach ($results['categories'] as $c): ?>
                <a href="<?= url('categories') ?>" class="list-group-item list-group-item-action">
                    <strong><?= e($c['title']) ?></strong> — <?= e($c['sub'] ?? '') ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($results['patching'])): ?>
            <h5><i class="fas fa-shield-alt mr-1"></i> <?= t('patching') ?></h5>
            <div class="list-group mb-3">
                <?php foreach ($results['patching'] as $s): ?>
                <a href="<?= url('patching/' . $s['id']) ?>" class="list-group-item list-group-item-action">
                    <strong><?= e($s['title']) ?></strong> — <?= e($s['sub'] ?? '') ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
