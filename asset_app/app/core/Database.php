<?php
// ============================================================================
//  DATABASE - PDO adapter (MySQL)
// ============================================================================

class Database
{
    private static ?PDO $instance = null;

    public static function conn(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException('Koneksi database gagal: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function driver(): string
    {
        return 'mysql';
    }

    // Inisialisasi skema bila tabel belum ada
    public static function ensureSchema(): void
    {
        $db = self::conn();
        $check = $db->query("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'");
        if (!$check || !$check->fetch()) {
            // assets_app.sql sudah berisi data seed (kategori, users, aset, log, patch_items),
            // jadi tidak perlu memanggil seed() lagi.
            $sql = file_get_contents(dirname(dirname(__DIR__)) . '/database/assets_app.sql');
            $db->exec($sql);
        }
        // Migrasi tabel fitur baru (patching) — idempoten
        self::migratePatching($db);
    }

    // Migrasi tabel patching (jadwal & checklist)
    private static function migratePatching(PDO $db): void
    {
        $idCol    = 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)';
        $statusCol = function (array $vals, string $default) {
            return "ENUM('" . implode("','", $vals) . "') NOT NULL DEFAULT '" . $default . "'";
        };
        $dt   = 'DATETIME';
        $dcol = 'DATE';
        $tiny = 'TINYINT(1)';
        $tail = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

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
        ){$tail}");

        // Index & UNIQUE constraint via CREATE INDEX terpisah (MySQL tak ada
        // CREATE INDEX IF NOT EXISTS; cek information_schema.statistics).
        $createIndexIfMissing = function (string $name, string $def) use ($db) {
            $table = trim(substr($def, strpos($def, ' ON ') + 4));
            $table = substr($table, 0, strpos($table, '('));
            $exists = $db->prepare("SELECT 1 FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
            $exists->execute([$table, $name]);
            if (!$exists->fetch()) {
                try { $db->exec($def); } catch (Throwable $e) { /* index mungkin sudah ada */ }
            }
        };
        $createIndexIfMissing('idx_patch_sched_quarter', 'CREATE INDEX idx_patch_sched_quarter ON patch_schedules(year, quarter)');
        $createIndexIfMissing('idx_patch_sched_status', 'CREATE INDEX idx_patch_sched_status ON patch_schedules(status)');
        $createIndexIfMissing('idx_checklist_schedule', 'CREATE INDEX idx_checklist_schedule ON patch_checklists(schedule_id)');
        $createIndexIfMissing('idx_checklist_asset', 'CREATE INDEX idx_checklist_asset ON patch_checklists(asset_id)');
        $createIndexIfMissing('idx_checklist_status', 'CREATE INDEX idx_checklist_status ON patch_checklists(status)');
        $createIndexIfMissing('idx_pcli_checklist', 'CREATE INDEX idx_pcli_checklist ON patch_checklist_items(checklist_id)');
        $createIndexIfMissing('uq_checklist', 'CREATE UNIQUE INDEX uq_checklist ON patch_checklists(schedule_id, asset_id)');
        $createIndexIfMissing('uq_checklist_item', 'CREATE UNIQUE INDEX uq_checklist_item ON patch_checklist_items(checklist_id, item_id)');

        // Migrasi kolom patch_code pada patch_checklist_items (kode patching, mis: KB5079473)
        $hasCol = $db->query("SHOW COLUMNS FROM patch_checklist_items LIKE 'patch_code'")->fetch();
        if (!$hasCol) {
            $db->exec("ALTER TABLE patch_checklist_items ADD COLUMN patch_code VARCHAR(80) DEFAULT NULL AFTER notes");
        }
        // Kolom photo pada assets (path foto aset)
        $hasPhoto = $db->query("SHOW COLUMNS FROM assets LIKE 'photo'")->fetch();
        if (!$hasPhoto) {
            $db->exec("ALTER TABLE assets ADD COLUMN photo VARCHAR(255) DEFAULT NULL AFTER location");
        }

        // Pastikan folder uploads ada
        $uploadDir = PUBLIC_PATH . '/uploads/assets';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        // Migrasi kolom tambahan & tabel fitur baru
        self::migrateExtended($db);

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

    // Migrasi tabel & kolom fitur baru (soft delete, audit, API token, borrow, notif, depreciation)
    private static function migrateExtended(PDO $db): void
    {
        $int = 'INT UNSIGNED';
        $dt = 'DATETIME';
        $tail = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $tiny = 'TINYINT(1)';
        $idCol = 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)';

        // Kolom deleted_at pada assets (soft delete)
        if (!$db->query("SHOW COLUMNS FROM assets LIKE 'deleted_at'")->fetch()) {
            $db->exec("ALTER TABLE assets ADD COLUMN deleted_at DATETIME DEFAULT NULL");
        }

        // Tabel audit_trail
        $db->exec("CREATE TABLE IF NOT EXISTS audit_trail (
            {$idCol},
            module VARCHAR(50) NOT NULL,
            action VARCHAR(40) NOT NULL,
            target_id {$int} DEFAULT NULL,
            description VARCHAR(255) DEFAULT NULL,
            user_id {$int} DEFAULT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            created_at {$dt} DEFAULT CURRENT_TIMESTAMP
        ){$tail}");

        // Tabel api_tokens
        $db->exec("CREATE TABLE IF NOT EXISTS api_tokens (
            {$idCol},
            user_id {$int} NOT NULL,
            token VARCHAR(80) NOT NULL,
            name VARCHAR(80) DEFAULT NULL,
            last_used_at {$dt} DEFAULT NULL,
            created_at {$dt} DEFAULT CURRENT_TIMESTAMP
        ){$tail}");

        // Tabel borrowings (peminjaman aset)
        $db->exec("CREATE TABLE IF NOT EXISTS borrowings (
            {$idCol},
            asset_id {$int} NOT NULL,
            borrower_name VARCHAR(100) DEFAULT NULL,
            user_id {$int} DEFAULT NULL,
            borrow_date {$dt} DEFAULT CURRENT_TIMESTAMP,
            expected_return {$dt} DEFAULT NULL,
            actual_return {$dt} DEFAULT NULL,
            note VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'borrowed'
        ){$tail}");

        // Tabel notifications
        $db->exec("CREATE TABLE IF NOT EXISTS notifications (
            {$idCol},
            user_id {$int} DEFAULT NULL,
            type VARCHAR(40) NOT NULL,
            title VARCHAR(120) NOT NULL,
            body TEXT DEFAULT NULL,
            link VARCHAR(255) DEFAULT NULL,
            is_read {$tiny} NOT NULL DEFAULT 0,
            created_at {$dt} DEFAULT CURRENT_TIMESTAMP
        ){$tail}");

        // Tabel settings (key-value) — menyimpan konfigurasi aplikasi seperti
        // nama perusahaan, alamat, telepon, dll. Dapat diubah oleh admin.
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            {$idCol},
            setting_key VARCHAR(60) NOT NULL,
            setting_value TEXT,
            updated_at {$dt} DEFAULT CURRENT_TIMESTAMP
        ){$tail}");

        // Kolom currency & useful_life pada assets
        if (!$db->query("SHOW COLUMNS FROM assets LIKE 'currency'")->fetch()) {
            $db->exec("ALTER TABLE assets ADD COLUMN currency VARCHAR(3) DEFAULT 'IDR'");
        }
        if (!$db->query("SHOW COLUMNS FROM assets LIKE 'useful_life'")->fetch()) {
            $db->exec("ALTER TABLE assets ADD COLUMN useful_life INT DEFAULT 5");
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
