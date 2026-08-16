<?php /** Layout blank untuk login & setup (split-screen modern) */ ?>
<!DOCTYPE html>
<html lang="<?= Lang::current() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css@3.5.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/login.css') ?>">
</head>
<body class="hold-transition login-page">
<div class="login-wrap">

    <!-- Left visual panel -->
    <div class="login-hero">
        <div class="login-hero-grid"></div>
        <div class="login-particles" id="particles"></div>
        <div class="login-hero-content">
            <a href="<?= url('login') ?>" class="login-hero-logo">
                <span class="logo-icon"><i class="fas fa-cubes"></i></span>
                <span><?= APP_NAME ?></span>
            </a>
            <h2><?= t('login_hero_title') ?? 'Manage Your Assets with Confidence' ?></h2>
            <p class="hero-sub"><?= t('login_hero_sub') ?? 'Complete IT & general asset management — track, patch, and report — all in one secure platform.' ?></p>
            <div class="login-features">
                <div class="login-feature">
                    <span class="feat-ic ic-1"><i class="fas fa-boxes-stacked"></i></span>
                    <span class="feat-tx"><?= t('login_feat1_t') ?? 'Asset Tracking' ?><small><?= t('login_feat1_d') ?? 'Real-time inventory & status' ?></small></span>
                </div>
                <div class="login-feature">
                    <span class="feat-ic ic-2"><i class="fas fa-shield-halved"></i></span>
                    <span class="feat-tx"><?= t('login_feat2_t') ?? 'Quarterly Patching' ?><small><?= t('login_feat2_d') ?? 'Scheduled maintenance checklists' ?></small></span>
                </div>
                <div class="login-feature">
                    <span class="feat-ic ic-3"><i class="fas fa-chart-pie"></i></span>
                    <span class="feat-tx"><?= t('login_feat3_t') ?? 'Reports & Analytics' ?><small><?= t('login_feat3_d') ?? 'Insightful dashboards & export' ?></small></span>
                </div>
            </div>
        </div>
        <div class="login-hero-foot">&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?></div>
    </div>

    <!-- Right form panel -->
    <div class="login-form-panel">
        <div class="login-card">
            <div class="login-lang">
                <a href="<?= url('language/set?lang=en') ?>" class="<?= Lang::is('en')?'active':'' ?>"><span class="flag-icon flag-icon-us"></span> EN</a>
                <a href="<?= url('language/set?lang=id') ?>" class="<?= Lang::is('id')?'active':'' ?>"><span class="flag-icon flag-icon-id"></span> ID</a>
            </div>
            <?= flash_messages() ?>
            <?= $content ?>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<script>
// Generate floating particles
(function(){
    var c = document.getElementById('particles');
    if(!c) return;
    for(var i=0;i<18;i++){
        var s = document.createElement('span');
        s.style.left = Math.random()*100 + '%';
        s.style.width = s.style.height = (3 + Math.random()*6) + 'px';
        s.style.animationDuration = (8 + Math.random()*12) + 's';
        s.style.animationDelay = (-Math.random()*15) + 's';
        s.style.opacity = (.3 + Math.random()*.5);
        c.appendChild(s);
    }
})();
// Toggle password visibility
document.querySelectorAll('.pw-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
        var inp = btn.parentElement.querySelector('input');
        if(!inp) return;
        if(inp.type === 'password'){ inp.type = 'text'; btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; }
        else { inp.type = 'password'; btn.innerHTML = '<i class="fas fa-eye"></i>'; }
    });
});
</script>
</body>
</html>
