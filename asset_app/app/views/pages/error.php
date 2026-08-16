<?php /** Halaman Error */ ?>
<div class="error-page">
    <h2 class="headline text-warning"><?= e($code ?? 404) ?></h2>
    <div class="error-content">
        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops!</h3>
        <p><?= e($message ?? 'Terjadi kesalahan.') ?></p>
        <p>
            Anda bisa kembali ke
            <a href="<?= url('dashboard') ?>">Dashboard</a>
            atau mencoba halaman lain.
        </p>
    </div>
</div>
