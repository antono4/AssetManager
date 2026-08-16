# Aplikasi Manajemen Aset (IT & Umum)

Aplikasi manajemen aset berbasis **PHP Native** dengan UI **AdminLTE 3** (terbaru), mendukung database **MySQL** (produksi) dan **SQLite** (demo/offline).

## Fitur

- **Dashboard** — statistik aset (total, tersedia, dipinjam, rusak), grafik distribusi per kategori (bar chart) & status (donut chart), aset terbaru, timeline aktivitas.
- **Manajemen Aset** — CRUD aset, pencarian (kode/nama/brand/lokasi), filter status & kategori, pagination, detail aset + riwayat log, ubah status cepat (tersedia/dipinjam/rusak) dengan catatan.
- **Kategori** — CRUD kategori (admin only), proteksi hapus bila masih dipakai aset.
- **Manajemen User** — CRUD user (admin only), role `admin`/`staff`, aktif/nonaktif, ganti password.
- **Profil** — user dapat mengedit profil & password sendiri.
- **Riwayat Aktivitas** — log semua perubahan aset (dibuat, diperbarui, status berubah) beserta pelakunya.
- **RBAC** — admin bisa semua; staff hanya lihat aset, ubah status, lihat riwayat, & edit profil.
- **Auth** — login/logout, password di-hash dengan bcrypt (`password_hash`), sesi aman (HttpOnly, SameSite).
- **Setup route** (`/setup`) — memperbarui password default ke hash bcrypt yang valid.

## Akun Default

| Username | Password | Role  |
|----------|----------|-------|
| admin    | admin123 | admin |
| staff    | staff123 | staff |

## Cara Menjalankan

### Mode Demo (SQLite, tanpa instalasi MySQL)

```bash
cd asset_app
php -S 0.0.0.0:8080 -t public public/index.php
```

Buka `http://localhost:8080`. Skema & data dummy otomatis dibuat di `database/asset_db.sqlite`. Login dengan akun default.

### Mode Produksi (MySQL)

1. Buat database & import skema:

```bash
mysql -u root -p -e "CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p asset_db < database/assets_app.sql
```

2. Set konfigurasi via environment variable (atau edit `config.php`):

```bash
export DB_DRIVER=mysql
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=asset_db
export DB_USER=root
export DB_PASS=secret
```

3. Jalankan dengan PHP built-in server atau web server (Apache/nginx):

```bash
php -S 0.0.0.0:8080 -t public public/index.php
```

4. (Penting) Karena hash bcrypt di `assets_app.sql` adalah placeholder, akses `http://localhost:8080/setup` sekali untuk mengisi password valid, atau jalankan:

```bash
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
```

lalu `UPDATE users SET password='<hash>' WHERE username='admin';`

## Struktur Proyek

```
asset_app/
├── config.php                 # Konfigurasi & autoload
├── database/
│   ├── assets_app.sql         # Skema MySQL (asli) + data dummy
│   └── asset_db.sqlite        # (auto-generated untuk demo SQLite)
├── public/
│   ├── index.php              # Entry point + routing
│   └── assets/
│       ├── css/app.css        # CSS custom
│       └── js/app.js          # JS custom
└── app/
    ├── core/
    │   ├── Database.php       # PDO adapter (MySQL + SQLite)
    │   ├── Auth.php           # Session, login, RBAC
    │   ├── Flash.php          # Pesan flash
    │   ├── View.php           # Template engine + helpers
    │   └── Router.php         # Router sederhana
    ├── models/
    │   ├── Asset.php
    │   ├── Category.php
    │   ├── AssetLog.php
    │   └── User.php
    ├── controllers/
    │   ├── AuthController.php
    │   ├── DashboardController.php
    │   ├── AssetController.php
    │   ├── CategoryController.php
    │   ├── UserController.php
    │   └── LogController.php
    └── views/
        ├── layouts/           # app.php, blank.php
        ├── pages/             # login, dashboard, assets/*, categories/*, users/*, logs/*
        └── partials/
```

## Teknologi

- **PHP 8+** (native, tanpa framework)
- **PDO** — adapter MySQL & SQLite
- **AdminLTE 3.2** + Bootstrap 4 + Font Awesome 6 + Bootstrap Icons (via CDN)
- **ApexCharts** — grafik dashboard
- **bcrypt** — hashing password

## Catatan Keamanan

- Password disimpan sebagai hash bcrypt, bukan plaintext.
- Sesi menggunakan cookie HttpOnly + SameSite.
- RBAC membatasi akses halaman admin.
- Escape HTML di semua output (`e()` helper) untuk mencegah XSS.
