<?php /** Halaman Login */ ?>
<p class="login-box-msg">Silakan masuk untuk melanjutkan</p>

<form action="<?= url('login') ?>" method="post">
    <div class="input-group mb-3">
        <input type="text" name="username" class="form-control" placeholder="Username" required autofocus
               value="<?= e($_POST['username'] ?? '') ?>">
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-user"></span></div>
        </div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
        </div>
    </div>
    <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember">Ingat saya</label>
            </div>
        </div>
        <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </div>
    </div>
</form>

<div class="mt-4 text-center">
    <small class="text-muted">
        <i class="fas fa-info-circle"></i>
        Demo: <strong>admin / admin123</strong> &nbsp;|&nbsp; <strong>staff / staff123</strong>
    </small>
</div>
<div class="mt-2 text-center">
    <a href="<?= url('setup') ?>" class="text-muted"><small><i class="fas fa-wrench"></i> Reset password default</small></a>
</div>
