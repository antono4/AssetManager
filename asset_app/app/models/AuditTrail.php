<?php
// ============================================================================
//  MODEL: AuditTrail — log perubahan semua modul (user, kategori, jadwal)
// ============================================================================

class AuditTrail
{
    public static function log(string $module, string $action, ?int $targetId = null, ?string $description = null): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        Database::query(
            "INSERT INTO audit_trail (module, action, target_id, description, user_id, ip) VALUES (?, ?, ?, ?, ?, ?)",
            [$module, $action, $targetId, $description, Auth::id(), $ip]
        );
    }

    public static function all(int $limit = 50, int $offset = 0, string $module = ''): array
    {
        $sql = "SELECT a.*, u.name AS user_name, u.username AS user_username
                FROM audit_trail a LEFT JOIN users u ON u.id = a.user_id";
        $params = [];
        if ($module !== '') {
            $sql .= " WHERE a.module = ?";
            $params[] = $module;
        }
        $sql .= " ORDER BY a.created_at DESC, a.id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return Database::fetchAll($sql, $params);
    }

    public static function count(string $module = ''): int
    {
        $sql = "SELECT COUNT(*) FROM audit_trail";
        $params = [];
        if ($module !== '') {
            $sql .= " WHERE module = ?";
            $params[] = $module;
        }
        return (int)Database::scalar($sql, $params);
    }

    public static function byUser(int $userId, int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT a.*, u.name AS user_name FROM audit_trail a LEFT JOIN users u ON u.id = a.user_id
             WHERE a.user_id = ? ORDER BY a.created_at DESC LIMIT " . (int)$limit,
            [$userId]
        );
    }

    public static function modules(): array
    {
        return Database::fetchAll("SELECT DISTINCT module FROM audit_trail ORDER BY module");
    }
}
