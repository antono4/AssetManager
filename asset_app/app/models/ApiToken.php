<?php
// ============================================================================
//  MODEL: ApiToken — token untuk REST API access
// ============================================================================

class ApiToken
{
    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT t.*, u.name AS user_name, u.username AS user_username
             FROM api_tokens t LEFT JOIN users u ON u.id = t.user_id
             ORDER BY t.created_at DESC"
        );
    }

    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            "SELECT * FROM api_tokens WHERE user_id=? ORDER BY created_at DESC",
            [$userId]
        );
    }

    public static function create(int $userId, string $name = ''): string
    {
        $token = bin2hex(random_bytes(32)); // 64 char hex
        Database::query(
            "INSERT INTO api_tokens (user_id, token, name) VALUES (?, ?, ?)",
            [$userId, $token, $name ?: 'Token ' . date('Y-m-d')]
        );
        AuditTrail::log('api_token', 'create', null, 'API token created');
        return $token;
    }

    public static function delete(int $id): void
    {
        Database::query("DELETE FROM api_tokens WHERE id=?", [$id]);
        AuditTrail::log('api_token', 'delete', $id, 'API token deleted');
    }

    // Validasi token, return user array or null
    public static function validate(string $token): ?array
    {
        $row = Database::fetch(
            "SELECT t.*, u.name, u.username, u.role, u.email
             FROM api_tokens t JOIN users u ON u.id = t.user_id
             WHERE t.token = ? AND u.is_active = 1",
            [$token]
        );
        if (!$row) return null;
        // update last used
        Database::query("UPDATE api_tokens SET last_used_at=? WHERE id=?", [date('Y-m-d H:i:s'), $row['id']]);
        return $row;
    }
}
