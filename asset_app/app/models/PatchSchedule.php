<?php
// ============================================================================
//  MODEL: PatchSchedule — jadwal patching kuartalan (per 3 bulan)
// ============================================================================

class PatchSchedule
{
    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM patch_checklists c WHERE c.schedule_id = s.id) AS total_aset,
                    (SELECT COUNT(*) FROM patch_checklists c WHERE c.schedule_id = s.id AND c.status = 'completed') AS done_aset
             FROM patch_schedules s
             ORDER BY s.year DESC, s.quarter DESC"
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch("SELECT * FROM patch_schedules WHERE id=?", [$id]);
    }

    public static function findWithStats(int $id): ?array
    {
        $s = self::find($id);
        if (!$s) {
            return null;
        }
        $s['total_aset'] = (int)Database::scalar(
            "SELECT COUNT(*) FROM patch_checklists WHERE schedule_id=?", [$id]
        );
        $s['done_aset'] = (int)Database::scalar(
            "SELECT COUNT(*) FROM patch_checklists WHERE schedule_id=? AND status='completed'", [$id]
        );
        $s['pending_aset'] = (int)Database::scalar(
            "SELECT COUNT(*) FROM patch_checklists WHERE schedule_id=? AND status='pending'", [$id]
        );
        $s['progress_aset'] = (int)Database::scalar(
            "SELECT COUNT(*) FROM patch_checklists WHERE schedule_id=? AND status='in_progress'", [$id]
        );
        $s['skipped_aset'] = (int)Database::scalar(
            "SELECT COUNT(*) FROM patch_checklists WHERE schedule_id=? AND status='skipped'", [$id]
        );
        return $s;
    }

    public static function create(array $d): int
    {
        Database::query(
            "INSERT INTO patch_schedules (name, quarter, year, start_date, due_date, status, description, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $d['name'], (int)$d['quarter'], (int)$d['year'],
                $d['start_date'] ?: null, $d['due_date'] ?: null,
                $d['status'] ?? 'draft', $d['description'] ?: null, Auth::id()
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        Database::query(
            "UPDATE patch_schedules SET name=?, quarter=?, year=?, start_date=?, due_date=?, status=?, description=? WHERE id=?",
            [
                $d['name'], (int)$d['quarter'], (int)$d['year'],
                $d['start_date'] ?: null, $d['due_date'] ?: null,
                $d['status'], $d['description'] ?: null, $id
            ]
        );
    }

    public static function delete(int $id): void
    {
        // Hapus checklist items dulu, lalu checklists, lalu schedule
        $db = Database::conn();
        $db->prepare("DELETE FROM patch_checklist_items WHERE checklist_id IN (SELECT id FROM patch_checklists WHERE schedule_id=?)")->execute([$id]);
        Database::query("DELETE FROM patch_checklists WHERE schedule_id=?", [$id]);
        Database::query("DELETE FROM patch_schedules WHERE id=?", [$id]);
    }

    // Auto-nama kuartal berdasarkan tahun & quarter
    public static function quarterName(int $quarter, int $year): string
    {
        return "Patching Q{$quarter} {$year}";
    }

    // Rentang tanggal kuartal (Q1=Jan-Mar, dst)
    public static function quarterDates(int $quarter, int $year): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $start = sprintf('%04d-%02d-01', $year, $startMonth);
        $endMonth = $startMonth + 2;
        $end = date('Y-m-t', mktime(0, 0, 0, $endMonth, 1, $year));
        return ['start' => $start, 'end' => $end];
    }

    // Kuartal & tahun saat ini
    public static function currentQuarter(): array
    {
        $month = (int)date('n');
        $quarter = (int)ceil($month / 3);
        $year = (int)date('Y');
        return ['quarter' => $quarter, 'year' => $year];
    }

    // Kuartal saat ini & berikutnya (untuk default form)
    public static function quarterOptions(): array
    {
        $cur = self::currentQuarter();
        $opts = [];
        // Q saat ini + 2 kuartal ke depan
        for ($i = 0; $i <= 2; $i++) {
            $q = $cur['quarter'] + $i;
            $y = $cur['year'];
            if ($q > 4) {
                $q -= 4;
                $y++;
            }
            $opts[] = ['quarter' => $q, 'year' => $y];
        }
        return $opts;
    }

    // Update status schedule otomatis berdasarkan checklist
    public static function refreshStatus(int $id): void
    {
        $s = self::findWithStats($id);
        if (!$s) {
            return;
        }
        $status = $s['status'];
        if ($s['total_aset'] > 0 && $s['done_aset'] + $s['skipped_aset'] >= $s['total_aset']) {
            $status = 'completed';
        } elseif ($s['done_aset'] > 0 || $s['progress_aset'] > 0) {
            if ($status === 'draft') {
                $status = 'ongoing';
            }
        }
        if ($status !== $s['status']) {
            Database::query("UPDATE patch_schedules SET status=? WHERE id=?", [$status, $id]);
        }
    }
}
