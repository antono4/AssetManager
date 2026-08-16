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
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination pagination-sm justify-content-center mb-0">
            <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= url('audit?page=' . ($page-1) . ($module?'&module=' . $module : '')) ?>">&laquo;</a></li>
            <?php for ($i=1; $i<=$totalPages; $i++): ?>
            <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="<?= url('audit?page=' . $i . ($module?'&module=' . $module : '')) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= url('audit?page=' . ($page+1) . ($module?'&module=' . $module : '')) ?>">&raquo;</a></li>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>
