<?php
// ============================================================================
//  DATABASE - PDO adapter (MySQL + SQLite)
//  Menerjemahkan skema MySQL ke SQLite saat mode demo.
// ============================================================================

class Database
{
    private static ?PDO $instance = null;

    public static function conn(): PDO
    {
        if (self::$instance === null) {
            try {
                if (DB_DRIVER === 'mysql') {
                    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                } else {
                    // SQLite demo
                    $dir = dirname(SQLITE_PATH);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    self::$instance = new PDO('sqlite:' . SQLITE_PATH, null, null, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                    self::$instance->exec('PRAGMA foreign_keys = ON');
                }
            } catch (PDOException $e) {
                throw new RuntimeException('Koneksi database gagal: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function driver(): string
    {
        return DB_DRIVER;
    }

    public static function isSqlite(): bool
    {
        return DB_DRIVER === 'sqlite';
    }

    // Inisialisasi skema bila tabel belum ada
    public static function ensureSchema(): void
    {
        $db = self::conn();
        $check = $db->query("SELECT name FROM " . (self::isSqlite() ? "sqlite_master" : "information_schema.tables") . " WHERE name = 'users'");
        if (!$check || !$check->fetch()) {
            if (self::isSqlite()) {
                self::createSqliteSchema($db);
            } else {
                $sql = file_get_contents(__DIR__ . '/../database/assets_app.sql');
                $db->exec($sql);
            }
            self::seed($db);
        }
        // Migrasi tabel fitur baru (patching) — idempoten
        self::migratePatching($db);
    }

    // Migrasi tabel patching (jadwal & checklist) untuk SQLite & MySQL
    private static function migratePatching(PDO $db): void
    {
        $sqlite = self::isSqlite();
        // SQLite: "id INTEGER PRIMARY KEY AUTOINCREMENT", tanpa baris KEY terpisah
        // MySQL  : "id INT UNSIGNED NOT NULL AUTO_INCREMENT" + baris KEY/PRIMARY KEY
        $idCol    = $sqlite ? 'id INTEGER PRIMARY KEY AUTOINCREMENT' : 'id INT UNSIGNED NOT NULL AUTO_INCREMENT';
        $statusCol = function (array $vals, string $default) use ($sqlite) {
            return $sqlite
                ? "TEXT NOT NULL DEFAULT '" . $default . "'"
                : "ENUM('" . implode("','", $vals) . "') NOT NULL DEFAULT '" . $default . "'";
        };
        $dt   = $sqlite ? 'TEXT' : 'DATETIME';
        $dcol = $sqlite ? 'TEXT' : 'DATE';
        $tiny = $sqlite ? 'INTEGER' : 'TINYINT(1)';
        $tail = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        // Helper untuk menambah baris KEY (hanya MySQL; SQLite abaikan)
        $key = function (string $line) use ($sqlite) {
            return $sqlite ? '' : $line . ',';
        };

        // patch_items — template item checklist patching
        $db->exec("CREATE TABLE IF NOT EXISTS patch_items (
            {$idCol},
            name VARCHAR(120) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            is_active {$tiny} NOT NULL DEFAULT 1,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at {$dt} DEFAULT CURRENT_TIMESTAMP
        ){$tail}");

        // patch_schedules — jadwal patching kuartalan
        $schedStatus = $statusCol(['draft','ongoing','completed'], 'draft');
        $db->exec("CREATE TABLE IF NOT EXISTS patch_schedules (
            {$idCol},
            name VARCHAR(120) NOT NULL,
            quarter INTEGER NOT NULL,
            year INTEGER NOT NULL,
            start_date {$dcol} DEFAULT NULL,
            due_date {$dcol} DEFAULT NULL,
            status {$schedStatus},
            description VARCHAR(255) DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            created_at {$dt} DEFAULT CURRENT_TIMESTAMP,
            updated_at {$dt} DEFAULT CURRENT_TIMESTAMP
            " . $key('PRIMARY KEY (id)') . "
            " . $key('KEY idx_patch_sched_quarter (year, quarter)') . "
            " . $key('KEY idx_patch_sched_status (status)') . "
        ){$tail}");

        // patch_checklists — per aset per jadwal
        $clStatus = $statusCol(['pending','in_progress','completed','skipped'], 'pending');
        $db->exec("CREATE TABLE IF NOT EXISTS patch_checklists (
            {$idCol},
            schedule_id INTEGER NOT NULL,
            asset_id INTEGER NOT NULL,
            status {$clStatus},
            patched_by INTEGER DEFAULT NULL,
            patched_at {$dt} DEFAULT NULL,
            notes VARCHAR(255) DEFAULT NULL,
            created_at {$dt} DEFAULT CURRENT_TIMESTAMP,
            updated_at {$dt} DEFAULT CURRENT_TIMESTAMP
            " . $key('PRIMARY KEY (id)') . "
            " . $key('UNIQUE KEY uq_checklist (schedule_id, asset_id)') . "
            " . $key('KEY idx_checklist_schedule (schedule_id)') . "
            " . $key('KEY idx_checklist_asset (asset_id)') . "
            " . $key('KEY idx_checklist_status (status)') . "
        ){$tail}");

        // patch_checklist_items — item yang dicentang per checklist
        $db->exec("CREATE TABLE IF NOT EXISTS patch_checklist_items (
            {$idCol},
            checklist_id INTEGER NOT NULL,
            item_id INTEGER NOT NULL,
            is_checked {$tiny} NOT NULL DEFAULT 0,
            checked_by INTEGER DEFAULT NULL,
            checked_at {$dt} DEFAULT NULL,
            notes VARCHAR(255) DEFAULT NULL
            " . $key('PRIMARY KEY (id)') . "
            " . $key('UNIQUE KEY uq_checklist_item (checklist_id, item_id)') . "
            " . $key('KEY idx_pcli_checklist (checklist_id)') . "
        ){$tail}");

        // Index untuk SQLite (CREATE INDEX terpisah, aman bila sudah ada)
        if ($sqlite) {
            @$db->exec('CREATE INDEX IF NOT EXISTS idx_patch_sched_quarter ON patch_schedules(year, quarter)');
            @$db->exec('CREATE INDEX IF NOT EXISTS idx_patch_sched_status ON patch_schedules(status)');
            @$db->exec('CREATE INDEX IF NOT EXISTS idx_checklist_schedule ON patch_checklists(schedule_id)');
            @$db->exec('CREATE INDEX IF NOT EXISTS idx_checklist_asset ON patch_checklists(asset_id)');
            @$db->exec('CREATE INDEX IF NOT EXISTS idx_checklist_status ON patch_checklists(status)');
            @$db->exec('CREATE INDEX IF NOT EXISTS idx_pcli_checklist ON patch_checklist_items(checklist_id)');
        }

        // Migrasi kolom patch_code pada patch_checklist_items (kode patching, mis: KB5079473)
        if ($sqlite) {
            $cols = $db->query("PRAGMA table_info(patch_checklist_items)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('patch_code', $cols, true)) {
                $db->exec("ALTER TABLE patch_checklist_items ADD COLUMN patch_code VARCHAR(80) DEFAULT NULL");
            }
        } else {
            $hasCol = $db->query("SHOW COLUMNS FROM patch_checklist_items LIKE 'patch_code'")->fetch();
            if (!$hasCol) {
                $db->exec("ALTER TABLE patch_checklist_items ADD COLUMN patch_code VARCHAR(80) DEFAULT NULL AFTER notes");
            }
        }

        // Seed template item patching bila kosong
        $cnt = (int)$db->query("SELECT COUNT(*) FROM patch_items")->fetchColumn();
        if ($cnt === 0) {
            $defaults = [
                ['Update Sistem Operasi / Firmware', 'Patch OS terbaru atau firmware perangkat', 1],
                ['Update Antivirus / Security', 'Update definisi virus & security patch', 2],
                ['Backup Data', 'Backup konfigurasi & data penting', 3],
                ['Cek Log Sistem', 'Tinjau log sistem untuk error/anomali', 4],
                ['Restart Layanan', 'Restart service/daemon kritis', 5],
                ['Verifikasi Konektivitas', 'Tes koneksi jaringan & fungsi perangkat', 6],
            ];
            $stmt = $db->prepare("INSERT INTO patch_items (name, description, sort_order, is_active) VALUES (?, ?, ?, 1)");
            foreach ($defaults as $it) {
                $stmt->execute($it);
            }
        }
    }

    private static function createSqliteSchema(PDO $db): void
    {
        $db->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            username TEXT NOT NULL UNIQUE,
            email TEXT,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'staff',
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS assets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            asset_code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            category_id INTEGER NOT NULL,
            brand_spec TEXT,
            location TEXT,
            status TEXT NOT NULL DEFAULT 'tersedia',
            purchase_date TEXT,
            price REAL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS asset_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            asset_id INTEGER NOT NULL,
            user_id INTEGER,
            action TEXT NOT NULL,
            note TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_asset_category ON assets(category_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_asset_status ON assets(status)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_log_asset ON asset_logs(asset_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_log_user ON asset_logs(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_user_role ON users(role)");
    }

    private static function seed(PDO $db): void
    {
        // Kategori
        $cats = [
            ['Komputer', 'PC desktop dan workstation'],
            ['Laptop', 'Laptop dan notebook'],
            ['Printer', 'Printer dan scanner'],
            ['Jaringan', 'Switch, router, access point'],
            ['Umum', 'Aset non-IT lainnya'],
        ];
        $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($cats as $c) {
            $stmt->execute($c);
        }

        // Users (password di-hash dengan bcrypt PHP)
        $users = [
            ['Administrator', DEFAULT_ADMIN_USER, 'admin@asset.app', password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT), 'admin'],
            ['Staff Satu', DEFAULT_STAFF_USER, 'staff@asset.app', password_hash(DEFAULT_STAFF_PASS, PASSWORD_BCRYPT), 'staff'],
        ];
        $stmt = $db->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmt->execute($u);
        }

        // Assets
        $assets = [
            ['AST-0001', 'PC Desktop Dev 01', 1, 'Dell OptiPlex 7090 / i7-11700 / 16GB / SSD 512GB', 'Ruang Server', 'tersedia', '2023-02-10', 12500000],
            ['AST-0002', 'PC Desktop Dev 02', 1, 'HP EliteDesk 800 G6 / i5-10500 / 8GB / SSD 256GB', 'Ruang Developer', 'tersedia', '2023-03-15', 9800000],
            ['AST-0003', 'Laptop Marketing', 2, 'Lenovo ThinkPad E14 / Ryzen 5 / 8GB / SSD 512GB', 'Ruang Marketing', 'dipinjam', '2023-05-20', 11000000],
            ['AST-0004', 'Laptop Direksi', 2, 'MacBook Air M2 / 8GB / SSD 256GB', 'Ruang Direksi', 'dipinjam', '2023-06-01', 18000000],
            ['AST-0005', 'Printer Laser HR', 3, 'Brother HL-L2375DW', 'Ruang HRD', 'tersedia', '2022-11-12', 2500000],
            ['AST-0006', 'Printer Inkjet', 3, 'Epson EcoTank L3210', 'Ruang Operasional', 'rusak', '2021-09-08', 2300000],
            ['AST-0007', 'Switch Core', 4, 'Cisco Catalyst 2960 24-Port', 'Ruang Server', 'tersedia', '2022-07-30', 15000000],
            ['AST-0008', 'Access Point', 4, 'TP-Link EAP670 AX3000', 'Lobi Utama', 'tersedia', '2023-08-22', 1800000],
            ['AST-0009', 'AC Split 1 PK', 5, 'Daikin R32 inverter', 'Ruang Server', 'tersedia', '2022-04-18', 4200000],
            ['AST-0010', 'Proyektor', 5, 'Epson EB-X51 2700 lumen', 'Ruang Rapat', 'rusak', '2020-10-05', 6500000],
        ];
        $stmt = $db->prepare("INSERT INTO assets (asset_code, name, category_id, brand_spec, location, status, purchase_date, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($assets as $a) {
            $stmt->execute($a);
        }

        // Logs
        $logs = [
            [3, 2, 'dipinjam', 'Dipinjam oleh tim marketing untuk presentasi klien'],
            [4, 2, 'dipinjam', 'Dipinjam oleh direksi untuk perjalanan dinas'],
            [6, 1, 'rusak', 'Kerusakan pada modul head printer, menunggu penggantian'],
            [10, 1, 'rusak', 'Lampu proyektor mati, perlu penggantian'],
            [7, 1, 'perawatan', 'Maintenance switch core bulanan'],
            [3, 1, 'status_update', 'Status diperbarui melalui dashboard'],
        ];
        $stmt = $db->prepare("INSERT INTO asset_logs (asset_id, user_id, action, note) VALUES (?, ?, ?, ?)");
        foreach ($logs as $l) {
            $stmt->execute($l);
        }
    }

    // --- Query helpers ---
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function scalar(string $sql, array $params = [])
    {
        $row = self::query($sql, $params)->fetch(PDO::FETCH_NUM);
        return $row[0] ?? null;
    }

    public static function lastInsertId(): string
    {
        return self::conn()->lastInsertId();
    }
}
