<?php /** Halaman Login — modern glassmorphism form */ ?>
<div class="login-head">
    <div class="head-ic"><i class="fas fa-fingerprint"></i></div>
    <h3><?= t('login_welcome') ?? 'Welcome Back' ?></h3>
    <p><?= t('login_message') ?></p>
</div>

<form action="<?= url('login') ?>" method="post" autocomplete="on">
    <div class="login-field">
        <input type="text" name="username" id="login-username" placeholder="<?= t('username') ?>" required autofocus
               value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username">
        <i class="fas fa-user field-ic"></i>
    </div>

    <div class="login-field">
        <input type="password" name="password" id="login-password" placeholder="<?= t('password') ?>" required autocomplete="current-password">
        <i class="fas fa-lock field-ic"></i>
        <button type="button" class="pw-toggle" tabindex="-1" aria-label="toggle password"><i class="fas fa-eye"></i></button>
    </div>

    <div class="login-row">
        <label class="login-check">
            <input type="checkbox" id="remember">
            <span class="check-box"></span>
            <?= t('remember_me') ?>
        </label>
    </div>

    <button type="submit" class="btn-login">
        <i class="fas fa-arrow-right-to-bracket mr-1"></i> <?= t('sign_in') ?>
    </button>
</form>

<div class="login-divider"><?= t('login_secure') ?? 'Secured Login' ?></div>

<div class="login-sec">
    <i class="fas fa-lock"></i> <?= t('login_secured_note') ?? 'Your data is protected with bcrypt encryption' ?>
</div>
