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
                WHERE a.deleted_at IS NULL";
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
        $sql = "SELECT COUNT(*) FROM assets a WHERE a.deleted_at IS NULL";
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
             WHERE a.id = ? AND a.deleted_at IS NULL", [$id]
        );
    }

    // Aset yang sudah di-soft-delete (trash)
    public static function trashed(): array
    {
        return Database::fetchAll(
            "SELECT a.*, c.name AS category_name
             FROM assets a LEFT JOIN categories c ON c.id = a.category_id
             WHERE a.deleted_at IS NOT NULL ORDER BY a.deleted_at DESC"
        );
    }

    // Aset yang sudah di-soft-delete (paginated)
    public static function trashedPaged(int $limit, int $offset): array
    {
        return Database::fetchAll(
            "SELECT a.*, c.name AS category_name
             FROM assets a LEFT JOIN categories c ON c.id = a.category_id
             WHERE a.deleted_at IS NOT NULL ORDER BY a.deleted_at DESC
             LIMIT " . (int)$limit . " OFFSET " . (int)$offset
        );
    }

    public static function trashedCount(): int
    {
        return (int)Database::scalar("SELECT COUNT(*) FROM assets WHERE deleted_at IS NOT NULL");
    }

    // Soft delete: pindahkan ke trash
    public static function softDelete(int $id): void
    {
        Database::query("UPDATE assets SET deleted_at=? WHERE id=?", [date('Y-m-d H:i:s'), $id]);
        AssetLog::add($id, Auth::id(), 'soft_deleted', 'Aset dipindahkan ke sampah');
        AuditTrail::log('asset', 'soft_delete', $id, 'Asset moved to trash: #' . $id);
    }

    // Restore dari trash
    public static function restore(int $id): void
    {
        Database::query("UPDATE assets SET deleted_at=NULL WHERE id=?", [$id]);
        AssetLog::add($id, Auth::id(), 'restored', 'Aset dipulihkan dari sampah');
        AuditTrail::log('asset', 'restore', $id, 'Asset restored: #' . $id);
    }

    // Hapus permanen (dari trash)
    public static function forceDelete(int $id): void
    {
        $asset = Database::fetch("SELECT * FROM assets WHERE id=?", [$id]);
        if ($asset) {
            if (!empty($asset['photo'])) {
                self::deletePhotoFile($asset['photo']);
            }
            Database::query("DELETE FROM assets WHERE id=?", [$id]);
            AuditTrail::log('asset', 'force_delete', $id, 'Asset permanently deleted: #' . $id);
        }
    }

    public static function create(array $d): int
    {
        $code = self::generateCode();
        $photo = self::handleUpload();
        Database::query(
            "INSERT INTO assets (asset_code, name, category_id, brand_spec, location, status, purchase_date, price, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$code, $d['name'], (int)$d['category_id'], $d['brand_spec'] ?: null, $d['location'] ?: null,
             $d['status'], $d['purchase_date'] ?: null, (float)$d['price'], $photo]
        );
        $id = (int)Database::lastInsertId();
        AssetLog::add($id, Auth::id(), 'created', 'Aset baru ditambahkan: ' . $code);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        $old = self::find($id);
        // Handle upload foto baru (bila ada)
        $photo = $old['photo'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            // Hapus foto lama bila ada
            if ($photo) {
                self::deletePhotoFile($photo);
            }
            $photo = self::handleUpload();
        }
        Database::query(
            "UPDATE assets SET name=?, category_id=?, brand_spec=?, location=?, status=?, purchase_date=?, price=?, photo=? WHERE id=?",
            [$d['name'], (int)$d['category_id'], $d['brand_spec'] ?: null, $d['location'] ?: null,
             $d['status'], $d['purchase_date'] ?: null, (float)$d['price'], $photo, $id]
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
        // Soft delete: pindahkan ke trash, jangan hapus permanen
        self::softDelete($id);
    }

    // Export semua aset ke format CSV
    public static function exportCsv(string $search = '', string $status = '', string $category = ''): string
    {
        $assets = self::all($search, $status, $category, 0, 0);
        $out = "asset_code,name,category,brand_spec,location,status,purchase_date,price,currency\n";
        foreach ($assets as $a) {
            $row = [
                $a['asset_code'], $a['name'], $a['category_name'],
                $a['brand_spec'] ?? '', $a['location'] ?? '', $a['status'],
                $a['purchase_date'] ?? '', $a['price'], $a['currency'] ?? 'IDR'
            ];
            $out .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }
        return $out;
    }

    // Hitung nilai penyusutan (depreciation) aset
    public static function depreciation(array $asset): array
    {
        $price = (float)$asset['price'];
        $usefulLife = (int)($asset['useful_life'] ?? 5);
        $purchaseDate = $asset['purchase_date'] ?? date('Y-m-d');
        $salvage = $price * 0.1; // nilai residu 10%
        $depreciable = $price - $salvage;
        $yearsElapsed = (time() - strtotime($purchaseDate)) / (365.25 * 24 * 3600);
        $yearsElapsed = max(0, $yearsElapsed);
        $annualDepreciation = $usefulLife > 0 ? $depreciable / $usefulLife : 0;
        $accumulatedDepreciation = min($depreciable, $annualDepreciation * $yearsElapsed);
        $bookValue = max($salvage, $price - $accumulatedDepreciation);
        return [
            'price' => $price,
            'salvage_value' => $salvage,
            'useful_life' => $usefulLife,
            'annual_depreciation' => $annualDepreciation,
            'years_elapsed' => round($yearsElapsed, 2),
            'accumulated_depreciation' => $accumulatedDepreciation,
            'book_value' => $bookValue,
        ];
    }

    // Global search: aset, user, kategori, jadwal patching
    public static function globalSearch(string $q): array
    {
        $kw = "%$q%";
        $assets = Database::fetchAll(
            "SELECT id, asset_code, name, 'asset' AS type FROM assets WHERE deleted_at IS NULL AND (asset_code LIKE ? OR name LIKE ?) LIMIT 10",
            [$kw, $kw]
        );
        $users = Database::fetchAll(
            "SELECT id, username AS title, name AS sub, 'user' AS type FROM users WHERE username LIKE ? OR name LIKE ? LIMIT 5",
            [$kw, $kw]
        );
        $categories = Database::fetchAll(
            "SELECT id, name AS title, description AS sub, 'category' AS type FROM categories WHERE name LIKE ? LIMIT 5",
            [$kw]
        );
        $schedules = Database::fetchAll(
            "SELECT id, name AS title, CONCAT('Q', quarter, ' ', year) AS sub, 'patching' AS type FROM patch_schedules WHERE name LIKE ? LIMIT 5",
            [$kw]
        );
        return ['assets' => $assets, 'users' => $users, 'categories' => $categories, 'patching' => $schedules];
    }

    // Import aset dari CSV
    public static function importCsv(string $csvContent): int
    {
        $lines = self::parseCsv($csvContent);
        if (count($lines) < 2) {
            return 0;
        }
        // skip header
        $imported = 0;
        for ($i = 1; $i < count($lines); $i++) {
            $row = $lines[$i];
            if (count($row) < 2) continue;
            $name = trim($row[0] ?? '');
            $catName = trim($row[1] ?? '');
            if ($name === '') continue;
            // cari kategori by name
            $cat = Database::fetch("SELECT id FROM categories WHERE name=?", [$catName]);
            $catId = $cat ? (int)$cat['id'] : 1;
            $code = self::generateCode();
            $brand = trim($row[2] ?? '');
            $location = trim($row[3] ?? '');
            $status = trim($row[4] ?? 'tersedia');
            if (!in_array($status, ['tersedia','dipinjam','rusak'])) $status = 'tersedia';
            $pdate = trim($row[5] ?? '') ?: null;
            $price = (float)($row[6] ?? 0);
            Database::query(
                "INSERT INTO assets (asset_code, name, category_id, brand_spec, location, status, purchase_date, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$code, $name, $catId, $brand ?: null, $location ?: null, $status, $pdate, $price]
            );
            $imported++;
        }
        return $imported;
    }

    // Parse CSV string ke array of rows
    private static function parseCsv(string $csv): array
    {
        $rows = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($csv));
        foreach ($lines as $line) {
            if ($line === '') continue;
            $rows[] = str_getcsv($line, ',', '"', '\\');
        }
        return $rows;
    }

    // Hapus foto saja (bila user klik hapus foto)
    public static function removePhoto(int $id): void
    {
        $asset = self::find($id);
        if ($asset && !empty($asset['photo'])) {
            self::deletePhotoFile($asset['photo']);
            Database::query("UPDATE assets SET photo=NULL WHERE id=?", [$id]);
        }
    }

    // Upload file foto, return path relatif atau null
    public static function handleUpload(): ?string
    {
        if (empty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed, true)) {
            return null;
        }
        // Maks 5MB
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            return null;
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'img',
        };
        $name = 'asset_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = PUBLIC_PATH . '/uploads/assets/' . $name;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            return 'uploads/assets/' . $name;
        }
        return null;
    }

    // Hapus file foto dari disk
    public static function deletePhotoFile(?string $path): void
    {
        if (!$path) {
            return;
        }
        $full = PUBLIC_PATH . '/' . $path;
        // Safety: path harus di dalam uploads/assets
        if (str_starts_with(realpath($full) ?: '', PUBLIC_PATH . '/uploads/assets') && is_file($full)) {
            @unlink($full);
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

    // --- Khusus Laporan ---
    // Aset dengan filter rentang tanggal pembelian (untuk laporan)
    public static function forReport(array $filters, int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT a.*, c.name AS category_name
                FROM assets a
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.purchase_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND a.purchase_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        $sql .= " ORDER BY a.asset_code ASC";
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        return Database::fetchAll($sql, $params);
    }

    // Jumlah aset untuk laporan dengan filter (tanpa fetch semua data)
    public static function forReportCount(array $filters): int
    {
        $sql = "SELECT COUNT(*) FROM assets a WHERE 1=1";
        $params = [];
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.purchase_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND a.purchase_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        return (int)Database::scalar($sql, $params);
    }

    // Ringkasan nilai aset berdasarkan filter
    public static function summaryForReport(array $filters): array
    {
        $rows = self::forReport($filters);
        $summary = [
            'total'      => count($rows),
            'tersedia'   => 0,
            'dipinjam'   => 0,
            'rusak'      => 0,
            'nilai_total' => 0.0,
            'nilai_tersedia' => 0.0,
            'nilai_dipinjam' => 0.0,
            'nilai_rusak'    => 0.0,
        ];
        foreach ($rows as $r) {
            $price = (float)$r['price'];
            $summary['nilai_total'] += $price;
            if ($r['status'] === 'tersedia') {
                $summary['tersedia']++;
                $summary['nilai_tersedia'] += $price;
            } elseif ($r['status'] === 'dipinjam') {
                $summary['dipinjam']++;
                $summary['nilai_dipinjam'] += $price;
            } elseif ($r['status'] === 'rusak') {
                $summary['rusak']++;
                $summary['nilai_rusak'] += $price;
            }
        }
        return $summary;
    }

    // Rekap per kategori (dengan filter)
    public static function recapByCategory(array $filters): array
    {
        $sql = "SELECT c.name AS category_name,
                       COUNT(a.id) AS total,
                       COALESCE(SUM(a.price), 0) AS nilai,
                       SUM(CASE WHEN a.status='tersedia' THEN 1 ELSE 0 END) AS tersedia,
                       SUM(CASE WHEN a.status='dipinjam' THEN 1 ELSE 0 END) AS dipinjam,
                       SUM(CASE WHEN a.status='rusak'    THEN 1 ELSE 0 END) AS rusak
                FROM categories c
                LEFT JOIN assets a ON a.category_id = c.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.purchase_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND a.purchase_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        $sql .= " GROUP BY c.id, c.name ORDER BY total DESC, c.name";
        return Database::fetchAll($sql, $params);
    }

    // Rekap per lokasi
    public static function recapByLocation(array $filters): array
    {
        $sql = "SELECT COALESCE(NULLIF(a.location,''), 'Tidak ditentukan') AS location,
                       COUNT(*) AS total,
                       COALESCE(SUM(a.price), 0) AS nilai,
                       SUM(CASE WHEN a.status='tersedia' THEN 1 ELSE 0 END) AS tersedia,
                       SUM(CASE WHEN a.status='dipinjam' THEN 1 ELSE 0 END) AS dipinjam,
                       SUM(CASE WHEN a.status='rusak'    THEN 1 ELSE 0 END) AS rusak
                FROM assets a
                WHERE 1=1";
        $params = [];
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.purchase_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND a.purchase_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        $sql .= " GROUP BY a.location ORDER BY total DESC";
        return Database::fetchAll($sql, $params);
    }

    // Daftar lokasi unik (untuk dropdown/autocomplete filter)
    public static function distinctLocations(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT location FROM assets WHERE location IS NOT NULL AND location <> '' ORDER BY location"
        );
    }
}
