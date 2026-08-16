<?php
// ============================================================================
//  MODEL: Asset
// ============================================================================

class Asset
{
    public static function all(string $search = '', string $status = '', string $category = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT a.*, c.name AS category_name
                FROM assets a
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE 1=1";
        $params = [];
        if ($search !== '') {
            $sql .= " AND (a.asset_code LIKE ? OR a.name LIKE ? OR a.brand_spec LIKE ? OR a.location LIKE ?)";
            $kw = "%$search%";
            $params = array_merge($params, [$kw, $kw, $kw, $kw]);
        }
        if ($status !== '') {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        if ($category !== '') {
            $sql .= " AND a.category_id = ?";
            $params[] = $category;
        }
        $sql .= " ORDER BY a.created_at DESC";
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        return Database::fetchAll($sql, $params);
    }

    public static function count(string $search = '', string $status = '', string $category = ''): int
    {
        $sql = "SELECT COUNT(*) FROM assets a WHERE 1=1";
        $params = [];
        if ($search !== '') {
            $sql .= " AND (a.asset_code LIKE ? OR a.name LIKE ? OR a.brand_spec LIKE ? OR a.location LIKE ?)";
            $kw = "%$search%";
            $params = array_merge($params, [$kw, $kw, $kw, $kw]);
        }
        if ($status !== '') {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        if ($category !== '') {
            $sql .= " AND a.category_id = ?";
            $params[] = $category;
        }
        return (int)Database::scalar($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT a.*, c.name AS category_name
             FROM assets a LEFT JOIN categories c ON c.id = a.category_id
             WHERE a.id = ?", [$id]
        );
    }

    public static function create(array $d): int
    {
        $code = self::generateCode();
        Database::query(
            "INSERT INTO assets (asset_code, name, category_id, brand_spec, location, status, purchase_date, price)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$code, $d['name'], (int)$d['category_id'], $d['brand_spec'] ?: null, $d['location'] ?: null,
             $d['status'], $d['purchase_date'] ?: null, (float)$d['price']]
        );
        $id = (int)Database::lastInsertId();
        AssetLog::add($id, Auth::id(), 'created', 'Aset baru ditambahkan: ' . $code);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        $old = self::find($id);
        Database::query(
            "UPDATE assets SET name=?, category_id=?, brand_spec=?, location=?, status=?, purchase_date=?, price=? WHERE id=?",
            [$d['name'], (int)$d['category_id'], $d['brand_spec'] ?: null, $d['location'] ?: null,
             $d['status'], $d['purchase_date'] ?: null, (float)$d['price'], $id]
        );
        $new = self::find($id);
        if ($old && $old['status'] !== $new['status']) {
            AssetLog::add($id, Auth::id(), $new['status'], 'Status berubah dari "' . $old['status'] . '" ke "' . $new['status'] . '"');
        } else {
            AssetLog::add($id, Auth::id(), 'updated', 'Data aset diperbarui');
        }
    }

    public static function delete(int $id): void
    {
        $asset = self::find($id);
        if ($asset) {
            Database::query("DELETE FROM assets WHERE id=?", [$id]);
        }
    }

    public static function setStatus(int $id, string $status, string $note = ''): void
    {
        $asset = self::find($id);
        if (!$asset) {
            return;
        }
        Database::query("UPDATE assets SET status=? WHERE id=?", [$status, $id]);
        AssetLog::add($id, Auth::id(), $status, $note ?: 'Status diubah ke "' . $status . '"');
    }

    public static function generateCode(): string
    {
        $last = Database::fetch("SELECT asset_code FROM assets ORDER BY id DESC LIMIT 1");
        $num = 1;
        if ($last && preg_match('/(\d+)$/', $last['asset_code'], $m)) {
            $num = (int)$m[1] + 1;
        }
        return 'AST-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }

    // --- Statistik ---
    public static function stats(): array
    {
        $total = (int)Database::scalar("SELECT COUNT(*) FROM assets");
        $tersedia = (int)Database::scalar("SELECT COUNT(*) FROM assets WHERE status='tersedia'");
        $dipinjam = (int)Database::scalar("SELECT COUNT(*) FROM assets WHERE status='dipinjam'");
        $rusak = (int)Database::scalar("SELECT COUNT(*) FROM assets WHERE status='rusak'");
        $nilai = (float)Database::scalar("SELECT COALESCE(SUM(price),0) FROM assets");
        return compact('total', 'tersedia', 'dipinjam', 'rusak', 'nilai');
    }

    public static function countByCategory(): array
    {
        return Database::fetchAll(
            "SELECT c.name, COUNT(a.id) AS total, COALESCE(SUM(a.price),0) AS nilai
             FROM categories c LEFT JOIN assets a ON a.category_id = c.id
             GROUP BY c.id, c.name ORDER BY total DESC"
        );
    }

    public static function countByStatus(): array
    {
        return Database::fetchAll(
            "SELECT status, COUNT(*) AS total FROM assets GROUP BY status"
        );
    }

    public static function recent(int $limit = 5): array
    {
        return self::all('', '', '', $limit, 0);
    }
}
