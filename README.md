<div align="center">

# 📦 AssetManager

### Aplikasi Manajemen Aset IT & Umum

**PHP Native · AdminLTE 3 · MySQL/SQLite · Multi-Bahasa · PWA · 20+ Fitur**

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![AdminLTE](https://img.shields.io/badge/AdminLTE-3.2-2b3a55)](https://adminlte.io)
[![Database](https://img.shields.io/badge/DB-MySQL%20%7C%20SQLite-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![PWA](https://img.shields.io/badge/PWA-Installable-5A0FC8?logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

[📱 Live Demo](#-quick-start) · [📖 Dokumentasi](#-dokumentasi) · [📦 Instalasi](#-instalasi) · [⚙️ Konfigurasi](#️-konfigurasi) · [🔒 RBAC](#-hak-akses-rbac)

</div>

---

## 📋 Tentang Aplikasi

**AssetManager** adalah aplikasi web untuk mengelola aset IT & umum secara komprehensif. Dibangun dengan PHP Native (tanpa framework), UI AdminLTE 3, dan mendukung database MySQL (produksi) maupun SQLite (demo). Aplikasi ini dilengkapi dengan 20+ fitur mulai dari manajemen aset, patching kuartalan, laporan, peminjaman, hingga REST API.

---

## ✨ Fitur Utama

### 📊 Dashboard Modern
- Welcome header dengan greeting dinamis (pagi/siang/sore/malam) + emoji animasi
- 4 kartu statistik gradient (Total, Tersedia, Dipinjam, Rusak) — klik untuk filter
- Grafik ApexCharts (bar distribusi kategori & donut status dengan total di tengah)
- Widget Patching dengan progress bar
- Quick Access shortcut ke menu utama
- Aset Terbaru dengan thumbnail foto
- Aktivitas Terbaru — timeline modern dengan icon berwarna per aksi
- **Dark Mode** — toggle dark/light (persisten via cookie)

### 📦 Manajemen Aset
- CRUD aset lengkap dengan **upload foto** (JPG/PNG/GIF/WebP, maks 5MB)
- Pencarian + filter status & kategori + pagination
- **QR Code** per aset — scan untuk buka detail
- **Soft Delete** — hapus ke trash, restore, atau hapus permanen
- **Export CSV** — download daftar aset dengan filter aktif
- **Import CSV** — tambah banyak aset sekaligus
- **Depreciation** — hitung nilai buku aset (straight-line)
- **Multi-Currency** — IDR, USD, EUR
- Kode aset auto-generate (AST-0001, ...)
- **Harga hanya tampil untuk admin**

### 🛡 Modul Patching (Kuartalan / per 3 Bulan)
- Jadwal patching per kuartal (Q1-Q4) dengan auto-fill tanggal
- Generate checklist per aset IT (exclude kategori "Umum")
- 6 item checklist standar (Update OS, Antivirus, Backup, Cek Log, Restart, Verifikasi)
- **Input kode patching** per item per komputer (mis: KB5079473)
- Centang item via AJAX — status auto-update
- **Daftar Komputer Patching** — tabel + matriks kode patching

### 📄 Modul Report (Laporan)
- 4 tab: Ringkasan, Per Kategori, Per Lokasi, Detail Aset
- Filter: kategori, status, lokasi, rentang tanggal pembelian
- **Cetak / Simpan PDF** — halaman print-friendly dengan kop & tanda tangan

### 🤝 Peminjaman Aset (Borrowing)
- Catat peminjaman: borrower, tanggal pinjam, expected return, note
- Auto-set status aset → dipinjam; Return → tersedia
- **Overdue detection** — peminjaman lewat jatuh tempo

### 🔔 Notifikasi
- In-app notification dengan badge di navbar
- Auto-check patching overdue
- Mark read / mark all read

### 🔐 Keamanan & Audit
- **Rate Limiting** login — max 5 percobaan, lock 15 menit
- **Audit Trail** — log semua modul dengan user, IP, waktu
- **REST API Token** — endpoint `/api/assets` dengan token auth
- Password bcrypt, session HttpOnly + SameSite, escape HTML (anti-XSS)
- **Activity by User** — lihat aktivitas per user

### 🌐 Multi-Bahasa (i18n)
- **Default: English** + **Bahasa Indonesia**
- Switcher di navbar (flag icon) + tombol EN/ID di login
- 200+ key terjemahan, preferensi persisten (cookie 1 tahun)

### 📲 PWA (Progressive Web App)
- Installable di mobile/desktop
- Service worker untuk offline cache

### 🎨 UI/UX
- Login modern split-screen dengan glassmorphism
- Dark mode penuh (sidebar, card, table, form, modal)
- Global search di navbar
- Responsive (desktop, tablet, mobile)

---

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.0+ (native, tanpa framework) |
| Database | MySQL 5.7+ / MariaDB 10+ atau SQLite (demo) |
| UI Framework | AdminLTE 3.2 + Bootstrap 4.6 |
| Icon | Font Awesome 6, Bootstrap Icons, Flag Icons |
| Chart | ApexCharts 3.49 |
| Auth | bcrypt + Rate Limiting + API Token |
| PWA | manifest.json + Service Worker |
| Font | Source Sans Pro |

---

## 📁 Struktur Proyek

```
AssetManager/
├── README.md                        # File ini
├── LICENSE                          # MIT License
├── index.html                       # GitHub Pages landing page
├── asset_app/
│   ├── config.php                   # Konfigurasi & autoload
│   ├── README.md                    # Dokumentasi teknis
│   ├── INSTALL.md                   # Panduan instalasi detail
│   ├── USER_GUIDE.md                # Panduan penggunaan detail
│   ├── XAMPP_Installation_Guide.pdf # Panduan instalasi XAMPP (PDF)
│   ├── LICENSE                      # MIT License (copy)
│   ├── database/
│   │   ├── assets_app.sql           # Skema MySQL + data dummy
│   │   └── asset_db.sqlite          # Auto-generated (demo SQLite)
│   ├── public/
│   │   ├── index.php                # Entry point + routing
│   │   ├── .htaccess                # Apache rewrite
│   │   ├── manifest.json            # PWA manifest
│   │   ├── sw.js                    # Service Worker
│   │   └── assets/
│   │       ├── css/ (app, dashboard, login, print, darkmode)
│   │       ├── js/app.js
│   │       └── uploads/assets/      # Foto aset
│   └── app/
│       ├── core/ (Database, Auth, Flash, Lang, View, Router)
│       ├── models/ (Asset, Category, AssetLog, User,
│       │            PatchSchedule, PatchChecklist,
│       │            AuditTrail, Notification, Borrowing, ApiToken)
│       ├── controllers/ (Auth, Dashboard, Asset, Category, User,
│       │                  Log, Report, Patch, Extended)
│       ├── lang/ (en.php, id.php)
│       └── views/
│           ├── layouts/ (app, blank, print)
│           └── pages/ (login, dashboard, assets/, categories/,
│                       users/, logs/, reports/, patch/,
│                       borrowings/, notifications/, audit/,
│                       api/, search, trash, import)
└── tools/
    └── generate_xampp_guide.py      # Generator PDF panduan XAMPP
```

---

## 🚀 Quick Start

### Mode Demo (SQLite — tanpa MySQL, langsung jalan)

```bash
git clone https://github.com/antono4/AssetManager.git
cd AssetManager/asset_app
php -S 0.0.0.0:8080 -t public public/index.php
```

Buka `http://localhost:8080` → login `admin` / `admin123`.

> ✅ Database SQLite & semua tabel + data dummy **otomatis dibuat** saat pertama dijalankan.

### Mode Produksi (MySQL)

```bash
# 1. Buat database
mysql -u root -p -e "CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import skema
mysql -u root -p asset_db < database/assets_app.sql

# 3. Jalankan
DB_DRIVER=mysql DB_HOST=127.0.0.1 DB_NAME=asset_db DB_USER=root DB_PASS=secret \
  php -S 0.0.0.0:8080 -t public public/index.php

# 4. Setup password (buka di browser)
# http://localhost:8080/setup
```

### Install di XAMPP

Lihat panduan lengkap: **[XAMPP_Installation_Guide.pdf](asset_app/XAMPP_Installation_Guide.pdf)**

Ringkasan:
1. Install XAMPP → start Apache & MySQL
2. Copy folder `asset_app` ke `C:\xampp\htdocs\`
3. Buat database `asset_db` via phpMyAdmin → import `assets_app.sql`
4. Akses `http://localhost/asset_app/public/setup` → reset password
5. Login `admin` / `admin123`

---

## 📖 Dokumentasi

| File | Deskripsi |
|------|-----------|
| [asset_app/README.md](asset_app/README.md) | Dokumentasi teknis lengkap (fitur, struktur, route, RBAC) |
| [asset_app/INSTALL.md](asset_app/INSTALL.md) | Panduan instalasi detail (SQLite, MySQL, Apache, Nginx, Docker) |
| [asset_app/USER_GUIDE.md](asset_app/USER_GUIDE.md) | Panduan penggunaan semua fitur |
| [asset_app/XAMPP_Installation_Guide.pdf](asset_app/XAMPP_Installation_Guide.pdf) | Panduan instalasi XAMPP dalam format PDF |
| [LICENSE](LICENSE) | MIT License |

---

## 📦 Instalasi

### Persyaratan

| Komponen | Versi |
|----------|-------|
| PHP | 8.0+ |
| PDO SQLite | (bundled, untuk demo) |
| PDO MySQL | (extension, untuk produksi) |
| Browser | Chrome / Firefox / Edge |

### Opsi Deploy

| Opsi | Deskripsi |
|------|-----------|
| **SQLite Demo** | `php -S 0.0.0.0:8080 -t public public/index.php` — tanpa MySQL |
| **MySQL** | Import SQL + set env var → jalankan PHP server |
| **XAMPP** | Copy ke htdocs + phpMyAdmin import → akses via browser |
| **Apache** | VirtualHost + .htaccess (sudah disediakan) |
| **Nginx** | PHP-FPM + try_files config |
| **Docker** | Dockerfile + docker run |

> 📖 Detail setiap opsi: [INSTALL.md](asset_app/INSTALL.md)

---

## ⚙️ Konfigurasi

Via environment variable atau edit `asset_app/config.php`:

| Variable | Default | Keterangan |
|----------|---------|-----------|
| `DB_DRIVER` | `sqlite` | `sqlite` (demo) atau `mysql` (produksi) |
| `DB_HOST` | `127.0.0.1` | Host MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_NAME` | `asset_db` | Nama database |
| `DB_USER` | `root` | User database |
| `DB_PASS` | (kosong) | Password database |

---

## 🔑 Akun Default

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| `admin` | `admin123` | admin | Semua fitur |
| `staff` | `staff123` | staff | Lihat aset, borrow, patching, profil |

> ⚠️ **Ganti password setelah instalasi!** Akses `/setup` atau edit di Manajemen User.

---

## 🔒 Hak Akses (RBAC)

| Fitur | Admin | Staff |
|-------|:-----:|:-----:|
| Dashboard, Dark Mode, Search, Bahasa | ✅ | ✅ |
| Lihat Aset, Export CSV | ✅ | ✅ |
| Lihat Harga, Depreciation | ✅ | ❌ |
| CRUD Aset, Upload Foto | ✅ | ❌ |
| Trash, Import CSV | ✅ | ❌ |
| Borrow/Return Aset | ✅ | ✅ |
| Ubah Status Aset | ✅ | ✅ |
| Kategori, Manajemen User | ✅ | ❌ |
| Audit Trail, API Token | ✅ | ❌ |
| Buat Jadwal Patching, Generate | ✅ | ❌ |
| Centang Checklist Patching | ✅ | ✅ |
| Laporan, Riwayat, Notifikasi | ✅ | ✅ |
| Edit Profil | ✅ | ✅ |

---

## 🛣 API & Route

### REST API
```bash
# Generate token di /api-tokens (admin)
# Lalu akses:
GET /api/assets
Header: X-Api-Token: YOUR_TOKEN
# atau
GET /api/assets?token=YOUR_TOKEN
```

### Route Utama
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET/POST | `/login` | Login |
| GET | `/dashboard` | Dashboard |
| GET | `/assets` | Daftar aset |
| GET | `/assets/{id}` | Detail aset + QR + depreciation |
| GET/POST | `/assets/create` | Tambah aset (admin) |
| GET | `/assets/export` | Export CSV |
| GET/POST | `/assets/import` | Import CSV (admin) |
| GET | `/assets/trash` | Trash (admin) |
| GET/POST | `/assets/{id}/borrow` | Pinjam aset |
| GET | `/borrowings` | Daftar peminjaman |
| GET | `/patching` | Jadwal patching |
| GET | `/patching/{id}/computers` | Daftar komputer + kode patching |
| GET | `/reports` | Laporan |
| GET | `/reports/print` | Cetak PDF |
| GET | `/audit` | Audit trail (admin) |
| GET/POST | `/api-tokens` | API token (admin) |
| GET | `/api/assets` | REST API (token auth) |
| GET | `/search?q=` | Global search |
| GET | `/dark-mode` | Toggle dark mode |
| GET | `/language/set?lang=` | Ganti bahasa |

> Daftar lengkap 40+ route: [asset_app/README.md](asset_app/README.md)

---

## 🔧 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Grafik kosong | Cek koneksi internet (CDN ApexCharts) |
| Login gagal/locked | Rate limit: tunggu 15 menit atau `/setup` |
| Foto tidak upload | Folder `uploads/assets` writable (775) |
| 404 semua halaman | Akses via `php -S ... public/index.php` |
| API 401 | Sertakan token: `?token=YOUR_TOKEN` |
| ERR_FAILED login | Clear browser cache/service worker |

---

## ❓ FAQ

<details>
<summary><b>Bisakah pakai tanpa MySQL?</b></summary>

Ya, mode SQLite otomatis. Jalankan `php -S 0.0.0.0:8080 -t public public/index.php` — database & data dummy otomatis dibuat.
</details>

<details>
<summary><b>Cara deploy ke server produksi?</b></summary>

Lihat [INSTALL.md](asset_app/INSTALL.md) — 5 opsi: SQLite, MySQL, Apache, Nginx, Docker.
</details>

<details>
<summary><b>Cara backup data?</b></summary>

MySQL: `mysqldump -u root -p asset_db > backup.sql`  
SQLite: copy `database/asset_db.sqlite`  
Foto: `tar -czf photos.tar.gz public/uploads/assets/`
</details>

<details>
<summary><b>Cara pakai REST API?</b></summary>

1. Login sebagai admin → menu API Token → Generate Token  
2. `GET /api/assets?token=YOUR_TOKEN` → JSON response
</details>

<details>
<summary><b>Cara tambah bahasa lain?</b></summary>

Tambah file `app/lang/xx.php` + daftar di `Lang::SUPPORTED` di `app/core/Lang.php`.
</details>

---

## 📄 Lisensi

**MIT License** — lihat [LICENSE](LICENSE).

Anda bebas untuk:
- ✅ Menggunakan secara komersial maupun non-komersial
- ✅ Memodifikasi dan mendistribusikan
- ✅ Menggunakan secara privat
- ✅ Membuka source code atau menutupnya

---

## 👥 Kontribusi

1. Fork repo ini
2. Buat branch fitur (`git checkout -b feature/nama-fitur`)
3. Commit perubahan (`git commit -m 'Tambah fitur X'`)
4. Push ke branch (`git push origin feature/nama-fitur`)
5. Buat Pull Request

---

<div align="center">

### 🌐 Links

[📦 GitHub](https://github.com/antono4/AssetManager) · [📖 Dokumentasi](asset_app/README.md) · [📦 Instalasi](asset_app/INSTALL.md) · [📖 Penggunaan](asset_app/USER_GUIDE.md) · [📄 PDF XAMPP](asset_app/XAMPP_Installation_Guide.pdf)

**Dibuat dengan ❤️ menggunakan PHP Native & AdminLTE 3**

© 2026 AssetManager · MIT License

</div>
