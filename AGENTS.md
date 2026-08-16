# AGENTS.md - Asset Management App

## Project: asset_app
PHP native asset management app with AdminLTE 3 UI. Located at `/workspace/project/asset_app`.

## Tech Stack
- PHP 8.4 (native, no framework), PDO (MySQL + SQLite adapter)
- AdminLTE 3.2 via CDN, Bootstrap 4, ApexCharts, bcrypt auth
- PHP built-in server for dev: `php -S 0.0.0.0:12000 -t public public/index.php`

## Key Architecture
- `config.php`: constants + autoload (spl_autoload_register maps class → core/models/controllers)
- `public/index.php`: entry point, path parsing, static file passthrough for built-in server, routes
- `app/core/Router.php`: regex-based routing with `{param}` placeholders
- `app/core/View.php`: render($view, $data, $layout) — uses extract(); view/layout names stored in `$_viewName`/`$_layoutName` to avoid collision with data keys like `page`
- `app/core/Database.php`: singleton PDO, `ensureSchema()` auto-creates SQLite schema + seeds if `users` table missing; MySQL uses `database/assets_app.sql`
- DB_DRIVER env: `sqlite` (default, demo) or `mysql` (production)

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
- SQLite uses `AUTOINCREMENT` (no underscore) and must follow `PRIMARY KEY` directly: `id INTEGER PRIMARY KEY AUTOINCREMENT`. MySQL uses `AUTO_INCREMENT` with separate `PRIMARY KEY (id)` line. The migratePatching() helper handles both via driver detection.
- DB migrations for new features: run via `ensureSchema()` → `migratePatching()` (idempotent, CREATE TABLE IF NOT EXISTS). Existing DBs get new tables without re-seed.
- SQLite permission gotcha: folder `database/` HARUS writable oleh user web server (XAMPP/Apache jalan sebagai `daemon`/`nobody`, Nginx+PHP-FPM sebagai `www-data`). Bila tidak writable → error `SQLSTATE[HY000] [14] unable to open database file`. Folder `database/` sering sudah ada (berisi `assets_app.sql`) saat diekstrak, sehingga `mkdir()` di `Database::conn()` tidak dipanggil dan izin default dipakai. Solusi: `chmod 775 database` + `chown` ke user web server. Kode `Database::conn()` kini auto-`chmod` folder (0777) & file DB (0666) bila tidak writable, tapi ini gagal bila PHP/web server sendiri tidak punya izin ubah izin → set manual tetap perlu.
- Folder writable yang perlu dijaga: `database/` (SQLite file) dan `public/uploads/assets/` (foto aset).

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
- 6 default items: Update OS/Firmware, Antivirus, Backup, Cek Log, Restart, Verifikasi
- Routes: /patching, /patching/create, /patching/{id}, /patching/checklist/{id}, toggle via AJAX
- RBAC: admin=full CRUD+generate; staff=view+centang+skip (no create/edit/delete/generate)
- Patch completion logged to asset_logs with action='patching'

## HTML Version (Static Clone)
- Lokasi: `html_version/` — jalankan via `python3 -m http.server` di folder tsb, buka `index.html` (jangan via file://).
- Port statis HTML/JS dari app PHP: tidak butuh server-side; data di localStorage (key `asset_manager_html_v1:*`), seed otomatis mengikuti database/assets_app.sql.
- Routing: hash-based (#/assets, #/patching/1, #/reports?tab=category). Router di assets/js/router.js — handler tanpa path-param menerima `query` sebagai arg pertama; handler dengan {id} menerima (params, query).
- Struktur JS: store.js (data+seed), i18n.js (EN/ID, port lang/*.php), helpers.js (e/rp/tgl/Auth/Setting), router.js, views.js (layout shell/blank/print, render ke #app-root via innerHTML), pages.js/pages2.js/pages3.js (semua halaman), app.js (register routes + hashchange).
- Library dimuat sekali di index.html (jQuery, Bootstrap 4, AdminLTE 3, ApexCharts CDN). Views render ke #app-root innerHTML; script per-halaman (chart) dieksekusi via new Function() di Views.runPendingScripts.
- Akun default: admin/admin123, staff/staff123 (password plain di localStorage — demo only). Reset: hapus key `asset_manager_html_v1:*` di localStorage atau buka #setup.
- Foto aset disimpan base64 di localStorage. REST API (#/api/assets) mengembalikan JSON statis (token tidak diverifikasi).
