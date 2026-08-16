<?php
// ============================================================================
//  MODEL: Notification — notifikasi in-app (patching overdue, dll)
// ============================================================================

class Notification
{
    public static function add(?int $userId, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        Database::query(
            "INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, ?, ?, ?, ?)",
            [$userId, $type, $title, $body, $link]
        );
    }

    // Notifikasi untuk semua admin
    public static function addAdmins(string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        $admins = Database::fetchAll("SELECT id FROM users WHERE role='admin' AND is_active=1");
        foreach ($admins as $a) {
            self::add((int)$a['id'], $type, $title, $body, $link);
        }
    }

    public static function forUser(int $userId, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT " . (int)$limit,
            [$userId]
        );
    }

    public static function unreadCount(int $userId): int
    {
        return (int)Database::scalar(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]
        );
    }

    public static function markRead(int $id): void
    {
        Database::query("UPDATE notifications SET is_read=1 WHERE id=?", [$id]);
    }

    public static function markAllRead(int $userId): void
    {
        Database::query("UPDATE notifications SET is_read=1 WHERE user_id=?", [$userId]);
    }

    // Cek patching overdue & due soon, generate notifikasi
    public static function checkPatchingDue(): void
    {
        $today = date('Y-m-d');
        $schedules = Database::fetchAll(
            "SELECT id, name, due_date FROM patch_schedules WHERE status IN ('draft','ongoing') AND due_date IS NOT NULL"
        );
        foreach ($schedules as $s) {
            $due = $s['due_date'];
            $daysLeft = (strtotime($due) - strtotime($today)) / 86400;
            // cek apakah sudah ada notifikasi untuk jadwal ini
            $exists = Database::scalar(
                "SELECT COUNT(*) FROM notifications WHERE type='patching_overdue' AND link LIKE ?",
                ['%/patching/' . $s['id'] . '%']
            );
            if ($daysLeft < 0 && !$exists) {
                self::addAdmins('patching_overdue', t('overdue_patching') . ': ' . $s['name'], 'Jadwal patching sudah lewat batas waktu', url('patching/' . $s['id']));
            }
        }
    }
}
