<?php
// ============================================================================
//  MODEL: AssetLog
// ============================================================================

class AssetLog
{
    public static function all(int $limit = 50, int $offset = 0, int $assetId = 0): array
    {
        $sql = "SELECT l.*, a.asset_code, a.name AS asset_name,
                       u.name AS user_name, u.username AS user_username
                FROM asset_logs l
                LEFT JOIN assets a ON a.id = l.asset_id
                LEFT JOIN users u ON u.id = l.user_id";
        $params = [];
        if ($assetId > 0) {
            $sql .= " WHERE l.asset_id = ?";
            $params[] = $assetId;
        }
        $sql .= " ORDER BY l.created_at DESC, l.id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return Database::fetchAll($sql, $params);
    }

    public static function count(int $assetId = 0): int
    {
        $sql = "SELECT COUNT(*) FROM asset_logs";
        $params = [];
        if ($assetId > 0) {
            $sql .= " WHERE asset_id = ?";
            $params[] = $assetId;
        }
        return (int)Database::scalar($sql, $params);
    }

    public static function add(int $assetId, ?int $userId, string $action, string $note = ''): void
    {
        Database::query(
            "INSERT INTO asset_logs (asset_id, user_id, action, note) VALUES (?, ?, ?, ?)",
            [$assetId, $userId, $action, $note]
        );
    }

    public static function recent(int $limit = 8): array
    {
        return self::all($limit, 0, 0);
    }
}
