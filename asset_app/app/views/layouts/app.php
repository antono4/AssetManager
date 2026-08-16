<?php
// ============================================================================
//  LAYOUT: APP (utama, dengan sidebar AdminLTE 3)
//  Menggunakan AdminLTE 3 via CDN + Bootstrap Icons + overlay-scrollbar + ApexCharts
// ============================================================================
/** @var string $content */
/** @var string $pageTitle */
/** @var array $currentUser */
use app\core\Auth;
$u = $currentUser;
$role = $u['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= e($pageTitle) ?> &middot; <?= APP_NAME ?></title>

    <!-- AdminLTE 3 (Bootstrap 4 + tema) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body class="hold-transition sidebar-mini layout-navbar-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a class="nav-link" href="<?= url('dashboard') ?>">Home</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a class="nav-link" href="<?= url('assets') ?>">Aset</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?= url('profile') ?>" title="Profil">
                    <i class="fas fa-user-circle"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('logout') ?>" title="Logout" onclick="return confirm('Keluar dari aplikasi?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-info elevation-4">
        <a href="<?= url('dashboard') ?>" class="brand-link">
            <i class="nav-icon fas fa-cubes brand-image ml-3 mt-1" style="font-size:1.6rem;color:#fff;opacity:.9"></i>
            <span class="brand-text font-weight-bold"><?= APP_NAME ?></span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <span class="brand-image img-circle elevation-2 d-flex align-items-center justify-content-center"
                          style="width:34px;height:34px;background:#fff;color:#343a40;font-weight:700">
                        <?= strtoupper(substr($u['name'] ?? '?', 0, 1)) ?>
                    </span>
                </div>
                <div class="info">
                    <a href="<?= url('profile') ?>" class="d-block"><?= e($u['name'] ?? 'Guest') ?></a>
                    <span class="badge badge-<?= $role === 'admin' ? 'danger' : 'info' ?>"><?= ucfirst($role) ?></span>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="<?= url('dashboard') ?>" class="nav-link <?= ($pageTitle === 'Dashboard') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('assets') ?>" class="nav-link <?= str_starts_with($pageTitle, 'Daftar Aset') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-box"></i><p>Aset</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('logs') ?>" class="nav-link <?= $pageTitle === 'Riwayat Aktivitas' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-history"></i><p>Riwayat</p>
                        </a>
                    </li>
                    <?php if ($role === 'admin'): ?>
                    <li class="nav-header">ADMINISTRASI</li>
                    <li class="nav-item">
                        <a href="<?= url('categories') ?>" class="nav-link <?= $pageTitle === 'Kategori Aset' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tags"></i><p>Kategori</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('users') ?>" class="nav-link <?= $pageTitle === 'Manajemen User' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-users"></i><p>Manajemen User</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('setup') ?>" class="nav-link">
                            <i class="nav-icon fas fa-wrench"></i><p>Setup Password</p>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1><?= e($pageTitle) ?></h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active"><?= e($pageTitle) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?= flash_messages() ?>
                <?= $content ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?>.</strong>
        Dibuat dengan PHP Native &amp; AdminLTE 3.
        <div class="float-right d-none d-sm-inline-block">
            <b>Database:</b> <?= Database::driver() === 'mysql' ? 'MySQL' : 'SQLite (Demo)' ?>
        </div>
    </footer>
</div>

<!-- jQuery + Bootstrap + AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<!-- ApexCharts untuk grafik -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script src="<?= asset_url('js/app.js') ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
