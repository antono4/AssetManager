<?php /** Layout print-friendly (tanpa sidebar, tanpa JS interaktif) */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset_url('img/favicon.svg') ?>">
    <link rel="shortcut icon" href="<?= asset_url('img/favicon.ico') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/print.css') ?>">
</head>
<body class="print-body">
    <div class="print-toolbar no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
        <a href="<?= url('reports') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="print-container">
        <?= $content ?>
    </div>
</body>
</html>
