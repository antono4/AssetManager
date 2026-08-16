<?php
// ============================================================================
//  SEEDER 1000 DATA DUMMY — AssetManager
//  Menambahkan 1000 aset dummy + peminjaman + log aktivitas yang realistis.
//
//  Cara pakai (dari folder asset_app):
//    php tools/seed_1000.php
//
//  Catatan:
//  - Aman dijalankan berulang kali (skip bila kode sudah ada).
//  - Kode aset auto-lanjut dari AST-#### terakhir yang ada di DB.
//  - Pakai transaksi batch agar cepat.
// ============================================================================

require __DIR__ . '/../config.php';

Database::ensureSchema();

// --- Data template realistis ---
$brands = [
    1 => ['Dell OptiPlex 7090', 'HP EliteDesk 800 G6', 'Lenovo ThinkCentre M70q', 'Asus ExpertCenter D500', 'Acer Veriton X200'],
    2 => ['Lenovo ThinkPad E14', 'HP ProBook 450 G8', 'Dell Latitude 5420', 'MacBook Air M2', 'Asus ZenBook 14'],
    3 => ['Brother HL-L2375DW', 'Epson EcoTank L3210', 'HP LaserJet Pro M404', 'Canon PIXMA G2010', 'Fuji Xerox DocuPrint P225d'],
    4 => ['Cisco Catalyst 2960', 'TP-Link EAP670 AX3000', 'Ubiquiti UniFi 6 Lite', 'Mikrotik hEX S', 'Aruba Instant On 1830'],
    5 => ['Daikin R32 Inverter 1PK', 'Epson EB-X51 Projector', 'Panasonic AC 1.5PK', 'Sharp Plasmacluster', 'Samsung AR12TYHYEW'],
];
$catNames = [1 => 'Komputer', 2 => 'Laptop', 3 => 'Printer', 4 => 'Jaringan', 5 => 'Umum'];
$locations = [
    'Ruang Server', 'Ruang Developer', 'Ruang Marketing', 'Ruang HRD', 'Ruang Direksi',
    'Ruang Operasional', 'Lobi Utama', 'Ruang Rapat', 'Gudang', 'Ruang IT',
];
// Distribusi status realistis: mayoritas tersedia
$statuses = ['tersedia', 'tersedia', 'tersedia', 'tersedia', 'tersedia', 'tersedia', 'dipinjam', 'dipinjam', 'rusak', 'perawatan'];
$currencies = ['IDR', 'IDR', 'IDR', 'IDR', 'IDR', 'IDR', 'IDR', 'IDR', 'USD', 'EUR'];
$borrowers = ['Budi Santoso', 'Siti Rahayu', 'Andi Wijaya', 'Dewi Lestari', 'Rudi Hartono',
              'Maya Putri', 'Joko Susilo', 'Rina Marlina', 'Agus Salim', 'Farah Diba'];

$total = 1000;
$existing = (int)Database::scalar("SELECT COUNT(*) FROM assets");
$startCode = $existing + 1;

echo "=== SEEDER 1000 DATA DUMMY ===\n";
echo "Aset eksisting: {$existing}\n";
echo "Mulai dari kode: AST-" . str_pad((string)$startCode, 4, '0', STR_PAD_LEFT) . "\n\n";

$t0 = microtime(true);

// --- Insert aset dalam batch transaksi ---
$batchSize = 200;
$db = Database::conn();
$stmt = $db->prepare(
    "INSERT INTO assets (asset_code, name, category_id, brand_spec, location, status, purchase_date, price, currency, useful_life)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
mt_srand(20260816); // reproducible

$assetIds = [];       // id aset yang baru dibuat (untuk log & borrow)
$dipinjamIds = [];    // id aset berstatus dipinjam (untuk borrowing)

for ($i = 0; $i < $total; $i++) {
    if ($i % $batchSize === 0) {
        if ($i > 0) {
            $db->commit();
        }
        $db->beginTransaction();
    }

    $codeNum = $startCode + $i;
    $code = 'AST-' . str_pad((string)$codeNum, 4, '0', STR_PAD_LEFT);
    $cat = random_int(1, 5);
    $brand = $brands[$cat][array_rand($brands[$cat])];
    $name = $catNames[$cat] . ' ' . $codeNum;
    $loc = $locations[array_rand($locations)];
    $status = $statuses[array_rand($statuses)];
    $year = random_int(2019, 2025);
    $month = str_pad((string)random_int(1, 12), 2, '0', STR_PAD_LEFT);
    $day = str_pad((string)random_int(1, 28), 2, '0', STR_PAD_LEFT);
    $date = "{$year}-{$month}-{$day}";
    $price = random_int(500, 35000) * 1000;
    $currency = $currencies[array_rand($currencies)];
    $usefulLife = random_int(3, 8);

    $stmt->execute([$code, $name, $cat, $brand, $loc, $status, $date, $price, $currency, $usefulLife]);
    $newId = (int)$db->lastInsertId();
    $assetIds[] = $newId;
    if ($status === 'dipinjam') {
        $dipinjamIds[] = $newId;
    }
}
$db->commit();

$t1 = microtime(true);
$finalCount = (int)Database::scalar("SELECT COUNT(*) FROM assets");

echo sprintf("Aset: %d ditambahkan (%.2fs)\n", $total, $t1 - $t0);

// --- Tambah peminjaman untuk aset berstatus dipinjam ---
$borrowCount = 0;
if (!empty($dipinjamIds)) {
    $db->beginTransaction();
    $bStmt = $db->prepare(
        "INSERT INTO borrowings (asset_id, borrower_name, user_id, borrow_date, expected_return, note, status)
         VALUES (?, ?, ?, ?, ?, ?, 'borrowed')"
    );
    foreach ($dipinjamIds as $aid) {
        $borrower = $borrowers[array_rand($borrowers)];
        $borrowTs = date('Y-m-d H:i:s', strtotime('-' . random_int(1, 30) . ' days'));
        $expected = date('Y-m-d H:i:s', strtotime($borrowTs . ' +' . random_int(7, 30) . ' days'));
        $bStmt->execute([$aid, $borrower, random_int(1, 2), $borrowTs, $expected, 'Peminjaman operasional']);
        $borrowCount++;
    }
    $db->commit();
}
echo "Peminjaman: {$borrowCount} ditambahkan\n";

// --- Tambah log aktivitas untuk sebagian aset ---
$logCount = 0;
$db->beginTransaction();
$lStmt = $db->prepare(
    "INSERT INTO asset_logs (asset_id, user_id, action, note, created_at)
     VALUES (?, ?, ?, ?, ?)"
);
// Log untuk 200 aset pertama yang baru dibuat
$logActions = ['created', 'status_update', 'dipinjam', 'updated', 'perawatan'];
for ($li = 0; $li < min(200, count($assetIds)); $li++) {
    $aid = $assetIds[$li];
    $action = $logActions[array_rand($logActions)];
    $logTs = date('Y-m-d H:i:s', strtotime('-' . random_int(1, 90) . ' days'));
    $notes = [
        'created'       => 'Aset ditambahkan ke inventaris',
        'status_update' => 'Status diperbarui melalui dashboard',
        'dipinjam'      => 'Dipinjam untuk keperluan operasional',
        'updated'       => 'Data aset diperbarui',
        'perawatan'     => 'Maintenance rutin dilakukan',
    ];
    $lStmt->execute([$aid, random_int(1, 2), $action, $notes[$action], $logTs]);
    $logCount++;
}
$db->commit();
echo "Log aktivitas: {$logCount} ditambahkan\n";

// --- Ringkasan ---
$t2 = microtime(true);
echo "\n=== SELESAI ===\n";
echo "Total waktu: " . sprintf("%.2fs\n", $t2 - $t0);
echo "Total aset di DB: " . number_format($finalCount) . "\n";
echo "DB size: " . round(filesize(SQLITE_PATH) / 1024 / 1024, 2) . " MB\n";

echo "\nDistribusi status:\n";
foreach (Database::fetchAll("SELECT status, COUNT(*) AS cnt FROM assets GROUP BY status ORDER BY cnt DESC") as $r) {
    echo "  " . str_pad($r['status'], 12) . ": " . number_format($r['cnt']) . "\n";
}
echo "Distribusi kategori:\n";
foreach (Database::fetchAll("SELECT c.name, COUNT(a.id) AS cnt FROM assets a JOIN categories c ON c.id = a.category_id GROUP BY c.id ORDER BY cnt DESC") as $r) {
    echo "  " . str_pad($r['name'], 12) . ": " . number_format($r['cnt']) . "\n";
}
