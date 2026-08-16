<?php /** Audit Trail */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list mr-1"></i> <?= t('audit_trail') ?> (<?= $total ?>)</h3>
        <div class="card-tools">
            <form method="get" class="d-inline">
                <select name="module" class="form-control form-control-sm d-inline" style="width:140px" onchange="this.form.submit()">
                    <option value="">All <?= t('module') ?></option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?= e($m['module']) ?>" <?= $module===$m['module']?'selected':'' ?>><?= ucfirst(e($m['module'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><?= t('time') ?></th><th><?= t('module') ?></th><th><?= t('action_col') ?></th><th>Description</th><th>User</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td><small><?= tglwaktu($l['created_at']) ?></small></td>
                    <td><span class="badge badge-info"><?= e($l['module']) ?></span></td>
                    <td><span class="badge badge-secondary"><?= e($l['action']) ?></span></td>
                    <td><?= e($l['description'] ?? '-') ?></td>
                    <td><small><?= e($l['user_name'] ?? 'System') ?></small></td>
                    <td><small class="text-muted"><?= e($l['ip'] ?? '-') ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    $auditBase = url('audit?') . ($module ? 'module=' . urlencode($module) . '&' : '');
    $auditPerPage = 30;
    ?>
    <?= pagination($page, $totalPages, $auditBase, $total, $auditPerPage) ?>
</div>
