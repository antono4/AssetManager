<?php
// ============================================================================
//  MODEL: Borrowing — peminjaman aset dengan detail
// ============================================================================

class Borrowing
{
    // Status peminjaman: borrowed (aktif), returned (selesai).
    // "overdue" bukan status tersimpan — turunan dari borrowed + expected_return < now.

    public static function all(int $limit = 0, int $offset = 0, string $status = '', string $search = ''): array
    {
        $sql = "SELECT b.*, a.asset_code, a.name AS asset_name, u.name AS user_name
                FROM borrowings b
                LEFT JOIN assets a ON a.id = b.asset_id
                LEFT JOIN users u ON u.id = b.user_id
                WHERE 1=1";
        $params = [];
        [$sql, $params] = self::applyFilters($sql, $params, $status, $search);
        $sql .= " ORDER BY b.borrow_date DESC";
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        return Database::fetchAll($sql, $params);
    }

    public static function count(string $status = '', string $search = ''): int
    {
        $sql = "SELECT COUNT(*) FROM borrowings b
                LEFT JOIN assets a ON a.id = b.asset_id
                LEFT JOIN users u ON u.id = b.user_id
                WHERE 1=1";
        [$sql, $params] = self::applyFilters($sql, [], $status, $search);
        return (int)Database::scalar($sql, $params);
    }

    // Tambah clause WHERE sesuai status & search ke SQL + params.
    private static function applyFilters(string $sql, array $params, string $status, string $search): array
    {
        switch ($status) {
            case 'active':
                $sql .= " AND b.status = 'borrowed'";
                break;
            case 'returned':
                $sql .= " AND b.status = 'returned'";
                break;
            case 'overdue':
                $sql .= " AND b.status = 'borrowed' AND b.expected_return IS NOT NULL AND b.expected_return < ?";
                $params[] = date('Y-m-d H:i:s');
                break;
        }
        if ($search !== '') {
            $kw = "%$search%";
            $sql .= " AND (a.asset_code LIKE ? OR a.name LIKE ? OR b.borrower_name LIKE ? OR u.name LIKE ?)";
            array_push($params, $kw, $kw, $kw, $kw);
        }
        return [$sql, $params];
    }

    // Statistik ringkasan untuk kartu di halaman peminjaman.
    public static function stats(): array
    {
        $now = date('Y-m-d H:i:s');
        return [
            'total'     => (int)Database::scalar("SELECT COUNT(*) FROM borrowings"),
            'active'    => (int)Database::scalar("SELECT COUNT(*) FROM borrowings WHERE status='borrowed'"),
            'returned'  => (int)Database::scalar("SELECT COUNT(*) FROM borrowings WHERE status='returned'"),
            'overdue'   => (int)Database::scalar("SELECT COUNT(*) FROM borrowings WHERE status='borrowed' AND expected_return IS NOT NULL AND expected_return < ?", [$now]),
        ];
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
