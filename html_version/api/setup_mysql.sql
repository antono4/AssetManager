-- Setup tambahan untuk html_version live MySQL: tabel & data yang dibuat
-- oleh migrateExtended()/migratePatching() di app PHP, plus reset password
-- ke plain text (auth API html_version memverifikasi plain, demo only).

USE assets_app;

-- Reset password user ke plain text (html_version auth demo)
UPDATE users SET password='admin123' WHERE username='admin';
UPDATE users SET password='staff123' WHERE username='staff';

-- Tabel borrowings
CREATE TABLE IF NOT EXISTS `borrowings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `borrower_name` VARCHAR(120) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `borrow_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expected_return` DATETIME DEFAULT NULL,
  `actual_return` DATETIME DEFAULT NULL,
  `status` ENUM('borrowed','returned') NOT NULL DEFAULT 'borrowed',
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_borrow_asset` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel audit_trail
CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(40) NOT NULL,
  `action` VARCHAR(40) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT 0,
  `title` VARCHAR(180) NOT NULL,
  `body` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `link` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel api_tokens
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) DEFAULT NULL,
  `token` VARCHAR(255) NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel settings
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed borrowings
INSERT INTO `borrowings` (`asset_id`,`borrower_name`,`user_id`,`borrow_date`,`expected_return`,`actual_return`,`status`,`note`) VALUES
(3,'Tim Marketing',2,'2026-08-01 09:00:00','2026-08-10 17:00:00',NULL,'borrowed','Presentasi klien'),
(4,'Bapak Direktur',1,'2026-08-05 08:00:00','2026-08-12 18:00:00',NULL,'borrowed','Perjalanan dinas'),
(7,'Tim Jaringan',1,'2026-06-01 10:00:00','2026-06-03 16:00:00','2026-06-03 15:30:00','returned','Konfigurasi switch')
ON DUPLICATE KEY UPDATE id=id;

-- Seed audit_trail
INSERT INTO `audit_trail` (`module`,`action`,`description`,`user_id`,`ip`) VALUES
('auth','login','User admin logged in',1,'127.0.0.1'),
('assets','created','Added asset AST-0001',1,'127.0.0.1'),
('patching','toggled','Checklist item toggled for AST-0001',2,'127.0.0.1'),
('borrowings','borrowed','Borrowed AST-0003',2,'127.0.0.1');

-- Seed notifications
INSERT INTO `notifications` (`user_id`,`title`,`body`,`is_read`,`link`) VALUES
(0,'Patching Q3 2026 sedang berjalan','Selesaikan checklist patching sebelum 30 Sep 2026.',0,'#patching/1');

-- Seed api_tokens
INSERT INTO `api_tokens` (`name`,`token`,`user_id`) VALUES
('Default Token','am_demo_token_9f3c7b1e2a8d4560',1);

-- Seed settings
INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('company_name','AssetManager'),
('company_address','Jl. Teknologi No. 1, Jakarta'),
('company_phone','021-1234567'),
('company_email','info@asset.app')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Seed patching (patch_schedules, patch_checklists, patch_checklist_items)
-- Hanya jika patch_schedules kosong
INSERT INTO `patch_schedules` (`name`,`quarter`,`year`,`start_date`,`due_date`,`status`,`description`,`created_by`)
SELECT 'Patching Q3 2026', 3, 2026, '2026-07-01', '2026-09-30', 'ongoing', 'Patch kuartal Q3', 1
WHERE NOT EXISTS (SELECT 1 FROM patch_schedules);

SET @sched := (SELECT id FROM patch_schedules ORDER BY id DESC LIMIT 1);

-- checklists untuk aset IT (exclude kategori 'Umum' = id 5)
INSERT INTO `patch_checklists` (`schedule_id`,`asset_id`,`status`,`patched_by`,`notes`)
SELECT @sched, a.id,
  CASE a.id WHEN 1 THEN 'in_progress' WHEN 3 THEN 'completed' WHEN 7 THEN 'completed' WHEN 8 THEN 'skipped' ELSE 'pending' END,
  CASE a.id WHEN 3 THEN 2 WHEN 7 THEN 1 ELSE NULL END,
  CASE a.id WHEN 8 THEN 'AP non-kritis, skip' WHEN 3 THEN 'Selesai lebih cepat' ELSE '' END
FROM assets a JOIN categories c ON c.id=a.category_id
WHERE c.name <> 'Umum' AND a.deleted_at IS NULL
  AND NOT EXISTS (SELECT 1 FROM patch_checklists pc WHERE pc.schedule_id=@sched AND pc.asset_id=a.id);

-- checklist items: buat 6 item per checklist yang belum punya item
INSERT INTO `patch_checklist_items` (`checklist_id`,`item_id`,`is_checked`,`checked_by`,`checked_at`,`patch_code`)
SELECT pc.id, pi.item_id,
  CASE WHEN pc.asset_id=1 AND pi.sort_order<=4 THEN 1
       WHEN pc.asset_id IN (3,7) THEN 1
       ELSE 0 END,
  CASE WHEN (pc.asset_id=1 AND pi.sort_order<=4) OR pc.asset_id IN (3,7) THEN COALESCE(pc.patched_by,1) ELSE NULL END,
  CASE WHEN (pc.asset_id=1 AND pi.sort_order<=4) OR pc.asset_id IN (3,7) THEN NOW() ELSE NULL END,
  CASE WHEN pi.sort_order=1 AND pc.asset_id IN (1,3) THEN 'KB5079473'
       WHEN pi.sort_order=2 AND pc.asset_id IN (1,3) THEN 'Av-Def-2026.08'
       WHEN pi.sort_order=1 AND pc.asset_id=7 THEN 'IOS-15.2'
       ELSE '' END
FROM patch_checklists pc
JOIN patch_items pi ON pi.is_active=1
WHERE NOT EXISTS (SELECT 1 FROM patch_checklist_items pcli WHERE pcli.checklist_id=pc.id AND pcli.item_id=pi.item_id);

-- Update status checklist AST-0001 ke in_progress (sudah 4/6)
UPDATE patch_checklists SET status='in_progress' WHERE schedule_id=@sched AND asset_id=1;
