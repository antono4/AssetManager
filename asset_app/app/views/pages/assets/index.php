<?php /** Daftar Aset + filter/search + pagination */ ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-box mr-1"></i> <?= t('asset_list') ?> <small class="text-muted">(<?= $total ?> <?= t('data') ?>)</small></h3>
        <?php if (Auth::isAdmin()): ?>
        <div class="card-tools">
            <a href="<?= url('assets/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> <?= t('add_asset') ?>
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="get" class="search-bar mb-3">
            <div class="row">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="<?= t('search_placeholder') ?>" value="<?= e($search) ?>">
                        <div class="input-group-append"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value=""><?= t('all_status') ?></option>
                        <option value="tersedia" <?= $status==='tersedia'?'selected':'' ?>><?= t('status_tersedia') ?></option>
                        <option value="dipinjam" <?= $status==='dipinjam'?'selected':'' ?>><?= t('status_dipinjam') ?></option>
                        <option value="rusak"    <?= $status==='rusak'?'selected':'' ?>><?= t('status_rusak') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-control">
                        <option value=""><?= t('all_categories') ?></option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (string)$category===(string)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-default btn-block"><i class="fas fa-filter"></i></button>
                </div>
            </div>
        </form>

        <?php if (empty($assets)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p class="mt-3"><?= t('no_matching_assets') ?></p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-assets">
                <thead>
                <tr>
                    <th><?= t('asset_code') ?></th><th><?= t('name') ?></th><th><?= t('category') ?></th><th><?= t('location') ?></th>
                    <th><?= t('status') ?></th><?php if (price_visible()): ?><th class="text-right"><?= t('price') ?></th><?php endif; ?><th class="text-center"><?= t('action') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($assets as $a): ?>
                    <tr data-href="<?= url('assets/' . $a['id']) ?>">
                        <td class="asset-code"><?= e($a['asset_code']) ?></td>
                        <td><?= e($a['name']) ?><br><small class="text-muted"><?= e($a['brand_spec']) ?></small></td>
                        <td><span class="badge badge-light"><?= e($a['category_name']) ?></span></td>
                        <td><i class="fas fa-map-marker-alt text-muted mr-1"></i><?= e($a['location']) ?: '-' ?></td>
                        <td><?= status_badge($a['status']) ?></td>
                        <?php if (price_visible()): ?><td class="text-right"><?= rp($a['price']) ?></td><?php endif; ?>
                        <td class="text-center">
                            <a href="<?= url('assets/' . $a['id']) ?>" class="btn btn-info btn-sm" title="<?= t('asset_detail') ?>"><i class="fas fa-eye"></i></a>
                            <?php if (Auth::isAdmin()): ?>
                            <a href="<?= url('assets/' . $a['id'] . '/edit') ?>" class="btn btn-warning btn-sm" title="<?= t('edit') ?>"><i class="fas fa-edit"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination pagination-sm justify-content-center mb-0">
            <?php
            $q = http_build_query(array_filter(['search'=>$search,'status'=>$status,'category'=>$category]));
            $base = url('assets') . ($q ? '?' . $q . '&' : '?');
            ?>
            <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= $base ?>page=<?= $page-1 ?>">&laquo;</a></li>
            <?php for ($i=1; $i<=$totalPages; $i++): ?>
                <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="<?= $base ?>page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= $base ?>page=<?= $page+1 ?>">&raquo;</a></li>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>
