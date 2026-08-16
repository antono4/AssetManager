-- ============================================================================
--  APLIKASI MANAJEMEN ASET (IT & UMUM)
--  Database : asset_db
--  Engine   : MySQL 5.7+ / MariaDB 10+
-- ----------------------------------------------------------------------------
--  Cara pakai:
--    mysql -u root -p -e "CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--    mysql -u root -p asset_db < assets_app.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;


-- ----------------------------------------------------------------------------
--  Tabel: categories  (Kategori aset)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(80)  NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
--  Tabel: users  (RBAC: role = admin | staff)
--  Password disimpan sebagai hash password_hash(PASSWORD_BCRYPT).
--  Default admin : username=admin   password=admin123
--  Default staff : username=staff   password=staff123
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `username`   VARCHAR(50)  NOT NULL,
  `email`      VARCHAR(120) DEFAULT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_username` (`username`),
  KEY `idx_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
--  Tabel: assets
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_code`     VARCHAR(30)  NOT NULL,
  `name`           VARCHAR(120) NOT NULL,
  `category_id`    INT UNSIGNED NOT NULL,
  `brand_spec`     VARCHAR(180) DEFAULT NULL,
  `location`       VARCHAR(120) DEFAULT NULL,
  `status`         ENUM('tersedia','dipinjam','rusak') NOT NULL DEFAULT 'tersedia',
  `purchase_date`  DATE         DEFAULT NULL,
  `price`          DECIMAL(14,2) DEFAULT 0.00,
  `created_at`     DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asset_code` (`asset_code`),
  KEY `idx_asset_category` (`category_id`),
  KEY `idx_asset_status`   (`status`),
  CONSTRAINT `fk_asset_category` FOREIGN KEY (`category_id`)
      REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
--  Tabel: asset_logs  (riwayat pemakaian / perawatan / perubahan status)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `asset_logs`;
CREATE TABLE `asset_logs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`   INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `action`     VARCHAR(40)  NOT NULL,
  `note`       TEXT         DEFAULT NULL,
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_asset` (`asset_id`),
  KEY `idx_log_user`  (`user_id`),
  CONSTRAINT `fk_log_asset` FOREIGN KEY (`asset_id`)
      REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_user`  FOREIGN KEY (`user_id`)
      REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================================
--  DATA DUMMY
-- ============================================================================


-- Kategori
INSERT INTO `categories` (`name`,`description`) VALUES
('Komputer',  'PC desktop dan workstation'),
('Laptop',    'Laptop dan notebook'),
('Printer',   'Printer dan scanner'),
('Jaringan',  'Switch, router, access point'),
('Umum',      'Aset non-IT lainnya');


-- Users
-- Hash bcrypt di bawah adalah PLACEHOLDER. Untuk mengisi hash yang valid,
-- akses route /setup di aplikasi (http://localhost:8080/setup) atau jalankan
-- dari PHP:
--   php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
-- lalu update kolom password.
INSERT INTO `users` (`name`,`username`,`email`,`password`,`role`) VALUES
('Administrator', 'admin', 'admin@asset.app',
 '$2y$10$e0nPp9mZg3wQvLxK1o2Jp.dummyhashadmin123abcdefgHIJKLmnOPQRSTUVwx',
 'admin'),
('Staff Satu',    'staff', 'staff@asset.app',
 '$2y$10$e0nPp9mZg3wQvLxK1o2Jp.dummyhashstaff123abcdefgHIJKLmnOPQRSTUVwx',
 'staff');


-- Assets
INSERT INTO `assets`
(`asset_code`,`name`,`category_id`,`brand_spec`,`location`,`status`,`purchase_date`,`price`) VALUES
('AST-0001','PC Desktop Dev 01',1,'Dell OptiPlex 7090 / i7-11700 / 16GB / SSD 512GB','Ruang Server','tersedia','2023-02-10', 12500000),
('AST-0002','PC Desktop Dev 02',1,'HP EliteDesk 800 G6 / i5-10500 / 8GB / SSD 256GB','Ruang Developer','tersedia','2023-03-15',  9800000),
('AST-0003','Laptop Marketing',2,'Lenovo ThinkPad E14 / Ryzen 5 / 8GB / SSD 512GB','Ruang Marketing','dipinjam','2023-05-20', 11000000),
('AST-0004','Laptop Direksi',  2,'MacBook Air M2 / 8GB / SSD 256GB','Ruang Direksi','dipinjam','2023-06-01', 18000000),
('AST-0005','Printer Laser HR', 3,'Brother HL-L2375DW','Ruang HRD','tersedia','2022-11-12',  2500000),
('AST-0006','Printer Inkjet',   3,'Epson EcoTank L3210','Ruang Operasional','rusak','2021-09-08',  2300000),
('AST-0007','Switch Core',      4,'Cisco Catalyst 2960 24-Port','Ruang Server','tersedia','2022-07-30', 15000000),
('AST-0008','Access Point',     4,'TP-Link EAP670 AX3000','Lobi Utama','tersedia','2023-08-22',  1800000),
('AST-0009','AC Split 1 PK',    5,'Daikin R32 inverter','Ruang Server','tersedia','2022-04-18',  4200000),
('AST-0010','Proyektor',        5,'Epson EB-X51 2700 lumen','Ruang Rapat','rusak','2020-10-05',  6500000);


-- Asset logs
INSERT INTO `asset_logs` (`asset_id`,`user_id`,`action`,`note`) VALUES
(3,2,'dipinjam','Dipinjam oleh tim marketing untuk presentasi klien'),
(4,2,'dipinjam','Dipinjam oleh direksi untuk perjalanan dinas'),
(6,1,'rusak','Kerusakan pada modul head printer, menunggu penggantian'),
(10,1,'rusak','Lampu proyektor mati, perlu penggantian'),
(7,1,'perawatan','Maintenance switch core bulanan'),
(3,1,'status_update','Status diperbarui melalui dashboard');


-- ============================================================================
--  TABEL PATCHING (Jadwal & Checklist Patching Kuartalan / per 3 bulan)
--  Dibuat otomatis oleh aplikasi (migratePatching). Skema MySQL manual:
-- ============================================================================

CREATE TABLE IF NOT EXISTS `patch_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `patch_schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `quarter` INT UNSIGNED NOT NULL,
  `year` INT UNSIGNED NOT NULL,
  `start_date` DATE DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `status` ENUM('draft','ongoing','completed') NOT NULL DEFAULT 'draft',
  `description` VARCHAR(255) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patch_sched_quarter` (`year`, `quarter`),
  KEY `idx_patch_sched_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `patch_checklists` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_id` INT UNSIGNED NOT NULL,
  `asset_id` INT UNSIGNED NOT NULL,
  `status` ENUM('pending','in_progress','completed','skipped') NOT NULL DEFAULT 'pending',
  `patched_by` INT UNSIGNED DEFAULT NULL,
  `patched_at` DATETIME DEFAULT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_checklist` (`schedule_id`, `asset_id`),
  KEY `idx_checklist_schedule` (`schedule_id`),
  KEY `idx_checklist_asset` (`asset_id`),
  KEY `idx_checklist_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `patch_checklist_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NOT NULL,
  `is_checked` TINYINT(1) NOT NULL DEFAULT 0,
  `checked_by` INT UNSIGNED DEFAULT NULL,
  `checked_at` DATETIME DEFAULT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_checklist_item` (`checklist_id`, `item_id`),
  KEY `idx_pcli_checklist` (`checklist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Template item checklist patching default
INSERT INTO `patch_items` (`name`,`description`,`sort_order`) VALUES
('Update Sistem Operasi / Firmware', 'Patch OS terbaru atau firmware perangkat', 1),
('Update Antivirus / Security', 'Update definisi virus & security patch', 2),
('Backup Data', 'Backup konfigurasi & data penting', 3),
('Cek Log Sistem', 'Tinjau log sistem untuk error/anomali', 4),
('Restart Layanan', 'Restart service/daemon kritis', 5),
('Verifikasi Konektivitas', 'Tes koneksi jaringan & fungsi perangkat', 6);
