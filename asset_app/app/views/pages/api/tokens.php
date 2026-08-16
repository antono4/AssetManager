<?php /** API Tokens */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-key mr-1"></i> <?= t('api_token') ?></h3>
    </div>
    <div class="card-body">
        <p class="text-muted small">Generate API token for REST API access. Use header <code>X-Api-Token</code> or query <code>?token=</code>.</p>
        <form method="post" action="<?= url('api-tokens') ?>" class="form-inline mb-3">
            <input type="text" name="name" class="form-control form-control-sm mr-2" placeholder="Token name (optional)">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= t('generate_token') ?></button>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Name</th><th>Token</th><th><?= t('created_at') ?></th><th>Last Used</th><th>User</th><th class="text-center"><?= t('action') ?></th></tr></thead>
                <tbody>
                <?php foreach ($tokens as $t): ?>
                <tr>
                    <td><?= e($t['name'] ?? '-') ?></td>
                    <td><code><?= e(substr($t['token'], 0, 16)) ?>...</code></td>
                    <td><small><?= tglwaktu($t['created_at']) ?></small></td>
                    <td><small><?= $t['last_used_at'] ? tglwaktu($t['last_used_at']) : '-' ?></small></td>
                    <td><small><?= e($t['user_name'] ?? '-') ?></small></td>
                    <td class="text-center">
                        <form action="<?= url('api-tokens/' . $t['id'] . '/delete') ?>" method="post" class="d-inline">
                            <button class="btn btn-danger btn-sm btn-delete" data-confirm="<?= t('delete') ?>?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <hr>
        <h6><?= t('api_docs') ?></h6>
        <pre class="bg-light p-2 rounded"><code>GET /api/assets
Header: X-Api-Token: YOUR_TOKEN
 atau
GET /api/assets?token=YOUR_TOKEN</code></pre>
    </div>
</div>
