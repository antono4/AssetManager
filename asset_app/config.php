<?php
// ============================================================================
//  KONFIGURASI APLIKASI MANAJEMEN ASET
// ============================================================================

// --- Pengaturan Aplikasi ---
define('APP_NAME', 'AssetManager');
define('APP_VERSION', '1.0.0');

// Hitung BASE_URL otomatis dari request (sub-folder aware).
// Dengan PHP built-in server + router, SCRIPT_NAME = path request, jadi
// kita andalkan PATH_INFO / REQUEST_URI relatif terhadap document root.
$_scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$_self = $_SERVER['PHP_SELF'] ?? $_scriptName;
// Jika SCRIPT_NAME menunjuk file index.php nyata, ambil foldernya.
if (preg_match('#/index\.php$#', $_scriptName)) {
    $_base = str_replace('\\', '/', dirname($_scriptName));
} elseif (isset($_SERVER['SCRIPT_FILENAME']) && is_file($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'index.php') {
    // Built-in server dengan router: index.php adalah router tunggal -> root
    $_base = '';
} else {
    // Fallback: anggap root
    $_base = '';
}
$_base = rtrim($_base, '/');
define('BASE_URL', getenv('APP_BASE_URL') !== false ? getenv('APP_BASE_URL') : $_base);

// --- Loader .env sederhana (tanpa dependency) ---
// Bila ada file .env di root app, muat ke getenv() agar konfigurasi DB (dan
// APP_BASE_URL) bisa diatur lewat file alih-alih env var OS. Berguna untuk
// XAMPP/Apache di Windows di mana set env var merepotkan. Env var OS yang
// sudah ada tetap dipakai (prioritas: OS env > .env file > default).
$__envFile = __DIR__ . '/.env';
if (is_file($__envFile)) {
    foreach (file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        $__line = trim($__line);
        if ($__line === '' || $__line[0] === '#') {
            continue;
        }
        if (!str_contains($__line, '=')) {
            continue;
        }
        [$__k, $__v] = explode('=', $__line, 2);
        $__k = trim($__k);
        $__v = trim($__v);
        // Buang tanda kutip pembungkus ("..." atau '...')
        if (strlen($__v) >= 2 && $__v[0] === $__v[-1] && ($__v[0] === '"' || $__v[0] === "'")) {
            $__v = substr($__v, 1, -1);
        }
        if (getenv($__k) === false) {
            putenv("$__k=$__v");
            $_ENV[$__k] = $__v;
        }
    }
}

// --- Pilihan Database ---
// Aplikasi hanya mendukung MySQL (koneksi demo SQLite sudah dihapus).
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');

// Konfigurasi MySQL
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'assets_app');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// --- Sesi & Keamanan ---
define('SESSION_NAME', 'asset_app_session');
define('SESSION_LIFETIME', 7200); // 2 jam
define('HASH_COST', 10);

// --- Path Aplikasi ---
define('APP_PATH', __DIR__ . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', __DIR__ . '/public');

// --- Default Akun (dipakai seeder/setup) ---
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');
define('DEFAULT_STAFF_USER', 'staff');
define('DEFAULT_STAFF_PASS', 'staff123');

// --- Timezone ---
date_default_timezone_set('Asia/Jakarta');

// --- Error reporting (matikan di produksi) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');

// --- Helper autoload ---
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// --- Inisialisasi bahasa (default English) ---
Lang::init();

// --- Helper functions global (url, asset_url, e, rp, dll) ---
require_once APP_PATH . '/core/helpers.php';
