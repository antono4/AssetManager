#!/usr/bin/env python3
"""Generate 1000-row MySQL INSERT file for AssetManager (assets table)."""
import random

random.seed(42)

brands = {
    1: ['Dell OptiPlex 7090 / i7-11700 / 16GB / SSD 512GB',
        'HP EliteDesk 800 G6 / i5-10500 / 8GB / SSD 256GB',
        'Lenovo ThinkCentre M75q / Ryzen 5 / 16GB / SSD 512GB',
        'Asus ProArt D6 / i9-12900K / 32GB / SSD 1TB'],
    2: ['Lenovo ThinkPad E14 / Ryzen 5 / 8GB / SSD 512GB',
        'MacBook Air M2 / 8GB / SSD 256GB',
        'HP ProBook 450 / i5-1135G7 / 16GB / SSD 512GB',
        'Asus ZenBook 14 / i7 / 16GB / SSD 512GB'],
    3: ['Brother HL-L2375DW', 'Epson EcoTank L3210',
        'Canon PIXMA G3010', 'HP LaserJet Pro M404'],
    4: ['Cisco Catalyst 2960 24-Port', 'TP-Link EAP670 AX3000',
        'Mikrotik hAP ac2', 'Ubiquiti UniFi U6-Lite'],
    5: ['Daikin R32 Inverter', 'Epson EB-X51 2700 lumen',
        'UPS APC BR1500GI', 'Sharp AC 1 PK'],
}
cat_names = {1: 'PC Desktop', 2: 'Laptop', 3: 'Printer/Scanner', 4: 'Jaringan', 5: 'Umum'}
locations = ['Ruang Server', 'Ruang Developer', 'Ruang Marketing', 'Ruang HRD',
             'Ruang Direksi', 'Lobi Utama', 'Ruang Rapat', 'Ruang Operasional',
             'Gudang', 'Workshop']
# Weighted toward 'tersedia' to match app's default distribution
statuses = ['tersedia', 'tersedia', 'tersedia', 'tersedia', 'dipinjam', 'dipinjam', 'rusak']
price_ranges = {1: (8_000_000, 25_000_000), 2: (7_000_000, 30_000_000),
                3: (1_500_000, 5_000_000), 4: (1_500_000, 20_000_000),
                5: (2_000_000, 8_000_000)}


def esc(s):
    return s.replace("'", "''")


lines = []
start = 11  # codes AST-0011 s/d AST-1010 (1000 rows)
for i in range(1000):
    num = start + i
    code = 'AST-%04d' % num
    cat = random.randint(1, 5)
    name = f"{cat_names[cat]} {code}"
    brand = random.choice(brands[cat])
    loc = random.choice(locations)
    st = random.choice(statuses)
    y = random.randint(2018, 2025)
    m = random.randint(1, 12)
    d = random.randint(1, 28)
    pdate = f"{y:04d}-{m:02d}-{d:02d}"
    lo, hi = price_ranges[cat]
    price = random.randint(lo, hi)
    lines.append(f"('{esc(code)}','{esc(name)}',{cat},'{esc(brand)}','{esc(loc)}','{st}','{pdate}',{price})")

header = """-- ============================================================================
--  1000 DATA DUMMY ASET — MySQL / MariaDB (INSERT murni, tanpa stored procedure)
--  Untuk AssetManager (database: asset_db, tabel: assets)
--
--  Cara pakai:
--    1. Import skema + seed awal:
--         mysql -u root -p asset_db < database/assets_app.sql
--    2. Import file ini (menambah 1000 aset, kode AST-0011 s/d AST-1010):
--         mysql -u root -p asset_db < database/seed_1000_rows.sql
--
--  Catatan:
--  - Idempoten: menghapus dulu data kode AST-0011..AST-1010 bila dijalankan ulang.
--  - Status hanya memakai 3 enum dari skema awal (tersedia/dipinjam/rusak).
--  - category_id: 1=Komputer 2=Laptop 3=Printer 4=Jaringan 5=Umum.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Bersihkan rentang kode dummy bila dijalankan ulang (idempoten).
-- Tabel `asset_logs` pasti ada (dari assets_app.sql); `borrowings` hanya ada
-- bila migrateExtended sudah dijalankan (via app / seed_1000.sql) — jadi
-- kita CREATE dummy table temp untuk memastikan DELETE tidak error bila tabel
-- belum ada, lalu di-drop.
CREATE TABLE IF NOT EXISTS `borrowings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `borrower_name` VARCHAR(100) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `borrow_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expected_return` DATETIME DEFAULT NULL,
  `actual_return` DATETIME DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'borrowed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `asset_logs` WHERE `asset_id` IN (SELECT `id` FROM `assets` WHERE `asset_code` BETWEEN 'AST-0011' AND 'AST-1010');
DELETE FROM `borrowings` WHERE `asset_id` IN (SELECT `id` FROM `assets` WHERE `asset_code` BETWEEN 'AST-0011' AND 'AST-1010');
DELETE FROM `assets`     WHERE `asset_code` BETWEEN 'AST-0011' AND 'AST-1010';

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `assets` (`asset_code`,`name`,`category_id`,`brand_spec`,`location`,`status`,`purchase_date`,`price`) VALUES
"""

chunk = 250
stmts = []
for i in range(0, len(lines), chunk):
    block = ",\n".join(lines[i:i + chunk])
    if i + chunk < len(lines):
        stmts.append(block + ",")
    else:
        stmts.append(block + ";")
body = "\n".join(stmts)

out = 'database/seed_1000_rows.sql'
with open(out, 'w') as f:
    f.write(header)
    f.write(body)
    f.write("\n")

print(f"rows: {len(lines)}")
print(f"file written: {out}")
