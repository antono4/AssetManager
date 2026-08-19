# AGENTS.md - Asset Management App

## Project: asset_app
PHP native asset management app with AdminLTE 3 UI. Located at `/workspace/project/asset_app`.

## Tech Stack
- PHP 8.4 (native, no framework), PDO MySQL (SQLite demo removed)
- AdminLTE 3.2 via CDN, Bootstrap 4, ApexCharts, bcrypt auth
- PHP built-in server for dev: `php -S 0.0.0.0:12000 -t public public/index.php`

## Key Architecture
- `config.php`: constants + autoload (spl_autoload_register maps class → core/models/controllers). Reads config from env vars OR a `.env` file in app root (loader added, no dependency).
- `public/index.php`: entry point, path parsing, static file passthrough for built-in server, routes
- `app/core/Router.php`: regex-based routing with `{param}` placeholders
- `app/core/View.php`: render($view, $data, $layout) — uses extract(); view/layout names stored in `$_viewName`/`$_layoutName` to avoid collision with data keys like `page`
- `app/core/Database.php`: MySQL-only singleton PDO. `ensureSchema()` imports `database/assets_app.sql` (which already seeds categories/users/assets/logs/patch_items) if `users` table missing, then `migratePatching()` creates feature tables (idempotent).
- DB config: `DB_DRIVER` (mysql, default), `DB_HOST/PORT/NAME/USER/PASS` — via env vars or `.env` file. Defaults: mysql / assets_app / root / (empty password) → matches XAMPP.

## Default Accounts
- admin / admin123 (role: admin)
- staff / staff123 (role: staff)
- `/setup` route resets these passwords to valid bcrypt hashes

## RBAC
- `Auth::requireLogin()` / `Auth::requireAdmin()` guard controllers
- Admin: full access (categories, users, asset CRUD, setup)
- Staff: view assets, change status, view logs, edit own profile (no categories/users/asset-create-edit)

## Important Patterns
- Helpers in `app/core/View.php`: `e()`, `url()`, `asset_url()`, `rp()`, `tgl()`, `status_badge()`, `flash_messages()`
- BASE_URL auto-computed in config.php (handles built-in server router vs sub-folder deployment)
- Asset codes auto-generated: AST-#### (zero-padded, sequential)

## Gotchas Learned
- PHP built-in server with router file: SCRIPT_NAME = request path (not /index.php), so BASE_URL must detect router mode
- Static files (CSS/JS) need `return false;` in index.php for built-in server to serve them directly
- `extract($data)` in View overwrites local vars — never name view/layout params same as data keys
- `?? === ` precedence bug: `(self::user()['role'] ?? null) === 'admin'` needs parentheses
- MySQL uses `AUTO_INCREMENT` with separate `PRIMARY KEY (id)` line. (SQLite demo removed — app is MySQL-only now.)
- DB migrations for new features: run via `ensureSchema()` → `migratePatching()` (idempotent, CREATE TABLE IF NOT EXISTS). Existing DBs get new tables without re-seed.
- `ensureSchema()` imports `database/assets_app.sql` which ALREADY seeds data — do NOT call `seed()` after import (duplicate key error). seed() method removed.
- Folder writable yang perlu dijaga: `public/uploads/assets/` (foto aset).

## Company Settings Module (Nama & Alamat Perusahaan)
- Table: `settings` (key-value: `setting_key`, `setting_value`, `updated_at`) — dibuat via `migrateExtended()` (idempotent, CREATE TABLE IF NOT EXISTS)
- Model `Setting::get($key, $default)` (cached), `Setting::set($key, $value)` (SELECT-then-INSERT/UPDATE upsert, driver-agnostic), `Setting::companyName()/companyAddress()/companyPhone()/companyEmail()` (companyName fallback ke APP_NAME bila kosong)
- Controller `SettingController::index()` (form) + `update()` (save) — **admin only** via `Auth::requireAdmin()`; log ke `audit_trail` module='settings'
- Route: `/settings` (GET form, POST update)
- View: `app/views/pages/settings/index.php` (form nama, alamat, telepon, email)
- Integrasi: brand-text sidebar (`app.php`) pakai `Setting::companyName()`; kop laporan (`reports/print.php`) pakai nama + alamat + telepon + email
- Menu sidebar "Company Settings" muncul di section ADMINISTRATION (admin only)

## Patching Module (Jadwal & Checklist per 3 bulan/kuartal)
- Tables: patch_items (template), patch_schedules (kuartal Q1-Q4), patch_checklists (per aset per jadwal), patch_checklist_items (centangan)
- Flow: admin buat jadwal (auto-fill tanggal kuartal) → generate checklist aset IT (exclude kategori "Umum") → staff/admin centang item → auto-status: in_progress → completed saat semua item tercentang → schedule auto-refresh status (draft→ongoing→completed)
- Form jadwal: **Kuartal** = dropdown statis Q1-Q4 (label rentang bulan), **Periode/Tahun** = input `type="date"` (name `period`); tahun diekstrak via `PatchSchedule::yearFromPeriod()` (regex `\d{4}`, fallback tahun berjalan). `quarterOptions()` sudah dihapus. Versi HTML (pages3.js) punya pola yang sama.
- 6 default items: Update OS/Firmware, Antivirus, Backup, Cek Log, Restart, Verifikasi
- Routes: /patching, /patching/create, /patching/{id}, /patching/checklist/{id}, toggle via AJAX
- RBAC: admin=full CRUD+generate; staff=view+centang+skip (no create/edit/delete/generate)
- Patch completion logged to asset_logs with action='patching'

## Performance Testing (100.000 data dummy)
- Tools: `asset_app/tools/seed_100000.php` (seeder MySQL) + `asset_app/tools/benchmark_100000.php` (benchmark) + `asset_app/tools/benchmark_100000_results.csv`.
- Dummy asset_code ber-prefix `AST-D` (mis. `AST-D000001`) supaya idempoten & mudah dibersihkan (`asset_code LIKE 'AST-D%'`).
- Jalankan: `php tools/seed_100000.php [assets] [borrowings] [logs]` (default 100000/22000/50000). Konfigurasi via `.env` atau env var OS.
- Seeder menyetel ENUM status MySQL ke `('tersedia','dipinjam','rusak','perawatan')` karena skema awal `database/assets_app.sql` hanya punya 3 status (app pakai 4).
- Hasil 100k MySQL (avg ms): find by id <1ms; count & pagination hal-1 ~10-20ms; search LIKE 80-100ms; deep offset (halaman akhir) ~144ms; `forReport()` tanpa LIMIT (tab summary `/reports`) memuat SEMUA aset → ~1.18s (bottleneck utama); `exportCsv()` ~510ms.
- Bug ditemukan & diperbaiki saat pengujian MySQL:
  1. `Database::ensureSchema()` pakai `SELECT name FROM information_schema.tables` — kolom `name` tidak ada di MySQL (harus `TABLE_NAME`) → fatal error di setiap request MySQL. Diperbaiki.
  2. `Database::migratePatching()` helper `$key()` menambah koma di akhir baris `KEY`, tapi kolom terakhir tak punya koma → `...CURRENT_TIMESTAMP PRIMARY KEY (id)` invalid di MySQL → fatal error. Diperbaiki: index/UNIQUE dibuat via `CREATE INDEX` terpisah (cek `information_schema.statistics`).
  3. Path `assets_app.sql` salah relatif (`__DIR__/../database`) → harus `dirname(dirname(__DIR__)).'/database'`. Diperbaiki agar `ensureSchema()` jalan dari DB kosong.
- Catatan: password default di `assets_app.sql` berukuran 70 char (bukan bcrypt 60) → login admin gagal sampai route `/setup` dipanggil (reset ke bcrypt valid).
- SQLite demo dihapus: `config.php` default `DB_DRIVER=mysql`, `SQLITE_PATH`/`isSqlite()`/`createSqliteSchema()`/`seed()` dihapus, semua branch SQLite di `migratePatching`/`migrateExtended` diratakan ke MySQL.

## HTML Version (Static Clone + Live API)
- Lokasi: `html_version/`. Dua mode: **Live** (backend API Python, data shared di server) atau **Statis** (fallback localStorage per-browser).
- Mode Live: jalankan `PORT=12001 python3 api/server.py` — backend (Python stdlib) menyajikan file statis + REST API (`/api/db` GET snapshot, `POST /api/db` simpan, `POST /api/login`, `POST /api/reset`, `GET /api/assets`). Data persisten di `html_version/database/live_db.json` (di-seed dari assets_app.sql, di-gitignore). Footer menampilkan "Database: Live API".
- Mode Statis: `python3 -m http.server` — bila `/api/db` tidak tersedia, Store.hydrate() fallback ke localStorage. Footer "Database: LocalStorage".
- Routing: hash-based (#/assets, #/patching/1, #/reports?tab=category). Router di assets/js/router.js — handler tanpa path-param menerima `query` sebagai arg pertama; handler dengan {id} menerima (params, query).
- Struktur JS: store.js (data layer: hydrate dari API/cache in-memory, save push ke API atau localStorage), i18n.js (EN/ID, port lang/*.php), helpers.js (e/rp/tgl/Auth/Setting), router.js, views.js (layout shell/blank/print, render ke #app-root via innerHTML), pages.js/pages2.js/pages3.js (semua halaman), app.js (register routes + hashchange + Store.hydrate() sebelum dispatch pertama).
- Library dimuat sekali di index.html (jQuery, Bootstrap 4, AdminLTE 3, ApexCharts CDN). Views render ke #app-root innerHTML; script per-halaman (chart) dieksekusi via new Function() di Views.runPendingScripts.
- Akun default: admin/admin123, staff/staff123 (password plain — demo only). Reset live DB: `curl -X POST <host>/api/reset` atau buka #setup.
- Foto aset disimpan base64 (di DB live maupun localStorage). REST API (#/api/assets) mengembalikan JSON dari server live.
