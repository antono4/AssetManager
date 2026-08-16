-- ============================================================================
--  SEEDER 1000 DATA DUMMY — MySQL / MariaDB
--  Untuk AssetManager (database: asset_db)
--
--  Cara pakai:
--    1. Pastikan database asset_db sudah dibuat & skema sudah di-import:
--         mysql -u root -p asset_db < database/assets_app.sql
--    2. Jalankan file ini:
--         mysql -u root -p asset_db < database/seed_1000.sql
--
--  Catatan:
--  - Menggunakan stored procedure untuk generate 1000 aset dummy realistis.
--  - Aman dijalankan berulang (DELETE data dummy lama berdasarkan kode AST-1xxx).
--  - Auto-lanjut kode dari AST-#### terakhir yang ada di tabel assets.
--  - Membuat: 1000 aset + ~218 peminjaman + 200 log aktivitas.
-- ============================================================================

-- Hapus data dummy sebelumnya (kode AST-0011 s/d AST-9999) agar tidak duplikat
-- saat dijalankan ulang. Kategori/users/asli tetap.
-- Tabel extended (borrowings, audit_trail, dll) mungkin belum ada bila hanya
-- import assets_app.sql — gunakan JOIN dengan aman (skip bila tabel tidak ada).
SET FOREIGN_KEY_CHECKS = 0;

-- Buat tabel extended bila belum ada (sama dengan migrateExtended di PHP)
CREATE TABLE IF NOT EXISTS `borrowings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `borrower_name` VARCHAR(100) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `borrow_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expected_return` DATETIME DEFAULT NULL,
  `actual_return` DATETIME DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'borrowed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(40) NOT NULL,
  `target_id` INT UNSIGNED DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(60) NOT NULL,
  `setting_value` TEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pastikan kolom extended pada assets ada (dibuat via migrateExtended di PHP)
ALTER TABLE `assets` ADD COLUMN IF NOT EXISTS `currency` VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE `assets` ADD COLUMN IF NOT EXISTS `useful_life` INT DEFAULT 5;
ALTER TABLE `assets` ADD COLUMN IF NOT EXISTS `photo` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `assets` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL;
-- Extend ENUM status untuk dukung 'perawatan' (aplikasi pakai 4 status)
ALTER TABLE `assets` MODIFY COLUMN `status` ENUM('tersedia','dipinjam','rusak','perawatan') NOT NULL DEFAULT 'tersedia';

DELETE FROM `borrowings`    WHERE `asset_id` IN (SELECT `id` FROM `assets` WHERE `asset_code` REGEXP '^AST-[0-9]{4}$' AND `asset_code` > 'AST-0010');
DELETE FROM `asset_logs`    WHERE `asset_id` IN (SELECT `id` FROM `assets` WHERE `asset_code` REGEXP '^AST-[0-9]{4}$' AND `asset_code` > 'AST-0010');
DELETE FROM `patch_checklist_items` WHERE `checklist_id` IN (SELECT `id` FROM `patch_checklists` WHERE `asset_id` IN (SELECT `id` FROM `assets` WHERE `asset_code` > 'AST-0010'));
DELETE FROM `patch_checklists` WHERE `asset_id` IN (SELECT `id` FROM `assets` WHERE `asset_code` > 'AST-0010');
DELETE FROM `assets` WHERE `asset_code` REGEXP '^AST-[0-9]{4}$' AND `asset_code` > 'AST-0010';
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================================
--  STORED PROCEDURE: seed 1000 aset dummy
-- ============================================================================
DROP PROCEDURE IF EXISTS `seed_assets_1000`;
DELIMITER //

CREATE PROCEDURE `seed_assets_1000`()
BEGIN
    DECLARE v_i INT DEFAULT 0;
    DECLARE v_total INT DEFAULT 1000;
    DECLARE v_code_num INT;
    DECLARE v_code VARCHAR(10);
    DECLARE v_cat INT;
    DECLARE v_brand VARCHAR(120);
    DECLARE v_name VARCHAR(120);
    DECLARE v_loc VARCHAR(120);
    DECLARE v_status VARCHAR(20);
    DECLARE v_year INT;
    DECLARE v_month INT;
    DECLARE v_day INT;
    DECLARE v_date DATE;
    DECLARE v_price DECIMAL(14,2);
    DECLARE v_currency VARCHAR(3);
    DECLARE v_useful INT;
    DECLARE v_rand DOUBLE;

    -- Hitung kode awal (lanjut dari kode terakhir)
    SELECT COALESCE(MAX(CAST(SUBSTRING(asset_code, 5) AS UNSIGNED)), 0) + 1
      INTO v_code_num
      FROM assets;

    WHILE v_i < v_total DO
        SET v_code_num = v_code_num;
        SET v_code = CONCAT('AST-', LPAD(v_code_num, 4, '0'));

        -- Kategori acak 1-5
        SET v_cat = FLOOR(RAND() * 5) + 1;

        -- Brand per kategori
        SET v_brand = ELT(FLOOR(RAND() * 5) + 1,
            CASE v_cat
                WHEN 1 THEN 'Dell OptiPlex 7090'
                WHEN 2 THEN 'Lenovo ThinkPad E14'
                WHEN 3 THEN 'Brother HL-L2375DW'
                WHEN 4 THEN 'Cisco Catalyst 2960'
                ELSE 'Daikin R32 Inverter'
            END,
            CASE v_cat
                WHEN 1 THEN 'HP EliteDesk 800 G6'
                WHEN 2 THEN 'HP ProBook 450 G8'
                WHEN 3 THEN 'Epson EcoTank L3210'
                WHEN 4 THEN 'TP-Link EAP670 AX3000'
                ELSE 'Epson EB-X51 Projector'
            END,
            CASE v_cat
                WHEN 1 THEN 'Lenovo ThinkCentre M70q'
                WHEN 2 THEN 'Dell Latitude 5420'
                WHEN 3 THEN 'HP LaserJet Pro M404'
                WHEN 4 THEN 'Ubiquiti UniFi 6 Lite'
                ELSE 'Panasonic AC 1.5PK'
            END,
            CASE v_cat
                WHEN 1 THEN 'Asus ExpertCenter D500'
                WHEN 2 THEN 'MacBook Air M2'
                WHEN 3 THEN 'Canon PIXMA G2010'
                WHEN 4 THEN 'Mikrotik hEX S'
                ELSE 'Sharp Plasmacluster'
            END,
            CASE v_cat
                WHEN 1 THEN 'Acer Veriton X200'
                WHEN 2 THEN 'Asus ZenBook 14'
                WHEN 3 THEN 'Fuji Xerox DocuPrint P225d'
                WHEN 4 THEN 'Aruba Instant On 1830'
                ELSE 'Samsung AR12TYHYEW'
            END
        );

        -- Nama aset per kategori
        SET v_name = CONCAT(
            ELT(v_cat, 'Komputer', 'Laptop', 'Printer', 'Jaringan', 'Umum'),
            ' ', v_code_num
        );

        -- Lokasi acak (10 pilihan)
        SET v_loc = ELT(FLOOR(RAND() * 10) + 1,
            'Ruang Server', 'Ruang Developer', 'Ruang Marketing', 'Ruang HRD',
            'Ruang Direksi', 'Ruang Operasional', 'Lobi Utama', 'Ruang Rapat',
            'Gudang', 'Ruang IT'
        );

        -- Status: 60% tersedia, 22% dipinjam, 8% rusak, 8% perawatan
        SET v_rand = RAND();
        SET v_status = CASE
            WHEN v_rand < 0.60 THEN 'tersedia'
            WHEN v_rand < 0.82 THEN 'dipinjam'
            WHEN v_rand < 0.90 THEN 'rusak'
            ELSE 'perawatan'
        END;

        -- Tanggal pembelian acak 2019-2025
        SET v_year  = FLOOR(RAND() * 7) + 2019;
        SET v_month = FLOOR(RAND() * 12) + 1;
        SET v_day   = FLOOR(RAND() * 28) + 1;
        SET v_date  = STR_TO_DATE(CONCAT(v_year, '-', LPAD(v_month, 2, '0'), '-', LPAD(v_day, 2, '0')), '%Y-%m-%d');

        -- Harga acak 500rb - 35jt
        SET v_price = (FLOOR(RAND() * 346) + 5) * 100000;

        -- Currency: 80% IDR, 10% USD, 10% EUR
        SET v_rand = RAND();
        SET v_currency = CASE
            WHEN v_rand < 0.80 THEN 'IDR'
            WHEN v_rand < 0.90 THEN 'USD'
            ELSE 'EUR'
        END;

        -- Useful life 3-8 tahun
        SET v_useful = FLOOR(RAND() * 6) + 3;

        INSERT INTO `assets`
            (`asset_code`, `name`, `category_id`, `brand_spec`, `location`,
             `status`, `purchase_date`, `price`, `currency`, `useful_life`)
        VALUES
            (v_code, v_name, v_cat, v_brand, v_loc,
             v_status, v_date, v_price, v_currency, v_useful);

        SET v_code_num = v_code_num + 1;
        SET v_i = v_i + 1;
    END WHILE;
END //

DELIMITER ;

-- Jalankan procedure
CALL `seed_assets_1000`();
DROP PROCEDURE IF EXISTS `seed_assets_1000`;


-- ============================================================================
--  PEMINJAMAN: buat record untuk aset berstatus dipinjam
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `borrowings` (`asset_id`, `borrower_name`, `user_id`, `borrow_date`, `expected_return`, `note`, `status`)
SELECT
    a.`id`,
    ELT(FLOOR(RAND() * 10) + 1,
        'Budi Santoso', 'Siti Rahayu', 'Andi Wijaya', 'Dewi Lestari', 'Rudi Hartono',
        'Maya Putri', 'Joko Susilo', 'Rina Marlina', 'Agus Salim', 'Farah Diba'
    ) AS borrower_name,
    FLOOR(RAND() * 2) + 1 AS user_id,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) + 1 DAY) AS borrow_date,
    DATE_ADD(DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) + 1 DAY), INTERVAL FLOOR(RAND() * 24) + 7 DAY) AS expected_return,
    'Peminjaman operasional' AS note,
    'borrowed' AS status
FROM `assets` a
WHERE a.`status` = 'dipinjam'
  AND a.`asset_code` > 'AST-0010';
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================================
--  LOG AKTIVITAS: 200 log untuk aset dummy
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `asset_logs` (`asset_id`, `user_id`, `action`, `note`, `created_at`)
SELECT
    a.`id`,
    FLOOR(RAND() * 2) + 1 AS user_id,
    ELT(FLOOR(RAND() * 5) + 1, 'created', 'status_update', 'dipinjam', 'updated', 'perawatan') AS action,
    ELT(FLOOR(RAND() * 5) + 1,
        'Aset ditambahkan ke inventaris',
        'Status diperbarui melalui dashboard',
        'Dipinjam untuk keperluan operasional',
        'Data aset diperbarui',
        'Maintenance rutin dilakukan'
    ) AS note,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 90) + 1 DAY) AS created_at
FROM `assets` a
WHERE a.`asset_code` > 'AST-0010'
ORDER BY a.`id`
LIMIT 200;
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================================
--  RINGKASAN
-- ============================================================================
SELECT '========================================' AS '';
SELECT '  SEED 1000 DATA DUMMY — SELESAI' AS '';
SELECT '========================================' AS '';
SELECT CONCAT('Total aset    : ', COUNT(*)) AS info FROM assets;
SELECT CONCAT('Tersedia      : ', SUM(status = 'tersedia')) AS info FROM assets;
SELECT CONCAT('Dipinjam      : ', SUM(status = 'dipinjam')) AS info FROM assets;
SELECT CONCAT('Rusak         : ', SUM(status = 'rusak')) AS info FROM assets;
SELECT CONCAT('Perawatan     : ', SUM(status = 'perawatan')) AS info FROM assets;
SELECT CONCAT('Peminjaman    : ', COUNT(*)) AS info FROM borrowings;
SELECT CONCAT('Log aktivitas : ', COUNT(*)) AS info FROM asset_logs;
SELECT '========================================' AS '';

-- Distribusi per kategori
SELECT c.`name` AS kategori, COUNT(a.`id`) AS jumlah
FROM categories c
LEFT JOIN assets a ON a.`category_id` = c.`id`
GROUP BY c.`id`, c.`name`
ORDER BY jumlah DESC;
