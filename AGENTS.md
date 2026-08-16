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

## Patching Module (Jadwal & Checklist per 3 bulan/kuartal)
- Tables: patch_items (template), patch_schedules (kuartal Q1-Q4), patch_checklists (per aset per jadwal), patch_checklist_items (centangan)
- Flow: admin buat jadwal (auto-fill tanggal kuartal) → generate checklist aset IT (exclude kategori "Umum") → staff/admin centang item → auto-status: in_progress → completed saat semua item tercentang → schedule auto-refresh status (draft→ongoing→completed)
- 6 default items: Update OS/Firmware, Antivirus, Backup, Cek Log, Restart, Verifikasi
- Routes: /patching, /patching/create, /patching/{id}, /patching/checklist/{id}, toggle via AJAX
- RBAC: admin=full CRUD+generate; staff=view+centang+skip (no create/edit/delete/generate)
- Patch completion logged to asset_logs with action='patching'
