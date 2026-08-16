<?php
// ============================================================================
//  MODEL: PatchChecklist — checklist patching per aset per jadwal
// ============================================================================

class PatchChecklist
{
    public static function activeItems(): array
    {
        return Database::fetchAll(
            "SELECT * FROM patch_items WHERE is_active=1 ORDER BY sort_order, id"
        );
    }

    // Semua checklist untuk schedule (dengan info aset)
    public static function forSchedule(int $scheduleId): array
    {
        return Database::fetchAll(
            "SELECT c.*, a.asset_code, a.name AS asset_name, a.location,
                    a.brand_spec, cat.name AS category_name,
                    u.name AS patched_by_name
             FROM patch_checklists c
             LEFT JOIN assets a ON a.id = c.asset_id
             LEFT JOIN categories cat ON cat.id = a.category_id
             LEFT JOIN users u ON u.id = c.patched_by
             WHERE c.schedule_id = ?
             ORDER BY a.asset_code",
            [$scheduleId]
        );
    }

    // Checklist untuk schedule (paginated) — untuk dataset besar
    public static function forSchedulePaged(int $scheduleId, int $limit, int $offset): array
    {
        return Database::fetchAll(
            "SELECT c.*, a.asset_code, a.name AS asset_name, a.location,
                    a.brand_spec, cat.name AS category_name,
                    u.name AS patched_by_name
             FROM patch_checklists c
             LEFT JOIN assets a ON a.id = c.asset_id
             LEFT JOIN categories cat ON cat.id = a.category_id
             LEFT JOIN users u ON u.id = c.patched_by
             WHERE c.schedule_id = ?
             ORDER BY a.asset_code
             LIMIT " . (int)$limit . " OFFSET " . (int)$offset,
            [$scheduleId]
        );
    }

    // Jumlah checklist untuk schedule
    public static function countForSchedule(int $scheduleId): int
    {
        return (int)Database::scalar(
            "SELECT COUNT(*) FROM patch_checklists WHERE schedule_id = ?",
            [$scheduleId]
        );
    }

    // Hitung progress item (done/total) untuk banyak checklist sekaligus
    // menghindari N+1 query saat render tabel. Returns: [checklist_id => [done,total]]
    public static function progressBatch(array $checklistIds): array
    {
        $result = [];
        if (empty($checklistIds)) {
            return $result;
        }
        $placeholders = implode(',', array_fill(0, count($checklistIds), '?'));
        $rows = Database::fetchAll(
            "SELECT checklist_id,
                    COUNT(*) AS total,
                    SUM(CASE WHEN is_checked = 1 THEN 1 ELSE 0 END) AS done
             FROM patch_checklist_items
             WHERE checklist_id IN ($placeholders)
             GROUP BY checklist_id",
            $checklistIds
        );
        foreach ($rows as $r) {
            $result[(int)$r['checklist_id']] = [
                'done' => (int)$r['done'],
                'total' => (int)$r['total'],
            ];
        }
        return $result;
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT c.*, a.asset_code, a.name AS asset_name, a.location,
                    a.brand_spec, cat.name AS category_name
             FROM patch_checklists c
             LEFT JOIN assets a ON a.id = c.asset_id
             LEFT JOIN categories cat ON cat.id = a.category_id
             WHERE c.id = ?",
            [$id]
        );
    }

    // Generate checklist untuk aset-aset pada schedule
    public static function generateForSchedule(int $scheduleId, array $assetIds): int
    {
        if (empty($assetIds)) {
            return 0;
        }
        $items = self::activeItems();
        $db = Database::conn();

        // Ambil aset yang sudah punya checklist untuk schedule ini (sekali query)
        $placeholders = implode(',', array_fill(0, count($assetIds), '?'));
        $existingRows = Database::fetchAll(
            "SELECT asset_id FROM patch_checklists WHERE schedule_id = ? AND asset_id IN ($placeholders)",
            array_merge([$scheduleId], $assetIds)
        );
        $existing = array_flip(array_column($existingRows, 'asset_id'));

        // Filter aset yang belum punya checklist
        $newIds = [];
        foreach ($assetIds as $aid) {
            $aid = (int)$aid;
            if (!isset($existing[$aid])) {
                $newIds[] = $aid;
            }
        }
        if (empty($newIds)) {
            return 0;
        }

        // Bulk insert dalam satu transaksi — jauh lebih cepat untuk dataset besar.
        $db->beginTransaction();
        try {
            $clStmt = $db->prepare("INSERT INTO patch_checklists (schedule_id, asset_id, status) VALUES (?, ?, 'pending')");
            $itStmt = $db->prepare("INSERT INTO patch_checklist_items (checklist_id, item_id, is_checked) VALUES (?, ?, 0)");
            $created = 0;
            foreach ($newIds as $aid) {
                $clStmt->execute([$scheduleId, $aid]);
                $checklistId = (int)$db->lastInsertId();
                foreach ($items as $it) {
                    $itStmt->execute([$checklistId, $it['id']]);
                }
                $created++;
            }
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
        PatchSchedule::refreshStatus($scheduleId);
        return $created;
    }

    // Ambil item checklist (centangan) untuk sebuah checklist
    public static function items(int $checklistId): array
    {
        return Database::fetchAll(
            "SELECT ci.*, i.name AS item_name, i.description AS item_desc,
                    u.name AS checked_by_name
             FROM patch_checklist_items ci
             LEFT JOIN patch_items i ON i.id = ci.item_id
             LEFT JOIN users u ON u.id = ci.checked_by
             WHERE ci.checklist_id = ?
             ORDER BY i.sort_order, i.id",
            [$checklistId]
        );
    }

    // Toggle / set status centang satu item (dengan optional patch_code)
    public static function toggleItem(int $checklistId, int $itemId, bool $checked, string $patchCode = ''): void
    {
        $checkedAt = $checked ? date('Y-m-d H:i:s') : null;
        $checkedBy = $checked ? Auth::id() : null;
        // upsert
        $exists = Database::fetch(
            "SELECT id, patch_code FROM patch_checklist_items WHERE checklist_id=? AND item_id=?",
            [$checklistId, $itemId]
        );
        if ($exists) {
            // Jika uncheck, jangan hapus patch_code yang sudah ada (biarkan tersimpan)
            Database::query(
                "UPDATE patch_checklist_items SET is_checked=?, checked_by=?, checked_at=?, patch_code=? WHERE checklist_id=? AND item_id=?",
                [(int)$checked, $checkedBy, $checkedAt, $patchCode !== '' ? $patchCode : $exists['patch_code'], $checklistId, $itemId]
            );
        } else {
            Database::query(
                "INSERT INTO patch_checklist_items (checklist_id, item_id, is_checked, checked_by, checked_at, patch_code) VALUES (?, ?, ?, ?, ?, ?)",
                [$checklistId, $itemId, (int)$checked, $checkedBy, $checkedAt, $patchCode ?: null]
            );
        }
        self::refreshChecklistStatus($checklistId);
    }

    // Simpan kode patching untuk satu item (tanpa ubah status centang)
    public static function savePatchCode(int $checklistId, int $itemId, string $patchCode): void
    {
        $exists = Database::fetch(
            "SELECT id FROM patch_checklist_items WHERE checklist_id=? AND item_id=?",
            [$checklistId, $itemId]
        );
        if ($exists) {
            Database::query(
                "UPDATE patch_checklist_items SET patch_code=? WHERE checklist_id=? AND item_id=?",
                [$patchCode !== '' ? $patchCode : null, $checklistId, $itemId]
            );
        } else {
            Database::query(
                "INSERT INTO patch_checklist_items (checklist_id, item_id, is_checked, patch_code) VALUES (?, ?, 0, ?)",
                [$checklistId, $itemId, $patchCode !== '' ? $patchCode : null]
            );
        }
    }

    // Ambil daftar komputer (checklist) beserta kode patching per item untuk sebuah jadwal
    public static function computersWithPatchCodes(int $scheduleId): array
    {
        $checklists = self::forSchedule($scheduleId);
        foreach ($checklists as &$cl) {
            $items = Database::fetchAll(
                "SELECT ci.patch_code, i.name AS item_name, ci.is_checked
                 FROM patch_checklist_items ci
                 LEFT JOIN patch_items i ON i.id = ci.item_id
                 WHERE ci.checklist_id = ?
                 ORDER BY i.sort_order, i.id",
                [$cl['id']]
            );
            $cl['patch_codes'] = $items;
            // Gabungkan semua kode patch yang sudah diisi
            $codes = array_filter(array_map(fn($i) => $i['patch_code'], $items));
            $cl['patch_codes_summary'] = $codes ? implode(', ', $codes) : '';
        }
        return $checklists;
    }

    // Update status checklist berdasarkan item yang tercentang
    public static function refreshChecklistStatus(int $checklistId): void
    {
        $total = (int)Database::scalar("SELECT COUNT(*) FROM patch_checklist_items WHERE checklist_id=?", [$checklistId]);
        $done = (int)Database::scalar("SELECT COUNT(*) FROM patch_checklist_items WHERE checklist_id=? AND is_checked=1", [$checklistId]);
        $status = 'pending';
        if ($total > 0 && $done >= $total) {
            $status = 'completed';
            Database::query(
                "UPDATE patch_checklists SET status='completed', patched_by=?, patched_at=? WHERE id=?",
                [Auth::id(), date('Y-m-d H:i:s'), $checklistId]
            );
            // catat di asset log
            $cl = self::find($checklistId);
            if ($cl) {
                AssetLog::add($cl['asset_id'], Auth::id(), 'patching', 'Patching selesai — jadwal #' . $cl['schedule_id']);
            }
            if ($cl) {
                PatchSchedule::refreshStatus($cl['schedule_id']);
            }
            return;
        }
        if ($done > 0) {
            $status = 'in_progress';
        }
        Database::query(
            "UPDATE patch_checklists SET status=?, patched_by=CASE WHEN ? > 0 THEN ? ELSE patched_by END WHERE id=?",
            [$status, $done, Auth::id(), $checklistId]
        );
        $cl = self::find($checklistId);
        if ($cl) {
            PatchSchedule::refreshStatus($cl['schedule_id']);
        }
    }

    // Set status manual (skip / reset)
    public static function setStatus(int $checklistId, string $status, string $note = ''): void
    {
        $cl = self::find($checklistId);
        if (!$cl) {
            return;
        }
        if ($status === 'skipped') {
            Database::query("UPDATE patch_checklists SET status='skipped', notes=? WHERE id=?", [$note ?: null, $checklistId]);
        } elseif ($status === 'pending') {
            // reset: uncheck semua item
            Database::query("UPDATE patch_checklist_items SET is_checked=0, checked_by=NULL, checked_at=NULL WHERE checklist_id=?", [$checklistId]);
            Database::query("UPDATE patch_checklists SET status='pending', patched_by=NULL, patched_at=NULL, notes=? WHERE id=?", [$note ?: null, $checklistId]);
        }
        PatchSchedule::refreshStatus($cl['schedule_id']);
    }

    // Hapus satu checklist
    public static function delete(int $checklistId): void
    {
        $cl = self::find($checklistId);
        if (!$cl) {
            return;
        }
        Database::query("DELETE FROM patch_checklist_items WHERE checklist_id=?", [$checklistId]);
        Database::query("DELETE FROM patch_checklists WHERE id=?", [$checklistId]);
        PatchSchedule::refreshStatus($cl['schedule_id']);
    }

    // Statistik dashboard
    public static function upcomingDue(int $limitDays = 30): array
    {
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime("+{$limitDays} days"));
        return Database::fetchAll(
            "SELECT s.id, s.name, s.year, s.quarter, s.due_date, s.status,
                    (SELECT COUNT(*) FROM patch_checklists c WHERE c.schedule_id = s.id) AS total,
                    (SELECT COUNT(*) FROM patch_checklists c WHERE c.schedule_id = s.id AND c.status='completed') AS done
             FROM patch_schedules s
             WHERE s.status IN ('draft','ongoing')
             ORDER BY s.due_date ASC"
        );
    }
}
