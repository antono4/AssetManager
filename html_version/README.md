# AssetManager — HTML Version

Versi statis (HTML/JS murni) dari aplikasi [AssetManager](../README.md) PHP. Mendukung dua mode: **Live** (backend API Python, data persisten di server, shared antar sesi) atau **Statis** (fallback localStorage per-browser). Mengkloning UI dan fitur aplikasi PHP asli (AdminLTE 3, dashboard, manajemen aset, patching kuartalan, laporan, peminjaman, RBAC, dark mode, multi-bahasa).

## Menjalankan

### Mode Live MySQL (koneksi langsung ke database `assets_app`)

Backend `api/server_mysql.py` (PyMySQL) membaca/menulis langsung ke database
MySQL `assets_app` (kompatibel dengan schema `assets_app.sql`). Data persisten
di MySQL, shared antar sesi, dan bisa dilihat di phpMyAdmin.

```bash
# 1. Install MariaDB/MySQL + PyMySQL
pip install pymysql

# 2. Buat database & import schema
mysql -u root -p -e "CREATE DATABASE assets_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p assets_app < asset_app/database/assets_app.sql
mysql -u root -p assets_app < html_version/api/setup_mysql.sql   # tabel + seed tambahan
mysql -u root -p assets_app < html_version/api/alter_mysql.sql   # kolom tambahan

# 3. Buat user API
mysql -u root -p -e "CREATE USER 'asset_app'@'%' IDENTIFIED BY 'asset_secret'; GRANT ALL ON assets_app.* TO 'asset_app'@'%'; FLUSH PRIVILEGES;"

# 4. Jalankan backend
cd html_version
MYSQL_HOST=127.0.0.1 MYSQL_USER=asset_app MYSQL_PASSWORD=asset_secret MYSQL_DB=assets_app \
  PORT=12001 python3 api/server_mysql.py
# buka http://localhost:12001/index.html  -> footer "Database: Live API"
```

Reset DB ke seed awal: `curl -X POST http://localhost:12001/api/reset`
(atau hapus data manual via phpMyAdmin, lalu re-import SQL setup).

### Mode Live JSON (backend file, tanpa MySQL)

Backend `api/server.py` (Python stdlib) — data persisten ke
`database/live_db.json`. Cocok bila tidak ada MySQL.

```bash
cd html_version && PORT=12001 python3 api/server.py
```

### Mode Statis (localStorage, tanpa server)

Bila backend API tidak tersedia, aplikasi otomatis fallback ke localStorage
(per-browser). Cukup buka `index.html` via server statis mana pun.

```bash
cd html_version
python3 -m http.server 8080
# buka http://localhost:8080/index.html  -> footer "Database: LocalStorage"
```

> Penting: jangan buka via `file://` (modul fetch/CDN diblok); pakai server statis.

## Akun Default

| Username | Password | Role |
|----------|----------|------|
| `admin`  | `admin123` | admin (semua fitur) |
| `staff`  | `staff123` | staff (lihat, pinjam, patching) |

> Reset data ke seed awal: hapus key `asset_manager_html_v1:*` di localStorage (DevTools → Application → Local Storage), lalu reload. Atau buka `#setup`.

## Fitur yang Di-port

- **Login** split-screen glassmorphism dengan particle animation
- **Dashboard** — stat card gradient, chart ApexCharts (bar kategori + donut status), widget patching, quick access, aset terbaru, timeline aktivitas
- **Manajemen Aset** — list + filter/search/pagination, detail + QR code + depreciation, form create/edit + upload foto (base64), ubah status, soft-delete (trash + restore + force-delete), export CSV, import CSV
- **Patching** — jadwal kuartal, generate checklist aset IT (exclude "Umum"), checklist per aset dengan toggle item + input kode patching (KBxxxx), auto-status (pending → in_progress → completed), skip/reset, matriks komputer × kode patch
- **Laporan** — 4 tab (Ringkasan/Per Kategori/Per Lokasi/Detail) + filter + cetak (popup window print-friendly dengan kop perusahaan + tanda tangan)
- **Peminjaman** — form borrow + return, deteksi overdue
- **RBAC** — admin (semua) vs staff (lihat/pinjam/patching, tanpa harga & menu admin)
- **Dark Mode** — toggle persisten via localStorage
- **Multi-Bahasa** — English (default) + Bahasa Indonesia
- **Audit Trail, Notifikasi, API Token** (simulasi), **Global Search**, **Settings Perusahaan** (nama/alamat/telepon/email → sidebar & kop laporan), **Profile**

## Struktur

```
html_version/
├── index.html              # shell HTML (AdminLTE + library via CDN, container #app-root)
└── assets/
    ├── css/
    │   ├── app.css         # port app.css + dashboard.css + login.css
    │   ├── darkmode.css    # dimuat dinamis saat dark mode aktif
    │   └── print.css       # port print.css (laporan cetak)
    └── js/
        ├── store.js        # data layer localStorage + seed (mengikuti assets_app.sql)
        ├── i18n.js         # EN + ID (port app/lang/*.php)
        ├── helpers.js      # e/rp/tgl/status_badge/pagination/audit/Auth/Setting
        ├── router.js       # hash router (port app/core/Router.php)
        ├── views.js        # layout shell/blank/print (port app/views/layouts/*.php)
        ├── pages.js        # login, dashboard, assets
        ├── pages2.js       # categories, users, borrowings, logs, trash, audit, notifications, api-tokens, settings, profile, search, import/export
        ├── pages3.js       # patching + reports
        └── app.js          # entry: register routes + hashchange
```

## Perbedaan vs Versi PHP

- **Penyimpanan**: localStorage (bukan MySQL/SQLite). Data per-browser, tidak dibagi.
- **Auth**: password disimpan plain di localStorage (demo only — versi PHP pakai bcrypt). Jangan pakai untuk produksi.
- **Routing**: hash-based (`#/assets`, `#/patching/1`) — tidak ada URL rewrite server.
- **API**: endpoint `#/api/assets` mengembalikan JSON statis (token tidak diverifikasi).
- **Foto**: disimpan sebagai base64 di localStorage (bukan file upload).

## Lisensi

MIT — sama dengan proyek induk.
