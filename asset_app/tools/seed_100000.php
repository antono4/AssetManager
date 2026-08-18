<?php
// ============================================================================
//  SEEDER 100.000 DATA DUMMY — driver-agnostic (SQLite & MySQL)
//  Untuk AssetManager
//
//  Cara pakai:
//    SQLite (mode demo, default):
//      php tools/seed_100000.php
//    MySQL (produksi) — pastikan config env DB_DRIVER=mysql dsb sudah benar:
//      DB_DRIVER=mysql DB_HOST=127.0.0.1 DB_NAME=asset_db DB_USER=root DB_PASS=... \
//      php tools/seed_100000.php
//
//  Catatan:
//  - Menggunakan koneksi & skema aplikasi (Database::ensureSchema()), sehingga
//    tabel & migrasi (patching, soft delete, borrowings, dll) selalu tersedia.
//  - Idempoten: menghapus data dummy lama (asset_code bertanda 'AST-D%' prefix)
//    sebelum menanam ulang, supaya aman dijalankan berulang.
//  - Membuat: 100.000 aset + ~22.000 peminjaman + 50.000 log aktivitas (default).
//  - Jumlah dapat diubah via argumen: php tools/seed_100000.php 100000 22000 50000
// ============================================================================

// Argumen CLI: [assets] [borrowings] [logs]
$totalAssets = isset($argv[1]) ? max(1, (int)$argv[1]) : 100000;
$totalBorrow = isset($argv[2]) ? (int)$argv[2] : (int)round($totalAssets * 0.22);
$totalLogs   = isset($argv[3]) ? (int)$argv[3] : (int)round($totalAssets * 0.50);

require_once __DIR__ . '/../config.php';

echo "=== Seeder 100.000 data dummy ===\n";
echo "Driver DB : " . Database::driver() . "\n";
echo "Target    : {$totalAssets} aset, {$totalBorrow} peminjaman, {$totalLogs} log\n";

// Pastikan skema + data awal ada (kategori, users, aset contoh, patching, dll)
Database::ensureSchema();
$db = Database::conn();

// Perluas ENUM status agar mendukung 'perawatan' (skema awal hanya 3 status).
$db->exec("ALTER TABLE assets MODIFY COLUMN status ENUM('tersedia','dipinjam','rusak','perawatan') NOT NULL DEFAULT 'tersedia'");

$t0 = microtime(true);

// --- 1. Hapus data dummy lama (idempoten) -------------------------------
echo "Membersihkan data dummy lama...\n";
$db->beginTransaction();
try {
    // borrowings & logs mengacu asset dummy
    $db->exec("DELETE FROM borrowings WHERE asset_id IN (SELECT id FROM assets WHERE asset_code LIKE 'AST-D%')");
    $db->exec("DELETE FROM asset_logs WHERE asset_id IN (SELECT id FROM assets WHERE asset_code LIKE 'AST-D%')");
    // patch checklist mengacu asset dummy
    @$db->exec("DELETE FROM patch_checklist_items WHERE checklist_id IN (SELECT id FROM patch_checklists WHERE asset_id IN (SELECT id FROM assets WHERE asset_code LIKE 'AST-D%'))");
    @$db->exec("DELETE FROM patch_checklists WHERE asset_id IN (SELECT id FROM assets WHERE asset_code LIKE 'AST-D%')");
    $db->exec("DELETE FROM assets WHERE asset_code LIKE 'AST-D%'");
    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    echo "Peringatan saat cleanup: " . $e->getMessage() . "\n";
}

// --- 2. Ambil kategori & user id untuk data realistis -----------------
$cats = $db->query("SELECT id FROM categories ORDER BY id")->fetchAll(PDO::FETCH_COLUMN, 0);
$users = $db->query("SELECT id FROM users ORDER BY id")->fetchAll(PDO::FETCH_COLUMN, 0);
if (!$cats) {
    fwrite(STDERR, "Tabel categories kosong. Jalankan /setup atau ensureSchema terlebih dahulu.\n");
    exit(1);
}

// Data realistis per kategori (brand_spec templates)
$brandTemplates = [
    1 => ['Dell OptiPlex 7090 / i7-11700 / 16GB / SSD 512GB', 'HP EliteDesk 800 G6 / i5-10500 / 8GB / SSD 256GB', 'Lenovo ThinkCentre M75q / Ryzen 5 / 16GB / SSD 512GB'],
    2 => ['Lenovo ThinkPad E14 / Ryzen 5 / 8GB / SSD 512GB', 'MacBook Air M2 / 8GB / SSD 256GB', 'HP ProBook 450 / i5 / 16GB / SSD 512GB'],
    3 => ['Brother HL-L2375DW', 'Epson EcoTank L3210', 'Canon PIXMA G3010'],
    4 => ['Cisco Catalyst 2960 24-Port', 'TP-Link EAP670 AX3000', 'Mikrotik hAP ac2'],
    5 => ['Daikin R32 Inverter', 'Epson EB-X51 2700 lumen', 'UPS APC BR1500GI'],
];
$locations = ['Ruang Server', 'Ruang Developer', 'Ruang Marketing', 'Ruang HRD', 'Ruang Direksi', 'Lobi Utama', 'Ruang Rapat', 'Ruang Operasional', 'Gudang', 'Workshop'];
$statuses  = ['tersedia', 'tersedia', 'tersedia', 'tersedia', 'dipinjam', 'dipinjam', 'rusak', 'perawatan']; // dibobot ke tersedia
$currencies = ['IDR', 'IDR', 'IDR', 'USD', 'EUR'];

// --- 3. Bulk insert aset dengan prepared statement + transaction -------
echo "Menanam {$totalAssets} aset...\n";
$db->beginTransaction();
$ins = $db->prepare(
    "INSERT INTO assets (asset_code, name, category_id, brand_spec, location, status, purchase_date, price, currency, useful_life, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$now = date('Y-m-d H:i:s');
$batchSize = 5000;
$flushAt = $batchSize;
for ($i = 1; $i <= $totalAssets; $i++) {
    $cat = $cats[array_rand($cats)];
    $tpls = $brandTemplates[$cat] ?? ['Unit Generik'];
    $brand = $tpls[array_rand($tpls)];
    $code = 'AST-D' . str_pad((string)$i, 6, '0', STR_PAD_LEFT);
    $name = 'Aset ' . $code;
    $loc = $locations[array_rand($locations)];
    $status = $statuses[array_rand($statuses)];
    $year = rand(2018, 2025);
    $month = str_pad((string)rand(1, 12), 2, '0', STR_PAD_LEFT);
    $day = str_pad((string)rand(1, 28), 2, '0', STR_PAD_LEFT);
    $pdate = "{$year}-{$month}-{$day}";
    $price = rand(15, 900) * 100000 + rand(0, 99999); // ~1.5jt - 90jt
    $cur = $currencies[array_rand($currencies)];
    $life = rand(3, 8);
    $ins->execute([$code, $name, $cat, $brand, $loc, $status, $pdate, $price, $cur, $life, $now, $now]);

    if ($i >= $flushAt) {
        $db->commit();
        $db->beginTransaction();
        $flushAt += $batchSize;
        printf("  ... %d / %d (%.1f%%)\n", $i, $totalAssets, $i * 100 / $totalAssets);
    }
}
$db->commit();
$t1 = microtime(true);
printf("Selesai menanam aset dalam %.2fs\n", $t1 - $t0);

// --- 4. Peminjaman (borrowings) ----------------------------------------
echo "Menanam {$totalBorrow} peminjaman...\n";
$assetIds = $db->query("SELECT id FROM assets WHERE asset_code LIKE 'AST-D%' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN, 0);
$db->beginTransaction();
$bIns = $db->prepare(
    "INSERT INTO borrowings (asset_id, borrower_name, user_id, borrow_date, expected_return, actual_return, note, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$flushAt = $batchSize;
for ($i = 0; $i < $totalBorrow; $i++) {
    $aid = $assetIds[array_rand($assetIds)];
    $uid = $users ? $users[array_rand($users)] : null;
    $borrower = 'Peminjam ' . rand(1, 200);
    $bDate = date('Y-m-d H:i:s', strtotime('-' . rand(1, 300) . ' days'));
    $exp = date('Y-m-d H:i:s', strtotime($bDate . ' +' . rand(3, 30) . ' days'));
    $returned = rand(0, 1) === 1;
    $actual = $returned ? date('Y-m-d H:i:s', strtotime($bDate . ' +' . rand(1, 25) . ' days')) : null;
    $st = $returned ? 'returned' : 'borrowed';
    $bIns->execute([$aid, $borrower, $uid, $bDate, $exp, $actual, 'Pinjam untuk operasional', $st]);
    if (($i + 1) >= $flushAt) {
        $db->commit();
        $db->beginTransaction();
        $flushAt += $batchSize;
    }
}
$db->commit();

// --- 5. Log aktivitas (asset_logs) -------------------------------------
echo "Menanam {$totalLogs} log aktivitas...\n";
$actions = ['created', 'updated', 'status_update', 'tersedia', 'dipinjam', 'rusak', 'perawatan', 'patching'];
$db->beginTransaction();
$lIns = $db->prepare("INSERT INTO asset_logs (asset_id, user_id, action, note, created_at) VALUES (?, ?, ?, ?, ?)");
$flushAt = $batchSize;
for ($i = 0; $i < $totalLogs; $i++) {
    $aid = $assetIds[array_rand($assetIds)];
    $uid = $users ? $users[array_rand($users)] : null;
    $act = $actions[array_rand($actions)];
    $note = 'Aktivitas ' . $act . ' #' . rand(1, $totalAssets);
    $when = date('Y-m-d H:i:s', strtotime('-' . rand(0, 720) . ' hours'));
    $lIns->execute([$aid, $uid, $act, $note, $when]);
    if (($i + 1) >= $flushAt) {
        $db->commit();
        $db->beginTransaction();
        $flushAt += $batchSize;
    }
}
$db->commit();

$t2 = microtime(true);
$total = (int)$db->query("SELECT COUNT(*) FROM assets WHERE asset_code LIKE 'AST-D%'")->fetchColumn();
$bTotal = (int)$db->query("SELECT COUNT(*) FROM borrowings")->fetchColumn();
$lTotal = (int)$db->query("SELECT COUNT(*) FROM asset_logs")->fetchColumn();
echo "\n=== Ringkasan ===\n";
echo "Aset dummy  : {$total}\n";
echo "Peminjaman  : {$bTotal}\n";
echo "Log aktivitas: {$lTotal}\n";
printf("Total waktu seeder: %.2fs\n", $t2 - $t0);
echo "Selesai.\n";
