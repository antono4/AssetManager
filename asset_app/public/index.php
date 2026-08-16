<?php
// ============================================================================
//  ENTRY POINT - Aplikasi Manajemen Aset
// ============================================================================

// Untuk PHP built-in server: jika request adalah file/direktori static yang
// ada di document root, kembalikan false agar server menyajikannya langsung.
$_reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$_reqPath = ltrim($_reqPath, '/');
if ($_reqPath !== '' && $_reqPath !== 'index.php') {
    $candidate = __DIR__ . '/' . $_reqPath;
    if (file_exists($candidate) && !is_dir($candidate)) {
        return false; // biarkan built-in server serve static file
    }
}

require_once __DIR__ . '/../config.php';

Auth::startSession();

// Pastikan skema & data dummy ada (auto untuk SQLite; untuk MySQL
// disarankan import assets_app.sql terlebih dahulu, tapi tetap aman
// bila tabel users belum ada -> dilewati tanpa error fatal di sini).
try {
    Database::ensureSchema();
} catch (Throwable $e) {
    // Untuk MySQL bila belum di-import, tampilkan pesan setup yang jelas
    // hanya pada route non-setup agar /setup tetap bisa dipakai.
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/setup') === false) {
        // Abaikan error koneksi DB agar /setup bisa dipakai memperbaiki.
    }
}

// Ambil path relatif. Dengan PHP built-in server + router, REQUEST_URI sudah
// berupa path bersih (tanpa index.php prefix). Untuk Apache/nginx dengan
// index.php di sub-folder, strip prefix BASE_URL bila ada.
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = $requestUri;

// Normalisasi: hapus kemungkinan /index.php prefix
if (preg_match('#(?:^|/)/index\.php#', $path)) {
    $path = preg_replace('#^/index\.php#', '', $path);
}
// Hapus BASE_URL prefix bila ada (sub-folder deployment non-router)
$base = BASE_URL;
if ($base !== '' && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
}
// Jika path masih punya prefix base (double), strip lagi (XAMPP edge case)
if ($base !== '' && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
}
$path = '/' . ltrim($path, '/');
if ($path === '/index.php') {
    $path = '/';
}

// --- Definisi Routes ---
$auth = new AuthController();
$dash = new DashboardController();
$assetCtl = new AssetController();
$catCtl = new CategoryController();
$userCtl = new UserController();
$logCtl = new LogController();

// Auth
Router::get('/login',  fn() => $auth->loginForm());
Router::post('/login', fn() => $auth->login());
Router::get('/logout', fn() => $auth->logout());
Router::get('/setup',  fn() => $auth->setup());

// Language switcher
Router::get('/language/set', function () {
    $lang = $_GET['lang'] ?? 'en';
    Lang::set($lang);
    $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard';
    Auth::redirect($back);
});

// Dark mode toggle
Router::get('/dark-mode', fn() => (new ExtendedController())->darkMode());

// Global search
Router::get('/search', fn() => (new ExtendedController())->search());

// Export & Import CSV
Router::get('/assets/export', fn() => (new ExtendedController())->exportCsv());
Router::get('/assets/import', fn() => (new ExtendedController())->importForm());
Router::post('/assets/import', fn() => (new ExtendedController())->import());
Router::get('/assets/csv-template', fn() => (new ExtendedController())->csvTemplate());

// Trash (soft delete)
Router::get('/assets/trash', fn() => (new ExtendedController())->trash());
Router::post('/assets/{id}/restore', fn($p) => (new ExtendedController())->restore($p));
Router::post('/assets/{id}/force-delete', fn($p) => (new ExtendedController())->forceDelete($p));

// Borrowing
Router::get('/borrowings', fn() => (new ExtendedController())->borrowings());
Router::get('/assets/{id}/borrow', fn($p) => (new ExtendedController())->borrowForm($p));
Router::post('/assets/{id}/borrow', fn($p) => (new ExtendedController())->borrow($p));
Router::post('/borrowings/{id}/return', fn($p) => (new ExtendedController())->returnAsset($p));

// Notifications
Router::get('/notifications', fn() => (new ExtendedController())->notifications());
Router::post('/notifications/{id}/read', fn($p) => (new ExtendedController())->markNotifRead($p));
Router::post('/notifications/read-all', fn() => (new ExtendedController())->markAllNotifRead());

// Audit Trail
Router::get('/audit', fn() => (new ExtendedController())->audit());

// API Tokens
Router::get('/api-tokens', fn() => (new ExtendedController())->apiTokens());
Router::post('/api-tokens', fn() => (new ExtendedController())->generateToken());
Router::post('/api-tokens/{id}/delete', fn($p) => (new ExtendedController())->deleteToken($p));

// REST API endpoint
Router::get('/api/assets', fn() => (new ExtendedController())->apiAssets());

// Activity by user
Router::get('/users/{id}/activity', fn($p) => (new ExtendedController())->activityByUser($p));

// Dashboard
Router::get('/',         fn() => $dash->index());
Router::get('/dashboard', fn() => $dash->index());

// Assets
Router::get('/assets',         fn() => $assetCtl->index());
Router::get('/assets/create',  fn() => $assetCtl->create());
Router::post('/assets',        fn() => $assetCtl->store());
Router::get('/assets/{id}',    fn($p) => $assetCtl->show($p));
Router::get('/assets/{id}/edit', fn($p) => $assetCtl->edit($p));
Router::post('/assets/{id}',   fn($p) => $assetCtl->update($p));
Router::post('/assets/{id}/delete', fn($p) => $assetCtl->delete($p));
Router::post('/assets/{id}/status', fn($p) => $assetCtl->status($p));
Router::post('/assets/{id}/remove-photo', fn($p) => $assetCtl->removePhoto($p));

// Categories
Router::get('/categories',     fn() => $catCtl->index());
Router::post('/categories',    fn() => $catCtl->store());
Router::post('/categories/{id}', fn($p) => $catCtl->update($p));
Router::post('/categories/{id}/delete', fn($p) => $catCtl->delete($p));

// Users
Router::get('/users',          fn() => $userCtl->index());
Router::post('/users',         fn() => $userCtl->store());
Router::post('/users/{id}',    fn($p) => $userCtl->update($p));
Router::post('/users/{id}/delete', fn($p) => $userCtl->delete($p));
Router::get('/profile',        fn() => $userCtl->profile());
Router::post('/profile',       fn() => $userCtl->updateProfile());

// Logs
Router::get('/logs',           fn() => $logCtl->index());

// Reports
$repCtl = new ReportController();
Router::get('/reports',        fn() => $repCtl->index());
Router::get('/reports/print',  fn() => $repCtl->print());

// Patching (jadwal & checklist kuartalan)
$patchCtl = new PatchController();
Router::get('/patching',                     fn() => $patchCtl->index());
Router::get('/patching/create',              fn() => $patchCtl->create());
Router::post('/patching',                    fn() => $patchCtl->store());
Router::get('/patching/{id}',                fn($p) => $patchCtl->show($p));
Router::get('/patching/{id}/edit',           fn($p) => $patchCtl->edit($p));
Router::post('/patching/{id}',               fn($p) => $patchCtl->update($p));
Router::post('/patching/{id}/delete',        fn($p) => $patchCtl->delete($p));
Router::post('/patching/{id}/generate',      fn($p) => $patchCtl->generate($p));
Router::post('/patching/{id}/generate-all',  fn($p) => $patchCtl->generateAll($p));
Router::get('/patching/checklist/{id}',      fn($p) => $patchCtl->checklist($p));
Router::post('/patching/checklist/{id}/toggle',     fn($p) => $patchCtl->toggle($p));
Router::post('/patching/checklist/{id}/save-code',  fn($p) => $patchCtl->saveCode($p));
Router::post('/patching/checklist/{id}/status',     fn($p) => $patchCtl->setChecklistStatus($p));
Router::post('/patching/checklist/{id}/delete',     fn($p) => $patchCtl->deleteChecklist($p));
Router::get('/patching/{id}/computers',             fn($p) => $patchCtl->computers($p));

// Dispatch
Router::dispatch($path, $_SERVER['REQUEST_METHOD'] ?? 'GET');
