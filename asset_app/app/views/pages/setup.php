<?php /** Halaman Setup - reset password default */ ?>
<p class="login-box-msg"><i class="fas fa-wrench text-primary"></i> Setup Password Default</p>

<?php if (!empty($updated)): ?>
    <div class="alert alert-success">
        <i class="icon fas fa-check-circle"></i> Password default berhasil diperbarui!
        <hr>
        <ul class="mb-0">
            <?php foreach ($updated as $u): ?>
                <li><code><?= e($u) ?></code></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <a href="<?= url('login') ?>" class="btn btn-primary btn-block">
        <i class="fas fa-sign-in-alt"></i> Lanjut ke Login
    </a>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="icon fas fa-exclamation-triangle"></i>
        Tidak ada akun default (admin/staff) yang ditemukan di database.
    </div>
<?php endif; ?>
