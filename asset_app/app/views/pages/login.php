<?php /** Halaman Login */ ?>
<p class="login-box-msg"><?= t('login_message') ?></p>

<form action="<?= url('login') ?>" method="post">
    <div class="input-group mb-3">
        <input type="text" name="username" class="form-control" placeholder="<?= t('username') ?>" required autofocus
               value="<?= e($_POST['username'] ?? '') ?>">
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-user"></span></div>
        </div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="<?= t('password') ?>" required>
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
        </div>
    </div>
    <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember"><?= t('remember_me') ?></label>
            </div>
        </div>
        <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block"><?= t('sign_in') ?></button>
        </div>
    </div>
</form>
