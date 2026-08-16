<?php /** Layout blank untuk login & setup (tanpa sidebar) */ ?>
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
</head>
<body class="hold-transition login-page" style="min-height:100vh;background:linear-gradient(135deg,#2b3a55 0%,#1a2235 100%)">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="<?= url('login') ?>" class="h1"><i class="fas fa-cubes text-primary"></i> <?= APP_NAME ?></a>
        </div>
        <div class="card-body">
            <div class="text-right mb-2">
                <a href="<?= url('language/set?lang=en') ?>" class="btn btn-xs btn-default <?= Lang::is('en')?'active':'' ?>" title="English">
                    <span class="flag-icon flag-icon-us"></span> EN
                </a>
                <a href="<?= url('language/set?lang=id') ?>" class="btn btn-xs btn-default <?= Lang::is('id')?'active':'' ?>" title="Bahasa Indonesia">
                    <span class="flag-icon flag-icon-id"></span> ID
                </a>
            </div>
            <?= flash_messages() ?>
            <?= $content ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html>
