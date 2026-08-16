-- Alterasi tambahan: tambah kolom yang dipakai html_version tapi tidak ada di
-- assets_app.sql asli (deleted_at, photo, currency untuk assets).

USE assets_app;

ALTER TABLE `assets`
  ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `photo` VARCHAR(255) DEFAULT '',
  ADD COLUMN IF NOT EXISTS `currency` VARCHAR(8) DEFAULT 'IDR';

-- Re-seed patch_checklists (sebelumnya gagal karena kolom deleted_at belum ada)
SET @sched := (SELECT id FROM patch_schedules ORDER BY id DESC LIMIT 1);

INSERT INTO `patch_checklists` (`schedule_id`,`asset_id`,`status`,`patched_by`,`notes`)
SELECT @sched, a.id,
  CASE a.id WHEN 1 THEN 'in_progress' WHEN 3 THEN 'completed' WHEN 7 THEN 'completed' WHEN 8 THEN 'skipped' ELSE 'pending' END,
  CASE a.id WHEN 3 THEN 2 WHEN 7 THEN 1 ELSE NULL END,
  CASE a.id WHEN 8 THEN 'AP non-kritis, skip' WHEN 3 THEN 'Selesai lebih cepat' ELSE '' END
FROM assets a JOIN categories c ON c.id=a.category_id
WHERE c.name <> 'Umum' AND a.deleted_at IS NULL
  AND NOT EXISTS (SELECT 1 FROM patch_checklists pc WHERE pc.schedule_id=@sched AND pc.asset_id=a.id);

-- checklist items
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

UPDATE patch_checklists SET status='in_progress' WHERE schedule_id=@sched AND asset_id=1;
