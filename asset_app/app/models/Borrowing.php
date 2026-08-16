<?php
// ============================================================================
//  MODEL: Borrowing — peminjaman aset dengan detail
// ============================================================================

class Borrowing
{
    public static function all(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT b.*, a.asset_code, a.name AS asset_name,
                       u.name AS user_name
                FROM borrowings b
                LEFT JOIN assets a ON a.id = b.asset_id
                LEFT JOIN users u ON u.id = b.user_id
                ORDER BY b.borrow_date DESC";
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        return Database::fetchAll($sql);
    }

    public static function count(): int
    {
        return (int)Database::scalar("SELECT COUNT(*) FROM borrowings");
    }

    public static function forAsset(int $assetId): array
    {
        return Database::fetchAll(
            "SELECT b.*, u.name AS user_name FROM borrowings b
             LEFT JOIN users u ON u.id = b.user_id
             WHERE b.asset_id = ? ORDER BY b.borrow_date DESC",
            [$assetId]
        );
    }

    public static function active(): array
    {
        return Database::fetchAll(
            "SELECT b.*, a.asset_code, a.name AS asset_name, u.name AS user_name
             FROM borrowings b
             LEFT JOIN assets a ON a.id = b.asset_id
             LEFT JOIN users u ON u.id = b.user_id
             WHERE b.status = 'borrowed'
             ORDER BY b.expected_return ASC"
        );
    }

    public static function create(int $assetId, array $d): int
    {
        Database::query(
            "INSERT INTO borrowings (asset_id, borrower_name, user_id, borrow_date, expected_return, note, status)
             VALUES (?, ?, ?, ?, ?, ?, 'borrowed')",
            [$assetId, $d['borrower_name'] ?? null, Auth::id(), date('Y-m-d H:i:s'),
             $d['expected_return'] ?? null, $d['note'] ?? null]
        );
        $id = (int)Database::lastInsertId();
        // set aset status dipinjam
        Asset::setStatus($assetId, 'dipinjam', $d['note'] ?? 'Dipinjam');
        AuditTrail::log('borrowing', 'create', $id, 'Borrowing created for asset #' . $assetId);
        return $id;
    }

    public static function returnAsset(int $borrowId): void
    {
        $b = Database::fetch("SELECT * FROM borrowings WHERE id=?", [$borrowId]);
        if (!$b) return;
        Database::query(
            "UPDATE borrowings SET status='returned', actual_return=? WHERE id=?",
            [date('Y-m-d H:i:s'), $borrowId]
        );
        Asset::setStatus((int)$b['asset_id'], 'tersedia', 'Dikembalikan');
        AuditTrail::log('borrowing', 'return', $borrowId, 'Asset returned: #' . $b['asset_id']);
    }

    public static function overdue(): array
    {
        return Database::fetchAll(
            "SELECT b.*, a.asset_code, a.name AS asset_name, u.name AS user_name
             FROM borrowings b
             LEFT JOIN assets a ON a.id = b.asset_id
             LEFT JOIN users u ON u.id = b.user_id
             WHERE b.status = 'borrowed' AND b.expected_return IS NOT NULL AND b.expected_return < ?
             ORDER BY b.expected_return ASC",
            [date('Y-m-d H:i:s')]
        );
    }
}
