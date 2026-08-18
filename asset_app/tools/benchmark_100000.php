<?php
// ============================================================================
//  BENCHMARK 100.000 DATA DUMMY — mengukur performa query aplikasi
//  Untuk AssetManager
//
//  Cara pakai:
//    php tools/benchmark_100000.php
//
//  Mengukur (via model/Database aplikasi, jadi menguji code path asli):
//   - Dashboard stats (5x COUNT + SUM)
//   - countByCategory (GROUP BY)
//   - countByStatus (GROUP BY)
//   - Asset::count() dengan & tanpa filter
//   - Asset::all() halaman pertama & deep pagination (offset besar)
//   - Asset::all() dengan search
//   - Asset::find() by id
//   - globalSearch()
//   - exportCsv() (ambil semua)
//   - REST API Asset::all('', '', '', 0, 0)
// ============================================================================

require_once __DIR__ . '/../config.php';
Database::ensureSchema();

function bench(string $label, callable $fn, int $runs = 3): array
{
    $times = [];
    $result = null;
    for ($r = 0; $r < $runs; $r++) {
        $t = microtime(true);
        $result = $fn();
        $times[] = (microtime(true) - $t) * 1000;
    }
    $avg = array_sum($times) / count($times);
    $min = min($times);
    $max = max($times);
    printf("  %-48s avg %8.2f ms  (min %8.2f, max %8.2f)\n", $label, $avg, $min, $max);
    return ['label' => $label, 'avg' => $avg, 'min' => $min, 'max' => $max, 'result' => $result, 'times' => $times];
}

echo "=== Benchmark 100.000 data ===\n";
echo "Driver DB : " . Database::driver() . "\n";
$total = (int)Database::scalar("SELECT COUNT(*) FROM assets WHERE asset_code LIKE 'AST-D%'");
echo "Aset dummy: {$total}\n";
if ($total < 1) {
    fwrite(STDERR, "Tidak ada data dummy. Jalankan: php tools/seed_100000.php\n");
    exit(1);
}
echo "\n";

// Ambil satu id dummy untuk uji find()
$sampleId = (int)Database::scalar("SELECT id FROM assets WHERE asset_code LIKE 'AST-D%' LIMIT 1");
$midId = (int)Database::scalar("SELECT id FROM assets WHERE asset_code LIKE 'AST-D%' ORDER BY id LIMIT 1 OFFSET " . intdiv($total, 2));
$lastId = (int)Database::scalar("SELECT id FROM assets WHERE asset_code LIKE 'AST-D%' ORDER BY id DESC LIMIT 1");
echo "Sample IDs: first={$sampleId} mid={$midId} last={$lastId}\n\n";

$results = [];

echo "[ Dashboard ]\n";
$results[] = bench('Asset::stats() (5x COUNT/SUM)', fn() => Asset::stats());
$results[] = bench('Asset::countByCategory() GROUP BY', fn() => Asset::countByCategory());
$results[] = bench('Asset::countByStatus() GROUP BY', fn() => Asset::countByStatus());
$results[] = bench('Asset::recent(5)', fn() => Asset::recent(5));

echo "\n[ Listing & Pagination ]\n";
$results[] = bench('Asset::count() tanpa filter', fn() => Asset::count());
$results[] = bench('Asset::count() filter status=dipinjam', fn() => Asset::count('', 'dipinjam', ''));
$results[] = bench('Asset::count() filter kategori=1', fn() => Asset::count('', '', '1'));
$results[] = bench('Asset::all() halaman 1 (10 baris)', fn() => Asset::all('', '', '', 10, 0));
// Deep pagination: offset mendekati akhir (slow path tanpa keyset)
$deepOffset = max(0, $total - 20);
$results[] = bench("Asset::all() deep offset={$deepOffset}", fn() => Asset::all('', '', '', 10, $deepOffset));

echo "\n[ Search ]\n";
$results[] = bench('Asset::all() search "AST-D0005%" (LIKE)', fn() => Asset::all('AST-D0005', '', '', 10, 0));
$results[] = bench('Asset::all() search "Laptop" (LIKE name)', fn() => Asset::all('Laptop', '', '', 10, 0));
$results[] = bench('Asset::count() search "Ruang Server" (LIKE location)', fn() => Asset::count('Ruang Server', '', ''));
$results[] = bench('Asset::globalSearch("AST-D0010")', fn() => Asset::globalSearch('AST-D0010'));

echo "\n[ Find by ID ]\n";
$results[] = bench('Asset::find(first)', fn() => Asset::find($sampleId));
$results[] = bench('Asset::find(mid)', fn() => Asset::find($midId));
$results[] = bench('Asset::find(last)', fn() => Asset::find($lastId));

echo "\n[ Bulk / API ]\n";
$results[] = bench('Asset::all() SEMUA (tanpa limit) — exportCsv path', function () {
    $a = Asset::all('', '', '', 0, 0);
    return count($a);
}, 1);
$results[] = bench('Asset::exportCsv() generate string', function () {
    $s = Asset::exportCsv();
    return strlen($s);
}, 1);

// Index check
echo "\n[ Index check ]\n";
$idx = Database::fetchAll("SHOW INDEX FROM assets");
$idxNames = array_unique(array_column($idx, 'Key_name'));
echo "  Index pada tabel assets: " . implode(', ', $idxNames) . "\n";

// Simpan hasil ke file CSV
$csvFile = __DIR__ . '/benchmark_100000_results.csv';
$fp = fopen($csvFile, 'w');
fputcsv($fp, ['label', 'avg_ms', 'min_ms', 'max_ms'], ',', '"', '\\');
foreach ($results as $r) {
    fputcsv($fp, [$r['label'], round($r['avg'], 3), round($r['min'], 3), round($r['max'], 3)], ',', '"', '\\');
}
fclose($fp);
echo "\nHasil disimpan ke: {$csvFile}\n";
